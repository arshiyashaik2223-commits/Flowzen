<div class="container-fluid p-0" style="max-width: 900px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold font-heading text-plum mb-1">Activity Timeline</h2>
            <p class="text-muted mb-0 small">
                Complete audit history of changes, task completions, and security events.
            </p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-outline-plum d-flex align-items-center gap-2">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                <span>Back to Overview</span>
            </a>
        </div>
    </div>

    <div class="fz-card p-4">
        <?php if (empty($activities)): ?>
            <div class="text-center py-5 text-muted">
                <i data-lucide="history" class="mb-2" style="width: 36px; height: 36px;"></i>
                <p class="small mb-0">No recorded activity logs found.</p>
            </div>
        <?php else: ?>
            <div class="timeline position-relative ps-4" style="border-left: 2px solid var(--color-border);">
                <?php foreach ($activities as $act): ?>
                    <?php
                        $icon = 'activity';
                        $colorClass = 'text-plum';
                        if ($act['action'] === 'created') { $icon = 'plus-circle'; $colorClass = 'text-citron'; }
                        elseif ($act['action'] === 'status_changed') { $icon = 'check-circle'; $colorClass = 'text-teal'; }
                        elseif ($act['action'] === 'deleted') { $icon = 'trash-2'; $colorClass = 'text-coral'; }
                        elseif ($act['action'] === 'archived') { $icon = 'archive'; $colorClass = 'text-orchid'; }
                    ?>
                    <div class="mb-4 position-relative">
                        <!-- Dot on timeline -->
                        <div class="position-absolute rounded-circle bg-plum d-flex align-items-center justify-content-center"
                             style="width: 22px; height: 22px; left: -36px; top: 2px; border: 2px solid #FFFCF6;">
                            <i data-lucide="<?= $icon ?>" class="<?= $colorClass ?>" style="width: 12px; height: 12px;"></i>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div>
                                <span class="badge bg-porcelain text-plum text-uppercase me-2" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    <?= str_replace('_', ' ', $act['action']) ?>
                                </span>
                                <span class="fw-semibold text-plum small"><?= e($act['description']) ?></span>
                            </div>
                            <span class="text-muted small mt-1 mt-md-0" style="font-size: 0.75rem;">
                                <?= date('M j, Y — g:i:s A', strtotime($act['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
