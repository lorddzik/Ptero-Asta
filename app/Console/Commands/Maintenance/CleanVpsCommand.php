<?php

namespace Pterodactyl\Console\Commands\Maintenance;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CleanVpsCommand extends Command
{
    protected $signature = 'ptero:clean-vps';

    protected $description = 'Clean host VPS cache, Docker logs, journald logs, APT cache, and flush RAM pagecache.';

    public function handle(): int
    {
        $this->info('Starting Asta VPS Deep Clean & Cache Optimizer...');

        $diskBefore = @disk_free_space('/') ?: 0;

        // 1. Truncate Docker logs
        $this->line('-> Truncating Docker container json logs...');
        $dockerLogFiles = @glob('/var/lib/docker/containers/*/*-json.log') ?: [];
        foreach ($dockerLogFiles as $file) {
            if (@is_file($file) && @is_writable($file)) {
                @file_put_contents($file, '');
            }
        }
        @shell_exec('truncate -s 0 /var/lib/docker/containers/*/*-json.log 2>/dev/null');

        // 2. Prune Docker build cache & dangling images
        $this->line('-> Pruning Docker build cache and dangling images...');
        @shell_exec('docker image prune -f 2>/dev/null');
        @shell_exec('docker builder prune -f 2>/dev/null');

        // 3. Vacuum systemd journal to 50MB
        $this->line('-> Vacuuming systemd journal logs to 50MB...');
        @shell_exec('journalctl --vacuum-size=50M 2>/dev/null');

        // 4. Clean APT package cache
        $this->line('-> Cleaning APT package archive cache...');
        @shell_exec('apt-get clean 2>/dev/null');

        // 5. Clean Panel cache & logs
        $this->line('-> Refreshing Pterodactyl panel framework cache and rotating logs...');
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
        } catch (\Throwable $e) {
            $this->warn('Artisan warning: ' . $e->getMessage());
        }

        $logFiles = @glob(storage_path('logs/*.log')) ?: [];
        foreach ($logFiles as $logFile) {
            if (basename($logFile) === 'laravel.log' && @filesize($logFile) > 10 * 1024 * 1024) {
                @file_put_contents($logFile, '');
            } elseif (@filemtime($logFile) < (time() - 7 * 86400)) {
                @unlink($logFile);
            }
        }

        // 6. Clean stale temp files
        $this->line('-> Purging stale temporary files in /tmp and /var/tmp...');
        @shell_exec('find /tmp /var/tmp -mindepth 1 -mtime +2 -delete 2>/dev/null');

        // 7. Flush Linux RAM PageCache & Buffers
        $this->line('-> Dropping Linux kernel PageCache and memory buffers...');
        @shell_exec('sync; echo 3 > /proc/sys/vm/drop_caches 2>/dev/null');

        $diskAfter = @disk_free_space('/') ?: 0;
        $diskFreed = max(0, $diskAfter - $diskBefore);

        $this->info('✓ VPS Deep Clean completed successfully!');
        $this->info('Disk space freed: ' . $this->formatBytes($diskFreed));

        return 0;
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
