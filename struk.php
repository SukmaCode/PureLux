<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Penjualan.php';
require_once 'models/Customer.php';
require_once 'models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

$penjualan = new Penjualan($db);
$customer = new Customer($db);
$pengaturan = new Pengaturan($db);

// Get settings
$nama_toko = $pengaturan->get('nama_toko') ?? APP_NAME;
$alamat_toko = $pengaturan->get('alamat_toko') ?? '';
$telepon_toko = $pengaturan->get('telepon_toko') ?? '';
$email_toko = $pengaturan->get('email_toko') ?? '';
$ppn_persen = floatval($pengaturan->get('ppn_persen') ?? 10);
$footer_struk = $pengaturan->get('footer_struk') ?? 'Terima kasih atas kunjungan Anda!';

$penjualan_id = sanitizeInput($_GET['id']);
$penjualan->id = $penjualan_id;

if (!$penjualan->readOne()) {
    header('Location: penjualan.php');
    exit();
}

// Get customer data if exists
$customer_name = '';
if (!empty($penjualan->customer_id)) {
    $customer->id = $penjualan->customer_id;
    if ($customer->readOne()) {
        $customer_name = $customer->nama_customer;
    }
}

$detail_stmt = $penjualan->getDetailPenjualan($penjualan_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            background: #ffffff;
            color: #333;
        }
        
        .receipt {
            width: 320px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #d4af37;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #d4af37;
        }
        
        .header h1 {
            font-size: 20px;
            margin: 0 0 8px 0;
            font-weight: bold;
            color: #d4af37;
            letter-spacing: 1px;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 10px;
            color: #666;
        }
        
        .transaction-info {
            border-bottom: 1px dashed #d4af37;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 11px;
        }
        
        .info-row span:first-child {
            color: #666;
            font-weight: 500;
        }
        
        .info-row span:last-child {
            color: #000;
            font-weight: 600;
        }
        
        .items {
            margin-bottom: 15px;
        }
        
        .item-header {
            display: grid;
            grid-template-columns: 2.5fr 0.8fr 1fr 1.2fr;
            gap: 5px;
            font-weight: bold;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 8px;
            margin-bottom: 8px;
            font-size: 10px;
            color: #d4af37;
            text-transform: uppercase;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 2.5fr 0.8fr 1fr 1.2fr;
            gap: 5px;
            margin-bottom: 8px;
            font-size: 11px;
            padding-bottom: 6px;
            border-bottom: 1px dotted #e0e0e0;
        }
        
        .item-row span:last-child {
            text-align: right;
            font-weight: 600;
            color: #000;
        }
        
        .item-row span:nth-child(2),
        .item-row span:nth-child(3) {
            text-align: center;
        }
        
        .summary {
            border-top: 2px dashed #d4af37;
            padding-top: 12px;
            margin-top: 10px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 11px;
        }
        
        .summary-row span:first-child {
            color: #666;
        }
        
        .summary-row span:last-child {
            color: #000;
            font-weight: 600;
        }
        
        .summary-row.total {
            font-weight: bold;
            border-top: 2px solid #d4af37;
            padding-top: 10px;
            margin-top: 8px;
            font-size: 13px;
        }
        
        .summary-row.total span:first-child {
            color: #d4af37;
            font-size: 14px;
        }
        
        .summary-row.total span:last-child {
            color: #d4af37;
            font-size: 14px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #d4af37;
            font-size: 10px;
            color: #666;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            .receipt {
                border: 1px solid #000;
                border-radius: 0;
                box-shadow: none;
                width: 100%;
                max-width: 300px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%);
            color: #000000;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.5);
        }
        
        .item-diskon {
            color: #e74c3c;
            font-size: 9px;
        }
        
        .qr-code-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px 0;
            border-top: 2px dashed #d4af37;
            border-bottom: 2px dashed #d4af37;
        }
        
        .qr-code-section h3 {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #d4af37;
            text-transform: uppercase;
        }
        
        .qr-code-container {
            display: inline-block;
            padding: 12px;
            background: white;
            border: 2px solid #d4af37;
            border-radius: 8px;
        }
        
        .qr-code-container img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto;
        }
        
        .qr-info {
            font-size: 10px;
            margin-top: 10px;
            color: #666;
            line-height: 1.4;
        }
        
        .note-section {
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px dashed #d4af37;
            font-size: 11px;
        }
        
        .note-section div:first-child {
            font-weight: bold;
            margin-bottom: 5px;
            color: #d4af37;
        }
        
        .method-badge {
            display: inline-block;
            padding: 3px 8px;
            background: rgba(212, 175, 55, 0.15);
            color: #d4af37;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn">Cetak Struk</button>
        <a href="penjualan.php" class="btn">Transaksi Baru</a>
        <a href="dashboard.php" class="btn">Dashboard</a>
    </div>

    <div class="receipt">
        <div class="header">
            <h1><?php echo htmlspecialchars($nama_toko); ?></h1>
            <p><?php if ($alamat_toko): ?><?php echo htmlspecialchars($alamat_toko); ?><br><?php endif; ?>
            <?php if ($telepon_toko): ?>Telp: <?php echo htmlspecialchars($telepon_toko); ?><br><?php endif; ?>
            <?php if ($email_toko): ?>Email: <?php echo htmlspecialchars($email_toko); ?><?php endif; ?></p>
        </div>
        
        <div class="transaction-info">
            <div class="info-row">
                <span>No. Transaksi:</span>
                <span><?php echo $penjualan->no_transaksi; ?></span>
            </div>
            <div class="info-row">
                <span>Tanggal:</span>
                <span><?php echo date('d/m/Y H:i:s', strtotime($penjualan->tanggal_penjualan)); ?></span>
            </div>
            <?php if (!empty($customer_name)): ?>
            <div class="info-row">
                <span>Customer:</span>
                <span><?php echo $customer_name; ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span>Kasir:</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
            <div class="info-row">
                <span>Metode:</span>
                <span><span class="method-badge"><?php echo strtoupper($penjualan->metode_transaksi ?? 'cash'); ?></span></span>
            </div>
            <?php if (!empty($penjualan->nomor_rekening)): ?>
            <div class="info-row">
                <span>No. Rekening:</span>
                <span><?php echo htmlspecialchars($penjualan->nomor_rekening); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="items">
            <div class="item-header">
                <span>Item</span>
                <span>Qty</span>
                <span>Harga</span>
                <span>Total</span>
            </div>
            
            <?php 
            $subtotal = 0;
            // Reset statement untuk fetch ulang
            $detail_stmt = $penjualan->getDetailPenjualan($penjualan_id);
            while ($row = $detail_stmt->fetch(PDO::FETCH_ASSOC)): 
                $item_diskon = isset($row['diskon']) ? floatval($row['diskon']) : 0;
                $item_total_before_diskon = $row['harga_satuan'] * $row['jumlah'];
                $item_total_after_diskon = $item_total_before_diskon - $item_diskon;
                $subtotal += max(0, $item_total_after_diskon);
            ?>
            <div class="item-row">
                <span><?php echo htmlspecialchars($row['nama_parfum']); ?></span>
                <span><?php echo $row['jumlah']; ?></span>
                <span><?php echo number_format($row['harga_satuan'], 0, ',', '.'); ?></span>
                <span>
                    <?php if ($item_diskon > 0): ?>
                        <span style="text-decoration: line-through; color: #999; font-size: 10px;">
                            <?php echo number_format($item_total_before_diskon, 0, ',', '.'); ?>
                        </span><br>
                        <span style="color: #e74c3c; font-weight: bold;">
                            -<?php echo number_format($item_diskon, 0, ',', '.'); ?>
                        </span><br>
                    <?php endif; ?>
                    <?php echo number_format(max(0, $item_total_after_diskon), 0, ',', '.'); ?>
                </span>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div class="summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
            </div>
            <?php if (!empty($penjualan->diskon) && $penjualan->diskon > 0): ?>
            <div class="summary-row">
                <span>Diskon:</span>
                <span>Rp <?php echo number_format($penjualan->diskon, 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row">
                <span>PPN (<?php echo $ppn_persen; ?>%):</span>
                <span>Rp <?php echo number_format($penjualan->ppn ?? 0, 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>Rp <?php echo number_format($penjualan->total_harga, 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Bayar:</span>
                <span>Rp <?php echo number_format($penjualan->total_bayar, 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Kembalian:</span>
                <span>Rp <?php echo number_format($penjualan->kembalian, 0, ',', '.'); ?></span>
            </div>
        </div>
        
        <?php 
        // QR code dummy untuk QRIS atau Transfer
        $show_qr = false;
        $qr_title = '';
        $metode_transaksi = strtolower(trim($penjualan->metode_transaksi ?? 'cash'));
        
        if (in_array($metode_transaksi, ['qris', 'transfer'])) {
            $show_qr = true;
            
            if ($metode_transaksi === 'qris') {
                $qr_title = 'QRIS Payment';
            } elseif ($metode_transaksi === 'transfer') {
                $qr_title = 'Transfer Payment';
            }
        }
        ?>
        
        <?php if ($show_qr): ?>
        <div class="qr-code-section">
            <h3><?php echo $qr_title; ?></h3>
            <div class="qr-code-container">
                <canvas id="qrcode"></canvas>
            </div>
            <div class="qr-info">
                <?php if (strtolower($penjualan->metode_transaksi) === 'qris'): ?>
                    Scan QR code untuk melakukan pembayaran
                <?php else: ?>
                    Scan QR code untuk informasi transfer
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($penjualan->note)): ?>
        <div class="note-section" style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #000;">
            <div style="font-weight: bold; margin-bottom: 5px;">Catatan:</div>
            <div><?php echo htmlspecialchars($penjualan->note); ?></div>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p><?php echo htmlspecialchars($footer_struk); ?></p>
            <p>parfum yang sudah dibeli tidak dapat dikembalikan</p>
        </div>
    </div>

    <?php if ($show_qr): ?>
    <script>
        // QR Code Dummy - menggunakan API online untuk generate QR code dummy
        window.onload = function() {
            const canvas = document.getElementById('qrcode');
            
            if (!canvas) {
                return;
            }
            
            // Data dummy untuk QR code
            // Untuk QRIS: dummy QRIS payment data
            // Untuk Transfer: dummy transfer info
            const qrDataDummy = <?php echo $metode_transaksi === 'qris' ? 
                json_encode('00020101021126680014ID.CO.QRIS.WWW01189360012345678900303UKE052400001530393654051000005802ID6009PureLuxPerfume62070703***6304') : 
                json_encode('BANK:BCA|REK:1234567890|A/N:PureLux Perfume|JUMLAH:100000|TRX:DUMMY'); ?>;
            
            // Gunakan API online untuk generate QR code (lebih reliable)
            const qrDataEncoded = encodeURIComponent(qrDataDummy);
            const img = document.createElement('img');
            img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + qrDataEncoded;
            img.alt = 'QR Code';
            img.style.display = 'block';
            img.style.margin = '0 auto';
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            
            // Replace canvas with img
            canvas.parentElement.replaceChild(img, canvas);
        };
    </script>
    <?php endif; ?>
</body>
</html>
