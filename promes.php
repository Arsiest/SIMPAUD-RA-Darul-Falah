<?php 
require_once 'koneksi.php';

// 1. SETUP DATABASE
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS program_semester (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bulan VARCHAR(50) NOT NULL,
        alokasi_waktu VARCHAR(50) NOT NULL,
        tema VARCHAR(255) NOT NULL,
        sub_topik TEXT NOT NULL,
        keterangan TEXT NOT NULL,
        capaian TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// 2. LOGIKA CRUD (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['simpan_promes'])) {
        $stmt = $pdo->prepare("INSERT INTO program_semester (bulan, alokasi_waktu, tema, sub_topik, keterangan, capaian) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['bulan'], $_POST['alokasi_waktu'], $_POST['tema'], 
            $_POST['sub_topik'], $_POST['keterangan'], $_POST['capaian']
        ]);
        header("Location: promes.php?status=success_add");
        exit;
    }
    elseif (isset($_POST['edit_promes'])) {
        $stmt = $pdo->prepare("UPDATE program_semester SET bulan=?, alokasi_waktu=?, tema=?, sub_topik=?, keterangan=?, capaian=? WHERE id=?");
        $stmt->execute([
            $_POST['bulan'], $_POST['alokasi_waktu'], $_POST['tema'], 
            $_POST['sub_topik'], $_POST['keterangan'], $_POST['capaian'], $_POST['id']
        ]);
        header("Location: promes.php?status=success_edit");
        exit;
    }
    elseif (isset($_POST['hapus_promes'])) {
        $stmt = $pdo->prepare("DELETE FROM program_semester WHERE id=?");
        $stmt->execute([$_POST['id']]);
        header("Location: promes.php?status=success_delete");
        exit;
    }
}

// 3. LOGIKA FETCH DATA
$data_promes = $pdo->query("SELECT * FROM program_semester ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
include 'sidebar.php'; 
?>
<style>
/* Accordion transition for grid rows */
.grid-detail {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}
.grid-detail.open {
    max-height: 500px;
}
.chevron-icon { transition: transform 0.3s ease; }
.row-active .chevron-icon { transform: rotate(180deg); }
.row-active {
    background-color: #f8fafc;
    border-left: 4px solid #1A2F22 !important; /* Night Green edge */
}
.cursor-pointer { cursor: pointer; }
.hover-bg-light:hover { background-color: #f8f9fa; }
.bg-slate-50 { background-color: #F2EFE9; /* Feather Beige */ }
.border-dashed { border-style: dashed !important; }

/* ---------------------------------------------------
   PRINT LAYOUT STYLES 
   --------------------------------------------------- */
@media print {
    @page { size: A4 landscape; margin: 1.5cm; }
    #sidebar, .topbar, .alert, button, .custom-card, .d-print-none, .accordion, .nav, footer, .hero-header, form, .modal { display: none !important; }
    body { background-color: white !important; color: black !important; }
    .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
    .print-only { display: block !important; }
    
    .print-table { width: 100%; border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; font-size: 10pt; }
    .print-table th, .print-table td { border: 1px solid black; padding: 8px 10px; vertical-align: top; }
    .print-table th { background-color: #f3f4f6 !important; -webkit-print-color-adjust: exact; font-weight: bold; text-align: center; vertical-align: middle; }
    .print-table tr { page-break-inside: avoid; }
    
    .print-header { text-align: center; margin-bottom: 25px; font-family: Arial, Helvetica, sans-serif; }
    .print-header h3 { margin: 0; font-size: 16pt; font-weight: bold; text-decoration: underline; }
    .print-header h4 { margin: 5px 0 0 0; font-size: 14pt; font-weight: bold; }
    .print-header p { margin: 5px 0 0 0; font-size: 12pt; }
}
</style>

<!-- Main Content -->
<main class="main-content">
    
    <!-- ==============================================
         WEB UI (D-PRINT-NONE) 
         ============================================== -->
    <div class="d-print-none">
        
        <!-- Info Alert -->
        <div class="alert d-flex align-items-center p-3 mb-4 shadow-sm border-0 bg-white" style="border-left: 4px solid #8B8C7A !important;">
            <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                <i class="bi bi-info-circle text-secondary fs-5"></i>
            </div>
            <div>
                <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                    Berikut adalah Program Semester (PROMES). Klik pada baris bulan untuk melihat detail Sub Topik, Kegiatan, dan Capaian.
                </p>
            </div>
            <div class="ms-auto d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm fw-bold shadow-sm d-none d-md-block" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Cetak Promes
                </button>
                <button class="btn btn-sm text-white fw-bold shadow-sm" style="background-color: #1A2F22;" data-bs-toggle="modal" data-bs-target="#modalTambahPromes">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Data
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">Grid Program Semester</h5>
        </div>

        <!-- THE GRID -->
        <div class="custom-card border overflow-hidden">
            <!-- Grid Header -->
            <div class="row mx-0 p-3 bg-light border-bottom text-muted fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">
                <div class="col-2 ps-3">Bulan</div>
                <div class="col-2 text-center">Alokasi Waktu</div>
                <div class="col-6">Fokus Tema Utama</div>
                <div class="col-2 text-center">Aksi</div>
            </div>

            <div class="d-flex flex-column">
                <?php if (empty($data_promes)): ?>
                    <div class="p-5 text-center text-muted">Belum ada data Program Semester.</div>
                <?php else: foreach ($data_promes as $row): ?>
                    <!-- ROW ITEM -->
                    <div class="border-bottom border-light position-relative">
                        <div class="row mx-0 p-3 cursor-pointer hover-bg-light align-items-center border-start border-4 border-transparent" onclick="toggleRow(this)">
                            <div class="col-2 ps-3 fw-bold text-dark"><?= htmlspecialchars($row['bulan']) ?></div>
                            <div class="col-2 text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-2"><?= htmlspecialchars($row['alokasi_waktu']) ?></span>
                            </div>
                            <div class="col-6 text-muted" style="font-size: 0.9rem;"><?= htmlspecialchars($row['tema']) ?></div>
                            <div class="col-2 text-center">
                                <!-- Aksi Edit / Hapus -->
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" title="Edit" onclick="event.stopPropagation(); editPromes(<?= htmlspecialchars(json_encode($row)) ?>);">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Hapus data bulan ini?');" onclick="event.stopPropagation();">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="hapus_promes" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                                <i class="bi bi-chevron-down chevron-icon ms-2 text-muted"></i>
                            </div>
                        </div>
                        <div class="grid-detail bg-white">
                            <div class="p-4 border-top border-dashed bg-slate-50">
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <h6 class="fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Sub Topik</h6>
                                        <div class="text-secondary" style="font-size: 0.85rem; padding-left: 1rem;">
                                            <?= nl2br(htmlspecialchars($row['sub_topik'])) ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Keterangan / Kegiatan</h6>
                                        <div class="text-secondary" style="font-size: 0.85rem; padding-left: 1rem;">
                                            <?= nl2br(htmlspecialchars($row['keterangan'])) ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Capaian Penilaian</h6>
                                        <p class="text-muted mt-2" style="font-size: 0.8rem;"><?= nl2br(htmlspecialchars($row['capaian'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div> <!-- /d-print-none -->

    <!-- ==============================================
         PRINT LAYOUT (D-NONE D-PRINT-BLOCK) 
         ============================================== -->
    <div class="d-none print-only">
        
        <div class="print-header">
            <h3>PROGRAM SEMESTER (PROMES)</h3>
            <h4>RA DARUL FALAH</h4>
            <p>Tahun Ajaran Berjalan</p>
        </div>
        
        <table class="print-table">
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th width="10%">BULAN</th>
                    <th width="10%">ALOKASI WAKTU</th>
                    <th width="25%">FOKUS TEMA UTAMA & SUB TOPIK</th>
                    <th width="25%">KETERANGAN / KEGIATAN</th>
                    <th width="25%">CAPAIAN PENILAIAN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data_promes)): $no=1; foreach($data_promes as $row): ?>
                <tr>
                    <td style="text-align: center;"><?= $no++ ?></td>
                    <td style="text-align: center;"><?= htmlspecialchars($row['bulan']) ?></td>
                    <td style="text-align: center;"><?= htmlspecialchars($row['alokasi_waktu']) ?></td>
                    <td>
                        <strong>Tema:</strong> <?= htmlspecialchars($row['tema']) ?><br><br>
                        <strong>Sub Topik:</strong><br>
                        <?= nl2br(htmlspecialchars($row['sub_topik'])) ?>
                    </td>
                    <td>
                        <?= nl2br(htmlspecialchars($row['keterangan'])) ?>
                    </td>
                    <td>
                        <?= nl2br(htmlspecialchars($row['capaian'])) ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        
    </div>
</main>

<!-- Modal Tambah PROMES -->
<div class="modal fade" id="modalTambahPromes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="promes.php" class="modal-content shadow" style="background-color: #F2EFE9;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #1A2F22;">Input Data PROMES</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Bulan</label>
                        <input type="text" name="bulan" class="form-control border-0 shadow-sm" placeholder="Contoh: Juli" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Alokasi Waktu</label>
                        <input type="text" name="alokasi_waktu" class="form-control border-0 shadow-sm" placeholder="Contoh: 3 Minggu" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Fokus Tema Utama</label>
                        <input type="text" name="tema" class="form-control border-0 shadow-sm" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Sub Topik</label>
                        <textarea name="sub_topik" class="form-control border-0 shadow-sm" rows="3" placeholder="- Sub topik 1..."></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Keterangan / Kegiatan</label>
                        <textarea name="keterangan" class="form-control border-0 shadow-sm" rows="3" placeholder="- Kegiatan 1..."></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Capaian Penilaian</label>
                        <textarea name="capaian" class="form-control border-0 shadow-sm" rows="3" placeholder="Nilai Agama, Jati Diri..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" name="simpan_promes" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1A2F22;">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit PROMES -->
<div class="modal fade" id="modalEditPromes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="promes.php" class="modal-content shadow" style="background-color: #F2EFE9;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #1A2F22;">Edit Data PROMES</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <input type="hidden" name="id" id="edit_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Bulan</label>
                        <input type="text" name="bulan" id="edit_bulan" class="form-control border-0 shadow-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Alokasi Waktu</label>
                        <input type="text" name="alokasi_waktu" id="edit_alokasi" class="form-control border-0 shadow-sm" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Fokus Tema Utama</label>
                        <input type="text" name="tema" id="edit_tema" class="form-control border-0 shadow-sm" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Sub Topik</label>
                        <textarea name="sub_topik" id="edit_sub" class="form-control border-0 shadow-sm" rows="3"></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Keterangan / Kegiatan</label>
                        <textarea name="keterangan" id="edit_ket" class="form-control border-0 shadow-sm" rows="3"></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Capaian Penilaian</label>
                        <textarea name="capaian" id="edit_capaian" class="form-control border-0 shadow-sm" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" name="edit_promes" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #1A2F22;">Update Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleRow(element) {
        const rowHeader = element;
        const detailContainer = element.nextElementSibling;
        
        rowHeader.classList.toggle('row-active');
        rowHeader.classList.toggle('border-transparent');
        
        if (detailContainer.classList.contains('open')) {
            detailContainer.classList.remove('open');
        } else {
            document.querySelectorAll('.grid-detail.open').forEach(el => {
                if (el !== detailContainer) {
                    el.classList.remove('open');
                    el.previousElementSibling.classList.remove('row-active');
                    el.previousElementSibling.classList.add('border-transparent');
                }
            });
            detailContainer.classList.add('open');
        }
    }

    function editPromes(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_bulan').value = data.bulan;
        document.getElementById('edit_alokasi').value = data.alokasi_waktu;
        document.getElementById('edit_tema').value = data.tema;
        document.getElementById('edit_sub').value = data.sub_topik;
        document.getElementById('edit_ket').value = data.keterangan;
        document.getElementById('edit_capaian').value = data.capaian;
        
        var modal = new bootstrap.Modal(document.getElementById('modalEditPromes'));
        modal.show();
    }
</script>

<?php include 'footer.php'; ?>
