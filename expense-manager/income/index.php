<?php
/**
 * Income List Page
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Income Management';
$currentPage = 'income';
$userId = (int)current_user_id();
$pdo = getDBConnection();

// Query all income entries with category names
$stmt = $pdo->prepare("
    SELECT t.*, c.name AS category_name
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = :user_id AND t.type = 'income'
    ORDER BY t.transaction_date DESC, t.id DESC
");
$stmt->execute([':user_id' => $userId]);
$incomeList = $stmt->fetchAll();

// Total Income calculation
$totalIncome = 0.0;
foreach ($incomeList as $inc) {
    $totalIncome += (float)$inc['amount'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header-row">
  <div>
    <h1 class="page-heading">Income Entries</h1>
    <p class="page-subheading">View, filter, and track all incoming funds and revenue sources.</p>
  </div>
  <div class="page-header-actions">
    <div class="stat-pill-box">
      <span class="stat-pill-label">Total Recorded Income:</span>
      <span class="stat-pill-value font-mono tabular-nums text-income font-bold"><?= format_currency($totalIncome) ?></span>
    </div>
    <a href="<?= base_url('income/add.php') ?>" class="btn btn-primary-income">+ Add Income</a>
  </div>
</div>

<div class="table-panel">
  <div class="table-responsive">
    <?php if (empty($incomeList)): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24" width="40" height="40" stroke="currentColor" fill="none" class="empty-icon text-income">
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
          <polyline points="17 6 23 6 23 12"></polyline>
        </svg>
        <h3>No Income Recorded Yet</h3>
        <p>Log your salary, freelance retainers, investment returns, or bonus credits to track your cashflow.</p>
        <a href="<?= base_url('income/add.php') ?>" class="btn btn-primary-income">+ Record First Income</a>
      </div>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Source / Category</th>
            <th>Payment Method</th>
            <th>Notes</th>
            <th class="text-right">Amount</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($incomeList as $row): ?>
            <tr>
              <td>
                <span class="font-mono text-sm"><?= date('d M Y', strtotime($row['transaction_date'])) ?></span>
              </td>
              <td>
                <span class="font-medium"><?= e($row['category_name'] ?? 'Uncategorized') ?></span>
              </td>
              <td>
                <span class="text-muted"><?= e($row['payment_method']) ?></span>
              </td>
              <td>
                <span class="cell-notes" title="<?= e($row['notes']) ?>"><?= e($row['notes'] ?: '-') ?></span>
              </td>
              <td class="text-right font-mono tabular-nums font-semibold text-income">
                + <?= format_currency($row['amount']) ?>
              </td>
              <td class="text-right">
                <div class="action-buttons">
                  <a href="<?= base_url('income/edit.php?id=' . $row['id']) ?>" class="btn-icon-subtle" title="Edit entry">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                  </a>
                  <button type="button" class="btn-icon-subtle btn-delete-trigger" 
                    data-action="<?= base_url('income/delete.php') ?>" 
                    data-id="<?= $row['id'] ?>"
                    data-name="<?= e($row['category_name']) ?> (<?= format_currency($row['amount']) ?>)"
                    title="Delete entry">
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
