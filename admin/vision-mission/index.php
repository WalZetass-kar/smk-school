<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$vm = $db->query("SELECT * FROM vision_mission ORDER BY id LIMIT 1")->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $vision  = trim($_POST['vision'] ?? '');
        $mission = trim($_POST['mission'] ?? '');
        $goals   = trim($_POST['goals'] ?? '');
        if ($vision === '') { $errors[] = 'Visi wajib diisi.'; }
        if ($mission === '') { $errors[] = 'Misi wajib diisi.'; }

        if (empty($errors)) {
            if ($vm) {
                $db->prepare("UPDATE vision_mission SET vision=?, mission=?, goals=? WHERE id=?")->execute([$vision, $mission, $goals, $vm['id']]);
            } else {
                $db->prepare("INSERT INTO vision_mission (vision, mission, goals) VALUES (?,?,?)")->execute([$vision, $mission, $goals]);
            }
            setFlash('success', 'Visi & misi berhasil disimpan.');
            redirect(BASE_URL . '/admin/vision-mission/index.php');
        }
        $vm = ['vision' => $vision, 'mission' => $mission, 'goals' => $goals];
    }
}

$pageTitle = 'Visi & Misi';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="mb-3">
      <label class="form-label small fw-semibold">Visi</label>
      <textarea name="vision" rows="3" class="form-control wysiwyg-editor" required><?= e($vm['vision'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">Misi</label>
      <textarea name="mission" rows="6" class="form-control wysiwyg-editor" required><?= e($vm['mission'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">Tujuan Sekolah <span class="text-muted fw-normal">(opsional)</span></label>
      <textarea name="goals" rows="3" class="form-control wysiwyg-editor"><?= e($vm['goals'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn btn-accent px-4">Simpan Perubahan</button>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
