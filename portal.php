<?php
session_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "nadifa";

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Initialize variables
$id = $nama_barang = $milik = $merek = $tempat_service = $tanggal = $kelengkapan = $masalah = $status_kembali = $catatan = '';
$operation = isset($_GET['action']) ? $_GET['action'] : 'list';
$title = 'Data Kerusakan Peralatan';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['simpan'])) {
        // Sanitize inputs
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
        $milik = mysqli_real_escape_string($koneksi, $_POST['milik']);
        $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
        $tempat_service = mysqli_real_escape_string($koneksi, $_POST['tempat_service']);
        $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
        $kelengkapan = mysqli_real_escape_string($koneksi, $_POST['kelengkapan']);
        $masalah = mysqli_real_escape_string($koneksi, $_POST['masalah']);
        $status_kembali = mysqli_real_escape_string($koneksi, $_POST['status_kembali']);
        $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);

        if ($id > 0) {
            // Update existing record
            $query = "UPDATE kerusakan_komputer SET
                nama_barang = '$nama_barang',
                milik = '$milik',
                merek = '$merek',
                tempat_service = '$tempat_service',
                tanggal = '$tanggal',
                kelengkapan = '$kelengkapan',
                masalah = '$masalah',
                status_kembali = '$status_kembali',
                catatan = '$catatan'
                WHERE id = $id";
            $message = 'Data berhasil diperbarui!';
        } else {
            // Create new record
            $query = "INSERT INTO kerusakan_komputer 
                (nama_barang, milik, merek, tempat_service, tanggal, kelengkapan, masalah, status_kembali, catatan)
                VALUES ('$nama_barang', '$milik', '$merek', '$tempat_service', '$tanggal', '$kelengkapan', '$masalah', '$status_kembali', '$catatan')";
            $message = 'Data berhasil ditambahkan!';
        }

        if (mysqli_query($koneksi, $query)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => $message];
            header("Location: portal.php");
            exit();
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => "Error: " . mysqli_error($koneksi)];
        }
    }
} elseif ($operation == 'delete') {
    $id = intval($_GET['id']);
    // Delete record
    if (mysqli_query($koneksi, "DELETE FROM kerusakan_komputer WHERE id=$id")) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Data berhasil dihapus!'];
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gagal menghapus data: ' . mysqli_error($koneksi)];
    }
    header("Location: portal.php");
    exit();
} elseif ($operation == 'edit' || $operation == 'view') {
    $id = intval($_GET['id']);
    // Load data for edit or view
    $result = mysqli_query($koneksi, "SELECT * FROM kerusakan_komputer WHERE id=$id");
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $nama_barang = $data['nama_barang'];
        $milik = $data['milik'];
        $merek = $data['merek'];
        $tempat_service = $data['tempat_service'];
        $tanggal = date('Y-m-d', strtotime($data['tanggal']));
        $kelengkapan = $data['kelengkapan'];
        $masalah = $data['masalah'];
        $status_kembali = $data['status_kembali'];
        $catatan = $data['catatan'];
        
        $title = ($operation == 'edit') ? 'Edit Data Kerusakan' : 'Detail Data Kerusakan';
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Data tidak ditemukan!'];
        header("Location: portal.php");
        exit();
    }
} elseif ($operation == 'create') {
    $title = 'Tambah Data Kerusakan';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nadifa Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <style>
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

    table td img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 0.5rem;
        vertical-align: middle;
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
        padding: 0.5rem;
        border: 1px solid var(--pastel-cream);
        border-radius: 5px;
        margin-top: 0.3rem;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--pastel-taupe);
    }

    .btn-custom {
        padding: 0.5rem 1.5rem;
        border-radius: 30px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-secondary {
        background: var(--pastel-taupe);
        color: var(--text-color);
    }

    .btn-success {
        background: #2e7d32;
        color: white;
    }

    /* TODO LIST */
    .todo-list {
        list-style: none;
    }

    .todo-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem 0;
        border-bottom: 1px solid var(--pastel-cream);
    }

    .todo-list li:last-child {
        border-bottom: none;
    }

    .todo-list li p {
        position: relative;
        padding-left: 1.5rem;
    }

    .todo-list li.completed p::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #2e7d32;
    }

    .todo-list li.not-completed p::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #ff8f00;
    }

    .todo-list li i {
        color: var(--text-light);
        cursor: pointer;
    }

    /* RESPONSIVE */
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

        .detail-card {
            flex-direction: column;
        }

        .label {
            width: 100%;
            margin-bottom: 0.5rem;
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


    html,
    body {
        height: 100%;
        overflow-x: hidden;
    }

    #sidebar {
        top: 0;
        left: 0;
    }

    /* Additional styles for form */
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

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: #e3f9e5;
        color: #2e7d32;
    }

    .alert-danger {
        background: #ffebee;
        color: #d32f2f;
    }
    </style>
</head>

<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-smile'></i>
            <span class="text">Nadifa</span>
        </a>
        <ul class="side-menu top">
            <li>
                <a href="#">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li class="active">
                <a href="portal.php">
                    <i class='bx bxs-wrench'></i>
                    <span class="text">Data Kerusakan</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bxs-doughnut-chart'></i>
                    <span class="text">Selesai Perbaikan</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bxs-message-dots'></i>
                    <span class="text">Masih Rusak</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bxs-group'></i>
                    <span class="text">Ada Diruangan</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="#">
                    <i class='bx bxs-cog'></i>
                    <span class="text">Data Komputer</span>
                </a>
            </li>
            <li>
                <a href="#" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
            <a href="#" class="notification">
                <i class='bx bxs-bell'></i>
                <span class="num">8</span>
            </a>
            <a href="#" class="profile">
                <img src="img/people.png">
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1><?= $title ?></h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="#">Data Kerusakan</a>
                        </li>
                    </ul>
                </div>
                <?php if ($operation == 'list'): ?>
                <a href="?action=create" class="btn-download">
                    <i class='bx bxs-plus-circle'></i>
                    <span class="text">Tambah Data</span>
                </a>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message']['type'] ?>">
                <?= $_SESSION['message']['text'] ?>
            </div>
            <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if ($operation == 'list'): ?>
            <!-- Main List View -->
            <div class="table-data">
                <div class="order" style="width: 100%">
                    <div class="head">
                        <h3>Daftar Kerusakan</h3>
                        <i class='bx bx-search'></i>
                        <i class='bx bx-filter'></i>
                    </div>
                    <table id="tabeldata">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Nama Barang</th>
                                <th class="text-center">Milik</th>
                                <th class="text-center">Merek</th>
                                <th class="text-center">Masalah</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($koneksi, "SELECT * FROM kerusakan_komputer ORDER BY status_kembali, tanggal DESC");
                            while ($row = mysqli_fetch_assoc($query)) {
                                $status_class = '';
                                $status_text = '';
                                
                                if (strtolower(trim($row['status_kembali'])) === 'masih rusak') {
                                    $status_class = 'status-danger';
                                    $status_text = 'Masih Rusak';
                                } elseif (strtolower(trim($row['status_kembali'])) === 'belum kembali') {
                                    $status_class = 'status-pending';
                                    $status_text = 'Belum Kembali';
                                } elseif (strtolower(trim($row['status_kembali'])) === 'sudah kembali') {
                                    $status_class = 'status-completed';
                                    $status_text = 'Sudah Kembali';
                                }
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['milik']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['merek']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['masalah']) ?></td>
                                <td class="text-center">
                                    <span class="status <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="?action=view&id=<?= $row['id'] ?>" class="btn btn-info btn-sm">Detail</a>
                                    <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="?action=delete&id=<?= $row['id'] ?>"
                                        onclick="return confirm('Hapus data ini?')"
                                        class="btn btn-danger btn-sm">Hapus</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($operation == 'create' || $operation == 'edit'): ?>
            <!-- Form for Create/Edit -->
            <div class="form-container">
                <div class="form-title">
                    <i class='bx bxs-<?= ($operation == 'create') ? 'plus-circle' : 'edit' ?>'></i>
                    <?= $title ?>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_barang">Jenis Barang</label>
                            <select class="form-control" name="nama_barang" required>
                                <option value="">Pilih Jenis Barang</option>
                                <option value="Komputer" <?= $nama_barang == 'Komputer' ? 'selected' : '' ?>>Komputer
                                </option>
                                <option value="Printer" <?= $nama_barang == 'Printer' ? 'selected' : '' ?>>Printer
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="milik">Milik (Unit/Bagian)</label>
                            <input type="text" class="form-control" name="milik" value="<?= $milik ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="merek">Merek</label>
                            <input type="text" class="form-control" name="merek" value="<?= $merek ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="tempat_service">Tempat Service</label>
                            <input type="text" class="form-control" name="tempat_service"
                                value="<?= $tempat_service ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tanggal">Tanggal Diantar/Diambil</label>
                            <input type="date" class="form-control" name="tanggal" value="<?= $tanggal ?>">
                        </div>
                        <div class="form-group">
                            <label for="kelengkapan">Kelengkapan Barang</label>
                            <select class="form-control" name="kelengkapan">
                                <option value="Tanpa Kabel" <?= $kelengkapan == 'Tanpa Kabel' ? 'selected' : '' ?>>Tanpa
                                    Kabel</option>
                                <option value="Tanpa Kabel Power"
                                    <?= $kelengkapan == 'Tanpa Kabel Power' ? 'selected' : '' ?>>Tanpa Kabel Power
                                </option>
                                <option value="Tanpa Kabel USB"
                                    <?= $kelengkapan == 'Tanpa Kabel USB' ? 'selected' : '' ?>>Tanpa Kabel USB</option>
                                <option value="Kabel Ada" <?= $kelengkapan == 'Kabel Ada' ? 'selected' : '' ?>>Kabel Ada
                                </option>
                                <option value="Kabel Power Ada"
                                    <?= $kelengkapan == 'Kabel Power Ada' ? 'selected' : '' ?>>Kabel Power Ada</option>
                                <option value="Kabel USB Ada" <?= $kelengkapan == 'Kabel USB Ada' ? 'selected' : '' ?>>
                                    Kabel USB Ada</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="masalah">Masalah</label>
                        <textarea class="form-control" name="masalah" rows="3" required><?= $masalah ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="status_kembali">Status</label>
                        <select class="form-control" name="status_kembali" required>
                            <option value="Belum Kembali" <?= $status_kembali == 'Belum Kembali' ? 'selected' : '' ?>>
                                Belum Kembali</option>
                            <option value="Sudah Kembali" <?= $status_kembali == 'Sudah Kembali' ? 'selected' : '' ?>>
                                Sudah Kembali</option>
                            <option value="Masih Rusak" <?= $status_kembali == 'Masih Rusak' ? 'selected' : '' ?>>Masih
                                Rusak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="catatan">Catatan</label>
                        <textarea class="form-control" name="catatan" rows="2"><?= $catatan ?></textarea>
                    </div>

                    <div class="text-center" style="margin-top: 2rem;">
                        <a href="portal.php" class="btn-custom btn-secondary">
                            <i class='bx bx-arrow-back'></i> Kembali
                        </a>
                        <button type="submit" name="simpan" class="btn-custom btn-success">
                            <i class='bx bx-save'></i> Simpan
                        </button>
                    </div>
                </form>
            </div>

            <?php elseif ($operation == 'view'): ?>
            <!-- Detail View -->
            <div class="form-container">
                <div class="form-title">
                    <i class='bx bxs-info-circle'></i>
                    <?= $title ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Jenis Barang</label>
                        <p><?= $data['nama_barang'] ?></p>
                    </div>
                    <div class="form-group">
                        <label>Milik (Unit/Bagian)</label>
                        <p><?= $data['milik'] ?></p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Merek</label>
                        <p><?= $data['merek'] ?></p>
                    </div>
                    <div class="form-group">
                        <label>Tempat Service</label>
                        <p><?= $data['tempat_service'] ?></p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Diantar/Diambil</label>
                        <p><?= date('d F Y', strtotime($data['tanggal'])) ?></p>
                    </div>
                    <div class="form-group">
                        <label>Kelengkapan Barang</label>
                        <p><?= $data['kelengkapan'] ?></p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Masalah</label>
                    <p><?= nl2br($data['masalah']) ?></p>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <?php
                        $status_class = '';
                        if ($data['status_kembali'] == 'Sudah Kembali') $status_class = 'status-completed';
                        if ($data['status_kembali'] == 'Belum Kembali') $status_class = 'status-pending';
                        if ($data['status_kembali'] == 'Masih Rusak') $status_class = 'status-danger';
                    ?>
                    <p><span class="status <?= $status_class ?>"><?= $data['status_kembali'] ?></span></p>
                </div>

                <div class="form-group">
                    <label>Catatan</label>
                    <p><?= nl2br($data['catatan']) ?></p>
                </div>

                <div class="text-center" style="margin-top: 2rem;">
                    <a href="portal.php" class="btn-custom btn-secondary">
                        <i class='bx bx-arrow-back'></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script>
    // Toggle sidebar
    document.querySelector('.bx-menu').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');

        if (sidebar.style.width === '260px') {
            sidebar.style.width = '80px';
            content.style.marginLeft = '80px';
            document.querySelectorAll('.side-menu .text').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelectorAll('.side-menu li a').forEach(el => {
                el.style.justifyContent = 'center';
            });
            document.querySelectorAll('.side-menu li a i').forEach(el => {
                el.style.marginRight = '0';
                el.style.fontSize = '1.5rem';
            });
        } else {
            sidebar.style.width = '260px';
            content.style.marginLeft = '260px';
            document.querySelectorAll('.side-menu .text').forEach(el => {
                el.style.display = 'block';
            });
            document.querySelectorAll('.side-menu li a').forEach(el => {
                el.style.justifyContent = 'flex-start';
            });
            document.querySelectorAll('.side-menu li a i').forEach(el => {
                el.style.marginRight = '1rem';
                el.style.fontSize = '1.2rem';
            });
        }
    });

    // Initialize DataTable
    $(document).ready(function() {
        $('#tabeldata').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
            }
        });
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
    </script>
</body>

</html>