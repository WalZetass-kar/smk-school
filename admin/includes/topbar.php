<?php
/**
 * admin/includes/topbar.php
 * Opens .admin-content + renders the topbar. Expects $pageTitle to be set.
 * Every admin page must close the wrapper itself with </main></div> before
 * requiring footer.php (see any admin module's index.php for the pattern).
 */
?>
<div class="admin-content">
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
        <i class="bi bi-list"></i>
      </button>
      <h1 class="h5 mb-0 text-primary"><?= e($pageTitle ?? 'Dashboard') ?></h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="small text-muted d-none d-sm-inline"><?= e($_SESSION['admin_name'] ?? '') ?></span>
      <span class="badge text-bg-light border"><?= e(ucfirst($_SESSION['admin_role'] ?? '')) ?></span>
    </div>
  </div>
  <main class="admin-main">
