<div class="auth-card">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-plum text-citron rounded-circle mb-3"
             style="width: 52px; height: 52px; border: 2px solid var(--color-citron);">
            <i data-lucide="zap" style="width: 26px; height: 26px;"></i>
        </div>
        <h1 class="h3 fw-bold font-heading mb-1 text-plum">Welcome to Flowzen</h1>
        <p class="text-muted small">Find your focus. Own your workflow.</p>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success py-2 px-3 small mb-3"><?= e($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger py-2 px-3 small mb-3"><?= e($flashError) ?></div>
    <?php endif; ?>

    <?php if (!empty($flashInfo)): ?>
        <div class="alert alert-info py-2 px-3 small mb-3"><?= e($flashInfo) ?></div>
    <?php endif; ?>

    <!-- Demo Evaluator Credentials Quick Helper -->
    <div class="p-3 mb-4 rounded-3" style="background: rgba(217, 243, 106, 0.18); border: 1px dashed #B8D648;">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i data-lucide="sparkles" class="text-plum" style="width: 16px; height: 16px;"></i>
            <span class="fw-bold small text-plum">RTIH Assessment Demo Login</span>
        </div>
        <div class="small text-muted font-monospace" style="font-size: 0.78rem;">
            Email: <strong class="text-dark">demo@flowzen.com</strong><br>
            Password: <strong class="text-dark">Password123!</strong>
        </div>
    </div>

    <form action="<?= APP_URL ?>/login" method="POST" novalidate>
        <?= Security::csrfField() ?>

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
            <input type="email"
                   class="form-control <?= isset($validationErrors['email']) ? 'is-invalid' : '' ?>"
                   id="email"
                   name="email"
                   value="<?= old('email') ?>"
                   placeholder="name@example.com"
                   required
                   autofocus>
            <?php if (isset($validationErrors['email'])): ?>
                <div class="invalid-feedback"><?= e($validationErrors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
            </div>
            <input type="password"
                   class="form-control <?= isset($validationErrors['password']) ? 'is-invalid' : '' ?>"
                   id="password"
                   name="password"
                   placeholder="Enter your password"
                   required>
            <?php if (isset($validationErrors['password'])): ?>
                <div class="invalid-feedback"><?= e($validationErrors['password']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-citron w-100 py-2 mb-3">Sign In</button>

        <div class="text-center">
            <span class="text-muted small">Don't have an account?</span>
            <a href="<?= APP_URL ?>/register" class="small fw-bold text-plum text-decoration-none ms-1">Create Account</a>
        </div>
    </form>
</div>
