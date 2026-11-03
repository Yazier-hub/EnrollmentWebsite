<?php
/** @var string $pageTitle */
/** @var PageAssets $pageAssets */
$pageTitle = $pageTitle ?? 'BCP Enrollment';
$baseUrl = PageAssets::baseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- BCP logo as favicon + apple touch icon -->
    <link rel="icon" type="image/png" href="<?php echo $baseUrl; ?>assets/bcp-logo.png">
    <link rel="apple-touch-icon" href="<?php echo $baseUrl; ?>assets/bcp-logo.png">
    <!-- Stylesheets -->
    <?php foreach (($pageAssets ?? new PageAssets('dashboard-overview'))->styles() as $stylesheet): ?>
        <link rel="stylesheet" href="<?= PageView::escape(PageAssets::url($stylesheet)) ?>">
    <?php endforeach; ?>

    <!-- Fonts + Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<!-- Shared header clock -->

<header class="top-bar">
    <button type="button" class="hamburger" aria-label="Toggle navigation" aria-controls="enrollment-sidebar" aria-expanded="true">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
    </button>
    <div class="realtime" id="realtimeClock" aria-live="polite">
        --:--
    </div>
</header>
