<?php

declare(strict_types=1);

namespace Dkd\KulaAudit\Command;

use Dkd\KulaAudit\Service\AuditService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'kula:audit',
    description: 'Audit TYPO3 extensions via Kula API (upgrade readiness + vulnerabilities)',
)]
class AuditCommand extends Command
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Bypass cache and re-run audit');
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Output raw JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool)$input->getOption('force');
        $jsonOutput = (bool)$input->getOption('json');

        $io->title('Kula Audit');
        $io->text('Checking composer.lock against Kula API...');

        $report = $this->auditService->runAudit($force);

        if (isset($report['error'])) {
            $io->error($report['error']);
            return Command::FAILURE;
        }

        if ($jsonOutput) {
            $output->writeln(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return Command::SUCCESS;
        }

        $this->renderReport($io, $report);
        return Command::SUCCESS;
    }

    private function renderReport(SymfonyStyle $io, array $report): void
    {
        $io->section(sprintf(
            'Summary: %d packages | Target: TYPO3 v%d',
            $report['packages_total'] ?? 0,
            $report['target_major'] ?? 13,
        ));

        // Upgrade readiness
        $upgrade = $report['upgrade'] ?? [];
        if (!empty($upgrade['packages'])) {
            $io->text(sprintf(
                'Upgrade: <fg=green>%d ready</> | <fg=yellow>%d pre-release</> | <fg=red>%d blocked</>',
                $upgrade['green'] ?? 0,
                $upgrade['yellow'] ?? 0,
                $upgrade['red'] ?? 0,
            ));

            $rows = [];
            foreach ($upgrade['packages'] as $pkg) {
                $status = match ($pkg['upgrade_status']) {
                    'green' => '<fg=green>ready</>',
                    'yellow' => '<fg=yellow>pre-release</>',
                    'red' => '<fg=red>blocked</>',
                    default => 'unknown',
                };
                $rows[] = [$status, $pkg['name'], $pkg['version'], $pkg['upgrade_version']];
            }
            $io->table(['Status', 'Package', 'Current', 'Upgrade'], $rows);
        }

        // Vulnerabilities
        $security = $report['security'] ?? [];
        $totalVulns = $security['total_vulns'] ?? 0;
        if ($totalVulns > 0) {
            $io->warning(sprintf('%d vulnerabilities found', $totalVulns));
            $rows = [];
            foreach ($security['packages'] ?? [] as $pkg) {
                foreach ($pkg['vulns'] as $vuln) {
                    $rows[] = [$pkg['name'], $pkg['version'], $vuln['id'], $vuln['summary']];
                }
            }
            $io->table(['Package', 'Version', 'ID', 'Summary'], $rows);
        } else {
            $io->success('No known vulnerabilities found');
        }
    }
}
