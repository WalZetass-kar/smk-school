<?php
/**
 * admin/includes/topbar.php
 * Opens .admin-content + renders the topbar. Expects $pageTitle to be set.
 */
$currentUserPhoto = null;
if (!empty($_SESSION['admin_id'])) {
    $dbTop = getDB();
    $uStmt = $dbTop->prepare("SELECT photo FROM users WHERE id = ?");
    $uStmt->execute([$_SESSION['admin_id']]);
    $currentUserPhoto = $uStmt->fetchColumn() ?: null;
}
?>
<div class="admin-content" id="adminContent">
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <!-- Mobile-only Burger Menu Toggle Button (Desktop has button inside sidebar) -->
      <button class="btn btn-sm btn-light border p-0 d-inline-flex d-lg-none align-items-center justify-content-center shadow-none rounded-3" id="sidebarToggleBtn" type="button" title="Buka Menu" style="width:38px;height:38px;">
        <i class="bi bi-list fs-4 text-primary"></i>
      </button>
      <h1 class="h5 mb-0 fw-bold text-primary"><?= e($pageTitle ?? 'Dashboard') ?></h1>
    </div>

    <!-- Right Side User Dropdown -->
    <div class="d-flex align-items-center gap-3">
      <a href="<?= e(BASE_URL) ?>/" target="_blank" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1" title="Lihat Website">
        <i class="bi bi-box-arrow-up-right"></i> <span class="small">Lihat Website</span>
      </a>

      <div class="dropdown">
        <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2 p-1 pe-3 rounded-pill shadow-none" type="button" id="adminUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <?php if (!empty($currentUserPhoto) && file_exists(BASE_PATH . '/' . $currentUserPhoto)): ?>
            <img src="<?= e(BASE_URL . '/' . $currentUserPhoto) ?>" alt="" class="rounded-circle" style="width:28px;height:28px;object-fit:cover;">
          <?php else: ?>
            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
              <i class="bi bi-person-fill" style="font-size:0.9rem;"></i>
            </div>
          <?php endif; ?>
          <span class="small fw-semibold text-dark d-none d-sm-inline"><?= e($_SESSION['admin_name'] ?? 'Admin') ?></span>
          <span class="badge <?= ($_SESSION['admin_role'] ?? '') === 'superadmin' ? 'bg-warning text-dark' : 'bg-primary' ?>" style="font-size:0.68rem;">
            <?= e(ucfirst($_SESSION['admin_role'] ?? '')) ?>
          </span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2" aria-labelledby="adminUserDropdown" style="min-width: 220px;">
          <li class="px-3 py-2 border-bottom">
            <div class="fw-bold small text-dark"><?= e($_SESSION['admin_name'] ?? '') ?></div>
            <div class="text-muted" style="font-size:0.75rem;">@<?= e($_SESSION['admin_username'] ?? '') ?> &middot; <?= e(ucfirst($_SESSION['admin_role'] ?? '')) ?></div>
          </li>
          <li><a class="dropdown-item py-2 small" href="<?= e(BASE_URL) ?>/admin/profile-admin.php"><i class="bi bi-person-gear me-2 text-primary"></i>Profil &amp; Keamanan</a></li>
          <?php if (isSuperAdmin()): ?>
            <li><a class="dropdown-item py-2 small" href="<?= e(BASE_URL) ?>/admin/users/index.php"><i class="bi bi-people-fill me-2 text-warning"></i>Manajemen Admin</a></li>
            <li><a class="dropdown-item py-2 small" href="<?= e(BASE_URL) ?>/admin/backup.php"><i class="bi bi-database-down me-2 text-info"></i>Backup Database</a></li>
          <?php endif; ?>
          <li><hr class="dropdown-divider my-1"></li>
          <li><a class="dropdown-item py-2 small text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal"><i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)</a></li>
        </ul>
      </div>
    </div>
  </div>
  <main class="admin-main">
