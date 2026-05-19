<?php
require_once 'config/database.php';
$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM service_reports WHERE id = $id");
$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Service Report</title>
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Times New Roman', Times, serif;
        background: white;
    }
    .print-container {
        width: 100%;
        background: white;
        margin: 0;
        padding: 0;
    }
    
/* KOP SURAT - SESUAI SPESIFIKASI */
.kop {
    text-align: center;
    border-bottom: 4px double #000000;  /* GARIS DOUBLE HITAM 4px */
    padding-bottom: 8px;
    margin-bottom: 20px;
}

.logo-row {
    display: flex;
    justify-content: space-between;  /* LOGO KIRI DAN KANAN */
    align-items: center;
    gap: 0;
    margin-bottom: 5px;
}

.logo-left, .logo-right {
    flex: 0 0 auto;
}

.logo {
    width: 75px;   /* UKURAN LOGO 75x75px */
    height: 75px;
    object-fit: contain;
}

.kop-text {
    text-align: center;
    flex: 1;
}

.kop h2 {
    font-family: 'Times New Roman', Times, serif;
    font-size: 14px;        /* UKURAN 14px */
    letter-spacing: 0px;
    margin: 2px 0;
    font-weight: 700;       /* BOLD */
}

.kop h3 {
    font-family: 'Times New Roman', Times, serif;
    font-size: 18px;        /* UKURAN 18px */
    margin: 2px 0;
    font-weight: 700;       /* BOLD */
}

.kop p {
    font-family: 'Times New Roman', Times, serif;
    font-size: 9px;         /* UKURAN 9px NORMAL */
    margin: 2px 0;
    line-height: 1.3;
    font-weight: 400;       /* NORMAL (TIDAK BOLD) */
}
    
    /* Title Service Report - DIPERBESAR */
    .title-service {
        text-align: center;
        margin: 15px 0 20px 0;
    }
    .title-service h4 {
        font-size: 18px;  /* DIPERBESAR DARI 14px */
        text-decoration: underline;
        margin: 3px 0;
        font-weight: bold;
    }
    
    /* Layout No dan Tanggal */
    .info-row {
        display: flex;
        margin-bottom: 8px;
        line-height: 1.4;
    }
    .info-label {
        width: 140px;
        font-weight: bold;
    }
    .info-value {
        flex: 1;
    }
    
    /* Service Type - CHECKBOX HORIZONTAL */
    .service-type {
        margin: 10px 0;
        padding: 5px 0;
    }
    .service-type-label {
        font-weight: bold;
        font-size: 11pt;
        margin-bottom: 5px;
    }
    .service-type-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 5px 20px;
        margin-left: 0;
    }
    .service-type-item {
        font-size: 10pt;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Info Alat - BORDER ATAS BAWAH */
    .info-alat {
        margin: 10px 0;
        padding: 8px 0;
    
    }
    .info-alat-title {
        font-weight: bold;
        margin-bottom: 8px;
        font-size: 11pt;
    }
    .info-alat-two-columns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px 30px;
    }
    .info-alat-row {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
    }
    .info-alat-label {
        font-weight: bold;
        width: 110px;
        flex-shrink: 0;
    }
    .info-alat-value {
        flex: 1;
        word-break: break-word;
    }
    
    /* Pemeriksaan - BORDER ATAS BAWAH */
    .check-section {
        margin: 10px 0;
        padding: 8px 0;
    }
    .check-section-title {
        font-weight: bold;
        margin-bottom: 8px;
        font-size: 11pt;
    }
    .pemeriksaan-grid-print {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px 20px;
    }
    .check-item-print {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 10pt;
        line-height: 1.4;
    }
    .check-box {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 14px;
        height: 14px;
        border: 1px solid #000000;
        font-weight: bold;
        font-size: 10px;
        flex-shrink: 0;
    }
    
    /* Permasalahan - BORDER FULL */
    .problem-section {
        margin: 0px 0;
        border: 1px solid #000000;
        padding: 8px;
    }
   
    /* Keterangan - BORDER FULL */
    .keterangan-section {
        margin: 0px 0;
        border: 1px solid #000000;
        padding: 8px;
    }
    .section-title {
        font-weight: bold;
        margin-bottom: 5px;
        font-size: 11pt;
    }
    .problem-content, .keterangan-content {
        line-height: 1.4;
        margin-top: 3px;
        font-size: 10pt;
    }

    /* Hasil Pemeriksaan - BORDER ATAS BAWAH */
    .hasil-section {
        margin: 10px 0;
        padding: 8px 0;
    }
    .hasil-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .hasil-label {
        font-weight: bold;
        width: 140px;
    }
    .hasil-options {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .hasil-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    /* Tanda Tangan */
    .signature {
        margin-top: 5px;
        display: flex;
        justify-content: space-between;
        text-align: center;
    }
    .signature div {
        width: 180px;
        font-size: 12pt;
    }
    .signature-line {
        margin-top: 40px;
        border-top: 1px solid #000000;
    }
    
    /* PRINT STYLES */
    @media print {
        @page {
            size: A4;
            margin: 2cm 2cm 2.5cm 2cm;
        }
        body {
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print-container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
        .no-print {
            display: none !important;
        }
        button {
            display: none !important;
        }
        .check-box {
            border: 1px solid #000000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
    
    button {
        margin: 20px;
        padding: 10px 20px;
        background: #2d6a2a;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 1000;
    }
    </style>
</head>
<body>
<div class="print-container">
    <button class="no-print" onclick="window.print()">🖨️ Cetak / Print</button>
    
<!-- KOP SURAT - SESUAI SPESIFIKASI -->
<div class="kop">
    <div class="logo-row">
        <?php
        $logo_pemko = 'img/logo_pemko.png';
        $logo_rss = 'img/logo_rss.png';
        ?>
        <div class="logo-left">
            <img src="<?= $logo_pemko ?>" alt="Logo Pemko" class="logo"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2275%22 height=%2275%22%3E%3Crect width=%2275%22 height=%2275%22 fill=%22%232c6e2a%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22white%22%3ELogo%3C/text%3E%3C/svg%3E'">
        </div>
        <div class="kop-text">
            <h2>PEMERINTAH KOTA BANJARMASIN</h2>
            <h3>RSUD SULTAN SURIANSYAH</h3>
            <p>Jalan Rantauan Darat RT.04 RW.01 Banjarmasin Kode Pos 70246</p>
            <p>Telepon: (0511)6782000 / (0511)6783707</p>
            <p>Website: http://rsudss.banjarmasinkota.go.id</p>
            <p>Email: rsudsultansuriansyah@gmail.com</p>
        </div>
        <div class="logo-right">
            <img src="<?= $logo_rss ?>" alt="Logo RSS" class="logo" 
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2275%22 height=%2275%22%3E%3Crect width=%2275%22 height=%2275%22 fill=%22%232c6e2a%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22white%22%3ELogo%3C/text%3E%3C/svg%3E'">
        </div>
    </div>
</div>

    <!-- TITLE -->
    <div class="title-service">
        <h4>SERVICE REPORT</h4>
        <h4>RSUD SULTAN SURIANSYAH BANJARMASIN</h4>
    </div>

    <!-- Service Report No & Tanggal -->
    <div class="info-row">
        <div class="info-label">Service Report No :</div>
        <div class="info-value"><?= isset($data['report_number']) ? $data['report_number'] : '-' ?></div>
        <div class="info-label" style="width:80px">Tanggal :</div>
        <div class="info-value"><?= isset($data['report_date']) ? date('d F Y', strtotime($data['report_date'])) : '-' ?></div>
    </div>

    <!-- Service Type -->
    <div class="service-type">
        <div class="service-type-label">Service Report Type :</div>
        <div class="service-type-grid">
            <div class="service-type-item">
                <span class="check-box">□</span> Instalasi Baru
            </div>
            <div class="service-type-item">
                <span class="check-box">□</span> Pemantauan Fungsi/Check
            </div>
            <div class="service-type-item">
                <span class="check-box">□</span> Kalibrasi
            </div>
            <div class="service-type-item">
                <span class="check-box">□</span> Pemeliharaan/Maintenance
            </div>
            <div class="service-type-item">
                <span class="check-box">□</span> Perbaikan/Service
            </div>
            <div class="service-type-item">
                <span class="check-box">□</span> Recall/Penarikan
            </div>
        </div>
    </div>

    <!-- Info Alat -->
    <div class="info-alat">
        <div class="info-alat-title">Nama Sarana</div>
        <div class="info-alat-two-columns">
            <div class="info-alat-row">
                <div class="info-alat-label">Prasarana</div>
                <div class="info-alat-value">: <?= isset($data['facility_type']) && $data['facility_type'] ? $data['facility_type'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">Ruangan</div>
                <div class="info-alat-value">: <?= isset($data['room_name']) && $data['room_name'] ? $data['room_name'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">Kalibrasi Terakhir</div>
                <div class="info-alat-value">: <?= isset($data['last_calibration']) && $data['last_calibration'] ? $data['last_calibration'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">Merk</div>
                <div class="info-alat-value">: <?= isset($data['brand']) && $data['brand'] ? $data['brand'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">Distributor</div>
                <div class="info-alat-value">: <?= isset($data['distributor']) && $data['distributor'] ? $data['distributor'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">Type/Model</div>
                <div class="info-alat-value">: <?= isset($data['model']) && $data['model'] ? $data['model'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">S/N (Kode Alat)</div>
                <div class="info-alat-value">: <?= isset($data['sn_code']) && $data['sn_code'] ? $data['sn_code'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">Tahun</div>
                <div class="info-alat-value">: <?= isset($data['year']) && $data['year'] ? $data['year'] : '-' ?></div>
            </div>
        </div>
    </div>

    <!-- Pemeriksaan -->
    <div class="check-section">
        <div class="check-section-title">Pemeriksaan</div>
        <div class="pemeriksaan-grid-print">
            <div class="check-item-print">
                <span class="check-box"><?= (isset($data['troubleshooting']) && $data['troubleshooting'] == 1) ? '✓' : '□' ?></span>
                <span>Trouble Shooting</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= (isset($data['function_test']) && $data['function_test'] == 1) ? '✓' : '□' ?></span>
                <span>Uji Energi / Power Supply</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= (isset($data['physical_test']) && $data['physical_test'] == 1) ? '✓' : '□' ?></span>
                <span>Uji Fisik (kabel, konektor, casing)</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= (isset($data['mechanical_check']) && $data['mechanical_check'] == 1) ? '✓' : '□' ?></span>
                <span>Mekanik (sensor, roller, paper feed)</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= (isset($data['warming_check']) && $data['warming_check'] == 1) ? '✓' : '□' ?></span>
                <span>Pemantauan Alat (log error, status device)</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= (isset($data['accessories_check']) && $data['accessories_check'] == 1) ? '✓' : '□' ?></span>
                <span>Kelengkapan Aksesori (kabel USB, power, dll)</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= (isset($data['parameter_setting']) && $data['parameter_setting'] == 1) ? '✓' : '□' ?></span>
                <span>Setting Parameter / Konfigurasi (IP, driver, port)</span>
            </div>
        </div>
    </div>

    <!-- Permasalahan dan Solusi -->
    <div class="problem-section">
        <div class="section-title">Permasalahan dan Solusi:</div>
        <div class="problem-content">
             <?= isset($data['problem_solution']) && $data['problem_solution'] ? nl2br(htmlspecialchars($data['problem_solution'])) : '-' ?>
        </div>
    </div>

    <!-- Keterangan -->
    <div class="keterangan-section">
        <div class="section-title">Keterangan:</div>
        <div class="keterangan-content">
             <?= isset($data['description']) && $data['description'] ? nl2br(htmlspecialchars($data['description'])) : '-' ?>
        </div>
    </div>

    <!-- Hasil Pemeriksaan -->
    <div class="hasil-section">
        <div class="hasil-row">
            <div class="hasil-label">Hasil Pemeriksaan</div>
            <div class="hasil-options">
                <div class="hasil-item">
                    <span class="check-box"><?= (isset($data['result_status']) && $data['result_status'] == 'Berfungsi Baik') ? '✓' : '□' ?></span>
                    <span>Berfungsi Baik</span>
                </div>
                <div class="hasil-item">
                    <span class="check-box"><?= (isset($data['result_status']) && $data['result_status'] == 'Tidak Berfungsi') ? '✓' : '□' ?></span>
                    <span>Tidak Berfungsi</span>
                </div>
                <div class="hasil-item">
                    <span class="check-box"><?= (isset($data['result_status']) && $data['result_status'] == 'Berfungsi Tidak Sempurna') ? '✓' : '□' ?></span>
                    <span>Berfungsi Tidak Sempurna</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature">
        <div>
           <br>User<br><br><br><br><br>(.............................................)
        </div>
        <div>
            Mengetahui,<br>Ka. PDE<br><br><br><br><br>(.............................................)
        </div>
        <div>
            <br>Staff PDE<br><br><br><br><br>(.............................................)
        </div>
    </div>
</div>

<script>
    (function() {
        setTimeout(function() {
            window.print();
        }, 500);
    })();
</script>
</body>
</html>