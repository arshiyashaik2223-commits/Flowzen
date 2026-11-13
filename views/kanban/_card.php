<?php
$isCompleted = $task['status'] === 'completed';
$isOverdue = !$isCompleted && !empty($task['due_date']) && $task['due_date'] < date('Y-m-d');
?>
<div class="kanban-card"
     draggable="true"
     data-task-id="<?= $task['id'] ?>"
     data-status="<?= $task['status'] ?>">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="badge badge-priority-<?= $task['priority'] ?> rounded-pill px-2 py-1" style="font-size:0.7rem;">
            <?= ucfirst($task['priority']) ?>
        </span>
        <a href="<?= APP_URL ?>/tasks/edit?id=<?= $task['id'] ?>" class="text-muted text-decoration-none" title="Edit">
            <i data-lucide="more-horizontal" style="width: 16px; height: 16px;"></i>
        </a>
    </div>

    <h4 class="h6 fw-bold mb-1 text-plum <?= $isCompleted ? 'text-decoration-line-through text-muted' : '' ?>" style="font-size: 0.95rem;">
        <?= e($task['title']) ?>
    </h4>

    <?php if (!empty($task['description'])): ?>
        <p class="small text-muted mb-2 text-truncate-2" style="font-size: 0.8rem; line-height: 1.4;">
            <?= e($task['description']) ?>
        </p>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top" style="font-size: 0.75rem;">
        <div>
            <?php if (!empty($task['due_date'])): ?>
                <?php if ($isOverdue): ?>
                    <span class="text-coral fw-bold d-flex align-items-center gap-1">
                        <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                        <?= e($task['due_date']) ?>
                    </span>
                <?php else: ?>
                    <span class="text-muted d-flex align-items-center gap-1">
                        <i data-lucide="calendar" style="width: 12px; height: 12px;"></i>
                        <?= e($task['due_date']) ?>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span class="text-muted">No deadline</span>
            <?php endif; ?>
        </div>

        <!-- Accessible status change control for non-drag users -->
        <div>
            <select class="form-select form-select-sm kanban-accessible-select py-0 px-2"
                    style="font-size: 0.72rem; height: 24px;"
                    title="Change status accessibly">
                <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>To: Pending</option>
                <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>To: Active</option>
                <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>To: Done</option>
            </select>
        </div>
    </div>
</div>
