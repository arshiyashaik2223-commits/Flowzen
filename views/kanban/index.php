<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold font-heading text-plum mb-1">Kanban Workflow Board</h2>
            <p class="text-muted mb-0 small">
                Drag cards across columns or use the accessible status selector to advance your workflow.
            </p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="<?= APP_URL ?>/tasks/create" class="btn btn-citron d-flex align-items-center gap-2">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                <span>Add Task</span>
            </a>
        </div>
    </div>

    <!-- 3-Column Kanban Board -->
    <div class="kanban-board-container">
        <!-- 1. PENDING COLUMN -->
        <div class="kanban-column">
            <div class="kanban-column-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-orchid" style="width: 10px; height: 10px;"></span>
                    <h3 class="h6 fw-bold mb-0 text-plum font-heading">Pending</h3>
                </div>
                <span class="badge bg-white text-dark border kanban-count-badge" data-status="pending">
                    <?= count($kanban['pending']) ?>
                </span>
            </div>

            <div class="kanban-cards-wrapper" data-status="pending">
                <?php foreach ($kanban['pending'] as $task): ?>
                    <?php include __DIR__ . '/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 2. IN PROGRESS COLUMN -->
        <div class="kanban-column">
            <div class="kanban-column-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-teal" style="width: 10px; height: 10px;"></span>
                    <h3 class="h6 fw-bold mb-0 text-plum font-heading">In Progress</h3>
                </div>
                <span class="badge bg-white text-dark border kanban-count-badge" data-status="in_progress">
                    <?= count($kanban['in_progress']) ?>
                </span>
            </div>

            <div class="kanban-cards-wrapper" data-status="in_progress">
                <?php foreach ($kanban['in_progress'] as $task): ?>
                    <?php include __DIR__ . '/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. COMPLETED COLUMN -->
        <div class="kanban-column">
            <div class="kanban-column-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-citron" style="width: 10px; height: 10px; border: 1px solid #746B78;"></span>
                    <h3 class="h6 fw-bold mb-0 text-plum font-heading">Completed</h3>
                </div>
                <span class="badge bg-white text-dark border kanban-count-badge" data-status="completed">
                    <?= count($kanban['completed']) ?>
                </span>
            </div>

            <div class="kanban-cards-wrapper" data-status="completed">
                <?php foreach ($kanban['completed'] as $task): ?>
                    <?php include __DIR__ . '/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Load Kanban Interactive Script -->
<script src="<?= APP_URL ?>/assets/js/kanban.js"></script>
