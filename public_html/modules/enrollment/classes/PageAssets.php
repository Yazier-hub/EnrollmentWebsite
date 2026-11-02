<?php
/** Resolves local assets and selects only the current page's styles and scripts. */
final class PageAssets
{
    private string $page;

    public function __construct(string $page)
    {
        if (!preg_match('/^[a-z]+(?:-[a-z]+)*$/', $page)) {
            throw new InvalidArgumentException('Invalid page asset name.');
        }
        $this->page = $page;
    }

    public static function baseUrl(): string
    {
        $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $directory = preg_replace('#/(pages|api|ajax)$#', '', $directory);
        return rtrim($directory === '.' ? '' : $directory, '/') . '/';
    }

    public static function url(string $relativePath): string
    {
        $file = __DIR__ . '/../' . $relativePath;
        $version = is_file($file) ? '?v=' . filemtime($file) : '';
        return self::baseUrl() . $relativePath . $version;
    }

    public function styles(): array
    {
        $styles = ['css/styles.css', 'css/layout/header-extras.css', 'css/layout/sidebar-extras.css'];
        $pageStyle = 'css/pages/' . $this->page . '.css';
        if (is_file(__DIR__ . '/../' . $pageStyle)) $styles[] = $pageStyle;
        $styles[] = 'css/components/page-utilities.css';
        return $styles;
    }

    public function scripts(): array
    {
        $scripts = [self::url('js/page-ui.js')];
        if ($this->page === 'dashboard-overview') {
            $scripts[] = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        }
        $pageScript = 'js/pages/' . $this->page . '.js';
        if (is_file(__DIR__ . '/../' . $pageScript)) $scripts[] = self::url($pageScript);
        return $scripts;
    }
}
