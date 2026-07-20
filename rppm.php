<?php
require_once 'koneksi.php';

// ==============================================================================
// 1. SETUP DATABASE
// ==============================================================================
try {
    // Tabel RPPM
    $pdo->exec("CREATE TABLE IF NOT EXISTS rppm (
        id INT AUTO_INCREMENT PRIMARY KEY,
        penulis VARCHAR(255) NOT NULL,
        asal_sekolah VARCHAR(255) NOT NULL,
        fase VARCHAR(50),
        model_pembelajaran VARCHAR(100),
        semester_bulan VARCHAR(100),
        minggu_ke VARCHAR(50),
        topik VARCHAR(255),
        cp TEXT,
        tp TEXT,
        kegiatan_inti TEXT,
        tanggal_dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Tabel RPPH
    $pdo->exec("CREATE TABLE IF NOT EXISTS rpph (
        id INT AUTO_INCREMENT PRIMARY KEY,
        semester_bulan VARCHAR(100),
        kelompok VARCHAR(100),
        minggu_hari VARCHAR(100),
        tema VARCHAR(255),
        subtema VARCHAR(255),
        kd TEXT,
        tujuan TEXT,
        kegiatan_pembuka TEXT,
        tanggal_dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// ==============================================================================
// 2. LOGIKA INSERT DATA (CRUD - CREATE)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['simpan_rppm'])) {
        // Proses penggabungan Kegiatan Inti dari form tabel ke string text
        $kegiatan_inti = '';
        if (isset($_POST['hari']) && is_array($_POST['hari'])) {
            for ($i = 0; $i < count($_POST['hari']); $i++) {
                if (!empty(trim($_POST['keg_inti'][$i]))) {
                    $kegiatan_inti .= "- " . $_POST['hari'][$i] . ": " . $_POST['keg_inti'][$i];
                    if (!empty(trim($_POST['alat_bahan'][$i]))) {
                        $kegiatan_inti .= " (Alat & Bahan: " . $_POST['alat_bahan'][$i] . ")";
                    }
                    $kegiatan_inti .= "\n";
                }
            }
        } else {
            $kegiatan_inti = $_POST['kegiatan_inti'] ?? '';
        }
        
        $stmt = $pdo->prepare("INSERT INTO rppm (penulis, asal_sekolah, fase, model_pembelajaran, semester_bulan, minggu_ke, topik, cp, tp, kegiatan_inti) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['penulis'], $_POST['asal_sekolah'], $_POST['fase'], $_POST['model_pembelajaran'], 
            $_POST['semester_bulan'], $_POST['minggu_ke'], $_POST['topik'], 
            $_POST['cp'], $_POST['tp'], $kegiatan_inti
        ]);
        header("Location: rppm.php?status=success_add");
        exit;
    } 
    elseif (isset($_POST['simpan_rpph'])) {
        // Konversi Array KD ke string dipisah koma
        $kd = isset($_POST['kd']) ? implode(', ', $_POST['kd']) : '';
        
        $stmt = $pdo->prepare("INSERT INTO rpph (semester_bulan, kelompok, minggu_hari, tema, subtema, kd, tujuan, kegiatan_pembuka) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['semester_bulan'], $_POST['kelompok'], $_POST['minggu_hari'], 
            $_POST['tema'], $_POST['subtema'], $kd, $_POST['tujuan'], $_POST['kegiatan_pembuka']
        ]);
        header("Location: rppm.php?status=success_add");
        exit;
    }
    elseif (isset($_POST['hapus_rppm'])) {
        $stmt = $pdo->prepare("DELETE FROM rppm WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: rppm.php?status=success_delete");
        exit;
    }
    elseif (isset($_POST['hapus_rpph'])) {
        $stmt = $pdo->prepare("DELETE FROM rpph WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: rppm.php?status=success_delete");
        exit;
    }
}

// ==============================================================================
// 3. LOGIKA SELECT DATA (CRUD - READ)
// ==============================================================================
$data_rppm = $pdo->query("SELECT * FROM rppm ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$data_rpph = $pdo->query("SELECT * FROM rpph ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// ==============================================================================
// 4. LOGIKA CETAK
// ==============================================================================
$is_print_rppm = isset($_GET['cetak_rppm']) ? $_GET['cetak_rppm'] : null;
$is_print_rpph = isset($_GET['cetak_rpph']) ? $_GET['cetak_rpph'] : null;
$print_data = null;

if ($is_print_rppm) {
    $stmt = $pdo->prepare("SELECT * FROM rppm WHERE id = ?");
    $stmt->execute([$is_print_rppm]);
    $print_data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($is_print_rpph) {
    $stmt = $pdo->prepare("SELECT * FROM rpph WHERE id = ?");
    $stmt->execute([$is_print_rpph]);
    $print_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'header.php';
include 'sidebar.php';
?>
<style>
    /* CUSTOM STYLES FOR SIMPAUD THEME */
    :root {
        --feather-beige: #F2EFE9;
        --dusty-olive: #8B8C7A;
        --herb-leaf: #7A916E;
        --night-green: #1A2F22;
    }
    
    .nav-pills-custom .nav-link {
        color: var(--dusty-olive);
        background-color: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-right: 10px;
        font-weight: 700;
        padding: 10px 24px;
        transition: all 0.2s;
    }
    .nav-pills-custom .nav-link:hover {
        background-color: var(--feather-beige);
    }
    .nav-pills-custom .nav-link.active {
        background-color: var(--night-green);
        color: #fff;
        border-color: var(--night-green);
        box-shadow: 0 4px 10px rgba(26, 47, 34, 0.2);
    }
    
    .custom-table th { background-color: #f8f9fa; color: var(--dusty-olive); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
    .custom-table td { vertical-align: middle; color: #333; }
    
    .modal-header-custom { background-color: var(--feather-beige); border-bottom: 2px solid var(--dusty-olive); }
    .modal-title { color: var(--night-green); font-weight: bold; }
    
    /* ==============================================
       PRINT SPECIFIC CSS (KUITANSI / DOKUMEN RESMI)
       ============================================== */
    @media print {
        @page { size: A4 portrait; margin: 1.5cm; }
        body { background: #fff !important; color: #000 !important; font-family: 'Times New Roman', Times, serif; font-size: 11pt; }
        
        .d-print-none, .sidebar, .header, nav, header, .navbar, .topbar { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        
        .print-layout { display: block !important; width: 100%; }
        
        .kop-surat { text-align: center; border-bottom: 4px double #000; padding-bottom: 15px; margin-bottom: 20px; }
        .kop-surat h3 { margin: 0; font-size: 16pt; font-weight: bold; }
        .kop-surat h4 { margin: 5px 0; font-size: 18pt; font-weight: bold; letter-spacing: 1px; }
        .kop-surat p { margin: 0; font-size: 11pt; }
        
        .identitas-table { width: 100%; margin-bottom: 20px; font-size: 11pt; }
        .identitas-table td { padding: 4px 0; vertical-align: top; border: none; }
        
        h5 { font-size: 12pt; font-weight: bold; margin-top: 20px; text-transform: uppercase; }
        .content-box { border: 1px solid #000; padding: 15px; min-height: 100px; margin-bottom: 15px; border-radius: 5px; }
        
        .ttd-area { width: 100%; margin-top: 50px; page-break-inside: avoid; }
        .ttd-area td { text-align: center; width: 50%; }
    }
</style>

<!-- ==============================================================
     AREA CETAK DOKUMEN RESMI (HANYA MUNCUL SAAT ADA PARAMETER CETAK)
     ============================================================== -->
<?php if ($print_data): ?>
<div class="d-none d-print-block print-layout">
    
    <!-- LAYOUT CETAK RPPM -->
    <?php if ($is_print_rppm): ?>
        <h4 style="text-align:center; font-weight:bold; text-decoration:underline; margin-bottom: 20px;">RENCANA PELAKSANAAN PEMBELAJARAN MINGGUAN (RPPM)</h4>
        
        <table class="identitas-table">
            <tr><td width="20%"><strong>Nama Guru</strong></td><td width="2%">:</td><td><?= htmlspecialchars($print_data['penulis']) ?></td></tr>
            <tr><td><strong>Asal Sekolah</strong></td><td>:</td><td><?= htmlspecialchars($print_data['asal_sekolah']) ?></td></tr>
            <tr><td><strong>Semester / Bulan</strong></td><td>:</td><td><?= htmlspecialchars($print_data['semester_bulan']) ?></td></tr>
            <tr><td><strong>Minggu Ke</strong></td><td>:</td><td><?= htmlspecialchars($print_data['minggu_ke']) ?></td></tr>
            <tr><td><strong>Topik</strong></td><td>:</td><td><?= htmlspecialchars($print_data['topik']) ?></td></tr>
        </table>
        
        <h5>A. Capaian Pembelajaran (CP)</h5>
        <div class="content-box">
            <?= nl2br(htmlspecialchars($print_data['cp'])) ?>
        </div>
        
        <h5>B. Tujuan Pembelajaran (TP)</h5>
        <div class="content-box">
            <?= nl2br(htmlspecialchars($print_data['tp'])) ?>
        </div>
        
        <h5>C. Kegiatan Inti</h5>
        <div class="content-box">
            <?= nl2br(htmlspecialchars($print_data['kegiatan_inti'])) ?>
        </div>
        
    <!-- LAYOUT CETAK RPPH -->
    <?php elseif ($is_print_rpph): ?>
        <h4 style="text-align:center; font-weight:bold; text-decoration:underline; margin-bottom: 20px;">RENCANA PELAKSANAAN PEMBELAJARAN HARIAN (RPPH)</h4>
        
        <table class="identitas-table">
            <tr><td width="20%"><strong>Kelompok Usia</strong></td><td width="2%">:</td><td><?= htmlspecialchars($print_data['kelompok']) ?></td></tr>
            <tr><td><strong>Semester / Bulan</strong></td><td>:</td><td><?= htmlspecialchars($print_data['semester_bulan']) ?></td></tr>
            <tr><td><strong>Minggu / Hari</strong></td><td>:</td><td><?= htmlspecialchars($print_data['minggu_hari']) ?></td></tr>
            <tr><td><strong>Tema / Subtema</strong></td><td>:</td><td><?= htmlspecialchars($print_data['tema']) ?> / <?= htmlspecialchars($print_data['subtema']) ?></td></tr>
        </table>
        
        <h5>A. Tujuan Kegiatan</h5>
        <div class="content-box">
            <?= nl2br(htmlspecialchars($print_data['tujuan'])) ?>
        </div>
        
        <h5>B. Deskripsi Kegiatan</h5>
        <div class="content-box">
            <?= nl2br(htmlspecialchars($print_data['kegiatan_pembuka'])) ?>
        </div>
        
        <h5>C. Penilaian (Assessment)</h5>
        <div class="content-box">
            Catatan Anekdot, Hasil Karya, dan Ceklis disesuaikan dengan instrumen penilaian kelas masing-masing.
        </div>
    <?php endif; ?>
    
    <table class="ttd-area">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala RA Darul Falah<br><br><br><br>
                <strong style="text-decoration: underline;">Yanah, S.Pd</strong>
            </td>
            <td>
                Jakarta, <?= date('d F Y') ?><br>
                Guru Kelas<br><br><br><br>
                <strong>___________________________</strong>
            </td>
        </tr>
    </table>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</div>
<?php endif; ?>


<!-- ==============================================================
     WEB UI (D-PRINT-NONE)
     ============================================================== -->
<main class="main-content d-print-none">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--night-green);">Manajemen RPPM & RPPH</h4>
            <p class="text-muted small mb-0">Kelola dokumen rencana pelaksanaan pembelajaran secara terpusat.</p>
        </div>
    </div>
    
    <!-- TAB NAVIGATION PILLS -->
    <ul class="nav nav-pills nav-pills-custom mb-4" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-rppm-tab" data-bs-toggle="pill" data-bs-target="#pills-rppm" type="button" role="tab">
                <i class="bi bi-calendar-week me-2"></i>RPPM (Mingguan)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-rpph-tab" data-bs-toggle="pill" data-bs-target="#pills-rpph" type="button" role="tab">
                <i class="bi bi-calendar-day me-2"></i>RPPH (Harian)
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="pills-tabContent">
        <!-- TAB 1: RPPM -->
        <div class="tab-pane fade show active" id="pills-rppm" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0 text-uppercase" style="color: var(--dusty-olive); font-size: 0.85rem;">Daftar Dokumen RPPM</h6>
                        <button class="btn btn-sm text-white fw-bold px-3 py-2" style="background-color: var(--herb-leaf); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#modalRPPM">
                            <i class="bi bi-plus-lg me-1"></i> Tambah RPPM
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table custom-table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="25%">Topik</th>
                                    <th width="20%">Guru / Penulis</th>
                                    <th width="20%">Minggu Ke</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($data_rppm)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data RPPM.</td></tr>
                                <?php else: $no=1; foreach($data_rppm as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('d M Y', strtotime($row['tanggal_dibuat'])) ?></td>
                                        <td class="fw-bold" style="color: var(--night-green);"><?= htmlspecialchars($row['topik']) ?></td>
                                        <td><?= htmlspecialchars($row['penulis']) ?></td>
                                        <td><?= htmlspecialchars($row['minggu_ke']) ?></td>
                                        <td class="text-center">
                                            <a href="rppm.php?cetak_rppm=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak Dokumen Resmi">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <form method="POST" action="rppm.php" style="display:inline-block;" onsubmit="return confirm('Hapus dokumen ini?');">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" name="hapus_rppm" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- TAB 2: RPPH -->
        <div class="tab-pane fade" id="pills-rpph" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0 text-uppercase" style="color: var(--dusty-olive); font-size: 0.85rem;">Daftar Dokumen RPPH</h6>
                        <button class="btn btn-sm text-white fw-bold px-3 py-2" style="background-color: var(--herb-leaf); border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#modalRPPH">
                            <i class="bi bi-plus-lg me-1"></i> Tambah RPPH
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table custom-table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="25%">Tema / Subtema</th>
                                    <th width="20%">Kelompok</th>
                                    <th width="20%">Hari / Minggu</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($data_rpph)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data RPPH.</td></tr>
                                <?php else: $no=1; foreach($data_rpph as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('d M Y', strtotime($row['tanggal_dibuat'])) ?></td>
                                        <td class="fw-bold" style="color: var(--night-green);"><?= htmlspecialchars($row['tema']) ?></td>
                                        <td><?= htmlspecialchars($row['kelompok']) ?></td>
                                        <td><?= htmlspecialchars($row['minggu_hari']) ?></td>
                                        <td class="text-center">
                                            <a href="rppm.php?cetak_rpph=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak Dokumen Resmi">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <form method="POST" action="rppm.php" style="display:inline-block;" onsubmit="return confirm('Hapus dokumen ini?');">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" name="hapus_rpph" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<!-- ==============================================================
     MODAL FORMS (TAMBAH RPPM & RPPH)
     ============================================================== -->
     
<!-- Modal Tambah RPPM -->
<div class="modal fade" id="modalRPPM" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="rppm.php" class="modal-content shadow" style="background-color: var(--feather-beige);">
            <div class="modal-header border-bottom-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                
                <h6 class="fw-bold mb-3" style="color: var(--night-green);">1. Identitas RPPM</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Penulis</label>
                        <input type="text" name="penulis" class="form-control border-0 shadow-sm" placeholder="Nama Guru" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Asal Sekolah</label>
                        <input type="text" name="asal_sekolah" class="form-control border-0 shadow-sm" value="RA Darul Falah" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Fase / Jenjang</label>
                        <input type="text" name="fase" class="form-control border-0 shadow-sm" value="Pondasi / PAUD" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Model Pembelajaran</label>
                        <input type="text" name="model_pembelajaran" class="form-control border-0 shadow-sm" value="Tatap Muka" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Semester / Bulan</label>
                        <input type="text" name="semester_bulan" class="form-control border-0 shadow-sm" placeholder="1 / Juli" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Minggu Ke-</label>
                        <input type="text" name="minggu_ke" class="form-control border-0 shadow-sm" placeholder="Ke-1 (Waktu: 900 Menit)" required>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Topik / Sub Topik</label>
                        <input type="text" name="topik" class="form-control border-0 shadow-sm" required>
                    </div>
                </div>

                <h6 class="fw-bold mb-3" style="color: var(--night-green);">2. Tujuan Pembelajaran</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Capaian Pembelajaran (CP)</label>
                        <textarea name="cp" class="form-control border-0 shadow-sm" rows="3" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Tujuan Pembelajaran (TP)</label>
                        <textarea name="tp" class="form-control border-0 shadow-sm" rows="3" required></textarea>
                    </div>
                </div>

                <h6 class="fw-bold mb-3" style="color: var(--night-green);">3. Kegiatan Inti</h6>
                <div class="table-responsive bg-white rounded shadow-sm border border-light">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="20%">Hari</th>
                                <th width="50%">Kegiatan</th>
                                <th width="30%">Alat & Bahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="hari[]" class="form-control border-0" value="Senin"></td>
                                <td><textarea name="keg_inti[]" class="form-control border-0" rows="2" placeholder="Tuliskan kegiatan..."></textarea></td>
                                <td><textarea name="alat_bahan[]" class="form-control border-0" rows="2"></textarea></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="hari[]" class="form-control border-0" value="Selasa"></td>
                                <td><textarea name="keg_inti[]" class="form-control border-0" rows="2" placeholder="Tuliskan kegiatan..."></textarea></td>
                                <td><textarea name="alat_bahan[]" class="form-control border-0" rows="2"></textarea></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="hari[]" class="form-control border-0" value="Rabu"></td>
                                <td><textarea name="keg_inti[]" class="form-control border-0" rows="2" placeholder="Tuliskan kegiatan..."></textarea></td>
                                <td><textarea name="alat_bahan[]" class="form-control border-0" rows="2"></textarea></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="hari[]" class="form-control border-0" value="Kamis"></td>
                                <td><textarea name="keg_inti[]" class="form-control border-0" rows="2" placeholder="Tuliskan kegiatan..."></textarea></td>
                                <td><textarea name="alat_bahan[]" class="form-control border-0" rows="2"></textarea></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="hari[]" class="form-control border-0" value="Jumat"></td>
                                <td><textarea name="keg_inti[]" class="form-control border-0" rows="2" placeholder="Tuliskan kegiatan..."></textarea></td>
                                <td><textarea name="alat_bahan[]" class="form-control border-0" rows="2"></textarea></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" name="simpan_rppm" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: var(--night-green);"><i class="bi bi-save me-1"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah RPPH -->
<div class="modal fade" id="modalRPPH" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="rppm.php" class="modal-content shadow">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Form Input RPPH Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label fw-bold small">Semester / Bulan</label><input type="text" name="semester_bulan" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label fw-bold small">Kelompok Usia</label><input type="text" name="kelompok" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label fw-bold small">Minggu / Hari</label><input type="text" name="minggu_hari" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold small">Tema</label><input type="text" name="tema" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold small">Subtema</label><input type="text" name="subtema" class="form-control" required></div>
                    
                    <div class="col-md-12 mt-4"><label class="form-label fw-bold" style="color: var(--night-green);">A. Tujuan Kegiatan</label>
                    <textarea name="tujuan" class="form-control" rows="3" required></textarea></div>
                    
                    <div class="col-md-12"><label class="form-label fw-bold" style="color: var(--night-green);">B. Deskripsi Kegiatan (Pembuka, Inti, Penutup)</label>
                    <textarea name="kegiatan_pembuka" class="form-control" rows="6" required placeholder="1. Pembukaan: ...\n2. Inti: ...\n3. Penutup: ..."></textarea></div>
                </div>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="simpan_rpph" class="btn text-white fw-bold px-4" style="background-color: var(--night-green);">Simpan RPPH</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
