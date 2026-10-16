<?php
/**
 * API Endpoint: Chart Data & Period Metrics (JSON)
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

$period = $_GET['period'] ?? 'this_month';
$today = date('Y-m-d');
$startDate = '';
$endDate = '';

switch ($period) {
    case 'today':
        $startDate = $today;
        $endDate = $today;
        break;
    case 'this_week':
        $startDate = date('Y-m-d', strtotime('monday this week'));
        $endDate = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'custom':
        $startDate = $_GET['from'] ?? date('Y-m-01');
        $endDate = $_GET['to'] ?? $today;
        if (!is_valid_date($startDate)) $startDate = date('Y-m-01');
        if (!is_valid_date($endDate)) $endDate = $today;
        break;
    case 'this_month':
    default:
        $period = 'this_month';
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        break;
}

// 1. Period totals
$summaryStmt = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS period_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS period_expense
    FROM transactions
    WHERE user_id = :user_id 
      AND transaction_date >= :start_date 
      AND transaction_date <= :end_date
");
$summaryStmt->execute([
    ':user_id' => $userId,
    ':start_date' => $startDate,
    ':end_date' => $endDate
]);
$summary = $summaryStmt->fetch();
$totalIncome = (float)$summary['period_income'];
$totalExpense = (float)$summary['period_expense'];
$netBalance = $totalIncome - $totalExpense;

// 2. Category Doughnut Data
$catStmt = $pdo->prepare("
    SELECT c.name AS category_name, SUM(t.amount) AS total_amount
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :user_id 
      AND t.type = 'expense'
      AND t.transaction_date >= :start_date 
      AND t.transaction_date <= :end_date
    GROUP BY t.category_id, c.name
    ORDER BY total_amount DESC
");
$catStmt->execute([
    ':user_id' => $userId,
    ':start_date' => $startDate,
    ':end_date' => $endDate
]);
$catRows = $catStmt->fetchAll();

$categoryLabels = array_column($catRows, 'category_name');
$categoryValues = array_map('floatval', array_column($catRows, 'total_amount'));

// 3. Daily Trend Data
$dailyStmt = $pdo->prepare("
    SELECT 
        transaction_date,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS day_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS day_expense
    FROM transactions
    WHERE user_id = :user_id 
      AND transaction_date >= :start_date 
      AND transaction_date <= :end_date
    GROUP BY transaction_date
    ORDER BY transaction_date ASC
");
$dailyStmt->execute([
    ':user_id' => $userId,
    ':start_date' => $startDate,
    ':end_date' => $endDate
]);
$dailyRows = $dailyStmt->fetchAll();

$dailyMap = [];
foreach ($dailyRows as $r) {
    $dailyMap[$r['transaction_date']] = [
        'income' => (float)$r['day_income'],
        'expense' => (float)$r['day_expense']
    ];
}

$chartDays = [];
$chartIncome = [];
$chartExpense = [];

$currDay = new DateTime($startDate);
$endDay = new DateTime($endDate);
$endDay->modify('+1 day'); // inclusive

$interval = new DateInterval('P1D');
$datePeriod = new DatePeriod($currDay, $interval, $endDay);

foreach ($datePeriod as $dt) {
    $dateKey = $dt->format('Y-m-d');
    $chartDays[] = $dt->format('d M');
    $chartIncome[] = $dailyMap[$dateKey]['income'] ?? 0.0;
    $chartExpense[] = $dailyMap[$dateKey]['expense'] ?? 0.0;
}

echo json_encode([
    'status' => 'success',
    'currency' => current_currency(),
    'period' => [
        'name' => $period,
        'start_date' => $startDate,
        'end_date' => $endDate
    ],
    'summary' => [
        'total_income' => $totalIncome,
        'total_expense' => $totalExpense,
        'net_balance' => $netBalance,
        'formatted_income' => format_currency($totalIncome),
        'formatted_expense' => format_currency($totalExpense),
        'formatted_balance' => format_currency($netBalance)
    ],
    'category_chart' => [
        'labels' => $categoryLabels,
        'values' => $categoryValues
    ],
    'trend_chart' => [
        'days' => $chartDays,
        'income' => $chartIncome,
        'expense' => $chartExpense
    ]
], JSON_PRETTY_PRINT);
