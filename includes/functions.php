<?php
/**
 * Spendly - Shared Helper Functions
 */

/**
 * Escapes HTML output to prevent Cross-Site Scripting (XSS).
 *
 * @param mixed $value
 * @return string
 */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Formats a monetary amount into Indonesian Rupiah (IDR).
 * Example: 150000 -> "Rp 150.000"
 *
 * @param float|int|string|null $amount
 * @return string
 */
function format_currency($amount): string
{
    $num = (float) ($amount ?? 0);
    return 'Rp ' . number_format($num, 0, ',', '.');
}

/**
 * Formats a date string into a clean, readable format.
 * Example: "2026-11-01" -> "01 Nov 2026"
 *
 * @param string|null $dateString
 * @param string $format
 * @return string
 */
function format_date(?string $dateString, string $format = 'd M Y'): string
{
    if (empty($dateString)) {
        return '-';
    }
    $timestamp = strtotime($dateString);
    if ($timestamp === false) {
        return (string) $dateString;
    }
    return date($format, $timestamp);
}

/**
 * Safe redirect to a URL.
 *
 * @param string $url
 * @return void
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Stores a flash message for the next request.
 *
 * @param string $type ('success', 'danger', 'warning', 'info')
 * @param string $message
 * @return void
 */
function set_flash_message(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_messages'][] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Retrieves and clears all pending flash messages.
 *
 * @return array
 */
function get_flash_messages(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Generates an application base URL or resolves relative paths.
 * Works seamlessly whether hosted at root or in a subfolder like /spendly/.
 *
 * @param string $path
 * @return string
 */
function base_url(string $path = ''): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dirName = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    // If scriptName is at root or dirname is empty / slash
    if ($dirName === '' || $dirName === '.') {
        $base = '';
    } else {
        $base = $dirName;
    }

    $cleanPath = ltrim($path, '/');
    if ($cleanPath === '') {
        return $base === '' ? '/' : $base . '/';
    }

    return ($base === '' ? '' : $base) . '/' . $cleanPath;
}
