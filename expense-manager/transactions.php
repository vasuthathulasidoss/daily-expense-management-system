<?php
/**
 * Combined Transactions Page (Search, Filters, Pagination, CSV Export)
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'All Transactions';
$currentPage = 'transactions';
$userId = (int)current_user_id();
$pdo = getDBConnection();

// -------------------------------------------------------------
// 1. Gather & Sanitize Filter Parameters
// -------------------------------------------------------------
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

// -------------------------------------------------------------
// 2. Build Dynamic SQL Query & Parameter Bindings
// -------------------------------------------------------------
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

// Determine Order By Clause
$sortColumns = [
    'date' => 't.transaction_date',
    'amount' => 't.amount',
    'category' => 'c.name',
    'id' => 't.id'
];
$orderByColumn = $sortColumns[$sortBy] ?? 't.transaction_date';
$orderSql = "ORDER BY {$orderByColumn} {$sortDir}, t.id DESC";

// -------------------------------------------------------------
// 3. Handle CSV Export If Requested
// -------------------------------------------------------------
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $exportStmt = $pdo->prepare("
        SELECT t.id, t.transaction_date, t.type, c.name AS category_name, t.payment_method, t.amount, t.notes, t.created_at
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE {$whereSql}
        {$orderSql}
    ");
    $exportStmt->execute($params);
    $exportRows = $exportStmt->fetchAll();

    $currencySymbol = current_currency();
    $filename = 'transactions_export_' . date('Y-m-d_His') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    // Output BOM for Excel UTF-8 compatibility
    fputs($output, "\xEF\xBB\xBF");

    // CSV Header row
    fputcsv($output, ['ID', 'Date', 'Type', 'Category', 'Payment Method', 'Amount', 'Currency', 'Notes', 'Created At']);

    foreach ($exportRows as $row) {
        fputcsv($output, [
            $row['id'],
            $row['transaction_date'],
            strtoupper($row['type']),
            $row['category_name'] ?? 'Uncategorized',
            $row['payment_method'],
            number_format((float)$row['amount'], 2, '.', ''),
            $currencySymbol,
            $row['notes'] ?? '',
            $row['created_at']
        ]);
    }

    fclose($output);
    exit;
}

// -------------------------------------------------------------
// 4. Get Total Count for Pagination
// -------------------------------------------------------------
$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE {$whereSql}
");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// -------------------------------------------------------------
// 5. Fetch Filtered & Paginated Records
// -------------------------------------------------------------
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

// -------------------------------------------------------------
// 6. Fetch User Categories for Filter Dropdown
// -------------------------------------------------------------
$allCatsStmt = $pdo->prepare("SELECT id, name, type FROM categories WHERE user_id = :user_id ORDER BY type ASC, name ASC");
$allCatsStmt->execute([':user_id' => $userId]);
$userCategories = $allCatsStmt->fetchAll();

// Helper to construct query string preserving other filters
function build_filter_url(array $overrides = []): string
{
    $current = $_GET;
    unset($current['export']);
    $merged = array_merge($current, $overrides);
    return 'transactions.php?' . http_build_query($merged);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header-row">
  <div>
    <h1 class="page-heading">All Transactions</h1>
    <p class="page-subheading">Comprehensive ledger of all incoming and outgoing financial activity.</p>
  </div>
  <div class="page-header-actions">
    <!-- Export CSV Button (retains current filters) -->
    <a href="<?= base_url(build_filter_url(['export' => 'csv'])) ?>" class="btn btn-outline" title="Export current filtered results to CSV">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
        <polyline points="7 10 12 15 17 10"></polyline>
        <line x1="12" y1="15" x2="12" y2="3"></line>
      </svg>
      <span>Export CSV</span>
    </a>
    <a href="<?= base_url('income/add.php') ?>" class="btn btn-outline-income">+ Income</a>
    <a href="<?= base_url('expenses/add.php') ?>" class="btn btn-primary-expense">+ Expense</a>
  </div>
</div>

<!-- Search & Filter Bar -->
<div class="filter-panel">
  <form method="GET" action="<?= base_url('transactions.php') ?>" class="filter-form" id="transaction-filter-form">
    <div class="filter-grid">
      <!-- Search Input -->
      <div class="filter-col">
        <label for="search" class="filter-label">Search Notes / Category</label>
        <div class="search-input-wrapper">
          <svg class="search-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input 
            type="text" 
            id="search" 
            name="search" 
            value="<?= e($search) ?>" 
            placeholder="e.g. groceries, bonus, Uber" 
            class="form-control form-control-sm search-field"
          >
        </div>
      </div>

      <!-- Type Filter -->
      <div class="filter-col">
        <label for="type" class="filter-label">Type</label>
        <select id="type" name="type" class="form-select form-select-sm">
          <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All Types</option>
          <option value="income" <?= $type === 'income' ? 'selected' : '' ?>>Income Only</option>
          <option value="expense" <?= $type === 'expense' ? 'selected' : '' ?>>Expenses Only</option>
        </select>
      </div>

      <!-- Category Filter -->
      <div class="filter-col">
        <label for="category_id" class="filter-label">Category</label>
        <select id="category_id" name="category_id" class="form-select form-select-sm">
          <option value="0">All Categories</option>
          <?php foreach ($userCategories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>>
              [<?= ucfirst($cat['type']) ?>] <?= e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Payment Method Filter -->
      <div class="filter-col">
        <label for="payment_method" class="filter-label">Payment Method</label>
        <select id="payment_method" name="payment_method" class="form-select form-select-sm">
          <option value="all" <?= $paymentMethod === 'all' ? 'selected' : '' ?>>All Methods</option>
          <option value="UPI" <?= $paymentMethod === 'UPI' ? 'selected' : '' ?>>UPI</option>
          <option value="Card" <?= $paymentMethod === 'Card' ? 'selected' : '' ?>>Card</option>
          <option value="Cash" <?= $paymentMethod === 'Cash' ? 'selected' : '' ?>>Cash</option>
          <option value="Bank" <?= $paymentMethod === 'Bank' ? 'selected' : '' ?>>Bank</option>
          <option value="Cheque" <?= $paymentMethod === 'Cheque' ? 'selected' : '' ?>>Cheque</option>
          <option value="Other" <?= $paymentMethod === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
      </div>

      <!-- Date Range -->
      <div class="filter-col">
        <label for="date_from" class="filter-label">From Date</label>
        <input type="date" id="date_from" name="date_from" value="<?= e($dateFrom) ?>" class="form-control form-control-sm">
      </div>

      <div class="filter-col">
        <label for="date_to" class="filter-label">To Date</label>
        <input type="date" id="date_to" name="date_to" value="<?= e($dateTo) ?>" class="form-control form-control-sm">
      </div>
    </div>

    <div class="filter-actions-bar">
      <div class="filter-active-summary">
        Showing <strong><?= count($transactions) ?></strong> of <strong><?= $totalRecords ?></strong> matched records
      </div>
      <div class="filter-btn-group">
        <a href="<?= base_url('transactions.php') ?>" class="btn btn-sm btn-secondary">Reset Filters</a>
        <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
      </div>
    </div>
  </form>
</div>

<!-- Transactions Table Panel -->
<div class="table-panel">
  <div class="table-responsive">
    <?php if (empty($transactions)): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24" width="40" height="40" stroke="currentColor" fill="none" class="empty-icon">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="8" y1="12" x2="16" y2="12"></line>
        </svg>
        <h3>No Matching Transactions Found</h3>
        <p>Try clearing your search query or broadening your date and category filters.</p>
        <a href="<?= base_url('transactions.php') ?>" class="btn btn-sm btn-secondary">Clear All Filters</a>
      </div>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>
              <a href="<?= base_url(build_filter_url(['sort_by' => 'date', 'sort_dir' => $sortBy === 'date' && $sortDir === 'DESC' ? 'asc' : 'desc'])) ?>" class="sort-header">
                Date <?= $sortBy === 'date' ? ($sortDir === 'ASC' ? '▲' : '▼') : '' ?>
              </a>
            </th>
            <th>Type</th>
            <th>
              <a href="<?= base_url(build_filter_url(['sort_by' => 'category', 'sort_dir' => $sortBy === 'category' && $sortDir === 'DESC' ? 'asc' : 'desc'])) ?>" class="sort-header">
                Category <?= $sortBy === 'category' ? ($sortDir === 'ASC' ? '▲' : '▼') : '' ?>
              </a>
            </th>
            <th>Method</th>
            <th>Notes</th>
            <th class="text-right">
              <a href="<?= base_url(build_filter_url(['sort_by' => 'amount', 'sort_dir' => $sortBy === 'amount' && $sortDir === 'DESC' ? 'asc' : 'desc'])) ?>" class="sort-header">
                Amount <?= $sortBy === 'amount' ? ($sortDir === 'ASC' ? '▲' : '▼') : '' ?>
              </a>
            </th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($transactions as $row): ?>
            <tr>
              <td>
                <span class="font-mono text-sm"><?= date('d M Y', strtotime($row['transaction_date'])) ?></span>
              </td>
              <td>
                <span class="type-indicator <?= $row['type'] === 'income' ? 'type-income' : 'type-expense' ?>">
                  <?= ucfirst($row['type']) ?>
                </span>
              </td>
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

  <!-- Pagination Bar (10 per page) -->
  <?php if ($totalPages > 1): ?>
    <div class="pagination-footer">
      <div class="pagination-info">
        Page <span class="font-bold"><?= $page ?></span> of <span class="font-bold"><?= $totalPages ?></span>
      </div>
      <div class="pagination-nav">
        <?php if ($page > 1): ?>
          <a href="<?= base_url(build_filter_url(['page' => 1])) ?>" class="btn-page" title="First page">&laquo; First</a>
          <a href="<?= base_url(build_filter_url(['page' => $page - 1])) ?>" class="btn-page" title="Previous page">&lsaquo; Prev</a>
        <?php endif; ?>

        <?php
          $startPage = max(1, $page - 2);
          $endPage = min($totalPages, $page + 2);
          for ($i = $startPage; $i <= $endPage; $i++):
        ?>
          <a href="<?= base_url(build_filter_url(['page' => $i])) ?>" class="btn-page <?= $i === $page ? 'active' : '' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="<?= base_url(build_filter_url(['page' => $page + 1])) ?>" class="btn-page" title="Next page">Next &rsaquo;</a>
          <a href="<?= base_url(build_filter_url(['page' => $totalPages])) ?>" class="btn-page" title="Last page">Last &raquo;</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
