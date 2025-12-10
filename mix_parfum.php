<?php
require_once 'config/config.php';
requireRole(['admin', 'gudang']);

require_once 'models/MixParfum.php';
require_once 'models/Parfum.php';

$database = new Database();
$db = $database->getConnection();

// Auto-create tables if not exist
try {
    $db->exec("CREATE TABLE IF NOT EXISTS mix_parfum (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_mix VARCHAR(20) UNIQUE NOT NULL,
        nama_mix VARCHAR(200) NOT NULL,
        deskripsi TEXT,
        harga_jual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        stok INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS detail_mix_parfum (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mix_parfum_id INT NOT NULL,
        parfum_id INT NOT NULL,
        persentase DECIMAL(5,2) NOT NULL,
        jumlah_ml DECIMAL(10,2) DEFAULT 0,
        FOREIGN KEY (mix_parfum_id) REFERENCES mix_parfum(id) ON DELETE CASCADE,
        FOREIGN KEY (parfum_id) REFERENCES parfum(id) ON DELETE CASCADE,
        INDEX idx_mix_parfum (mix_parfum_id),
        INDEX idx_parfum (parfum_id)
    )");
} catch (PDOException $e) {
    // Ignore if tables already exist
}

$mixParfum = new MixParfum($db);
$parfum = new Parfum($db);

$message = '';
$message_type = '';

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_detail') {
    header('Content-Type: application/json');
    $mixParfum->id = sanitizeInput($_GET['id']);
    if ($mixParfum->readOne()) {
        $detail_stmt = $mixParfum->getDetailMix($mixParfum->id);
        $komposisi = [];
        while ($detail = $detail_stmt->fetch(PDO::FETCH_ASSOC)) {
            $komposisi[] = [
                'nama_parfum' => $detail['nama_parfum'],
                'persentase' => $detail['persentase'],
                'jumlah_ml' => $detail['jumlah_ml']
            ];
        }
        echo json_encode([
            'nama_mix' => $mixParfum->nama_mix,
            'deskripsi' => $mixParfum->deskripsi,
            'komposisi' => $komposisi
        ]);
    } else {
        echo json_encode(['error' => 'Mix parfum tidak ditemukan']);
    }
    exit;
}

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    $db->beginTransaction();
                    
                    $mixParfum->kode_mix = $mixParfum->generateKodeMix();
                    $mixParfum->nama_mix = sanitizeInput($_POST['nama_mix']);
                    $mixParfum->deskripsi = sanitizeInput($_POST['deskripsi'] ?? '');
                    $mixParfum->harga_jual = sanitizeInput($_POST['harga_jual'] ?? 0);
                    $mixParfum->stok = sanitizeInput($_POST['stok'] ?? 0);
                    $mixParfum->is_active = 1;
                    
                    $mix_id = $mixParfum->create();
                    
                    if (!$mix_id) {
                        throw new Exception('Gagal membuat mix parfum');
                    }
                    
                    // Process detail mix
                    $items = json_decode($_POST['items'], true);
                    $total_percent = 0;
                    
                    foreach ($items as $item) {
                        $persentase = floatval($item['persentase']);
                        $jumlah_ml = floatval($item['jumlah_ml'] ?? 0);
                        $total_percent += $persentase;
                        
                        $mixParfum->addDetail($mix_id, $item['parfum_id'], $persentase, $jumlah_ml);
                    }
                    
                    // Validate total percentage
                    if (abs($total_percent - 100) > 0.01) {
                        throw new Exception('Total persentase harus 100% (Saat ini: ' . $total_percent . '%)');
                    }
                    
                    $db->commit();
                    $message = 'Mix de\'Parfum berhasil dibuat!';
                    $message_type = 'success';
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
                break;
                
            case 'delete':
                $mixParfum->id = sanitizeInput($_POST['id']);
                if ($mixParfum->delete()) {
                    $message = 'Mix de\'Parfum berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus mix parfum!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all mix parfum
$stmt = $mixParfum->readAll();

// Get all parfum for dropdown
$parfum_stmt = $parfum->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mix de'Parfum - <?php echo APP_NAME; ?></title>
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
        .mix-item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
            padding: 15px;
            border: 2px solid #d0d0d0;
            border-radius: 8px;
            background: #f9f9f9;
            align-items: center;
        }
        .mix-item-row:hover {
            border-color: #d4af37;
            background: #fffbe6;
        }
        .percent-total {
            font-weight: bold;
            color: #d4af37;
            font-size: 16px;
            padding: 10px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 8px;
            text-align: center;
        }
        .percent-total.error {
            color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
        }
    </style>
</head>
<body class="bg-black">
    <!-- Main Container (White with Gold Border) -->
    <div class="dashboard-container flex">
        <!-- Sidebar -->
        <?php 
        $page = "mix_parfum";
        $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-scroll bg-white">
            <!-- Top Navigation -->
            <header class="flex justify-between items-center mb-10 fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-black mb-2">Mix de'Parfum</h1>
                    <p class="text-gray-600">Campurkan wangi parfum untuk hasil yang unik ✨</p>
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

                <!-- Create Mix Form -->
                <div class="form-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <h2 style="color: #d4af37; font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Buat Mix Parfum Baru</h2>
                    <form method="POST" id="mixForm">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="items" id="itemsInput">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama_mix">Nama Mix Parfum</label>
                                <input type="text" id="nama_mix" name="nama_mix" required placeholder="cth: Floral Fresh, Woody Spicy">
                            </div>
                            <div class="form-group">
                                <label for="harga_jual">Harga Jual (Rp)</label>
                                <input type="number" id="harga_jual" name="harga_jual" min="0" step="100" value="0">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="stok">Stok</label>
                                <input type="number" id="stok" name="stok" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label for="deskripsi">Deskripsi</label>
                                <textarea id="deskripsi" name="deskripsi" rows="2" placeholder="Deskripsi mix parfum"></textarea>
                            </div>
                        </div>

                        <!-- Mix Items Section -->
                        <div class="form-group">
                            <label>Komposisi Parfum</label>
                            <div id="mixItemsContainer">
                                <div class="mix-item-row">
                                    <select class="parfum-select" onchange="updateTotalPercent()">
                                        <option value="">Pilih Parfum</option>
                                        <?php 
                                        $parfum_stmt->execute();
                                        while ($row = $parfum_stmt->fetch(PDO::FETCH_ASSOC)): 
                                        ?>
                                            <option value="<?php echo $row['id']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_parfum']); ?>">
                                                <?php echo htmlspecialchars($row['nama_parfum']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <input type="number" class="persentase-input" placeholder="%" min="0" max="100" step="0.01" onchange="updateTotalPercent()" onkeyup="updateTotalPercent()">
                                    <input type="number" class="jumlah-ml-input" placeholder="ML" min="0" step="0.1" value="0">
                                    <button type="button" onclick="removeMixItem(this)" style="padding: 8px 16px; background: linear-gradient(135deg, #e74c3c, #c0392b); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(231, 76, 60, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Hapus</button>
                                </div>
                            </div>
                            <div class="percent-total" id="totalPercent">Total: 0%</div>
                            <button type="button" onclick="addMixItem()" style="padding: 10px 20px; background: linear-gradient(135deg, #27ae60, #229954); color: #ffffff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; margin-top: 10px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(39, 174, 96, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Tambah Parfum</button>
                        </div>

                        <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; margin-top: 15px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Buat Mix Parfum</button>
                    </form>
                </div>

                <!-- Mix Parfum List -->
                <div class="table-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <div class="table-header" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); padding: 20px;">
                        <h3 class="table-title" style="color: #000000; font-size: 20px; font-weight: bold; margin: 0;">Daftar Mix de'Parfum</h3>
                    </div>
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(212, 175, 55, 0.1);">
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Kode</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Nama Mix</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Komposisi</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Harga</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Stok</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                                $detail_stmt = $mixParfum->getDetailMix($row['id']);
                                $komposisi = [];
                                while ($detail = $detail_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $komposisi[] = $detail['nama_parfum'] . ' (' . $detail['persentase'] . '%)';
                                }
                            ?>
                            <tr style="border-b: 1px solid #e0e0e0; transition: background 0.2s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#ffffff'">
                                <td style="padding: 15px; color: #000000; font-weight: 500;"><?php echo $row['kode_mix']; ?></td>
                                <td style="padding: 15px; color: #000000; font-weight: 600;"><?php echo htmlspecialchars($row['nama_mix']); ?></td>
                                <td style="padding: 15px; color: #000000; font-size: 12px;">
                                    <?php echo implode(', ', array_slice($komposisi, 0, 2)); ?>
                                    <?php if (count($komposisi) > 2): ?>
                                        <span style="color: #666;">+<?php echo count($komposisi) - 2; ?> lainnya</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px; color: #000000; font-weight: 600;"><?php echo formatCurrency($row['harga_jual']); ?></td>
                                <td style="padding: 15px; color: #000000;"><?php echo $row['stok']; ?></td>
                                <td style="padding: 15px;">
                                    <button onclick="viewDetail(<?php echo $row['id']; ?>)" style="padding: 8px 16px; background: linear-gradient(135deg, #3498db, #2980b9); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin-right: 5px; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(52, 152, 219, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Detail</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus mix parfum ini?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" style="padding: 8px 16px; background: linear-gradient(135deg, #e74c3c, #c0392b); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(231, 76, 60, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; border: 2px solid #d4af37; width: 90%; max-width: 600px; max-height: 90%; overflow-y: auto; box-shadow: 0 8px 30px rgba(212, 175, 55, 0.3);">
            <h2 style="color: #d4af37; font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Detail Mix Parfum</h2>
            <div id="detailContent"></div>
            <button onclick="closeDetailModal()" style="padding: 10px 20px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; margin-top: 20px; width: 100%;">Tutup</button>
        </div>
    </div>

    <script>
        let mixItemCount = 1;

        function addMixItem() {
            const container = document.getElementById('mixItemsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'mix-item-row';
            
            newItem.innerHTML = `
                <select class="parfum-select" onchange="updateTotalPercent()">
                    <option value="">Pilih Parfum</option>
                    ${document.querySelector('.parfum-select').innerHTML}
                </select>
                <input type="number" class="persentase-input" placeholder="%" min="0" max="100" step="0.01" onchange="updateTotalPercent()" onkeyup="updateTotalPercent()">
                <input type="number" class="jumlah-ml-input" placeholder="ML" min="0" step="0.1" value="0">
                <button type="button" onclick="removeMixItem(this)" style="padding: 8px 16px; background: linear-gradient(135deg, #e74c3c, #c0392b); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(231, 76, 60, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Hapus</button>
            `;
            
            container.appendChild(newItem);
            mixItemCount++;
        }

        function removeMixItem(button) {
            if (document.querySelectorAll('.mix-item-row').length > 1) {
                button.parentElement.remove();
                updateTotalPercent();
            } else {
                alert('Minimal harus ada 1 parfum');
            }
        }

        function updateTotalPercent() {
            let total = 0;
            document.querySelectorAll('.persentase-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            
            const totalElement = document.getElementById('totalPercent');
            totalElement.textContent = 'Total: ' + total.toFixed(2) + '%';
            
            if (Math.abs(total - 100) < 0.01) {
                totalElement.className = 'percent-total';
            } else {
                totalElement.className = 'percent-total error';
            }
        }

        function viewDetail(id) {
            // Fetch detail via AJAX or show in modal
            fetch('mix_parfum.php?action=get_detail&id=' + id)
                .then(response => response.json())
                .then(data => {
                    let html = '<h3 style="color: #d4af37; margin-bottom: 15px;">' + data.nama_mix + '</h3>';
                    html += '<p style="margin-bottom: 15px; color: #666;">' + (data.deskripsi || 'Tidak ada deskripsi') + '</p>';
                    html += '<h4 style="color: #000; margin-bottom: 10px; font-weight: 600;">Komposisi:</h4>';
                    html += '<ul style="list-style: none; padding: 0;">';
                    data.komposisi.forEach(item => {
                        html += '<li style="padding: 8px; margin-bottom: 5px; background: #f9f9f9; border-left: 3px solid #d4af37; border-radius: 4px;">';
                        html += '<strong>' + item.nama_parfum + '</strong> - ' + item.persentase + '%';
                        if (item.jumlah_ml > 0) {
                            html += ' (' + item.jumlah_ml + ' ML)';
                        }
                        html += '</li>';
                    });
                    html += '</ul>';
                    
                    document.getElementById('detailContent').innerHTML = html;
                    document.getElementById('detailModal').style.display = 'block';
                })
                .catch(error => {
                    alert('Error loading detail');
                });
        }

        function closeDetailModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        // Handle form submission
        document.getElementById('mixForm').addEventListener('submit', function(e) {
            const items = [];
            let totalPercent = 0;
            
            document.querySelectorAll('.mix-item-row').forEach(row => {
                const select = row.querySelector('.parfum-select');
                const persentase = parseFloat(row.querySelector('.persentase-input').value) || 0;
                const jumlah_ml = parseFloat(row.querySelector('.jumlah-ml-input').value) || 0;
                
                if (select.value && persentase > 0) {
                    items.push({
                        parfum_id: select.value,
                        persentase: persentase,
                        jumlah_ml: jumlah_ml
                    });
                    totalPercent += persentase;
                }
            });
            
            if (items.length === 0) {
                e.preventDefault();
                alert('Minimal harus ada 1 parfum dengan persentase!');
                return false;
            }
            
            if (Math.abs(totalPercent - 100) > 0.01) {
                e.preventDefault();
                alert('Total persentase harus 100%! (Saat ini: ' + totalPercent.toFixed(2) + '%)');
                return false;
            }
            
            document.getElementById('itemsInput').value = JSON.stringify(items);
        });

    </script>
</body>
</html>

