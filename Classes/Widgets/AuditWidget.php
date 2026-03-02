<?php

declare(strict_types=1);

namespace Dkd\KulaAudit\Widgets;

use Dkd\KulaAudit\Service\AuditService;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class AuditWidget implements WidgetInterface
{
    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly AuditService $auditService,
        private readonly StandaloneView $view,
    ) {
    }

    public function renderWidgetContent(): string
    {
        $report = $this->auditService->getLatestResult();
        $hasData = !empty($report) && !isset($report['error']);

        // Determine traffic light status
        $status = 'unknown';
        if ($hasData) {
            $upgrade = $report['upgrade'] ?? [];
            $vulns = $report['security']['total_vulns'] ?? 0;
            $red = $upgrade['red'] ?? 0;

            if ($red === 0 && $vulns === 0) {
                $status = 'green';
            } elseif ($red > 0 || $vulns > 0) {
                $status = ($red > 3 || $vulns > 3) ? 'red' : 'yellow';
            }
        }

        $this->view->setTemplate('Widget/AuditWidget');
        $this->view->assignMultiple([
            'report' => $report,
            'hasData' => $hasData,
            'status' => $status,
            'configuration' => $this->configuration,
        ]);

        return $this->view->render();
    }

    public function getOptions(): array
    {
        return [];
    }
}
