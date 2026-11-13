<div class="container-fluid p-0">
    <!-- Header Title & Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold font-heading text-plum mb-1">My Tasks</h2>
            <p class="text-muted mb-0 small">
                Manage, search, and track all tasks in your personal workspace.
            </p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="<?= APP_URL ?>/tasks/create" class="btn btn-citron d-flex align-items-center gap-2">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                <span>Add Task</span>
            </a>
            <a href="<?= APP_URL ?>/tasks/export" class="btn btn-outline-plum d-flex align-items-center gap-2" title="Export as CSV">
                <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Search, Status & Priority Filter Form -->
    <div class="fz-card mb-4 p-3">
        <form method="GET" action="<?= APP_URL ?>/tasks" class="row g-2 align-items-center">
            <!-- Search Title -->
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                    </span>
                    <input type="text"
                           name="search"
                           class="form-control border-start-0"
                           placeholder="Search tasks by title..."
                           value="<?= e($filters['search']) ?>">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>

            <!-- Priority Filter -->
            <div class="col-6 col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="high" <?= $filters['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="medium" <?= $filters['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="low" <?= $filters['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>

            <!-- Sorting -->
            <div class="col-6 col-md-2">
                <select name="sort" class="form-select">
                    <option value="created_desc" <?= $filters['sort'] === 'created_desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="created_asc" <?= $filters['sort'] === 'created_asc' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="due_asc" <?= $filters['sort'] === 'due_asc' ? 'selected' : '' ?>>Due Date (Earliest)</option>
                    <option value="due_desc" <?= $filters['sort'] === 'due_desc' ? 'selected' : '' ?>>Due Date (Latest)</option>
                    <option value="priority_desc" <?= $filters['sort'] === 'priority_desc' ? 'selected' : '' ?>>Highest Priority</option>
                    <option value="title_asc" <?= $filters['sort'] === 'title_asc' ? 'selected' : '' ?>>Title (A-Z)</option>
                </select>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-plum flex-grow-1">Filter</button>
                <a href="<?= APP_URL ?>/tasks" class="btn btn-outline-plum" title="Reset Filters">
                    <i data-lucide="rotate-ccw" style="width: 16px; height: 16px;"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tasks Listing Table -->
    <div class="fz-card p-0 overflow-hidden mb-4">
        <?php if (empty($tasks)): ?>
            <div class="text-center py-5">
                <div class="rounded-circle bg-porcelain d-inline-flex align-items-center justify-content-center p-3 mb-3 text-muted">
                    <i data-lucide="inbox" style="width: 36px; height: 36px;"></i>
                </div>
                <h3 class="h5 fw-bold text-plum mb-1">No tasks found</h3>
                <p class="text-muted small mb-3">No tasks match your current filter settings or workspace is empty.</p>
                <a href="<?= APP_URL ?>/tasks/create" class="btn btn-citron btn-sm">Create a New Task</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-porcelain border-bottom">
                        <tr class="small text-muted font-heading">
                            <th style="width: 40px;" class="ps-3">Status</th>
                            <th>Task Details</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Created</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <?php
                                $isCompleted = $task['status'] === 'completed';
                                $isOverdue = !$isCompleted && !empty($task['due_date']) && $task['due_date'] < date('Y-m-d');
                            ?>
                            <tr>
                                <!-- Quick Status Toggle Form -->
                                <td class="ps-3">
                                    <form action="<?= APP_URL ?>/tasks/update-status" method="POST" class="m-0">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $isCompleted ? 'pending' : 'completed' ?>">
                                        <button type="submit" class="btn btn-link p-0 text-decoration-none" title="<?= $isCompleted ? 'Mark Pending' : 'Mark Completed' ?>">
                                            <?php if ($isCompleted): ?>
                                                <i data-lucide="check-circle-2" style="width: 22px; height: 22px; color: var(--color-teal);"></i>
                                            <?php else: ?>
                                                <i data-lucide="circle" style="width: 22px; height: 22px; color: var(--color-muted);"></i>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </td>

                                <!-- Title and Description -->
                                <td>
                                    <div class="fw-bold text-plum <?= $isCompleted ? 'text-decoration-line-through text-muted' : '' ?>">
                                        <?= e($task['title']) ?>
                                    </div>
                                    <?php if (!empty($task['description'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 380px;">
                                            <?= e($task['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Priority Badge -->
                                <td>
                                    <span class="badge badge-priority-<?= $task['priority'] ?> rounded-pill px-2 py-1 small">
                                        <?= ucfirst($task['priority']) ?>
                                    </span>
                                </td>

                                <!-- Due Date -->
                                <td>
                                    <?php if (!empty($task['due_date'])): ?>
                                        <?php if ($isOverdue): ?>
                                            <span class="badge badge-priority-high rounded-pill px-2 py-1 small d-inline-flex align-items-center gap-1">
                                                <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                                <?= e($task['due_date']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="small text-secondary"><?= e($task['due_date']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="small text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Created Timestamp -->
                                <td>
                                    <span class="small text-muted"><?= date('M j, Y', strtotime($task['created_at'])) ?></span>
                                </td>

                                <!-- Actions -->
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit -->
                                        <a href="<?= APP_URL ?>/tasks/edit?id=<?= $task['id'] ?>" class="btn btn-outline-plum btn-sm p-1" title="Edit">
                                            <i data-lucide="edit-3" style="width: 15px; height: 15px;"></i>
                                        </a>

                                        <!-- Archive -->
                                        <form action="<?= APP_URL ?>/tasks/archive" method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                            <button type="submit" class="btn btn-outline-plum btn-sm p-1" title="Archive">
                                                <i data-lucide="archive" style="width: 15px; height: 15px;"></i>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form action="<?= APP_URL ?>/tasks/delete" method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                            <button type="submit"
                                                    class="btn btn-outline-plum btn-sm p-1 text-danger"
                                                    data-confirm="Are you sure you want to delete this task? This action cannot be undone."
                                                    title="Delete">
                                                <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
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

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
        <nav class="d-flex justify-content-between align-items-center" aria-label="Tasks pagination">
            <div class="small text-muted">
                Showing <?= count($tasks) ?> of <?= $totalTasks ?> total tasks (Page <?= $currentPage ?> of <?= $totalPages ?>)
            </div>
            <ul class="pagination pagination-sm mb-0">
                <?php
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    $urlPrefix = APP_URL . '/tasks?' . ($queryString ? $queryString . '&' : '') . 'page=';
                ?>
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $urlPrefix . ($currentPage - 1) ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $urlPrefix . $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $urlPrefix . ($currentPage + 1) ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
