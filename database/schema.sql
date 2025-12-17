-- Database schema untuk Sistem POS Minimart
-- Versi: 2.0
-- Tanggal: 2025-11-11
-- 
-- Fitur:
-- - Manajemen parfum dengan diskon per item
-- - Sistem penjualan dengan PPN dinamis
-- - Sistem pembelian
-- - Laporan penjualan dan pembelian
-- - Pengaturan aplikasi (PPN, informasi toko, dll)

CREATE DATABASE IF NOT EXISTS pos_parfum;
USE pos_parfum;

-- Tabel Users (Admin, Kasir, Gudang)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'kasir', 'gudang', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Kategori parfum
CREATE TABLE kategori_parfum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel parfum
CREATE TABLE parfum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_parfum VARCHAR(20) UNIQUE NOT NULL,
    nama_parfum VARCHAR(200) NOT NULL,
    kategori_id INT,
    satuan VARCHAR(20) NOT NULL,
    harga_beli DECIMAL(10,2) NOT NULL,
    harga_jual DECIMAL(10,2) NOT NULL,
    diskon DECIMAL(10,2) DEFAULT 0.00,
    stok INT DEFAULT 0,
    stok_minimum INT DEFAULT 0,
    tanggal_expired DATE,
    deskripsi TEXT,
    foto_parfum VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_parfum(id) ON DELETE SET NULL
);

-- Tabel vendor
CREATE TABLE vendor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_vendor VARCHAR(200) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel customer
CREATE TABLE customer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_customer VARCHAR(200) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Pembelian (Masuk dari Gudang)
CREATE TABLE pembelian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_faktur VARCHAR(50) UNIQUE NOT NULL,
    vendor_id INT,
    user_id INT,
    total_harga DECIMAL(12,2) NOT NULL,
    tanggal_pembelian DATE NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendor(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Detail Pembelian
CREATE TABLE detail_pembelian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pembelian_id INT,
    parfum_id INT,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (pembelian_id) REFERENCES pembelian(id) ON DELETE CASCADE,
    FOREIGN KEY (parfum_id) REFERENCES parfum(id) ON DELETE CASCADE
);

-- Tabel Penjualan (Kasir)
CREATE TABLE penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    customer_id INT,
    diskon DECIMAL(12,2) NOT NULL,
    ppn DECIMAL(12,2) NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL,
    total_bayar DECIMAL(12,2) NOT NULL,
    kembalian DECIMAL(12,2) NOT NULL,
    note TEXT,
    metode_transaksi VARCHAR(20) DEFAULT 'cash',
    nomor_rekening VARCHAR(50) NULL,
    tanggal_penjualan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE SET NULL
);

-- Tabel Detail Penjualan
CREATE TABLE detail_penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penjualan_id INT,
    parfum_id INT,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    diskon DECIMAL(10,2) DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (penjualan_id) REFERENCES penjualan(id) ON DELETE CASCADE,
    FOREIGN KEY (parfum_id) REFERENCES parfum(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS mix_parfum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_mix VARCHAR(20) UNIQUE NOT NULL,
    nama_mix VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    harga_jual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stok INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Detail Mix Parfum (komposisi campuran)
CREATE TABLE IF NOT EXISTS detail_mix_parfum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mix_parfum_id INT NOT NULL,
    parfum_id INT NOT NULL,
    persentase DECIMAL(5,2) NOT NULL COMMENT 'Persentase campuran (0-100)',
    jumlah_ml DECIMAL(10,2) DEFAULT 0 COMMENT 'Jumlah dalam ML',
    FOREIGN KEY (mix_parfum_id) REFERENCES mix_parfum(id) ON DELETE CASCADE,
    FOREIGN KEY (parfum_id) REFERENCES parfum(id) ON DELETE CASCADE,
    INDEX idx_mix_parfum (mix_parfum_id),
    INDEX idx_parfum (parfum_id)
);

-- Insert data awal
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@apotek.com', 'admin'),
('kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Utama', 'kasir@apotek.com', 'kasir'),
('gudang1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Gudang', 'gudang@apotek.com', 'gudang');

INSERT INTO kategori_parfum (nama_kategori, deskripsi) VALUES
('Floral', 'Aroma bunga yang lembut dan feminin seperti mawar, melati, dan lily.'),
('Fruity', 'Aroma buah segar seperti apel, berries, dan peach.'),
('Fresh / Citrus', 'Aroma segar seperti lemon, lime, bergamot, dan aquatic.'),
('Woody', 'Aroma kayu seperti sandalwood, cedarwood, dan vetiver.'),
('Oriental / Amber', 'Aroma hangat, spicy, dan mewah seperti amber, cinnamon, dan incense.'),
('Gourmand', 'Aroma manis seperti vanilla, caramel, chocolate, dan coffee.'),
('Aromatic', 'Aroma herbal dan segar seperti lavender, rosemary, dan sage.'),
('Musk', 'Aroma musky yang soft, bersih, dan elegan.'),
('Leather', 'Aroma kulit yang maskulin dan bold.'),
('Unisex', 'Kategori aroma yang cocok untuk pria maupun wanita.');

INSERT INTO vendor (nama_vendor, alamat, telepon, email) VALUES
('Mykonos Official', 'Jl. Raya Jakarta No. 123', '021-1234567', 'info@mykonos.com'),
('Jayrosse Official', 'Jl. Sudirman No. 456', '021-7654321', 'info@jarosse.com'),
('HMNS Indonesia', 'Jl. Kemang Raya No. 21, Jakarta Selatan', '021-88991234', 'support@hmns.id'),
('Evangeline Parfum', 'Jl. Gatot Subroto No. 15, Medan', '061-7788112', 'info@evangeline.co.id'),
('Carl & Claire Fragrance', 'Jl. Puri Indah Blok B3 No.11, Jakarta Barat', '021-99001212', 'care@carlandclaire.com'),
('Kahf Indonesia', 'Jl. TB Simatupang No. 45, Jakarta Selatan', '021-88330122', 'cs@kahf.com'),
('Wardah Beauty (Parfum Division)', 'Jl. Pulogadung No.19, Jakarta Timur', '021-55667788', 'parfum@wardahbeauty.com'),
('Vitalis Indonesia', 'Jl. Cikini Raya No.30, Jakarta Pusat', '021-76889922', 'info@vitalis.co.id'),
('Casablanca Perfume', 'Jl. Ahmad Yani No. 44, Bandung', '022-99112233', 'cs@casablanca.co.id'),
('Morris Perfume Indonesia', 'Jl. Gajah Mada No. 55, Surabaya', '031-77889911', 'info@morris.co.id'),
('Musk by Lilith', 'Jl. Raya Setiabudi No. 12, Bandung', '022-55778899', 'support@muskbylilith.com'),
('Alchemist Fragrance', 'Jl. Teuku Umar No. 23, Bali', '0361-778823', 'info@alchemistfragrance.com'),
('Zahra Perfume', 'Jl. Sisingamangaraja No. 123, Pekanbaru', '0761-557712', 'contact@zahraperfume.id'),
('Oullu Fragrance', 'Jl. Pangeran Antasari No. 29, Jakarta Selatan', '021-99887766', 'hello@oullu.id'),
('Odiva Perfume vendor', 'Jl. Rajawali No. 18, Surabaya', '031-55778123', 'admin@odiva.id'),
('Camellia Perfume', 'Jl. Veteran No. 77, Malang', '0341-889900', 'info@camellia.co.id'),
('Emperor Fragrance', 'Jl. Solo Baru No. 20, Solo', '0271-778899', 'cs@emperorfragrance.com'),
('Bali Alchemy Perfume', 'Jl. Sunset Road No. 10, Bali', '0361-665544', 'support@balialchemy.com');


-- Tabel Pengaturan
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunci VARCHAR(100) UNIQUE NOT NULL,
    nilai TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert pengaturan default
INSERT INTO pengaturan (kunci, nilai) VALUES
('nama_toko', 'PureLux Perfume'),
('alamat_toko', ''),
('telepon_toko', ''),
('email_toko', ''),
('ppn_persen', '10'),
('footer_struk', 'Terima kasih atas kunjungan Anda!'),
('warna_primary', '#667eea'),
('warna_secondary', '#764ba2'),
('warna_sidebar', '#2c3e50'),
('warna_sidebar_header', '#34495e'),
('warna_success', '#27ae60'),
('warna_danger', '#e74c3c'),
('warna_warning', '#f39c12'),
('warna_info', '#3498db');