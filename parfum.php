<?php
require_once 'config/config.php';
requireRole(['admin', 'gudang']);

require_once 'models/Parfum.php';
require_once 'models/KategoriParfum.php';

$database = new Database();
$db = $database->getConnection();

$parfum = new Parfum($db);
$kategori = new KategoriParfum($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $parfum->kode_parfum = sanitizeInput($_POST['kode_parfum']);
                $parfum->nama_parfum = sanitizeInput($_POST['nama_parfum']);
                $parfum->kategori_id = sanitizeInput($_POST['kategori_id']);
                $parfum->satuan = sanitizeInput($_POST['satuan']);
                $parfum->harga_beli = sanitizeInput($_POST['harga_beli']);
                $parfum->harga_jual = sanitizeInput($_POST['harga_jual']);
                $parfum->diskon = sanitizeInput($_POST['diskon'] ?? 0);
                $parfum->stok = sanitizeInput($_POST['stok']);
                $parfum->stok_minimum = sanitizeInput($_POST['stok_minimum']);
                $parfum->tanggal_expired = sanitizeInput($_POST['tanggal_expired']);
                $parfum->deskripsi = sanitizeInput($_POST['deskripsi']);

                if ($parfum->create()) {
                    $message = 'Data parfum berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan data parfum!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $parfum->id = sanitizeInput($_POST['id']);
                $parfum->kode_parfum = sanitizeInput($_POST['kode_parfum']);
                $parfum->nama_parfum = sanitizeInput($_POST['nama_parfum']);
                $parfum->kategori_id = sanitizeInput($_POST['kategori_id']);
                $parfum->satuan = sanitizeInput($_POST['satuan']);
                $parfum->harga_beli = sanitizeInput($_POST['harga_beli']);
                $parfum->harga_jual = sanitizeInput($_POST['harga_jual']);
                $parfum->diskon = sanitizeInput($_POST['diskon'] ?? 0);
                $parfum->stok = sanitizeInput($_POST['stok']);
                $parfum->stok_minimum = sanitizeInput($_POST['stok_minimum']);
                $parfum->tanggal_expired = sanitizeInput($_POST['tanggal_expired']);
                $parfum->deskripsi = sanitizeInput($_POST['deskripsi']);

                if ($parfum->update()) {
                    $message = 'Data parfum berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui data parfum!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $parfum->id = sanitizeInput($_POST['id']);
                if ($parfum->delete()) {
                    $message = 'Data parfum berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus data parfum!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all parfum
$stmt = $parfum->readAll();

// Get all kategori for dropdown
$kategori_stmt = $kategori->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Parfum - <?php echo APP_NAME; ?></title>
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
    <div class="bg-white border-2 border-[#d4af37] rounded-xl shadow-[0_4px_20px_rgba(212,175,55,0.3)] m-5 min-h-[calc(100vh-40px)] flex">
        <!-- Sidebar -->
        <?php 
        $page = "parfum";
        $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; 
        ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto bg-white">
            <!-- Top Navigation -->
            <header class="flex justify-between items-center mb-10 fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-black mb-2">Data Parfum</h1>
                    <p class="text-gray-600">Kelola data parfum dengan tampilan elegan ✨</p>
                </div>

                <div class="flex items-center gap-4 p-3 pr-5 rounded-xl">
                    <div class="text-right">
                        <div class="font-semibold text-black"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="text-gray-600 text-sm"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold bg-gradient-to-br from-[#d4af37] to-[#d4af37]-light text-black shadow-[0_2px_8px_rgba(212,175,55,0.3)]">
                        <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="mb-5 p-4 rounded-lg border-l-4 <?php echo $message_type === 'error' ? 'bg-red-50 border-red-500 text-red-700' : 'bg-[#d4af37]/15 border-[#d4af37] text-[#d4af37]'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Add Parfum Form -->
            <div class="bg-white border-2 border-[#d4af37] rounded-xl p-6 mb-8 shadow-[0_4px_15px_rgba(0,0,0,0.1)]">
                <h2 class="text-2xl font-bold text-[#d4af37] mb-5 pb-3 border-b-2 border-[#d4af37]">Tambah Parfum Baru</h2>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="kode_parfum" class="block mb-2 font-semibold text-[#d4af37] text-sm">Kode parfum</label>
                            <input type="text" id="kode_parfum" name="kode_parfum" required 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                        </div>
                        <div>
                            <label for="nama_parfum" class="block mb-2 font-semibold text-[#d4af37] text-sm">Nama parfum</label>
                            <input type="text" id="nama_parfum" name="nama_parfum" required 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="kategori_id" class="block mb-2 font-semibold text-[#d4af37] text-sm">Kategori</label>
                            <select id="kategori_id" name="kategori_id" required 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                                <option value="">Pilih Kategori</option>
                                <?php while ($row = $kategori_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_kategori']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label for="satuan" class="block mb-2 font-semibold text-[#d4af37] text-sm">Satuan</label>
                            <input type="text" id="satuan" name="satuan" required placeholder="cth: tablet, botol, kapsul"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="harga_beli" class="block mb-2 font-semibold text-[#d4af37] text-sm">Harga Beli</label>
                            <input type="number" id="harga_beli" name="harga_beli" required min="0" step="100"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                        </div>
                        <div>
                            <label for="harga_jual" class="block mb-2 font-semibold text-[#d4af37] text-sm">Harga Jual</label>
                            <input type="number" id="harga_jual" name="harga_jual" required min="0" step="100"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="diskon" class="block mb-2 font-semibold text-[#d4af37] text-sm">Diskon Default (Rp)</label>
                            <input type="number" id="diskon" name="diskon" min="0" step="100" value="0"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="stok" class="block mb-2 font-semibold text-[#d4af37] text-sm">Stok</label>
                            <input type="number" id="stok" name="stok" required min="0"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                        </div>
                        <div>
                            <label for="stok_minimum" class="block mb-2 font-semibold text-[#d4af37] text-sm">Stok Minimum</label>
                            <input type="number" id="stok_minimum" name="stok_minimum" required min="0"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="tanggal_expired" class="block mb-2 font-semibold text-[#d4af37] text-sm">Tanggal Expired</label>
                            <input type="date" id="tanggal_expired" name="tanggal_expired"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="deskripsi" class="block mb-2 font-semibold text-[#d4af37] text-sm">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="3"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all"></textarea>
                    </div>

                    <button type="submit" 
                        class="px-6 py-3 bg-[#d4af37] text-black font-semibold rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(212,175,55,0.3)]">
                        Tambah Parfum
                    </button>
                </form>
            </div>

            <!-- Data Parfum Table -->
            <div class="bg-white border-2 border-[#d4af37] rounded-xl overflow-hidden shadow-[0_4px_15px_rgba(0,0,0,0.1)]">
                <div class="bg-[#d4af37] px-5 py-4">
                    <h3 class="text-xl font-bold text-black">Daftar Parfum</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-[#d4af37]/10">
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Kode</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Nama Parfum</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Kategori</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Satuan</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Harga Beli</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Harga Jual</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Diskon</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Stok</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Stok Min</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Expired</th>
                                <th class="px-4 py-4 text-left text-[#d4af37] font-semibold border-b-2 border-[#d4af37]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 text-black font-medium"><?php echo $row['kode_parfum']; ?></td>
                                <td class="px-4 py-4 text-black font-medium"><?php echo $row['nama_parfum']; ?></td>
                                <td class="px-4 py-4 text-black"><?php echo $row['nama_kategori']; ?></td>
                                <td class="px-4 py-4 text-black"><?php echo $row['satuan']; ?></td>
                                <td class="px-4 py-4 text-black"><?php echo formatCurrency($row['harga_beli']); ?></td>
                                <td class="px-4 py-4 text-black font-semibold"><?php echo formatCurrency($row['harga_jual']); ?></td>
                                <td class="px-4 py-4 text-black"><?php echo formatCurrency($row['diskon'] ?? 0); ?></td>
                                <td class="px-4 py-4">
                                    <span class="px-3 py-1 rounded-md text-xs font-semibold <?php echo $row['stok'] <= $row['stok_minimum'] ? 'bg-red-100 text-red-700' : 'bg-[#d4af37]/15 text-[#d4af37]'; ?>">
                                        <?php echo $row['stok']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-black"><?php echo $row['stok_minimum']; ?></td>
                                <td class="px-4 py-4 text-black">
                                    <?php 
                                    if ($row['tanggal_expired']) {
                                        $expired = strtotime($row['tanggal_expired']);
                                        $now = time();
                                        $days_left = ($expired - $now) / (60 * 60 * 24);
                                        
                                        if ($days_left < 0) {
                                            echo '<span class="px-3 py-1 rounded-md text-xs font-semibold bg-red-100 text-red-700">Expired</span>';
                                        } elseif ($days_left <= 30) {
                                            echo '<span class="px-3 py-1 rounded-md text-xs font-semibold bg-yellow-100 text-yellow-800">' . date('d/m/Y', $expired) . '</span>';
                                        } else {
                                            echo date('d/m/Y', $expired);
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex gap-2">
                                        <button onclick="editparfum(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                            class="px-4 py-2 bg-gradient-to-r from-[#d4af37] to-[#d4af37]-light text-black font-semibold rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(212,175,55,0.3)]">
                                            Edit
                                        </button>
                                        <button onclick="deleteparfum(<?php echo $row['id']; ?>)" 
                                            class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(239,68,68,0.3)]">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/70 z-50">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl border-2 border-[#d4af37] w-[90%] max-w-2xl max-h-[90vh] overflow-y-auto shadow-[0_8px_30px_rgba(212,175,55,0.3)] p-8">
            <h2 class="text-2xl font-bold text-[#d4af37] mb-5 pb-3 border-b-2 border-[#d4af37]">Edit Parfum</h2>
            <form method="POST" id="editForm" class="space-y-5">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="edit_kode_parfum" class="block mb-2 font-semibold text-[#d4af37] text-sm">Kode parfum</label>
                        <input type="text" id="edit_kode_parfum" name="kode_parfum" required 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                    </div>
                    <div>
                        <label for="edit_nama_parfum" class="block mb-2 font-semibold text-[#d4af37] text-sm">Nama parfum</label>
                        <input type="text" id="edit_nama_parfum" name="nama_parfum" required 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="edit_kategori_id" class="block mb-2 font-semibold text-[#d4af37] text-sm">Kategori</label>
                        <select id="edit_kategori_id" name="kategori_id" required 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                            <?php 
                            $kategori_stmt = $kategori->readAll();
                            while ($row = $kategori_stmt->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="edit_satuan" class="block mb-2 font-semibold text-[#d4af37] text-sm">Satuan</label>
                        <input type="text" id="edit_satuan" name="satuan" required 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="edit_harga_beli" class="block mb-2 font-semibold text-[#d4af37] text-sm">Harga Beli</label>
                        <input type="number" id="edit_harga_beli" name="harga_beli" required min="0" step="100"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                    </div>
                    <div>
                        <label for="edit_harga_jual" class="block mb-2 font-semibold text-[#d4af37] text-sm">Harga Jual</label>
                        <input type="number" id="edit_harga_jual" name="harga_jual" required min="0" step="100"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="edit_diskon" class="block mb-2 font-semibold text-[#d4af37] text-sm">Diskon Default (Rp)</label>
                        <input type="number" id="edit_diskon" name="diskon" min="0" step="100" value="0"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="edit_stok" class="block mb-2 font-semibold text-[#d4af37] text-sm">Stok</label>
                        <input type="number" id="edit_stok" name="stok" required min="0"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                    </div>
                    <div>
                        <label for="edit_stok_minimum" class="block mb-2 font-semibold text-[#d4af37] text-sm">Stok Minimum</label>
                        <input type="number" id="edit_stok_minimum" name="stok_minimum" required min="0"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="edit_tanggal_expired" class="block mb-2 font-semibold text-[#d4af37] text-sm">Tanggal Expired</label>
                        <input type="date" id="edit_tanggal_expired" name="tanggal_expired"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all">
                    </div>
                </div>

                <div>
                    <label for="edit_deskripsi" class="block mb-2 font-semibold text-[#d4af37] text-sm">Deskripsi</label>
                    <textarea id="edit_deskripsi" name="deskripsi" rows="3"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-black focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] transition-all"></textarea>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-[#d4af37] to-[#d4af37]-light text-black font-semibold rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(212,175,55,0.3)]">
                        Update
                    </button>
                    <button type="button" onclick="closeEditModal()" 
                        class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all hover:bg-gray-300">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editparfum(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_kode_parfum').value = data.kode_parfum;
            document.getElementById('edit_nama_parfum').value = data.nama_parfum;
            document.getElementById('edit_kategori_id').value = data.kategori_id;
            document.getElementById('edit_satuan').value = data.satuan;
            document.getElementById('edit_harga_beli').value = data.harga_beli;
            document.getElementById('edit_harga_jual').value = data.harga_jual;
            document.getElementById('edit_diskon').value = data.diskon || 0;
            document.getElementById('edit_stok').value = data.stok;
            document.getElementById('edit_stok_minimum').value = data.stok_minimum;
            document.getElementById('edit_tanggal_expired').value = data.tanggal_expired;
            document.getElementById('edit_deskripsi').value = data.deskripsi;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deleteparfum(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data parfum ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('editModal').onclick = function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
