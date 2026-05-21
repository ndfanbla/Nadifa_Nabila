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
        width: 18cm;
        text-align: center;
        border-bottom: 2px solid #000000;
        margin-bottom: 20px;
        background: white;
    }

    .logo-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0;
    }

    .logo-left, .logo-right {
        flex: 0 0 auto;
    }

    .logo {
        width: 100px;
        height: 100px;
        object-fit: contain;
    }

    .kop-text {
        text-align: center;
        flex: 1;
    }
    
        /* Baris 1: PEMERINTAH KOTA BANJARMASIN */
    .baris1 {
        font-family: 'Arial', sans-serif;
        font-weight: normal;
        font-size: 18pt;
        letter-spacing: normal;
        text-transform: uppercase;
        color: black;
        margin: 2px 0;
        line-height: 1.0;
        text-align: center;
    }

    /* Baris 2: RSUD SULTAN SURIANSYAH (BOLD) */
    .baris2 {
        font-family: 'Arial', sans-serif;
        font-weight: bold;
        font-size: 18pt;
        letter-spacing: normal;
        text-transform: uppercase;
        color: black;
        margin: 2px 0 4px 0;
        line-height: 1.0;
        text-align: center;
    }

    /* CSS KHUSUS SERVICE REPORT - TERPISAH DARI KOP SURAT */
    .title-service h4 {
        font-family: 'Times New Roman', Times, serif;
        font-size: 13pt;
        font-weight: bold;
        text-align: center;
        color: black;
    }

    .service-report-left {
        font-family: 'Arial', sans-serif;
        font-size: 11pt;
        font-weight: bold;
        float: left;
        margin-top: 10px;
    }

    .service-report-right {
        font-family: 'Arial', sans-serif;
        font-size: 11pt;
        font-weight: bold;
        float: right;
        margin-top: 10px;
    }

    /* Clearfix */
    .title-service {
        overflow: auto;
    }

    .service-left {
        font-family: 'Times New Roman', Times, serif;
        font-size: 11pt;
        float: left;
    }

    .service-right {
        font-family: 'Times New Roman', Times, serif;
        font-size: 11pt;
        float: right;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        font-family: 'Arial', sans-serif;
        font-size: 11pt;
    }

    .info-label {
        font-weight: bold;
        color: black;
    }

    .info-value {
        font-weight: normal;
        color: black;
    }

    /* Alamat dan kontak biasa (termasuk Telepon) */
    .alamat, .kontak {
        font-family: 'Arial', sans-serif;
        font-weight: normal;
        font-size: 9pt;
        letter-spacing: normal;
        text-transform: none;
        color: black;
        margin: 0;
        line-height: 1.0;
        text-align: center;
    }

    /* Link website dan email */
    .link {
        font-family: 'Arial', sans-serif;
        font-weight: normal;
        font-size: 9pt;
        text-decoration: underline;
        color: #0066cc;
        line-height: 1.0;
        text-align: center;
    }

    .link:hover {
        color: #004499;
        text-decoration: underline;
    }

    /* Service Type - CHECKBOX HORIZONTAL */
    .service-type {
        margin: 10px 0;
        padding: 5px 0;
        display: flex;
        
    }
    .service-type-label {
        font-weight: bold;
        font-size: 11pt;
        margin-bottom: 5px;
    }
    .service-type-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2px 20px;
        margin-left: 30px;
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
        font-size: 11pt;
    }
    .info-alat-two-columns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2px 30px;
    }
    .info-alat-row {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
    }
    .info-alat-label {
        width: 124px;
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
        font-size: 11pt;
    }
    .pemeriksaan-grid-print {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2px 20px;
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
        margin-top: 2px;
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
            margin: 1cm 2cm 2.5cm 2cm;
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
                <div class="baris1">PEMERINTAH KOTA BANJARMASIN</div>
                <div class="baris2">RSUD SULTAN SURIANSYAH</div>
                <div class="alamat">Jalan Rantauan Darat RT.04 RW.01 Banjarmasin Kode Pos 70246</div>
                <div class="kontak">Telepon: (0511)6782000 / (0511)6783707</div>
                <div style="display:flex; font-size: 9pt; justify-content: center; gap: 4px;">
                    <span>Website: </span>
                    <span class="kontak">
                        <a href="http://rsudss.banjarmasinkota.go.id" class="link" target="_blank">http://rsudss.banjarmasinkota.go.id</a>
                    </span>
                    <span>E-mail: </span>
                    <span class="kontak">
                        <a href="mailto:rsudsultansuriansyah@gmail.com" class="link" target="_blank">rsudsultansuriansyah@gmail.com</a>
                    </span>
                </div>
            </div>
            <div class="logo-right">
                <img src="<?= $logo_rss ?>" alt="Logo RSS" class="logo" 
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2275%22 height=%2275%22%3E%3Crect width=%2275%22 height=%2275%22 fill=%22%232c6e2a%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22white%22%3ELogo%3C/text%3E%3C/svg%3E'">
            </div>
        </div>
    </div>
    <hr style="posision:relative; transform: translateY(-19px);">

<!-- TITLE SERVICE REPORT -->
<div class="title-service">
    <h4><u>SERVICE REPORT</u></h4>
    <h4><u>RSUD SULTAN SURIANSYAH BANJARMASIN</u></h4>
</div>
<br>

<!-- Service Report No & Tanggal -->
<div class="service-left"><b>Service Report No : </b>115/SR/V/2026</div>
<div class="service-right"><b>Tanggal: 18 May 2026</b></div>

<!-- Clearfix agar tidak mengganggu layout bawahnya -->
<div style="clear: both;"></div>

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
                <div class="info-alat-label">Merk</div>
                <div class="info-alat-value">: <?= isset($data['brand']) && $data['brand'] ? $data['brand'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">Kalibrasi Terakhir</div>
                <div class="info-alat-value">: <?= isset($data['last_calibration']) && $data['last_calibration'] ? $data['last_calibration'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">Type/Model</div>
                <div class="info-alat-value">: <?= isset($data['model']) && $data['model'] ? $data['model'] : '-' ?></div>
            </div>
            <div class="info-alat-row">
                <div class="info-alat-label">Distributor</div>
                <div class="info-alat-value">: <?= isset($data['distributor']) && $data['distributor'] ? $data['distributor'] : '-' ?></div>
            </div>
            <div style="margin-top: 20px;" class="info-alat-row">
                <div class="info-alat-label">S/N (Kode Alat)</div>
                <div class="info-alat-value">: <?= isset($data['sn_code']) && $data['sn_code'] ? $data['sn_code'] : '-' ?></div>
            </div>
            <div style="margin-top: 20px;" class="info-alat-row">
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
           <br>User<br><br><br><br><br>(...........................................)
        </div>
        <div>
            Mengetahui,<br>Ka. PDE<br><br><br><br><br>(...........................................)
        </div>
        <div>
            <br>Staff PDE<br><br><br><br><br>(...........................................)
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