<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<nav id="sidebar" class="d-flex flex-column py-3 d-none d-lg-flex overflow-y-auto">
    <div class="text-center mb-4 px-3">
        <div class="d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-mortarboard-fill fs-3 text-navy"></i>
            <h5 class="mb-0 fw-bold text-navy">SIMPAUD</h5>
        </div>
    </div>
    
    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill text-navy"></i> Beranda
            </a>
        </li>

        <div class="sidebar-heading text-uppercase">Dokumen I</div>
        <li class="nav-item">
            <a href="materi.php" class="nav-link <?= ($current_page == 'materi.php') ? 'active' : '' ?>">
                <i class="bi bi-journal-text text-warning"></i> Materi Pembelajaran
            </a>
        </li>
        <li class="nav-item">
            <a href="kalender.php" class="nav-link <?= ($current_page == 'kalender.php') ? 'active' : '' ?>">
                <i class="bi bi-calendar-event text-danger"></i> Kalender Pendidikan
            </a>
        </li>
        
        <div class="sidebar-heading text-uppercase">Dokumen II</div>
        <li class="nav-item">
            <a href="promes.php" class="nav-link <?= ($current_page == 'promes.php') ? 'active' : '' ?>">
                <i class="bi bi-folder2 text-warning"></i> Program Semester
            </a>
        </li>
        <li class="nav-item">
            <a href="rppm.php" class="nav-link <?= ($current_page == 'rppm.php') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-ruled text-primary"></i> RPPM & RPPH
            </a>
        </li>
        
        <div class="sidebar-heading text-uppercase">Menu Lainnya</div>
        <li class="nav-item">
            <a href="penilaian.php" class="nav-link <?= ($current_page == 'penilaian.php') ? 'active' : '' ?>">
                <i class="bi bi-clipboard-check"></i> Penilaian
            </a>
        </li>

        <li class="nav-item">
            <a href="siswa.php" class="nav-link <?= ($current_page == 'siswa.php') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Data Siswa
            </a>
        </li>
        <li class="nav-item">
            <a href="absensi_guru.php" class="nav-link <?= ($current_page == 'absensi_guru.php') ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i> Data Guru
            </a>
        </li>
        <li class="nav-item">
            <a href="bayar.php" class="nav-link <?= ($current_page == 'bayar.php') ? 'active' : '' ?>">
                <i class="bi bi-cash-coin"></i> Keuangan SPP
            </a>
        </li>
    </ul>
</nav>

<!-- Content Wrapper -->
<div class="d-flex flex-column flex-grow-1 overflow-hidden">
    
    <!-- Topbar -->
    <header class="topbar d-flex justify-content-between align-items-center w-100">
        <div class="d-flex align-items-center gap-2">
            <button class="btn d-lg-none border-0 fs-4 text-dark"><i class="bi bi-list"></i></button>
            <!-- Logo RA Darul Falah -->
            <img src="logo.png" alt="Logo RA Darul Falah" width="40" height="40" class="ms-1 ms-md-2 object-fit-contain">
            <h5 class="mb-0 fw-bold text-dark ms-1">RA Darul Falah</h5>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2 border-start ps-3 ms-2">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fw-bold fs-6">Yanah</p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Administrator</p>
                </div>
                <img src="https://ui-avatars.com/api/?name=Yanah&background=0dcaf0&color=fff&rounded=true" alt="User Profile" class="rounded-circle" width="40" height="40">
            </div>
        </div>
    </header>
