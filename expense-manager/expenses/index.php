<?php
/**
 * Expense List Page
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Expense Management';
$currentPage = 'expenses';
$userId = (int)current_user_id();
$pdo = getDBConnection();

// Query all expense entries with category names
$stmt = $pdo->prepare("
    SELECT t.*, c.name AS category_name
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :user_id AND t.type = 'expense'
    ORDER BY t.transaction_date DESC, t.id DESC
");
$stmt->execute([':user_id' => $userId]);
$expenseList = $stmt->fetchAll();

// Total Expense calculation
$totalExpense = 0.0;
foreach ($expenseList as $exp) {
    $totalExpense += (float)$exp['amount'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header-row">
  <div>
    <h1 class="page-heading">Expense Entries</h1>
    <p class="page-subheading">Track outgoing costs, bills, dining, rent, and shopping expenditures.</p>
  </div>
  <div class="page-header-actions">
    <div class="stat-pill-box">
      <span class="stat-pill-label">Total Recorded Expenses:</span>
      <span class="stat-pill-value font-mono tabular-nums text-expense font-bold"><?= format_currency($totalExpense) ?></span>
    </div>
    <a href="<?= base_url('expenses/add.php') ?>" class="btn btn-primary-expense">+ Add Expense</a>
  </div>
</div>

<div class="table-panel">
  <div class="table-responsive">
    <?php if (empty($expenseList)): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24" width="40" height="40" stroke="currentColor" fill="none" class="empty-icon text-expense">
          <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
          <polyline points="17 18 23 18 23 12"></polyline>
        </svg>
        <h3>No Expenses Recorded Yet</h3>
        <p>Log your daily purchases, groceries, utility payments, and subscriptions to stay on top of your budget.</p>
        <a href="<?= base_url('expenses/add.php') ?>" class="btn btn-primary-expense">+ Record First Expense</a>
      </div>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Category</th>
            <th>Payment Method</th>
            <th>Notes</th>
            <th class="text-right">Amount</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($expenseList as $row): ?>
            <tr>
              <td>
                <span class="font-mono text-sm"><?= date('d M Y', strtotime($row['transaction_date'])) ?></span>
              </td>
              <td>
                <span class="category-indicator cat-expense"></span>
                <span class="font-medium"><?= e($row['category_name'] ?? 'Uncategorized') ?></span>
              </td>
              <td>
                <span class="text-muted"><?= e($row['payment_method']) ?></span>
              </td>
              <td>
                <span class="cell-notes" title="<?= e($row['notes']) ?>"><?= e($row['notes'] ?: '-') ?></span>
              </td>
              <td class="text-right font-mono tabular-nums font-semibold text-expense">
                - <?= format_currency($row['amount']) ?>
              </td>
              <td class="text-right">
                <div class="action-buttons">
                  <a href="<?= base_url('expenses/edit.php?id=' . $row['id']) ?>" class="btn-icon-subtle" title="Edit expense">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                  </a>
                  <button type="button" class="btn-icon-subtle btn-delete-trigger" 
                    data-action="<?= base_url('expenses/delete.php') ?>" 
                    data-id="<?= $row['id'] ?>"
                    data-name="<?= e($row['category_name']) ?> (<?= format_currency($row['amount']) ?>)"
                    title="Delete expense">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
