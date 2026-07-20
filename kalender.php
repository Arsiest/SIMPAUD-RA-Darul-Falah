<?php 
require_once 'koneksi.php';
include 'header.php'; 
include 'sidebar.php'; 
?>
<style>
/* Calendar Specific Styles */
.cal-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    border-radius: 8px;
    margin: 2px;
    cursor: pointer;
    transition: background-color 0.2s;
}
.cal-day.hover-eff:hover {
    background-color: #f8f9fa;
}
/* Legend Colors (Sesuai dengan PDF Kaldik sebelumnya) */
.bg-pengenalan { background-color: #90ee90 !important; color: #000 !important; } 
.bg-asas { background-color: #008000 !important; color: white !important; } 
.bg-raport { background-color: #ff00ff !important; color: white !important; } 
.bg-libur-sem { background-color: #ffff00 !important; color: #000 !important; } 
.bg-idul-fitri { background-color: #ffa500 !important; color: white !important; } 
.bg-ujian { background-color: #ffcccb !important; color: #000 !important; } 
.bg-libur-nas { background-color: #ff0000 !important; color: white !important; } 
.text-danger-custom { color: #dc3545 !important; }
</style>
<!-- Main Content -->
<main class="main-content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h5 class="fw-bold mb-1 text-dark">Pedoman Kalender Pendidikan Madrasah</h5>
            <h6 class="text-muted mb-0">Tahun Ajaran 2026/2027</h6>
        </div>
    </div>
    <!-- KETERANGAN (LEGEND) -->
    <div class="custom-card p-4 mb-4 border">
        <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Keterangan</h6>
        <div class="row g-3" style="font-size: 0.85rem;">
            <div class="col-md-6 col-lg-3 d-flex align-items-center">
                <div class="bg-pengenalan rounded shadow-sm me-2" style="width:18px;height:18px;"></div> Pengenalan Lingkungan
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-center">
                <div class="bg-asas rounded shadow-sm me-2" style="width:18px;height:18px;"></div> Asesmen Sumatif (ASAS)
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-center">
                <div class="bg-raport rounded shadow-sm me-2" style="width:18px;height:18px;"></div> Penyerahan Raport
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-center">
                <div class="bg-libur-sem rounded shadow-sm me-2" style="width:18px;height:18px;"></div> Libur Semester
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-center">
                <div class="bg-idul-fitri rounded shadow-sm me-2" style="width:18px;height:18px;"></div> Libur Idul Fitri 1447 H
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-center">
                <div class="bg-ujian rounded shadow-sm me-2" style="width:18px;height:18px;"></div> Rentang Ujian Madrasah
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-center">
                <div class="bg-libur-nas rounded shadow-sm me-2" style="width:18px;height:18px;"></div> Hari Libur Nasional
            </div>
        </div>
    </div>
    <!-- CALENDAR GRID -->
    <div id="calendar-container" class="row g-4 pb-4">
        <!-- Kalender akan di-render oleh JavaScript -->
    </div>
</main>
<script>
    const academicYear = [
        { id: 'juli', month: 'Juli 2026', startDay: 3, days: 31 }, // startDay: 0=Aha, 1=Sen, 2=Sel, 3=Rab
        { id: 'agustus', month: 'Agustus 2026', startDay: 6, days: 31 },
        { id: 'september', month: 'September 2026', startDay: 2, days: 30 },
        { id: 'oktober', month: 'Oktober 2026', startDay: 4, days: 31 },
        { id: 'november', month: 'November 2026', startDay: 0, days: 30 },
        { id: 'desember', month: 'Desember 2026', startDay: 2, days: 31 },
        { id: 'januari', month: 'Januari 2027', startDay: 5, days: 31 },
        { id: 'februari', month: 'Februari 2027', startDay: 1, days: 28 },
        { id: 'maret', month: 'Maret 2027', startDay: 1, days: 31 },
        { id: 'april', month: 'April 2027', startDay: 4, days: 30 },
        { id: 'mei', month: 'Mei 2027', startDay: 6, days: 31 },
        { id: 'juni', month: 'Juni 2027', startDay: 2, days: 30 }
    ];
    const daysOfWeek = ['Aha', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    const container = document.getElementById('calendar-container');
    const markedDates = {
        'juli': {
            13: 'bg-pengenalan', 14: 'bg-pengenalan', 15: 'bg-pengenalan'
        },
        'agustus': {
            17: 'bg-libur-nas'
        },
        'september': {
            28: 'bg-libur-nas'
        },
        'desember': {
            1: 'bg-asas', 2: 'bg-asas', 3: 'bg-asas', 4: 'bg-asas', 5: 'bg-asas', 7: 'bg-asas',
            18: 'bg-raport',
            21: 'bg-libur-sem', 22: 'bg-libur-sem', 23: 'bg-libur-sem', 24: 'bg-libur-sem', 26: 'bg-libur-sem', 28: 'bg-libur-sem', 29: 'bg-libur-sem', 30: 'bg-libur-sem', 31: 'bg-libur-sem',
            25: 'bg-libur-nas'
        },
        'januari': {
            1: 'bg-libur-nas'
        },
        'februari': {
            8: 'bg-libur-nas'
        },
        'maret': {
            8: 'bg-idul-fitri', 9: 'bg-idul-fitri', 10: 'bg-idul-fitri', 11: 'bg-idul-fitri', 12: 'bg-idul-fitri', 13: 'bg-idul-fitri', 15: 'bg-idul-fitri',
            22: 'bg-ujian', 23: 'bg-ujian', 24: 'bg-ujian', 25: 'bg-ujian', 27: 'bg-ujian'
        },
        'april': {
            1: 'bg-ujian', 2: 'bg-ujian', 3: 'bg-ujian'
        },
        'mei': {
            1: 'bg-libur-nas',
            6: 'bg-libur-nas',
            20: 'bg-libur-nas'
        },
        'juni': {
            1: 'bg-libur-nas',
            7: 'bg-asas', 8: 'bg-asas', 9: 'bg-asas', 10: 'bg-asas', 11: 'bg-asas', 12: 'bg-asas',
            25: 'bg-raport',
            28: 'bg-libur-sem', 29: 'bg-libur-sem', 30: 'bg-libur-sem'
        }
    };
    function renderCalendar() {
        academicYear.forEach(monthData => {
            // Container untuk card bulan (menggunakan grid col Bootstrap)
            const col = document.createElement('div');
            col.className = 'col-12 col-md-6 col-lg-4 col-xl-3';
            const card = document.createElement('div');
            card.className = 'custom-card h-100 p-4 border bg-white';
            
            const title = document.createElement('h6');
            title.className = 'fw-bold text-center mb-3 text-dark border-bottom pb-2';
            title.innerText = monthData.month;
            card.appendChild(title);
            // Container untuk grid hari
            const grid = document.createElement('div');
            // flex-wrap untuk memecah 7 kolom
            grid.className = 'd-flex flex-wrap text-center mt-3';
            // Render Header Hari (Aha, Sen, Sel, dsb)
            daysOfWeek.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.style.width = '14.28%'; // 100% / 7
                dayHeader.className = 'text-muted fw-bold mb-2';
                dayHeader.style.fontSize = '0.75rem';
                dayHeader.innerText = day;
                grid.appendChild(dayHeader);
            });
            // Kosongkan sel untuk hari sebelum startDay
            for (let i = 0; i < monthData.startDay; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.style.width = '14.28%';
                grid.appendChild(emptyCell);
            }
            // Render tanggal
            for (let day = 1; day <= monthData.days; day++) {
                const cellWrapper = document.createElement('div');
                cellWrapper.style.width = '14.28%';
                cellWrapper.className = 'd-flex justify-content-center p-1';
                const dayCell = document.createElement('div');
                dayCell.className = 'cal-day fw-semibold w-100 shadow-sm';
                dayCell.innerText = day;
                const currentDayOfWeek = (monthData.startDay + day - 1) % 7;
                const isSunday = currentDayOfWeek === 0;
                const monthKey = monthData.id;
                
                // Cek warna berdasarkan markedDates JSON di atas
                if (markedDates[monthKey] && markedDates[monthKey][day]) {
                    const colorClass = markedDates[monthKey][day];
                    dayCell.classList.add(colorClass);
                } else {
                    if (isSunday) {
                        dayCell.classList.add('text-danger-custom'); // Warna merah khusus Minggu
                    } else {
                        dayCell.classList.add('text-dark');
                    }
                    dayCell.classList.add('hover-eff'); // efek hover custom (hanya yang tidak diwarnai)
                }
                cellWrapper.appendChild(dayCell);
                grid.appendChild(cellWrapper);
            }
            card.appendChild(grid);
            col.appendChild(card);
            container.appendChild(col);
        });
    }
    // Jalankan fungsi
    renderCalendar();
</script>
<?php include 'footer.php'; ?>