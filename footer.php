    </div> <!-- End Content Wrapper -->
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($_GET['status']) && strpos($_GET['status'], 'success') !== false): ?>
    <div id="toastNotif" class="position-fixed top-0 end-0 p-3 mt-5" style="z-index: 9999">
        <div class="toast align-items-center text-white bg-success border-0 show shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex p-1">
                <div class="toast-body fw-bold" style="font-size: 1rem;">
                    <i class="bi bi-check-circle-fill me-2"></i> 
                    <?php 
                        $status = $_GET['status'];
                        if($status == 'success_add') echo 'Data berhasil disimpan!';
                        elseif($status == 'success_edit') echo 'Data berhasil diperbarui!';
                        elseif($status == 'success_delete') echo 'Data berhasil dihapus!';
                        elseif($status == 'success_absen') echo 'Absensi berhasil dicatat!';
                        elseif($status == 'success_pay') echo 'Pembayaran berhasil diproses!';
                        else echo 'Tindakan berhasil!';
                    ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
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
</body>
</html>
