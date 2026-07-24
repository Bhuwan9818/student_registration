<?php
/**
 * Shared helpers for the pixel-accurate printable admission forms.
 * Include this in every admission-form.php before rendering.
 */

/**
 * Renders a fixed number of individual bordered character boxes
 * (the "□□□□□□□□" style fields used on paper forms), filling them
 * left-to-right with the uppercase characters of $value.
 */
function charBoxes(string $value = null, int $count = 20, string $extraClass = ''): string
{
    $value = strtoupper((string)$value);
    $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
    $html = '<div class="char-boxes ' . htmlspecialchars($extraClass) . '">';
    for ($i = 0; $i < $count; $i++) {
        $c = $chars[$i] ?? '';
        $html .= '<span class="char-box">' . htmlspecialchars($c) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

/** Escapes a value for safe HTML output, returning '' for null. */
function v($value = null): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Renders a checkbox square that shows a tick mark when $checked is true.
 */
function checkBox(bool $checked = false): string
{
    return '<span class="tick-box">' . ($checked ? '&#10003;' : '') . '</span>';
}
