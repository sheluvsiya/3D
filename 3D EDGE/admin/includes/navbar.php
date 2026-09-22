<header class="admin-topbar">
  <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-label="Toggle navigation">
    <i class="bi bi-list"></i>
  </button>

  <div>
    <h1 class="topbar-title mb-0"><?= e($pageTitle) ?></h1>
  </div>

  <div class="topbar-search d-none d-md-block">
    <i class="bi bi-search"></i>
    <input type="text" placeholder="Search this section..." disabled title="Use the search field within each section's table">
  </div>

  <div class="topbar-actions">
    <div class="dropdown">
      <button class="notif-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        <?php if ($totalNotifs > 0): ?>
          <span class="dot"><?= $totalNotifs > 99 ? '99+' : $totalNotifs ?></span>
        <?php endif; ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 260px;">
        <li><h6 class="dropdown-header">Needs attention</h6></li>
        <?php if ($totalNotifs === 0): ?>
          <li><span class="dropdown-item-text text-muted small">You're all caught up.</span></li>
        <?php else: ?>
          <?php if ($notifCounts['pending_quotes'] > 0): ?>
            <li><a class="dropdown-item d-flex justify-content-between" href="/admin/quotes/index.php?status=pending">New quote requests <span class="badge bg-warning-subtle text-warning-emphasis"><?= $notifCounts['pending_quotes'] ?></span></a></li>
          <?php endif; ?>
          <?php if ($notifCounts['pending_orders'] > 0): ?>
            <li><a class="dropdown-item d-flex justify-content-between" href="/admin/orders/index.php?status=pending">Pending orders <span class="badge bg-warning-subtle text-warning-emphasis"><?= $notifCounts['pending_orders'] ?></span></a></li>
          <?php endif; ?>
          <?php if ($notifCounts['pending_reviews'] > 0): ?>
            <li><a class="dropdown-item d-flex justify-content-between" href="/admin/reviews/index.php?status=pending">Reviews awaiting moderation <span class="badge bg-warning-subtle text-warning-emphasis"><?= $notifCounts['pending_reviews'] ?></span></a></li>
          <?php endif; ?>
          <?php if ($notifCounts['new_messages'] > 0): ?>
            <li><a class="dropdown-item d-flex justify-content-between" href="/admin/messages/index.php?status=new">New contact messages <span class="badge bg-warning-subtle text-warning-emphasis"><?= $notifCounts['new_messages'] ?></span></a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
    </div>

    <div class="d-none d-sm-flex align-items-center gap-2">
      <div class="sidebar-footer-avatar" style="width:32px;height:32px;border-radius:50%;background:var(--3de-green-wash);color:var(--3de-green);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;">
        <?= e(strtoupper(substr(CURRENT_ADMIN_NAME, 0, 1))) ?>
      </div>
    </div>
  </div>
</header>
