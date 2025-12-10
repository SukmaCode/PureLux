<?php
class MixParfum {
    private $conn;
    private $table_name = "mix_parfum";
    private $detail_table = "detail_mix_parfum";

    public $id;
    public $kode_mix;
    public $nama_mix;
    public $deskripsi;
    public $harga_jual;
    public $stok;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET kode_mix=:kode_mix, nama_mix=:nama_mix, deskripsi=:deskripsi, 
                      harga_jual=:harga_jual, stok=:stok, is_active=:is_active";

        $stmt = $this->conn->prepare($query);

        $this->kode_mix = htmlspecialchars(strip_tags($this->kode_mix));
        $this->nama_mix = htmlspecialchars(strip_tags($this->nama_mix));
        $this->deskripsi = !empty($this->deskripsi) ? htmlspecialchars(strip_tags($this->deskripsi)) : null;
        $this->harga_jual = htmlspecialchars(strip_tags($this->harga_jual));
        $this->stok = htmlspecialchars(strip_tags($this->stok));
        $this->is_active = isset($this->is_active) ? htmlspecialchars(strip_tags($this->is_active)) : 1;

        $stmt->bindParam(':kode_mix', $this->kode_mix);
        $stmt->bindParam(':nama_mix', $this->nama_mix);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
        $stmt->bindParam(':harga_jual', $this->harga_jual);
        $stmt->bindParam(':stok', $this->stok);
        $stmt->bindParam(':is_active', $this->is_active);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function addDetail($mix_id, $parfum_id, $persentase, $jumlah_ml = 0) {
        $query = "INSERT INTO " . $this->detail_table . "
                  SET mix_parfum_id=:mix_parfum_id, parfum_id=:parfum_id, 
                      persentase=:persentase, jumlah_ml=:jumlah_ml";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':mix_parfum_id', $mix_id);
        $stmt->bindParam(':parfum_id', $parfum_id);
        $stmt->bindParam(':persentase', $persentase);
        $stmt->bindParam(':jumlah_ml', $jumlah_ml);

        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT m.*, 
                  (SELECT COUNT(*) FROM " . $this->detail_table . " WHERE mix_parfum_id = m.id) as jumlah_komposisi
                  FROM " . $this->table_name . " m
                  ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->kode_mix = $row['kode_mix'];
            $this->nama_mix = $row['nama_mix'];
            $this->deskripsi = $row['deskripsi'];
            $this->harga_jual = $row['harga_jual'];
            $this->stok = $row['stok'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    public function getDetailMix($mix_id) {
        $query = "SELECT dm.*, p.nama_parfum, p.kode_parfum
                  FROM " . $this->detail_table . " dm
                  LEFT JOIN parfum p ON dm.parfum_id = p.id
                  WHERE dm.mix_parfum_id = :mix_id
                  ORDER BY dm.persentase DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mix_id', $mix_id);
        $stmt->execute();

        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET nama_mix=:nama_mix, deskripsi=:deskripsi, harga_jual=:harga_jual, 
                      stok=:stok, is_active=:is_active
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->nama_mix = htmlspecialchars(strip_tags($this->nama_mix));
        $this->deskripsi = !empty($this->deskripsi) ? htmlspecialchars(strip_tags($this->deskripsi)) : null;
        $this->harga_jual = htmlspecialchars(strip_tags($this->harga_jual));
        $this->stok = htmlspecialchars(strip_tags($this->stok));
        $this->is_active = isset($this->is_active) ? htmlspecialchars(strip_tags($this->is_active)) : 1;

        $stmt->bindParam(':nama_mix', $this->nama_mix);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
        $stmt->bindParam(':harga_jual', $this->harga_jual);
        $stmt->bindParam(':stok', $this->stok);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete() {
        // Delete detail first (CASCADE will handle it, but explicit is better)
        $detail_query = "DELETE FROM " . $this->detail_table . " WHERE mix_parfum_id = :id";
        $detail_stmt = $this->conn->prepare($detail_query);
        $detail_stmt->bindParam(':id', $this->id);
        $detail_stmt->execute();

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function deleteDetail($detail_id) {
        $query = "DELETE FROM " . $this->detail_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $detail_id);
        return $stmt->execute();
    }

    public function generateKodeMix() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $count = $row['count'] + 1;
        $kode_mix = 'MIX' . date('Ymd') . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        return $kode_mix;
    }
}
?>

