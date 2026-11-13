<div class="container-fluid p-0" style="max-width: 720px;">
    <!-- Breadcrumb / Header -->
    <div class="mb-4">
        <a href="<?= APP_URL ?>/tasks" class="small text-muted text-decoration-none d-inline-flex align-items-center gap-1 mb-2">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to My Tasks</span>
        </a>
        <h2 class="h3 fw-bold font-heading text-plum mb-1">Create New Task</h2>
        <p class="text-muted small">Add a clear objective, set a priority, and establish a deadline.</p>
    </div>

    <!-- Form Card -->
    <div class="fz-card">
        <form action="<?= APP_URL ?>/tasks/store" method="POST" novalidate>
            <?= Security::csrfField() ?>

            <!-- Title -->
            <div class="mb-3">
                <label for="title" class="form-label small fw-semibold text-plum">
                    Task Title <span class="text-coral">*</span>
                </label>
                <input type="text"
                       class="form-control <?= isset($validationErrors['title']) ? 'is-invalid' : '' ?>"
                       id="title"
                       name="title"
                       value="<?= old('title') ?>"
                       placeholder="e.g. Conduct user research interview"
                       required
                       autofocus>
                <?php if (isset($validationErrors['title'])): ?>
                    <div class="invalid-feedback"><?= e($validationErrors['title']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label small fw-semibold text-plum">
                    Description <span class="text-muted fw-normal">(Optional)</span>
                </label>
                <textarea class="form-control"
                          id="description"
                          name="description"
                          rows="4"
                          placeholder="Provide details, acceptance criteria, or relevant context..."><?= old('description') ?></textarea>
            </div>

            <!-- Status & Priority Row -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="status" class="form-label small fw-semibold text-plum">Initial Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending" <?= old('status', 'pending') === 'pending' ? 'selected' : '' ?>>Pending (To Do)</option>
                        <option value="in_progress" <?= old('status') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="priority" class="form-label small fw-semibold text-plum">Priority Level</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="low" <?= old('priority') === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= old('priority', 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>High</option>
                    </select>
                </div>
            </div>

            <!-- Deadline -->
            <div class="mb-4">
                <label for="due_date" class="form-label small fw-semibold text-plum">
                    Due Date <span class="text-muted fw-normal">(Optional)</span>
                </label>
                <input type="date"
                       class="form-control <?= isset($validationErrors['due_date']) ? 'is-invalid' : '' ?>"
                       id="due_date"
                       name="due_date"
                       value="<?= old('due_date') ?>">
                <?php if (isset($validationErrors['due_date'])): ?>
                    <div class="invalid-feedback"><?= e($validationErrors['due_date']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                <a href="<?= APP_URL ?>/tasks" class="btn btn-outline-plum px-4">Cancel</a>
                <button type="submit" class="btn btn-citron px-4">Save Task</button>
            </div>
        </form>
    </div>
</div>
