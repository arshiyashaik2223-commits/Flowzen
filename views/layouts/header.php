<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($_COOKIE['flowzen_theme'] ?? 'light', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Security::getCsrfToken() ?>">
    <title><?= e($pageTitle ?? 'Flowzen - Premium Productivity') ?></title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Nocturne Citron Design System -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">

    <!-- Global Application Config for JS -->
    <script>
        window.FLOWZEN_APP_URL = "<?= APP_URL ?>";
    </script>
</head>
<body class="<?= isset($layout) && $layout === 'auth' ? 'auth-body' : 'app-body' ?>">
