  </main>
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
