-- Migration: Tambah tabel untuk fitur Mix de'Parfum
-- Tanggal: 2025-12-04

USE pos_parfum;

-- Tabel Mix Parfum (hasil campuran)
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

