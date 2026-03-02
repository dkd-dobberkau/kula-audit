<?php

declare(strict_types=1);

namespace Dkd\KulaAudit\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Registry;

/**
 * Audit service: reads composer.lock, sends to Kula API, caches results.
 */
class AuditService
{
    private const REGISTRY_NAMESPACE = 'tx_kulaaudit';
    private const RESULT_KEY = 'latest_result';
    private const TTL_SECONDS = 86400; // 24 hours
    private const DEFAULT_API_URL = 'https://app.kula-audit.de/api/audit';
    private const DEFAULT_TARGET = 13;

    public function __construct(
        private readonly Registry $registry,
    ) {
    }

    /**
     * Run audit: read composer.lock, POST to Kula API, cache result.
     *
     * @param bool $force Skip cache and re-run
     * @return array The audit report
     */
    public function runAudit(bool $force = false): array
    {
        if (!$force) {
            $cached = $this->getLatestResult();
            if ($cached !== [] && $this->isFresh($cached)) {
                return $cached;
            }
        }

        $lockPath = $this->getComposerLockPath();
        if (!file_exists($lockPath)) {
            return ['error' => 'composer.lock not found at ' . $lockPath];
        }

        $report = $this->callApi($lockPath);
        if (!isset($report['error'])) {
            $report['_cached_at'] = time();
            $this->registry->set(self::REGISTRY_NAMESPACE, self::RESULT_KEY, $report);
        }

        return $report;
    }

    /**
     * Get the latest cached audit result from sys_registry.
     */
    public function getLatestResult(): array
    {
        return $this->registry->get(self::REGISTRY_NAMESPACE, self::RESULT_KEY, []);
    }

    /**
     * Clear cached audit results.
     */
    public function clearResult(): void
    {
        $this->registry->remove(self::REGISTRY_NAMESPACE, self::RESULT_KEY);
    }

    private function isFresh(array $result): bool
    {
        $cachedAt = $result['_cached_at'] ?? 0;
        return (time() - $cachedAt) < self::TTL_SECONDS;
    }

    private function getComposerLockPath(): string
    {
        return Environment::getProjectPath() . '/composer.lock';
    }

    private function getApiUrl(): string
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['kula_audit']['apiUrl']
            ?? self::DEFAULT_API_URL;
    }

    private function getTargetMajor(): int
    {
        return (int)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['kula_audit']['targetMajor']
            ?? self::DEFAULT_TARGET);
    }

    /**
     * POST composer.lock to the Kula API and return the report.
     */
    private function callApi(string $lockPath): array
    {
        $client = new Client(['timeout' => 120]);

        try {
            $response = $client->post($this->getApiUrl(), [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => Utils::tryFopen($lockPath, 'r'),
                        'filename' => 'composer.lock',
                    ],
                    [
                        'name' => 'target',
                        'contents' => (string)$this->getTargetMajor(),
                    ],
                ],
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (!is_array($data)) {
                return ['error' => 'Invalid API response'];
            }

            return $data;
        } catch (\Throwable $e) {
            return ['error' => 'API request failed: ' . $e->getMessage()];
        }
    }
}
