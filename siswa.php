<?php
require_once 'koneksi.php';

// 1. SETUP DATABASE
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS siswa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nisn VARCHAR(20) NOT NULL,
        nama_lengkap VARCHAR(150) NOT NULL,
        kelas VARCHAR(50) NOT NULL,
        jenis_kelamin ENUM('L', 'P') NOT NULL,
        tempat_lahir VARCHAR(100),
        tanggal_lahir DATE,
        nama_wali VARCHAR(150),
        alamat TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Auto-migrate (tambahkan kolom jika belum ada dari database sebelumnya)
    $pdo->exec("ALTER TABLE siswa ADD COLUMN jenis_kelamin ENUM('L', 'P') NULL");
    $pdo->exec("ALTER TABLE siswa ADD COLUMN tempat_lahir VARCHAR(100) NULL");
    $pdo->exec("ALTER TABLE siswa ADD COLUMN tanggal_lahir DATE NULL");
    $pdo->exec("ALTER TABLE siswa ADD COLUMN nama_wali VARCHAR(150) NULL");
    $pdo->exec("ALTER TABLE siswa ADD COLUMN alamat TEXT NULL");
} catch (PDOException $e) {}

// 2. LOGIKA CRUD (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['simpan_siswa'])) {
        $stmt = $pdo->prepare("INSERT INTO siswa (nisn, nama_lengkap, kelas, jenis_kelamin, tempat_lahir, tanggal_lahir, nama_wali, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nisn'], $_POST['nama_lengkap'], $_POST['kelas'], $_POST['jenis_kelamin'], 
            $_POST['tempat_lahir'], $_POST['tanggal_lahir'], $_POST['nama_wali'], $_POST['alamat']
        ]);
        header("Location: siswa.php?status=success_add");
        exit;
    }
    elseif (isset($_POST['edit_siswa'])) {
        $stmt = $pdo->prepare("UPDATE siswa SET nisn=?, nama_lengkap=?, kelas=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, nama_wali=?, alamat=? WHERE id=?");
        $stmt->execute([
            $_POST['nisn'], $_POST['nama_lengkap'], $_POST['kelas'], $_POST['jenis_kelamin'], 
            $_POST['tempat_lahir'], $_POST['tanggal_lahir'], $_POST['nama_wali'], $_POST['alamat'], $_POST['id']
        ]);
        header("Location: siswa.php?status=success_edit");
        exit;
    }
    elseif (isset($_POST['hapus_siswa'])) {
        $stmt = $pdo->prepare("DELETE FROM siswa WHERE id=?");
        $stmt->execute([$_POST['id']]);
        header("Location: siswa.php?status=success_delete");
        exit;
    }
}

// 3. LOGIKA FETCH DATA
$kelas_filter = $_GET['kelas'] ?? 'Semua';
if ($kelas_filter !== 'Semua') {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE kelas = ? ORDER BY nama_lengkap ASC");
    $stmt->execute([$kelas_filter]);
    $data_siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $data_siswa = $pdo->query("SELECT * FROM siswa ORDER BY kelas ASC, nama_lengkap ASC")->fetchAll(PDO::FETCH_ASSOC);
}

include 'header.php'; 
include 'sidebar.php'; 
?>

<!-- Main Content -->
<main class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1A2F22;">Data Siswa</h4>
            <p class="text-muted small mb-0">Kelola profil, kelas, dan identitas anak didik.</p>
        </div>
        <button class="btn btn-sm text-white fw-bold px-3 py-2 shadow-sm" style="background-color: #7A916E; border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
            <i class="bi bi-person-plus me-1"></i> Tambah Siswa Baru
        </button>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            
            <!-- Filter & Search Bar -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-4">
                    <form method="GET" action="siswa.php" class="d-flex gap-2">
                        <select name="kelas" class="form-select form-select-sm border-0 bg-light fw-bold text-secondary" onchange="this.form.submit()">
                            <option value="Semua" <?= $kelas_filter=='Semua' ? 'selected' : '' ?>>Semua Kelas</option>
                            <option value="A" <?= $kelas_filter=='A' ? 'selected' : '' ?>>Kelas A (4-5 Tahun)</option>
                            <option value="B" <?= $kelas_filter=='B' ? 'selected' : '' ?>>Kelas B (5-6 Tahun)</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted small text-uppercase" width="5%">No</th>
                            <th class="text-muted small text-uppercase" width="15%">NISN</th>
                            <th class="text-muted small text-uppercase" width="25%">Nama Lengkap</th>
                            <th class="text-muted small text-uppercase" width="10%">L/P</th>
                            <th class="text-muted small text-uppercase" width="15%">Kelas</th>
                            <th class="text-muted small text-uppercase" width="15%">Nama Wali</th>
                            <th class="text-muted small text-uppercase text-center" width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_siswa)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data siswa untuk kelas ini.</td></tr>
                        <?php else: $no=1; foreach($data_siswa as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-bold text-secondary"><?= htmlspecialchars($row['nisn']) ?></td>
                                <td class="fw-bold" style="color: #1A2F22;"><?= htmlspecialchars($row['nama_lengkap'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['jenis_kelamin'] ?? '-') ?></td>
                                <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1"><?= htmlspecialchars($row['kelas'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($row['nama_wali'] ?? '-') ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" title="Edit Data" onclick='editSiswa(<?= json_encode($row) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="siswa.php" class="d-inline" onsubmit="return confirm('Hapus permanen data siswa ini?');">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="hapus_siswa" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</main>

<!-- Modal Tambah Siswa -->
<div class="modal fade" id="modalTambahSiswa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="siswa.php" class="modal-content shadow" style="background-color: #F2EFE9;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #1A2F22;">Formulir Pendaftaran Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">NISN / No Induk</label>
                        <input type="text" name="nisn" class="form-control border-0 shadow-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Nama Lengkap Anak</label>
                        <input type="text" name="nama_lengkap" class="form-control border-0 shadow-sm" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select border-0 shadow-sm" required>
                            <option value="L">Laki-Laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Kelas / Kelompok</label>
                        <select name="kelas" class="form-select border-0 shadow-sm" required>
                            <option value="A">Kelas A (4-5 Tahun)</option>
                            <option value="B">Kelas B (5-6 Tahun)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control border-0 shadow-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control border-0 shadow-sm">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Nama Wali Murid (Ayah/Ibu)</label>
                        <input type="text" name="nama_wali" class="form-control border-0 shadow-sm" required>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Alamat Lengkap Tempat Tinggal</label>
                        <textarea name="alamat" class="form-control border-0 shadow-sm" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="simpan_siswa" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1A2F22;">Simpan Profil Siswa</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div class="modal fade" id="modalEditSiswa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="siswa.php" class="modal-content shadow" style="background-color: #F2EFE9;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #1A2F22;">Edit Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <input type="hidden" name="id" id="edit_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">NISN / No Induk</label>
                        <input type="text" name="nisn" id="edit_nisn" class="form-control border-0 shadow-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Nama Lengkap Anak</label>
                        <input type="text" name="nama_lengkap" id="edit_nama" class="form-control border-0 shadow-sm" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="edit_jk" class="form-select border-0 shadow-sm" required>
                            <option value="L">Laki-Laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Kelas / Kelompok</label>
                        <select name="kelas" id="edit_kelas" class="form-select border-0 shadow-sm" required>
                            <option value="A">Kelas A (4-5 Tahun)</option>
                            <option value="B">Kelas B (5-6 Tahun)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="edit_tempat" class="form-control border-0 shadow-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="edit_tgl" class="form-control border-0 shadow-sm">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Nama Wali Murid (Ayah/Ibu)</label>
                        <input type="text" name="nama_wali" id="edit_wali" class="form-control border-0 shadow-sm" required>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Alamat Lengkap</label>
                        <textarea name="alamat" id="edit_alamat" class="form-control border-0 shadow-sm" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="edit_siswa" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1A2F22;">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editSiswa(data) {
        document.getElementById('edit_id').value = data.id || '';
        document.getElementById('edit_nisn').value = data.nisn || '';
        document.getElementById('edit_nama').value = data.nama_lengkap || '';
        document.getElementById('edit_jk').value = data.jenis_kelamin || 'L';
        document.getElementById('edit_kelas').value = data.kelas || 'A';
        document.getElementById('edit_tempat').value = data.tempat_lahir || '';
        document.getElementById('edit_tgl').value = data.tanggal_lahir || '';
        document.getElementById('edit_wali').value = data.nama_wali || '';
        document.getElementById('edit_alamat').value = data.alamat || '';
        
        var modal = new bootstrap.Modal(document.getElementById('modalEditSiswa'));
        modal.show();
    }
</script>

<?php include 'footer.php'; ?>
