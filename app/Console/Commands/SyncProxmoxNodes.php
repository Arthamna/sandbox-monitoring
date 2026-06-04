<?php

namespace App\Console\Commands;

use App\Services\CtfLogger;
use App\Services\ProxmoxNodeMaintenanceService;
use Illuminate\Console\Command;

class SyncProxmoxNodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxmox:nodes-sync {--dry-run}';
    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Randomize status and weight for Proxmox nodes';

    public function handle(ProxmoxNodeMaintenanceService $service): int
    {
        $startTime = microtime(true);

        $result = $service->sync((bool) $this->option('dry-run'));

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        $this->info("Done. Total: {$result['total']}, Updated: {$result['updated']}, Dry run: " . ($result['dry_run'] ? 'yes' : 'no'));

        CtfLogger::info('command:proxmox:nodes-sync', "Sync completed. Total: {$result['total']}, Updated: {$result['updated']}", [
            'total' => $result['total'],
            'updated' => $result['updated'],
            'dry_run' => $result['dry_run'],
            'duration_ms' => $durationMs,
        ]);

        return self::SUCCESS;
    }
}
