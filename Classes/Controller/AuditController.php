<?php

declare(strict_types=1);

namespace Dkd\KulaAudit\Controller;

use Dkd\KulaAudit\Service\AuditService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Fluid\View\StandaloneView;

#[AsController]
class AuditController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly AuditService $auditService,
        private readonly UriBuilder $uriBuilder,
    ) {
    }

    public function overview(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $report = $this->auditService->getLatestResult();

        // Build "Run Audit" button URL
        $runAuditUrl = (string)$this->uriBuilder->buildUriFromRoute('admin_kula_audit.runAudit');

        $view = $this->createView('Audit/Overview');
        $view->assignMultiple([
            'report' => $report,
            'hasData' => !empty($report) && !isset($report['error']),
            'error' => $report['error'] ?? null,
            'cachedAt' => $report['_cached_at'] ?? null,
            'runAuditUrl' => $runAuditUrl,
        ]);

        $moduleTemplate->setContent($view->render());
        return $moduleTemplate->renderResponse();
    }

    public function runAudit(ServerRequestInterface $request): ResponseInterface
    {
        $this->auditService->runAudit(force: true);
        $overviewUrl = (string)$this->uriBuilder->buildUriFromRoute('admin_kula_audit');
        return new RedirectResponse($overviewUrl);
    }

    private function createView(string $templateName): StandaloneView
    {
        $view = new StandaloneView();
        $view->setTemplateRootPaths(['EXT:kula_audit/Resources/Private/Templates/']);
        $view->setTemplate($templateName);
        return $view;
    }
}
