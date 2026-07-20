<?php 
require_once 'koneksi.php';

// ==========================================
// BUSINESS LOGIC 1: Kalender Pendidikan Otomatis
// ==========================================
$total_hari_belajar = 0;
$total_libur = 0;
$sisa_hari_belajar = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hitung_kalender'])) {
    $tgl_masuk = strtotime($_POST['tgl_masuk']);
    $tgl_akhir = strtotime($_POST['tgl_akhir']);
    
    // Perhitungan sederhana hari (Asumsi 1 semester)
    $datediff = $tgl_akhir - $tgl_masuk;
    $total_hari_belajar = round($datediff / (60 * 60 * 24));
    
    $libur_input = (int)$_POST['hari_libur'];
    $ujian_input = (int)$_POST['hari_ujian'];
    $rapat_input = (int)$_POST['hari_rapat'];
    
    $total_libur = $libur_input + $rapat_input; // Ujian tetap dihitung masuk
    $sisa_hari_belajar = $total_hari_belajar - $total_libur;
} else {
    // Default 0 (Menunggu Input User)
    $total_hari_belajar = 0;
    $total_libur = 0;
    $sisa_hari_belajar = 0;
}
// ==========================================
// BUSINESS LOGIC 2: Rekap Kehadiran Guru (Dari Database)
// ==========================================
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS guru (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_guru VARCHAR(100) NOT NULL
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS absensi_guru (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_guru INT NOT NULL,
        tanggal DATE NOT NULL,
        status ENUM('Hadir', 'Izin', 'Sakit', 'Alpha') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_guru) REFERENCES guru(id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {}

$stmt = $pdo->query("
    SELECT 
        g.nama_guru as nama,
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as H,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as I,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as S,
        SUM(CASE WHEN a.status = 'Alpha' THEN 1 ELSE 0 END) as A,
        COUNT(a.id) as total_hari
    FROM guru g
    LEFT JOIN absensi_guru a ON g.id = a.id_guru
    GROUP BY g.id
");
$guru_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// BUSINESS LOGIC 3: Rekap Data Murid (Dari Database)
// ==========================================
try {
    $stmt_murid = $pdo->query("
        SELECT 
            SUM(CASE WHEN jk = 'L' THEN 1 ELSE 0 END) as Laki_laki,
            SUM(CASE WHEN jk = 'P' THEN 1 ELSE 0 END) as Perempuan,
            SUM(CASE WHEN kelas = 'A' THEN 1 ELSE 0 END) as Kelas_A,
            SUM(CASE WHEN kelas = 'B' THEN 1 ELSE 0 END) as Kelas_B,
            COUNT(id) as total_murid
        FROM (
            -- Subquery to ensure table exists in logic flow without erroring if not created yet
            SELECT * FROM siswa
        ) AS s
    ");
    $stats = $stmt_murid->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $stats = ['Laki_laki' => 0, 'Perempuan' => 0, 'Kelas_A' => 0, 'Kelas_B' => 0, 'total_murid' => 0];
}

$murid_stats = [
    'Laki-laki' => (int)$stats['Laki_laki'],
    'Perempuan' => (int)$stats['Perempuan'],
    'Kelas A' => (int)$stats['Kelas_A'],
    'Kelas B' => (int)$stats['Kelas_B']
];
$total_murid = (int)$stats['total_murid'];

include 'header.php'; 
include 'sidebar.php'; 
?>
<!-- Main Content -->
<main class="main-content">
    
    <!-- Hero Header -->
    <div class="hero-header d-flex flex-column justify-content-center mb-4">
        <p class="mb-1 text-white-50 fw-semibold">Tahun Ajar Aktif</p>
        <h2 class="mb-0 fw-bold">2026/2027 - Semester 1</h2>
    </div>
    
    <!-- ==========================================
         DASHBOARD SUMMARY CARDS (Logic 1 & 3)
         ========================================== -->
    <div class="row g-4 mb-4">
        <!-- Card Rekap Kalender -->
        <div class="col-md-6 col-lg-4">
            <div class="custom-card p-4 h-100 border-0 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-muted mb-0">Kalender Akademik</h6>
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-2"><i class="bi bi-calendar-check"></i></div>
                </div>
                <h3 class="fw-bold text-navy mb-1"><?= $sisa_hari_belajar ?> Hari</h3>
                <p class="text-muted small mb-0">Sisa hari efektif belajar</p>
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Total: <?= $total_hari_belajar ?></span>
                        <span>Libur: <?= $total_libur ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Rekap Murid Gender -->
        <div class="col-md-6 col-lg-4">
            <div class="custom-card p-4 h-100 border-0 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-muted mb-0">Total Murid</h6>
                    <div class="bg-success bg-opacity-10 text-success rounded p-2"><i class="bi bi-people"></i></div>
                </div>
                <h3 class="fw-bold text-navy mb-1"><?= $total_murid ?> Anak</h3>
                <p class="text-muted small mb-3">Rasio Laki-laki & Perempuan</p>
                
                <?php 
                $pct_L = ($total_murid > 0) ? round(($murid_stats['Laki-laki'] / $total_murid) * 100) : 0;
                $pct_P = 100 - $pct_L;
                ?>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-info" style="width: <?= $pct_L ?>%"></div>
                    <div class="progress-bar bg-warning" style="width: <?= $pct_P ?>%"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted mt-2 fw-semibold">
                    <span class="text-info"><i class="bi bi-gender-male"></i> L: <?= $murid_stats['Laki-laki'] ?></span>
                    <span class="text-warning"><i class="bi bi-gender-female"></i> P: <?= $murid_stats['Perempuan'] ?></span>
                </div>
            </div>
        </div>

        <!-- Card Rekap Murid Kelas -->
        <div class="col-md-6 col-lg-4">
            <div class="custom-card p-4 h-100 border-0 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-muted mb-0">Distribusi Kelas</h6>
                    <div class="bg-warning bg-opacity-10 text-warning rounded p-2"><i class="bi bi-diagram-3"></i></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                    <div class="text-center w-50 border-end">
                        <h4 class="fw-bold text-dark mb-0"><?= $murid_stats['Kelas A'] ?></h4>
                        <span class="text-muted small">Kelas A</span>
                    </div>
                    <div class="text-center w-50">
                        <h4 class="fw-bold text-dark mb-0"><?= $murid_stats['Kelas B'] ?></h4>
                        <span class="text-muted small">Kelas B</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         SIMULASI INPUT KALENDER & REKAP GURU
         ========================================== -->
    <div class="row g-4 mb-4">
        
        <!-- Form Hitung Kalender (Logic 1) -->
        <div class="col-lg-5">
            <div class="custom-card p-4 border-0 shadow-sm bg-white h-100">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-calculator me-2"></i>Kalkulator Hari Efektif</h6>
                <form method="POST" action="">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">Mulai Semester</label>
                            <input type="date" name="tgl_masuk" class="form-control form-control-sm" value="2026-07-13" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Akhir Semester</label>
                            <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="2026-12-18" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small text-muted">Libur (Hari)</label>
                            <input type="number" name="hari_libur" class="form-control form-control-sm" value="10" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted">Ujian (Hari)</label>
                            <input type="number" name="hari_ujian" class="form-control form-control-sm" value="5" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted">Rapat (Hari)</label>
                            <input type="number" name="hari_rapat" class="form-control form-control-sm" value="3" required>
                        </div>
                    </div>
                    <button type="submit" name="hitung_kalender" class="btn btn-primary bg-navy border-0 w-100 btn-sm fw-bold">Hitung Sisa Hari</button>
                </form>
            </div>
        </div>

        <!-- Rekap Kehadiran Guru (Logic 2) -->
        <div class="col-lg-7">
            <div class="custom-card p-4 border-0 shadow-sm bg-white h-100">
                <h6 class="fw-bold text-dark mb-4"><i class="bi bi-person-badge me-2"></i>Persentase Kehadiran Guru (Database)</h6>
                
                <div class="d-flex flex-column gap-3">
                    <?php if(empty($guru_attendance)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-person-badge fs-2 text-muted mb-2 d-block"></i>
                            <span class="text-muted small">Belum ada data guru atau absensi.</span>
                        </div>
                    <?php else: ?>
                        <?php foreach($guru_attendance as $g): 
                            $total = (int)$g['total_hari'];
                            $pct = ($total > 0) ? round(($g['H'] / $total) * 100) : 0;
                            $bg_class = $pct < 80 ? 'bg-danger' : 'bg-success';
                            $text_class = $pct < 80 ? 'text-danger' : 'text-success';
                        ?>
                        <div>
                            <div class="d-flex justify-content-between align-items-end mb-1">
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($g['nama']) ?> <span class="text-muted fw-normal" style="font-size:0.75rem;">(Total <?= $total ?> Hari)</span></span>
                                <span class="small fw-bold <?= $text_class ?>"><?= $pct ?>%</span>
                            </div>
                            <div class="progress mb-1" style="height: 8px;">
                                <div class="progress-bar <?= $bg_class ?>" style="width: <?= $pct ?>%"></div>
                            </div>
                            <div class="d-flex gap-3 small text-muted" style="font-size: 0.75rem;">
                                <span>Hadir: <?= (int)$g['H'] ?></span>
                                <span>Izin: <?= (int)$g['I'] ?></span>
                                <span>Sakit: <?= (int)$g['S'] ?></span>
                                <span>Alpha: <?= (int)$g['A'] ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Profil Lembaga (Static) -->
    <div class="custom-card p-4 mb-4 border-0 shadow-sm bg-white rounded-lg">
        <div class="d-flex flex-column flex-md-row justify-content-between border-bottom pb-3 mb-3">
            <div>
                <h4 class="fw-bold text-navy mb-1">RA DARUL FALAH</h4>
                <div class="text-muted" style="font-size: 0.9rem;">
                    <span class="me-3"><i class="bi bi-hash"></i> NSM: 101231740092</span>
                    <span><i class="bi bi-hash"></i> NPSN: 69732449</span>
                </div>
            </div>
            <div class="text-md-end mt-2 mt-md-0 text-muted" style="font-size: 0.85rem;">
                <div><span class="fw-semibold text-dark">Status:</span> swasta &nbsp;|&nbsp; <span class="fw-semibold text-dark">Bentuk SP:</span> RA</div>
                <div><i class="bi bi-telephone-fill me-1"></i> 0217804792 &nbsp;|&nbsp; <i class="bi bi-envelope-fill me-1"></i> RADARULFALAH30@GMAIL.COM</div>
            </div>
        </div>
        <h6 class="fw-bold text-dark mb-3">Informasi Umum</h6>
        <div class="row g-3" style="font-size: 0.85rem;">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td width="40%" class="text-muted pb-2">Kepala Madrasah</td><td class="fw-semibold text-dark pb-2">: YANAH</td></tr>
                    <tr><td class="text-muted pb-2">Nama Penyelenggara</td><td class="fw-semibold text-dark pb-2">: SAIF ARRAHMAN</td></tr>
                    <tr><td class="text-muted pb-2">Afiliasi Keagamaan</td><td class="fw-semibold text-dark pb-2">: Nahdlatul Ulama</td></tr>
                    <tr><td class="text-muted pb-2">Waktu Belajar</td><td class="fw-semibold text-dark pb-2">: Pagi</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td width="35%" class="text-muted pb-2">Alamat</td><td class="fw-semibold text-dark pb-2">: JL. BENDA GG. H. MUSA RT.008/04 NO.30</td></tr>
                    <tr><td class="text-muted pb-2">Kelurahan/Desa</td><td class="fw-semibold text-dark pb-2">: CILANDAK TIMUR</td></tr>
                    <tr><td class="text-muted pb-2">Kecamatan</td><td class="fw-semibold text-dark pb-2">: PASAR MINGGU</td></tr>
                    <tr><td class="text-muted pb-2">Provinsi</td><td class="fw-semibold text-dark pb-2">: DKI JAKARTA</td></tr>
                </table>
            </div>
        </div>
    </div>

</main>
<?php include 'footer.php'; ?>
