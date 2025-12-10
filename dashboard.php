<?php
require_once 'config/config.php';
requireLogin();

// Include models
require_once 'models/User.php';
require_once 'models/Parfum.php';
require_once 'models/Penjualan.php';
require_once 'models/Pembelian.php';
require_once 'models/Vendor.php';

$database = new Database();
$db = $database->getConnection();

// Get dashboard data based on user role
$role = $_SESSION['user_role'];
$stats = [];
$chart_labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu'];

if ($role === 'admin' || $role === 'kasir') {
    // Get penjualan stats
    $parfum = new Parfum($db);
    $penjualan = new Penjualan($db);
    $total_penjualan_hari = $penjualan->getTotalPenjualanHari();
    $total_penjualan_bulan = $penjualan->getTotalPenjualanBulan();
    $total_transaksi_hari = $penjualan->getTotalTransaksiHari();
    $total_parfum_masuk = $parfum->getTotalparfum();
}

if ($role === 'admin' || $role === 'gudang') {
    // Get parfum stats
    $parfum = new Parfum($db);
    $vendor = new Vendor($db);
    $total_parfum = $parfum->getTotalparfum();
    $parfum_expired = $parfum->getparfumExpired();
    $stok_minimum = $parfum->getStokMinimum();
    $vendor_aktif = $vendor->vendorAktif();
    
    // Get pembelian stats
    $pembelian = new Pembelian($db);
    $total_pembelian_bulan = $pembelian->getTotalPembelianBulan();
}

// Get recent activities
$recent_penjualan = [];
$recent_pembelian = [];

if ($role === 'admin' || $role === 'kasir') {
    $stmt = $penjualan->readRecent(5);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recent_penjualan[] = $row;
    }
}

if ($role === 'admin' || $role === 'gudang') {
    $stmt = $pembelian->readRecent(5);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recent_pembelian[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="./src/output.css">
    <style>
        body {
            background-color: #000000;
            margin: 0;
            padding: 0;
        }
        .fade-in {
            animation: fadeIn .6s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .dashboard-container {
            background: #ffffff;
            border: 2px solid #d4af37;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
            margin: 20px;
            min-height: calc(100vh - 40px);
        }
    </style>
</head>
<body class="bg-black">
    <!-- Main Dashboard Container (White with Gold Border) -->
    <div class="dashboard-container flex">
        <!-- Sidebar -->
        <?php $page = "dashboard"; ?>
        <?php $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-scroll bg-white">
            <!-- Top Navigation -->
            <header class="flex justify-between items-center mb-10 fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-black mb-2">Dashboard</h1>
                    <p class="text-gray-600">Kelola toko parfum Anda dengan tampilan elegan ✨</p>
                </div>

                <div class="flex items-center gap-4 p-3 pr-5 rounded-xl">
                    <div class="text-right">
                        <div class="font-semibold text-black"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="text-gray-600 text-sm"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; box-shadow: 0 2px 8px rgba(212, 175, 55, 0.3);">
                        <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                </div>
            </header>

    <!-- CARDS -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        <!-- Card -->
         <?php if ($role === 'admin' || $role === 'kasir'): ?>
        <div class="rounded-xl p-6 shadow-lg fade-in bg-white" style="border: 2px solid #d4af37;">
            <p class="text-black text-sm font-medium">Penjualan Hari Ini</p>
            <h2 class="text-3xl font-bold mt-2" style="color: #d4af37;">Rp <?php echo number_format($total_penjualan_hari, 0, ',', '.'); ?></h2>
            <p class="text-sm mt-1 text-gray-600">Total pendapatan</p>
        </div>

        <div class="rounded-xl p-6 shadow-lg fade-in bg-white" style="border: 2px solid #d4af37;">
            <p class="text-black text-sm font-medium">Transaksi Hari Ini</p>
            <h2 class="text-3xl font-bold mt-2" style="color: #d4af37;"><?php echo $total_transaksi_hari; ?></h2>
            <p class="text-sm mt-1 text-gray-600">Jumlah transaksi</p>
        </div>
        <?php endif; ?>
        
        <?php if ($role === 'admin' || $role === 'gudang'): ?>
        <div class="rounded-xl p-6 shadow-lg fade-in bg-white" style="border: 2px solid #d4af37;">
            <p class="text-black text-sm font-medium">Total Parfum</p>
            <h2 class="text-3xl font-bold mt-2" style="color: #d4af37;"><?php echo $total_parfum?></h2>
            <p class="text-sm mt-1 text-gray-600">Stok parfum tersedia</p>
        </div>

        <div class="rounded-xl p-6 shadow-lg fade-in bg-white" style="border: 2px solid #d4af37;">
            <p class="text-black text-sm font-medium">Stok Minimum</p>
            <h2 class="text-3xl font-bold mt-2" style="color: #d4af37;"><?php echo $stok_minimum?></h2>
            <p class="text-sm mt-1 text-gray-600">Perlu restock segera</p>
        </div>
        <?php endif; ?>
    </section>

    <!-- CHART SECTION -->
    <section class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-10 fade-in">

        <!-- Chart Penjualan -->
        <div class="rounded-xl shadow-lg p-6 col-span-2 bg-white fade-in" style="border: 2px solid #d4af37;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-black">Grafik Penjualan Bulanan</h3>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-yellow-400 animate-pulse"></div>
                    <span class="text-sm text-gray-600">Live</span>
                </div>
            </div>
            <div class="relative">
                <canvas id="salesChart" class="w-full h-64"></canvas>
            </div>
        </div>

        <!-- Card Pembelian -->
        <div class="rounded-xl shadow-lg p-6 bg-white" style="border: 2px solid #d4af37;">
            <h3 class="text-xl font-semibold mb-4" style="color: #d4af37;">Statistik Pembelian</h3>
            <ul class="space-y-3">
                <li class="flex justify-between text-black">
                    <span>Total Pembelian Bulan Ini</span>
                    <span class="font-bold" style="color: #d4af37;"><?php echo number_format($total_pembelian_bulan, 0, ',', '.')?></span>
                </li>
                <li class="flex justify-between text-black">
                    <span>Vendor Aktif</span>
                    <span class="font-bold" style="color: #d4af37;"><?php echo $vendor_aktif?></span>
                </li>
                <li class="flex justify-between text-black">
                    <span>Parfum Masuk</span>
                    <span class="font-bold" style="color: #d4af37;"><?php echo $total_parfum_masuk?></span>
                </li>
            </ul>
        </div>
    </section>

    <!-- TABLES -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-8 fade-in">

        <!-- Penjualan Terbaru -->
        <?php if ($role === 'admin' || $role === 'kasir'): ?>
        <div class="rounded-xl shadow-lg overflow-hidden bg-white" style="border: 2px solid #d4af37;">
            <div class="p-6" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%);">
                <h3 class="text-xl font-bold text-black">Penjualan Terbaru</h3>
            </div>
            <table class="w-full text-left bg-white">
                <thead>
                    <tr style="background: rgba(212, 175, 55, 0.1);">
                        <th class="px-6 py-3 text-sm" style="color: #d4af37;">No. Transaksi</th>
                        <th class="px-6 py-3 text-sm" style="color: #d4af37;">Total</th>
                        <th class="px-6 py-3 text-sm" style="color: #d4af37;">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_penjualan)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">Belum ada data penjualan</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recent_penjualan as $penjualan): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-black"><?php echo htmlspecialchars($penjualan['no_transaksi']); ?></td>
                        <td class="px-6 py-4 font-semibold" style="color: #d4af37;">Rp <?php echo number_format($penjualan['total_harga'], 0, ',', '.'); ?></td>
                        <td class="px-6 py-4 text-black"><?php echo date('d/m/Y H:i', strtotime($penjualan['tanggal_penjualan'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Pembelian Terbaru -->
        <?php if ($role === 'admin' || $role === 'gudang'): ?>
        <div class="rounded-xl shadow-lg overflow-hidden bg-white" style="border: 2px solid #d4af37;">
            <div class="p-6" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%);">
                <h3 class="text-xl font-bold text-black">Pembelian Terbaru</h3>
            </div>
            <table class="w-full text-left bg-white">
                <thead>
                    <tr style="background: rgba(212, 175, 55, 0.1);">
                        <th class="px-6 py-3 text-sm" style="color: #d4af37;">Faktur</th>
                        <th class="px-6 py-3 text-sm" style="color: #d4af37;">Total</th>
                        <th class="px-6 py-3 text-sm" style="color: #d4af37;">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_pembelian)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">Belum ada data pembelian</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recent_pembelian as $pembelian): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-black"><?php echo htmlspecialchars($pembelian['no_faktur']); ?></td>
                        <td class="px-6 py-4 font-semibold" style="color: #d4af37;">Rp <?php echo number_format($pembelian['total_harga'], 0, ',', '.'); ?></td>
                        <td class="px-6 py-4 text-black"><?php echo date('d/m/Y', strtotime($pembelian['tanggal_pembelian'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </section>
    </main>
    </div>
    </div>
</body>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById("salesChart").getContext("2d");
    
    const chart = new Chart(ctx, {
        type: "line",
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: "Penjualan",
                data: <?php echo json_encode($total_penjualan_bulan); ?>,
                borderWidth: 3,
                borderColor: "#d4af37",
                backgroundColor: "rgba(212, 175, 55, 0.2)",
                tension: 0.4,
                fill: true,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: "#d4af37",
                pointBorderColor: "#ffffff",
                pointBorderWidth: 2,
                pointHoverBackgroundColor: "#c9a961",
                pointHoverBorderColor: "#d4af37",
                pointHoverBorderWidth: 3,
                shadowOffsetX: 0,
                shadowOffsetY: 4,
                shadowBlur: 10,
                shadowColor: "rgba(212, 175, 55, 0.3)"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 2000,
                easing: 'easeInOutCubic',
                delay: (context) => {
                    let delay = 0;
                    if (context.type === 'data' && context.mode === 'default') {
                        delay = context.dataIndex * 100;
                    }
                    return delay;
                },
                onProgress: function(animation) {
                    // Animation progress callback
                },
                onComplete: function(animation) {
                    // Animation complete callback
                }
            },
            animations: {
                x: {
                    from: 0,
                    duration: 1500,
                    easing: 'easeOutCubic'
                },
                y: {
                    from: 0,
                    duration: 1500,
                    easing: 'easeOutCubic'
                },
                colors: {
                    from: 'transparent',
                    duration: 2000
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: { 
                        color: '#333',
                        font: {
                            size: 12
                        }
                    },
                    grid: { 
                        color: '#e0e0e0',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: { 
                        color: '#333',
                        font: {
                            size: 12
                        }
                    },
                    grid: { 
                        color: '#e0e0e0',
                        drawBorder: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    labels: { 
                        color: '#333',
                        font: {
                            size: 14,
                            weight: 'bold'
                        },
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#d4af37',
                    bodyColor: '#ffffff',
                    borderColor: '#d4af37',
                    borderWidth: 2,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return 'Penjualan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    // Add hover effect animation
    const canvas = document.getElementById("salesChart");
    let hoverTimeout;
    
    canvas.addEventListener('mousemove', function(e) {
        const points = chart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
        if (points.length) {
            canvas.style.cursor = 'pointer';
            canvas.style.transition = 'transform 0.2s ease';
            clearTimeout(hoverTimeout);
        } else {
            canvas.style.cursor = 'default';
        }
    });
    
    canvas.addEventListener('mouseleave', function() {
        canvas.style.cursor = 'default';
    });
    
    // Animate chart on scroll into view with fade-in effect
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Trigger animation when chart comes into view
                chart.update('active');
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    entry.target.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    }, { threshold: 0.1 });
    
    observer.observe(canvas);
    
    // Add periodic update animation (optional - for live data feel)
    // Uncomment if you want periodic animation refresh
    // setInterval(() => {
    //     chart.update('none'); // Update without animation
    // }, 5000);
});    
</script>   
</html>