<?php

declare(strict_types=1);

use Dkd\KulaAudit\Controller\AuditController;

return [
    'admin_kula_audit' => [
        'parent' => 'admin',
        'position' => ['after' => '*'],
        'access' => 'admin',
        'workspaces' => '*',
        'iconIdentifier' => 'module-kula-audit',
        'labels' => 'LLL:EXT:kula_audit/Resources/Private/Language/locallang.xlf:module',
        'routes' => [
            '_default' => [
                'target' => AuditController::class . '::overview',
            ],
            'runAudit' => [
                'target' => AuditController::class . '::runAudit',
                'methods' => ['POST'],
            ],
        ],
    ],
];
