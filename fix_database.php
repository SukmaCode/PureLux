<?php
/**
 * Script untuk memperbaiki database - menambahkan kolom yang hilang
 * Jalankan script ini sekali untuk menambahkan kolom metode_transaksi dan nomor_rekening
 */

require_once 'config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$success = false;
$errors = [];

try {
    // Cek apakah kolom metode_transaksi sudah ada
    $check_query = "SHOW COLUMNS FROM penjualan LIKE 'metode_transaksi'";
    $stmt = $db->prepare($check_query);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Tambah kolom metode_transaksi
        $alter_query = "ALTER TABLE penjualan 
                       ADD COLUMN metode_transaksi VARCHAR(20) DEFAULT 'cash' AFTER note";
        $db->exec($alter_query);
        $success = true;
        $errors[] = "✓ Kolom metode_transaksi berhasil ditambahkan";
    } else {
        $errors[] = "✓ Kolom metode_transaksi sudah ada";
    }
    
    // Cek apakah kolom nomor_rekening sudah ada
    $check_query2 = "SHOW COLUMNS FROM penjualan LIKE 'nomor_rekening'";
    $stmt2 = $db->prepare($check_query2);
    $stmt2->execute();
    
    if ($stmt2->rowCount() == 0) {
        // Tambah kolom nomor_rekening
        $alter_query2 = "ALTER TABLE penjualan 
                        ADD COLUMN nomor_rekening VARCHAR(50) NULL AFTER metode_transaksi";
        $db->exec($alter_query2);
        $success = true;
        $errors[] = "✓ Kolom nomor_rekening berhasil ditambahkan";
    } else {
        $errors[] = "✓ Kolom nomor_rekening sudah ada";
    }
    
    // Update data existing jika ada
    try {
        $update_query = "UPDATE penjualan SET metode_transaksi = 'cash' WHERE metode_transaksi IS NULL";
        $db->exec($update_query);
    } catch (PDOException $e) {
        // Ignore jika tidak ada data
    }
    
} catch (PDOException $e) {
    $errors[] = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Database - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="./src/output.css">
    <style>
        body {
            background-color: #000000;
            margin: 0;
            padding: 20px;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            border: 2px solid #d4af37;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
        }
        h1 {
            color: #d4af37;
            margin-bottom: 20px;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            background: rgba(212, 175, 55, 0.15);
            border-left: 4px solid #d4af37;
            color: #d4af37;
        }
        .error {
            background: rgba(139, 0, 0, 0.2);
            border-left: 4px solid #8b0000;
            color: #ff6b6b;
        }
        .btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%);
            color: #000000;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(212, 175, 55, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Perbaikan Database</h1>
        
        <?php foreach ($errors as $error): ?>
            <div class="message <?php echo strpos($error, '✓') !== false ? 'success' : 'error'; ?>">
                <?php echo $error; ?>
            </div>
        <?php endforeach; ?>
        
        <?php if ($success || (count($errors) > 0 && strpos($errors[0], '✓') !== false)): ?>
            <div class="message success">
                <strong>Database berhasil diperbaiki! Sekarang Anda bisa memproses transaksi.</strong>
            </div>
        <?php endif; ?>
        
        <a href="penjualan.php" class="btn">Kembali ke Halaman Penjualan</a>
    </div>
</body>
</html>

