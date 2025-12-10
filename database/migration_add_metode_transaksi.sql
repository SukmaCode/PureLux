-- Migration: Tambah kolom metode_transaksi dan nomor_rekening ke tabel penjualan
-- Tanggal: 2025-12-02

USE pos_parfum;

-- Tambah kolom metode_transaksi
ALTER TABLE penjualan 
ADD COLUMN metode_transaksi VARCHAR(20) DEFAULT 'cash' AFTER note;

-- Tambah kolom nomor_rekening
ALTER TABLE penjualan 
ADD COLUMN nomor_rekening VARCHAR(50) NULL AFTER metode_transaksi;

-- Update data existing jika ada
UPDATE penjualan SET metode_transaksi = 'cash' WHERE metode_transaksi IS NULL;

