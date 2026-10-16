<?php
/**
 * API Endpoint: AJAX Filter Transactions (JSON)
 * Daily Expense Management System
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$pdo = getDBConnection();

$search = trim($_GET['search'] ?? '');
$type = trim($_GET['type'] ?? 'all');
$categoryId = isset($_GET['category_id']) && ctype_digit((string)$_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$paymentMethod = trim($_GET['payment_method'] ?? 'all');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$sortBy = trim($_GET['sort_by'] ?? 'date');
$sortDir = strtolower(trim($_GET['sort_dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

$page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$whereClauses = ['t.user_id = :user_id'];
$params = [':user_id' => $userId];

if ($type === 'income' || $type === 'expense') {
    $whereClauses[] = 't.type = :type';
    $params[':type'] = $type;
}

if ($categoryId > 0) {
    $whereClauses[] = 't.category_id = :category_id';
    $params[':category_id'] = $categoryId;
}

if ($paymentMethod !== '' && $paymentMethod !== 'all') {
    $whereClauses[] = 't.payment_method = :payment_method';
    $params[':payment_method'] = $paymentMethod;
}

if (!empty($dateFrom) && is_valid_date($dateFrom)) {
    $whereClauses[] = 't.transaction_date >= :date_from';
    $params[':date_from'] = $dateFrom;
}

if (!empty($dateTo) && is_valid_date($dateTo)) {
    $whereClauses[] = 't.transaction_date <= :date_to';
    $params[':date_to'] = $dateTo;
}

if ($search !== '') {
    $whereClauses[] = '(t.notes LIKE :search OR c.name LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

$whereSql = implode(' AND ', $whereClauses);

$sortColumns = [
    'date' => 't.transaction_date',
    'amount' => 't.amount',
    'category' => 'c.name',
    'id' => 't.id'
];
$orderByColumn = $sortColumns[$sortBy] ?? 't.transaction_date';
$orderSql = "ORDER BY {$orderByColumn} {$sortDir}, t.id DESC";

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t LEFT JOIN categories c ON t.category_id = c.id WHERE {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Data
$querySql = "
    SELECT t.*, c.name AS category_name
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE {$whereSql}
    {$orderSql}
    LIMIT :offset, :per_page
";
$dataStmt = $pdo->prepare($querySql);
foreach ($params as $k => $v) {
    $dataStmt->bindValue($k, $v);
}
$dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$dataStmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
$dataStmt->execute();
$transactions = $dataStmt->fetchAll();

$formattedList = [];
foreach ($transactions as $t) {
    $formattedList[] = [
        'id' => (int)$t['id'],
        'type' => $t['type'],
        'category_name' => $t['category_name'] ?? 'Uncategorized',
        'amount' => (float)$t['amount'],
        'formatted_amount' => format_currency($t['amount']),
        'payment_method' => $t['payment_method'],
        'transaction_date' => $t['transaction_date'],
        'formatted_date' => date('d M Y', strtotime($t['transaction_date'])),
        'notes' => $t['notes'] ?? '',
        'edit_url' => base_url($t['type'] === 'income' ? 'income/edit.php?id=' . $t['id'] : 'expenses/edit.php?id=' . $t['id']),
        'delete_action' => base_url($t['type'] === 'income' ? 'income/delete.php' : 'expenses/delete.php')
    ];
}

echo json_encode([
    'status' => 'success',
    'total_records' => $totalRecords,
    'total_pages' => $totalPages,
    'current_page' => $page,
    'per_page' => $perPage,
    'transactions' => $formattedList
]);
