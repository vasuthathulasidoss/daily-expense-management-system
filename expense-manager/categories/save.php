<?php
/**
 * Save Category (Create / Update)
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

check_csrf();

$userId = (int)current_user_id();
$pdo = getDBConnection();

$id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? 'expense');

if ($name === '' || mb_strlen($name) > 100) {
    set_flash('danger', 'Category name is required and must not exceed 100 characters.');
    redirect(base_url('categories/index.php'));
}

if (!in_array($type, ['income', 'expense'], true)) {
    $type = 'expense';
}

if ($id > 0) {
    // Update existing category (ensure user ownership)
    $stmt = $pdo->prepare("UPDATE categories SET name = :name, type = :type WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':name' => $name,
        ':type' => $type,
        ':id' => $id,
        ':user_id' => $userId
    ]);
    set_flash('success', 'Category "' . htmlspecialchars($name) . '" updated successfully.');
} else {
    // Create new category
    $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, created_at) VALUES (:user_id, :name, :type, NOW())");
    $stmt->execute([
        ':user_id' => $userId,
        ':name' => $name,
        ':type' => $type
    ]);
    set_flash('success', 'Category "' . htmlspecialchars($name) . '" created successfully.');
}

redirect(base_url('categories/index.php'));
