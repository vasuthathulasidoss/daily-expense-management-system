<?php
/**
 * Financial Reports & Month-over-Month Comparison
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Financial Reports';
$currentPage = 'reports';
$userId = (int)current_user_id();
$pdo = getDBConnection();

// Selected Month and Year
$month = isset($_GET['month']) && ctype_digit((string)$_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year = isset($_GET['year']) && ctype_digit((string)$_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

if ($month < 1 || $month > 12) $month = (int)date('n');
if ($year < 2000 || $year > 2099) $year = (int)date('Y');

$currentMonthObj = new DateTime(sprintf('%04d-%02d-01', $year, $month));
$prevMonthObj = (clone $currentMonthObj)->modify('-1 month');
$prevMonth = (int)$prevMonthObj->format('n');
$prevYear = (int)$prevMonthObj->format('Y');

// 1. Current Month Totals
$currStmt = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense,
        COUNT(id) AS transaction_count
    FROM transactions
    WHERE user_id = :user_id 
      AND MONTH(transaction_date) = :month 
      AND YEAR(transaction_date) = :year
");
$currStmt->execute([':user_id' => $userId, ':month' => $month, ':year' => $year]);
$currTotals = $currStmt->fetch();

$curIncome = (float)$currTotals['total_income'];
$curExpense = (float)$currTotals['total_expense'];
$curSavings = $curIncome - $curExpense;
$curSavingsRate = $curIncome > 0 ? ($curSavings / $curIncome) * 100 : 0.0;

// 2. Previous Month Totals
$prevStmt = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense,
        COUNT(id) AS transaction_count
    FROM transactions
    WHERE user_id = :user_id 
      AND MONTH(transaction_date) = :prev_month 
      AND YEAR(transaction_date) = :prev_year
");
$prevStmt->execute([':user_id' => $userId, ':prev_month' => $prevMonth, ':prev_year' => $prevYear]);
$prevTotals = $prevStmt->fetch();

$prevIncome = (float)$prevTotals['total_income'];
$prevExpense = (float)$prevTotals['total_expense'];
$prevSavings = $prevIncome - $prevExpense;

// Helper to compute percentage delta
function calc_delta(float $current, float $previous): array
{
    if ($previous <= 0.001) {
        return ['val' => $current > 0 ? '+100%' : '0%', 'positive' => $current >= 0];
    }
    $diff = (($current - $previous) / $previous) * 100;
    return [
        'val' => ($diff >= 0 ? '+' : '') . number_format($diff, 1) . '%',
        'positive' => $diff >= 0
    ];
}

$incomeDelta = calc_delta($curIncome, $prevIncome);
$expenseDelta = calc_delta($curExpense, $prevExpense);
$savingsDelta = calc_delta($curSavings, $prevSavings);

// 3. Category-wise Expense Breakdown for Selected Month
$expCatStmt = $pdo->prepare("
    SELECT c.name AS category_name, SUM(t.amount) AS total_amount, COUNT(t.id) AS tx_count
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :user_id 
      AND t.type = 'expense'
      AND MONTH(t.transaction_date) = :month 
      AND YEAR(t.transaction_date) = :year
    GROUP BY t.category_id, c.name
    ORDER BY total_amount DESC
");
$expCatStmt->execute([':user_id' => $userId, ':month' => $month, ':year' => $year]);
$expenseBreakdown = $expCatStmt->fetchAll();

// 4. Category-wise Income Breakdown for Selected Month
$incCatStmt = $pdo->prepare("
    SELECT c.name AS category_name, SUM(t.amount) AS total_amount, COUNT(t.id) AS tx_count
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :user_id 
      AND t.type = 'income'
      AND MONTH(t.transaction_date) = :month 
      AND YEAR(t.transaction_date) = :year
    GROUP BY t.category_id, c.name
    ORDER BY total_amount DESC
");
$incCatStmt->execute([':user_id' => $userId, ':month' => $month, ':year' => $year]);
$incomeBreakdown = $incCatStmt->fetchAll();

// 5. Payment Method Distribution
$pmStmt = $pdo->prepare("
    SELECT payment_method, type, SUM(amount) AS total_amount, COUNT(id) AS count
    FROM transactions
    WHERE user_id = :user_id 
      AND MONTH(transaction_date) = :month 
      AND YEAR(transaction_date) = :year
    GROUP BY payment_method, type
    ORDER BY total_amount DESC
");
$pmStmt->execute([':user_id' => $userId, ':month' => $month, ':year' => $year]);
$paymentBreakdown = $pmStmt->fetchAll();

$monthName = $currentMonthObj->format('F');
$prevMonthName = $prevMonthObj->format('F');

require_once __DIR__ . '/includes/header.php';
?>

<div class="reports-wrapper">
  <!-- Report Top Header (No-print controls + Print Trigger) -->
  <div class="page-header-row no-print">
    <div>
      <h1 class="page-heading">Financial Summary & Report</h1>
      <p class="page-subheading">Comprehensive cashflow analysis for <?= $monthName ?> <?= $year ?> with month-over-month variances.</p>
    </div>

    <div class="page-header-actions">
      <!-- Month & Year Selector -->
      <form method="GET" action="<?= base_url('reports.php') ?>" class="report-filter-form">
        <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>>
              <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
            </option>
          <?php endfor; ?>
        </select>
        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
            <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>>
              <?= $y ?>
            </option>
          <?php endfor; ?>
        </select>
      </form>

      <!-- Print Button -->
      <button type="button" class="btn btn-outline" onclick="window.print()" title="Print or Save as PDF">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="6 9 6 2 18 2 18 9"></polyline>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
          <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        <span>Print Report</span>
      </button>
    </div>
  </div>

  <!-- Print Header Only visible when printing -->
  <div class="print-only print-document-header">
    <div class="print-brand">
      <h2>ExpensePulse &middot; Financial Statement</h2>
      <p>Report Period: <strong><?= $monthName ?> <?= $year ?></strong> | Generated: <?= date('Y-m-d H:i') ?> | User: <?= e($user['name']) ?> (<?= e($user['email']) ?>)</p>
    </div>
  </div>

  <!-- Key Metrics Comparison Strip -->
  <div class="metric-grid-4">
    <!-- Income Metric -->
    <div class="metric-card">
      <span class="metric-title">Total Income</span>
      <div class="metric-value font-mono tabular-nums text-income"><?= format_currency($curIncome) ?></div>
      <div class="metric-delta">
        <span class="delta-tag <?= $incomeDelta['positive'] ? 'text-income' : 'text-expense' ?>">
          <?= $incomeDelta['val'] ?>
        </span>
        <span class="delta-label">vs <?= $prevMonthName ?> (<?= format_currency($prevIncome) ?>)</span>
      </div>
    </div>

    <!-- Expense Metric -->
    <div class="metric-card">
      <span class="metric-title">Total Expenses</span>
      <div class="metric-value font-mono tabular-nums text-expense"><?= format_currency($curExpense) ?></div>
      <div class="metric-delta">
        <span class="delta-tag <?= !$expenseDelta['positive'] ? 'text-income' : 'text-expense' ?>">
          <?= $expenseDelta['val'] ?>
        </span>
        <span class="delta-label">vs <?= $prevMonthName ?> (<?= format_currency($prevExpense) ?>)</span>
      </div>
    </div>

    <!-- Net Savings Metric -->
    <div class="metric-card">
      <span class="metric-title">Net Savings</span>
      <div class="metric-value font-mono tabular-nums <?= $curSavings >= 0 ? 'text-income' : 'text-expense' ?>">
        <?= format_currency($curSavings) ?>
      </div>
      <div class="metric-delta">
        <span class="delta-tag <?= $savingsDelta['positive'] ? 'text-income' : 'text-expense' ?>">
          <?= $savingsDelta['val'] ?>
        </span>
        <span class="delta-label">vs <?= $prevMonthName ?> (<?= format_currency($prevSavings) ?>)</span>
      </div>
    </div>

    <!-- Savings Rate Metric -->
    <div class="metric-card">
      <span class="metric-title">Savings Rate</span>
      <div class="metric-value font-mono tabular-nums <?= $curSavingsRate >= 20 ? 'text-income' : ($curSavingsRate > 0 ? 'text-primary' : 'text-expense') ?>">
        <?= number_format($curSavingsRate, 1) ?>%
      </div>
      <div class="metric-delta">
        <span class="delta-label"><?= $curSavingsRate >= 20 ? 'Healthy savings target (&ge;20%)' : 'Below 20% savings benchmark' ?></span>
      </div>
    </div>
  </div>

  <!-- Breakdown Tables Grid -->
  <div class="report-tables-grid">
    <!-- Category-Wise Expense Breakdown -->
    <div class="table-panel">
      <div class="panel-header">
        <div class="panel-title-group">
          <h2 class="panel-title">Expense Breakdown by Category</h2>
          <span class="panel-subtitle"><?= count($expenseBreakdown) ?> active categories in <?= $monthName ?></span>
        </div>
      </div>

      <div class="table-responsive">
        <?php if (empty($expenseBreakdown)): ?>
          <div class="empty-state">
            <p>No expense transactions recorded in <?= $monthName ?> <?= $year ?>.</p>
          </div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Category</th>
                <th class="text-center">Count</th>
                <th class="text-right">Share</th>
                <th class="text-right">Total Spent</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($expenseBreakdown as $row): 
                $share = $curExpense > 0 ? ((float)$row['total_amount'] / $curExpense) * 100 : 0;
              ?>
                <tr>
                  <td>
                    <span class="category-indicator cat-expense"></span>
                    <span class="font-medium"><?= e($row['category_name']) ?></span>
                  </td>
                  <td class="text-center text-muted font-mono tabular-nums"><?= (int)$row['tx_count'] ?></td>
                  <td class="text-right font-mono tabular-nums">
                    <div class="share-cell">
                      <span><?= number_format($share, 1) ?>%</span>
                      <div class="share-mini-bar">
                        <div class="share-fill-expense" style="width: <?= min(100, $share) ?>%"></div>
                      </div>
                    </div>
                  </td>
                  <td class="text-right font-mono tabular-nums font-semibold text-expense">
                    <?= format_currency($row['total_amount']) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="table-total-row">
                <th colspan="3">Total Expenditures</th>
                <th class="text-right font-mono tabular-nums text-expense font-bold"><?= format_currency($curExpense) ?></th>
              </tr>
            </tfoot>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- Category-Wise Income Breakdown -->
    <div class="table-panel">
      <div class="panel-header">
        <div class="panel-title-group">
          <h2 class="panel-title">Income Breakdown by Source</h2>
          <span class="panel-subtitle"><?= count($incomeBreakdown) ?> income sources in <?= $monthName ?></span>
        </div>
      </div>

      <div class="table-responsive">
        <?php if (empty($incomeBreakdown)): ?>
          <div class="empty-state">
            <p>No income transactions recorded in <?= $monthName ?> <?= $year ?>.</p>
          </div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Source Category</th>
                <th class="text-center">Count</th>
                <th class="text-right">Share</th>
                <th class="text-right">Total Received</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($incomeBreakdown as $row): 
                $share = $curIncome > 0 ? ((float)$row['total_amount'] / $curIncome) * 100 : 0;
              ?>
                <tr>
                  <td>
                    <span class="category-indicator cat-income"></span>
                    <span class="font-medium"><?= e($row['category_name']) ?></span>
                  </td>
                  <td class="text-center text-muted font-mono tabular-nums"><?= (int)$row['tx_count'] ?></td>
                  <td class="text-right font-mono tabular-nums">
                    <div class="share-cell">
                      <span><?= number_format($share, 1) ?>%</span>
                      <div class="share-mini-bar">
                        <div class="share-fill-income" style="width: <?= min(100, $share) ?>%"></div>
                      </div>
                    </div>
                  </td>
                  <td class="text-right font-mono tabular-nums font-semibold text-income">
                    + <?= format_currency($row['total_amount']) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="table-total-row">
                <th colspan="3">Total Income</th>
                <th class="text-right font-mono tabular-nums text-income font-bold"><?= format_currency($curIncome) ?></th>
              </tr>
            </tfoot>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Month vs Month Comparison Card -->
  <div class="table-panel">
    <div class="panel-header">
      <div class="panel-title-group">
        <h2 class="panel-title">Period Variance Comparison</h2>
        <span class="panel-subtitle"><?= $monthName ?> <?= $year ?> versus <?= $prevMonthName ?> <?= $prevYear ?></span>
      </div>
    </div>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Financial Metric</th>
            <th class="text-right"><?= $prevMonthName ?> <?= $prevYear ?></th>
            <th class="text-right"><?= $monthName ?> <?= $year ?></th>
            <th class="text-right">Variance / Delta</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="font-medium">Total Income</td>
            <td class="text-right font-mono tabular-nums"><?= format_currency($prevIncome) ?></td>
            <td class="text-right font-mono tabular-nums text-income font-semibold"><?= format_currency($curIncome) ?></td>
            <td class="text-right font-mono tabular-nums font-bold <?= $incomeDelta['positive'] ? 'text-income' : 'text-expense' ?>">
              <?= $incomeDelta['val'] ?>
            </td>
          </tr>
          <tr>
            <td class="font-medium">Total Expenses</td>
            <td class="text-right font-mono tabular-nums"><?= format_currency($prevExpense) ?></td>
            <td class="text-right font-mono tabular-nums text-expense font-semibold"><?= format_currency($curExpense) ?></td>
            <td class="text-right font-mono tabular-nums font-bold <?= !$expenseDelta['positive'] ? 'text-income' : 'text-expense' ?>">
              <?= $expenseDelta['val'] ?>
            </td>
          </tr>
          <tr>
            <td class="font-medium">Net Savings</td>
            <td class="text-right font-mono tabular-nums"><?= format_currency($prevSavings) ?></td>
            <td class="text-right font-mono tabular-nums font-semibold <?= $curSavings >= 0 ? 'text-income' : 'text-expense' ?>">
              <?= format_currency($curSavings) ?>
            </td>
            <td class="text-right font-mono tabular-nums font-bold <?= $savingsDelta['positive'] ? 'text-income' : 'text-expense' ?>">
              <?= $savingsDelta['val'] ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
