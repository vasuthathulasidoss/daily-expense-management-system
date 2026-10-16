<?php
/**
 * Global Utility Functions & Helpers
 * Daily Expense Management System
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookie settings
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

/**
 * Escape HTML output to prevent XSS
 */
function e(?string $string): string
{
    return htmlspecialchars((string)($string ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate and sanitize CSRF token from POST request or die
 */
function check_csrf(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verify_csrf_token($token)) {
        http_response_code(403);
        die('Invalid or expired CSRF token. Please refresh and try again.');
    }
}

/**
 * Get dynamic application base URL
 */
function base_url(string $path = ''): string
{
    // Determine the root folder where index/dashboard is located
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Normalize up to /expense-manager or root
    $segments = explode('/', trim($scriptDir, '/'));
    $baseSegments = [];
    foreach ($segments as $seg) {
        $baseSegments[] = $seg;
        if ($seg === 'expense-manager') {
            break;
        }
    }
    
    $base = '/' . implode('/', $baseSegments);
    if ($base === '/') {
        $base = '';
    }

    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Redirect helper
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Set flash alert message (stored in session)
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Retrieve and clear flash message
 */
function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Get current user ID
 */
function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get current user data array
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Get user currency symbol
 */
function current_currency(): string
{
    return $_SESSION['user']['currency'] ?? '₹';
}

/**
 * Format currency with symbol
 */
function format_currency(float|int|string $amount, ?string $symbol = null): string
{
    $val = (float)$amount;
    $sym = $symbol ?? current_currency();
    return $sym . ' ' . number_format($val, 2);
}

/**
 * Validate date in YYYY-MM-DD format
 */
function is_valid_date(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Seed default categories for a newly created user
 */
function seed_user_default_categories(PDO $pdo, int $userId): void
{
    $defaultCategories = [
        ['Salary', 'income'],
        ['Freelance', 'income'],
        ['Investments', 'income'],
        ['Other Income', 'income'],
        ['Food & Dining', 'expense'],
        ['Transport', 'expense'],
        ['Rent & Housing', 'expense'],
        ['Bills & Utilities', 'expense'],
        ['Shopping', 'expense'],
        ['Health & Medical', 'expense'],
        ['Education', 'expense'],
        ['Entertainment', 'expense'],
        ['Other Expense', 'expense'],
    ];

    $stmt = $pdo->prepare("INSERT INTO `categories` (`user_id`, `name`, `type`) VALUES (:user_id, :name, :type)");
    foreach ($defaultCategories as $cat) {
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => $cat[0],
            ':type' => $cat[1]
        ]);
    }
}
