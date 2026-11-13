<div class="container-fluid p-0" style="max-width: 720px;">
    <div class="mb-4 pb-2 border-bottom">
        <h2 class="h3 fw-bold font-heading text-plum mb-1">Profile & Settings</h2>
        <p class="text-muted mb-0 small">
            Manage your personal profile details, security credentials, and workspace preferences.
        </p>
    </div>

    <!-- Profile Information Card -->
    <div class="fz-card mb-4">
        <h3 class="h5 fw-bold font-heading text-plum mb-3">Personal Profile</h3>
        <form action="<?= APP_URL ?>/settings/profile" method="POST" novalidate>
            <?= Security::csrfField() ?>

            <div class="mb-3">
                <label for="name" class="form-label small fw-semibold text-plum">Full Name</label>
                <input type="text"
                       class="form-control <?= isset($validationErrors['name']) ? 'is-invalid' : '' ?>"
                       id="name"
                       name="name"
                       value="<?= old('name', $user['name'] ?? '') ?>"
                       required>
                <?php if (isset($validationErrors['name'])): ?>
                    <div class="invalid-feedback"><?= e($validationErrors['name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="email" class="form-label small fw-semibold text-plum">Email Address</label>
                <input type="email"
                       class="form-control bg-porcelain text-muted"
                       id="email"
                       value="<?= e($user['email'] ?? '') ?>"
                       readonly
                       disabled>
                <div class="form-text" style="font-size: 0.75rem;">Account email cannot be modified once registered.</div>
            </div>

            <h4 class="h6 fw-bold font-heading text-plum mb-3 pt-2 border-top">Change Password</h4>

            <div class="mb-3">
                <label for="new_password" class="form-label small fw-semibold text-plum">
                    New Password <span class="text-muted fw-normal">(Leave blank to keep current)</span>
                </label>
                <input type="password"
                       class="form-control <?= isset($validationErrors['new_password']) ? 'is-invalid' : '' ?>"
                       id="new_password"
                       name="new_password"
                       placeholder="Enter new password (min 8 chars)">
                <?php if (isset($validationErrors['new_password'])): ?>
                    <div class="invalid-feedback"><?= e($validationErrors['new_password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label small fw-semibold text-plum">Confirm New Password</label>
                <input type="password"
                       class="form-control <?= isset($validationErrors['confirm_password']) ? 'is-invalid' : '' ?>"
                       id="confirm_password"
                       name="confirm_password"
                       placeholder="Re-type new password">
                <?php if (isset($validationErrors['confirm_password'])): ?>
                    <div class="invalid-feedback"><?= e($validationErrors['confirm_password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-citron px-4">Update Profile</button>
            </div>
        </form>
    </div>

    <!-- Appearance & Theme Card -->
    <div class="fz-card mb-4">
        <h3 class="h5 fw-bold font-heading text-plum mb-2">Appearance & Theme</h3>
        <p class="text-muted small mb-3">Select your visual theme preference for Flowzen.</p>

        <?php $currentTheme = $_COOKIE['flowzen_theme'] ?? 'light'; ?>
        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-porcelain border">
            <div class="d-flex align-items-center gap-3">
                <i data-lucide="<?= $currentTheme === 'dark' ? 'moon' : 'sun' ?>" style="width: 24px; height: 24px; color: var(--color-plum);"></i>
                <div>
                    <div class="fw-bold text-plum small">Current Theme: <?= ucfirst($currentTheme) ?> Mode</div>
                    <div class="small text-muted" style="font-size:0.75rem;">Switch between Nocturne Citron light & dark palettes</div>
                </div>
            </div>
            <form action="<?= APP_URL ?>/settings/theme" method="POST" class="m-0">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-outline-plum btn-sm">
                    Switch to <?= $currentTheme === 'dark' ? 'Light' : 'Dark' ?>
                </button>
            </form>
        </div>
    </div>
</div>
