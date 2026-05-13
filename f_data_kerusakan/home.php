<?php
// Mulai session dan include koneksi
session_start();
include 'koneksi/koneksi.php';

// Fungsi untuk membuat/update tabel kerusakan_komputer
function createTableKerusakan($koneksi) {
    $query = "CREATE TABLE IF NOT EXISTS kerusakan_komputer (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_barang VARCHAR(100) NOT NULL,
        milik VARCHAR(100) NOT NULL,
        merek VARCHAR(100) NOT NULL,
        tempat_service VARCHAR(100) NOT NULL,
        tanggal_diantar_diambil DATE NOT NULL,
        nomor_barang VARCHAR(100),
        kelengkapan_barang TEXT,
        masalah TEXT,
        keterangan TEXT,
        bukti_foto VARCHAR(255) DEFAULT NULL,
        status ENUM('belum_kembali', 'sudah_kembali') DEFAULT 'belum_kembali',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    return mysqli_query($koneksi, $query);
}

// Fungsi untuk menambahkan kolom status jika belum ada
function addStatusColumnIfNotExists($koneksi) {
    // Cek apakah kolom status sudah ada
    $check_query = "SHOW COLUMNS FROM kerusakan_komputer LIKE 'status'";
    $result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($result) == 0) {
        // Kolom status belum ada, tambahkan
        $alter_query = "ALTER TABLE kerusakan_komputer ADD COLUMN status ENUM('belum_kembali', 'sudah_kembali') DEFAULT 'belum_kembali'";
        return mysqli_query($koneksi, $alter_query);
    }
    
    return true;
}

// Panggil fungsi untuk memastikan tabel dan kolom ada
createTableKerusakan($koneksi);
addStatusColumnIfNotExists($koneksi);

// Fungsi untuk mendapatkan data kerusakan komputer
function getKerusakanKomputer($koneksi) {
    $query = "SELECT * FROM kerusakan_komputer ORDER BY created_at DESC";
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($koneksi) . "</div>";
        return [];
    }
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Fungsi untuk menyimpan data
function saveKerusakanKomputer($koneksi, $data, $foto = null) {
    $id = mysqli_real_escape_string($koneksi, $data['id'] ?? '');
    $nama_barang = mysqli_real_escape_string($koneksi, $data['nama_barang']);
    $milik = mysqli_real_escape_string($koneksi, $data['milik']);
    $merek = mysqli_real_escape_string($koneksi, $data['merek']);
    $tempat_service = mysqli_real_escape_string($koneksi, $data['tempat_service']);
    $tanggal_diantar_diambil = mysqli_real_escape_string($koneksi, $data['tanggal_diantar_diambil']);
    $nomor_barang = mysqli_real_escape_string($koneksi, $data['nomor_barang'] ?? '');
    $kelengkapan_barang = mysqli_real_escape_string($koneksi, $data['kelengkapan_barang'] ?? '');
    $masalah = mysqli_real_escape_string($koneksi, $data['masalah'] ?? '');
    $keterangan = mysqli_real_escape_string($koneksi, $data['keterangan'] ?? '');
    $status = mysqli_real_escape_string($koneksi, $data['status'] ?? 'belum_kembali');
    $bukti_foto = mysqli_real_escape_string($koneksi, $foto);
    
    if (!empty($id)) {
        // Bangun query UPDATE secara dinamis
        $update_fields = [
            "nama_barang = '$nama_barang'",
            "milik = '$milik'",
            "merek = '$merek'",
            "tempat_service = '$tempat_service'",
            "tanggal_diantar_diambil = '$tanggal_diantar_diambil'",
            "nomor_barang = '$nomor_barang'",
            "kelengkapan_barang = '$kelengkapan_barang'",
            "masalah = '$masalah'",
            "keterangan = '$keterangan'",
            "status = '$status'"
        ];
        
        // Tambahkan field foto hanya jika ada foto baru
        if ($foto) {
            $update_fields[] = "bukti_foto = '$bukti_foto'";
        }
        
        $set_clause = implode(", ", $update_fields);
        $query = "UPDATE kerusakan_komputer SET $set_clause WHERE id = $id";
        
    } else {
        $query = "INSERT INTO kerusakan_komputer 
                  (nama_barang, milik, merek, tempat_service, tanggal_diantar_diambil, nomor_barang, kelengkapan_barang, masalah, keterangan, bukti_foto, status)
                  VALUES (
                  '$nama_barang',
                  '$milik',
                  '$merek',
                  '$tempat_service',
                  '$tanggal_diantar_diambil',
                  '$nomor_barang',
                  '$kelengkapan_barang',
                  '$masalah',
                  '$keterangan',
                  '$bukti_foto',
                  '$status'
                  )";
    }
    
    return mysqli_query($koneksi, $query);
}

// Fungsi untuk menghapus data
function deleteKerusakanKomputer($koneksi, $id) {
    // Hapus file foto jika ada
    $data = getKerusakanById($koneksi, $id);
    if ($data && !empty($data['bukti_foto'])) {
        $file_path = 'uploads/' . $data['bukti_foto'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $id = mysqli_real_escape_string($koneksi, $id);
    $query = "DELETE FROM kerusakan_komputer WHERE id = $id";
    return mysqli_query($koneksi, $query);
}

// Fungsi untuk mendapatkan data by ID
function getKerusakanById($koneksi, $id) {
    $id = mysqli_real_escape_string($koneksi, $id);
    $query = "SELECT * FROM kerusakan_komputer WHERE id = $id";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Fungsi untuk mendapatkan statistik kerusakan
function getStatistikKerusakan($koneksi) {
    // Cek dulu apakah kolom status ada
    $check_column = "SHOW COLUMNS FROM kerusakan_komputer LIKE 'status'";
    $result_check = mysqli_query($koneksi, $check_column);
    
    if (mysqli_num_rows($result_check) == 0) {
        // Kolom status belum ada, gunakan logika alternatif
        return getStatistikAlternatif($koneksi);
    }
    
    $query_total = "SELECT COUNT(*) as total FROM kerusakan_komputer";
    $query_sudah_kembali = "SELECT COUNT(*) as sudah_kembali FROM kerusakan_komputer WHERE status = 'sudah_kembali'";
    $query_belum_kembali = "SELECT COUNT(*) as belum_kembali FROM kerusakan_komputer WHERE status = 'belum_kembali'";
    $query_perbaikan = "SELECT COUNT(*) as perbaikan FROM kerusakan_komputer WHERE keterangan LIKE '%selesai%' OR keterangan LIKE '%diperbaiki%' OR keterangan LIKE '%sudah%'";
    
    $result_total = mysqli_query($koneksi, $query_total);
    $result_sudah_kembali = mysqli_query($koneksi, $query_sudah_kembali);
    $result_belum_kembali = mysqli_query($koneksi, $query_belum_kembali);
    $result_perbaikan = mysqli_query($koneksi, $query_perbaikan);
    
    $total = $result_total ? (mysqli_fetch_assoc($result_total)['total'] ?? 0) : 0;
    $sudah_kembali = $result_sudah_kembali ? (mysqli_fetch_assoc($result_sudah_kembali)['sudah_kembali'] ?? 0) : 0;
    $belum_kembali = $result_belum_kembali ? (mysqli_fetch_assoc($result_belum_kembali)['belum_kembali'] ?? 0) : 0;
    $perbaikan = $result_perbaikan ? (mysqli_fetch_assoc($result_perbaikan)['perbaikan'] ?? 0) : 0;
    
    return [
        'total' => $total,
        'sudah_kembali' => $sudah_kembali,
        'belum_kembali' => $belum_kembali,
        'perbaikan' => $perbaikan,
        'presentase_sudah_kembali' => $total > 0 ? round(($sudah_kembali / $total) * 100, 2) : 0,
        'presentase_belum_kembali' => $total > 0 ? round(($belum_kembali / $total) * 100, 2) : 0,
        'presentase_perbaikan' => $total > 0 ? round(($perbaikan / $total) * 100, 2) : 0
    ];
}

// Fungsi alternatif jika kolom status belum ada
function getStatistikAlternatif($koneksi) {
    $query_total = "SELECT COUNT(*) as total FROM kerusakan_komputer";
    $result_total = mysqli_query($koneksi, $query_total);
    $total = $result_total ? (mysqli_fetch_assoc($result_total)['total'] ?? 0) : 0;
    
    // Untuk sementara, anggap semua data sebagai belum kembali
    $belum_kembali = $total;
    $sudah_kembali = 0;
    
    $query_perbaikan = "SELECT COUNT(*) as perbaikan FROM kerusakan_komputer WHERE keterangan LIKE '%selesai%' OR keterangan LIKE '%diperbaiki%' OR keterangan LIKE '%sudah%'";
    $result_perbaikan = mysqli_query($koneksi, $query_perbaikan);
    $perbaikan = $result_perbaikan ? (mysqli_fetch_assoc($result_perbaikan)['perbaikan'] ?? 0) : 0;
    
    return [
        'total' => $total,
        'sudah_kembali' => $sudah_kembali,
        'belum_kembali' => $belum_kembali,
        'perbaikan' => $perbaikan,
        'presentase_sudah_kembali' => 0,
        'presentase_belum_kembali' => 100,
        'presentase_perbaikan' => $total > 0 ? round(($perbaikan / $total) * 100, 2) : 0
    ];
}

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
                    header("Location: home.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Format file tidak didukung! Hanya JPG, JPEG, PNG, GIF, dan WEBP yang diizinkan.";
                header("Location: home.php");
                exit;
            }
        } else {
            // Jika tidak ada foto baru, gunakan foto lama (untuk edit)
            $foto_name = $_POST['foto_lama'] ?? null;
        }
        
        $data = [
            'id' => $_POST['id'] ?? '',
            'nama_barang' => $_POST['nama_barang'],
            'milik' => $_POST['milik'],
            'merek' => $_POST['merek'],
            'tempat_service' => $_POST['tempat_service'],
            'tanggal_diantar_diambil' => $_POST['tanggal_diantar_diambil'],
            'nomor_barang' => $_POST['nomor_barang'] ?? '',
            'kelengkapan_barang' => $_POST['kelengkapan_barang'] ?? '',
            'masalah' => $_POST['masalah'] ?? '',
            'keterangan' => $_POST['keterangan'] ?? '',
            'status' => $_POST['status'] ?? 'belum_kembali'
        ];
        
        if (saveKerusakanKomputer($koneksi, $data, $foto_name)) {
            $_SESSION['message'] = "Data berhasil disimpan!";
        } else {
            $_SESSION['error'] = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
        
        header("Location: home.php");
        exit;
    }
}

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if (deleteKerusakanKomputer($koneksi, $id)) {
        $_SESSION['message'] = "Data berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($koneksi);
    }
    
    header("Location: home.php");
    exit;
}

// Ambil data untuk ditampilkan
$data_kerusakan = getKerusakanKomputer($koneksi);
$statistik = getStatistikKerusakan($koneksi);

// Cek apakah sedang edit atau lihat detail
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_data = getKerusakanById($koneksi, $_GET['edit']);
}

$detail_data = null;
if (isset($_GET['detail'])) {
    $detail_data = getKerusakanById($koneksi, $_GET['detail']);
}

// Filter data untuk halaman service
$data_sudah_kembali = array_filter($data_kerusakan, function($item) {
    return isset($item['status']) && $item['status'] === 'sudah_kembali';
});

$data_belum_kembali = array_filter($data_kerusakan, function($item) {
    return !isset($item['status']) || $item['status'] === 'belum_kembali';
});
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Manajemen Kerusakan Komputer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    /* CSS Styles tetap sama seperti sebelumnya */
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
        background: conic-gradient(#4caf50 0% <?=$statistik['presentase_sudah_kembali'] ?>%,
                #ff9800 <?=$statistik['presentase_sudah_kembali'] ?>% <?=$statistik['presentase_sudah_kembali'] + $statistik['presentase_belum_kembali'] ?>%,
                #f44336 <?=$statistik['presentase_sudah_kembali'] + $statistik['presentase_belum_kembali'] ?>% 100%);
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

    /* TABLE DATA */
    .table-data {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
    }

    .order,
    .todo {
        background: white;
        border-radius: 15px;
        box-shadow: var(--soft-shadow);
        padding: 1.5rem;
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
    }

    table th {
        text-align: left;
        padding: 0.8rem 0;
        color: var(--text-light);
        font-weight: 500;
        border-bottom: 1px solid var(--pastel-cream);
    }

    table td {
        padding: 0.8rem 0;
        border-bottom: 1px solid var(--pastel-cream);
    }

    table tr:last-child td {
        border-bottom: none;
    }

    .status {
        padding: 0.3rem 0.8rem;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .status.completed {
        background: #e3f9e5;
        color: #2e7d32;
    }

    .status.pending {
        background: #fff8e1;
        color: #ff8f00;
    }

    .status.process {
        background: #e3f2fd;
        color: #1565c0;
    }

    .status.sudah-kembali {
        background: #e3f9e5;
        color: #2e7d32;
    }

    .status.belum-kembali {
        background: #ffebee;
        color: #d32f2f;
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

    .status-danger {
        color: #d32f2f;
        font-weight: 500;
    }

    .status-success {
        color: #2e7d32;
        font-weight: 500;
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
        border-color: var(--pastel-taupe);
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

    .btn-success {
        background: #2e7d32;
        color: white;
    }

    .btn-danger {
        background: #d32f2f;
        color: white;
    }

    .btn-warning {
        background: #ff8f00;
        color: white;
    }

    .btn-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-secondary:hover {
        background: var(--pastel-brown);
    }

    .btn-success:hover {
        background: #1b5e20;
    }

    .btn-danger:hover {
        background: #b71c1c;
    }

    .btn-warning:hover {
        background: #e65100;
    }

    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
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

    /* Dropdown Styles */
    .dropdown {
        position: relative;
    }

    .dropdown-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .dropdown-arrow {
        font-size: 0.8rem;
        transition: transform 0.3s;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        background: var(--sidebar-bg);
        min-width: 200px;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s;
        z-index: 1000;
    }

    .dropdown-menu li {
        margin: 0;
        padding: 0;
    }

    .dropdown-menu li a {
        display: block;
        padding: 0.8rem 1.5rem;
        color: var(--sidebar-text);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s;
    }

    .dropdown-menu li:last-child a {
        border-bottom: none;
    }

    .dropdown-menu li a:hover {
        background: var(--sidebar-active);
        padding-left: 2rem;
    }

    .dropdown:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown:hover .dropdown-arrow {
        transform: rotate(180deg);
    }


    /* Tambahan CSS untuk sidebar responsif */
    #sidebar {
        transition: all 0.3s ease;
    }

    #sidebar.collapsed {
        width: 80px;
    }

    #sidebar.collapsed .brand .text,
    #sidebar.collapsed .side-menu li a .text {
        display: none !important;
    }

    #sidebar.collapsed .side-menu li a {
        justify-content: center;
        padding: 0.8rem;
    }

    #sidebar.collapsed .side-menu li a i {
        margin-right: 0;
        font-size: 1.5rem;
    }

    #sidebar.collapsed .brand {
        justify-content: center;
        padding: 1rem 0.5rem;
    }

    #sidebar.collapsed .brand i {
        margin-right: 0;
    }

    /* Overlay untuk mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        #sidebar:not(.collapsed) {
            width: 260px !important;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        #sidebar.collapsed {
            width: 0 !important;
            overflow: hidden;
        }

        #content {
            margin-left: 0 !important;
            transition: margin-left 0.3s ease;
        }

        nav {
            width: 100% !important;
            transition: width 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block;
        }
    }

    @media (max-width: 576px) {
        #sidebar:not(.collapsed) {
            width: 100% !important;
            max-width: 280px;
        }

        .head-title h1 {
            font-size: 1.5rem;
        }

        .box-info {
            grid-template-columns: 1fr;
        }

        .chart-container {
            grid-template-columns: 1fr;
        }

        .progress-chart {
            width: 150px;
            height: 150px;
        }

        .progress-chart::before {
            width: 110px;
            height: 110px;
        }

        .chart-center .percentage {
            font-size: 1.5rem;
        }
    }

    /* Smooth transitions untuk semua elemen */
    #sidebar,
    #content,
    nav,
    #sidebar .brand,
    #sidebar .side-menu li a,
    #sidebar .side-menu li a .text {
        transition: all 0.3s ease;
    }

    /* Hover effects untuk sidebar collapsed */
    #sidebar.collapsed .side-menu li:hover {
        background: var(--sidebar-active);
        border-radius: 50%;
        width: 50px;
        height: 50px;
        margin: 0.5rem auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #sidebar.collapsed .side-menu li a {
        justify-content: center;
    }
    </style>
</head>

<body>
    <!-- SIDEBAR -->
    <!-- SIDEBAR -->
    <div id="sidebar">
        <div class="brand">
            <i class="fas fa-laptop-medical"></i>
            <span class="text">DATA NADIFA</span>
        </div>
        <ul class="side-menu top">
            <li
                class="<?= !isset($_GET['tambah']) && !isset($_GET['edit']) && !isset($_GET['detail']) ? 'active' : '' ?>">
                <a href="home.php">
                    <i class="fas fa-chart-line"></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'ds_kembali.php' ? 'active' : '' ?>">
                <a href="ds_kembali.php">
                    <i class="fas fa-check-circle"></i>
                    <span class="text">Sudah Kembali Service</span>
                </a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'ds_belum_kembali.php' ? 'active' : '' ?>">
                <a href="ds_belum_kembali.php">
                    <i class="fas fa-clock"></i>
                    <span class="text">Belum Kembali Service</span>
                </a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'diruangan.php' ? 'active' : '' ?>">
                <a href="diruangan.php">
                    <i class="fas fa-clock"></i>
                    <span class="text">Barang Di Ruangan</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-history"></i>
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
                    <h1>Dashboard Kerusakan Komputer</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li><i class="fas fa-chevron-right"></i></li>
                        <li>
                            <a href="#" class="active">Overview</a>
                        </li>
                    </ul>
                </div>
                <!-- <a href="?tambah=true" class="btn-download">
                    <i class="fas fa-plus"></i>
                    <span class="text">Tambah Data</span>
                </a> -->
            </div>

            <!-- Pesan Notifikasi -->
            <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['message']; ?>
                <?php unset($_SESSION['message']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <!-- Box Info -->
            <ul class="box-info">
                <li>
                    <i class="fas fa-laptop"></i>
                    <div class="text">
                        <h3><?= $statistik['total'] ?></h3>
                        <p>Total Kerusakan</p>
                    </div>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <div class="text">
                        <h3><?= $statistik['sudah_kembali'] ?></h3>
                        <p>Sudah Kembali</p>
                    </div>
                </li>
                <li>
                    <i class="fas fa-clock"></i>
                    <div class="text">
                        <h3><?= $statistik['belum_kembali'] ?></h3>
                        <p>Belum Kembali</p>
                    </div>
                </li>
                <li>
                    <i class="fas fa-tools"></i>
                    <div class="text">
                        <h3><?= $statistik['perbaikan'] ?></h3>
                        <p>Sudah Diperbaiki</p>
                    </div>
                </li>
            </ul>

            <!-- Chart Container -->
            <div class="chart-container">
                <div class="chart-box">
                    <div class="head">
                        <h3>Status Pengembalian</h3>
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="chart-content">
                        <div class="progress-chart">
                            <div class="chart-center">
                                <div class="percentage"><?= $statistik['presentase_sudah_kembali'] ?>%</div>
                                <div class="label">Sudah Kembali</div>
                            </div>
                        </div>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #4caf50;"></div>
                            <div class="legend-text">Sudah Kembali (<?= $statistik['sudah_kembali'] ?>)</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #ff9800;"></div>
                            <div class="legend-text">Belum Kembali (<?= $statistik['belum_kembali'] ?>)</div>
                        </div>
                    </div>
                </div>

                <div class="chart-box">
                    <div class="head">
                        <h3>Status Perbaikan</h3>
                        <i class="fas fa-wrench"></i>
                    </div>
                    <div class="chart-content">
                        <div class="progress-chart" style="background: conic-gradient(
                            #4caf50 0% <?= $statistik['presentase_perbaikan'] ?>%,
                            #f44336 <?= $statistik['presentase_perbaikan'] ?>% 100%
                        );">
                            <div class="chart-center">
                                <div class="percentage"><?= $statistik['presentase_perbaikan'] ?>%</div>
                                <div class="label">Sudah Diperbaiki</div>
                            </div>
                        </div>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #4caf50;"></div>
                            <div class="legend-text">Sudah Diperbaiki (<?= $statistik['perbaikan'] ?>)</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #f44336;"></div>
                            <div class="legend-text">Belum Diperbaiki
                                (<?= $statistik['total'] - $statistik['perbaikan'] ?>)</div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            // Toggle sidebar untuk responsive
            document.getElementById('btn-toggle')?.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                const content = document.getElementById('content');

                if (sidebar.style.width === '80px' || window.getComputedStyle(sidebar).width === '80px') {
                    sidebar.style.width = '260px';
                    content.style.marginLeft = '260px';
                } else {
                    sidebar.style.width = '80px';
                    content.style.marginLeft = '80px';
                }
            });

            // Fungsi pencarian
            document.querySelector('nav form input')?.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
            </script>
            <script>
            // Toggle sidebar untuk responsive
            document.getElementById('btn-toggle')?.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                const content = document.getElementById('content');
                const nav = document.querySelector('nav');

                if (sidebar.classList.contains('collapsed')) {
                    // Jika sidebar collapsed, buka
                    sidebar.classList.remove('collapsed');
                    sidebar.style.width = '260px';
                    content.style.marginLeft = '260px';
                    nav.style.width = 'calc(100% - 260px)';

                    // Tampilkan teks menu
                    const menuTexts = document.querySelectorAll('#sidebar .text');
                    menuTexts.forEach(text => {
                        text.style.display = 'block';
                    });

                    // Tampilkan teks brand
                    const brandText = document.querySelector('#sidebar .brand .text');
                    if (brandText) brandText.style.display = 'inline';

                } else {
                    // Jika sidebar terbuka, collapse
                    sidebar.classList.add('collapsed');
                    sidebar.style.width = '80px';
                    content.style.marginLeft = '80px';
                    nav.style.width = 'calc(100% - 80px)';

                    // Sembunyikan teks menu
                    const menuTexts = document.querySelectorAll('#sidebar .text');
                    menuTexts.forEach(text => {
                        text.style.display = 'none';
                    });

                    // Sembunyikan teks brand
                    const brandText = document.querySelector('#sidebar .brand .text');
                    if (brandText) brandText.style.display = 'none';
                }
            });

            // Fungsi untuk handle resize window
            function handleResize() {
                const sidebar = document.getElementById('sidebar');
                const content = document.getElementById('content');
                const nav = document.querySelector('nav');

                if (window.innerWidth <= 992) {
                    // Mode mobile/tablet - sidebar collapsed by default
                    if (!sidebar.classList.contains('collapsed')) {
                        sidebar.classList.add('collapsed');
                        sidebar.style.width = '80px';
                        content.style.marginLeft = '80px';
                        nav.style.width = 'calc(100% - 80px)';

                        // Sembunyikan teks menu
                        const menuTexts = document.querySelectorAll('#sidebar .text');
                        menuTexts.forEach(text => {
                            text.style.display = 'none';
                        });

                        // Sembunyikan teks brand
                        const brandText = document.querySelector('#sidebar .brand .text');
                        if (brandText) brandText.style.display = 'none';
                    }
                } else {
                    // Mode desktop - sidebar expanded by default
                    if (sidebar.classList.contains('collapsed')) {
                        sidebar.classList.remove('collapsed');
                        sidebar.style.width = '260px';
                        content.style.marginLeft = '260px';
                        nav.style.width = 'calc(100% - 260px)';

                        // Tampilkan teks menu
                        const menuTexts = document.querySelectorAll('#sidebar .text');
                        menuTexts.forEach(text => {
                            text.style.display = 'block';
                        });

                        // Tampilkan teks brand
                        const brandText = document.querySelector('#sidebar .brand .text');
                        if (brandText) brandText.style.display = 'inline';
                    }
                }
            }

            // Event listener untuk resize
            window.addEventListener('resize', handleResize);

            // Panggil sekali saat load
            document.addEventListener('DOMContentLoaded', function() {
                handleResize();
            });

            // Fungsi pencarian
            document.querySelector('nav form input')?.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });

            // Close sidebar ketika klik di luar (untuk mobile)
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const toggleBtn = document.getElementById('btn-toggle');

                if (window.innerWidth <= 768 &&
                    !sidebar.contains(event.target) &&
                    !toggleBtn.contains(event.target) &&
                    !sidebar.classList.contains('collapsed')) {

                    sidebar.classList.add('collapsed');
                    sidebar.style.width = '80px';
                    document.getElementById('content').style.marginLeft = '80px';
                    document.querySelector('nav').style.width = 'calc(100% - 80px)';

                    // Sembunyikan teks
                    const menuTexts = document.querySelectorAll('#sidebar .text');
                    menuTexts.forEach(text => {
                        text.style.display = 'none';
                    });

                    const brandText = document.querySelector('#sidebar .brand .text');
                    if (brandText) brandText.style.display = 'none';
                }
            });
            </script>
</body>

</html>