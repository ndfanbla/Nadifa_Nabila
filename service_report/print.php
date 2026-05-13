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
       <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Times New Roman', Times, serif;
        background: white;
        padding: 20px;
    }
    .print-container {
        max-width: 210mm;
        margin: 0 auto;
        background: white;
    }
    
    /* KOP SURAT */
    .kop {
        text-align: center;
        border-bottom: 2px solid #000000;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .logo-row {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 30px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .logo {
        width: 70px;
        height: 70px;
        object-fit: contain;
    }
    .kop h2 {
        font-size: 14px;
        letter-spacing: 1px;
        margin: 2px 0;
        font-weight: bold;
    }
    .kop h3 {
        font-size: 16px;
        margin: 2px 0;
        font-weight: bold;
    }
    .kop p {
        font-size: 9px;
        margin: 1px 0;
        line-height: 1.4;
    }
    
    /* Title Service Report */
    .title-service {
        text-align: center;
        margin: 25px 0 20px 0;
    }
    .title-service h4 {
        font-size: 16px;
        text-decoration: underline;
        margin: 3px 0;
        font-weight: bold;
    }
    
    /* Layout 2 kolom dengan border hitam */
    .info-row {
        display: flex;
        margin-bottom: 6px;
        line-height: 1.5;
    }
    .info-label {
        width: 170px;
        font-weight: bold;
    }
    .info-value {
        flex: 1;
    }
    
    /* Service Type dengan border */
    .service-type {
        margin: 15px 0;
        padding: 10px 0;
        border-top: 1px solid #000000;
        border-bottom: 1px solid #000000;
    }
    .service-type-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px 15px;
    }
    .service-type-item {
        font-size: 10pt;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .check-symbol {
        display: inline-block;
        width: 14px;
        text-align: center;
    }
    
    /* Info Alat dengan border */
    .info-alat {
        margin: 15px 0;
        padding: 10px 0;
        border-top: 1px solid #000000;
        border-bottom: 1px solid #000000;
    }
    
    /* Pemeriksaan Grid dengan border hitam */
    .check-section {
        margin: 15px 0;
        border: 1px solid #000000;
        padding: 12px;
    }
    .check-section-title {
        font-weight: bold;
        margin-bottom: 10px;
        font-size: 11pt;
    }
    .pemeriksaan-grid-print {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px 20px;
    }
    .check-item-print {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 10pt;
        line-height: 1.4;
    }
    .check-box {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 18px;
        height: 18px;
        border: 1.5px solid #000000;
        font-weight: bold;
        font-size: 12px;
        background: white;
        flex-shrink: 0;
    }
    
    /* Permasalahan dan Keterangan */
    .problem-section, .keterangan-section {
        margin: 12px 0;
    }
    .problem-row, .keterangan-row {
        display: flex;
        margin-bottom: 8px;
        line-height: 1.5;
    }
    .problem-label, .keterangan-label {
        width: 170px;
        font-weight: bold;
    }
    .problem-value, .keterangan-value {
        flex: 1;
    }
    
    /* Hasil Pemeriksaan */
    .hasil-section {
        margin: 15px 0;
        padding: 10px 0;
        border-top: 1px solid #000000;
        border-bottom: 1px solid #000000;
    }
    .hasil-row {
        display: flex;
        margin-bottom: 8px;
        line-height: 1.5;
    }
    .hasil-label {
        width: 170px;
        font-weight: bold;
    }
    .hasil-options {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }
    .hasil-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .hasil-item .check-box {
        width: 16px;
        height: 16px;
    }
    
    /* Tanda Tangan */
    .signature {
        margin-top: 50px;
        display: flex;
        justify-content: space-between;
        text-align: center;
    }
    .signature div {
        width: 200px;
    }
    .signature-line {
        margin-top: 50px;
        border-top: 1px solid #000000;
        width: 100%;
    }
    
    hr {
        margin: 10px 0;
        border: 0.5px solid #000000;
    }
    
    /* PRINT STYLES */
    @media print {
        @page {
            size: A4;
            margin: 10mm;
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
        .service-type,
        .info-alat,
        .check-section,
        .hasil-section {
            border-color: #000000 !important;
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
    <button class="no-print" onclick="window.print()" style="position:fixed; top:10px; right:10px;">🖨️ Cetak / Print</button>
    
    <!-- KOP SURAT -->
    <div class="kop">
        <div class="logo-row">
            <?php
            $logo_pemko = 'img/logo_pemko.png';
            $logo_rss = 'img/logo_rss.png';
            ?>
            <img src="<?= $logo_pemko ?>" alt="Logo Pemko" class="logo"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2270%22 height=%2270%22%3E%3Crect width=%2270%22 height=%2270%22 fill=%22%232c6e2a%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22white%22%3ELogo%3C/text%3E%3C/svg%3E'">
            <div>
                <h2>PEMERINTAH KOTA BANJARMASIN</h2>
                <h3>RSUD SULTAN SURIANSYAH</h3>
                <p>Jalan Bandaraya, Dataran RT.04 RW.01 Banjarmasin Kota Pos 70246</p>
                <p>Telepon: (0511)6782000/ (0511)6783707</p>
                <p>Website: http://rsudss.banjarmasinkota.go.id E-mail: rsudsultansuriansyah@gmail.com</p>
            </div>
            <img src="<?= $logo_rss ?>" alt="Logo RSS" class="logo" 
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2270%22 height=%2270%22%3E%3Crect width=%2270%22 height=%2270%22 fill=%22%232c6e2a%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22white%22%3ELogo%3C/text%3E%3C/svg%3E'">
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
        <div class="info-value"><?= $data['report_number'] ?></div>
        <div class="info-label" style="width:80px">Tanggal :</div>
        <div class="info-value"><?= date('d F Y', strtotime($data['report_date'])) ?></div>
    </div>

    <!-- Service Type -->
    <div class="service-type">
        <!-- <div class="service-type-grid"> -->
            <div class="service-type-item"><span class="check-symbol">□</span> Instalasi Baru</div>
            <div class="service-type-item"><span class="check-symbol">□</span> Pemantauan Fungsi/Check</div>
            <div class="service-type-item"><span class="check-symbol">□</span> Kalibrasi</div>
            <div class="service-type-item"><span class="check-symbol">☑</span> Pemeliharaan/Maintenance</div>
            <div class="service-type-item"><span class="check-symbol">□</span> Perbaikan/Service</div>
            <div class="service-type-item"><span class="check-symbol">□</span> Recall/Penarikan</div>
        <!-- </div> -->
    </div>

    <!-- Info Alat -->
    <div class="info-alat">
        <div class="info-row"><div class="info-label">Nama Sarana</div><div class="info-value">: <?= $data['room_name'] ?></div></div>
        <div class="info-row"><div class="info-label">Prasarana</div><div class="info-value">: <?= $data['facility_type'] ?></div></div>
        <div class="info-row"><div class="info-label">Merk</div><div class="info-value">: <?= $data['brand'] ?></div></div>
        <div class="info-row"><div class="info-label">Type/Model</div><div class="info-value">: <?= $data['model'] ?></div></div>
                <div class="info-row"><div class="info-label">S/N (Kode Alat)</div><div class="info-value">: <?= $data['sn_code'] ?: '-' ?></div></div>
        <div class="info-row"><div class="info-label">Kalibrasi Terakhir</div><div class="info-value">: <?= $data['last_calibration'] ?: '-' ?></div></div>
        <div class="info-row"><div class="info-label">Distributor</div><div class="info-value">: <?= $data['distributor'] ?: '-' ?></div></div>
        <div class="info-row"><div class="info-label">Tahun</div><div class="info-value">: <?= $data['year'] ?: '-' ?></div></div>

    </div>

    <!-- Pemeriksaan -->
    <div class="check-section">
        <div class="check-section-title">Pemeriksaan</div>
        <div class="pemeriksaan-grid-print">
            <div class="check-item-print">
                <span class="check-box"><?= $data['troubleshooting'] ? '✓' : '□' ?></span>
                <span>Trouble Shooting</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= $data['function_test'] ? '✓' : '□' ?></span>
                <span>Uji Energi / Power Supply</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= $data['physical_test'] ? '✓' : '□' ?></span>
                <span>Uji Fisik (kabel, konektor, casing)</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= $data['mechanical_check'] ? '✓' : '□' ?></span>
                <span>Mekanik (sensor, roller, paper feed)</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= $data['warming_check'] ? '✓' : '□' ?></span>
                <span>Pemantauan Alat (log error, status device)</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= $data['accessories_check'] ? '✓' : '□' ?></span>
                <span>Kelengkapan Aksesori (kabel USB, power, dll)</span>
            </div>
            <div class="check-item-print">
                <span class="check-box"><?= $data['parameter_setting'] ? '✓' : '□' ?></span>
                <span>Setting Parameter / Konfigurasi (IP, driver, port)</span>
            </div>
        </div>
    </div>

    <!-- Permasalahan dan Solusi -->
    <div class="problem-section">
        <div class="problem-row">
            <div class="problem-label">Permasalahan dan Solusi</div>
            <div class="problem-value">: <?= nl2br(htmlspecialchars($data['problem_solution'])) ?></div>
        </div>
    </div>

    <!-- Keterangan -->
    <div class="keterangan-section">
        <div class="keterangan-row">
            <div class="keterangan-label">Keterangan</div>
            <div class="keterangan-value">: <?= nl2br(htmlspecialchars($data['description'])) ?></div>
        </div>
    </div>

    <!-- Hasil Pemeriksaan -->
    <div class="hasil-section">
        <div class="hasil-row">
            <div class="hasil-label">Hasil Pemeriksaan</div>
            <div class="hasil-options">
                <div class="hasil-item">
                    <span class="check-box"><?= $data['result_status'] == 'Berfungsi Baik' ? '✓' : '□' ?></span>
                    <span>Berfungsi Baik</span>
                </div>
                <div class="hasil-item">
                    <span class="check-box"><?= $data['result_status'] == 'Tidak Berfungsi' ? '✓' : '□' ?></span>
                    <span>Tidak Berfungsi</span>
                </div>
                <div class="hasil-item">
                    <span class="check-box"><?= $data['result_status'] == 'Berfungsi Tidak Sempurna' ? '✓' : '□' ?></span>
                    <span>Berfungsi Tidak Sempurna</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature">
        <div>Mengetahui,<br>User<br><br><br><br>(......................)</div>
        <div>Ka. PDE<br><br><br><br>(......................)</div>
        <div>Staff PDE<br><br><br><br>(......................)</div>
    </div>
</div>
<script>
    (function() {
        setTimeout(function() {
            window.print();
        }, 500);
        
        window.onafterprint = function() {
            // window.close(); // Uncomment jika ingin auto close
        };
    })();
</script>
</body>
</html>