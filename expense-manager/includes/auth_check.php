<?php
/**
 * Authentication Gatekeeper
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['user_id'])) {
    set_flash('danger', 'Please log in to access this page.');
    redirect(base_url('auth/login.php'));
}

// Refresh user profile details in session if needed
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT id, name, email, currency, created_at FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // User was deleted from DB
    session_unset();
    session_destroy();
    set_flash('danger', 'Your session expired. Please log in again.');
    redirect(base_url('auth/login.php'));
}

$_SESSION['user'] = $user;
