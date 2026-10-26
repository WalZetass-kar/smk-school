  </main>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-body p-4 text-center">
        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
          <i class="bi bi-box-arrow-right fs-2"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1" id="logoutConfirmModalLabel">Konfirmasi Keluar</h5>
        <p class="text-muted small mb-4">Apakah Anda yakin ingin mengakhiri sesi dan keluar dari panel admin?</p>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light border w-50 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">Batal</button>
          <form method="post" action="<?= e(BASE_URL) ?>/admin/logout.php" class="w-50 m-0">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold rounded-3">Ya, Keluar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Universal Action / Delete / Preset Confirmation Modal -->
<div class="modal fade" id="universalConfirmModal" tabindex="-1" aria-labelledby="universalConfirmTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-body p-4 text-center">
        <div id="universalConfirmIconWrap" class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
          <i id="universalConfirmIcon" class="bi bi-trash3-fill fs-2"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2" id="universalConfirmTitle">Konfirmasi Tindakan</h5>
        <p class="text-muted small mb-4" id="universalConfirmMessage" style="line-height: 1.55;">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light border w-50 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i> Batal
          </button>
          <button type="button" class="btn btn-danger w-50 py-2 fw-semibold rounded-3 d-inline-flex align-items-center justify-content-center gap-1" id="universalConfirmBtn">
            <i class="bi bi-check-lg"></i> <span id="universalConfirmBtnText">Ya, Lanjutkan</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= e(ASSETS_URL . '/vendor/jquery/jquery.min.js') ?>"></script>
<script src="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= e(ASSETS_URL . '/vendor/summernote/summernote-lite.min.js') ?>"></script>
<script src="<?= e(ASSETS_URL . '/js/main.js') ?>"></script>
<script>
// ==========================================
// Admin Sidebar Toggle (Mini Icon / Full Mode)
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
  const sidebarToggleInside = document.getElementById('sidebarToggleInsideBtn');
  const sidebarToggleTop    = document.getElementById('sidebarToggleBtn');
  const sidebarCloseBtn     = document.getElementById('sidebarCloseBtn');
  const adminSidebar        = document.getElementById('adminSidebar');
  const sidebarBackdrop     = document.getElementById('sidebarBackdrop');

  // Function to toggle sidebar mode
  function handleSidebarToggle(e) {
    if (e) e.preventDefault();
    if (window.innerWidth >= 992) {
      document.body.classList.toggle('sidebar-collapsed');
      localStorage.setItem('admin_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed'));
    } else {
      adminSidebar.classList.toggle('show');
      if (sidebarBackdrop) {
        sidebarBackdrop.classList.toggle('show');
      }
    }
  }

  // Restore desktop collapsed state from localStorage
  if (window.innerWidth >= 992 && localStorage.getItem('admin_sidebar_collapsed') === 'true') {
    document.body.classList.add('sidebar-collapsed');
  }

  if (sidebarToggleInside) {
    sidebarToggleInside.addEventListener('click', handleSidebarToggle);
  }

  if (sidebarToggleTop) {
    sidebarToggleTop.addEventListener('click', handleSidebarToggle);
  }

  // Close Button on Mobile
  if (sidebarCloseBtn) {
    sidebarCloseBtn.addEventListener('click', function(e) {
      e.preventDefault();
      adminSidebar.classList.remove('show');
      if (sidebarBackdrop) {
        sidebarBackdrop.classList.remove('show');
      }
    });
  }

  // Backdrop Click
  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', function() {
      adminSidebar.classList.remove('show');
      sidebarBackdrop.classList.remove('show');
    });
  }

  // Close on Escape Key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      adminSidebar.classList.remove('show');
      if (sidebarBackdrop) {
        sidebarBackdrop.classList.remove('show');
      }
    }
  });

  // Summernote WYSIWYG
  if ($('.wysiwyg-editor').length) {
    $('.wysiwyg-editor').summernote({
      placeholder: 'Tulis konten lengkap di sini...',
      tabsize: 2,
      height: 280,
      toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
        ['fontname', ['fontname']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['insert', ['link', 'picture', 'video', 'hr']],
        ['view', ['fullscreen', 'codeview', 'help']]
      ]
    });
  }
});
</script>
</body>
</html>
