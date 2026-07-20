<?php 
require_once 'koneksi.php';

// --- SETUP DATABASE OTOMATIS JIKA BELUM ADA ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS materi_poin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kategori VARCHAR(100) NOT NULL,
        deskripsi TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS materi_sub_poin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        poin_id INT NOT NULL,
        kode VARCHAR(20) NOT NULL,
        deskripsi TEXT NOT NULL,
        FOREIGN KEY (poin_id) REFERENCES materi_poin(id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {}

// --- LOGIKA BACKEND UNTUK MENYIMPAN DATA (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_materi'])) {
    $kategori = trim($_POST['kategori']);
    $poin_utama = trim($_POST['poin_utama']);
    
    if (!empty($kategori) && !empty($poin_utama)) {
        try {
            $pdo->beginTransaction();
            
            // Insert Poin Utama
            $stmt = $pdo->prepare("INSERT INTO materi_poin (kategori, deskripsi) VALUES (?, ?)");
            $stmt->execute([$kategori, $poin_utama]);
            $poin_id = $pdo->lastInsertId();
            
            // Loop & Insert Sub-poin Dinamis
            if (isset($_POST['sub_kode']) && is_array($_POST['sub_kode'])) {
                $stmtSub = $pdo->prepare("INSERT INTO materi_sub_poin (poin_id, kode, deskripsi) VALUES (?, ?, ?)");
                foreach ($_POST['sub_kode'] as $index => $kode) {
                    $sub_desc = $_POST['sub_deskripsi'][$index] ?? '';
                    $kode = trim($kode);
                    $sub_desc = trim($sub_desc);
                    
                    if (!empty($kode) && !empty($sub_desc)) {
                        $stmtSub->execute([$poin_id, $kode, $sub_desc]);
                    }
                }
            }
            
            $pdo->commit();
            header("Location: materi.php?status=success_add");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = urlencode($e->getMessage());
            header("Location: materi.php?status=error_add&msg=$error_msg");
            exit;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_materi'])) {
    $stmt = $pdo->prepare("DELETE FROM materi_poin WHERE id = ?");
    $stmt->execute([$_POST['poin_id']]);
    header("Location: materi.php?status=success_delete");
    exit;
}

// --- LOGIKA FETCH & GROUPING DATA ---
$stmt = $pdo->query("
    SELECT p.id as poin_id, p.kategori, p.deskripsi as poin_desc, 
           s.id as sub_id, s.kode, s.deskripsi as sub_desc 
    FROM materi_poin p 
    LEFT JOIN materi_sub_poin s ON p.id = s.poin_id 
    ORDER BY p.kategori, p.id, s.id
");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kelompokkan data berdasarkan Kategori -> Poin -> Sub-poin
$grouped_data = [];
foreach ($data as $row) {
    $kat = $row['kategori'];
    $p_id = $row['poin_id'];
    
    if (!isset($grouped_data[$kat])) {
        $grouped_data[$kat] = [];
    }
    if (!isset($grouped_data[$kat][$p_id])) {
        $grouped_data[$kat][$p_id] = [
            'deskripsi' => $row['poin_desc'],
            'sub_poin' => []
        ];
    }
    if ($row['sub_id']) {
        $grouped_data[$kat][$p_id]['sub_poin'][] = [
            'kode' => $row['kode'],
            'deskripsi' => $row['sub_desc']
        ];
    }
}

// Kategori Tabs
$kategori_tabs = ['Nilai Agama', 'Jati Diri', 'Literasi'];

include 'header.php'; 
include 'sidebar.php'; 
?>

<!-- Main Content -->
<main class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-navy mb-0">Materi Pembelajaran</h4>
        <!-- Tombol Pemicu Modal -->
        <button class="btn btn-primary bg-navy border-0" data-bs-toggle="modal" data-bs-target="#modalTambahMateri">
            <i class="bi bi-plus-circle"></i> Tambah Materi
        </button>
    </div>
    
    <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Materi berhasil ditambahkan!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- TABS HEADER -->
    <ul class="nav nav-tabs mb-4" id="materiTab" role="tablist">
        <?php foreach($kategori_tabs as $index => $kat): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $index === 0 ? 'active' : '' ?> fw-bold text-secondary" id="tab-<?= $index ?>" data-bs-toggle="tab" data-bs-target="#content-<?= $index ?>" type="button" role="tab">
                    <?= htmlspecialchars($kat) ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- TABS CONTENT -->
    <div class="tab-content" id="materiTabContent">
        <?php foreach($kategori_tabs as $index => $kat): ?>
            <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="content-<?= $index ?>" role="tabpanel">
                
                <?php if (empty($grouped_data[$kat])): ?>
                    <!-- EMPTY STATE -->
                    <div class="custom-card p-4 text-center py-5 shadow-sm border-0">
                        <i class="bi bi-journal-text fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="fw-bold text-muted">Belum ada materi untuk <?= htmlspecialchars($kat) ?></h5>
                        <p class="text-muted mb-0">Klik tombol "Tambah Materi" di atas untuk memasukkan data.</p>
                    </div>
                <?php else: ?>
                    <!-- ACCORDION LIST UNTUK DATA -->
                    <div class="accordion shadow-sm custom-card border-0" id="accordion-<?= $index ?>">
                        <?php 
                        $no = 1;
                        foreach($grouped_data[$kat] as $poin_id => $poin_data): 
                            $collapseId = "collapse-" . str_replace(' ', '', $kat) . "-" . $poin_id;
                        ?>
                            <div class="accordion-item border-0 border-bottom">
                                <h2 class="accordion-header d-flex align-items-center bg-white">
                                    <button class="accordion-button collapsed fw-bold text-dark bg-white flex-grow-1 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                                        <span class="me-3 text-primary"><?= $no++ ?>.</span> <?= htmlspecialchars($poin_data['deskripsi']) ?>
                                    </button>
                                    <form method="POST" class="d-inline ms-2 me-3" onsubmit="return confirm('Hapus materi ini beserta sub-poinnya?');">
                                        <input type="hidden" name="poin_id" value="<?= $poin_id ?>">
                                        <button type="submit" name="hapus_materi" class="btn btn-sm btn-outline-danger border-0" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </h2>
                                <div id="<?= $collapseId ?>" class="accordion-collapse collapse" data-bs-parent="#accordion-<?= $index ?>">
                                    <div class="accordion-body bg-light pt-3 pb-4">
                                        <?php if(empty($poin_data['sub_poin'])): ?>
                                            <p class="text-muted fst-italic mb-0">Tidak ada sub-poin.</p>
                                        <?php else: ?>
                                            <table class="table table-sm table-bordered mb-0 bg-white shadow-sm rounded overflow-hidden">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="15%" class="text-center text-muted">Kode</th>
                                                        <th class="text-muted">Deskripsi Sub-Poin</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($poin_data['sub_poin'] as $sub): ?>
                                                    <tr>
                                                        <td class="text-center fw-semibold text-secondary align-middle"><?= htmlspecialchars($sub['kode']) ?></td>
                                                        <td class="text-secondary align-middle"><?= htmlspecialchars($sub['deskripsi']) ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            </div>
        <?php endforeach; ?>
    </div>

</main>

<!-- Modal Tambah Materi -->
<div class="modal fade" id="modalTambahMateri" tabindex="-1" aria-labelledby="modalTambahMateriLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form action="" method="POST">
                <div class="modal-header border-bottom-0 bg-light">
                    <h5 class="modal-title fw-bold text-navy" id="modalTambahMateriLabel">Tambah Materi Pembelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Kategori Tab</label>
                        <select name="kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Nilai Agama">Nilai Agama dan Budi Pekerti</option>
                            <option value="Jati Diri">Jati Diri</option>
                            <option value="Literasi">Dasar-dasar Literasi, dll</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Poin Utama</label>
                        <textarea name="poin_utama" class="form-control" rows="2" placeholder="Contoh: Anak mengenal dan percaya kepada Allah SWT..." required></textarea>
                    </div>

                    <hr class="text-muted mb-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-navy">Sub-Poin (Dinamis)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="btn-tambah-sub">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Sub-poin
                        </button>
                    </div>

                    <div id="sub-poin-container">
                        <!-- Baris Sub-poin default (Baris 1) -->
                        <div class="row g-2 mb-2 sub-poin-row align-items-center">
                            <div class="col-3">
                                <input type="text" name="sub_kode[]" class="form-control form-control-sm" placeholder="Kode (A.1.1)" required>
                            </div>
                            <div class="col-8">
                                <input type="text" name="sub_deskripsi[]" class="form-control form-control-sm" placeholder="Deskripsi Sub-poin..." required>
                            </div>
                            <div class="col-1 text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-hapus-sub" disabled><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_materi" class="btn btn-primary bg-navy border-0 fw-bold px-4">Simpan Materi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('sub-poin-container');
    const btnTambah = document.getElementById('btn-tambah-sub');

    // Event Listener Tambah Sub-poin
    btnTambah.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 sub-poin-row align-items-center';
        row.innerHTML = `
            <div class="col-3">
                <input type="text" name="sub_kode[]" class="form-control form-control-sm" placeholder="Kode (A.1.1)" required>
            </div>
            <div class="col-8">
                <input type="text" name="sub_deskripsi[]" class="form-control form-control-sm" placeholder="Deskripsi Sub-poin..." required>
            </div>
            <div class="col-1 text-center">
                <button type="button" class="btn btn-sm btn-danger btn-hapus-sub"><i class="bi bi-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
        updateDeleteButtons();
    });

    // Event Listener Hapus Sub-poin (Event Delegation)
    container.addEventListener('click', function(e) {
        if (e.target.closest('.btn-hapus-sub')) {
            const row = e.target.closest('.sub-poin-row');
            if (container.querySelectorAll('.sub-poin-row').length > 1) {
                row.remove();
                updateDeleteButtons();
            }
        }
    });

    // Fungsi update status tombol hapus
    function updateDeleteButtons() {
        const rows = container.querySelectorAll('.sub-poin-row');
        const btns = container.querySelectorAll('.btn-hapus-sub');
        if (rows.length === 1) {
            btns[0].disabled = true; // Jangan hapus jika tersisa 1
        } else {
            btns.forEach(btn => btn.disabled = false);
        }
    }
});
</script>

<?php include 'footer.php'; ?>
