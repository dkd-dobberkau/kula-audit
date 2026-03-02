<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Kula Audit',
    'description' => 'TYPO3 extension audit: upgrade readiness, vulnerabilities, and SBOM via Kula API. Dashboard widget + Backend module + CLI command.',
    'category' => 'module',
    'author' => 'dkd Internet Service GmbH',
    'author_email' => 'info@dkd.de',
    'author_company' => 'dkd Internet Service GmbH',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'dashboard' => '12.4.0-13.99.99',
        ],
    ],
];
