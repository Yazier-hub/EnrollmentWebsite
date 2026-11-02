<?php
/** Formatting helpers shared by templates. */
final class PageView
{
    public static function value($value, $fallback = 'N/A') {
        if ($value === null) return $fallback;
        if (is_string($value) && trim($value) === '') return $fallback;
        return $value;
    }

    public static function input($name, $id = '', $label = '', $required = false,
                     $placeholder = '', $value = '', $type = 'text',
                     $helpText = '', $hasPattern = false) {
        $idAttr         = $id !== '' ? "id=\"{$id}\"" : '';
        $requiredAttr   = $required ? ' required' : '';
        $requiredMark   = $required ? ' <span class="required">*</span>' : '';
        $isNA           = is_string($value) && strtoupper(trim($value)) === 'N/A';
        $displayValue   = $isNA ? 'N/A' : $value;
        $readonlyAttr   = $isNA ? ' readonly' : '';
        $checkedAttr    = $isNA ? ' checked' : '';
        $patternAttr    = $hasPattern
            ? ' pattern="\d{4}(\s*-\s*\d{4})?" title="Format: YYYY-YYYY (e.g., 2024-2025)"'
            : '';

        $inputId = $id !== '' ? $id : $name;

        echo '<div class="form-group na-group">';
        echo "<label for=\"{$inputId}\">{$label}{$requiredMark}</label>";

        echo '<div class="na-input-wrap">';

        if ($type === 'textarea') {
            echo "<textarea name=\"{$name}\" {$idAttr} rows=\"2\"{$requiredAttr}{$readonlyAttr} "
               . "placeholder=\"" . htmlspecialchars($placeholder) . "\" "
               . "class=\"na-input\" data-na-toggle=\"na_chk_{$inputId}\">"
               . htmlspecialchars($displayValue)
               . "</textarea>";
        } else {
            echo "<input type=\"text\" name=\"{$name}\" {$idAttr}{$requiredAttr}{$readonlyAttr}"
               . "{$patternAttr} "
               . "placeholder=\"" . htmlspecialchars($placeholder) . "\" "
               . "value=\"" . htmlspecialchars($displayValue) . "\" "
               . "class=\"na-input\" data-na-toggle=\"na_chk_{$inputId}\">";
        }

        echo '<label class="na-checkbox" for="na_chk_' . htmlspecialchars($inputId) . '" '
           . 'title="Check to set this field to N/A">'
           . "<input type=\"checkbox\" id=\"na_chk_{$inputId}\" "
           . 'data-action-change="toggle-na" data-input-id="' . self::escape($inputId) . '"'
           . "{$checkedAttr}>"
           . '<span>N/A</span>'
           . '</label>';

        echo '</div>';

        if ($helpText !== '') {
            echo '<div class="help-text">' . htmlspecialchars($helpText) . '</div>';
        }

        echo '</div>';
    }

    public static function escape($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
    public static function timeAgo(string $ts): string {
        $diff = time() - strtotime($ts);
        if ($diff < 60)     return 'just now';
        if ($diff < 3600)   return floor($diff / 60) . 'm ago';
        if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M d, Y', strtotime($ts));
    }

}
