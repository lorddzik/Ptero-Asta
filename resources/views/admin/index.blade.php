@extends('layouts.admin')

@section('title')
    Overview
@endsection

@section('content-header')
    <div class="row">
        <div class="col-sm-7 col-xs-12">
            <h1 style="margin:0;font-weight:900;letter-spacing:-0.5px;">
                SYSTEM OVERVIEW
                <small style="font-weight:700;">Real-time Node & Infrastructure Monitor</small>
            </h1>
        </div>
        <div class="col-sm-5 col-xs-12 text-right">
            <div style="display:inline-flex;align-items:center;gap:8px;margin-top:5px;">
                <span class="label label-success" id="realtimeStatusBadge" style="font-size:11px;padding:5px 8px;border:1.5px solid #000;box-shadow:1.5px 1.5px 0 #000;">
                    <i class="fa fa-circle text-white animate-pulse"></i> LIVE
                </span>
                <button type="button" id="btnRefreshStats" class="btn btn-sm btn-default" style="border:2px solid #000;box-shadow:2px 2px 0 #000;font-weight:800;">
                    <i class="fa fa-refresh"></i> REFRESH
                </button>
                <div class="checkbox" style="margin:0;display:inline-block;">
                    <label style="font-weight:800;font-size:11px;text-transform:uppercase;cursor:pointer;">
                        <input type="checkbox" id="chkAutoRefresh" checked> Auto (5s)
                    </label>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
{{-- ROW 1: TOP STAT CARDS (NEO-BRUTALIST) --}}
<div class="row">
    {{-- SERVERS STAT CARD --}}
    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="box" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;overflow:hidden;margin-bottom:20px;">
            <div class="box-body" style="padding:15px;background:var(--neo-surface, #ffffff);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:1px;color:var(--neo-text-muted, #666);">TOTAL SERVERS</div>
                        <div style="font-size:32px;font-weight:900;line-height:1.2;color:var(--neo-text, #111);" id="statServersTotal">{{ $stats['servers_count'] }}</div>
                    </div>
                    <div style="width:48px;height:48px;border:2px solid #000;box-shadow:2px 2px 0 #000;background:#38bdf8;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#000;">
                        <i class="fa fa-server"></i>
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;gap:4px;flex-wrap:wrap;">
                    <span class="label label-success" style="border:1px solid #000;">{{ $stats['servers_active'] }} Active</span>
                    <span class="label label-warning" style="border:1px solid #000;">{{ $stats['servers_installing'] }} Installing</span>
                    @if($stats['servers_suspended'] > 0)
                        <span class="label label-danger" style="border:1px solid #000;">{{ $stats['servers_suspended'] }} Suspended</span>
                    @endif
                </div>
                <div style="margin-top:12px;padding-top:10px;border-top:1.5px solid rgba(0,0,0,0.1);">
                    <a href="{{ route('admin.servers') }}" style="font-size:11px;font-weight:800;text-transform:uppercase;color:var(--neo-text, #111);">Kelola Server &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    {{-- USERS STAT CARD --}}
    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="box" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;overflow:hidden;margin-bottom:20px;">
            <div class="box-body" style="padding:15px;background:var(--neo-surface, #ffffff);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:1px;color:var(--neo-text-muted, #666);">TOTAL USERS</div>
                        <div style="font-size:32px;font-weight:900;line-height:1.2;color:var(--neo-text, #111);" id="statUsersTotal">{{ $stats['users_count'] }}</div>
                    </div>
                    <div style="width:48px;height:48px;border:2px solid #000;box-shadow:2px 2px 0 #000;background:#a855f7;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#000;">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;gap:4px;flex-wrap:wrap;">
                    <span class="label label-primary" style="border:1px solid #000;">{{ $stats['users_admin'] }} Admins</span>
                    <span class="label label-info" style="border:1px solid #000;">{{ $stats['users_2fa'] }} 2FA Active</span>
                </div>
                <div style="margin-top:12px;padding-top:10px;border-top:1.5px solid rgba(0,0,0,0.1);">
                    <a href="{{ route('admin.users') }}" style="font-size:11px;font-weight:800;text-transform:uppercase;color:var(--neo-text, #111);">Kelola Pengguna &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    {{-- NODES STAT CARD --}}
    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="box" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;overflow:hidden;margin-bottom:20px;">
            <div class="box-body" style="padding:15px;background:var(--neo-surface, #ffffff);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:1px;color:var(--neo-text-muted, #666);">ACTIVE NODES</div>
                        <div style="font-size:32px;font-weight:900;line-height:1.2;color:var(--neo-text, #111);" id="statNodesTotal">{{ $stats['nodes_count'] }}</div>
                    </div>
                    <div style="width:48px;height:48px;border:2px solid #000;box-shadow:2px 2px 0 #000;background:#22c55e;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#000;">
                        <i class="fa fa-sitemap"></i>
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;gap:4px;flex-wrap:wrap;">
                    <span class="label label-success" id="statNodesLiveBadge" style="border:1px solid #000;">Checking...</span>
                    <span class="label label-default" style="border:1px solid #000;">{{ $stats['nodes_count'] }} Total</span>
                </div>
                <div style="margin-top:12px;padding-top:10px;border-top:1.5px solid rgba(0,0,0,0.1);">
                    <a href="{{ route('admin.nodes') }}" style="font-size:11px;font-weight:800;text-transform:uppercase;color:var(--neo-text, #111);">Kelola Node &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ALLOCATIONS STAT CARD --}}
    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="box" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;overflow:hidden;margin-bottom:20px;">
            <div class="box-body" style="padding:15px;background:var(--neo-surface, #ffffff);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:1px;color:var(--neo-text-muted, #666);">ALLOCATIONS / PORTS</div>
                        <div style="font-size:32px;font-weight:900;line-height:1.2;color:var(--neo-text, #111);">{{ $stats['allocations_assigned'] }} <small style="font-size:16px;color:var(--neo-text-muted);">/ {{ $stats['allocations_count'] }}</small></div>
                    </div>
                    <div style="width:48px;height:48px;border:2px solid #000;box-shadow:2px 2px 0 #000;background:#f59e0b;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#000;">
                        <i class="fa fa-plug"></i>
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;gap:4px;flex-wrap:wrap;">
                    <span class="label label-primary" style="border:1px solid #000;">{{ $stats['allocations_assigned'] }} Used</span>
                    <span class="label label-default" style="border:1px solid #000;">{{ $stats['allocations_free'] }} Free</span>
                </div>
                <div style="margin-top:12px;padding-top:10px;border-top:1.5px solid rgba(0,0,0,0.1);">
                    <a href="{{ route('admin.nodes') }}" style="font-size:11px;font-weight:800;text-transform:uppercase;color:var(--neo-text, #111);">Lihat Port Node &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 2: OVERALLOCATION & CAPACITY GAUGES --}}
<div class="row">
    <div class="col-md-6 col-xs-12">
        <div class="box" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;margin-bottom:20px;">
            <div class="box-header with-border" style="border-bottom:2px solid #000;">
                <h3 class="box-title" style="font-weight:900;font-size:14px;text-transform:uppercase;">
                    <i class="fa fa-microchip"></i> Total RAM Allocation vs Capacity
                </h3>
            </div>
            <div class="box-body" style="padding:20px;">
                <div style="display:flex;justify-content:space-between;font-weight:900;font-size:14px;margin-bottom:8px;">
                    <span>Teralokasi: {{ number_format($stats['total_server_memory']) }} MiB</span>
                    <span>Kapasitas: {{ number_format($stats['total_node_memory']) }} MiB ({{ $stats['overall_memory_percent'] }}%)</span>
                </div>
                <div class="progress" style="height:24px;border:2px solid #000;box-shadow:2px 2px 0 #000;border-radius:6px;background:#e5e7eb;overflow:hidden;margin-bottom:5px;">
                    <div class="progress-bar @if($stats['overall_memory_percent'] > 95) progress-bar-danger @elseif($stats['overall_memory_percent'] > 80) progress-bar-warning @else progress-bar-success @endif"
                         role="progressbar"
                         style="width: {{ min(100, $stats['overall_memory_percent']) }}%;font-weight:900;line-height:24px;color:#000;">
                        {{ $stats['overall_memory_percent'] }}%
                    </div>
                </div>
                <small class="text-muted" style="font-weight:700;">
                    @if($stats['overall_memory_percent'] > 100)
                        <span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Over-allocated! Server client dialokasikan melebihi RAM fisik node.</span>
                    @else
                        Kapasitas memori server masih dalam batas aman fisik.
                    @endif
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xs-12">
        <div class="box" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;margin-bottom:20px;">
            <div class="box-header with-border" style="border-bottom:2px solid #000;">
                <h3 class="box-title" style="font-weight:900;font-size:14px;text-transform:uppercase;">
                    <i class="fa fa-hdd-o"></i> Total Disk Allocation vs Capacity
                </h3>
            </div>
            <div class="box-body" style="padding:20px;">
                <div style="display:flex;justify-content:space-between;font-weight:900;font-size:14px;margin-bottom:8px;">
                    <span>Teralokasi: {{ number_format($stats['total_server_disk'] / 1024, 1) }} GiB</span>
                    <span>Kapasitas: {{ number_format($stats['total_node_disk'] / 1024, 1) }} GiB ({{ $stats['overall_disk_percent'] }}%)</span>
                </div>
                <div class="progress" style="height:24px;border:2px solid #000;box-shadow:2px 2px 0 #000;border-radius:6px;background:#e5e7eb;overflow:hidden;margin-bottom:5px;">
                    <div class="progress-bar @if($stats['overall_disk_percent'] > 95) progress-bar-danger @elseif($stats['overall_disk_percent'] > 80) progress-bar-warning @else progress-bar-info @endif"
                         role="progressbar"
                         style="width: {{ min(100, $stats['overall_disk_percent']) }}%;font-weight:900;line-height:24px;color:#000;">
                        {{ $stats['overall_disk_percent'] }}%
                    </div>
                </div>
                <small class="text-muted" style="font-weight:700;">
                    Total kapasitas storage yang dialokasikan ke semua server di semua node.
                </small>
            </div>
        </div>
    </div>
</div>

{{-- ROW 3: LOCAL HOST VPS REALTIME HARDWARE MONITOR --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;margin-bottom:20px;">
            <div class="box-header with-border" style="border-bottom:2px solid #000;display:flex;justify-content:space-between;align-items:center;">
                <h3 class="box-title" style="font-weight:900;font-size:15px;text-transform:uppercase;">
                    <i class="fa fa-dashboard"></i> Panel VPS Host Monitor (Real-time Kernel)
                </h3>
                <span class="label label-primary" style="border:1.5px solid #000;box-shadow:1.5px 1.5px 0 #000;font-weight:800;">HOST VPS</span>
            </div>
            <div class="box-body" style="padding:20px;">
                <div class="row text-center">
                    {{-- CPU GAUGE --}}
                    <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom:15px;">
                        <div style="border:2px solid #000;box-shadow:2px 2px 0 #000;border-radius:8px;padding:15px;background:var(--neo-surface-light, #f9fafb);">
                            <div style="font-size:11px;font-weight:900;text-transform:uppercase;color:var(--neo-text-muted);">CPU USAGE</div>
                            <div style="font-size:30px;font-weight:900;margin:5px 0;" id="hostCpuValue">{{ $hostMetrics['cpu_usage'] }}%</div>
                            <div class="progress" style="height:12px;border:1.5px solid #000;border-radius:4px;background:#e5e7eb;margin:0;">
                                <div id="hostCpuBar" class="progress-bar progress-bar-success" style="width:{{ min(100, $hostMetrics['cpu_usage']) }}%;"></div>
                            </div>
                            <div style="font-size:10px;font-weight:800;color:var(--neo-text-muted);margin-top:6px;" id="hostLoadAvg">
                                Load: {{ implode(', ', $hostMetrics['load_avg']) }}
                            </div>
                        </div>
                    </div>

                    {{-- RAM GAUGE --}}
                    <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom:15px;">
                        <div style="border:2px solid #000;box-shadow:2px 2px 0 #000;border-radius:8px;padding:15px;background:var(--neo-surface-light, #f9fafb);">
                            <div style="font-size:11px;font-weight:900;text-transform:uppercase;color:var(--neo-text-muted);">RAM PHYSICAL</div>
                            <div style="font-size:30px;font-weight:900;margin:5px 0;" id="hostRamValue">{{ $hostMetrics['memory']['percent'] }}%</div>
                            <div class="progress" style="height:12px;border:1.5px solid #000;border-radius:4px;background:#e5e7eb;margin:0;">
                                <div id="hostRamBar" class="progress-bar progress-bar-info" style="width:{{ min(100, $hostMetrics['memory']['percent']) }}%;"></div>
                            </div>
                            <div style="font-size:10px;font-weight:800;color:var(--neo-text-muted);margin-top:6px;" id="hostRamText">
                                {{ round($hostMetrics['memory']['used'] / (1024*1024*1024), 1) }} / {{ round($hostMetrics['memory']['total'] / (1024*1024*1024), 1) }} GB
                            </div>
                        </div>
                    </div>

                    {{-- DISK STORAGE GAUGE --}}
                    <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom:15px;">
                        <div style="border:2px solid #000;box-shadow:2px 2px 0 #000;border-radius:8px;padding:15px;background:var(--neo-surface-light, #f9fafb);">
                            <div style="font-size:11px;font-weight:900;text-transform:uppercase;color:var(--neo-text-muted);">DISK USAGE (/)</div>
                            <div style="font-size:30px;font-weight:900;margin:5px 0;" id="hostDiskValue">{{ $hostMetrics['disk']['percent'] }}%</div>
                            <div class="progress" style="height:12px;border:1.5px solid #000;border-radius:4px;background:#e5e7eb;margin:0;">
                                <div id="hostDiskBar" class="progress-bar progress-bar-warning" style="width:{{ min(100, $hostMetrics['disk']['percent']) }}%;"></div>
                            </div>
                            <div style="font-size:10px;font-weight:800;color:var(--neo-text-muted);margin-top:6px;" id="hostDiskText">
                                {{ round($hostMetrics['disk']['used'] / (1024*1024*1024), 1) }} / {{ round($hostMetrics['disk']['total'] / (1024*1024*1024), 1) }} GB
                            </div>
                        </div>
                    </div>

                    {{-- NETWORK I/O GAUGE --}}
                    <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom:15px;">
                        <div style="border:2px solid #000;box-shadow:2px 2px 0 #000;border-radius:8px;padding:15px;background:var(--neo-surface-light, #f9fafb);">
                            <div style="font-size:11px;font-weight:900;text-transform:uppercase;color:var(--neo-text-muted);">NETWORK I/O (TRAFFIC)</div>
                            <div style="font-size:20px;font-weight:900;margin:10px 0;" id="hostNetText">
                                <span class="text-green"><i class="fa fa-arrow-down"></i> {{ round($hostMetrics['network']['rx_bytes'] / (1024*1024), 1) }} MB</span>
                                <span class="text-blue" style="margin-left:8px;"><i class="fa fa-arrow-up"></i> {{ round($hostMetrics['network']['tx_bytes'] / (1024*1024), 1) }} MB</span>
                            </div>
                            <div style="font-size:10px;font-weight:800;color:var(--neo-text-muted);margin-top:6px;" id="hostUptimeText">
                                Uptime: {{ floor($hostMetrics['uptime'] / 86400) }} hari {{ floor(($hostMetrics['uptime'] % 86400) / 3600) }} jam
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 4: DETAILED NODE MONITORING CARDS --}}
<div class="row">
    <div class="col-xs-12">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h2 style="margin:0;font-size:18px;font-weight:900;text-transform:uppercase;letter-spacing:-0.5px;">
                <i class="fa fa-sitemap"></i> Nodes Health & Performance
            </h2>
            <a href="{{ route('admin.nodes.new') }}" class="btn btn-sm btn-primary" style="border:2px solid #000;box-shadow:2px 2px 0 #000;font-weight:800;">
                <i class="fa fa-plus"></i> ADD NODE
            </a>
        </div>
    </div>

    @foreach($nodeDetails as $node)
    <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="box node-monitor-card" id="nodeCard-{{ $node['id'] }}" data-ping-url="{{ $node['ping_url'] }}" data-secret="{{ $node['secret'] }}" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;margin-bottom:20px;">
            <div class="box-header with-border" style="border-bottom:2px solid #000;padding:12px 15px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h4 style="margin:0;font-weight:900;font-size:16px;">
                            <a href="{{ route('admin.nodes.view', $node['id']) }}" style="color:var(--neo-text, #111);">{{ $node['name'] }}</a>
                        </h4>
                        <small style="color:var(--neo-text-muted);font-family:monospace;font-weight:700;">{{ $node['fqdn'] }}</small>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="node-heartbeat-badge label label-default" style="border:1.5px solid #000;font-size:10px;font-weight:800;">
                            <i class="fa fa-refresh fa-spin"></i> CHECKING
                        </span>
                    </div>
                </div>
            </div>
            <div class="box-body" style="padding:15px;">
                {{-- BADGES ROW --}}
                <div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:12px;">
                    <span class="label label-primary" style="border:1px solid #000;">{{ $node['servers_count'] }} Servers</span>
                    <span class="label label-info" style="border:1px solid #000;">{{ $node['location'] }}</span>
                    @if($node['allow_http'])
                        <span class="label label-success" style="border:1px solid #000;" title="Akses HTTP diizinkan"><i class="fa fa-globe"></i> HTTP ON</span>
                    @else
                        <span class="label label-danger" style="border:1px solid #000;" title="Akses HTTP diblokir"><i class="fa fa-ban"></i> NON-HTTP</span>
                    @endif
                    @if($node['maintenance_mode'])
                        <span class="label label-warning" style="border:1px solid #000;"><i class="fa fa-wrench"></i> Maintenance</span>
                    @endif
                </div>

                {{-- RAM ALLOCATION BAR --}}
                <div style="margin-bottom:10px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:800;margin-bottom:3px;">
                        <span>RAM ALLOCATED</span>
                        <span id="nodeRamText-{{ $node['id'] }}">{{ number_format($node['allocated_memory']) }} / {{ number_format($node['memory_limit']) }} MiB ({{ $node['memory_percent'] }}%)</span>
                    </div>
                    <div class="progress" style="height:10px;border:1.5px solid #000;border-radius:4px;background:#e5e7eb;margin:0;">
                        <div id="nodeRamBar-{{ $node['id'] }}" class="progress-bar @if($node['memory_percent'] > 90) progress-bar-danger @elseif($node['memory_percent'] > 75) progress-bar-warning @else progress-bar-success @endif" style="width:{{ min(100, $node['memory_percent']) }}%;"></div>
                    </div>
                </div>

                {{-- DISK ALLOCATION BAR --}}
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:800;margin-bottom:3px;">
                        <span>DISK ALLOCATED</span>
                        <span id="nodeDiskText-{{ $node['id'] }}">{{ number_format($node['allocated_disk'] / 1024, 1) }} / {{ number_format($node['disk_limit'] / 1024, 1) }} GiB ({{ $node['disk_percent'] }}%)</span>
                    </div>
                    <div class="progress" style="height:10px;border:1.5px solid #000;border-radius:4px;background:#e5e7eb;margin:0;">
                        <div id="nodeDiskBar-{{ $node['id'] }}" class="progress-bar @if($node['disk_percent'] > 90) progress-bar-danger @elseif($node['disk_percent'] > 75) progress-bar-warning @else progress-bar-info @endif" style="width:{{ min(100, $node['disk_percent']) }}%;"></div>
                    </div>
                </div>

                {{-- DAEMON DETAILS FOOTER --}}
                <div style="padding-top:10px;border-top:1px solid rgba(0,0,0,0.1);display:flex;justify-content:space-between;font-size:11px;font-weight:800;">
                    <span class="node-version-text" style="color:var(--neo-text-muted);">Daemon: Wings</span>
                    <a href="{{ route('admin.nodes.view.settings', $node['id']) }}" style="color:var(--neo-text, #111);">Pengaturan &rarr;</a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ROW 5: QUICK ACTIONS BAR & SYSTEM INFO --}}
<div class="row">
    {{-- QUICK ACTIONS --}}
    <div class="col-md-7 col-xs-12">
        <div class="box" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;margin-bottom:20px;">
            <div class="box-header with-border" style="border-bottom:2px solid #000;">
                <h3 class="box-title" style="font-weight:900;font-size:14px;text-transform:uppercase;">
                    <i class="fa fa-bolt"></i> Quick Actions
                </h3>
            </div>
            <div class="box-body" style="padding:15px;display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.servers.new') }}" class="btn btn-primary" style="border:2px solid #000;box-shadow:2px 2px 0 #000;font-weight:800;">
                    <i class="fa fa-plus-circle"></i> Create Server
                </a>
                <a href="{{ route('admin.nodes.new') }}" class="btn btn-success" style="border:2px solid #000;box-shadow:2px 2px 0 #000;font-weight:800;">
                    <i class="fa fa-sitemap"></i> Create Node
                </a>
                <a href="{{ route('admin.users.new') }}" class="btn btn-info" style="border:2px solid #000;box-shadow:2px 2px 0 #000;font-weight:800;">
                    <i class="fa fa-user-plus"></i> Create User
                </a>
                <a href="{{ route('admin.databases') }}" class="btn btn-warning" style="border:2px solid #000;box-shadow:2px 2px 0 #000;font-weight:800;">
                    <i class="fa fa-database"></i> Databases
                </a>
                <a href="{{ route('admin.servers') }}" class="btn btn-default" style="border:2px solid #000;box-shadow:2px 2px 0 #000;font-weight:800;">
                    <i class="fa fa-list"></i> Server List
                </a>
            </div>
        </div>
    </div>

    {{-- SYSTEM VERSION & ENVIRONMENT --}}
    <div class="col-md-5 col-xs-12">
        <div class="box @if($version->isLatestPanel()) box-success @else box-danger @endif" style="border:2.5px solid #000;box-shadow:4px 4px 0 #000;border-radius:8px;margin-bottom:20px;">
            <div class="box-header with-border" style="border-bottom:2px solid #000;">
                <h3 class="box-title" style="font-weight:900;font-size:14px;text-transform:uppercase;">
                    <i class="fa fa-info-circle"></i> System Environment
                </h3>
            </div>
            <div class="box-body" style="padding:15px;font-size:12px;font-weight:700;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span>Asta Panel Version</span>
                    <code>{{ config('app.version') }}</code>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span>PHP Runtime</span>
                    <code>{{ phpversion() }}</code>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span>Update Status</span>
                    @if ($version->isLatestPanel())
                        <span class="label label-success" style="border:1px solid #000;">UP TO DATE</span>
                    @else
                        <span class="label label-danger" style="border:1px solid #000;">UPDATE AVAILABLE</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    (function () {
        var autoRefreshTimer = null;

        // Function to poll local host stats
        function fetchSystemStats() {
            $.ajax({
                type: 'GET',
                url: '{{ route('admin.system-stats') }}',
                timeout: 4000
            }).done(function (res) {
                if (res.status === 'success' && res.host) {
                    var h = res.host;
                    // Update CPU
                    $('#hostCpuValue').text(h.cpu_usage + '%');
                    $('#hostCpuBar').css('width', Math.min(100, h.cpu_usage) + '%');
                    if (h.load_avg) {
                        $('#hostLoadAvg').text('Load: ' + h.load_avg.join(', '));
                    }

                    // Update RAM
                    if (h.memory) {
                        $('#hostRamValue').text(h.memory.percent + '%');
                        $('#hostRamBar').css('width', Math.min(100, h.memory.percent) + '%');
                        var usedGb = (h.memory.used / (1024*1024*1024)).toFixed(1);
                        var totalGb = (h.memory.total / (1024*1024*1024)).toFixed(1);
                        $('#hostRamText').text(usedGb + ' / ' + totalGb + ' GB');
                    }

                    // Update Disk
                    if (h.disk) {
                        $('#hostDiskValue').text(h.disk.percent + '%');
                        $('#hostDiskBar').css('width', Math.min(100, h.disk.percent) + '%');
                    }

                    // Update Network
                    if (h.network) {
                        var rxMb = (h.network.rx_bytes / (1024*1024)).toFixed(1);
                        var txMb = (h.network.tx_bytes / (1024*1024)).toFixed(1);
                        $('#hostNetText').html('<span class="text-green"><i class="fa fa-arrow-down"></i> ' + rxMb + ' MB</span> <span class="text-blue" style="margin-left:8px;"><i class="fa fa-arrow-up"></i> ' + txMb + ' MB</span>');
                    }

                    // Update nodes allocated memory from database
                    if (res.nodes && res.nodes.length) {
                        res.nodes.forEach(function (n) {
                            $('#nodeRamText-' + n.id).text(n.allocated_memory.toLocaleString() + ' / ' + n.memory_limit.toLocaleString() + ' MiB (' + n.memory_percent + '%)');
                            $('#nodeRamBar-' + n.id).css('width', Math.min(100, n.memory_percent) + '%');
                        });
                    }
                }
            });
        }

        // Function to ping each node's Wings daemon
        function pingAllNodes() {
            var liveCount = 0;
            var totalCount = $('.node-monitor-card').length;

            $('.node-monitor-card').each(function (i, el) {
                var pingUrl = $(el).data('ping-url');
                var secret = $(el).data('secret');
                var badge = $(el).find('.node-heartbeat-badge');
                var versionText = $(el).find('.node-version-text');

                $.ajax({
                    type: 'GET',
                    url: pingUrl,
                    headers: { 'Authorization': 'Bearer ' + secret },
                    timeout: 4000
                }).done(function (data) {
                    liveCount++;
                    badge.removeClass('label-default label-danger').addClass('label-success').html('<i class="fa fa-circle"></i> ONLINE');
                    if (data && data.version) {
                        versionText.text('Wings v' + data.version + ' (' + (data.cpu_count || '?') + ' CPU)');
                    }
                    updateNodesLiveBadge(liveCount, totalCount);
                }).fail(function () {
                    badge.removeClass('label-default label-success').addClass('label-danger').html('<i class="fa fa-times-circle"></i> OFFLINE');
                    updateNodesLiveBadge(liveCount, totalCount);
                });
            });
        }

        function updateNodesLiveBadge(live, total) {
            if (live === total) {
                $('#statNodesLiveBadge').removeClass('label-default label-warning label-danger').addClass('label-success').text(live + '/' + total + ' Online');
            } else if (live > 0) {
                $('#statNodesLiveBadge').removeClass('label-default label-success label-danger').addClass('label-warning').text(live + '/' + total + ' Online');
            } else {
                $('#statNodesLiveBadge').removeClass('label-default label-success label-warning').addClass('label-danger').text('0/' + total + ' Online');
            }
        }

        function runAllUpdates() {
            fetchSystemStats();
            pingAllNodes();
        }

        // Initial run
        runAllUpdates();

        // Setup timer
        function resetAutoRefresh() {
            if (autoRefreshTimer) clearInterval(autoRefreshTimer);
            if ($('#chkAutoRefresh').is(':checked')) {
                autoRefreshTimer = setInterval(runAllUpdates, 5000);
            }
        }
        resetAutoRefresh();

        $('#chkAutoRefresh').on('change', function () {
            resetAutoRefresh();
        });

        $('#btnRefreshStats').on('click', function () {
            $(this).find('i').addClass('fa-spin');
            runAllUpdates();
            setTimeout(function () {
                $('#btnRefreshStats i').removeClass('fa-spin');
            }, 800);
        });
    })();
    </script>
@endsection
