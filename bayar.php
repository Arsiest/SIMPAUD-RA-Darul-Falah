<?php
// ==============================================================================
// 1. MIDTRANS WEBHOOK / NOTIFICATION HANDLER (SIMULATOR & REAL)
// ==============================================================================
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $json_input = file_get_contents('php://input');
    if (!empty($json_input)) {
        require_once 'koneksi.php';
        $notif = json_decode($json_input, true);
        
        // Jika ada notifikasi dari Midtrans tentang order_id dan statusnya
        if ($notif && isset($notif['order_id']) && isset($notif['transaction_status'])) {
            if ($notif['transaction_status'] == 'settlement' || $notif['transaction_status'] == 'capture') {
                $stmt = $pdo->prepare("UPDATE spp_tagihan SET status='Lunas', tgl_bayar=NOW() WHERE order_id = ?");
                $stmt->execute([$notif['order_id']]);
            }
        }
        // Hentikan eksekusi script karena ini hanya diakses oleh background Midtrans
        http_response_code(200);
        exit; 
    }
}

require_once 'koneksi.php';

// Konfigurasi API Midtrans Sandbox
$midtrans_client_key = "Mid-client-XXXXXXXX";
$midtrans_server_key = "Mid-server-XXXXXXXXXXXX";

// ==============================================================================
// 2. SETUP DATABASE SPP
// ==============================================================================
// Struktur Tabel tagihan_spp: 
// id (INT), id_siswa (INT), bulan (VARCHAR), nominal (INT), 
// status (ENUM), order_id (VARCHAR), tgl_bayar (DATETIME), created_at (TIMESTAMP)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS spp_tagihan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_siswa INT NOT NULL,
        bulan VARCHAR(50) NOT NULL,
        nominal INT NOT NULL,
        status ENUM('Lunas', 'Belum Bayar') NOT NULL DEFAULT 'Belum Bayar',
        order_id VARCHAR(50) NULL,
        tgl_bayar DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Patch untuk memastikan kolom order_id ada (jika tabel sudah terlanjur dibuat di versi sebelumnya)
    try {
        $pdo->exec("ALTER TABLE spp_tagihan ADD COLUMN order_id VARCHAR(50) NULL AFTER status");
    } catch(PDOException $e) {}
    
} catch(PDOException $e) {}


// ==============================================================================
// 3. MIDTRANS SNAP API REQUEST (CREATE TRANSACTION)
// ==============================================================================
$snap_token = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_spp'])) {
    $tagihan_id = $_POST['tagihan_id'];
    
    // Ambil data tagihan dan nama siswa
    $stmt = $pdo->prepare("SELECT t.*, s.nama_lengkap FROM spp_tagihan t JOIN siswa s ON t.id_siswa = s.id WHERE t.id = ?");
    $stmt->execute([$tagihan_id]);
    $tagihan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tagihan && $tagihan['status'] == 'Belum Bayar') {
        
        // Buat order_id unik (tambahkan timestamp agar selalu unik untuk testing localhost)
        $order_id = "INV-" . date('Ym') . "-" . str_pad($tagihan['id'], 5, '0', STR_PAD_LEFT) . "-" . time();
        // Update order_id ke database
        $pdo->prepare("UPDATE spp_tagihan SET order_id = ? WHERE id = ?")->execute([$order_id, $tagihan_id]);
        
        // Siapkan Payload untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int)$tagihan['nominal'],
            ],
            'customer_details' => [
                'first_name' => $tagihan['nama_lengkap'],
            ],
            // Aktifkan otomatis Virtual Account & QRIS
            'enabled_payments' => [
                'bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'other_va', 'gopay', 'shopeepay', 'qris'
            ]
        ];
        
        // Eksekusi cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://app.sandbox.midtrans.com/snap/v1/transactions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/json",
                // Autorisasi Basic Auth menggunakan Server Key
                "Authorization: Basic " . base64_encode($midtrans_server_key . ":")
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        if (!$err) {
            $res = json_decode($response, true);
            if (isset($res['token'])) {
                // Simpan token untuk diinject ke JavaScript
                $snap_token = $res['token'];
            } else {
                $err_msg = urlencode("Gagal token: " . $res['error_messages'][0] ?? 'Unknown Error');
                header("Location: bayar.php?view=public&siswa_id=$siswa_id_aktif&status=error_pay&msg=$err_msg");
                exit;
            }
        }
    }
}


// Simulasi Webhook Manual untuk Testing Lunas di Browser
if (isset($_GET['simulasilunas']) && isset($_GET['orderid'])) {
    $stmt = $pdo->prepare("UPDATE spp_tagihan SET status='Lunas', tgl_bayar=NOW() WHERE order_id = ?");
    $stmt->execute([$_GET['orderid']]);
    $sid = $_GET['siswa_id'];
    header("Location: bayar.php?view=public&siswa_id=$sid&status=success_pay");
    exit;
}

// Ambil semua data siswa untuk dropdown public view
$stmt = $pdo->query("SELECT id, nama_lengkap, nisn, kelas FROM siswa ORDER BY nama_lengkap ASC");
$siswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$view = isset($_GET['view']) ? $_GET['view'] : 'admin';

// ==============================================================================
// 4. ADMIN VIEW
// ==============================================================================
if ($view == 'admin') {
    
    $bulan_ini = "Juli 2026";
    
    // Total murid aktif dari tabel siswa
    $total_siswa_aktif = count($siswa_list);
    $target_bulanan = $total_siswa_aktif * 250000;
    
    // Hitung pendapatan yang benar-benar sudah lunas
    $stmt_stat = $pdo->prepare("SELECT 
        SUM(CASE WHEN status = 'Lunas' THEN nominal ELSE 0 END) as total_lunas,
        SUM(CASE WHEN status = 'Lunas' THEN 1 ELSE 0 END) as jumlah_lunas
        FROM spp_tagihan WHERE bulan = ?");
    $stmt_stat->execute([$bulan_ini]);
    $stat = $stmt_stat->fetch(PDO::FETCH_ASSOC);
    
    $total_lunas = $stat['total_lunas'] ?: 0;
    $sisa_tagihan = $target_bulanan - $total_lunas;
    
    $total_siswa_tagihan = $total_siswa_aktif;
    $siswa_dibayar = $stat['jumlah_lunas'] ?: 0;
    $persentase = $total_siswa_tagihan > 0 ? round(($siswa_dibayar / $total_siswa_tagihan) * 100) : 0;

    // Ambil data tabel (Menampilkan SEMUA murid, baik yang sudah ada record tagihan maupun yang belum)
    $stmt_trx = $pdo->prepare("
        SELECT s.id as siswa_id, s.nama_lengkap, s.nisn, s.kelas, 
               t.order_id, t.nominal, t.status, ? as bulan 
        FROM siswa s 
        LEFT JOIN spp_tagihan t ON s.id = t.id_siswa AND t.bulan = ? 
        ORDER BY s.kelas ASC, s.nama_lengkap ASC
    ");
    $stmt_trx->execute([$bulan_ini, $bulan_ini]);
    $transaksi = $stmt_trx->fetchAll(PDO::FETCH_ASSOC);

    include 'header.php';
    include 'sidebar.php';
    ?>
    <style>
        .progress-bar-spp { background-color: var(--herb-leaf, #7A916E); }
        .text-night-green { color: var(--night-green, #1A2F22) !important; }
        .bg-night-green { background-color: var(--night-green, #1A2F22); color: white; }
    </style>
    <main class="main-content">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <h4 class="fw-bold text-night-green mb-0"><i class="bi bi-cash-coin me-2"></i>Keuangan SPP</h4>
            <a href="bayar.php?view=public" class="btn btn-warning fw-bold shadow-sm rounded-pill px-4" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Halaman Publik Wali Murid
            </a>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="custom-card p-4 h-100">
                    <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:0.75rem;">Status SPP Bulan Ini (<?= $bulan_ini ?>)</h6>
                    <h3 class="fw-bold text-night-green mb-3"><?= $persentase ?>% Lunas</h3>
                    <div class="progress mb-2 bg-secondary bg-opacity-10" style="height: 12px; border-radius: 6px;">
                        <div class="progress-bar progress-bar-spp" style="width: <?= $persentase ?>%; border-radius: 6px;"></div>
                    </div>
                    <p class="text-muted small mb-0 mt-2"><?= $siswa_dibayar ?> dari <?= $total_siswa_tagihan ?> murid telah membayar bulan ini.</p>
                </div>
            </div>
            <div class="col-md-8">
                <div class="custom-card p-4 h-100 d-flex flex-column justify-content-center">
                    <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:0.75rem;">Ringkasan Pendapatan (<?= $bulan_ini ?>)</h6>
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <p class="text-muted small mb-1">Target Bulanan</p>
                            <h5 class="fw-bold text-night-green mb-0">Rp <?= number_format($target_bulanan, 0, ',', '.') ?></h5>
                        </div>
                        <div class="col-4 border-end">
                            <p class="text-muted small mb-1">Terkumpul</p>
                            <h5 class="fw-bold text-success mb-0">Rp <?= number_format($total_lunas, 0, ',', '.') ?></h5>
                        </div>
                        <div class="col-4">
                            <p class="text-muted small mb-1">Sisa Tagihan</p>
                            <h5 class="fw-bold text-danger mb-0">Rp <?= number_format($sisa_tagihan, 0, ',', '.') ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="custom-card p-4 border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-bold text-night-green mb-0">Rincian Transaksi Pembayaran</h6>
                <div class="input-group" style="width: 250px;">
                    <input type="text" class="form-control form-control-sm border-secondary" placeholder="Cari NISN / Nama...">
                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted small text-uppercase fw-bold">Order ID</th>
                            <th class="text-muted small text-uppercase fw-bold">NISN</th>
                            <th class="text-muted small text-uppercase fw-bold">Nama Murid</th>
                            <th class="text-muted small text-uppercase fw-bold">Kelas</th>
                            <th class="text-muted small text-uppercase fw-bold">Bulan</th>
                            <th class="text-muted small text-uppercase fw-bold">Jumlah</th>
                            <th class="text-muted small text-uppercase fw-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($transaksi)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">0 Data - Belum ada transaksi pembayaran.</td></tr>
                        <?php else: ?>
                            <?php foreach($transaksi as $trx): ?>
                            <tr>
                                <td class="text-muted small"><?= htmlspecialchars($trx['order_id'] ?? '-') ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($trx['nisn'] ?: '-') ?></td>
                                <td class="fw-bold text-night-green"><?= htmlspecialchars($trx['nama_lengkap']) ?></td>
                                <td><span class="badge bg-light text-dark border">Kelas <?= htmlspecialchars($trx['kelas'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($trx['bulan']) ?></td>
                                <td class="fw-bold">Rp <?= number_format($trx['nominal'] ?? 250000, 0, ',', '.') ?></td>
                                <td>
                                    <?php if(($trx['status'] ?? 'Belum Bayar') == 'Lunas'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-2">Belum Bayar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <?php
    include 'footer.php';
} else {
    // ==============================================================================
    // 5. PUBLIC VIEW WALI MURID
    // ==============================================================================
    
    $selected_siswa = null;
    $tagihan_siswa = [];
    $siswa_id_aktif = isset($_GET['siswa_id']) ? $_GET['siswa_id'] : '';
    
    if (!empty($siswa_id_aktif)) {
        $stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
        $stmt->execute([$siswa_id_aktif]);
        $selected_siswa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($selected_siswa) {
            $stmt_t = $pdo->prepare("SELECT * FROM spp_tagihan WHERE id_siswa = ? ORDER BY id ASC");
            $stmt_t->execute([$selected_siswa['id']]);
            $tagihan_siswa = $stmt_t->fetchAll(PDO::FETCH_ASSOC);
            
            // Generate dummy bill if empty for testing Midtrans flow
            if (empty($tagihan_siswa)) {
                $pdo->prepare("INSERT INTO spp_tagihan (id_siswa, bulan, nominal) VALUES (?, ?, ?)")->execute([$selected_siswa['id'], 'Juli 2026', 250000]);
                $stmt_t->execute([$selected_siswa['id']]);
                $tagihan_siswa = $stmt_t->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }
    
    // Siapkan data cetak spesifik (dari Lunas)
    $cetak_id = isset($_GET['cetak_id']) ? $_GET['cetak_id'] : null;
    $cetak_data = null;
    if ($cetak_id) {
        foreach($tagihan_siswa as $t) {
            if ($t['id'] == $cetak_id && $t['status'] == 'Lunas') {
                $cetak_data = $t;
                break;
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cek Tagihan SPP - RA Darul Falah</title>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        
        <!-- SCRIPT MIDTRANS SNAP -->
        <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= $midtrans_client_key ?>"></script>
        
        <style>
            :root {
                --feather-beige: #F2EFE9;
                --dusty-olive: #8B8C7A;
                --herb-leaf: #7A916E;
                --night-green: #1A2F22;
                --soft-green: #d1e7dd;
            }
            body { font-family: 'Nunito', sans-serif; background-color: var(--feather-beige); color: var(--night-green); }
            
            .public-container { max-width: 480px; margin: 0 auto; padding: 2rem 1rem; }
            .public-card { background: #fff; border-radius: 12px; border: 1px solid rgba(139, 140, 122, 0.2); box-shadow: 0 4px 12px rgba(0,0,0,0.04); padding: 1.5rem; margin-bottom: 1rem; }
            
            .btn-bayar { background-color: var(--night-green); color: #fff; border: none; border-radius: 6px; font-weight: 600; font-size: 0.85rem; padding: 6px 16px; transition: all 0.2s; }
            .btn-bayar:hover { background-color: #122117; color: #fff; }
            .btn-cetak { background-color: #fff; color: var(--night-green); border: 1px solid var(--dusty-olive); border-radius: 6px; font-weight: 600; font-size: 0.85rem; padding: 6px 12px; transition: all 0.2s; text-decoration: none; display: inline-block; }
            
            .badge-lunas { background-color: var(--soft-green); color: var(--night-green); padding: 6px 14px; font-weight: 700; border-radius: 20px; font-size: 0.8rem; display: inline-block; }
            
            .list-tagihan { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #f0f0f0; }
            .list-tagihan:last-child { border-bottom: none; padding-bottom: 0; }
            
            .kembali-link { color: var(--dusty-olive); font-size: 0.9rem; text-decoration: none; font-weight: 700; display: block; text-align: center; margin-top: 2.5rem; transition: color 0.2s; }
            .kembali-link:hover { color: var(--night-green); }
            
            /* =========================================================
               PRINT SPECIFIC CSS (KUITANSI RESMI)
               ========================================================= */
            @media print {
                @page { size: A4 portrait; margin: 2cm; }
                body { background-color: #fff !important; color: #000 !important; font-family: 'Times New Roman', Times, serif; }
                
                /* Sembunyikan elemen Web UI (Card Pencarian, Header Web, Navigasi, List Tagihan) */
                .d-print-none, .public-card, .kembali-link { display: none !important; }
                .public-container { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
                
                /* Tampilkan Area Cetak */
                .print-layout { display: block !important; width: 100%; position: relative; }
                
                /* Kop Surat Resmi */
                .print-header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 25px; }
                .print-header h2 { font-size: 22pt; font-weight: bold; margin: 0; }
                .print-header p { font-size: 12pt; margin: 5px 0 0 0; }
                
                /* Title Kuitansi */
                .kuitansi-title { text-align: center; font-size: 16pt; font-weight: bold; text-decoration: underline; margin-bottom: 30px; letter-spacing: 1px; }
                
                /* Tabel Informasi Siswa */
                .print-table-info { width: 100%; font-size: 12pt; margin-bottom: 30px; }
                .print-table-info td { padding: 5px 0; border: none; }
                
                /* Tabel Rincian Pembayaran */
                .print-table-detail { width: 100%; border-collapse: collapse; font-size: 12pt; margin-bottom: 40px; }
                .print-table-detail th, .print-table-detail td { border: 1px solid #000; padding: 10px; }
                .print-table-detail th { background-color: #f0f0f0 !important; -webkit-print-color-adjust: exact; text-align: center; }
                
                /* Area Tanda Tangan */
                .print-signature { text-align: right; font-size: 12pt; margin-top: 50px; }
                
                /* Watermark LUNAS */
                .watermark-lunas {
                    position: absolute; top: 40%; left: 20%; font-size: 80pt; color: rgba(0, 128, 0, 0.1); 
                    font-weight: bold; transform: rotate(-35deg); border: 15px solid rgba(0, 128, 0, 0.1); 
                    padding: 20px 50px; border-radius: 30px; z-index: -1;
                }
            }
        </style>
    </head>
    <body>
        
        <?php if ($cetak_data && $selected_siswa): ?>
        <!-- ==============================================================
             AREA CETAK KUITANSI RESMI (HANYA MUNCUL SAAT PRINT DITEKAN)
             ============================================================== -->
        <div class="d-none d-print-block print-layout">
            <div class="watermark-lunas">L U N A S</div>
            
            <div class="print-header">
                <h2>RA DARUL FALAH</h2>
                <p>Jl. Benda Gg. H. Musa RT.008/04 No.30, Cilandak Timur, Jakarta Selatan</p>
            </div>
            
            <div class="kuitansi-title">KUITANSI PEMBAYARAN SPP</div>
            
            <table class="print-table-info">
                <tr><td width="25%">No. Transaksi / Order ID</td><td width="3%">:</td><td><b><?= htmlspecialchars($cetak_data['order_id']) ?></b></td></tr>
                <tr><td>Telah Terima Dari</td><td>:</td><td>Bapak/Ibu Wali Murid dari <b><?= htmlspecialchars($selected_siswa['nama_lengkap']) ?></b></td></tr>
                <tr><td>Kelas / NISN</td><td>:</td><td>Kelas <?= htmlspecialchars($selected_siswa['kelas']) ?> / <?= htmlspecialchars($selected_siswa['nisn'] ?: '-') ?></td></tr>
            </table>
            
            <table class="print-table-detail">
                <thead>
                    <tr>
                        <th width="10%">No</th>
                        <th width="50%">Keterangan Pembayaran</th>
                        <th width="40%">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:center;">1</td>
                        <td>Pembayaran SPP Bulan <b><?= htmlspecialchars($cetak_data['bulan']) ?></b></td>
                        <td style="text-align:right; font-weight:bold;">Rp <?= number_format($cetak_data['nominal'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:right; font-weight:bold;">TOTAL</td>
                        <td style="text-align:right; font-weight:bold;">Rp <?= number_format($cetak_data['nominal'], 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <table style="width: 100%;">
                <tr>
                    <td width="60%">
                        <i style="font-size:10pt; color:#555;">* Kuitansi ini sah diterbitkan oleh sistem tanpa cap basah.</i><br>
                        <i style="font-size:10pt; color:#555;">* Tanggal Bayar: <?= date('d M Y, H:i', strtotime($cetak_data['tgl_bayar'])) ?></i>
                    </td>
                    <td width="40%" class="print-signature">
                        <p>Jakarta, <?= date('d M Y') ?></p>
                        <p>Tata Usaha / Bendahara,</p>
                        <br><br><br><br>
                        <p><strong>_________________________</strong></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Script Autoprint untuk spesifik cetak -->
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
        <?php endif; ?>


        <!-- ==============================================================
             WEB UI MODE PUBLIK (HANYA MUNCUL DI LAYAR HP/PC)
             ============================================================== -->
        <div class="public-container d-print-none">
            
            <!-- Web Header Logo -->
            <div class="text-center mb-4 mt-2">
                <img src="logo.png" alt="Logo" width="45" height="45" class="mb-2 object-fit-contain" onerror="this.style.display='none'">
                <h5 class="fw-bold mb-0 text-night-green">RA Darul Falah</h5>
            </div>
            
            <!-- CARD 1: FORM PENCARIAN -->
            <div class="public-card">
                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pencarian Data Siswa</h6>
                <form method="GET" action="bayar.php">
                    <input type="hidden" name="view" value="public">
                    <select name="siswa_id" class="form-select border-dusty" style="font-size: 0.95rem; font-weight: 600;" onchange="this.form.submit()">
                        <option value="">-- Pilih Nama Siswa --</option>
                        <?php foreach($siswa_list as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($siswa_id_aktif == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nama_lengkap']) ?> (NISN: <?= htmlspecialchars($s['nisn'] ?: '-') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <?php if($selected_siswa): ?>
                <!-- CARD 2: PROFIL MURID -->
                <div class="public-card">
                    <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Profil Murid</h6>
                    <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                        <span class="text-muted small">Nama Murid</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($selected_siswa['nama_lengkap']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                        <span class="text-muted small">Kelas</span>
                        <span class="fw-bold text-dark">Kelas <?= htmlspecialchars($selected_siswa['kelas']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Tahun Ajar</span>
                        <span class="fw-bold text-dark">2026/2027 - Sem. 1</span>
                    </div>
                </div>
                
                <!-- CARD 3: DAFTAR TAGIHAN SPP -->
                <div class="public-card">
                    <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Daftar Tagihan SPP</h6>
                    
                    <?php if(empty($tagihan_siswa)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
                            <small>Belum ada tagihan SPP untuk siswa ini.</small>
                        </div>
                    <?php else: ?>
                        <?php foreach($tagihan_siswa as $t): ?>
                            <div class="list-tagihan">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars($t['bulan']) ?></div>
                                    <div class="text-muted small">Rp <?= number_format($t['nominal'], 0, ',', '.') ?></div>
                                </div>
                                <div>
                                    <?php if($t['status'] == 'Lunas'): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge-lunas">Lunas</span>
                                            <!-- Tombol Cetak Bukti (Link khusus) -->
                                            <a href="bayar.php?view=public&siswa_id=<?= $siswa_id_aktif ?>&cetak_id=<?= $t['id'] ?>" class="btn-cetak" title="Cetak Kuitansi Resmi"><i class="bi bi-printer"></i> Cetak Bukti</a>
                                        </div>
                                    <?php else: ?>
                                        <!-- Form Midtrans Pay -->
                                        <form method="POST" action="bayar.php?view=public&siswa_id=<?= $siswa_id_aktif ?>">
                                            <input type="hidden" name="tagihan_id" value="<?= $t['id'] ?>">
                                            <button type="submit" name="pay_spp" class="btn-bayar shadow-sm">Bayar Sekarang</button>
                                        </form>
                                        
                                        <!-- Tombol Simulasi Webhook (Hanya untuk testing Sandbox) -->
                                        <?php if(!empty($t['order_id'] ?? '')): ?>
                                        <div class="mt-2 text-end">
                                            <a href="bayar.php?view=public&siswa_id=<?= $siswa_id_aktif ?>&simulasilunas=1&orderid=<?= $t['order_id'] ?>" style="font-size: 0.65rem;" class="text-muted text-decoration-none">[Test Simulasi Lunas]</a>
                                        </div>
                                        <?php endif; ?>
                                        
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Instruksi Pilih Siswa -->
                <div class="public-card text-center py-5">
                    <i class="bi bi-search fs-1 text-muted mb-3 d-block opacity-50"></i>
                    <h6 class="fw-bold text-muted">Silakan Pilih Nama Siswa</h6>
                    <p class="text-muted small mb-0">Pilih nama siswa pada kolom pencarian di atas untuk melihat rincian tagihan SPP.</p>
                </div>
            <?php endif; ?>
            
            <a href="bayar.php?view=admin" class="kembali-link">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard Admin SIMPAUD
            </a>
            
        </div>
        
        <!-- SCRIPT UNTUK TRIGGER POP-UP MIDTRANS -->
        <script>
            <?php if(!empty($snap_token)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                window.snap.pay('<?= $snap_token ?>', {
                    onSuccess: function(result){
                        // Teruskan order_id ke backend untuk update status jadi Lunas secara manual (karena localhost tidak bisa terima webhook otomatis)
                        window.location.href = window.location.pathname + "?view=public&siswa_id=<?= $siswa_id_aktif ?>&simulasilunas=1&orderid=" + result.order_id;
                    },
                    onPending: function(result){
                    },
                    onError: function(result){
                    },
                    onClose: function(){
                    }
                });
            });
            <?php endif; ?>
        </script>
        
        <!-- Toast Notification Container -->
        <?php if (isset($_GET['status'])): ?>
        <div id="toastNotif" class="position-fixed top-0 end-0 p-3 mt-4" style="z-index: 9999">
            <?php if(strpos($_GET['status'], 'success') !== false): ?>
            <div class="toast align-items-center text-white bg-success border-0 show shadow" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex p-1">
                    <div class="toast-body fw-bold" style="font-size: 1rem;">
                        <i class="bi bi-check-circle-fill me-2"></i> Pembayaran berhasil diproses!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            <?php else: ?>
            <div class="toast align-items-center text-white bg-danger border-0 show shadow" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex p-1">
                    <div class="toast-body fw-bold" style="font-size: 1rem;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars(urldecode($_GET['msg'] ?? 'Terjadi kesalahan!')) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <script>
            setTimeout(function() {
                var toastEl = document.getElementById('toastNotif');
                if (toastEl) {
                    toastEl.style.transition = 'opacity 0.5s ease';
                    toastEl.style.opacity = '0';
                    setTimeout(() => toastEl.remove(), 500);
                }
            }, 4000);
        </script>
        <?php endif; ?>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
?>
