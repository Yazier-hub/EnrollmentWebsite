<?php

final class PageView
{
    public static function render(string $name, array $data): void
    {
        if (!preg_match('/^[a-z-]+$/', $name)) {
            throw new InvalidArgumentException('Invalid view name.');
        }
        $file = __DIR__ . '/../views/' . $name . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('View not found.');
        }
        $e = [self::class, 'escape'];
        extract($data, EXTR_SKIP);
        require $file;
    }

    public static function escape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function json($value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
            | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    }
}
