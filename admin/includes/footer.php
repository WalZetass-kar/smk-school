  </main>
</div>
<script src="<?= e(ASSETS_URL . '/vendor/jquery/jquery.min.js') ?>"></script>
<script src="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= e(ASSETS_URL . '/vendor/summernote/summernote-lite.min.js') ?>"></script>
<script src="<?= e(ASSETS_URL . '/js/main.js') ?>"></script>
<script>
$(document).ready(function() {
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
