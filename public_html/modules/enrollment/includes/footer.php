<?php
/** @var PageAssets $pageAssets */
?>
<div>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> School Management System. All rights reserved.</p>
    </footer>
</div>
    <?php foreach (['dropdown', 'hamburger', 'tab-content', 'realtime'] as $script): ?>
        <script type="module" src="<?= PageView::escape(PageAssets::url('js/' . $script . '.js')) ?>"></script>
    <?php endforeach; ?>
    <?php foreach (($pageAssets ?? new PageAssets('dashboard-overview'))->scripts() as $scriptUrl): ?>
        <script defer src="<?= PageView::escape($scriptUrl) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
