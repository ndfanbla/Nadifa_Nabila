<?php
session_start();
include 'koneksi/koneksi.php';

// Debug mode - sementara
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==================== FUNGSI BARU UNTUK SISTEM UNIVERSAL ====================

// FUNGSI 1: Membuat tabel jika tidak ada
function createTableIfNotExists($koneksi, $table_name, $default_status) {
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE '$table_name'");
    
    if (mysqli_num_rows($check_table) == 0) {
        // Tentukan struktur tabel berdasarkan jenisnya
        if ($table_name == 'barang_di_ruangan' || $table_name == 'barang_di_gudang') {
            // Struktur untuk barang di ruangan/gudang (tanpa field service)
            $query = "CREATE TABLE $table_name (
                id INT AUTO_INCREMENT PRIMARY KEY,
                no_urut INT NOT NULL,
                nama_barang VARCHAR(100) NOT NULL,
                ruangan VARCHAR(100) NOT NULL,
                merek VARCHAR(100) NOT NULL,
                nomor_barang VARCHAR(100),
                kondisi ENUM('baik', 'rusak_ringan', 'tidak_bisa_diperbaiki') DEFAULT 'baik',
                keterangan TEXT,
                bukti_foto VARCHAR(255) DEFAULT NULL,
                status_barang ENUM('belum_kembali','sudah_kembali','di_ruangan','di_gudang') DEFAULT '$default_status',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            // Struktur untuk tabel service (belum_kembali_dari_service, sudah_kembali_dari_service)
            $query = "CREATE TABLE $table_name (
                id INT AUTO_INCREMENT PRIMARY KEY,
                no_urut INT NOT NULL,
                nama_barang VARCHAR(100) NOT NULL,
                ruangan VARCHAR(100) NOT NULL,
                merek VARCHAR(100) NOT NULL,
                tempat_service VARCHAR(100) NOT NULL,
                tanggal_diambil_dari_ruangan DATE,
                tanggal_service DATE NOT NULL,
                kelengkapan_barang TEXT,
                masalah TEXT NOT NULL,
                keterangan TEXT,
                nomor_barang VARCHAR(100),
                bukti_foto VARCHAR(255) DEFAULT NULL,
                tanggal_kembali DATE NOT NULL,
                kondisi_sebelum_service ENUM('baik', 'rusak_ringan', 'tidak_bisa_diperbaiki') DEFAULT 'baik',
                status_barang ENUM('belum_kembali','sudah_kembali','di_ruangan','di_gudang') DEFAULT '$default_status',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        
        return mysqli_query($koneksi, $query);
    }
    
    // Cek dan tambah kolom status_barang jika belum ada
    $check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM $table_name LIKE 'status_barang'");
    if (mysqli_num_rows($check_column) == 0) {
        $alter_query = "ALTER TABLE $table_name 
                       ADD COLUMN status_barang ENUM('belum_kembali','sudah_kembali','di_ruangan','di_gudang') DEFAULT '$default_status'";
        return mysqli_query($koneksi, $alter_query);
    }
    
    return true;
}

// Fungsi untuk membuat/update tabel belum_kembali_dari_service
function createTablebelumKembali($koneksi) {
    return createTableIfNotExists($koneksi, 'belum_kembali_dari_service', 'belum_kembali');
}

// Panggil fungsi untuk memastikan tabel ada
createTablebelumKembali($koneksi);

// TAMBAHAN: Fungsi universal untuk mendapatkan data by ID dari semua tabel
function getDataByIdUniversal($koneksi, $id, $status = 'belum_kembali') {
    $id = mysqli_real_escape_string($koneksi, $id);
    
    // Tentukan tabel berdasarkan status
    $table_name = '';
    switch($status) {
        case 'belum_kembali': $table_name = 'belum_kembali_dari_service'; break;
        case 'sudah_kembali': $table_name = 'sudah_kembali_dari_service'; break;
        case 'di_ruangan': $table_name = 'barang_di_ruangan'; break;
        case 'di_gudang': $table_name = 'barang_di_gudang'; break;
        default: return null;
    }
    
    // Pastikan tabel exists
    createTableIfNotExists($koneksi, $table_name, $status);
    
    $query = "SELECT * FROM $table_name WHERE id = $id";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// FUNGSI 2: Fungsi universal untuk menyimpan data dengan sistem berpindah tabel
function saveDataBarangUniversal($koneksi, $data, $foto = null) {
    // Debug log
    error_log("saveDataBarangUniversal called with status: " . ($data['status_barang'] ?? 'none'));
    
    // Jika ada ID (edit mode), cek status sebelumnya
    $old_status = 'belum_kembali';
    $old_table = '';
    if (!empty($data['id'])) {
        // Cari data lama di semua tabel untuk mengetahui status sebelumnya
        $tables = ['belum_kembali_dari_service', 'sudah_kembali_dari_service', 'barang_di_ruangan', 'barang_di_gudang'];
        foreach ($tables as $table) {
            $check_query = "SELECT * FROM $table WHERE id = " . intval($data['id']);
            $check_result = mysqli_query($koneksi, $check_query);
            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $old_data = mysqli_fetch_assoc($check_result);
                $old_status = $old_data['status_barang'] ?? 'belum_kembali';
                $old_table = $table;
                break;
            }
        }
    }
    
    // Tentukan tabel target berdasarkan status_barang yang baru
    $new_table_name = '';
    $new_default_status = '';
    $is_service_table = false;
    
    switch($data['status_barang']) {
        case 'belum_kembali':
            $new_table_name = 'belum_kembali_dari_service';
            $new_default_status = 'belum_kembali';
            $is_service_table = true;
            break;
        case 'sudah_kembali':
            $new_table_name = 'sudah_kembali_dari_service';
            $new_default_status = 'sudah_kembali';
            $is_service_table = true;
            break;
        case 'di_ruangan':
            $new_table_name = 'barang_di_ruangan';
            $new_default_status = 'di_ruangan';
            $is_service_table = false;
            break;
        case 'di_gudang':
            $new_table_name = 'barang_di_gudang';
            $new_default_status = 'di_gudang';
            $is_service_table = false;
            break;
        default:
            error_log("Invalid status_barang: " . $data['status_barang']);
            return false;
    }
    
    error_log("Old table: $old_table, Old status: $old_status, New table: $new_table_name, New status: " . $data['status_barang']);
    
    // Cek apakah tabel baru exists, jika tidak buat tabel
    if (!createTableIfNotExists($koneksi, $new_table_name, $new_default_status)) {
        error_log("Gagal membuat atau memeriksa tabel: $new_table_name");
        return false;
    }
    
    // Jika status berubah, hapus data dari tabel lama
    if (!empty($data['id']) && !empty($old_table) && $old_table != $new_table_name) {
        error_log("Status changed from $old_status to " . $data['status_barang'] . ". Deleting from $old_table");
        $delete_query = "DELETE FROM $old_table WHERE id = " . intval($data['id']);
        mysqli_query($koneksi, $delete_query);
        // Reset ID agar dibuat sebagai data baru di tabel yang berbeda
        $data['id'] = 0;
    }
    
    // Auto-generate nomor urut untuk tabel yang dipilih
    $no_urut = getNextNoUrutUniversal($koneksi, $new_table_name);

    // Jika edit dan ada nomor urut di data, gunakan yang sudah ada
    if (!empty($data['id']) && !empty($data['no_urut'])) {
        $no_urut = intval($data['no_urut']);
    }
    
    // Escape data dasar yang sama untuk semua tabel
    $id = isset($data['id']) ? intval($data['id']) : 0;
    $nama_barang = mysqli_real_escape_string($koneksi, $data['nama_barang']);
    $ruangan = mysqli_real_escape_string($koneksi, $data['ruangan']);
    $merek = mysqli_real_escape_string($koneksi, $data['merek']);
    $nomor_barang = mysqli_real_escape_string($koneksi, $data['nomor_barang'] ?? '');
    $keterangan = mysqli_real_escape_string($koneksi, $data['keterangan'] ?? '');
    $status_barang = mysqli_real_escape_string($koneksi, $data['status_barang']);
    
    $bukti_foto = $foto ? mysqli_real_escape_string($koneksi, $foto) : null;
    
    if ($is_service_table) {
        // Untuk tabel service (belum_kembali, sudah_kembali)
        $tempat_service = mysqli_real_escape_string($koneksi, $data['tempat_service']);
        $tanggal_diambil_dari_ruangan = !empty($data['tanggal_diambil_dari_ruangan']) ? 
            mysqli_real_escape_string($koneksi, $data['tanggal_diambil_dari_ruangan']) : null;
        $tanggal_service = mysqli_real_escape_string($koneksi, $data['tanggal_service']);
        $kelengkapan_barang = mysqli_real_escape_string($koneksi, $data['kelengkapan_barang'] ?? '');
        $masalah = mysqli_real_escape_string($koneksi, $data['masalah'] ?? '');
        $tanggal_kembali = mysqli_real_escape_string($koneksi, $data['tanggal_kembali']);

        // Tentukan field kondisi berdasarkan tabel
        if ($new_table_name == 'belum_kembali_dari_service') {
            $kondisi_field = mysqli_real_escape_string($koneksi, $data['kondisi_sebelum_service'] ?? 'baik');
        } else {
            $kondisi_field = mysqli_real_escape_string($koneksi, $data['kondisi_setelah_service'] ?? 'baik');
        }
        
        if (!empty($id)) {
            // UPDATE data service di tabel yang sama
            $query = "UPDATE $new_table_name SET 
                no_urut = '$no_urut',
                nama_barang = '$nama_barang',
                ruangan = '$ruangan',
                merek = '$merek',
                tempat_service = '$tempat_service',
                tanggal_diambil_dari_ruangan = " . ($tanggal_diambil_dari_ruangan ? "'$tanggal_diambil_dari_ruangan'" : "NULL") . ",
                tanggal_service = '$tanggal_service',
                kelengkapan_barang = '$kelengkapan_barang',
                masalah = '$masalah',
                keterangan = '$keterangan',
                nomor_barang = '$nomor_barang',
                tanggal_kembali = '$tanggal_kembali',
                status_barang = '$status_barang'";
            
            // Tambahkan field kondisi yang sesuai
            if ($new_table_name == 'belum_kembali_dari_service') {
                $query .= ", kondisi_sebelum_service = '$kondisi_field'";
            } else {
                // Untuk tabel sudah_kembali, pastikan kolomnya ada
                $check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM $new_table_name LIKE 'kondisi_setelah_service'");
                if (mysqli_num_rows($check_column) == 0) {
                    $alter_query = "ALTER TABLE $new_table_name ADD COLUMN kondisi_setelah_service ENUM('baik', 'rusak_ringan', 'tidak_bisa_diperbaiki') DEFAULT 'baik'";
                    mysqli_query($koneksi, $alter_query);
                }
                $query .= ", kondisi_setelah_service = '$kondisi_field'";
            }
            
            if ($foto) {
                $query .= ", bukti_foto = '$bukti_foto'";
            }
            
            $query .= " WHERE id = $id";
            
        } else {
            // INSERT data service baru
            if ($new_table_name == 'belum_kembali_dari_service') {
                $query = "INSERT INTO $new_table_name 
                          (no_urut, nama_barang, ruangan, merek, tempat_service, 
                           tanggal_diambil_dari_ruangan, tanggal_service, kelengkapan_barang, 
                           masalah, keterangan, nomor_barang, bukti_foto, 
                           tanggal_kembali, kondisi_sebelum_service, status_barang)
                          VALUES (
                          '$no_urut',
                          '$nama_barang',
                          '$ruangan',
                          '$merek',
                          '$tempat_service',
                          " . ($tanggal_diambil_dari_ruangan ? "'$tanggal_diambil_dari_ruangan'" : "NULL") . ",
                          '$tanggal_service',
                          '$kelengkapan_barang',
                          '$masalah',
                          '$keterangan',
                          '$nomor_barang',
                          " . ($bukti_foto ? "'$bukti_foto'" : "NULL") . ",
                          '$tanggal_kembali',
                          '$kondisi_field',
                          '$status_barang'
                          )";
            } else {
                // Untuk tabel sudah_kembali, pastikan kolomnya ada
                $check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM $new_table_name LIKE 'kondisi_setelah_service'");
                if (mysqli_num_rows($check_column) == 0) {
                    $alter_query = "ALTER TABLE $new_table_name ADD COLUMN kondisi_setelah_service ENUM('baik', 'rusak_ringan', 'tidak_bisa_diperbaiki') DEFAULT 'baik'";
                    mysqli_query($koneksi, $alter_query);
                }
                
                $query = "INSERT INTO $new_table_name 
                          (no_urut, nama_barang, ruangan, merek, tempat_service, 
                           tanggal_diambil_dari_ruangan, tanggal_service, kelengkapan_barang, 
                           masalah, keterangan, nomor_barang, bukti_foto, 
                           tanggal_kembali, kondisi_setelah_service, status_barang)
                          VALUES (
                          '$no_urut',
                          '$nama_barang',
                          '$ruangan',
                          '$merek',
                          '$tempat_service',
                          " . ($tanggal_diambil_dari_ruangan ? "'$tanggal_diambil_dari_ruangan'" : "NULL") . ",
                          '$tanggal_service',
                          '$kelengkapan_barang',
                          '$masalah',
                          '$keterangan',
                          '$nomor_barang',
                          " . ($bukti_foto ? "'$bukti_foto'" : "NULL") . ",
                          '$tanggal_kembali',
                          '$kondisi_field',
                          '$status_barang'
                          )";
            }
        }
    } else {
        // Untuk tabel barang di ruangan/gudang
        $kondisi = mysqli_real_escape_string($koneksi, $data['kondisi'] ?? 'baik');
        
        if (!empty($id)) {
            // UPDATE data barang di ruangan/gudang
            $query = "UPDATE $new_table_name SET 
                no_urut = '$no_urut',
                nama_barang = '$nama_barang',
                ruangan = '$ruangan',
                merek = '$merek',
                nomor_barang = '$nomor_barang',
                kondisi = '$kondisi',
                keterangan = '$keterangan',
                status_barang = '$status_barang'";
            
            if ($foto) {
                $query .= ", bukti_foto = '$bukti_foto'";
            }
            
            $query .= " WHERE id = $id";
            
        } else {
            // INSERT data barang di ruangan/gudang baru
            $query = "INSERT INTO $new_table_name 
                      (no_urut, nama_barang, ruangan, merek, nomor_barang, 
                       kondisi, keterangan, bukti_foto, status_barang)
                      VALUES (
                      '$no_urut',
                      '$nama_barang',
                      '$ruangan',
                      '$merek',
                      '$nomor_barang',
                      '$kondisi',
                      '$keterangan',
                      " . ($bukti_foto ? "'$bukti_foto'" : "NULL") . ",
                      '$status_barang'
                      )";
        }
    }
    
    error_log("SQL Query: " . $query);
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        error_log("Database Error: " . mysqli_error($koneksi));
        return false;
    }
    return true;
}

// Fungsi untuk mendapatkan nomor urut universal
function getNextNoUrutUniversal($koneksi, $table_name) {
    $query = "SELECT MAX(no_urut) as max_urut FROM $table_name";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return ($row['max_urut'] ?? 0) + 1;
    }
    return 1;
}

// ==================== FUNGSI YANG SUDAH ADA (DIPERBAIKI) ====================

// Fungsi untuk mendapatkan nomor urut berikutnya
function getNextNoUrut($koneksi) {
    $query = "SELECT MAX(no_urut) as max_urut FROM belum_kembali_dari_service";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return ($row['max_urut'] ?? 0) + 1;
    }
    return 1;
}

// FUNGSI BARU: Untuk mendapatkan semua data berdasarkan status
function getDataByStatus($koneksi, $status) {
    $table_name = '';
    switch($status) {
        case 'belum_kembali': $table_name = 'belum_kembali_dari_service'; break;
        case 'sudah_kembali': $table_name = 'sudah_kembali_dari_service'; break;
        case 'di_ruangan': $table_name = 'barang_di_ruangan'; break;
        case 'di_gudang': $table_name = 'barang_di_gudang'; break;
        default: return [];
    }
    
    // Pastikan tabel exists
    createTableIfNotExists($koneksi, $table_name, $status);
    
    $query = "SELECT * FROM $table_name ORDER BY no_urut ASC";
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        error_log("Error getting data from $table_name: " . mysqli_error($koneksi));
        return [];
    }
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Fungsi kompatibilitas
function getAllDataByStatus($koneksi, $status = 'belum_kembali') {
    return getDataByStatus($koneksi, $status);
}

// Fungsi untuk menyimpan data (legacy)
function savebelumKembaliService($koneksi, $data, $foto = null) {
    // Escape semua input
    $id = isset($data['id']) ? mysqli_real_escape_string($koneksi, $data['id']) : '';
    
    // Auto-generate nomor urut untuk data baru
    if (empty($id)) {
        $no_urut = getNextNoUrut($koneksi);
    } else {
        $no_urut = mysqli_real_escape_string($koneksi, $data['no_urut']);
    }
    
    $nama_barang = mysqli_real_escape_string($koneksi, $data['nama_barang']);
    $ruangan = mysqli_real_escape_string($koneksi, $data['ruangan']);
    $merek = mysqli_real_escape_string($koneksi, $data['merek']);
    $tempat_service = mysqli_real_escape_string($koneksi, $data['tempat_service']);
    $tanggal_diambil_dari_ruangan = isset($data['tanggal_diambil_dari_ruangan']) && !empty($data['tanggal_diambil_dari_ruangan']) ? 
        mysqli_real_escape_string($koneksi, $data['tanggal_diambil_dari_ruangan']) : null;
    $tanggal_service = mysqli_real_escape_string($koneksi, $data['tanggal_service']);
    $kelengkapan_barang = mysqli_real_escape_string($koneksi, $data['kelengkapan_barang'] ?? '');
    $masalah = mysqli_real_escape_string($koneksi, $data['masalah'] ?? '');
    $keterangan = mysqli_real_escape_string($koneksi, $data['keterangan'] ?? '');
    $nomor_barang = mysqli_real_escape_string($koneksi, $data['nomor_barang'] ?? '');
    $tanggal_kembali = mysqli_real_escape_string($koneksi, $data['tanggal_kembali']);
    $kondisi_sebelum_service = mysqli_real_escape_string($koneksi, $data['kondisi_sebelum_service'] ?? 'baik');
    $bukti_foto = $foto ? mysqli_real_escape_string($koneksi, $foto) : null;
    
    if (!empty($id)) {
        // UPDATE data
        $query = "UPDATE belum_kembali_dari_service SET 
            no_urut = '$no_urut',
            nama_barang = '$nama_barang',
            ruangan = '$ruangan',
            merek = '$merek',
            tempat_service = '$tempat_service',
            tanggal_diambil_dari_ruangan = " . ($tanggal_diambil_dari_ruangan ? "'$tanggal_diambil_dari_ruangan'" : "NULL") . ",
            tanggal_service = '$tanggal_service',
            kelengkapan_barang = '$kelengkapan_barang',
            masalah = '$masalah',
            keterangan = '$keterangan',
            nomor_barang = '$nomor_barang',
            tanggal_kembali = '$tanggal_kembali',
            kondisi_sebelum_service = '$kondisi_sebelum_service'";
        
        // Tambahkan foto jika ada
        if ($foto) {
            $query .= ", bukti_foto = '$bukti_foto'";
        }
        
        $query .= " WHERE id = $id";
        
    } else {
        // INSERT data baru
        $query = "INSERT INTO belum_kembali_dari_service 
                  (no_urut, nama_barang, ruangan, merek, tempat_service, tanggal_diambil_dari_ruangan, 
                   tanggal_service, kelengkapan_barang, masalah, keterangan, nomor_barang, bukti_foto, 
                   tanggal_kembali, kondisi_sebelum_service)
                  VALUES (
                  '$no_urut',
                  '$nama_barang',
                  '$ruangan',
                  '$merek',
                  '$tempat_service',
                  " . ($tanggal_diambil_dari_ruangan ? "'$tanggal_diambil_dari_ruangan'" : "NULL") . ",
                  '$tanggal_service',
                  '$kelengkapan_barang',
                  '$masalah',
                  '$keterangan',
                  '$nomor_barang',
                  " . ($bukti_foto ? "'$bukti_foto'" : "NULL") . ",
                  '$tanggal_kembali',
                  '$kondisi_sebelum_service'
                  )";
    }
    
    $result = mysqli_query($koneksi, $query);
    if (!$result) {
        error_log("Database Error: " . mysqli_error($koneksi));
    }
    return $result;
}

// Fungsi universal untuk menghapus data
function deleteDataUniversal($koneksi, $id, $status = 'belum_kembali') {
    // Tentukan tabel berdasarkan status
    $table_name = '';
    switch($status) {
        case 'belum_kembali': $table_name = 'belum_kembali_dari_service'; break;
        case 'sudah_kembali': $table_name = 'sudah_kembali_dari_service'; break;
        case 'di_ruangan': $table_name = 'barang_di_ruangan'; break;
        case 'di_gudang': $table_name = 'barang_di_gudang'; break;
        default: return false;
    }
    
    // Ambil data untuk menghapus foto jika ada
    $data = getDataByIdUniversal($koneksi, $id, $status);
    if ($data && !empty($data['bukti_foto'])) {
        $file_path = 'uploads/' . $data['bukti_foto'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $id = mysqli_real_escape_string($koneksi, $id);
    $query = "DELETE FROM $table_name WHERE id = $id";
    return mysqli_query($koneksi, $query);
}

// Fungsi untuk menghapus data (legacy)
function deletebelumKembaliService($koneksi, $id) {
    return deleteDataUniversal($koneksi, $id, 'belum_kembali');
}

// Fungsi kompatibilitas
function getbelumKembaliById($koneksi, $id) {
    return getDataByIdUniversal($koneksi, $id, 'belum_kembali');
}

function getStatistikUniversal($koneksi, $status = 'belum_kembali') {
    $data = getDataByStatus($koneksi, $status);
    
    $statistik = [
        'total' => count($data),
        'baik' => 0,
        'rusak_ringan' => 0,
        'tidak_bisa_diperbaiki' => 0,
        'presentase_baik' => 0,
        'presentase_rusak_ringan' => 0,
        'presentase_tidak_bisa' => 0
    ];
    
    foreach ($data as $item) {
        $kondisi = $item['kondisi_sebelum_service'] ?? $item['kondisi'] ?? 'baik';
        switch ($kondisi) {
            case 'baik': $statistik['baik']++; break;
            case 'rusak_ringan': $statistik['rusak_ringan']++; break;
            case 'tidak_bisa_diperbaiki': $statistik['tidak_bisa_diperbaiki']++; break;
        }
    }
    
    // Hitung presentase
    if ($statistik['total'] > 0) {
        $statistik['presentase_baik'] = round(($statistik['baik'] / $statistik['total']) * 100, 2);
        $statistik['presentase_rusak_ringan'] = round(($statistik['rusak_ringan'] / $statistik['total']) * 100, 2);
        $statistik['presentase_tidak_bisa'] = round(($statistik['tidak_bisa_diperbaiki'] / $statistik['total']) * 100, 2);
    }
    
    return $statistik;
}

// ==================== PROSES FORM SUBMIT YANG DIPERBAIKI ====================

// Proses Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['simpan'])) {
        $foto_name = null;
        
        // Proses upload foto
        if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_tmp = $_FILES['bukti_foto']['tmp_name'];
            $file_name = $_FILES['bukti_foto']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validasi ekstensi file
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($file_ext, $allowed_ext)) {
                // Generate nama file unik
                $foto_name = uniqid() . '_' . time() . '.' . $file_ext;
                $file_path = $upload_dir . $foto_name;
                
                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Jika edit dan ada foto lama, hapus foto lama
                    if (!empty($_POST['id']) && !empty($_POST['foto_lama'])) {
                        $old_file = $upload_dir . $_POST['foto_lama'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                } else {
                    $_SESSION['error'] = "Gagal mengupload foto!";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            } else {
                $_SESSION['error'] = "Format file tidak didukung! Hanya JPG, JPEG, PNG, GIF, dan WEBP yang diizinkan.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        } else {
            // Jika tidak ada foto baru, gunakan foto lama (untuk edit)
            $foto_name = $_POST['foto_lama'] ?? null;
        }
        
        // Validasi data yang required
        $required_fields = ['nama_barang', 'ruangan', 'merek', 'tempat_service', 'tanggal_service', 'tanggal_kembali', 'status_barang'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Field " . ucfirst(str_replace('_', ' ', $field)) . " harus diisi!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        }
        
        // Debug: Cek data yang dikirim
        error_log("POST Data: " . print_r($_POST, true));
        
        // Persiapkan data untuk disimpan
        $data = [
            'id' => $_POST['id'] ?? '',
            'no_urut' => $_POST['no_urut'] ?? '',
            'nama_barang' => $_POST['nama_barang'],
            'ruangan' => $_POST['ruangan'],
            'merek' => $_POST['merek'],
            'tempat_service' => $_POST['tempat_service'],
            'tanggal_diambil_dari_ruangan' => $_POST['tanggal_diambil_dari_ruangan'] ?? '',
            'tanggal_service' => $_POST['tanggal_service'],
            'kelengkapan_barang' => $_POST['kelengkapan_barang'] ?? '',
            'masalah' => $_POST['masalah'] ?? '',
            'keterangan' => $_POST['keterangan'] ?? '',
            'nomor_barang' => $_POST['nomor_barang'] ?? '',
            'tanggal_kembali' => $_POST['tanggal_kembali'],
            'status_barang' => $_POST['status_barang'],
            'kondisi_sebelum_service' => $_POST['kondisi_sebelum_service'] ?? 'baik'
        ];
        
        error_log("Data to save: " . print_r($data, true));
        
        // Gunakan fungsi universal untuk menyimpan data
        if (saveDataBarangUniversal($koneksi, $data, $foto_name)) {
            $_SESSION['message'] = !empty($data['id']) ? "Data berhasil diupdate!" : "Data berhasil disimpan!";
            
            // Redirect ke halaman yang sesuai berdasarkan status_barang
            $status_barang = $data['status_barang'];
            switch($status_barang) {
                case 'belum_kembali':
                    header("Location: ds_belum_kembali.php");
                    break;
                case 'sudah_kembali':
                    header("Location: ds_kembali.php");
                    break;
                case 'di_ruangan':
                    header("Location: diruangan.php");
                    break;
                case 'di_gudang':
                    header("Location: di_gudang.php");
                    break;
                default:
                    header("Location: ds_belum_kembali.php");
            }
            exit;
        } else {
            $_SESSION['error'] = "Gagal menyimpan data. Silakan coba lagi.";
            error_log("Save data failed for status: " . $data['status_barang']);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if (is_numeric($id)) {
        if (deletebelumKembaliService($koneksi, $id)) {
            $_SESSION['message'] = "Data berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = "ID tidak valid!";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil data untuk ditampilkan - PERBAIKAN: Gunakan fungsi universal
$data_belum_kembali = getAllDataByStatus($koneksi, 'belum_kembali');
$statistik = getStatistikUniversal($koneksi, 'belum_kembali');

// Cek apakah sedang edit atau lihat detail
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_data = getDataByIdUniversal($koneksi, $_GET['edit'], 'belum_kembali');
}

$detail_data = null;
if (isset($_GET['detail']) && is_numeric($_GET['detail'])) {
    $detail_data = getbelumKembaliById($koneksi, $_GET['detail']);
}

// Dapatkan nomor urut berikutnya untuk form tambah
$next_no_urut = getNextNoUrut($koneksi);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Data belum Kembali dari Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    /* CSS TETAP SAMA PERSIS SEPERTI ASLINYA */
    :root {
        --pastel-brown: #d7ccc8;
        --pastel-cream: #f5f0e6;
        --pastel-beige: #e6d5c3;
        --pastel-taupe: #c8b6a6;
        --pastel-blue: #bbdefb;
        --pastel-green: #c8e6c9;
        --soft-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --header-gradient: linear-gradient(135deg, #d7ccc8 0%, #e6d5c3 100%);
        --sidebar-bg: #5d4037;
        --sidebar-text: #efebe9;
        --sidebar-active: #8d6e63;
        --main-bg: #f9f7f7;
        --text-color: #5d4037;
        --text-light: #8d6e63;
    }

    * {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background-color: var(--main-bg);
        color: var(--text-color);
        height: 100%;
        overflow-x: hidden;
    }

    a {
        text-decoration: none;
    }

    /* SIDEBAR */
    #sidebar {
        position: fixed;
        width: 260px;
        height: 100%;
        background: var(--sidebar-bg);
        color: var(--sidebar-text);
        transition: all 0.3s;
        z-index: 100;
        top: 0;
        left: 0;
    }

    #sidebar .brand {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        color: var(--sidebar-text);
        font-size: 1.2rem;
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    #sidebar .brand i {
        font-size: 1.5rem;
        margin-right: 0.5rem;
    }

    #sidebar .side-menu {
        margin: 1rem 0;
    }

    #sidebar .side-menu li {
        list-style: none;
        margin: 0.5rem 0;
        padding: 0.5rem 1.5rem;
        transition: all 0.3s;
    }

    #sidebar .side-menu li a {
        display: flex;
        align-items: center;
        color: var(--sidebar-text);
        font-size: 0.9rem;
    }

    #sidebar .side-menu li a i {
        font-size: 1.2rem;
        margin-right: 1rem;
    }

    #sidebar .side-menu li a .text {
        flex: 1;
    }

    #sidebar .side-menu li.active {
        background: var(--sidebar-active);
        border-radius: 0 30px 30px 0;
    }

    #sidebar .side-menu li:hover:not(.active) {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 0 30px 30px 0;
    }

    #sidebar .side-menu.top {
        margin-top: 2rem;
    }

    /* CONTENT */
    #content {
        margin-left: 260px;
        min-height: 100vh;
        transition: all 0.3s;
    }

    /* NAVBAR */
    nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: white;
        box-shadow: var(--soft-shadow);
        position: fixed;
        width: calc(100% - 260px);
        top: 0;
        z-index: 99;
    }

    nav .nav-link {
        color: var(--text-color);
        font-weight: 500;
    }

    nav form {
        display: flex;
        align-items: center;
        background: var(--pastel-cream);
        border-radius: 30px;
        padding: 0.5rem 1rem;
        width: 40%;
    }

    nav form input {
        border: none;
        background: transparent;
        width: 100%;
        padding: 0.5rem;
        outline: none;
        color: var(--text-color);
    }

    nav form .search-btn {
        border: none;
        background: transparent;
        cursor: pointer;
        color: var(--text-light);
    }

    .switch-mode {
        display: block;
        width: 40px;
        height: 20px;
        background: var(--pastel-taupe);
        border-radius: 20px;
        position: relative;
        cursor: pointer;
    }

    .switch-mode::before {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        background: white;
        border-radius: 50%;
        top: 2px;
        left: 2px;
        transition: all 0.3s;
    }

    #switch-mode:checked+.switch-mode::before {
        left: 22px;
    }

    .notification {
        position: relative;
        color: var(--text-color);
    }

    .notification .num {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ff6b6b;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
    }

    /* MAIN */
    main {
        margin-top: 70px;
        padding: 2rem 1.5rem;
        background: var(--main-bg);
        min-height: calc(100vh - 70px);
    }

    .head-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .head-title h1 {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: var(--text-color);
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        list-style: none;
        margin-top: 0.5rem;
    }

    .breadcrumb li {
        margin-right: 0.5rem;
    }

    .breadcrumb li a {
        color: var(--text-light);
        font-size: 0.9rem;
    }

    .breadcrumb li a.active {
        color: var(--text-color);
        font-weight: 500;
    }

    .breadcrumb li i {
        font-size: 0.8rem;
        color: var(--text-light);
    }

    .btn-download {
        display: flex;
        align-items: center;
        background: var(--pastel-taupe);
        color: var(--text-color);
        padding: 0.7rem 1.5rem;
        border-radius: 30px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-download:hover {
        background: var(--pastel-brown);
        transform: translateY(-2px);
    }

    .btn-download i {
        margin-right: 0.5rem;
    }

    /* BOX INFO */
    .box-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .box-info li {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        box-shadow: var(--soft-shadow);
        display: flex;
        align-items: center;
        transition: all 0.3s;
    }

    .box-info li:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .box-info li i {
        font-size: 2rem;
        color: var(--pastel-taupe);
        margin-right: 1rem;
    }

    .box-info li .text h3 {
        font-size: 1.5rem;
        color: var(--text-color);
    }

    .box-info li .text p {
        color: var(--text-light);
        font-size: 0.9rem;
    }

    /* CHART CONTAINER */
    .chart-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .chart-box {
        background: white;
        border-radius: 15px;
        box-shadow: var(--soft-shadow);
        padding: 1.5rem;
    }

    .chart-box .head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .chart-box .head h3 {
        font-family: 'Playfair Display', serif;
        color: var(--text-color);
    }

    .chart-box .head i {
        color: var(--text-light);
    }

    .chart-content {
        height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .progress-chart {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: conic-gradient(#4caf50 0% <?=$statistik['presentase_baik'] ?? 0 ?>%,
                #ff9800 <?=$statistik['presentase_baik'] ?? 0 ?>% <?=($statistik['presentase_baik'] ?? 0) + ($statistik['presentase_rusak_ringan'] ?? 0) ?>%,
                #f44336 <?=($statistik['presentase_baik'] ?? 0) + ($statistik['presentase_rusak_ringan'] ?? 0) ?>% 100%);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .progress-chart::before {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        background: white;
        border-radius: 50%;
    }

    .chart-center {
        position: relative;
        z-index: 1;
        text-align: center;
    }

    .chart-center .percentage {
        font-size: 2rem;
        font-weight: bold;
        color: var(--text-color);
    }

    .chart-center .label {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .chart-legend {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 3px;
    }

    .legend-text {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    /* TABLE DATA - PERBAIKAN UTAMA */
    .table-data {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .order {
        background: white;
        border-radius: 15px;
        box-shadow: var(--soft-shadow);
        padding: 1.5rem;
        overflow-x: auto;
    }

    .head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .head h3 {
        font-family: 'Playfair Display', serif;
        color: var(--text-color);
    }

    .head i {
        color: var(--text-light);
        cursor: pointer;
        margin-left: 1rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    table th {
        text-align: left;
        padding: 1rem 0.8rem;
        color: var(--text-light);
        font-weight: 500;
        border-bottom: 1px solid var(--pastel-cream);
        white-space: nowrap;
    }

    table td {
        padding: 1rem 0.8rem;
        border-bottom: 1px solid var(--pastel-cream);
        vertical-align: top;
    }

    table tr:last-child td {
        border-bottom: none;
    }

    table tr:hover {
        background-color: var(--pastel-cream);
    }

    .status {
        padding: 0.4rem 0.8rem;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
        text-align: center;
        min-width: 100px;
    }

    .status.baik {
        background: #e3f9e5;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    .status.rusak_ringan {
        background: #fff3e0;
        color: #ef6c00;
        border: 1px solid #ffcc80;
    }

    .status.tidak_bisa_diperbaiki {
        background: #ffebee;
        color: #d32f2f;
        border: 1px solid #ffcdd2;
    }

    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-warning {
        background: #ff8f00;
        color: white;
    }

    .btn-success {
        background: #2e7d32;
        color: white;
    }

    .btn-danger {
        background: #d32f2f;
        color: white;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-warning:hover {
        background: #e65100;
    }

    .btn-success:hover {
        background: #1b5e20;
    }

    .btn-danger:hover {
        background: #b71c1c;
    }

    /* Damage specific styles */
    .container-detail {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: var(--soft-shadow);
        margin-bottom: 2rem;
    }

    .header-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        color: var(--text-color);
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--pastel-taupe);
        padding-bottom: 0.5rem;
    }

    .detail-card {
        display: flex;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--pastel-cream);
    }

    .detail-card:last-child {
        border-bottom: none;
    }

    .label {
        width: 200px;
        font-weight: 500;
        color: var(--text-color);
    }

    .value {
        flex: 1;
        color: var(--text-light);
    }

    .btn-back {
        display: inline-block;
        margin-top: 1.5rem;
        padding: 0.5rem 1.5rem;
        background: var(--pastel-taupe);
        color: var(--text-color);
        border-radius: 30px;
        transition: all 0.3s;
    }

    .btn-back:hover {
        background: var(--pastel-brown);
    }

    .card-custom {
        background: white;
        border-radius: 15px;
        box-shadow: var(--soft-shadow);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .card-custom .title {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        color: var(--text-color);
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--pastel-taupe);
        padding-bottom: 0.5rem;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .form-group {
        flex: 1;
        min-width: 200px;
    }

    .form-control {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid var(--pastel-cream);
        border-radius: 8px;
        margin-top: 0.3rem;
        background: var(--pastel-cream);
        color: var(--text-color);
    }

    .form-control:focus {
        outline: none;
        border-color=var(--pastel-taupe);
        background: white;
    }

    .btn-custom {
        padding: 0.7rem 1.5rem;
        border-radius: 30px;
        font-weight: 500;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-secondary {
        background: var(--pastel-taupe);
        color: var(--text-color);
    }

    .btn-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-secondary:hover {
        background: var(--pastel-brown);
    }

    /* Alert styles */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: #e3f9e5;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    .alert-danger {
        background: #ffebee;
        color: #d32f2f;
        border: 1px solid #ffcdd2;
    }

    /* No data styles */
    .no-data {
        text-align: center;
        padding: 3rem;
        color: var(--text-light);
    }

    .no-data i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: var(--pastel-taupe);
    }

    .no-data h3 {
        font-family: 'Playfair Display', serif;
        margin-bottom: 0.5rem;
    }

    /* Form container */
    .form-container {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: var(--soft-shadow);
    }

    .form-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        color: var(--text-color);
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--pastel-taupe);
        padding-bottom: 0.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-color);
        font-weight: 500;
    }

    .btn-submit {
        background: var(--pastel-taupe);
        color: var(--text-color);
        padding: 0.7rem 1.5rem;
        border-radius: 30px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-submit:hover {
        background: var(--pastel-brown);
    }

    .text-center {
        text-align: center;
    }

    /* Responsive */
    @media (max-width: 992px) {
        #sidebar {
            width: 80px;
        }

        #sidebar .brand .text,
        #sidebar .side-menu li a .text {
            display: none;
        }

        #sidebar .side-menu li a {
            justify-content: center;
        }

        #sidebar .side-menu li a i {
            margin-right: 0;
            font-size: 1.5rem;
        }

        #content {
            margin-left: 80px;
        }

        nav {
            width: calc(100% - 80px);
        }
    }

    @media (max-width: 768px) {
        nav form {
            width: 60%;
        }

        .table-data {
            grid-template-columns: 1fr;
        }

        .chart-container {
            grid-template-columns: 1fr;
        }

        .detail-card {
            flex-direction: column;
        }

        .label {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        table {
            min-width: 600px;
        }
    }

    @media (max-width: 576px) {
        nav form {
            display: none;
        }

        .head-title {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-download {
            margin-top: 1rem;
        }

        .form-row {
            flex-direction: column;
        }

        .form-group {
            width: 100%;
        }
    }

    /* Style untuk preview foto */
    .current-photo img {
        border: 2px solid var(--pastel-taupe);
        transition: transform 0.3s;
    }

    .current-photo img:hover {
        transform: scale(1.05);
    }

    .form-text {
        color: var(--text-light);
        font-size: 0.8rem;
        margin-top: 0.3rem;
    }

    /* Style untuk file input */
    .form-control[type="file"] {
        padding: 0.5rem;
        background: white;
    }
    </style>
</head>

<body>
    <!-- SIDEBAR -->
    <div id="sidebar">
        <div class="brand">
            <i class="fas fa-laptop-medical"></i>
            <span class="text">DATA NADIFA</span>
        </div>
        <ul class="side-menu top">
            <li>
                <a href="home.php">
                    <i class="fas fa-chart-line"></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="ds_kembali.php">
                    <i class="fas fa-check-circle"></i>
                    <span class="text">Sudah Kembali Service</span>
                </a>
            </li>
            <li class="active">
                <a href="ds_belum_kembali.php">
                    <i class="fas fa-clock"></i>
                    <span class="text">Belum Kembali Service</span>
                </a>
            </li>
            <li>
                <a href="diruangan.php">
                    <i class="fas fa-door-open"></i>
                    <span class="text">Barang Di Ruangan</span>
                </a>
            </li>
            <li>
                <a href="di_gudang.php">
                    <i class="fas fa-warehouse"></i>
                    <span class="text">Barang Di Gudang</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-chart-bar"></i>
                    <span class="text">Laporan</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="/NADIFA_NABILA">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="text">Keluar</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- CONTENT -->
    <div id="content">
        <!-- NAVBAR -->
        <nav>
            <div class="nav-link">
                <i class="fas fa-bars" id="btn-toggle"></i>
            </div>
            <form action="#">
                <input type="search" placeholder="Search...">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <input type="checkbox" id="switch-mode" hidden>
                <label for="switch-mode" class="switch-mode"></label>
                <a href="#" class="notification">
                    <i class="fas fa-bell"></i>
                    <span class="num">8</span>
                </a>
                <a href="#" class="profile">
                    <img src="https://via.placeholder.com/36" alt="Profile">
                </a>
            </div>
        </nav>

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Data Barang Belum Kembali dari Service</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li><i class="fas fa-chevron-right"></i></li>
                        <li>
                            <a href="#" class="active">Belum Kembali</a>
                        </li>
                    </ul>
                </div>
                <a href="?tambah=true" class="btn-download">
                    <i class="fas fa-plus"></i>
                    <span class="text">Tambah Data</span>
                </a>
            </div>

            <!-- Pesan Notifikasi -->
            <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['message']); ?>
                <?php unset($_SESSION['message']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <!-- Box Info -->
            <ul class="box-info">
                <li>
                    <i class="fas fa-laptop"></i>
                    <div class="text">
                        <h3><?= $statistik['total'] ?></h3>
                        <p>Total Barang</p>
                    </div>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <div class="text">
                        <h3><?= $statistik['baik'] ?></h3>
                        <p>Kondisi Baik</p>
                    </div>
                </li>
                <li>
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="text">
                        <h3><?= $statistik['rusak_ringan'] ?></h3>
                        <p>Rusak Ringan</p>
                    </div>
                </li>
                <li>
                    <i class="fas fa-times-circle"></i>
                    <div class="text">
                        <h3><?= $statistik['tidak_bisa_diperbaiki'] ?></h3>
                        <p>Tidak Bisa Diperbaiki</p>
                    </div>
                </li>
            </ul>

            <!-- Bagian Tabel Data -->
            <?php if (!isset($_GET['tambah']) && !isset($_GET['edit']) && !isset($_GET['detail'])): ?>
            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Data Barang belum Kembali dari Service</h3>
                        <div>
                            <i class="fas fa-filter"></i>
                            <i class="fas fa-sync-alt"></i>
                        </div>
                    </div>

                    <?php if (empty($data_belum_kembali)): ?>
                    <div class="no-data">
                        <i class="fas fa-inbox"></i>
                        <h3>Tidak ada data barang yang belum kembali</h3>
                        <p>Klik tombol "Tambah Data" untuk menambahkan data pertama Anda</p>
                    </div>
                    <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>No Urut</th>
                                    <th>Nama Barang</th>
                                    <th>Ruangan</th>
                                    <th>Merek</th>
                                    <th>Tempat Service</th>
                                    <th>Tanggal Service</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Kondisi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data_belum_kembali as $item): ?>
                                <tr>
                                    <td><?= $item['no_urut'] ?></td>
                                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                                    <td><?= htmlspecialchars($item['ruangan']) ?></td>
                                    <td><?= htmlspecialchars($item['merek']) ?></td>
                                    <td><?= htmlspecialchars($item['tempat_service']) ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal_service'])) ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal_kembali'])) ?></td>
                                    <td>
                                        <span class="status <?= $item['kondisi_sebelum_service'] ?>">
                                            <?= 
                                                $item['kondisi_sebelum_service'] == 'baik' ? 'Baik' : 
                                                ($item['kondisi_sebelum_service'] == 'rusak_ringan' ? 'Rusak Ringan' : 'Tidak Bisa Diperbaiki')
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?edit=<?= $item['id'] ?>" class="action-btn btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="?detail=<?= $item['id'] ?>" class="action-btn btn-success">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a href="?hapus=<?= $item['id'] ?>" class="action-btn btn-danger"
                                                onclick="return confirm('Hapus data ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Bagian Form Tambah/Edit -->
            <?php if (isset($_GET['tambah']) || isset($_GET['edit'])): ?>
            <div class="form-container">
                <h2 class="form-title">
                    <i class="fas fa-<?= isset($_GET['edit']) ? 'edit' : 'plus' ?>"></i>
                    <?= isset($_GET['edit']) ? 'Edit Data Barang' : 'Tambah Data Barang' ?>
                </h2>

                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
                    <input type="hidden" name="foto_lama" value="<?= $edit_data['bukti_foto'] ?? '' ?>">

                    <!-- DROPDOWN STATUS BARANG - DITAMBAHKAN -->
                    <div class="form-group">
                        <label for="status_barang">Status Barang *</label>
                        <select class="form-control" id="status_barang" name="status_barang" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="belum_kembali"
                                <?= (isset($edit_data['status_barang']) && $edit_data['status_barang'] == 'belum_kembali') ? 'selected' : (isset($_GET['tambah']) ? 'selected' : '') ?>>
                                Belum Kembali dari Service</option>
                            <option value="sudah_kembali"
                                <?= (isset($edit_data['status_barang']) && $edit_data['status_barang'] == 'sudah_kembali') ? 'selected' : '' ?>>
                                Sudah Kembali dari Service</option>
                            <option value="di_ruangan"
                                <?= (isset($edit_data['status_barang']) && $edit_data['status_barang'] == 'di_ruangan') ? 'selected' : '' ?>>
                                Barang di Ruangan</option>
                            <option value="di_gudang"
                                <?= (isset($edit_data['status_barang']) && $edit_data['status_barang'] == 'di_gudang') ? 'selected' : '' ?>>
                                Barang di Gudang</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Nomor Urut</label>
                            <div class="form-control" style="background: #e9ecef; color: #495057; cursor: not-allowed;">
                                <?php if (isset($_GET['edit']) && $edit_data): ?>
                                <?= $edit_data['no_urut'] ?>
                                <input type="hidden" name="no_urut" value="<?= $edit_data['no_urut'] ?>">
                                <?php else: ?>
                                <?= $next_no_urut ?>
                                <input type="hidden" name="no_urut" value="<?= $next_no_urut ?>">
                                <?php endif; ?>
                            </div>
                            <small class="form-text">Nomor urut otomatis diisi oleh sistem</small>
                        </div>
                        <div class="form-group">
                            <label for="nama_barang">Nama Barang *</label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang"
                                value="<?= $edit_data['nama_barang'] ?? '' ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ruangan">Ruangan *</label>
                            <input type="text" class="form-control" id="ruangan" name="ruangan"
                                value="<?= $edit_data['ruangan'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="merek">Merek *</label>
                            <input type="text" class="form-control" id="merek" name="merek"
                                value="<?= $edit_data['merek'] ?? '' ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tempat_service">Tempat Service *</label>
                            <input type="text" class="form-control" id="tempat_service" name="tempat_service"
                                value="<?= $edit_data['tempat_service'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="nomor_barang">Nomor Barang</label>
                            <input type="text" class="form-control" id="nomor_barang" name="nomor_barang"
                                value="<?= $edit_data['nomor_barang'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tanggal_diambil_dari_ruangan">Tanggal Diambil dari Ruangan</label>
                            <input type="date" class="form-control" id="tanggal_diambil_dari_ruangan"
                                name="tanggal_diambil_dari_ruangan"
                                value="<?= $edit_data['tanggal_diambil_dari_ruangan'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_service">Tanggal Service *</label>
                            <input type="date" class="form-control" id="tanggal_service" name="tanggal_service"
                                value="<?= $edit_data['tanggal_service'] ?? '' ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tanggal_kembali">Tanggal Kembali *</label>
                            <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali"
                                value="<?= $edit_data['tanggal_kembali'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="kondisi_sebelum_service">Kondisi Sebelum Service *</label>
                            <select class="form-control" id="kondisi_sebelum_service" name="kondisi_sebelum_service"
                                required>
                                <option value="baik"
                                    <?= (isset($edit_data['kondisi_sebelum_service']) && $edit_data['kondisi_sebelum_service'] == 'baik') ? 'selected' : '' ?>>
                                    Baik</option>
                                <option value="rusak_ringan"
                                    <?= (isset($edit_data['kondisi_sebelum_service']) && $edit_data['kondisi_sebelum_service'] == 'rusak_ringan') ? 'selected' : '' ?>>
                                    Rusak Ringan</option>
                                <option value="tidak_bisa_diperbaiki"
                                    <?= (isset($edit_data['kondisi_sebelum_service']) && $edit_data['kondisi_sebelum_service'] == 'tidak_bisa_diperbaiki') ? 'selected' : '' ?>>
                                    Tidak Bisa Diperbaiki</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bukti_foto">Bukti Foto</label>
                        <input type="file" class="form-control" id="bukti_foto" name="bukti_foto" accept="image/*">
                        <small class="form-text">Format: JPG, JPEG, PNG, GIF, WEBP (Maks: 2MB)</small>

                        <!-- Tampilkan foto lama jika sedang edit -->
                        <?php if (isset($_GET['edit']) && !empty($edit_data['bukti_foto'])): ?>
                        <div class="current-photo" style="margin-top: 10px;">
                            <p>Foto Saat Ini:</p>
                            <img src="uploads/<?= htmlspecialchars($edit_data['bukti_foto']) ?>" alt="Bukti Foto"
                                style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="kelengkapan_barang">Kelengkapan Barang</label>
                        <textarea class="form-control" id="kelengkapan_barang" name="kelengkapan_barang" rows="3"
                            placeholder="Contoh: Unit, Charger, Tas..."><?= $edit_data['kelengkapan_barang'] ?? '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="masalah">Masalah/Kerusakan *</label>
                        <textarea class="form-control" id="masalah" name="masalah" rows="3" required
                            placeholder="Jelaskan masalah atau kerusakan yang terjadi..."><?= $edit_data['masalah'] ?? '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                            placeholder="Keterangan tambahan..."><?= $edit_data['keterangan'] ?? '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="simpan" class="btn-custom btn-success">
                            <i class="fas fa-save"></i> Simpan Data
                        </button>
                        <a href="ds_belum_kembali.php" class="btn-custom btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Bagian Detail -->
            <?php if (isset($_GET['detail']) && $detail_data): ?>
            <div class="container-detail">
                <h2 class="header-title">
                    <i class="fas fa-info-circle"></i> Detail Barang
                </h2>

                <div class="detail-item">
                    <div class="detail-card">
                        <div class="label">No Urut</div>
                        <div class="value"><?= htmlspecialchars($detail_data['no_urut']) ?></div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Nama Barang</div>
                        <div class="value"><?= htmlspecialchars($detail_data['nama_barang']) ?></div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Ruangan</div>
                        <div class="value"><?= htmlspecialchars($detail_data['ruangan']) ?></div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Merek</div>
                        <div class="value"><?= htmlspecialchars($detail_data['merek']) ?></div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Tempat Service</div>
                        <div class="value"><?= htmlspecialchars($detail_data['tempat_service']) ?></div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Nomor Barang</div>
                        <div class="value">
                            <?= !empty($detail_data['nomor_barang']) ? htmlspecialchars($detail_data['nomor_barang']) : '-' ?>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Tanggal Diambil dari Ruangan</div>
                        <div class="value">
                            <?= $detail_data['tanggal_diambil_dari_ruangan'] ? date('d M Y', strtotime($detail_data['tanggal_diambil_dari_ruangan'])) : '-' ?>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Tanggal Service</div>
                        <div class="value"><?= date('d M Y', strtotime($detail_data['tanggal_service'])) ?></div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Tanggal Kembali</div>
                        <div class="value"><?= date('d M Y', strtotime($detail_data['tanggal_kembali'])) ?></div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Kondisi Sebelum Service</div>
                        <div class="value">
                            <span class="status <?= $detail_data['kondisi_sebelum_service'] ?>">
                                <?= 
                                    $detail_data['kondisi_sebelum_service'] == 'baik' ? 'Baik' : 
                                    ($detail_data['kondisi_sebelum_service'] == 'rusak_ringan' ? 'Rusak Ringan' : 'Tidak Bisa Diperbaiki')
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Kelengkapan Barang</div>
                        <div class="value">
                            <?= !empty($detail_data['kelengkapan_barang']) ? htmlspecialchars($detail_data['kelengkapan_barang']) : '-' ?>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Masalah/Kerusakan</div>
                        <div class="value"><?= htmlspecialchars($detail_data['masalah']) ?></div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Keterangan</div>
                        <div class="value">
                            <?= !empty($detail_data['keterangan']) ? htmlspecialchars($detail_data['keterangan']) : '-' ?>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="label">Bukti Foto</div>
                        <div class="value">
                            <?php if (!empty($detail_data['bukti_foto'])): ?>
                            <img src="uploads/<?= htmlspecialchars($detail_data['bukti_foto']) ?>" alt="Bukti Foto"
                                style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid var(--pastel-taupe);">
                            <?php else: ?>
                            <span style="color: var(--text-light);">Tidak ada foto</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <a href="ds_belum_kembali.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Tambahkan ini di bagian head untuk memastikan form berfungsi -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validasi form sebelum submit
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let valid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.style.borderColor = 'red';
                    } else {
                        field.style.borderColor = '';
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    alert('Harap isi semua field yang wajib diisi!');
                }
            });
        });

        // Konfirmasi hapus
        const deleteLinks = document.querySelectorAll('a[href*="hapus"]');
        deleteLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    e.preventDefault();
                }
            });
        });
    });
    </script>
</body>

</html>