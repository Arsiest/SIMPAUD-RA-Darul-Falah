<?php
require_once 'koneksi.php';

// --- SETUP DATABASE (Auto Migration) ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS guru (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_guru VARCHAR(150) NOT NULL
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS absensi_guru (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_guru INT NOT NULL,
        tanggal DATE NOT NULL,
        status ENUM('Hadir', 'Izin', 'Sakit', 'Alpha') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_guru) REFERENCES guru(id) ON DELETE CASCADE
    )");
    
    // Auto-tambah kolom jika belum ada (Migration aman)
    $pdo->exec("ALTER TABLE guru ADD COLUMN nuptk VARCHAR(50)");
    $pdo->exec("ALTER TABLE guru ADD COLUMN status_pegawai ENUM('PNS', 'Non PNS')");
    $pdo->exec("ALTER TABLE guru ADD COLUMN jk ENUM('L', 'P')");
    $pdo->exec("ALTER TABLE guru ADD COLUMN tempat_lahir VARCHAR(100)");
    $pdo->exec("ALTER TABLE guru ADD COLUMN tgl_lahir DATE");
    $pdo->exec("ALTER TABLE guru ADD COLUMN jtm INT");
} catch (PDOException $e) {
    // Abaikan jika kolom sudah ada
}

// --- PROSES TAMBAH GURU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_guru'])) {
    $nama = $_POST['nama_guru'];
    $nuptk = $_POST['nuptk'];
    $status_pegawai = $_POST['status_pegawai'];
    $jk = $_POST['jk'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $jtm = $_POST['jtm'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO guru (nama_guru, nuptk, status_pegawai, jk, tempat_lahir, tgl_lahir, jtm) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $nuptk, $status_pegawai, $jk, $tempat_lahir, $tgl_lahir, $jtm]);
        header("Location: absensi_guru.php?status=success_add");
        exit;
    } catch(Exception $e) {
        $error = urlencode($e->getMessage());
        header("Location: absensi_guru.php?status=error_add&msg=$error");
        exit;
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_guru'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_guru'];
    $nuptk = $_POST['nuptk'];
    $status_pegawai = $_POST['status_pegawai'];
    $jk = $_POST['jk'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $jtm = $_POST['jtm'];
    
    try {
        $stmt = $pdo->prepare("UPDATE guru SET nama_guru=?, nuptk=?, status_pegawai=?, jk=?, tempat_lahir=?, tgl_lahir=?, jtm=? WHERE id=?");
        $stmt->execute([$nama, $nuptk, $status_pegawai, $jk, $tempat_lahir, $tgl_lahir, $jtm, $id]);
        header("Location: absensi_guru.php?status=success_edit");
        exit;
    } catch(Exception $e) {
        $error = urlencode($e->getMessage());
        header("Location: absensi_guru.php?status=error_edit&msg=$error");
        exit;
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_guru'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM guru WHERE id=?");
        $stmt->execute([$_POST['id']]);
        header("Location: absensi_guru.php?status=success_delete");
        exit;
    } catch(Exception $e) {
        $error = urlencode($e->getMessage());
        header("Location: absensi_guru.php?status=error_delete&msg=$error");
        exit;
    }
}

// --- PROSES SIMPAN ABSENSI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_absensi'])) {
    $id_guru = $_POST['id_guru'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO absensi_guru (id_guru, tanggal, status) VALUES (?, ?, ?)");
        $stmt->execute([$id_guru, $tanggal, $status]);
        header("Location: absensi_guru.php?status=success_absen");
        exit;
    } catch (Exception $e) {
        $error = urlencode($e->getMessage());
        header("Location: absensi_guru.php?status=error_absen&msg=$error");
        exit;
    }
}

// Fetch Guru List
$data_guru = $pdo->query("SELECT * FROM guru ORDER BY nama_guru ASC")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
include 'sidebar.php'; 
?>
<main class="main-content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-navy mb-1">Data & Absensi Guru</h4>
            <p class="text-muted mb-0">Manajemen biodata guru dan pencatatan kehadiran harian.</p>
        </div>
        <div>
            <button class="btn btn-primary bg-navy border-0 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahGuru">
                <i class="bi bi-person-plus me-1"></i> Tambah Data Guru
            </button>
        </div>
    </div>
    
    <?php if(empty($data_guru)): ?>
        <!-- EMPTY STATE -->
        <div class="custom-card p-4 text-center py-5 shadow-sm border-0 bg-white">
            <i class="bi bi-person-badge fs-1 text-muted mb-3 d-block"></i>
            <h5 class="fw-bold text-muted">Belum ada guru terdaftar</h5>
            <p class="text-muted mb-4">Silakan klik "Tambah Data Guru" untuk memasukkan data pengajar terlebih dahulu.</p>
            <button class="btn btn-outline-primary fw-bold px-4" data-bs-toggle="modal" data-bs-target="#modalTambahGuru">
                <i class="bi bi-plus-lg me-1"></i> Input Data Pertama
            </button>
        </div>
    <?php else: ?>
        <!-- TABEL DATA GURU -->
        <div class="custom-card border-0 shadow-sm bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Nama Lengkap</th>
                            <th class="py-3">NUPTK / PEG ID</th>
                            <th class="text-center py-3">Status</th>
                            <th class="text-center py-3">Beban JTM</th>
                            <th class="text-center pe-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php foreach($data_guru as $guru): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px; font-weight: bold;">
                                            <?= strtoupper(substr($guru['nama_guru'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($guru['nama_guru']) ?></h6>
                                            <span class="text-muted small"><?= $guru['jk'] == 'L' ? 'Laki-laki' : 'Perempuan' ?> &bull; <?= htmlspecialchars($guru['tempat_lahir'] ?: '') ?><?= $guru['tgl_lahir'] ? ', ' . date('d M Y', strtotime($guru['tgl_lahir'])) : '' ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark fw-semibold"><?= htmlspecialchars($guru['nuptk'] ?: '-') ?></div>
                                </td>
                                <td class="text-center">
                                    <?php if($guru['status_pegawai'] == 'PNS'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1">PNS</span>
                                    <?php elseif($guru['status_pegawai'] == 'Non PNS'): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-1">Non PNS</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border px-3 py-1">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="fw-semibold text-dark"><?= (int)$guru['jtm'] ?> Jam</div>
                                </td>
                                <td class="text-center pe-4">
                                    <!-- Tombol Absen Memicu Modal Absen Kecil -->
                                    <button class="btn btn-sm btn-outline-success me-1 fw-bold" onclick="bukaModalAbsen(<?= $guru['id'] ?>, '<?= addslashes($guru['nama_guru']) ?>')" title="Isi Absensi"><i class="bi bi-check-circle me-1"></i> Absen</button>
                                    <button class="btn btn-sm btn-light border text-info me-1" title="Edit Data" onclick='editGuru(<?= json_encode($guru) ?>)'><i class="bi bi-pencil-square"></i></button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Hapus data guru ini?');">
                                        <input type="hidden" name="id" value="<?= $guru['id'] ?>">
                                        <button type="submit" name="hapus_guru" class="btn btn-sm btn-light border text-danger" title="Hapus Data"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- ==========================================
     MODAL TAMBAH DATA GURU
     ========================================== -->
<div class="modal fade" id="modalTambahGuru" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom-0">
        <h5 class="modal-title fw-bold text-navy"><i class="bi bi-person-plus-fill me-2"></i>Tambah Data Guru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
          <div class="modal-body p-4">
              <div class="row g-3">
                  <div class="col-md-12">
                      <label class="form-label text-muted small fw-bold">Nama Lengkap Guru</label>
                      <input type="text" name="nama_guru" class="form-control" required placeholder="Masukkan nama lengkap">
                  </div>
                  
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">NUPTK / PEG ID</label>
                      <input type="text" name="nuptk" class="form-control" placeholder="Nomor unik pendidik">
                  </div>
                  
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">Beban JTM</label>
                      <div class="input-group">
                          <input type="number" name="jtm" class="form-control" required placeholder="0">
                          <span class="input-group-text bg-light text-muted small">Jam</span>
                      </div>
                  </div>

                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">Tempat Lahir</label>
                      <input type="text" name="tempat_lahir" class="form-control" required placeholder="Kota kelahiran">
                  </div>
                  
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">Tanggal Lahir</label>
                      <input type="date" name="tgl_lahir" class="form-control" required>
                  </div>

                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold d-block">Status Kepegawaian</label>
                      <div class="form-check form-check-inline mt-1">
                          <input class="form-check-input" type="radio" name="status_pegawai" id="stsPNS" value="PNS" required>
                          <label class="form-check-label" for="stsPNS">PNS</label>
                      </div>
                      <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="status_pegawai" id="stsNonPNS" value="Non PNS" required>
                          <label class="form-check-label" for="stsNonPNS">Non PNS</label>
                      </div>
                  </div>

                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold d-block">Jenis Kelamin</label>
                      <div class="form-check form-check-inline mt-1">
                          <input class="form-check-input" type="radio" name="jk" id="jkGuruL" value="L" required>
                          <label class="form-check-label" for="jkGuruL">Laki-laki</label>
                      </div>
                      <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="jk" id="jkGuruP" value="P" required>
                          <label class="form-check-label" for="jkGuruP">Perempuan</label>
                      </div>
                  </div>
              </div>
          </div>
          <div class="modal-footer border-top-0 bg-light">
            <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="tambah_guru" class="btn btn-primary bg-navy border-0 fw-bold px-4">Simpan Guru</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- ==========================================
     MODAL INPUT ABSENSI (KECIL)
     ========================================== -->
<div class="modal fade" id="modalAbsensi" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom-0 py-2">
        <h6 class="modal-title fw-bold text-navy">Isi Absensi</h6>
        <button type="button" class="btn-close" style="font-size:0.7rem;" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
          <div class="modal-body p-3">
              <input type="hidden" name="id_guru" id="absensi_id_guru">
              
              <div class="text-center mb-3">
                  <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex justify-content-center align-items-center mb-2" style="width: 50px; height: 50px;">
                      <i class="bi bi-person-check fs-3"></i>
                  </div>
                  <h6 class="fw-bold mb-0 text-dark" id="absensi_nama_guru">Nama Guru</h6>
              </div>

              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold mb-1">Tanggal</label>
                  <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
              </div>
              
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold mb-1 d-block">Status</label>
                  <div class="row g-2">
                      <div class="col-6">
                          <input type="radio" class="btn-check" name="status" id="abs_hadir" value="Hadir" checked required>
                          <label class="btn btn-outline-success btn-sm w-100 fw-bold" for="abs_hadir">Hadir</label>
                      </div>
                      <div class="col-6">
                          <input type="radio" class="btn-check" name="status" id="abs_izin" value="Izin">
                          <label class="btn btn-outline-warning btn-sm w-100 fw-bold" for="abs_izin">Izin</label>
                      </div>
                      <div class="col-6">
                          <input type="radio" class="btn-check" name="status" id="abs_sakit" value="Sakit">
                          <label class="btn btn-outline-info btn-sm w-100 fw-bold" for="abs_sakit">Sakit</label>
                      </div>
                      <div class="col-6">
                          <input type="radio" class="btn-check" name="status" id="abs_alpha" value="Alpha">
                          <label class="btn btn-outline-danger btn-sm w-100 fw-bold" for="abs_alpha">Alpha</label>
                      </div>
                  </div>
              </div>
          </div>
          <div class="modal-footer border-top-0 bg-light p-2">
            <button type="submit" name="simpan_absensi" class="btn btn-primary bg-navy border-0 btn-sm w-100 fw-bold">Simpan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- ==========================================
     MODAL EDIT DATA GURU
     ========================================== -->
<div class="modal fade" id="modalEditGuru" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom-0">
        <h5 class="modal-title fw-bold text-navy"><i class="bi bi-pencil-square me-2"></i>Edit Data Guru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
          <div class="modal-body p-4">
              <input type="hidden" name="id" id="edit_guru_id">
              <div class="row g-3">
                  <div class="col-md-12">
                      <label class="form-label text-muted small fw-bold">Nama Lengkap Guru</label>
                      <input type="text" name="nama_guru" id="edit_guru_nama" class="form-control" required>
                  </div>
                  
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">NUPTK / PEG ID</label>
                      <input type="text" name="nuptk" id="edit_guru_nuptk" class="form-control">
                  </div>
                  
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">Beban JTM</label>
                      <div class="input-group">
                          <input type="number" name="jtm" id="edit_guru_jtm" class="form-control" required>
                          <span class="input-group-text bg-light text-muted small">Jam</span>
                      </div>
                  </div>

                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">Tempat Lahir</label>
                      <input type="text" name="tempat_lahir" id="edit_guru_tempat" class="form-control" required>
                  </div>
                  
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">Tanggal Lahir</label>
                      <input type="date" name="tgl_lahir" id="edit_guru_tgl" class="form-control" required>
                  </div>

                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold d-block">Status Kepegawaian</label>
                      <select name="status_pegawai" id="edit_guru_status" class="form-select" required>
                          <option value="PNS">PNS</option>
                          <option value="Non PNS">Non PNS</option>
                      </select>
                  </div>

                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold d-block">Jenis Kelamin</label>
                      <select name="jk" id="edit_guru_jk" class="form-select" required>
                          <option value="L">Laki-laki</option>
                          <option value="P">Perempuan</option>
                      </select>
                  </div>
              </div>
          </div>
          <div class="modal-footer border-top-0 bg-light">
            <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="edit_guru" class="btn btn-primary bg-navy border-0 fw-bold px-4">Update Guru</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function bukaModalAbsen(id, nama) {
    document.getElementById('absensi_id_guru').value = id;
    document.getElementById('absensi_nama_guru').innerText = nama;
    var myModal = new bootstrap.Modal(document.getElementById('modalAbsensi'));
    myModal.show();
}

function editGuru(data) {
    document.getElementById('edit_guru_id').value = data.id;
    document.getElementById('edit_guru_nama').value = data.nama_guru;
    document.getElementById('edit_guru_nuptk').value = data.nuptk;
    document.getElementById('edit_guru_jtm').value = data.jtm;
    document.getElementById('edit_guru_tempat').value = data.tempat_lahir;
    document.getElementById('edit_guru_tgl').value = data.tgl_lahir;
    document.getElementById('edit_guru_status').value = data.status_pegawai;
    document.getElementById('edit_guru_jk').value = data.jk;
    
    var modal = new bootstrap.Modal(document.getElementById('modalEditGuru'));
    modal.show();
}
</script>

<?php include 'footer.php'; ?>
