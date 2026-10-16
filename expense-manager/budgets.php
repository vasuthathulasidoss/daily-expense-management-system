<?php
/**
 * Monthly Category Budgets
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Monthly Budgets';
$currentPage = 'budgets';
$userId = (int)current_user_id();
$pdo = getDBConnection();

// Selected Month & Year (default: current)
$month = isset($_GET['month']) && ctype_digit((string)$_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year = isset($_GET['year']) && ctype_digit((string)$_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

if ($month < 1 || $month > 12) $month = (int)date('n');
if ($year < 2000 || $year > 2099) $year = (int)date('Y');

// Handle Save Budget Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $catId = isset($_POST['category_id']) && ctype_digit((string)$_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $postMonth = isset($_POST['month']) && ctype_digit((string)$_POST['month']) ? (int)$_POST['month'] : $month;
    $postYear = isset($_POST['year']) && ctype_digit((string)$_POST['year']) ? (int)$_POST['year'] : $year;
    $amountRaw = trim($_POST['amount'] ?? '0');

    if ($catId <= 0) {
        set_flash('danger', 'Invalid category selected.');
        redirect(base_url("budgets.php?month={$postMonth}&year={$postYear}"));
    }

    // Verify category belongs to user and is of type expense
    $chk = $pdo->prepare("SELECT id, name FROM categories WHERE id = :id AND user_id = :user_id AND type = 'expense' LIMIT 1");
    $chk->execute([':id' => $catId, ':user_id' => $userId]);
    $cat = $chk->fetch();

    if (!$cat) {
        set_flash('danger', 'Unauthorized or invalid expense category.');
        redirect(base_url("budgets.php?month={$postMonth}&year={$postYear}"));
    }

    $amount = max(0.0, (float)$amountRaw);

    if ($amount > 0) {
        // Upsert budget
        $upsert = $pdo->prepare("
            INSERT INTO budgets (user_id, category_id, month, year, amount, created_at)
            VALUES (:user_id, :category_id, :month, :year, :amount, NOW())
            ON DUPLICATE KEY UPDATE amount = :amount_update
        ");
        $upsert->execute([
            ':user_id' => $userId,
            ':category_id' => $catId,
            ':month' => $postMonth,
            ':year' => $postYear,
            ':amount' => $amount,
            ':amount_update' => $amount
        ]);
        set_flash('success', 'Monthly budget for "' . htmlspecialchars($cat['name']) . '" updated to ' . format_currency($amount) . '.');
    } else {
        // Delete budget if 0 or cleared
        $del = $pdo->prepare("DELETE FROM budgets WHERE user_id = :user_id AND category_id = :category_id AND month = :month AND year = :year");
        $del->execute([
            ':user_id' => $userId,
            ':category_id' => $catId,
            ':month' => $postMonth,
            ':year' => $postYear
        ]);
        set_flash('info', 'Budget cleared for "' . htmlspecialchars($cat['name']) . '".');
    }

    redirect(base_url("budgets.php?month={$postMonth}&year={$postYear}"));
}

// Fetch all Expense Categories for this user, along with any configured budget and actual spent in selected month/year
$budgetSql = "
    SELECT 
        c.id AS category_id,
        c.name AS category_name,
        COALESCE(b.amount, 0) AS budget_amount,
        COALESCE(
            (SELECT SUM(t.amount) 
             FROM transactions t 
             WHERE t.user_id = :user_id 
               AND t.category_id = c.id 
               AND t.type = 'expense' 
               AND MONTH(t.transaction_date) = :month 
               AND YEAR(t.transaction_date) = :year
            ), 0
        ) AS actual_spent
    FROM categories c
    LEFT JOIN budgets b 
        ON c.id = b.category_id 
       AND b.user_id = :user_id 
       AND b.month = :month 
       AND b.year = :year
    WHERE c.user_id = :user_id AND c.type = 'expense'
    ORDER BY c.name ASC
";

$stmt = $pdo->prepare($budgetSql);
$stmt->execute([
    ':user_id' => $userId,
    ':month' => $month,
    ':year' => $year
]);
$budgetRows = $stmt->fetchAll();

// Calculate total budget & total spent across all expense categories
$totalBudget = 0.0;
$totalSpent = 0.0;
$overBudgetCount = 0;

foreach ($budgetRows as $row) {
    $b = (float)$row['budget_amount'];
    $s = (float)$row['actual_spent'];
    $totalBudget += $b;
    $totalSpent += $s;
    if ($b > 0 && $s > $b) {
        $overBudgetCount++;
    }
}

$monthName = date('F', mktime(0, 0, 0, $month, 10));

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header-row">
  <div>
    <h1 class="page-heading">Monthly Budgets</h1>
    <p class="page-subheading">Set expense targets and monitor spending velocity. Over-budget categories highlight in red.</p>
  </div>
  
  <!-- Month & Year Selector -->
  <form method="GET" action="<?= base_url('budgets.php') ?>" class="month-selector-form">
    <div class="month-selector-group">
      <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
        <?php for ($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>>
            <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
          </option>
        <?php endfor; ?>
      </select>

      <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
        <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
          <option value="<?= $y ?>" <?= $y === (int)$year ? 'selected' : '' ?>>
            <?= $y ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>
  </form>
</div>

<!-- Overall Budget Metric Strip -->
<div class="metric-grid-3">
  <div class="metric-card">
    <span class="metric-title">Total Allocated Budget</span>
    <div class="metric-value font-mono tabular-nums text-primary"><?= format_currency($totalBudget) ?></div>
    <span class="period-context"><?= $monthName ?> <?= $year ?></span>
  </div>

  <div class="metric-card">
    <span class="metric-title">Total Spent in Period</span>
    <div class="metric-value font-mono tabular-nums text-expense"><?= format_currency($totalSpent) ?></div>
    <span class="period-context">Actual recorded expenses</span>
  </div>

  <div class="metric-card">
    <span class="metric-title">Budget Health</span>
    <div class="metric-value font-mono tabular-nums <?= $overBudgetCount > 0 ? 'text-expense font-bold' : 'text-income' ?>">
      <?= $overBudgetCount > 0 ? $overBudgetCount . ' Over Budget' : 'All Under Budget' ?>
    </div>
    <span class="period-context">
      <?= $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) . '% of total allocated spent' : 'Set category targets below' ?>
    </span>
  </div>
</div>

<!-- Category Budget Progress Cards -->
<div class="budget-items-list">
  <?php if (empty($budgetRows)): ?>
    <div class="empty-state">
      <p>No expense categories available. Create expense categories first.</p>
      <a href="<?= base_url('categories/index.php') ?>" class="btn btn-primary">+ Add Categories</a>
    </div>
  <?php else: ?>
    <?php foreach ($budgetRows as $row): 
      $budget = (float)$row['budget_amount'];
      $spent = (float)$row['actual_spent'];
      $pct = $budget > 0 ? ($spent / $budget) * 100 : 0;
      $barWidth = min(100, $pct);
      $isOver = $budget > 0 && $spent > $budget;
      $remaining = $budget - $spent;
    ?>
      <div class="budget-card <?= $isOver ? 'border-danger' : '' ?>">
        <div class="budget-card-header">
          <div class="budget-category-info">
            <span class="category-indicator cat-expense"></span>
            <h3 class="budget-cat-name"><?= e($row['category_name']) ?></h3>
            <?php if ($isOver): ?>
              <span class="badge-status badge-over-budget">Over Budget by <?= format_currency(abs($remaining)) ?></span>
            <?php elseif ($budget > 0): ?>
              <span class="badge-status badge-ok"><?= format_currency($remaining) ?> remaining</span>
            <?php else: ?>
              <span class="text-muted text-xs">No limit configured</span>
            <?php endif; ?>
          </div>

          <div class="budget-actions">
            <button type="button" class="btn btn-sm btn-outline" 
              onclick="openBudgetModal(<?= $row['category_id'] ?>, '<?= e(addslashes($row['category_name'])) ?>', <?= $budget ?>)">
              <?= $budget > 0 ? 'Edit Limit' : 'Set Budget' ?>
            </button>
          </div>
        </div>

        <!-- Progress Bar Container -->
        <div class="budget-progress-track">
          <div 
            class="budget-progress-bar <?= $isOver ? 'progress-danger' : ($pct >= 75 ? 'progress-warning' : 'progress-normal') ?>"
            style="width: <?= $budget > 0 ? $barWidth : 0 ?>%;"
            aria-valuenow="<?= round($pct) ?>" 
            aria-valuemin="0" 
            aria-valuemax="100">
          </div>
        </div>

        <!-- Numbers row -->
        <div class="budget-card-footer">
          <div class="budget-metric">
            <span class="label">Spent:</span>
            <span class="value font-mono tabular-nums font-semibold <?= $isOver ? 'text-expense' : '' ?>">
              <?= format_currency($spent) ?>
            </span>
          </div>

          <div class="budget-metric text-center">
            <span class="label">Usage:</span>
            <span class="value font-mono tabular-nums font-semibold">
              <?= $budget > 0 ? round($pct, 1) . '%' : 'N/A' ?>
            </span>
          </div>

          <div class="budget-metric text-right">
            <span class="label">Target Limit:</span>
            <span class="value font-mono tabular-nums">
              <?= $budget > 0 ? format_currency($budget) : 'Not Set' ?>
            </span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Set / Edit Budget Modal -->
<div id="budget-modal" class="modal-backdrop" aria-hidden="true" role="dialog">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 id="budget-modal-title" class="modal-title">Set Category Budget</h3>
      <button type="button" class="modal-close" onclick="closeBudgetModal()">&times;</button>
    </div>
    <form method="POST" action="<?= base_url('budgets.php') ?>" id="budget-form">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" id="modal-cat-id" name="category_id" value="">
      <input type="hidden" name="month" value="<?= $month ?>">
      <input type="hidden" name="year" value="<?= $year ?>">

      <div class="modal-body">
        <p class="modal-intro">
          Setting monthly budget limit for: <strong id="modal-cat-name"></strong> in <strong><?= $monthName ?> <?= $year ?></strong>
        </p>

        <div class="form-group">
          <label for="modal-budget-amount" class="form-label required">Monthly Budget Limit (<?= e(current_currency()) ?>)</label>
          <div class="input-prefix-wrapper">
            <span class="input-prefix"><?= e(current_currency()) ?></span>
            <input 
              type="number" 
              step="0.01" 
              min="0" 
              id="modal-budget-amount" 
              name="amount" 
              class="form-control form-control-lg font-mono tabular-nums" 
              placeholder="0.00" 
              required
            >
          </div>
          <span class="form-hint">Enter 0 to clear or remove the budget for this month.</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeBudgetModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Monthly Limit</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openBudgetModal(catId, catName, currentAmount) {
    document.getElementById('modal-cat-id').value = catId;
    document.getElementById('modal-cat-name').textContent = catName;
    document.getElementById('modal-budget-amount').value = currentAmount > 0 ? currentAmount : '';
    document.getElementById('budget-modal').classList.add('open');
    document.getElementById('budget-modal').setAttribute('aria-hidden', 'false');
    setTimeout(() => document.getElementById('modal-budget-amount').focus(), 100);
  }

  function closeBudgetModal() {
    document.getElementById('budget-modal').classList.remove('open');
    document.getElementById('budget-modal').setAttribute('aria-hidden', 'true');
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
