<!DOCTYPE html>
<html lang="<?= \App\Core\Language::current() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('app_name') ?> - <?= __('app_tagline') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- 👇 INSERT IT HERE — right before </head> 👇 -->
    <?php if (str_contains($_SERVER['REQUEST_URI'], '/chat') || str_contains($_SERVER['REQUEST_URI'], '/feed')): ?>
        <script>window.__SAIP_LANG__ = '<?= \App\Core\Language::current() ?>';</script>
        <script src="<?= BASE_URL ?>/js/chat.js" defer></script>
    <?php endif; ?>
    <!-- 👆 END INSERT 👆 -->
     <?php if (str_contains($_SERVER['REQUEST_URI'], '/marketplace')): ?>
    <script src="<?= BASE_URL ?>/js/marketplace.js" defer></script>
<?php endif; ?>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js').catch(() => {});
    });
}
</script>

</head>
<body>
    <div class="app-container">