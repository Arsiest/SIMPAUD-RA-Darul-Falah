<?php
require_once 'koneksi.php';

// ==========================================
// 1. SETUP DATABASE
// ==========================================
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS penilaian (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rpph_id INT NOT NULL,
        siswa_id INT NOT NULL,
        indikator VARCHAR(255) NOT NULL,
        nilai ENUM('BB', 'MB', 'BSH', 'BSB') NULL,
        tanggal DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_penilaian (rpph_id, siswa_id, indikator, tanggal)
    )");
} catch (PDOException $e) {}

// ==========================================
// 2. LOGIKA INSERT/UPDATE (CRUD)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_penilaian'])) {
    $rpph_id = $_POST['rpph_id'];
    $tanggal = $_POST['tanggal'];
    $kelas = $_POST['kelas'];
    $data_nilai = $_POST['nilai'] ?? []; 
    
    $stmt_insert = $pdo->prepare("INSERT INTO penilaian (rpph_id, siswa_id, indikator, nilai, tanggal) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
    $stmt_delete = $pdo->prepare("DELETE FROM penilaian WHERE rpph_id = ? AND siswa_id = ? AND indikator = ? AND tanggal = ?");
    
    foreach ($data_nilai as $sid => $indikator_arr) {
        foreach ($indikator_arr as $b64_ind => $val) {
            $ind = base64_decode($b64_ind);
            if (!empty($val)) {
                $stmt_insert->execute([$rpph_id, $sid, $ind, $val, $tanggal]);
            } else {
                $stmt_delete->execute([$rpph_id, $sid, $ind, $tanggal]);
            }
        }
    }
    header("Location: penilaian.php?rpph_id=$rpph_id&kelas=$kelas&tanggal=$tanggal&status=success_save");
    exit;
}

// ==========================================
// 3. LOGIKA SELECT & FILTERING
// ==========================================
$rpph_id = $_GET['rpph_id'] ?? '';
$kelas = $_GET['kelas'] ?? '';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// Ambil list RPPH untuk dropdown
$rpph_list = $pdo->query("SELECT id, tema, subtema, tanggal_dibuat FROM rpph ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$siswa_list = [];
$indikator_list = [];
$nilai_map = [];

// Jika form filter sudah di-submit
if ($rpph_id && $kelas && $tanggal) {
    // A. Ambil murid sesuai kelas
    $stmt = $pdo->prepare("SELECT id, nama_lengkap FROM siswa WHERE kelas = ? ORDER BY nama_lengkap ASC");
    $stmt->execute([$kelas]);
    $siswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // B. Ambil RPPH & Ekstrak Indikator
    $stmt = $pdo->prepare("SELECT * FROM rpph WHERE id = ?");
    $stmt->execute([$rpph_id]);
    $rpph_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rpph_data) {
        $raw_indikator = explode("\n", $rpph_data['tujuan']);
        foreach($raw_indikator as $ind) {
            // Hapus angka di depan jika ada (misal: "1. Berdoa" -> "Berdoa")
            $ind = trim(preg_replace('/^[0-9]+\.\s*/', '', $ind));
            if(!empty($ind)) {
                $indikator_list[] = $ind;
            }
        }
    }
    
    // C. Ambil data nilai yang sudah pernah diinput sebelumnya (untuk ngisi default select)
    $stmt = $pdo->prepare("SELECT siswa_id, indikator, nilai FROM penilaian WHERE rpph_id = ? AND tanggal = ?");
    $stmt->execute([$rpph_id, $tanggal]);
    $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($existing as $ex) {
        $nilai_map[$ex['siswa_id']][$ex['indikator']] = $ex['nilai'];
    }
}

include 'header.php';
include 'sidebar.php';
?>

<style>
    :root {
        --night-green: #1b3323;
        --herb-leaf: #7A916E;
    }
    
    .table-penilaian th {
        background-color: var(--night-green) !important;
        color: white !important;
        vertical-align: middle;
        text-align: center;
        border-color: #3d5e48;
    }
    .table-penilaian td {
        vertical-align: middle;
        border-color: #dee2e6;
    }
    .indikator-text {
        min-width: 250px;
        font-weight: 500;
    }
    .select-nilai {
        min-width: 80px;
        text-align: center;
    }
    
    /* PRINT STYLES */
    @media print {
        @page { size: landscape; margin: 1cm; }
        body { background: white !important; color: black !important; font-size: 10pt; }
        .d-print-none, .sidebar, .topbar { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        .card { border: none !important; box-shadow: none !important; }
        
        .table-penilaian th { background-color: #ddd !important; color: black !important; border-color: black !important; }
        .table-penilaian td { border-color: black !important; }
        
        .cetak-header { display: block !important; text-align: center; margin-bottom: 20px; }
        .cetak-header h3 { margin: 0; font-weight: bold; font-size: 14pt; text-decoration: underline; }
        .cetak-header p { margin: 5px 0 0 0; font-size: 11pt; }
        
        /* Hilangkan panah select dan border saat dicetak */
        select.select-nilai {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            border: none;
            background: transparent;
            font-weight: bold;
            color: black;
            text-align: center;
            width: 100%;
        }
    }
</style>

<!-- HEADER CETAK (Hanya Muncul Saat Diprint) -->
<div class="d-none cetak-header d-print-block text-start mb-4">
    <h3 class="text-center mb-3">FORMAT SKALA CAPAIAN PERKEMBANGAN HARIAN</h3>
    
    <div class="row w-100 fw-bold" style="font-size: 11pt;">
        <div class="col-6">
            <table class="table table-borderless table-sm mb-0">
                <tr><td width="35%" class="py-0">Kelompok Usia</td><td width="2%" class="py-0">:</td><td class="py-0"><?= $kelas ? "Kelas ".$kelas : "......" ?></td></tr>
                <tr><td class="py-0">Tanggal Penilaian</td><td class="py-0">:</td><td class="py-0"><?= $tanggal ? date('d-m-Y', strtotime($tanggal)) : "......" ?></td></tr>
                <tr><td class="py-0">Semester / Bulan</td><td class="py-0">:</td><td class="py-0"><?= isset($rpph_data) ? htmlspecialchars($rpph_data['semester_bulan']) : "......" ?></td></tr>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-borderless table-sm mb-0">
                <tr><td width="30%" class="py-0">Tema</td><td width="2%" class="py-0">:</td><td class="py-0"><?= isset($rpph_data) ? htmlspecialchars($rpph_data['tema']) : "......" ?></td></tr>
                <tr><td class="py-0">Subtema</td><td class="py-0">:</td><td class="py-0"><?= isset($rpph_data) ? htmlspecialchars($rpph_data['subtema']) : "......" ?></td></tr>
                <tr><td class="py-0">Minggu / Hari</td><td class="py-0">:</td><td class="py-0"><?= isset($rpph_data) ? htmlspecialchars($rpph_data['minggu_hari']) : "......" ?></td></tr>
            </table>
        </div>
    </div>
</div>

<!-- Main Content -->
<main class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--night-green);">Penilaian Harian</h4>
            <p class="text-muted small mb-0">Ceklis skala capaian perkembangan anak didik berdasarkan RPPH.</p>
        </div>
        <?php if(!empty($siswa_list) && !empty($indikator_list)): ?>
        <button onclick="window.print()" class="btn text-white fw-bold shadow-sm" style="background-color: var(--night-green);">
            <i class="bi bi-printer me-2"></i>Cetak Ceklis Kelas
        </button>
        <?php endif; ?>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none">
        <div class="card-body p-4 bg-light rounded-4">
            <form method="GET" action="penilaian.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold small text-muted">Pilih Dokumen RPPH</label>
                    <select name="rpph_id" class="form-select border-0 shadow-sm" required>
                        <option value="">-- Pilih Dokumen --</option>
                        <?php foreach($rpph_list as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $rpph_id == $r['id'] ? 'selected' : '' ?>>
                                <?= date('d M Y', strtotime($r['tanggal_dibuat'])) ?> - <?= htmlspecialchars($r['tema']) ?> / <?= htmlspecialchars($r['subtema']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Kelompok / Kelas</label>
                    <select name="kelas" class="form-select border-0 shadow-sm" required>
                        <option value="">-- Pilih Kelas --</option>
                        <option value="A" <?= $kelas == 'A' ? 'selected' : '' ?>>Kelas A (4-5 Tahun)</option>
                        <option value="B" <?= $kelas == 'B' ? 'selected' : '' ?>>Kelas B (5-6 Tahun)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Tanggal Penilaian</label>
                    <input type="date" name="tanggal" class="form-control border-0 shadow-sm" value="<?= htmlspecialchars($tanggal) ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn text-white w-100 fw-bold shadow-sm" style="background-color: var(--herb-leaf);">
                        Tampilkan Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Matriks Penilaian -->
    <?php if ($rpph_id && $kelas && $tanggal): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <?php if (empty($siswa_list)): ?>
                    <div class="alert alert-warning text-center">Belum ada data siswa di kelas ini. <a href="siswa.php" class="fw-bold">Tambah Data Siswa</a></div>
                <?php elseif (empty($indikator_list)): ?>
                    <div class="alert alert-danger text-center">Dokumen RPPH ini belum memiliki "Tujuan Kegiatan". Silakan lengkapi RPPH terlebih dahulu.</div>
                <?php else: ?>
                    
                    <form method="POST" action="penilaian.php">
                        <input type="hidden" name="rpph_id" value="<?= $rpph_id ?>">
                        <input type="hidden" name="kelas" value="<?= $kelas ?>">
                        <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                        
                        <div class="table-responsive mb-4 border rounded">
                            <table class="table table-bordered table-hover mb-0 table-penilaian">
                                <thead>
                                    <tr>
                                        <th width="5%" rowspan="2">No</th>
                                        <th rowspan="2">Indikator Penilaian</th>
                                        <th colspan="<?= count($siswa_list) ?>">Nama Siswa</th>
                                    </tr>
                                    <tr>
                                        <?php foreach($siswa_list as $s): ?>
                                            <th>
                                                <!-- Nama depan saja agar tidak terlalu lebar -->
                                                <?= htmlspecialchars(explode(' ', trim($s['nama_lengkap']))[0]) ?>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no=1; foreach($indikator_list as $ind): ?>
                                        <tr>
                                            <td class="text-center fw-bold"><?= $no++ ?></td>
                                            <td class="indikator-text"><?= htmlspecialchars($ind) ?></td>
                                            
                                            <?php foreach($siswa_list as $s): 
                                                // Cek apakah nilai sudah pernah diisi
                                                $val = $nilai_map[$s['id']][$ind] ?? '';
                                                $b64_ind = base64_encode($ind);
                                            ?>
                                            <td class="p-1">
                                                <select name="nilai[<?= $s['id'] ?>][<?= $b64_ind ?>]" class="form-select form-select-sm select-nilai border-0 bg-transparent text-center fw-bold">
                                                    <option value=""></option>
                                                    <option value="BB" <?= $val=='BB'?'selected':'' ?>>BB</option>
                                                    <option value="MB" <?= $val=='MB'?'selected':'' ?>>MB</option>
                                                    <option value="BSH" <?= $val=='BSH'?'selected':'' ?>>BSH</option>
                                                    <option value="BSB" <?= $val=='BSB'?'selected':'' ?>>BSB</option>
                                                </select>
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-end d-print-none">
                            <button type="submit" name="simpan_penilaian" class="btn btn-lg text-white px-5 shadow fw-bold" style="background-color: var(--night-green); border-radius: 50px;">
                                <i class="bi bi-check2-circle me-2"></i> Simpan Penilaian
                            </button>
                        </div>
                    </form>
                    
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
</main>

<?php include 'footer.php'; ?>
