<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold font-heading text-plum mb-1">Notification Center</h2>
            <p class="text-muted mb-0 small">
                In-app notifications for approaching deadlines and priority tasks.
            </p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <?php if ($unreadCount > 0): ?>
                <form action="<?= APP_URL ?>/notifications/read-all" method="POST" class="m-0">
                    <?= Security::csrfField() ?>
                    <button type="submit" class="btn btn-outline-plum btn-sm d-flex align-items-center gap-1">
                        <i data-lucide="check-check" style="width: 16px; height: 16px;"></i>
                        <span>Mark All as Read</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="fz-card p-0 overflow-hidden mb-4">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5 text-muted">
                <i data-lucide="bell-off" class="mb-2" style="width: 36px; height: 36px;"></i>
                <h3 class="h5 fw-bold text-plum mb-1">No notifications</h3>
                <p class="small mb-0">You are all caught up! No active deadline alerts or warnings.</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <?php
                        $isUnread = (int)$n['is_read'] === 0;
                        $icon = 'info';
                        $borderClass = 'border-secondary';
                        if ($n['type'] === 'warning') { $icon = 'clock'; $borderClass = 'border-warning'; }
                        elseif ($n['type'] === 'danger') { $icon = 'alert-triangle'; $borderClass = 'border-danger'; }
                        elseif ($n['type'] === 'success') { $icon = 'check-circle'; $borderClass = 'border-success'; }
                    ?>
                    <div class="list-group-item p-3 d-flex justify-content-between align-items-start <?= $isUnread ? 'bg-porcelain' : '' ?>"
                         style="border-left: 4px solid <?= $n['type'] === 'danger' ? 'var(--color-coral)' : ($n['type'] === 'warning' ? 'var(--color-citron)' : 'var(--color-teal)') ?> !important;">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center bg-white shadow-sm mt-1"
                                 style="width: 36px; height: 36px; flex-shrink: 0;">
                                <i data-lucide="<?= $icon ?>" style="width: 18px; height: 18px; color: var(--color-plum);"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h4 class="h6 mb-0 fw-bold text-plum" style="font-size: 0.95rem;"><?= e($n['title']) ?></h4>
                                    <?php if ($isUnread): ?>
                                        <span class="badge bg-citron text-dark rounded-pill" style="font-size:0.65rem;">NEW</span>
                                    <?php endif; ?>
                                </div>
                                <p class="small text-muted mb-1 mt-1"><?= e($n['message']) ?></p>
                                <span class="small text-muted" style="font-size: 0.72rem;">
                                    <?= date('M j, Y g:i A', strtotime($n['created_at'])) ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($isUnread): ?>
                            <form action="<?= APP_URL ?>/notifications/read" method="POST" class="m-0 ms-2">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-plum p-1" title="Mark as read">
                                    <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
