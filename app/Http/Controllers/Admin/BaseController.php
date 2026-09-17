<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Helpers\SoftwareVersionService;

class BaseController extends Controller
{
    /**
     * BaseController constructor.
     */
    public function __construct(private SoftwareVersionService $version)
    {
    }

    /**
     * Return the admin index view with comprehensive metrics.
     */
    public function index(): View
    {
        $serversCount = Server::query()->count();
        $serversSuspended = Server::query()->where('status', Server::STATUS_SUSPENDED)->count();
        $serversInstalling = Server::query()->whereIn('status', [
            Server::STATUS_INSTALLING,
            Server::STATUS_INSTALL_FAILED,
            Server::STATUS_REINSTALL_FAILED,
        ])->count();
        $serversActive = max(0, $serversCount - $serversSuspended - $serversInstalling);

        $usersCount = User::query()->count();
        $usersAdmin = User::query()->where('root_admin', 1)->count();
        $users2FA = User::query()->where('use_totp', 1)->count();

        $nodes = Node::query()->withCount('servers')->with('location')->get();
        $nodesCount = $nodes->count();

        $allocationsCount = Allocation::query()->count();
        $allocationsAssigned = Allocation::query()->whereNotNull('server_id')->count();

        $totalServerMemory = (int) Server::query()->sum('memory');
        $totalServerDisk = (int) Server::query()->sum('disk');
        $totalNodeMemory = (int) $nodes->sum('memory');
        $totalNodeDisk = (int) $nodes->sum('disk');

        $nodeDetails = $nodes->map(function (Node $node) {
            $allocatedMemory = (int) Server::query()->where('node_id', $node->id)->sum('memory');
            $allocatedDisk = (int) Server::query()->where('node_id', $node->id)->sum('disk');

            return [
                'id' => $node->id,
                'name' => $node->name,
                'fqdn' => $node->fqdn,
                'scheme' => $node->scheme,
                'daemonListen' => $node->daemonListen,
                'location' => $node->location ? $node->location->short : 'N/A',
                'servers_count' => $node->servers_count,
                'memory_limit' => $node->memory,
                'disk_limit' => $node->disk,
                'allocated_memory' => $allocatedMemory,
                'allocated_disk' => $allocatedDisk,
                'memory_percent' => $node->memory > 0 ? round(($allocatedMemory / $node->memory) * 100, 1) : 0,
                'disk_percent' => $node->disk > 0 ? round(($allocatedDisk / $node->disk) * 100, 1) : 0,
                'allow_http' => $node->allow_http ?? true,
                'public' => $node->public,
                'maintenance_mode' => $node->maintenance_mode,
                'ping_url' => "{$node->scheme}://{$node->fqdn}:{$node->daemonListen}/api/system",
                'secret' => $node->getDecryptedKey(),
            ];
        });

        $hostMetrics = $this->getHostMetrics();

        return view('admin.index', [
            'version' => $this->version,
            'stats' => [
                'servers_count' => $serversCount,
                'servers_active' => $serversActive,
                'servers_suspended' => $serversSuspended,
                'servers_installing' => $serversInstalling,
                'users_count' => $usersCount,
                'users_admin' => $usersAdmin,
                'users_2fa' => $users2FA,
                'nodes_count' => $nodesCount,
                'allocations_count' => $allocationsCount,
                'allocations_assigned' => $allocationsAssigned,
                'allocations_free' => max(0, $allocationsCount - $allocationsAssigned),
                'total_server_memory' => $totalServerMemory,
                'total_server_disk' => $totalServerDisk,
                'total_node_memory' => $totalNodeMemory,
                'total_node_disk' => $totalNodeDisk,
                'overall_memory_percent' => $totalNodeMemory > 0 ? round(($totalServerMemory / $totalNodeMemory) * 100, 1) : 0,
                'overall_disk_percent' => $totalNodeDisk > 0 ? round(($totalServerDisk / $totalNodeDisk) * 100, 1) : 0,
            ],
            'nodeDetails' => $nodeDetails,
            'hostMetrics' => $hostMetrics,
        ]);
    }

    /**
     * Return live host and node metrics in JSON format for real-time polling.
     */
    public function systemStats(): JsonResponse
    {
        $nodes = Node::query()->select('id', 'name', 'fqdn', 'scheme', 'daemonListen', 'memory', 'disk')->withCount('servers')->get();

        $nodesData = $nodes->map(function (Node $node) {
            $allocatedMemory = (int) Server::query()->where('node_id', $node->id)->sum('memory');
            $allocatedDisk = (int) Server::query()->where('node_id', $node->id)->sum('disk');

            return [
                'id' => $node->id,
                'name' => $node->name,
                'servers_count' => $node->servers_count,
                'allocated_memory' => $allocatedMemory,
                'allocated_disk' => $allocatedDisk,
                'memory_limit' => $node->memory,
                'disk_limit' => $node->disk,
                'memory_percent' => $node->memory > 0 ? round(($allocatedMemory / $node->memory) * 100, 1) : 0,
                'disk_percent' => $node->disk > 0 ? round(($allocatedDisk / $node->disk) * 100, 1) : 0,
            ];
        });

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->toIso8601String(),
            'host' => $this->getHostMetrics(),
            'nodes' => $nodesData,
        ]);
    }

    /**
     * Extract real-time host hardware metrics from Linux kernel interfaces.
     */
    private function getHostMetrics(): array
    {
        $metrics = [
            'cpu_usage' => 0,
            'load_avg' => sys_getloadavg() ?: [0, 0, 0],
            'memory' => ['total' => 0, 'used' => 0, 'free' => 0, 'percent' => 0],
            'disk' => ['total' => 0, 'used' => 0, 'free' => 0, 'percent' => 0],
            'network' => ['rx_bytes' => 0, 'tx_bytes' => 0],
            'uptime' => 0,
        ];

        // Disk metrics
        try {
            $totalDisk = @disk_total_space('/') ?: 0;
            $freeDisk = @disk_free_space('/') ?: 0;
            $usedDisk = max(0, $totalDisk - $freeDisk);
            $diskPercent = $totalDisk > 0 ? round(($usedDisk / $totalDisk) * 100, 1) : 0;
            $metrics['disk'] = [
                'total' => $totalDisk,
                'used' => $usedDisk,
                'free' => $freeDisk,
                'percent' => $diskPercent,
            ];
        } catch (\Throwable $e) {
        }

        // Memory metrics from /proc/meminfo
        if (@file_exists('/proc/meminfo')) {
            $meminfo = @file_get_contents('/proc/meminfo');
            if ($meminfo) {
                preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $totalMatches);
                preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $availMatches);
                if (empty($availMatches)) {
                    preg_match('/MemFree:\s+(\d+)\s+kB/', $meminfo, $freeMatches);
                    preg_match('/Buffers:\s+(\d+)\s+kB/', $meminfo, $buffMatches);
                    preg_match('/Cached:\s+(\d+)\s+kB/', $meminfo, $cachedMatches);
                    $freeKb = ($freeMatches[1] ?? 0) + ($buffMatches[1] ?? 0) + ($cachedMatches[1] ?? 0);
                } else {
                    $freeKb = $availMatches[1] ?? 0;
                }

                $totalKb = $totalMatches[1] ?? 0;
                $usedKb = max(0, $totalKb - $freeKb);
                $memPercent = $totalKb > 0 ? round(($usedKb / $totalKb) * 100, 1) : 0;

                $metrics['memory'] = [
                    'total' => $totalKb * 1024,
                    'used' => $usedKb * 1024,
                    'free' => $freeKb * 1024,
                    'percent' => $memPercent,
                ];
            }
        }

        // CPU Usage % from /proc/stat
        if (@file_exists('/proc/stat')) {
            $stat = @file('/proc/stat');
            if (!empty($stat[0])) {
                $cpu = explode(' ', preg_replace('/\s+/', ' ', trim($stat[0])));
                if (count($cpu) >= 5) {
                    $idle = (int) $cpu[4];
                    $total = array_sum(array_slice($cpu, 1));

                    $last = cache()->get('system:cpu:last');
                    if ($last && is_array($last)) {
                        $diffTotal = $total - $last['total'];
                        $diffIdle = $idle - $last['idle'];
                        if ($diffTotal > 0) {
                            $metrics['cpu_usage'] = round((($diffTotal - $diffIdle) / $diffTotal) * 100, 1);
                        }
                    }
                    cache()->put('system:cpu:last', ['total' => $total, 'idle' => $idle], now()->addMinutes(2));
                }
            }
        }

        // Network I/O from /proc/net/dev
        if (@file_exists('/proc/net/dev')) {
            $netLines = @file('/proc/net/dev');
            if ($netLines) {
                $rxTotal = 0;
                $txTotal = 0;
                foreach ($netLines as $line) {
                    if (str_contains($line, ':') && !str_contains($line, 'lo:')) {
                        $parts = preg_split('/\s+/', trim(explode(':', $line)[1]));
                        if (count($parts) >= 9) {
                            $rxTotal += (float) $parts[0];
                            $txTotal += (float) $parts[8];
                        }
                    }
                }
                $metrics['network'] = [
                    'rx_bytes' => $rxTotal,
                    'tx_bytes' => $txTotal,
                ];
            }
        }

        // Uptime from /proc/uptime
        if (@file_exists('/proc/uptime')) {
            $uptimeContent = @file_get_contents('/proc/uptime');
            if ($uptimeContent) {
                $metrics['uptime'] = (int) explode(' ', trim($uptimeContent))[0];
            }
        }

        return $metrics;
    }
}
