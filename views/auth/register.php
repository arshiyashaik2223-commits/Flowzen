<div class="auth-card">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-plum text-citron rounded-circle mb-3"
             style="width: 52px; height: 52px; border: 2px solid var(--color-citron);">
            <i data-lucide="user-plus" style="width: 26px; height: 26px;"></i>
        </div>
        <h1 class="h3 fw-bold font-heading mb-1 text-plum">Create Flowzen Account</h1>
        <p class="text-muted small">Begin managing your tasks with clarity and precision.</p>
    </div>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger py-2 px-3 small mb-3"><?= e($flashError) ?></div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/register" method="POST" novalidate>
        <?= Security::csrfField() ?>

        <div class="mb-3">
            <label for="name" class="form-label small fw-semibold text-secondary">Full Name</label>
            <input type="text"
                   class="form-control <?= isset($validationErrors['name']) ? 'is-invalid' : '' ?>"
                   id="name"
                   name="name"
                   value="<?= old('name') ?>"
                   placeholder="e.g. Arshiya Parveen"
                   required
                   autofocus>
            <?php if (isset($validationErrors['name'])): ?>
                <div class="invalid-feedback"><?= e($validationErrors['name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
            <input type="email"
                   class="form-control <?= isset($validationErrors['email']) ? 'is-invalid' : '' ?>"
                   id="email"
                   name="email"
                   value="<?= old('email') ?>"
                   placeholder="name@example.com"
                   required>
            <?php if (isset($validationErrors['email'])): ?>
                <div class="invalid-feedback"><?= e($validationErrors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold text-secondary">Password (min 8 characters)</label>
            <input type="password"
                   class="form-control <?= isset($validationErrors['password']) ? 'is-invalid' : '' ?>"
                   id="password"
                   name="password"
                   placeholder="Create a strong password"
                   required>
            <?php if (isset($validationErrors['password'])): ?>
                <div class="invalid-feedback"><?= e($validationErrors['password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="confirm_password" class="form-label small fw-semibold text-secondary">Confirm Password</label>
            <input type="password"
                   class="form-control <?= isset($validationErrors['confirm_password']) ? 'is-invalid' : '' ?>"
                   id="confirm_password"
                   name="confirm_password"
                   placeholder="Re-type your password"
                   required>
            <?php if (isset($validationErrors['confirm_password'])): ?>
                <div class="invalid-feedback"><?= e($validationErrors['confirm_password']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-citron w-100 py-2 mb-3">Create Workspace Account</button>

        <div class="text-center">
            <span class="text-muted small">Already have an account?</span>
            <a href="<?= APP_URL ?>/login" class="small fw-bold text-plum text-decoration-none ms-1">Sign In</a>
        </div>
    </form>
</div>
