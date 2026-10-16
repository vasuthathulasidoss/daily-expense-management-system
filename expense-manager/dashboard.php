<?php
/**
 * Main Application Dashboard
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
$userId = (int)current_user_id();
$pdo = getDBConnection();

// 1. Determine Time Period Filter
$period = $_GET['period'] ?? 'this_month';
$startDate = '';
$endDate = '';

$today = date('Y-m-d');

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

// 2. Fetch Period Summary Metrics (Total Income, Total Expense, Net Balance)
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
$periodSummary = $summaryStmt->fetch();
$totalIncome = (float)$periodSummary['period_income'];
$totalExpense = (float)$periodSummary['period_expense'];
$netBalance = $totalIncome - $totalExpense;

// 3. Fetch All-Time Overall Net Balance
$allTimeStmt = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) AS all_time_balance
    FROM transactions 
    WHERE user_id = :user_id
");
$allTimeStmt->execute([':user_id' => $userId]);
$allTimeBalance = (float)$allTimeStmt->fetchColumn();

// 4. Fetch Today's Transactions
$todayStmt = $pdo->prepare("
    SELECT t.*, c.name AS category_name
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :user_id AND t.transaction_date = :today
    ORDER BY t.id DESC
");
$todayStmt->execute([':user_id' => $userId, ':today' => $today]);
$todayTransactions = $todayStmt->fetchAll();

// 5. Fetch Recent 5 Transactions
$recentStmt = $pdo->prepare("
    SELECT t.*, c.name AS category_name
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :user_id
    ORDER BY t.transaction_date DESC, t.id DESC
    LIMIT 5
");
$recentStmt->execute([':user_id' => $userId]);
$recentTransactions = $recentStmt->fetchAll();

// 6. Expense Breakdown by Category for Selected Period (for Doughnut Chart)
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
$categoryBreakdown = $catStmt->fetchAll();

// 7. Daily Income vs Expense for Current Month (for Bar Chart)
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');
$dailyStmt = $pdo->prepare("
    SELECT 
        transaction_date,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS day_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS day_expense
    FROM transactions
    WHERE user_id = :user_id 
      AND transaction_date >= :month_start 
      AND transaction_date <= :month_end
    GROUP BY transaction_date
    ORDER BY transaction_date ASC
");
$dailyStmt->execute([
    ':user_id' => $userId,
    ':month_start' => $monthStart,
    ':month_end' => $monthEnd
]);
$dailyRecords = $dailyStmt->fetchAll();

// Map daily records by date for easy chart generation
$dailyMap = [];
foreach ($dailyRecords as $row) {
    $dailyMap[$row['transaction_date']] = [
        'income' => (float)$row['day_income'],
        'expense' => (float)$row['day_expense']
    ];
}

$chartDays = [];
$chartIncome = [];
$chartExpense = [];
$daysInMonth = (int)date('t');
for ($d = 1; $d <= $daysInMonth; $d++) {
    $dateKey = sprintf('%s-%02d', date('Y-m'), $d);
    $chartDays[] = date('d M', strtotime($dateKey));
    $chartIncome[] = $dailyMap[$dateKey]['income'] ?? 0;
    $chartExpense[] = $dailyMap[$dateKey]['expense'] ?? 0;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-wrapper">
  <!-- Period Filter Bar -->
  <div class="period-filter-bar">
    <div class="period-selector">
      <span class="filter-label">Filter Period:</span>
      <div class="segmented-control">
        <a href="<?= base_url('dashboard.php?period=today') ?>" class="seg-btn <?= $period === 'today' ? 'active' : '' ?>">Today</a>
        <a href="<?= base_url('dashboard.php?period=this_week') ?>" class="seg-btn <?= $period === 'this_week' ? 'active' : '' ?>">This Week</a>
        <a href="<?= base_url('dashboard.php?period=this_month') ?>" class="seg-btn <?= $period === 'this_month' ? 'active' : '' ?>">This Month</a>
        <button type="button" class="seg-btn <?= $period === 'custom' ? 'active' : '' ?>" id="toggle-custom-date-btn">Custom Range</button>
      </div>
    </div>

    <!-- Custom Range Form (Collapsible) -->
    <form method="GET" action="<?= base_url('dashboard.php') ?>" class="custom-date-form <?= $period === 'custom' ? 'open' : '' ?>" id="custom-date-form">
      <input type="hidden" name="period" value="custom">
      <div class="date-input-group">
        <label for="from" class="sr-only">From</label>
        <input type="date" id="from" name="from" value="<?= e($startDate) ?>" class="form-control form-control-sm" required>
        <span class="date-sep">to</span>
        <label for="to" class="sr-only">To</label>
        <input type="date" id="to" name="to" value="<?= e($endDate) ?>" class="form-control form-control-sm" required>
        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
      </div>
    </form>
  </div>

  <!-- Metric Summary Cards Grid -->
  <div class="metric-grid">
    <!-- Income Card -->
    <div class="metric-card metric-income">
      <div class="metric-card-header">
        <span class="metric-title">Total Income</span>
        <div class="metric-badge-icon bg-income-subtle">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
            <polyline points="17 6 23 6 23 12"></polyline>
          </svg>
        </div>
      </div>
      <div class="metric-value font-mono tabular-nums text-income"><?= format_currency($totalIncome) ?></div>
      <div class="metric-footer">
        <span class="period-context"><?= date('M d', strtotime($startDate)) ?> &ndash; <?= date('M d, Y', strtotime($endDate)) ?></span>
      </div>
    </div>

    <!-- Expense Card -->
    <div class="metric-card metric-expense">
      <div class="metric-card-header">
        <span class="metric-title">Total Expenses</span>
        <div class="metric-badge-icon bg-expense-subtle">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
            <polyline points="17 18 23 18 23 12"></polyline>
          </svg>
        </div>
      </div>
      <div class="metric-value font-mono tabular-nums text-expense"><?= format_currency($totalExpense) ?></div>
      <div class="metric-footer">
        <span class="period-context"><?= date('M d', strtotime($startDate)) ?> &ndash; <?= date('M d, Y', strtotime($endDate)) ?></span>
      </div>
    </div>

    <!-- Period Balance Card -->
    <div class="metric-card metric-balance">
      <div class="metric-card-header">
        <span class="metric-title">Net Savings (Period)</span>
        <div class="metric-badge-icon bg-neutral-subtle">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path>
            <path d="M12 18V6"></path>
          </svg>
        </div>
      </div>
      <div class="metric-value font-mono tabular-nums <?= $netBalance >= 0 ? 'text-income' : 'text-expense' ?>">
        <?= format_currency($netBalance) ?>
      </div>
      <div class="metric-footer">
        <span class="period-context">Income minus Expenses</span>
      </div>
    </div>

    <!-- Overall Cumulative Balance Card -->
    <div class="metric-card metric-alltime">
      <div class="metric-card-header">
        <span class="metric-title">Current Total Balance</span>
        <div class="metric-badge-icon bg-neutral-subtle">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
          </svg>
        </div>
      </div>
      <div class="metric-value font-mono tabular-nums <?= $allTimeBalance >= 0 ? 'text-primary' : 'text-expense' ?>">
        <?= format_currency($allTimeBalance) ?>
      </div>
      <div class="metric-footer">
        <span class="period-context">All-time lifetime balance</span>
      </div>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="dashboard-charts-grid">
    <!-- Chart 1: Category Expense Breakdown -->
    <div class="chart-panel">
      <div class="panel-header">
        <div class="panel-title-group">
          <h2 class="panel-title">Expenses by Category</h2>
          <span class="panel-subtitle">Distribution across categories for this period</span>
        </div>
      </div>
      <div class="chart-container" style="position: relative; height: 280px;">
        <?php if (empty($categoryBreakdown)): ?>
          <div class="empty-state">
            <p>No expense transactions recorded for the selected period.</p>
            <a href="<?= base_url('expenses/add.php') ?>" class="btn btn-sm btn-outline-expense">Log First Expense</a>
          </div>
        <?php else: ?>
          <canvas id="categoryDoughnutChart" 
            data-labels="<?= e(json_encode(array_column($categoryBreakdown, 'category_name'))) ?>"
            data-values="<?= e(json_encode(array_map('floatval', array_column($categoryBreakdown, 'total_amount')))) ?>"
            data-currency="<?= e(current_currency()) ?>">
          </canvas>
        <?php endif; ?>
      </div>
    </div>

    <!-- Chart 2: Daily Income vs Expense Trend -->
    <div class="chart-panel">
      <div class="panel-header">
        <div class="panel-title-group">
          <h2 class="panel-title">Daily Cashflow Trend</h2>
          <span class="panel-subtitle"><?= date('F Y') ?> &middot; Daily Income vs Expense</span>
        </div>
      </div>
      <div class="chart-container" style="position: relative; height: 280px;">
        <canvas id="dailyCashflowBarChart" 
          data-days="<?= e(json_encode($chartDays)) ?>"
          data-income="<?= e(json_encode($chartIncome)) ?>"
          data-expense="<?= e(json_encode($chartExpense)) ?>"
          data-currency="<?= e(current_currency()) ?>">
        </canvas>
      </div>
    </div>
  </div>

  <!-- Transactions Tables Grid (Today's Transactions & Recent 5) -->
  <div class="dashboard-tables-grid">
    <!-- Today's Transactions Panel -->
    <div class="table-panel">
      <div class="panel-header">
        <div class="panel-title-group">
          <h2 class="panel-title">Today's Transactions</h2>
          <span class="panel-subtitle"><?= date('l, M d, Y') ?> &middot; <?= count($todayTransactions) ?> items</span>
        </div>
        <div class="panel-actions">
          <a href="<?= base_url('transactions.php?date_from=' . $today . '&date_to=' . $today) ?>" class="btn-link">View in All</a>
        </div>
      </div>

      <div class="table-responsive">
        <?php if (empty($todayTransactions)): ?>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" fill="none" class="empty-icon">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p>No transactions recorded yet today.</p>
            <div class="empty-actions">
              <a href="<?= base_url('income/add.php') ?>" class="btn btn-sm btn-outline-income">+ Income</a>
              <a href="<?= base_url('expenses/add.php') ?>" class="btn btn-sm btn-outline-expense">+ Expense</a>
            </div>
          </div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Category</th>
                <th>Method</th>
                <th>Notes</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($todayTransactions as $row): ?>
                <tr>
                  <td>
                    <span class="category-indicator <?= $row['type'] === 'income' ? 'cat-income' : 'cat-expense' ?>"></span>
                    <span class="font-medium"><?= e($row['category_name'] ?? 'Uncategorized') ?></span>
                  </td>
                  <td><span class="text-muted"><?= e($row['payment_method']) ?></span></td>
                  <td><span class="cell-notes" title="<?= e($row['notes']) ?>"><?= e($row['notes'] ?: '-') ?></span></td>
                  <td class="text-right font-mono tabular-nums font-semibold <?= $row['type'] === 'income' ? 'text-income' : 'text-expense' ?>">
                    <?= $row['type'] === 'income' ? '+' : '-' ?> <?= format_currency($row['amount']) ?>
                  </td>
                  <td class="text-right">
                    <div class="action-buttons">
                      <a href="<?= base_url($row['type'] === 'income' ? 'income/edit.php?id=' . $row['id'] : 'expenses/edit.php?id=' . $row['id']) ?>" class="btn-icon-subtle" title="Edit">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                      </a>
                      <button type="button" class="btn-icon-subtle btn-delete-trigger" 
                        data-action="<?= base_url($row['type'] === 'income' ? 'income/delete.php' : 'expenses/delete.php') ?>" 
                        data-id="<?= $row['id'] ?>"
                        data-name="<?= e($row['category_name']) ?> (<?= format_currency($row['amount']) ?>)"
                        title="Delete">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent 5 Transactions Panel -->
    <div class="table-panel">
      <div class="panel-header">
        <div class="panel-title-group">
          <h2 class="panel-title">Recent Transactions</h2>
          <span class="panel-subtitle">Last 5 entries recorded</span>
        </div>
        <div class="panel-actions">
          <a href="<?= base_url('transactions.php') ?>" class="btn-link">All Transactions &rarr;</a>
        </div>
      </div>

      <div class="table-responsive">
        <?php if (empty($recentTransactions)): ?>
          <div class="empty-state">
            <p>No transactions found in your account.</p>
            <a href="<?= base_url('income/add.php') ?>" class="btn btn-sm btn-primary">Get Started</a>
          </div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Method</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentTransactions as $row): ?>
                <tr>
                  <td>
                    <span class="font-mono text-sm"><?= date('d M Y', strtotime($row['transaction_date'])) ?></span>
                  </td>
                  <td>
                    <span class="category-indicator <?= $row['type'] === 'income' ? 'cat-income' : 'cat-expense' ?>"></span>
                    <span class="font-medium"><?= e($row['category_name'] ?? 'Uncategorized') ?></span>
                  </td>
                  <td><span class="text-muted"><?= e($row['payment_method']) ?></span></td>
                  <td class="text-right font-mono tabular-nums font-semibold <?= $row['type'] === 'income' ? 'text-income' : 'text-expense' ?>">
                    <?= $row['type'] === 'income' ? '+' : '-' ?> <?= format_currency($row['amount']) ?>
                  </td>
                  <td class="text-right">
                    <div class="action-buttons">
                      <a href="<?= base_url($row['type'] === 'income' ? 'income/edit.php?id=' . $row['id'] : 'expenses/edit.php?id=' . $row['id']) ?>" class="btn-icon-subtle" title="Edit">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                      </a>
                      <button type="button" class="btn-icon-subtle btn-delete-trigger" 
                        data-action="<?= base_url($row['type'] === 'income' ? 'income/delete.php' : 'expenses/delete.php') ?>" 
                        data-id="<?= $row['id'] ?>"
                        data-name="<?= e($row['category_name']) ?> (<?= format_currency($row['amount']) ?>)"
                        title="Delete">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
