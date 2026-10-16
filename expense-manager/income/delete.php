<?php
/**
 * Delete Income Entry
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
    set_flash('danger', 'Invalid transaction identifier provided.');
    redirect(base_url('income/index.php'));
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("DELETE FROM transactions WHERE id = :id AND user_id = :user_id AND type = 'income'");
$stmt->execute([':id' => $id, ':user_id' => $userId]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Income entry was successfully deleted.');
} else {
    set_flash('danger', 'Income entry could not be found or has already been removed.');
}

redirect(base_url('income/index.php'));
