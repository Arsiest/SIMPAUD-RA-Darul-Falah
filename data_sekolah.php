<?php 
require_once 'koneksi.php';
include 'header.php'; 
include 'sidebar.php'; 
?>
<!-- Main Content -->
<main class="main-content">
    <h4 class="fw-bold text-secondary mb-4" style="color: #6c757d !important;">Identitas Madrasah</h4>
    
    <div class="custom-card p-4">
        <!-- Row 1: NSM & NPSN -->
        <div class="row align-items-center mb-1">
            <div class="col-md-5">
                <div class="form-floating-custom">
                    <label>NSM</label>
                    <input type="text" value="101231740092" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating-custom">
                    <label>NPSN</label>
                    <input type="text" value="69732449" readonly>
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2 mb-3">
                <button class="btn text-white fw-bold px-3 py-2 border-0" style="background-color: #e0e0e0; font-size:12px; border-radius: 4px;">PENGAJUAN</button>
                <button class="btn text-white fw-bold px-3 py-2 border-0" style="background-color: #e0e0e0; font-size:12px; border-radius: 4px;">CEK PENGAJUAN</button>
            </div>
        </div>
        <!-- Row 2: Nama Lembaga -->
        <div class="row mb-1">
            <div class="col-12">
                <div class="form-floating-custom">
                    <label>NAMA LEMBAGA</label>
                    <input type="text" value="RA DARUL FALAH" readonly>
                </div>
            </div>
        </div>
        <!-- Row 3: Nama Singkatan -->
        <div class="row mb-1">
            <div class="col-12">
                <div class="form-floating-custom">
                    <label>NAMA SINGKATAN</label>
                    <input type="text" value="RA DF" readonly>
                </div>
            </div>
        </div>
        <!-- Row 4: Status & Jenis -->
        <div class="row mb-1">
            <div class="col-md-6">
                <div class="form-floating-custom">
                    <label>STATUS MADRASAH</label>
                    <select disabled>
                        <option>Swasta</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating-custom">
                    <label>JENIS LEMBAGA</label>
                    <select disabled>
                        <option>Raudhatul Athfal</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- Row 5: NPWP & Telepon -->
        <div class="row mb-1">
            <div class="col-md-6">
                <div class="form-floating-custom">
                    <label>NPWP</label>
                    <input type="text" value="02.838.060.8-017.000" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating-custom">
                    <label>NOMOR TELEPON</label>
                    <input type="text" value="0217804792" readonly>
                </div>
            </div>
        </div>
        <!-- Row 6: Website & Email -->
        <div class="row mb-1">
            <div class="col-md-6">
                <div class="form-floating-custom">
                    <label>ALAMAT WEBSITE</label>
                    <input type="text" value="" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating-custom">
                    <label>EMAIL</label>
                    <input type="text" value="radarulfalah30@gmail.com" readonly>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
