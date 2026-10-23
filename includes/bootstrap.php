<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
session_start();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function database_error(string $message): never {
    error_log($message);
    http_response_code(503);
    exit('The database is unavailable. Please check the connection settings.');
}

function db() {
    static $connection = null;
    if ($connection !== null) {
        return $connection;
    }
    $path = __DIR__ . '/../config.php';
    if (!is_file($path)) {
        http_response_code(503);
        exit('Database setup needed. Copy config.example.php to config.php, configure MySQL, and import database/schema.sql.');
    }
    $config = require $path;
    // Handle database errors explicitly rather than using exceptions.
    mysqli_report(MYSQLI_REPORT_OFF);
    $connection = mysqli_connect( $config['host'], $config['user'], $config['password'], $config['database'], $config['port'], $config['socket']);
    if (!$connection) {
        database_error(mysqli_connect_error());
    }
    if (!mysqli_set_charset($connection, 'utf8mb4')) {
        database_error(mysqli_error($connection));
    }
    return $connection;
}
// SELECT queries return a result; writes return true. Registration may handle a duplicate email.

function query(string $sql, array $params = [], bool $allow_duplicate = false) {
    $connection = db();
    $statement = mysqli_prepare($connection, $sql);
    if (!$statement) {
        database_error(mysqli_error($connection));
    }
    if ($params) {
        $types = '';
        foreach ($params as $value) {
            $types .= is_int($value) ? 'i' : 's';
        }
        if (!mysqli_stmt_bind_param($statement, $types, ...$params)) {
            database_error(mysqli_stmt_error($statement));
        }
    }
    if (!mysqli_stmt_execute($statement)) {
        $code = mysqli_stmt_errno($statement);
        $message = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        if ($allow_duplicate && $code === 1062) {
            return false;
        }
        database_error($message);
    }
    if (mysqli_stmt_field_count($statement) > 0) {
        $result = mysqli_stmt_get_result($statement);
        if (!$result) {
            database_error(mysqli_stmt_error($statement));
        }
    } else {
        $result = true;
    }
    mysqli_stmt_close($statement);
    return $result;
}

function user(): ?array {
    static $loaded = false, $user = null;
    if (!$loaded) {
        $loaded = true;
        if (isset($_SESSION['user_id'])) {
            $user = mysqli_fetch_assoc(query(
                'SELECT id, name FROM users WHERE id = ? AND active = 1',
                [$_SESSION['user_id']]
            )) ?: null;
        }
    }
    return $user;
}

function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function require_login(): array {
    $u = user();
    if (!$u) {
        redirect('login.php');
    }
    return $u;
}

function csrf(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function token_field(): void {
    echo '<input type="hidden" name="csrf" value="' . e(csrf()) . '">';
}

function check_csrf(): void {
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Form expired. Go back, reload the page, and try again.');
    }
}

function flash(string $message): void {
    $_SESSION['flash'] = $message;
}

function valid_url(string $url): bool {
    return strlen($url) <= 2048 && filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower((string)parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function valid_date(string $date): bool {
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $date)) {
        return false;
    }
    $parts = explode('-', $date);
    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}

