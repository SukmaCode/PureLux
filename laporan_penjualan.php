<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Penjualan.php';

$database = new Database();
$db = $database->getConnection();

$penjualan = new Penjualan($db);

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Get laporan data
$stmt = $penjualan->getLaporanPenjualan($start_date, $end_date);

// Calculate summary
$total_penjualan = 0;
$total_diskon = 0;
$total_transaksi = 0;
$laporan_data = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total_penjualan += $row['total_harga'];
    $total_diskon += $row['diskon'] ?? 0;
    $total_transaksi++;
    $laporan_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - <?php echo APP_NAME; ?></title>
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
        .dashboard-container {
            background: #ffffff;
            border: 2px solid #d4af37;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
            margin: 20px;
            min-height: calc(100vh - 40px);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #d4af37;
            font-size: 14px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #d0d0d0;
            border-radius: 8px;
            font-size: 15px;
            background: white;
            color: #000000;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
    </style>
</head>
<body class="bg-black">
    <!-- Main Container (White with Gold Border) -->
    <div class="dashboard-container flex">
        <!-- Sidebar -->
        <?php 
        $page = "laporan_penjualan";
        $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-scroll bg-white">
            <!-- Top Navigation -->
            <header class="flex justify-between items-center mb-10 fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-black mb-2">Laporan Penjualan</h1>
                    <p class="text-gray-600">Lihat laporan penjualan dengan tampilan elegan ✨</p>
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

            <!-- Content -->
            <div class="content" style="background: transparent;">
                <!-- Filter Section -->
                <div class="form-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <h3 style="color: #d4af37; font-size: 20px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Filter Laporan</h3>
                    <form method="GET" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                            <label for="start_date">Tanggal Mulai:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                            <label for="end_date">Tanggal Selesai:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div style="display: flex; gap: 10px; align-items: end;">
                            <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; height: fit-content;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Filter</button>
                            <button type="button" onclick="window.print()" style="padding: 12px 24px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; height: fit-content;" onmouseover="this.style.background='#d0d0d0'" onmouseout="this.style.background='#e0e0e0'">Cetak</button>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div class="rounded-xl p-6 shadow-lg fade-in bg-white" style="border: 2px solid #d4af37;">
                        <p class="text-black text-sm font-medium">Total Penjualan</p>
                        <h2 class="text-3xl font-bold mt-2" style="color: #d4af37;"><?php echo formatCurrency($total_penjualan); ?></h2>
                        <p class="text-sm mt-1 text-gray-600">Periode: <?php echo date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)); ?></p>
                    </div>

                    <div class="rounded-xl p-6 shadow-lg fade-in bg-white" style="border: 2px solid #d4af37;">
                        <p class="text-black text-sm font-medium">Total Transaksi</p>
                        <h2 class="text-3xl font-bold mt-2" style="color: #d4af37;"><?php echo $total_transaksi; ?></h2>
                        <p class="text-sm mt-1 text-gray-600">Jumlah transaksi dalam periode</p>
                    </div>

                    <div class="rounded-xl p-6 shadow-lg fade-in bg-white" style="border: 2px solid #d4af37;">
                        <p class="text-black text-sm font-medium">Rata-rata per Transaksi</p>
                        <h2 class="text-3xl font-bold mt-2" style="color: #d4af37;"><?php echo $total_transaksi > 0 ? formatCurrency($total_penjualan / $total_transaksi) : 'Rp 0'; ?></h2>
                        <p class="text-sm mt-1 text-gray-600">Nilai rata-rata per transaksi</p>
                    </div>

                    <div class="rounded-xl p-6 shadow-lg fade-in bg-white" style="border: 2px solid #d4af37;">
                        <p class="text-black text-sm font-medium">Total Diskon</p>
                        <h2 class="text-3xl font-bold mt-2" style="color: #d4af37;"><?php echo formatCurrency($total_diskon); ?></h2>
                        <p class="text-sm mt-1 text-gray-600">Total diskon diberikan</p>
                    </div>
                </div>

                <!-- Laporan Table -->
                <div class="table-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <div class="table-header" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); padding: 20px;">
                        <h3 class="table-title" style="color: #000000; font-size: 20px; font-weight: bold; margin: 0;">Detail Laporan Penjualan</h3>
                    </div>
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(212, 175, 55, 0.1);">
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">No. Transaksi</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Kasir</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Diskon</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Total Harga</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Total Bayar</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Kembalian</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Tanggal</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($laporan_data)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 20px; color: #666;">
                                        Tidak ada data penjualan untuk periode yang dipilih
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($laporan_data as $row): ?>
                                <tr style="border-b: 1px solid #e0e0e0; transition: background 0.2s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#ffffff'">
                                    <td style="padding: 15px; color: #000000; font-weight: 500;"><?php echo $row['no_transaksi']; ?></td>
                                    <td style="padding: 15px; color: #000000;"><?php echo $row['kasir']; ?></td>
                                    <td style="padding: 15px; color: #000000;"><?php echo formatCurrency($row['diskon'] ?? 0); ?></td>
                                    <td style="padding: 15px; color: #000000; font-weight: 600;"><?php echo formatCurrency($row['total_harga']); ?></td>
                                    <td style="padding: 15px; color: #000000; font-weight: 600;"><?php echo formatCurrency($row['total_bayar']); ?></td>
                                    <td style="padding: 15px; color: #000000; font-weight: 600;"><?php echo formatCurrency($row['kembalian']); ?></td>
                                    <td style="padding: 15px; color: #000000;"><?php echo date('d/m/Y H:i', strtotime($row['tanggal_penjualan'])); ?></td>
                                    <td style="padding: 15px;">
                                        <a href="struk.php?id=<?php echo $row['id']; ?>" 
                                           style="padding: 8px 16px; background: linear-gradient(135deg, #3498db, #2980b9); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(52, 152, 219, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'" target="_blank">Struk</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    </div>

    <style>
        @media print {
            .sidebar, header, .form-container, button {
                display: none !important;
            }
            
            .dashboard-container {
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            
            .content {
                padding: 0 !important;
            }
            
            .table-container {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }
            
            .grid {
                display: none !important;
            }
        }
    </style>
</body>
</html>
