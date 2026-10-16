<?php
/**
 * Delete Category
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

check_csrf();

$id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
$userId = (int)current_user_id();

if ($id <= 0) {
    set_flash('danger', 'Invalid category identifier provided.');
    redirect(base_url('categories/index.php'));
}

$pdo = getDBConnection();

// Verify category exists and belongs to current user
$catStmt = $pdo->prepare("SELECT id, name FROM categories WHERE id = :id AND user_id = :user_id LIMIT 1");
$catStmt->execute([':id' => $id, ':user_id' => $userId]);
$category = $catStmt->fetch();

if (!$category) {
    set_flash('danger', 'Category not found or unauthorized.');
    redirect(base_url('categories/index.php'));
}

// Check for linked transactions
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = :category_id AND user_id = :user_id");
$countStmt->execute([':category_id' => $id, ':user_id' => $userId]);
$transactionCount = (int)$countStmt->fetchColumn();

if ($transactionCount > 0) {
    set_flash(
        'danger',
        'Cannot delete category "' . htmlspecialchars($category['name']) . '" because it is linked to ' . 
        $transactionCount . ' transaction(s). Please reassign or delete those transactions first.'
    );
    redirect(base_url('categories/index.php'));
}

// Delete category and any related monthly budgets
try {
    $pdo->beginTransaction();

    $budgetStmt = $pdo->prepare("DELETE FROM budgets WHERE category_id = :category_id AND user_id = :user_id");
    $budgetStmt->execute([':category_id' => $id, ':user_id' => $userId]);

    $delStmt = $pdo->prepare("DELETE FROM categories WHERE id = :id AND user_id = :user_id");
    $delStmt->execute([':id' => $id, ':user_id' => $userId]);

    $pdo->commit();
    set_flash('success', 'Category "' . htmlspecialchars($category['name']) . '" was successfully deleted.');
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Category Deletion Error: ' . $e->getMessage());
    set_flash('danger', 'An error occurred while deleting the category.');
}

redirect(base_url('categories/index.php'));
