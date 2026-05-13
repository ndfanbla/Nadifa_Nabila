<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Report - RSUD Sultan Suriansyah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-file-medical"></i> SERVICE REPORT</h1>
        <div class="header-badge">
            <i class="fas fa-database"></i> Total: <span id="totalCount">0</span>
        </div>
    </div>

    <!-- Form -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-pen-alt"></i>
            <h2 id="formTitle">Tambah Service Report</h2>
        </div>
        <div class="card-body">
            <form id="serviceForm">
                <input type="hidden" id="editId" value="">
                <div class="form-grid">
                    <div class="form-group"><label>📅 Tanggal</label><input type="date" id="tanggal" required></div>
                    <div class="form-group"><label>🏥 Ruangan</label><input type="text" id="ruangan" placeholder="Rehab Medik" required></div>
                    <div class="form-group"><label>🖨️ Prasarana</label><input type="text" id="prasarana" value="Printer" required></div>
                    <div class="form-group"><label>🏷️ Merk</label><input type="text" id="merk" required></div>
                    <div class="form-group"><label>📟 Type/Model</label><input type="text" id="model" required></div>
                    <div class="form-group"><label>🔢 S/N (Kode Alat)</label><input type="text" id="sn_code" value="-"></div>
                    <div class="form-group"><label>📅 Kalibrasi Terakhir</label><input type="text" id="last_calibration" value="-"></div>
                    <div class="form-group"><label>🏢 Distributor</label><input type="text" id="distributor" value="-"></div>
                    <div class="form-group"><label>📆 Tahun</label><input type="text" id="year" value="-"></div>
                </div>

               <div class="section-title"><i class="fas fa-clipboard-list"></i> Pemeriksaan</div>
<div class="pemeriksaan-grid">
    <label class="check-item"><input type="checkbox" value="Trouble Shooting" id="chk_trouble"> Trouble Shooting</label>
    <label class="check-item"><input type="checkbox" value="Uji Energi / Power Supply" id="chk_function"> Uji Energi / Power Supply</label>
    <label class="check-item"><input type="checkbox" value="Uji Fisik (kabel, konektor, casing)" id="chk_physical"> Uji Fisik (kabel, konektor, casing)</label>
    <label class="check-item"><input type="checkbox" value="Mekanik (sensor, roller, paper feed)" id="chk_mechanical"> Mekanik (sensor, roller, paper feed)</label>
    <label class="check-item"><input type="checkbox" value="Pemantauan Alat (log error, status device)" id="chk_warming"> Pemantauan Alat (log error, status device)</label>
    <label class="check-item"><input type="checkbox" value="Kelengkapan Aksesori (kabel USB, power, dll)" id="chk_accessories"> Kelengkapan Aksesori (kabel USB, power, dll)</label>
    <label class="check-item"><input type="checkbox" value="Setting Parameter / Konfigurasi (IP, driver, port)" id="chk_parameter"> Setting Parameter / Konfigurasi (IP, driver, port)</label>

                    <label class="check-item"><input type="checkbox" value="Mekanik" id="chk_mechanical"> Mekanik</label>
                    <label class="check-item"><input type="checkbox" value="Pemanasan Alat" id="chk_warming"> Pemanasan Alat</label>
                    <label class="check-item"><input type="checkbox" value="Trouble Shooting" id="chk_trouble"> Trouble Shooting</label>
                </div>

                <div class="section-title"><i class="fas fa-exclamation-triangle"></i> Permasalahan dan Solusi</div>
                <div class="form-group"><textarea id="problem_solution" rows="3" placeholder="Maintenance Catridge Penuh, perlu di ganti..." required></textarea></div>

                <div class="section-title"><i class="fas fa-info-circle"></i> Keterangan</div>
                <div class="form-group"><textarea id="description" rows="2" placeholder="Pembelian Maintenance Catridge MC-G04..."></textarea></div>

                <div class="section-title"><i class="fas fa-chart-line"></i> Hasil Pemeriksaan</div>
                <div class="radio-group" id="hasilGroup">
                    <label class="radio-item"><input type="radio" name="hasil" value="Berfungsi Baik"> Berfungsi Baik</label>
                    <label class="radio-item"><input type="radio" name="hasil" value="Tidak Berfungsi"> Tidak Berfungsi</label>
                    <label class="radio-item"><input type="radio" name="hasil" value="Berfungsi Tidak Sempurna"> Berfungsi Tidak Sempurna</label>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary"><i class="fas fa-undo"></i> Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table"></i>
            <h2>Riwayat Service Report</h2>
        </div>
        <div class="card-body">
            <div class="table-wrapper">
                <table id="reportTable">
                    <thead>
                        <tr><th>No. Report</th><th>Tanggal</th><th>Ruangan</th><th>Prasarana</th><th>Merk/Model</th><th>Hasil</th><th>Aksi</th></tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>