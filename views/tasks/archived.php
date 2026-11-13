<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold font-heading text-plum mb-1">Archived Tasks</h2>
            <p class="text-muted mb-0 small">
                Completed or retired tasks kept safe for historical reference.
            </p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= APP_URL ?>/tasks" class="btn btn-outline-plum d-flex align-items-center gap-2">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                <span>Back to Active Tasks</span>
            </a>
        </div>
    </div>

    <div class="fz-card p-0 overflow-hidden mb-4">
        <?php if (empty($tasks)): ?>
            <div class="text-center py-5">
                <div class="rounded-circle bg-porcelain d-inline-flex align-items-center justify-content-center p-3 mb-3 text-muted">
                    <i data-lucide="archive" style="width: 36px; height: 36px;"></i>
                </div>
                <h3 class="h5 fw-bold text-plum mb-1">Archive is empty</h3>
                <p class="text-muted small mb-0">You have not archived any tasks yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-porcelain border-bottom">
                        <tr class="small text-muted font-heading">
                            <th class="ps-3">Task Details</th>
                            <th>Status When Archived</th>
                            <th>Priority</th>
                            <th>Archived Date</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-plum"><?= e($task['title']) ?></div>
                                    <?php if (!empty($task['description'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 380px;">
                                            <?= e($task['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-status-<?= $task['status'] ?> rounded-pill px-2 py-1 small">
                                        <?= ucwords(str_replace('_', ' ', $task['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-priority-<?= $task['priority'] ?> rounded-pill px-2 py-1 small">
                                        <?= ucfirst($task['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-muted"><?= date('M j, Y g:i A', strtotime($task['archived_at'])) ?></span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Restore Form -->
                                        <form action="<?= APP_URL ?>/tasks/restore" method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                            <button type="submit" class="btn btn-outline-plum btn-sm d-inline-flex align-items-center gap-1" title="Restore to active tasks">
                                                <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i>
                                                <span class="d-none d-md-inline">Restore</span>
                                            </button>
                                        </form>

                                        <!-- Permanent Delete -->
                                        <form action="<?= APP_URL ?>/tasks/delete" method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                            <button type="submit"
                                                    class="btn btn-outline-plum btn-sm text-danger"
                                                    data-confirm="Permanently delete this task? This cannot be undone."
                                                    title="Permanently Delete">
                                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
