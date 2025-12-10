<?php
require_once 'config/config.php';
requireRole(['admin', 'gudang']);

require_once 'models/Parfum.php';

$database = new Database();
$db = $database->getConnection();

$parfum = new Parfum($db);

$message = '';
$message_type = '';

// Handle stock adjustment
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'adjust_stock') {
    $parfum_id = sanitizeInput($_POST['parfum_id']);
    $adjustment = sanitizeInput($_POST['adjustment']);
    $reason = sanitizeInput($_POST['reason']);
    
    if ($parfum->updateStok($parfum_id, $adjustment)) {
        // Log stock adjustment (optional)
        $message = 'Stok berhasil disesuaikan!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menyesuaikan stok!';
        $message_type = 'error';
    }
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Get parfum data based on filter
switch ($filter) {
    case 'low_stock':
        $query = "SELECT o.*, k.nama_kategori 
                  FROM parfum o
                  LEFT JOIN kategori_parfum k ON o.kategori_id = k.id
                  WHERE o.stok <= o.stok_minimum
                  ORDER BY o.nama_parfum";
        break;
    case 'expired':
        $query = "SELECT o.*, k.nama_kategori 
                  FROM parfum o
                  LEFT JOIN kategori_parfum k ON o.kategori_id = k.id
                  WHERE o.tanggal_expired <= CURDATE()
                  ORDER BY o.nama_parfum";
        break;
    case 'expiring_soon':
        $query = "SELECT o.*, k.nama_kategori 
                  FROM parfum o
                  LEFT JOIN kategori_parfum k ON o.kategori_id = k.id
                  WHERE o.tanggal_expired <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                  AND o.tanggal_expired > CURDATE()
                  ORDER BY o.tanggal_expired";
        break;
    default:
        if (!empty($search)) {
            $stmt = $parfum->search($search);
        } else {
            $stmt = $parfum->readAll();
        }
        break;
}

if (isset($query)) {
    $stmt = $db->prepare($query);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Stok - <?php echo APP_NAME; ?></title>
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
        .form-group textarea,
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
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: rgba(212, 175, 55, 0.15);
            color: #d4af37;
        }
        .badge-danger {
            background: rgba(139, 0, 0, 0.2);
            color: #8b0000;
        }
        .badge-warning {
            background: rgba(255, 193, 7, 0.2);
            color: #856404;
        }
    </style>
</head>
<body class="bg-black">
    <!-- Main Container (White with Gold Border) -->
    <div class="dashboard-container flex">
        <!-- Sidebar -->
        <?php 
        $page = "stok";
        $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-scroll bg-white">
            <!-- Top Navigation -->
            <header class="flex justify-between items-center mb-10 fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-black mb-2">Manajemen Stok</h1>
                    <p class="text-gray-600">Kelola stok parfum dengan tampilan elegan ✨</p>
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
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: 20px; padding: 15px; border-radius: 8px; border-left: 4px solid; <?php echo $message_type === 'error' ? 'background: rgba(139, 0, 0, 0.2); border-color: #8b0000; color: #ff6b6b;' : 'background: rgba(212, 175, 55, 0.15); border-color: #d4af37; color: #d4af37;'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Filter Section -->
                <div class="form-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <h3 style="color: #d4af37; font-size: 20px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Filter Stok</h3>
                    <form method="GET" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                            <label for="filter">Filter:</label>
                            <select id="filter" name="filter" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Semua parfum</option>
                                <option value="low_stock" <?php echo $filter === 'low_stock' ? 'selected' : ''; ?>>Stok Minimum</option>
                                <option value="expired" <?php echo $filter === 'expired' ? 'selected' : ''; ?>>Sudah Expired</option>
                                <option value="expiring_soon" <?php echo $filter === 'expiring_soon' ? 'selected' : ''; ?>>Akan Expired (30 hari)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                            <label for="search">Cari:</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Nama atau kode parfum">
                        </div>
                        <div style="display: flex; gap: 10px; align-items: end;">
                            <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; height: fit-content;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Filter</button>
                            <a href="stok.php" style="padding: 12px 24px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.2s; height: fit-content;" onmouseover="this.style.background='#d0d0d0'" onmouseout="this.style.background='#e0e0e0'">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Stock Adjustment Form -->
                <div class="form-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <h3 style="color: #d4af37; font-size: 20px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Penyesuaian Stok</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="adjust_stock">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="parfum_id">Pilih parfum</label>
                                <select id="parfum_id" name="parfum_id" required>
                                    <option value="">Pilih parfum</option>
                                    <?php 
                                    $parfum_stmt = $parfum->readAll();
                                    while ($row = $parfum_stmt->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                        <option value="<?php echo $row['id']; ?>">
                                            <?php echo $row['nama_parfum'] . ' (Stok: ' . $row['stok'] . ')'; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="adjustment">Penyesuaian</label>
                                <input type="number" id="adjustment" name="adjustment" required 
                                       placeholder="+/- jumlah stok" step="1">
                                <small>Gunakan tanda + untuk menambah, - untuk mengurangi</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reason">Alasan Penyesuaian</label>
                            <textarea id="reason" name="reason" rows="2" required 
                                      placeholder="Contoh: Koreksi stok, kerusakan parfum, dll"></textarea>
                        </div>
                        
                        <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; margin-top: 15px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Sesuaikan Stok</button>
                    </form>
                </div>

                <!-- Stock Table -->
                <div class="table-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <div class="table-header" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); padding: 20px;">
                        <h3 class="table-title" style="color: #000000; font-size: 20px; font-weight: bold; margin: 0;">Daftar Stok Parfum</h3>
                    </div>
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(212, 175, 55, 0.1);">
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Kode</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Nama Parfum</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Kategori</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Stok</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Stok Min</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Status</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Expired</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Harga Beli</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Harga Jual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr style="border-b: 1px solid #e0e0e0; transition: background 0.2s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#ffffff'">
                                <td style="padding: 15px; color: #000000; font-weight: 500;"><?php echo $row['kode_parfum']; ?></td>
                                <td style="padding: 15px; color: #000000; font-weight: 500;"><?php echo $row['nama_parfum']; ?></td>
                                <td style="padding: 15px; color: #000000;"><?php echo $row['nama_kategori']; ?></td>
                                <td style="padding: 15px;">
                                    <span class="badge <?php echo $row['stok'] <= $row['stok_minimum'] ? 'badge-danger' : 'badge-success'; ?>">
                                        <?php echo $row['stok']; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; color: #000000;"><?php echo $row['stok_minimum']; ?></td>
                                <td style="padding: 15px;">
                                    <?php
                                    if ($row['stok'] <= 0) {
                                        echo '<span class="badge badge-danger">Habis</span>';
                                    } elseif ($row['stok'] <= $row['stok_minimum']) {
                                        echo '<span class="badge badge-warning">Minimal</span>';
                                    } else {
                                        echo '<span class="badge badge-success">Aman</span>';
                                    }
                                    ?>
                                </td>
                                <td style="padding: 15px; color: #000000;">
                                    <?php 
                                    if ($row['tanggal_expired']) {
                                        $expired = strtotime($row['tanggal_expired']);
                                        $now = time();
                                        $days_left = ($expired - $now) / (60 * 60 * 24);
                                        
                                        if ($days_left < 0) {
                                            echo '<span class="badge badge-danger">Expired</span>';
                                        } elseif ($days_left <= 30) {
                                            echo '<span class="badge badge-warning">' . date('d/m/Y', $expired) . '</span>';
                                        } else {
                                            echo date('d/m/Y', $expired);
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td style="padding: 15px; color: #000000;"><?php echo formatCurrency($row['harga_beli']); ?></td>
                                <td style="padding: 15px; color: #000000; font-weight: 600;"><?php echo formatCurrency($row['harga_jual']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    </div>
</body>
</html>
