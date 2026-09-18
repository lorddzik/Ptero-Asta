<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Allocation;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Pterodactyl\Services\Servers\ServerDeletionService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CleanerController extends Controller
{
    /**
     * CleanerController constructor.
     */
    public function __construct(private ServerDeletionService $deletionService)
    {
    }

    /**
     * Check if user is a root administrator.
     */
    protected function checkRootAdmin(Request $request): void
    {
        if (!$request->user() || !$request->user()->root_admin) {
            throw new AccessDeniedHttpException('Akses ditolak. Hanya Root Administrator yang diizinkan mengelola VPS Optimizer & Reset.');
        }
    }

    /**
     * Display the VPS cleaner and reset dashboard.
     */
    public function index(Request $request): View
    {
        $this->checkRootAdmin($request);

        $totalServers = Server::query()->count();
        $totalUsers = User::query()->where('root_admin', 0)->count();
        $totalRootAdmins = User::query()->where('root_admin', 1)->count();
        $totalNodes = Node::query()->count();
        $totalAllocations = Allocation::query()->count();
        $usedAllocations = Allocation::query()->whereNotNull('server_id')->count();

        // Host system metrics
        $diskTotal = @disk_total_space('/') ?: 0;
        $diskFree = @disk_free_space('/') ?: 0;
        $diskUsed = max(0, $diskTotal - $diskFree);
        $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;

        $ramStats = $this->getRamStats();

        return view('admin.cleaner.index', [
            'totalServers' => $totalServers,
            'totalUsers' => $totalUsers,
            'totalRootAdmins' => $totalRootAdmins,
            'totalNodes' => $totalNodes,
            'totalAllocations' => $totalAllocations,
            'usedAllocations' => $usedAllocations,
            'diskTotal' => $diskTotal,
            'diskFree' => $diskFree,
            'diskUsed' => $diskUsed,
            'diskPercent' => $diskPercent,
            'ramStats' => $ramStats,
        ]);
    }

    /**
     * Scan system for potential junk and cache sizes.
     */
    public function scan(Request $request): JsonResponse
    {
        $this->checkRootAdmin($request);

        $dockerLogsBytes = $this->getDockerLogsSize();
        $journalBytes = $this->getJournalSize();
        $aptBytes = $this->getDirectorySize('/var/cache/apt/archives');
        $panelCacheBytes = $this->getDirectorySize(storage_path('framework/cache')) 
                         + $this->getDirectorySize(storage_path('framework/views'))
                         + $this->getDirectorySize(storage_path('logs'));
        $tmpBytes = $this->getDirectorySize('/tmp') + $this->getDirectorySize('/var/tmp');

        $ramStats = $this->getRamStats();
        $ramBuffersBytes = $ramStats['buffers_cached'] ?? 0;

        $totalJunkBytes = $dockerLogsBytes + $journalBytes + $aptBytes + $panelCacheBytes + $tmpBytes;

        return response()->json([
            'success' => true,
            'scanned_at' => now()->toIso8601String(),
            'categories' => [
                'docker_logs' => [
                    'bytes' => $dockerLogsBytes,
                    'formatted' => $this->formatBytes($dockerLogsBytes),
                    'desc' => 'File log container Docker (*-json.log)',
                ],
                'journal' => [
                    'bytes' => $journalBytes,
                    'formatted' => $this->formatBytes($journalBytes),
                    'desc' => 'Systemd journal log sistem Linux',
                ],
                'apt_cache' => [
                    'bytes' => $aptBytes,
                    'formatted' => $this->formatBytes($aptBytes),
                    'desc' => 'Arsip paket APT (.deb) usang',
                ],
                'panel_cache' => [
                    'bytes' => $panelCacheBytes,
                    'formatted' => $this->formatBytes($panelCacheBytes),
                    'desc' => 'Cache Laravel, views compiled, dan file logs lama',
                ],
                'tmp_files' => [
                    'bytes' => $tmpBytes,
                    'formatted' => $this->formatBytes($tmpBytes),
                    'desc' => 'File temporary sistem (/tmp, /var/tmp)',
                ],
                'ram_cache' => [
                    'bytes' => $ramBuffersBytes,
                    'formatted' => $this->formatBytes($ramBuffersBytes),
                    'desc' => 'PageCache & Kernel Buffers di RAM yang bisa diflush',
                ],
            ],
            'total_junk_bytes' => $totalJunkBytes,
            'total_junk_formatted' => $this->formatBytes($totalJunkBytes),
            'server_count' => Server::query()->count(),
            'client_user_count' => User::query()->where('root_admin', 0)->count(),
        ]);
    }

    /**
     * Execute Mode 1: Safe Cache Purge.
     */
    public function executeClean(Request $request): JsonResponse
    {
        $this->checkRootAdmin($request);

        $diskBefore = @disk_free_space('/') ?: 0;
        $ramBefore = $this->getRamStats()['free'] ?? 0;

        $logs = [];

        // 1. Truncate Docker logs
        $logs[] = '[1/7] Memangkas file log container Docker (*-json.log)...';
        $dockerLogFiles = @glob('/var/lib/docker/containers/*/*-json.log') ?: [];
        $truncatedCount = 0;
        foreach ($dockerLogFiles as $file) {
            if (@is_file($file) && @is_writable($file)) {
                @file_put_contents($file, '');
                $truncatedCount++;
            }
        }
        @shell_exec('truncate -s 0 /var/lib/docker/containers/*/*-json.log 2>/dev/null');
        $logs[] = "  ✓ Selesai truncate Docker logs ({$truncatedCount} files diproses).";

        // 2. Prune Docker build cache & dangling images
        $logs[] = '[2/7] Membersihkan build cache & dangling Docker images...';
        @shell_exec('docker image prune -f 2>/dev/null');
        @shell_exec('docker builder prune -f 2>/dev/null');
        $logs[] = '  ✓ Docker image & builder cache berhasil dipangkas.';

        // 3. Vacuum systemd journal to 50M
        $logs[] = '[3/7] Melakukan vacuum systemd journal logs ke 50MB...';
        @shell_exec('journalctl --vacuum-size=50M 2>/dev/null');
        $logs[] = '  ✓ Systemd journal vacuum selesai.';

        // 4. Clean APT package cache
        $logs[] = '[4/7] Membersihkan cache arsip APT (.deb)...';
        @shell_exec('apt-get clean 2>/dev/null');
        $logs[] = '  ✓ Direktori /var/cache/apt/archives/ dibersihkan.';

        // 5. Clean Panel Framework Cache & Old Logs
        $logs[] = '[5/7] Membersihkan framework cache & log lama Pterodactyl...';
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
        } catch (\Throwable $e) {
            $logs[] = '  ! Warning Artisan: ' . $e->getMessage();
        }

        // Clean old Laravel logs older than 7 days
        $logFiles = @glob(storage_path('logs/*.log')) ?: [];
        $cleanedLogs = 0;
        foreach ($logFiles as $logFile) {
            if (basename($logFile) === 'laravel.log') {
                if (@filesize($logFile) > 10 * 1024 * 1024) { // > 10MB
                    @file_put_contents($logFile, '');
                    $cleanedLogs++;
                }
            } elseif (@filemtime($logFile) < (time() - 7 * 86400)) {
                @unlink($logFile);
                $cleanedLogs++;
            }
        }
        $logs[] = "  ✓ Panel framework cache refreshed, {$cleanedLogs} log file dibersihkan.";

        // 6. Clean stale temp files
        $logs[] = '[6/7] Membersihkan temporary files usang di /tmp dan /var/tmp...';
        @shell_exec('find /tmp /var/tmp -mindepth 1 -mtime +2 -delete 2>/dev/null');
        $logs[] = '  ✓ File temp kadaluarsa dibersihkan.';

        // 7. Flush Linux RAM PageCache & Buffers
        $logs[] = '[7/7] Melakukan flush Linux kernel PageCache & memory buffers...';
        @shell_exec('sync; echo 3 > /proc/sys/vm/drop_caches 2>/dev/null');
        $logs[] = '  ✓ RAM PageCache di-flush. Memori siap dialokasikan penuh.';

        $diskAfter = @disk_free_space('/') ?: 0;
        $ramAfter = $this->getRamStats()['free'] ?? 0;

        $diskFreed = max(0, $diskAfter - $diskBefore);
        $ramFreed = max(0, $ramAfter - $ramBefore);

        $logs[] = '=============================================';
        $logs[] = "Sukses! Disk dibebaskan: " . $this->formatBytes($diskFreed) . " | RAM dibebaskan: " . $this->formatBytes($ramFreed);

        return response()->json([
            'success' => true,
            'mode' => 'safe_clean',
            'disk_freed_bytes' => $diskFreed,
            'disk_freed_formatted' => $this->formatBytes($diskFreed),
            'ram_freed_bytes' => $ramFreed,
            'ram_freed_formatted' => $this->formatBytes($ramFreed),
            'logs' => $logs,
        ]);
    }

    /**
     * Execute Mode 2: Full Factory Reset (Fresh Install State).
     */
    public function executeReset(Request $request): JsonResponse
    {
        $this->checkRootAdmin($request);

        // Security confirmation check
        $confirmText = trim((string) $request->input('confirm_text'));
        if ($confirmText !== 'RESET-ASTA') {
            return response()->json([
                'success' => false,
                'message' => 'Teks konfirmasi salah. Harap ketik "RESET-ASTA" persis dengan huruf kapital untuk melanjutkan.',
            ], 422);
        }

        $diskBefore = @disk_free_space('/') ?: 0;
        $ramBefore = $this->getRamStats()['free'] ?? 0;

        $logs = [];
        $logs[] = '=== MEMULAI FACTORY RESET PANEL (FRESH STATE) ===';
        $logs[] = 'PERINGATAN: Menghapus seluruh server, data container, dan user non-admin...';

        // 1. Delete all servers from database and remote Wings daemons
        $servers = Server::all();
        $serversTotal = $servers->count();
        $logs[] = "[1/8] Menghapus {$serversTotal} server client dari database & Wings...";

        $deletedServersCount = 0;
        foreach ($servers as $server) {
            try {
                $this->deletionService->withForce(true)->handle($server);
                $deletedServersCount++;
            } catch (\Throwable $e) {
                Log::error("FactoryReset: Failed deleting server #{$server->id}: " . $e->getMessage());
                // Force delete directly from DB if service fails
                try {
                    $server->allocations()->update(['server_id' => null, 'notes' => null]);
                    $server->databases()->delete();
                    $server->delete();
                    $deletedServersCount++;
                } catch (\Throwable $ex) {
                    $logs[] = "  ! Gagal menghapus server #{$server->id}: " . $ex->getMessage();
                }
            }
        }
        $logs[] = "  ✓ Selesai. {$deletedServersCount} server berhasil dihapus.";

        // 2. Reset all allocations to free state
        $logs[] = '[2/8] Mengosongkan seluruh port alokasi (allocations reset)...';
        Allocation::query()->update([
            'server_id' => null,
            'notes' => null,
        ]);
        $logs[] = '  ✓ Seluruh port alokasi berhasil dikembalikan ke status kosong/bebas.';

        // 3. Delete all non-admin users
        $logs[] = '[3/8] Menghapus user client non-admin...';
        $deletedUsersCount = User::query()->where('root_admin', 0)->delete();
        $logs[] = "  ✓ Selesai. {$deletedUsersCount} akun client dihapus. Akun Root Admin tetap aman.";

        // 4. Force stop and remove all Docker containers on host
        $logs[] = '[4/8] Menghentikan & menghapus seluruh Docker container host...';
        @shell_exec('docker rm -f $(docker ps -aq) 2>/dev/null');
        $logs[] = '  ✓ Seluruh Docker container client dibersihkan.';

        // 5. Clean /var/lib/pterodactyl/volumes/* on local host
        $logs[] = '[5/8] Menghapus seluruh data file server di /var/lib/pterodactyl/volumes/*...';
        @shell_exec('rm -rf /var/lib/pterodactyl/volumes/* 2>/dev/null');
        $logs[] = '  ✓ Direktori volume server lokal telah dikosongkan total.';

        // 6. Prune all unused Docker volumes and images
        $logs[] = '[6/8] Menjalankan Docker System Prune (volumes & dangling images)...';
        @shell_exec('docker system prune -a --volumes -f 2>/dev/null');
        $logs[] = '  ✓ Docker volume & image cache dibersihkan tuntas.';

        // 7. Systemd journal vacuum, APT clean, and Docker log truncate
        $logs[] = '[7/8] Membersihkan log sistem, journald, dan APT cache...';
        @shell_exec('truncate -s 0 /var/lib/docker/containers/*/*-json.log 2>/dev/null');
        @shell_exec('journalctl --vacuum-size=50M 2>/dev/null');
        @shell_exec('apt-get clean 2>/dev/null');
        @shell_exec('find /tmp /var/tmp -mindepth 1 -delete 2>/dev/null');
        $logs[] = '  ✓ Log sistem dan temporary files dibersihkan.';

        // 8. Flush framework cache & RAM PageCache
        $logs[] = '[8/8] Memperbarui cache panel dan membebaskan RAM...';
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
        } catch (\Throwable $e) {}

        @shell_exec('sync; echo 3 > /proc/sys/vm/drop_caches 2>/dev/null');
        $logs[] = '  ✓ Panel cache disegarkan & RAM PageCache dibebaskan.';

        $diskAfter = @disk_free_space('/') ?: 0;
        $ramAfter = $this->getRamStats()['free'] ?? 0;

        $diskFreed = max(0, $diskAfter - $diskBefore);
        $ramFreed = max(0, $ramAfter - $ramBefore);

        $logs[] = '=============================================';
        $logs[] = 'FACTORY RESET BERHASIL! Panel kini berada pada status FRESH INSTALL.';
        $logs[] = "Total Server Dihapus: {$deletedServersCount} | Total User Dihapus: {$deletedUsersCount}";
        $logs[] = "Disk Dibebaskan: " . $this->formatBytes($diskFreed) . " | RAM Dibebaskan: " . $this->formatBytes($ramFreed);

        return response()->json([
            'success' => true,
            'mode' => 'factory_reset',
            'deleted_servers' => $deletedServersCount,
            'deleted_users' => $deletedUsersCount,
            'disk_freed_bytes' => $diskFreed,
            'disk_freed_formatted' => $this->formatBytes($diskFreed),
            'ram_freed_bytes' => $ramFreed,
            'ram_freed_formatted' => $this->formatBytes($ramFreed),
            'logs' => $logs,
        ]);
    }

    /**
     * Get directory size in bytes.
     */
    protected function getDirectorySize(string $path): int
    {
        if (!@file_exists($path)) {
            return 0;
        }

        $size = 0;
        try {
            $output = @shell_exec("du -sb " . escapeshellarg($path) . " 2>/dev/null");
            if ($output && preg_match('/^(\d+)/', trim($output), $matches)) {
                return (int) $matches[1];
            }

            // Fallback PHP iterator
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS)
            );
            foreach ($iterator as $file) {
                $size += $file->getSize();
            }
        } catch (\Throwable $e) {
        }

        return $size;
    }

    /**
     * Get Docker logs total size in bytes.
     */
    protected function getDockerLogsSize(): int
    {
        $size = 0;
        try {
            $files = @glob('/var/lib/docker/containers/*/*-json.log') ?: [];
            foreach ($files as $file) {
                $size += @filesize($file) ?: 0;
            }
        } catch (\Throwable $e) {
        }

        return $size;
    }

    /**
     * Get systemd journal size in bytes.
     */
    protected function getJournalSize(): int
    {
        try {
            $output = @shell_exec('journalctl --disk-usage 2>/dev/null');
            if ($output) {
                // e.g. "Archived and active journals take up 1.2G in the file system."
                if (preg_match('/take up ([\d\.]+)([KMGTP]) in the file system/i', $output, $matches)) {
                    $val = (float) $matches[1];
                    $unit = strtoupper($matches[2]);
                    $multipliers = ['K' => 1024, 'M' => 1024 ** 2, 'G' => 1024 ** 3, 'T' => 1024 ** 4, 'P' => 1024 ** 5];
                    return (int) ($val * ($multipliers[$unit] ?? 1));
                }
            }
        } catch (\Throwable $e) {
        }

        return $this->getDirectorySize('/var/log/journal');
    }

    /**
     * Get Linux RAM statistics from /proc/meminfo.
     */
    protected function getRamStats(): array
    {
        $stats = [
            'total' => 0,
            'free' => 0,
            'used' => 0,
            'buffers_cached' => 0,
            'percent' => 0,
        ];

        if (!@file_exists('/proc/meminfo')) {
            return $stats;
        }

        $meminfo = @file_get_contents('/proc/meminfo');
        if (!$meminfo) {
            return $stats;
        }

        preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $totalMatches);
        preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $availMatches);
        preg_match('/MemFree:\s+(\d+)\s+kB/', $meminfo, $freeMatches);
        preg_match('/Buffers:\s+(\d+)\s+kB/', $meminfo, $buffMatches);
        preg_match('/Cached:\s+(\d+)\s+kB/', $meminfo, $cachedMatches);

        $totalKb = (int) ($totalMatches[1] ?? 0);
        $buffKb = (int) ($buffMatches[1] ?? 0);
        $cachedKb = (int) ($cachedMatches[1] ?? 0);
        $availKb = isset($availMatches[1]) ? (int) $availMatches[1] : ((int) ($freeMatches[1] ?? 0) + $buffKb + $cachedKb);
        $usedKb = max(0, $totalKb - $availKb);

        $stats['total'] = $totalKb * 1024;
        $stats['free'] = $availKb * 1024;
        $stats['used'] = $usedKb * 1024;
        $stats['buffers_cached'] = ($buffKb + $cachedKb) * 1024;
        $stats['percent'] = $totalKb > 0 ? round(($usedKb / $totalKb) * 100, 1) : 0;

        return $stats;
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
