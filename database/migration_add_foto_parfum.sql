-- Migration: Tambah kolom foto_parfum ke tabel parfum (jika belum ada)
-- Tanggal: 2025-12-17

USE pos_parfum;

-- Tambah kolom foto_parfum (nullable) agar form create bisa simpan tanpa upload foto
ALTER TABLE parfum
ADD COLUMN foto_parfum VARCHAR(255) NULL AFTER deskripsi;


