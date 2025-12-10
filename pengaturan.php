<?php
require_once 'config/config.php';
requireRole(['admin']);

require_once 'models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

$pengaturan = new Pengaturan($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update') {
    $settings = [
        'nama_toko' => sanitizeInput($_POST['nama_toko'] ?? ''),
        'alamat_toko' => sanitizeInput($_POST['alamat_toko'] ?? ''),
        'telepon_toko' => sanitizeInput($_POST['telepon_toko'] ?? ''),
        'email_toko' => sanitizeInput($_POST['email_toko'] ?? ''),
        'ppn_persen' => sanitizeInput($_POST['ppn_persen'] ?? '10'),
        'footer_struk' => sanitizeInput($_POST['footer_struk'] ?? ''),
        'warna_primary' => sanitizeInput($_POST['warna_primary'] ?? '#667eea'),
        'warna_secondary' => sanitizeInput($_POST['warna_secondary'] ?? '#764ba2'),
        'warna_sidebar' => sanitizeInput($_POST['warna_sidebar'] ?? '#2c3e50'),
        'warna_sidebar_header' => sanitizeInput($_POST['warna_sidebar_header'] ?? '#34495e'),
        'warna_success' => sanitizeInput($_POST['warna_success'] ?? '#27ae60'),
        'warna_danger' => sanitizeInput($_POST['warna_danger'] ?? '#e74c3c'),
        'warna_warning' => sanitizeInput($_POST['warna_warning'] ?? '#f39c12'),
        'warna_info' => sanitizeInput($_POST['warna_info'] ?? '#3498db')
    ];
    
    if ($pengaturan->updateAll($settings)) {
        $message = 'Pengaturan berhasil diperbarui!';
        $message_type = 'success';
    } else {
        $message = 'Gagal memperbarui pengaturan!';
        $message_type = 'error';
    }
}

// Get all settings
$settings = $pengaturan->getAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Aplikasi - <?php echo APP_NAME; ?></title>
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
    </style>
</head>
<body class="bg-black">
    <!-- Main Container (White with Gold Border) -->
    <div class="dashboard-container flex">
        <!-- Sidebar -->
        <?php 
        $page = "pengaturan";
        $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-scroll bg-white">
            <!-- Top Navigation -->
            <header class="flex justify-between items-center mb-10 fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-black mb-2">Pengaturan Aplikasi</h1>
                    <p class="text-gray-600">Kelola pengaturan aplikasi dengan tampilan elegan ✨</p>
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

                <!-- Pengaturan Form -->
                <div class="form-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <h2 style="color: #d4af37; font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Pengaturan Umum</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update">
                        
                        <div class="form-group">
                            <label for="nama_toko">Nama Toko</label>
                            <input type="text" id="nama_toko" name="nama_toko" 
                                   value="<?php echo htmlspecialchars($settings['nama_toko'] ?? APP_NAME); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="alamat_toko">Alamat Toko</label>
                            <textarea id="alamat_toko" name="alamat_toko" rows="3"><?php echo htmlspecialchars($settings['alamat_toko'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telepon_toko">Telepon</label>
                                <input type="text" id="telepon_toko" name="telepon_toko" 
                                       value="<?php echo htmlspecialchars($settings['telepon_toko'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email_toko">Email</label>
                                <input type="email" id="email_toko" name="email_toko" 
                                       value="<?php echo htmlspecialchars($settings['email_toko'] ?? ''); ?>">
                            </div>
                        </div>

                        <hr style="margin: 30px 0; border: none; border-top: 2px solid #d4af37;">

                        <h3 style="margin-bottom: 20px; color: #d4af37; font-size: 20px; font-weight: bold;">Pengaturan Pajak</h3>
                        
                        <div class="form-group">
                            <label for="ppn_persen">PPN (%)</label>
                            <input type="number" id="ppn_persen" name="ppn_persen" 
                                   value="<?php echo htmlspecialchars($settings['ppn_persen'] ?? '10'); ?>" 
                                   min="0" max="100" step="0.1" required>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Persentase Pajak Pertambahan Nilai (PPN) yang akan diterapkan pada setiap transaksi penjualan
                            </small>
                        </div>

                        <hr style="margin: 30px 0; border: none; border-top: 2px solid #d4af37;">

                        <h3 style="margin-bottom: 20px; color: #d4af37; font-size: 20px; font-weight: bold;">Pengaturan Struk</h3>
                        
                        <div class="form-group">
                            <label for="footer_struk">Footer Struk</label>
                            <textarea id="footer_struk" name="footer_struk" rows="3" 
                                      placeholder="Contoh: Terima kasih atas kunjungan Anda!"><?php echo htmlspecialchars($settings['footer_struk'] ?? 'Terima kasih atas kunjungan Anda!'); ?></textarea>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Teks yang akan ditampilkan di bagian bawah struk
                            </small>
                        </div>

                        <hr style="margin: 30px 0; border: none; border-top: 2px solid #d4af37;">

                        <h3 style="margin-bottom: 20px; color: #d4af37; font-size: 20px; font-weight: bold;">Pengaturan Warna Tema</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_primary">Warna Primary</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_primary" name="warna_primary" 
                                           value="<?php echo htmlspecialchars($settings['warna_primary'] ?? '#667eea'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_primary'] ?? '#667eea'); ?>" 
                                           onchange="document.getElementById('warna_primary').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                                <small style="color: #666; display: block; margin-top: 5px;">
                                    Warna utama untuk tombol, link, dan elemen aktif
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="warna_secondary">Warna Secondary</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_secondary" name="warna_secondary" 
                                           value="<?php echo htmlspecialchars($settings['warna_secondary'] ?? '#764ba2'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_secondary'] ?? '#764ba2'); ?>" 
                                           onchange="document.getElementById('warna_secondary').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                                <small style="color: #666; display: block; margin-top: 5px;">
                                    Warna sekunder untuk gradient dan accent
                                </small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_sidebar">Warna Sidebar</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_sidebar" name="warna_sidebar" 
                                           value="<?php echo htmlspecialchars($settings['warna_sidebar'] ?? '#2c3e50'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_sidebar'] ?? '#2c3e50'); ?>" 
                                           onchange="document.getElementById('warna_sidebar').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="warna_sidebar_header">Warna Header Sidebar</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_sidebar_header" name="warna_sidebar_header" 
                                           value="<?php echo htmlspecialchars($settings['warna_sidebar_header'] ?? '#34495e'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_sidebar_header'] ?? '#34495e'); ?>" 
                                           onchange="document.getElementById('warna_sidebar_header').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_success">Warna Success</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_success" name="warna_success" 
                                           value="<?php echo htmlspecialchars($settings['warna_success'] ?? '#27ae60'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_success'] ?? '#27ae60'); ?>" 
                                           onchange="document.getElementById('warna_success').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="warna_danger">Warna Danger</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_danger" name="warna_danger" 
                                           value="<?php echo htmlspecialchars($settings['warna_danger'] ?? '#e74c3c'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_danger'] ?? '#e74c3c'); ?>" 
                                           onchange="document.getElementById('warna_danger').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_warning">Warna Warning</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_warning" name="warna_warning" 
                                           value="<?php echo htmlspecialchars($settings['warna_warning'] ?? '#f39c12'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_warning'] ?? '#f39c12'); ?>" 
                                           onchange="document.getElementById('warna_warning').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="warna_info">Warna Info</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="color" id="warna_info" name="warna_info" 
                                           value="<?php echo htmlspecialchars($settings['warna_info'] ?? '#3498db'); ?>" 
                                           style="width: 80px; height: 40px; border: none; border-radius: 5px; cursor: pointer;">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_info'] ?? '#3498db'); ?>" 
                                           onchange="document.getElementById('warna_info').value = this.value"
                                           style="flex: 1; padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px;">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                            <strong>Tips:</strong> Gunakan color picker untuk memilih warna atau masukkan kode hex (contoh: #667eea). 
                            Perubahan warna akan langsung diterapkan setelah menyimpan.
                        </div>

                        <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; margin-top: 20px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Simpan Pengaturan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    </div>

    <script>
        // Sync color picker dengan text input
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            colorInput.addEventListener('input', function() {
                const textInput = this.parentElement.querySelector('input[type="text"]');
                if (textInput) {
                    textInput.value = this.value;
                }
            });
        });

        // Sync text input dengan color picker
        document.querySelectorAll('input[type="text"]').forEach(textInput => {
            if (textInput.previousElementSibling && textInput.previousElementSibling.type === 'color') {
                textInput.addEventListener('input', function() {
                    const colorInput = this.parentElement.querySelector('input[type="color"]');
                    if (colorInput && /^#[0-9A-F]{6}$/i.test(this.value)) {
                        colorInput.value = this.value;
                    }
                });
            }
        });
    </script>
</body>
</html>

