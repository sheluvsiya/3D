<?php
/**
 * Sidebar navigation.
 * Included by header.php. Uses $activeNav to highlight the current
 * section and $notifCounts (from header.php) for badge counts.
 */

function nav_link(string $key, string $href, string $icon, string $label, string $activeNav, int $count = 0): void
{
    $active = $key === $activeNav ? ' active' : '';
    echo '<a href="' . e($href) . '" class="sidebar-link' . $active . '">';
    echo '<i class="bi ' . $icon . '"></i><span>' . e($label) . '</span>';
    if ($count > 0) {
        echo '<span class="badge-count">' . $count . '</span>';
    }
    echo '</a>';
}
?>
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <img src="/admin/assets/images/logo.png" alt="3D Edge">
  </div>

  <nav class="sidebar-nav">
    <?php nav_link('dashboard', '/admin/index.php', 'bi-grid-1x2', 'Dashboard', $activeNav); ?>

    <div class="sidebar-group-label">Catalogue</div>
    <?php nav_link('products', '/admin/products/index.php', 'bi-box-seam', 'Products', $activeNav); ?>
    <?php nav_link('services', '/admin/services/index.php', 'bi-tools', 'Services', $activeNav); ?>
    <?php nav_link('materials', '/admin/materials/index.php', 'bi-layers', 'Materials', $activeNav); ?>

    <div class="sidebar-group-label">Sales</div>
    <?php nav_link('quotes', '/admin/quotes/index.php', 'bi-file-earmark-text', 'Quote Requests', $activeNav, $notifCounts['pending_quotes']); ?>
    <?php nav_link('orders', '/admin/orders/index.php', 'bi-bag-check', 'Orders', $activeNav, $notifCounts['pending_orders']); ?>

    <div class="sidebar-group-label">Customers</div>
    <?php nav_link('customers', '/admin/customers/index.php', 'bi-people', 'Customers', $activeNav); ?>
    <?php nav_link('reviews', '/admin/reviews/index.php', 'bi-star', 'Reviews', $activeNav, $notifCounts['pending_reviews']); ?>
    <?php nav_link('messages', '/admin/messages/index.php', 'bi-envelope', 'Contact Messages', $activeNav, $notifCounts['new_messages']); ?>

    <div class="sidebar-group-label">Content</div>
    <?php nav_link('gallery', '/admin/gallery/index.php', 'bi-images', 'Project Gallery', $activeNav); ?>
    <?php nav_link('blog', '/admin/blog/index.php', 'bi-newspaper', 'Blog / News', $activeNav); ?>
    <?php nav_link('faqs', '/admin/faqs/index.php', 'bi-question-circle', 'FAQs', $activeNav); ?>
    <?php nav_link('content', '/admin/content/index.php', 'bi-file-richtext', 'Website Content', $activeNav); ?>

    <div class="sidebar-group-label">System</div>
    <?php nav_link('settings', '/admin/settings/index.php', 'bi-gear', 'Settings', $activeNav); ?>
  </nav>

  <div class="sidebar-footer">
    <div class="avatar"><?= e(strtoupper(substr(CURRENT_ADMIN_NAME, 0, 1))) ?></div>
    <div>
      <div class="name"><?= e(CURRENT_ADMIN_NAME) ?></div>
      <div class="role">Administrator</div>
    </div>
    <button type="button" class="logout-btn" title="Logout (placeholder — auth not yet implemented)" disabled>
      <i class="bi bi-box-arrow-right"></i>
    </button>
  </div>
</aside>
