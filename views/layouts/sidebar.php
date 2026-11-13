<?php
$currentRoute = trim($_GET['url'] ?? '', '/');
if (empty($currentRoute)) {
    $currentRoute = 'dashboard';
}
$user = Session::user() ?? ['name' => 'Flowzen User', 'email' => 'user@flowzen.com'];
$unreadNotifications = Notification::getUnreadCount(Session::userId() ?? 0);
?>
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <a href="<?= APP_URL ?>/dashboard" class="text-decoration-none d-flex align-items-center gap-2">
                <span class="fs-4 fw-bold text-white font-heading tracking-tight">Flowzen</span>
            </a>
            <span class="brand-badge">RTIH</span>
        </div>
        <p class="small text-muted mb-0" style="font-size: 0.72rem; color: #B9A8D5 !important;">
            Find your focus. Own your workflow.
        </p>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-label">Workspace</div>
        <a href="<?= APP_URL ?>/dashboard" class="sidebar-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i>
            <span>Overview</span>
        </a>
        <a href="<?= APP_URL ?>/tasks" class="sidebar-link <?= str_starts_with($currentRoute, 'tasks') && !str_contains($currentRoute, 'archived') ? 'active' : '' ?>">
            <i data-lucide="check-square" style="width: 18px; height: 18px;"></i>
            <span>My Tasks</span>
        </a>
        <a href="<?= APP_URL ?>/kanban" class="sidebar-link <?= $currentRoute === 'kanban' ? 'active' : '' ?>">
            <i data-lucide="trello" style="width: 18px; height: 18px;"></i>
            <span>Kanban Board</span>
        </a>
        <a href="<?= APP_URL ?>/calendar" class="sidebar-link <?= $currentRoute === 'calendar' ? 'active' : '' ?>">
            <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
            <span>Calendar</span>
        </a>

        <div class="nav-section-label mt-3">Insights & History</div>
        <a href="<?= APP_URL ?>/activity" class="sidebar-link <?= $currentRoute === 'activity' ? 'active' : '' ?>">
            <i data-lucide="history" style="width: 18px; height: 18px;"></i>
            <span>Activity Timeline</span>
        </a>
        <a href="<?= APP_URL ?>/notifications" class="sidebar-link <?= $currentRoute === 'notifications' ? 'active' : '' ?>">
            <i data-lucide="bell" style="width: 18px; height: 18px;"></i>
            <span class="flex-grow-1">Notifications</span>
            <?php if ($unreadNotifications > 0): ?>
                <span class="badge bg-citron text-dark rounded-pill px-2 py-1" style="font-size:0.7rem;">
                    <?= $unreadNotifications ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="<?= APP_URL ?>/tasks/archived" class="sidebar-link <?= str_contains($currentRoute, 'archived') ? 'active' : '' ?>">
            <i data-lucide="archive" style="width: 18px; height: 18px;"></i>
            <span>Archived Tasks</span>
        </a>

        <div class="nav-section-label mt-3">Preferences</div>
        <a href="<?= APP_URL ?>/settings" class="sidebar-link <?= $currentRoute === 'settings' ? 'active' : '' ?>">
            <i data-lucide="settings" style="width: 18px; height: 18px;"></i>
            <span>Profile & Settings</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="rounded-circle bg-deep-plum text-citron fw-bold d-flex align-items-center justify-content-center"
                     style="width: 36px; height: 36px; font-size: 0.85rem; flex-shrink: 0; border: 1px solid rgba(217, 243, 106, 0.3);">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div class="text-truncate">
                    <div class="fw-semibold text-white small text-truncate"><?= e($user['name']) ?></div>
                    <div class="text-muted" style="font-size: 0.72rem; color: #A99EAF !important;"><?= e($user['email']) ?></div>
                </div>
            </div>

            <!-- Secure Logout Form -->
            <form action="<?= APP_URL ?>/logout" method="POST" class="m-0 p-0">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-link text-muted p-1 hover-white" title="Sign Out"
                        style="color: #D8D0DC !important; transition: var(--transition);">
                    <i data-lucide="log-out" style="width: 18px; height: 18px;"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
