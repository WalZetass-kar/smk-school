<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$article = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
}
$categories = $db->query("SELECT * FROM news_categories ORDER BY name ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $title      = trim($_POST['title'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0) ?: null;
        $excerpt    = trim($_POST['excerpt'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $status     = in_array($_POST['status'] ?? '', ['draft', 'publish'], true) ? $_POST['status'] : 'draft';
        $image      = $article['featured_image'] ?? null;

        if ($title === '') { $errors[] = 'Judul wajib diisi.'; }
        if ($content === '') { $errors[] = 'Isi berita wajib diisi.'; }

        $slug = slugify($title);
        $base = $slug;
        $n = 1;
        while (true) {
            $check = $db->prepare("SELECT id FROM news WHERE slug = ? AND id != ?");
            $check->execute([$slug, $article['id'] ?? 0]);
            if (!$check->fetch()) break;
            $slug = $base . '-' . (++$n);
        }

        if (!empty($_FILES['featured_image']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['featured_image'], $uploadError);
            if ($path) { $image = $path; } else { $errors[] = 'Gambar: ' . $uploadError; }
        } elseif (!$article && empty($image)) {
            $errors[] = 'Gambar utama wajib diunggah.';
        }

        if (empty($errors)) {
            $content = cleanHtml($content);
            $publishedAt = $article['published_at'] ?? null;
            if ($status === 'publish' && !$publishedAt) {
                $publishedAt = date('Y-m-d H:i:s');
            }
            if ($article) {
                $db->prepare("UPDATE news SET category_id=?, title=?, slug=?, excerpt=?, content=?, featured_image=?, status=?, published_at=? WHERE id=?")
                   ->execute([$categoryId, $title, $slug, $excerpt, $content, $image, $status, $publishedAt, $article['id']]);
                logActivity('UPDATE_NEWS', 'news', "Memperbarui artikel berita: $title ($status)");
            } else {
                $db->prepare("INSERT INTO news (category_id, title, slug, excerpt, content, featured_image, author_id, status, published_at) VALUES (?,?,?,?,?,?,?,?,?)")
                   ->execute([$categoryId, $title, $slug, $excerpt, $content, $image, $_SESSION['admin_id'], $status, $publishedAt]);
                logActivity('CREATE_NEWS', 'news', "Menerbitkan artikel berita baru: $title ($status)");
            }
            setFlash('success', 'Berita berhasil disimpan.');
            redirect(BASE_URL . '/admin/news/index.php');
        }
        $article = ['title' => $title, 'category_id' => $categoryId, 'excerpt' => $excerpt, 'content' => $content, 'status' => $status, 'featured_image' => $image];
    }
}

$pageTitle = $id ? 'Edit Berita' : 'Tambah Berita';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label small fw-semibold">Judul</label>
        <input type="text" name="title" class="form-control" value="<?= e($article['title'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Kategori</label>
        <select name="category_id" class="form-select">
          <option value="">— Tanpa kategori —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) ($article['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Ringkasan</label>
        <input type="text" name="excerpt" class="form-control" value="<?= e($article['excerpt'] ?? '') ?>" maxlength="255">
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Isi Berita</label>
        <textarea name="content" rows="10" class="form-control wysiwyg-editor" required><?= e($article['content'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Gambar Utama</label>
        <?php if (!empty($article['featured_image'])): ?><img src="<?= e(BASE_URL . '/' . $article['featured_image']) ?>" class="thumb mb-2 d-block" style="width:100px;height:100px;" alt=""><?php endif; ?>
        <input type="file" name="featured_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Status</label>
        <select name="status" class="form-select">
          <option value="draft" <?= ($article['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
          <option value="publish" <?= ($article['status'] ?? '') === 'publish' ? 'selected' : '' ?>>Publish</option>
        </select>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-accent px-4">Simpan</button>
        <a href="index.php" class="btn btn-outline-primary-custom px-4">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
