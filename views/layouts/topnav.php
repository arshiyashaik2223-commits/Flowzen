<?php
$unreadCount = Notification::getUnreadCount(Session::userId() ?? 0);
$theme = $_COOKIE['flowzen_theme'] ?? 'light';
?>
<header class="app-topnav d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-outline-plum d-lg-none p-2" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
            <i data-lucide="menu" style="width: 20px; height: 20px;"></i>
        </button>
        <div>
            <h1 class="h5 mb-0 fw-bold font-heading text-plum"><?= e($pageTitle ?? 'Flowzen Workspace') ?></h1>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <!-- Quick New Task Button -->
        <a href="<?= APP_URL ?>/tasks/create" class="btn btn-citron btn-sm d-flex align-items-center gap-1 px-3 py-2">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
            <span class="d-none d-sm-inline">New Task</span>
        </a>

        <!-- Notifications Bell -->
        <a href="<?= APP_URL ?>/notifications" class="btn btn-outline-plum btn-sm position-relative p-2" title="Notifications">
            <i data-lucide="bell" style="width: 18px; height: 18px;"></i>
            <?php if ($unreadCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-coral border border-light rounded-circle">
                    <span class="visually-hidden">New alerts</span>
                </span>
            <?php endif; ?>
        </a>

        <!-- Dark / Light Theme Toggle -->
        <form action="<?= APP_URL ?>/settings/theme" method="POST" class="m-0 p-0">
            <?= Security::csrfField() ?>
            <button type="submit" class="btn btn-outline-plum btn-sm p-2" title="Switch Theme (<?= $theme === 'dark' ? 'Light Mode' : 'Dark Mode' ?>)">
                <i data-lucide="<?= $theme === 'dark' ? 'sun' : 'moon' ?>" style="width: 18px; height: 18px;"></i>
            </button>
        </form>
    </div>
</header>

<!-- Global Flash Alert Notifications Container -->
<div class="px-3 px-md-4 pt-3">
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert"
             style="background-color: rgba(217, 243, 106, 0.35); color: #2A3603; border-left: 4px solid var(--color-citron) !important;">
            <i data-lucide="check-circle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
            <div class="flex-grow-1 fw-medium"><?= e($flashSuccess) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert"
             style="background-color: rgba(230, 141, 120, 0.3); color: #5C190D; border-left: 4px solid var(--color-coral) !important;">
            <i data-lucide="alert-circle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
            <div class="flex-grow-1 fw-medium"><?= e($flashError) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashWarning): ?>
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i data-lucide="alert-triangle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
            <div class="flex-grow-1 fw-medium"><?= e($flashWarning) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashInfo): ?>
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i data-lucide="info" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
            <div class="flex-grow-1 fw-medium"><?= e($flashInfo) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>
