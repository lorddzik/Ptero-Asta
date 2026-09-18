<?php

namespace Pterodactyl\Console\Commands\Maintenance;

use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Pterodactyl\Models\Allocation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Pterodactyl\Services\Servers\ServerDeletionService;

class FactoryResetCommand extends Command
{
    protected $signature = 'ptero:factory-reset {--force : Bypass the confirmation prompt}';

    protected $description = 'Wipe all client servers, containers, disk volumes, and non-admin users. Returns Pterodactyl to a fresh install state.';

    public function __construct(private ServerDeletionService $deletionService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->alert('DANGER: FACTORY RESET PTERODACTYL PANEL');
        $this->warn('This action will permanently delete ALL client servers, containers, disk volumes, and non-admin users.');
        $this->warn('Root Admin accounts, nodes, nests/eggs, and panel configuration will be preserved.');

        if (!$this->option('force')) {
            if (!$this->confirm('Are you ABSOLUTELY sure you want to reset all servers and return to a fresh panel state?')) {
                $this->info('Factory reset aborted.');
                return 0;
            }

            $confirmText = $this->ask('Type "RESET-ASTA" to confirm:');
            if ($confirmText !== 'RESET-ASTA') {
                $this->error('Confirmation string did not match. Aborting.');
                return 1;
            }
        }

        $this->info('Starting Factory Reset process...');

        // 1. Delete all servers
        $servers = Server::all();
        $this->line("-> Deleting {$servers->count()} servers from database and Wings...");
        $deletedServers = 0;
        foreach ($servers as $server) {
            try {
                $this->deletionService->withForce(true)->handle($server);
                $deletedServers++;
            } catch (\Throwable $e) {
                Log::error("CLI FactoryReset: Failed deleting server #{$server->id}: " . $e->getMessage());
                try {
                    $server->allocations()->update(['server_id' => null, 'notes' => null]);
                    $server->databases()->delete();
                    $server->delete();
                    $deletedServers++;
                } catch (\Throwable $ex) {}
            }
        }
        $this->info("✓ Deleted {$deletedServers} servers.");

        // 2. Reset allocations
        $this->line('-> Resetting all port allocations to free state...');
        Allocation::query()->update(['server_id' => null, 'notes' => null]);
        $this->info('✓ Port allocations reset.');

        // 3. Delete non-admin users
        $this->line('-> Deleting non-admin users...');
        $deletedUsers = User::query()->where('root_admin', 0)->delete();
        $this->info("✓ Deleted {$deletedUsers} non-admin users. Root Admins preserved.");

        // 4. Force stop and remove Docker containers
        $this->line('-> Removing all Docker containers on host...');
        @shell_exec('docker rm -f $(docker ps -aq) 2>/dev/null');

        // 5. Clean local volumes
        $this->line('-> Emptying /var/lib/pterodactyl/volumes/*...');
        @shell_exec('rm -rf /var/lib/pterodactyl/volumes/* 2>/dev/null');

        // 6. Prune Docker volumes & images
        $this->line('-> Pruning Docker system (volumes & images)...');
        @shell_exec('docker system prune -a --volumes -f 2>/dev/null');

        // 7. Vacuum journals & truncate logs
        $this->line('-> Vacuuming systemd journal & truncating Docker logs...');
        @shell_exec('truncate -s 0 /var/lib/docker/containers/*/*-json.log 2>/dev/null');
        @shell_exec('journalctl --vacuum-size=50M 2>/dev/null');
        @shell_exec('apt-get clean 2>/dev/null');
        @shell_exec('find /tmp /var/tmp -mindepth 1 -delete 2>/dev/null');

        // 8. Flush cache & RAM
        $this->line('-> Refreshing panel cache and flushing RAM PageCache...');
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
        } catch (\Throwable $e) {}

        @shell_exec('sync; echo 3 > /proc/sys/vm/drop_caches 2>/dev/null');

        $this->info('=====================================================');
        $this->info('✓ FACTORY RESET COMPLETE! Panel is now in FRESH INSTALL state.');

        return 0;
    }
}
