@extends('layouts.admin')

@section('title')
    VPS Optimizer & Factory Reset
@endsection

@section('content-header')
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="margin:0;font-weight:800;color:var(--neo-text,#FFFFFF);text-shadow:2px 2px 0px #000000;">
                <i class="fa fa-magic text-aqua"></i> VPS OPTIMIZER & FACTORY RESET
                <small style="color:var(--neo-text-muted,#94A3B8);font-weight:600;font-size:13px;display:block;margin-top:4px;">
                    Deep Cache Purge, RAM Optimization, & 1-Click Fresh Install Reset
                </small>
            </h1>
        </div>
        <ol class="breadcrumb" style="background:var(--neo-surface,#161B22);border:2px solid #000000;box-shadow:2px 2px 0px #000000;padding:8px 14px;border-radius:6px;margin:0;">
            <li><a href="{{ route('admin.index') }}" style="color:var(--asta-cyan,#00D2FF);font-weight:700;"><i class="fa fa-dashboard"></i> Admin</a></li>
            <li class="active" style="color:var(--neo-text,#FFFFFF);font-weight:700;">VPS Optimizer</li>
        </ol>
    </div>
@endsection

@section('content')
<style>
.opt-card {
    background: var(--neo-surface, #161B22);
    border: 2.5px solid #000000;
    box-shadow: 4px 4px 0px #000000;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 24px;
    position: relative;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.opt-card-danger {
    background: #1c1417;
    border: 2.5px solid #EF4444;
    box-shadow: 4px 4px 0px #000000;
}
.opt-title {
    font-size: 17px;
    font-weight: 800;
    margin-top: 0;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--neo-text, #FFFFFF);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.opt-btn {
    border: 2.5px solid #000000;
    box-shadow: 3px 3px 0px #000000;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 6px;
    padding: 10px 20px;
    cursor: pointer;
    transition: all 0.1s ease;
}
.opt-btn:hover {
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px #000000;
}
.opt-btn:active {
    transform: translate(2px, 2px);
    box-shadow: 1px 1px 0px #000000;
}
.opt-btn-cyan {
    background: var(--asta-cyan, #00D2FF);
    color: #000000;
}
.opt-btn-danger {
    background: #EF4444;
    color: #FFFFFF;
}
.opt-btn-danger:disabled {
    background: #4B5563;
    color: #9CA3AF;
    cursor: not-allowed;
    transform: none;
    box-shadow: 2px 2px 0px #000000;
}
.metric-pill {
    background: var(--neo-bg, #0D1117);
    border: 2px solid #000000;
    box-shadow: 2px 2px 0px #000000;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 12px;
}
.terminal-box {
    background: #0B0E14;
    border: 2.5px solid #000000;
    box-shadow: 4px 4px 0px #000000;
    border-radius: 6px;
    padding: 16px;
    font-family: 'Consolas', 'Courier New', monospace;
    font-size: 12px;
    color: #10B981;
    min-height: 160px;
    max-height: 320px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
}
.waste-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: var(--neo-bg, #0D1117);
    border: 2px solid #000000;
    border-radius: 6px;
    margin-bottom: 8px;
}
.waste-badge {
    display: inline-block;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 800;
    border-radius: 4px;
    border: 1.5px solid #000000;
    box-shadow: 1.5px 1.5px 0px #000000;
}
</style>

<!-- Top Hardware & Data Summary -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="metric-pill">
            <div style="font-size:11px;font-weight:800;color:var(--neo-text-muted,#94A3B8);text-transform:uppercase;">DISK STORAGE (HOST)</div>
            <div style="font-size:20px;font-weight:900;color:var(--neo-text,#FFFFFF);margin-top:2px;">
                {{ round(($diskTotal - $diskFree) / (1024**3), 1) }} / {{ round($diskTotal / (1024**3), 1) }} GB
            </div>
            <div class="progress" style="height:8px;background:#21262D;border:1.5px solid #000000;margin:6px 0 0 0;border-radius:4px;">
                <div class="progress-bar" style="width: {{ $diskPercent }}%; background: {{ $diskPercent > 85 ? '#EF4444' : '#00D2FF' }};"></div>
            </div>
            <div style="font-size:11px;color:var(--neo-text-muted,#94A3B8);margin-top:4px;">Tersedia: {{ round($diskFree / (1024**3), 1) }} GB ({{ 100 - $diskPercent }}%)</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="metric-pill">
            <div style="font-size:11px;font-weight:800;color:var(--neo-text-muted,#94A3B8);text-transform:uppercase;">RAM USAGE & BUFFER</div>
            <div style="font-size:20px;font-weight:900;color:var(--neo-text,#FFFFFF);margin-top:2px;">
                {{ round(($ramStats['used'] ?? 0) / (1024**3), 1) }} / {{ round(($ramStats['total'] ?? 0) / (1024**3), 1) }} GB
            </div>
            <div class="progress" style="height:8px;background:#21262D;border:1.5px solid #000000;margin:6px 0 0 0;border-radius:4px;">
                <div class="progress-bar" style="width: {{ $ramStats['percent'] ?? 0 }}%; background: {{ ($ramStats['percent'] ?? 0) > 85 ? '#EF4444' : '#10B981' }};"></div>
            </div>
            <div style="font-size:11px;color:var(--neo-text-muted,#94A3B8);margin-top:4px;">Buffer/Cache: {{ round(($ramStats['buffers_cached'] ?? 0) / (1024**2), 0) }} MB</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="metric-pill">
            <div style="font-size:11px;font-weight:800;color:var(--neo-text-muted,#94A3B8);text-transform:uppercase;">ACTIVE SERVERS</div>
            <div style="font-size:20px;font-weight:900;color:#FACC15;margin-top:2px;">
                {{ $totalServers }} Server
            </div>
            <div style="font-size:11px;color:var(--neo-text-muted,#94A3B8);margin-top:8px;">
                Alokasi Port Terpakai: <strong>{{ $usedAllocations }} / {{ $totalAllocations }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="metric-pill">
            <div style="font-size:11px;font-weight:800;color:var(--neo-text-muted,#94A3B8);text-transform:uppercase;">CLIENT USERS</div>
            <div style="font-size:20px;font-weight:900;color:#A855F7;margin-top:2px;">
                {{ $totalUsers }} User
            </div>
            <div style="font-size:11px;color:var(--neo-text-muted,#94A3B8);margin-top:8px;">
                Root Admins (Dilindungi): <strong>{{ $totalRootAdmins }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Scanner & Mode 1 (Safe Clean) -->
    <div class="col-md-6 col-xs-12">
        <!-- Live System Waste Scanner -->
        <div class="opt-card">
            <div class="opt-title">
                <i class="fa fa-search text-aqua"></i> 1. ANALISIS SAMPAH & CACHE
                <button type="button" id="btnScan" class="opt-btn opt-btn-cyan pull-right" style="padding:4px 12px;font-size:12px;">
                    <i class="fa fa-refresh"></i> SCAN SEKARANG
                </button>
            </div>
            <p style="color:var(--neo-text-muted,#94A3B8);font-size:12px;margin-bottom:16px;">
                Periksa ukuran file log Docker yang membengkak, systemd journal, cache APT, dan PageCache RAM yang membuat VPS terasa berat.
            </p>

            <div id="wasteList">
                <div class="waste-item">
                    <div>
                        <strong style="color:#FFFFFF;font-size:13px;"><i class="fa fa-file-text-o text-yellow"></i> Docker Container Logs</strong>
                        <div style="font-size:11px;color:#94A3B8;">File log output container (*-json.log)</div>
                    </div>
                    <span class="waste-badge" id="badge-docker-logs" style="background:#FACC15;color:#000000;">Memuat...</span>
                </div>
                <div class="waste-item">
                    <div>
                        <strong style="color:#FFFFFF;font-size:13px;"><i class="fa fa-book text-aqua"></i> Linux Systemd Journal Logs</strong>
                        <div style="font-size:11px;color:#94A3B8;">Log sistem operasi Linux di /var/log/journal</div>
                    </div>
                    <span class="waste-badge" id="badge-journal" style="background:#00D2FF;color:#000000;">Memuat...</span>
                </div>
                <div class="waste-item">
                    <div>
                        <strong style="color:#FFFFFF;font-size:13px;"><i class="fa fa-archive text-purple"></i> APT Package Archive Cache</strong>
                        <div style="font-size:11px;color:#94A3B8;">Arsip file paket .deb bekas instalasi</div>
                    </div>
                    <span class="waste-badge" id="badge-apt" style="background:#A855F7;color:#FFFFFF;">Memuat...</span>
                </div>
                <div class="waste-item">
                    <div>
                        <strong style="color:#FFFFFF;font-size:13px;"><i class="fa fa-cubes text-green"></i> Panel Cache & Framework Logs</strong>
                        <div style="font-size:11px;color:#94A3B8;">Cache Blade, routes, configs, dan laravel-*.log lama</div>
                    </div>
                    <span class="waste-badge" id="badge-panel" style="background:#10B981;color:#FFFFFF;">Memuat...</span>
                </div>
                <div class="waste-item">
                    <div>
                        <strong style="color:#FFFFFF;font-size:13px;"><i class="fa fa-dashboard text-danger"></i> RAM PageCache & Kernel Buffers</strong>
                        <div style="font-size:11px;color:#94A3B8;">Memori RAM yang tersandera cache I/O filesystem</div>
                    </div>
                    <span class="waste-badge" id="badge-ram" style="background:#EF4444;color:#FFFFFF;">Memuat...</span>
                </div>
            </div>

            <div style="margin-top:14px;background:#0D1117;border:2px solid #000000;border-radius:6px;padding:12px;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-weight:800;color:var(--neo-text,#FFFFFF);">TOTAL POTENSI SAMPAH:</span>
                <span id="totalJunkFormatted" style="font-weight:900;font-size:18px;color:var(--asta-cyan,#00D2FF);text-shadow:1.5px 1.5px 0px #000000;">0 MB</span>
            </div>
        </div>

        <!-- Mode 1: Safe Cache Purge -->
        <div class="opt-card">
            <div class="opt-title">
                <i class="fa fa-bolt text-yellow"></i> MODE 1: SAFE CACHE PURGE (ZERO RISK)
            </div>
            <p style="color:var(--neo-text-muted,#94A3B8);font-size:12px;margin-bottom:14px;">
                Membersihkan seluruh cache dan log tanpa menyentuh server client atau data pengguna. Cocok untuk perawatan rutin tanpa downtime.
            </p>

            <ul style="color:var(--neo-text,#FFFFFF);font-size:12px;padding-left:20px;line-height:1.8;margin-bottom:18px;">
                <li><i class="fa fa-check text-green"></i> Truncate file log container Docker ke 0 byte (tanpa stop container).</li>
                <li><i class="fa fa-check text-green"></i> Prune dangling Docker images & builder cache sisa build server.</li>
                <li><i class="fa fa-check text-green"></i> Vacuum systemd journal log ke batas aman (50 MB).</li>
                <li><i class="fa fa-check text-green"></i> Hapus cache paket APT dan temporary files usang.</li>
                <li><i class="fa fa-check text-green"></i> <strong>Flush PageCache RAM Linux (drop_caches)</strong> untuk membebaskan RAM secara instan.</li>
                <li><i class="fa fa-check text-green"></i> Segarkan cache framework Pterodactyl.</li>
            </ul>

            <button type="button" id="btnRunPurge" class="opt-btn opt-btn-cyan" style="width:100%;font-size:14px;padding:12px;">
                <i class="fa fa-flash"></i> JALANKAN SAFE CACHE PURGE (BEBASKAN STORAGE & RAM)
            </button>
        </div>
    </div>

    <!-- Right Column: Mode 2 (Factory Reset) & Wings External Node Helper -->
    <div class="col-md-6 col-xs-12">
        <!-- Mode 2: Factory Reset VPS (Fresh State) -->
        <div class="opt-card opt-card-danger">
            <div class="opt-title" style="color:#EF4444;">
                <i class="fa fa-exclamation-triangle"></i> MODE 2: FACTORY RESET (FRESH INSTALL STATE)
            </div>
            <p style="color:#F87171;font-size:12px;font-weight:700;margin-bottom:12px;">
                MENGEMBALIKAN PANEL KE KONDISI BARU SELESAI DI-INSTALL. SEMUA SERVER CLIENT & USER AKAN DIHAPUS TOTAL!
            </p>

            <div style="background:#000000;border:2px solid #EF4444;border-radius:6px;padding:12px;margin-bottom:16px;font-size:12px;">
                <div style="color:#EF4444;font-weight:800;margin-bottom:6px;">DATA YANG DIHAPUS TOTAL:</div>
                <div style="color:#FCA5A5;line-height:1.6;">
                    • Seluruh <strong>{{ $totalServers }} Server Client</strong> (database, container, dan file disk di /var/lib/pterodactyl/volumes/*)<br>
                    • Seluruh <strong>{{ $totalUsers }} User Client</strong> (non-admin)<br>
                    • Seluruh container Docker yang tersisa & volume Docker yang menggantung
                </div>

                <div style="color:#10B981;font-weight:800;margin-top:10px;margin-bottom:6px;">DATA YANG DIPERTAHANKAN (TIDAK HILANG):</div>
                <div style="color:#A7F3D0;line-height:1.6;">
                    • Source code Panel Pterodactyl, Nginx, SSL Cert, dan PHP<br>
                    • Akun <strong>Root Administrator</strong> (login kamu tetap aktif)<br>
                    • Konfigurasi Nodes, Database Host, Alokasi Port, dan Nests / Eggs
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="color:#FFFFFF;font-size:12px;font-weight:700;display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" id="checkConfirm1" style="transform:scale(1.2);">
                    Saya paham seluruh server client dan file di disk akan dihapus permanen.
                </label>
                <label style="color:#FFFFFF;font-size:12px;font-weight:700;display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:6px;">
                    <input type="checkbox" id="checkConfirm2" style="transform:scale(1.2);">
                    Saya ingin mengembalikan panel & VPS ke kondisi fresh install seperti baru.
                </label>
            </div>

            <div style="margin-bottom:16px;">
                <label style="color:#EF4444;font-size:12px;font-weight:800;text-transform:uppercase;">
                    Ketik teks <span style="background:#000000;padding:2px 8px;border:1px solid #EF4444;border-radius:4px;user-select:all;">RESET-ASTA</span> untuk membuka tombol:
                </label>
                <input type="text" id="inputConfirmText" class="form-control" placeholder="Ketik RESET-ASTA di sini..." style="background:#000000;border:2px solid #EF4444;color:#FFFFFF;font-weight:800;font-size:14px;box-shadow:2px 2px 0px #000000;">
            </div>

            <button type="button" id="btnRunReset" class="opt-btn opt-btn-danger" style="width:100%;font-size:14px;padding:12px;" disabled>
                <i class="fa fa-trash"></i> WIPE SEMUA SERVER & RESET KE FRESH INSTALL
            </button>
        </div>

        <!-- External Wings Node Cleaner Script -->
        <div class="opt-card">
            <div class="opt-title">
                <i class="fa fa-server text-green"></i> NODE WINGS LUAR (VPS TAMBAHAN)
            </div>
            <p style="color:var(--neo-text-muted,#94A3B8);font-size:12px;margin-bottom:12px;">
                Jika kamu memiliki Node Wings di VPS terpisah: saat Factory Reset dijalankan di panel, <strong>seluruh data server di node luar otomatis terhapus</strong> lewat Wings API.
            </p>
            <p style="color:var(--neo-text-muted,#94A3B8);font-size:12px;margin-bottom:10px;">
                Untuk membersihkan sisa log Docker host dan RAM di VPS Node luar, jalankan 1 baris perintah ini di terminal SSH node:
            </p>

            <div style="position:relative;">
                <textarea id="nodeScript" readonly class="form-control" rows="3" style="background:#0B0E14;border:2px solid #000000;color:#00D2FF;font-family:monospace;font-size:11px;resize:none;box-shadow:2px 2px 0px #000000;">sudo bash -c 'truncate -s 0 /var/lib/docker/containers/*/*-json.log 2>/dev/null; journalctl --vacuum-size=50M 2>/dev/null; apt-get clean 2>/dev/null; docker image prune -f 2>/dev/null; sync; echo 3 > /proc/sys/vm/drop_caches; echo "✓ Node Wings Cache & RAM Cleaned!"'</textarea>
                <button type="button" class="opt-btn opt-btn-cyan" onclick="
                    var copyText = document.getElementById('nodeScript');
                    copyText.select();
                    document.execCommand('copy');
                    swal('Tersalin!', 'Perintah pembersih Node Wings berhasil disalin ke clipboard.', 'success');
                " style="position:absolute;top:8px;right:8px;padding:4px 10px;font-size:11px;">
                    <i class="fa fa-copy"></i> SALIN
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Console Log Output -->
<div class="row">
    <div class="col-xs-12">
        <div class="opt-card">
            <div class="opt-title">
                <i class="fa fa-terminal text-green"></i> TERMINAL EKSEKUSI REAL-TIME
                <span id="execSpinner" style="display:none;margin-left:10px;font-size:13px;color:#00D2FF;">
                    <i class="fa fa-circle-o-notch fa-spin"></i> Memproses pembersihan...
                </span>
            </div>
            <div id="terminalOutput" class="terminal-box">Menunggu perintah eksekusi... Klik salah satu tombol di atas untuk memulai.</div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
@parent
<script>
$(document).ready(function () {
    const csrfToken = $('meta[name="_token"]').attr('content');

    // Load Scan on page load
    loadScan();

    $('#btnScan').on('click', function () {
        loadScan();
    });

    function loadScan() {
        $('#btnScan').prop('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> SCANNING...');
        
        $.ajax({
            url: "{{ route('admin.cleaner.scan') }}",
            method: 'GET',
            success: function (res) {
                $('#btnScan').prop('disabled', false).html('<i class="fa fa-refresh"></i> SCAN SEKARANG');
                if (res.success && res.categories) {
                    $('#badge-docker-logs').text(res.categories.docker_logs.formatted);
                    $('#badge-journal').text(res.categories.journal.formatted);
                    $('#badge-apt').text(res.categories.apt_cache.formatted);
                    $('#badge-panel').text(res.categories.panel_cache.formatted);
                    $('#badge-ram').text(res.categories.ram_cache.formatted);
                    $('#totalJunkFormatted').text(res.total_junk_formatted);
                }
            },
            error: function () {
                $('#btnScan').prop('disabled', false).html('<i class="fa fa-refresh"></i> SCAN SEKARANG');
            }
        });
    }

    // Mode 1: Safe Cache Purge
    $('#btnRunPurge').on('click', function () {
        swal({
            title: 'Jalankan Safe Cache Purge?',
            text: 'Proses ini akan memotong file log Docker, membersihkan cache APT, vacuum journal, dan membebaskan PageCache RAM. Tidak ada data server yang dihapus.',
            type: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ya, Jalankan!',
            cancelButtonText: 'Batal',
            closeOnConfirm: true
        }, function () {
            executePurge();
        });
    });

    function executePurge() {
        $('#btnRunPurge').prop('disabled', true);
        $('#execSpinner').show();
        $('#terminalOutput').text('[SYSTEM] Memulai Safe Cache Purge...\nHarap tunggu sebentar...');

        $.ajax({
            url: "{{ route('admin.cleaner.purge') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                $('#btnRunPurge').prop('disabled', false);
                $('#execSpinner').hide();
                if (res.success) {
                    $('#terminalOutput').text(res.logs.join('\n'));
                    swal('Sukses!', 'Safe Cache Purge Selesai!\nDisk dibebaskan: ' + res.disk_freed_formatted + '\nRAM dibebaskan: ' + res.ram_freed_formatted, 'success');
                    loadScan();
                } else {
                    $('#terminalOutput').text('[ERROR] Gagal: ' + (res.message || 'Terjadi kesalahan sistem.'));
                }
            },
            error: function (xhr) {
                $('#btnRunPurge').prop('disabled', false);
                $('#execSpinner').hide();
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghubungi server.';
                $('#terminalOutput').text('[ERROR] ' + msg);
                swal('Gagal', msg, 'error');
            }
        });
    }

    // Mode 2: Reset Form Validation
    function checkResetUnlock() {
        var c1 = $('#checkConfirm1').is(':checked');
        var c2 = $('#checkConfirm2').is(':checked');
        var text = $('#inputConfirmText').val().trim();

        if (c1 && c2 && text === 'RESET-ASTA') {
            $('#btnRunReset').prop('disabled', false);
        } else {
            $('#btnRunReset').prop('disabled', true);
        }
    }

    $('#checkConfirm1, #checkConfirm2').on('change', checkResetUnlock);
    $('#inputConfirmText').on('input', checkResetUnlock);

    // Mode 2: Factory Reset Execution
    $('#btnRunReset').on('click', function () {
        swal({
            title: 'KONFIRMASI TERAKHIR: FACTORY RESET',
            text: 'PERINGATAN KERAS: Seluruh {{ $totalServers }} server client dan {{ $totalUsers }} user non-admin akan DIHAPUS PERMANEN! Apakah Anda benar-benar yakin?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'HAPUS SEMUA & RESET!',
            cancelButtonText: 'Batal',
            closeOnConfirm: true
        }, function () {
            executeFactoryReset();
        });
    });

    function executeFactoryReset() {
        $('#btnRunReset').prop('disabled', true);
        $('#execSpinner').show();
        $('#terminalOutput').text('[DANGER] Memulai Full Factory Reset...\nSedang menghapus server, data disk, container, dan user...');

        $.ajax({
            url: "{{ route('admin.cleaner.reset') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                confirm_text: $('#inputConfirmText').val().trim()
            },
            success: function (res) {
                $('#btnRunReset').prop('disabled', false);
                $('#execSpinner').hide();
                if (res.success) {
                    $('#terminalOutput').text(res.logs.join('\n'));
                    swal('Panel Berhasil Direset!', 'Semua server dan user client telah dihapus.\nPanel kini dalam kondisi FRESH INSTALL.\nDisk dibebaskan: ' + res.disk_freed_formatted, 'success');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else {
                    $('#terminalOutput').text('[ERROR] ' + (res.message || 'Gagal mereset panel.'));
                    swal('Gagal', res.message, 'error');
                }
            },
            error: function (xhr) {
                $('#btnRunReset').prop('disabled', false);
                $('#execSpinner').hide();
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem saat mereset.';
                $('#terminalOutput').text('[ERROR] ' + msg);
                swal('Gagal', msg, 'error');
            }
        });
    }
});
</script>
@endsection
