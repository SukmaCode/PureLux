<?php
require_once 'config/config.php';
requireRole(['admin', 'gudang']);

require_once 'models/Pembelian.php';
require_once 'models/Vendor.php';
require_once 'models/Parfum.php';

$database = new Database();
$db = $database->getConnection();

$pembelian = new Pembelian($db);
$vendor = new Vendor($db);
$parfum = new Parfum($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    $db->beginTransaction();
                    
                    // Create pembelian record
                    $pembelian->no_faktur = $pembelian->generateNoFaktur();
                    $pembelian->vendor_id = sanitizeInput($_POST['vendor_id']);
                    $pembelian->user_id = $_SESSION['user_id'];
                    $pembelian->total_harga = sanitizeInput($_POST['total_harga']);
                    $pembelian->tanggal_pembelian = sanitizeInput($_POST['tanggal_pembelian']);
                    $pembelian->status = 'pending';
                    
                    $pembelian_id = $pembelian->create();
                    
                    if (!$pembelian_id) {
                        throw new Exception('Gagal membuat pembelian');
                    }
                    
                    // Process detail pembelian
                    $items = json_decode($_POST['items'], true);
                    foreach ($items as $item) {
                        // Insert detail pembelian
                        $detail_query = "INSERT INTO detail_pembelian (pembelian_id, parfum_id, jumlah, harga_satuan, subtotal) 
                                         VALUES (:pembelian_id, :parfum_id, :jumlah, :harga_satuan, :subtotal)";
                        $detail_stmt = $db->prepare($detail_query);
                        $detail_stmt->bindParam(':pembelian_id', $pembelian_id);
                        $detail_stmt->bindParam(':parfum_id', $item['id']);
                        $detail_stmt->bindParam(':jumlah', $item['quantity']);
                        $detail_stmt->bindParam(':harga_satuan', $item['price']);
                        $detail_stmt->bindParam(':subtotal', $item['subtotal']);
                        
                        if (!$detail_stmt->execute()) {
                            throw new Exception('Gagal menyimpan detail pembelian');
                        }
                    }
                    
                    $db->commit();
                    $message = 'Pembelian berhasil ditambahkan!';
                    $message_type = 'success';
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
                break;
                
            case 'complete':
                $pembelian_id = sanitizeInput($_POST['id']);
                
                try {
                    $db->beginTransaction();
                    
                    // Update status to completed
                    $pembelian->updateStatus($pembelian_id, 'completed');
                    
                    // Update stock
                    $detail_stmt = $pembelian->getDetailPembelian($pembelian_id);
                    while ($row = $detail_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $parfum->updateStok($row['parfum_id'], $row['jumlah']);
                    }
                    
                    $db->commit();
                    $message = 'Pembelian berhasil diselesaikan!';
                    $message_type = 'success';
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all pembelian
$stmt = $pembelian->readAll();

// Get all vendor for dropdown
$vendor_stmt = $vendor->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembelian - <?php echo APP_NAME; ?></title>
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
        $page = "pembelian";
        $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-scroll bg-white">
            <!-- Top Navigation -->
            <header class="flex justify-between items-center mb-10 fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-black mb-2">Pembelian Parfum</h1>
                    <p class="text-gray-600">Kelola pembelian parfum dengan tampilan elegan ✨</p>
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

                <!-- Add Pembelian Form -->
                <div class="form-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <h2 style="color: #d4af37; font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Tambah Pembelian Baru</h2>
                    <form method="POST" id="pembelianForm">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="items" id="itemsInput">
                        <input type="hidden" name="total_harga" id="totalHargaInput">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="vendor_id">vendor</label>
                                <select id="vendor_id" name="vendor_id" required>
                                    <option value="">Pilih vendor</option>
                                    <?php while ($row = $vendor_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_vendor']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_pembelian">Tanggal Pembelian</label>
                                <input type="date" id="tanggal_pembelian" name="tanggal_pembelian" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="form-group">
                            <label>Items Pembelian</label>
                            <div id="itemsContainer">
                                <div class="item-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                    <select class="parfum-select" onchange="loadparfumInfo(this)">
                                        <option value="">Pilih parfum</option>
                                        <?php 
                                        $parfum_stmt = $parfum->readAll();
                                        while ($row = $parfum_stmt->fetch(PDO::FETCH_ASSOC)): 
                                        ?>
                                            <option value="<?php echo $row['id']; ?>" 
                                                    data-harga="<?php echo $row['harga_beli']; ?>"
                                                    data-nama="<?php echo $row['nama_parfum']; ?>">
                                                <?php echo $row['nama_parfum']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <input type="number" class="quantity-input" placeholder="Jumlah" min="1" onchange="calculateSubtotal(this)">
                                    <input type="number" class="price-input" placeholder="Harga" min="0" step="100" onchange="calculateSubtotal(this)">
                                    <input type="number" class="subtotal-input" placeholder="Subtotal" readonly>
                                    <button type="button" onclick="removeItem(this)" style="padding: 8px 16px; background: linear-gradient(135deg, #e74c3c, #c0392b); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(231, 76, 60, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Hapus</button>
                                </div>
                            </div>
                            <button type="button" onclick="addItem()" style="padding: 10px 20px; background: linear-gradient(135deg, #27ae60, #229954); color: #ffffff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(39, 174, 96, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Tambah Item</button>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Total Harga:</label>
                                <input type="text" id="totalDisplay" value="Rp 0" readonly style="font-weight: bold; font-size: 16px;">
                            </div>
                        </div>

                        <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; margin-top: 15px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Simpan Pembelian</button>
                    </form>
                </div>

                <!-- Data Pembelian Table -->
                <div class="table-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <div class="table-header" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); padding: 20px;">
                        <h3 class="table-title" style="color: #000000; font-size: 20px; font-weight: bold; margin: 0;">Daftar Pembelian</h3>
                    </div>
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(212, 175, 55, 0.1);">
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">No. Faktur</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Vendor</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Total Harga</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Tanggal</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Status</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr style="border-b: 1px solid #e0e0e0; transition: background 0.2s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#ffffff'">
                                <td style="padding: 15px; color: #000000; font-weight: 500;"><?php echo $row['no_faktur']; ?></td>
                                <td style="padding: 15px; color: #000000;"><?php echo $row['nama_vendor']; ?></td>
                                <td style="padding: 15px; color: #000000; font-weight: 600;"><?php echo formatCurrency($row['total_harga']); ?></td>
                                <td style="padding: 15px; color: #000000;"><?php echo date('d/m/Y', strtotime($row['tanggal_pembelian'])); ?></td>
                                <td style="padding: 15px;">
                                    <span class="badge <?php echo $row['status'] === 'completed' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="complete">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" style="padding: 8px 16px; background: linear-gradient(135deg, #27ae60, #229954); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin-right: 5px; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(39, 174, 96, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'" onclick="return confirm('Konfirmasi penerimaan parfum?')">
                                                Terima
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button onclick="viewDetail(<?php echo $row['id']; ?>)" 
                                            style="padding: 8px 16px; background: linear-gradient(135deg, #3498db, #2980b9); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(52, 152, 219, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Detail</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    </div>

    <script>
        let itemCount = 1;

        function addItem() {
            const container = document.getElementById('itemsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'item-row';
            newItem.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;';
            
            newItem.innerHTML = `
                <select class="parfum-select" onchange="loadparfumInfo(this)">
                    <option value="">Pilih parfum</option>
                    ${document.querySelector('.parfum-select').innerHTML}
                </select>
                <input type="number" class="quantity-input" placeholder="Jumlah" min="1" onchange="calculateSubtotal(this)">
                <input type="number" class="price-input" placeholder="Harga" min="0" step="100" onchange="calculateSubtotal(this)">
                <input type="number" class="subtotal-input" placeholder="Subtotal" readonly>
                <button type="button" onclick="removeItem(this)" class="btn btn-danger btn-sm">Hapus</button>
            `;
            
            container.appendChild(newItem);
            itemCount++;
        }

        function removeItem(button) {
            if (document.querySelectorAll('.item-row').length > 1) {
                button.parentElement.remove();
                calculateTotal();
            } else {
                alert('Minimal harus ada 1 item');
            }
        }

        function loadparfumInfo(select) {
            const option = select.options[select.selectedIndex];
            if (option.value) {
                const priceInput = select.parentElement.querySelector('.price-input');
                priceInput.value = option.dataset.harga;
                calculateSubtotal(select);
            }
        }

        function calculateSubtotal(element) {
            const row = element.parentElement;
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = quantity * price;
            
            row.querySelector('.subtotal-input').value = subtotal;
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            const items = [];
            
            document.querySelectorAll('.item-row').forEach(row => {
                const select = row.querySelector('.parfum-select');
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const subtotal = quantity * price;
                
                if (select.value && quantity > 0 && price > 0) {
                    total += subtotal;
                    items.push({
                        id: select.value,
                        quantity: quantity,
                        price: price,
                        subtotal: subtotal
                    });
                }
            });
            
            document.getElementById('totalDisplay').value = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('totalHargaInput').value = total;
            document.getElementById('itemsInput').value = JSON.stringify(items);
        }

        function viewDetail(id) {
            // Implement detail view modal or redirect
            alert('Detail pembelian ID: ' + id);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotal();
        });
    </script>
</body>
</html>
