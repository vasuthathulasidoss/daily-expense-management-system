<?php
/**
 * Category Management
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Category Management';
$currentPage = 'categories';
$userId = (int)current_user_id();
$pdo = getDBConnection();

// Fetch all categories for user with transaction counts
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(t.id) AS transaction_count
    FROM categories c
    LEFT JOIN transactions t ON c.id = t.category_id
    WHERE c.user_id = :user_id
    GROUP BY c.id
    ORDER BY c.type ASC, c.name ASC
");
$stmt->execute([':user_id' => $userId]);
$allCategories = $stmt->fetchAll();

$incomeCategories = [];
$expenseCategories = [];

foreach ($allCategories as $cat) {
    if ($cat['type'] === 'income') {
        $incomeCategories[] = $cat;
    } else {
        $expenseCategories[] = $cat;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header-row">
  <div>
    <h1 class="page-heading">Categories</h1>
    <p class="page-subheading">Organize income sources and expense categories. Categories with existing transactions are protected from accidental deletion.</p>
  </div>
  <div>
    <button type="button" class="btn btn-primary" onclick="openCategoryModal()">+ Add New Category</button>
  </div>
</div>

<div class="categories-grid">
  <!-- Expense Categories Panel -->
  <div class="table-panel">
    <div class="panel-header">
      <div class="panel-title-group">
        <h2 class="panel-title">Expense Categories</h2>
        <span class="panel-subtitle"><?= count($expenseCategories) ?> active categories</span>
      </div>
      <button type="button" class="btn btn-sm btn-outline-expense" onclick="openCategoryModal('expense')">+ Add Expense Category</button>
    </div>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Category Name</th>
            <th>Type</th>
            <th class="text-center">Linked Transactions</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($expenseCategories as $cat): ?>
            <tr>
              <td>
                <span class="category-indicator cat-expense"></span>
                <span class="font-medium"><?= e($cat['name']) ?></span>
              </td>
              <td><span class="text-xs text-muted uppercase">Expense</span></td>
              <td class="text-center font-mono tabular-nums">
                <?php if ((int)$cat['transaction_count'] > 0): ?>
                  <a href="<?= base_url('transactions.php?category_id=' . $cat['id']) ?>" class="badge-link" title="Filter transactions">
                    <?= (int)$cat['transaction_count'] ?> records
                  </a>
                <?php else: ?>
                  <span class="text-muted">0 records</span>
                <?php endif; ?>
              </td>
              <td class="text-right">
                <div class="action-buttons">
                  <button type="button" class="btn-icon-subtle" 
                    onclick="editCategory(<?= $cat['id'] ?>, '<?= e(addslashes($cat['name'])) ?>', 'expense')" 
                    title="Rename category">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                  </button>

                  <?php if ((int)$cat['transaction_count'] > 0): ?>
                    <button type="button" class="btn-icon-subtle disabled-btn" 
                      title="Cannot delete category with <?= (int)$cat['transaction_count'] ?> linked transactions" disabled>
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn-icon-subtle btn-delete-trigger" 
                      data-action="<?= base_url('categories/delete.php') ?>" 
                      data-id="<?= $cat['id'] ?>"
                      data-name="<?= e($cat['name']) ?> Category"
                      title="Delete category">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Income Categories Panel -->
  <div class="table-panel">
    <div class="panel-header">
      <div class="panel-title-group">
        <h2 class="panel-title">Income Categories</h2>
        <span class="panel-subtitle"><?= count($incomeCategories) ?> active sources</span>
      </div>
      <button type="button" class="btn btn-sm btn-outline-income" onclick="openCategoryModal('income')">+ Add Income Category</button>
    </div>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Category Name</th>
            <th>Type</th>
            <th class="text-center">Linked Transactions</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($incomeCategories as $cat): ?>
            <tr>
              <td>
                <span class="category-indicator cat-income"></span>
                <span class="font-medium"><?= e($cat['name']) ?></span>
              </td>
              <td><span class="text-xs text-muted uppercase">Income</span></td>
              <td class="text-center font-mono tabular-nums">
                <?php if ((int)$cat['transaction_count'] > 0): ?>
                  <a href="<?= base_url('transactions.php?category_id=' . $cat['id']) ?>" class="badge-link" title="Filter transactions">
                    <?= (int)$cat['transaction_count'] ?> records
                  </a>
                <?php else: ?>
                  <span class="text-muted">0 records</span>
                <?php endif; ?>
              </td>
              <td class="text-right">
                <div class="action-buttons">
                  <button type="button" class="btn-icon-subtle" 
                    onclick="editCategory(<?= $cat['id'] ?>, '<?= e(addslashes($cat['name'])) ?>', 'income')" 
                    title="Rename category">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                  </button>

                  <?php if ((int)$cat['transaction_count'] > 0): ?>
                    <button type="button" class="btn-icon-subtle disabled-btn" 
                      title="Cannot delete category with <?= (int)$cat['transaction_count'] ?> linked transactions" disabled>
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn-icon-subtle btn-delete-trigger" 
                      data-action="<?= base_url('categories/delete.php') ?>" 
                      data-id="<?= $cat['id'] ?>"
                      data-name="<?= e($cat['name']) ?> Category"
                      title="Delete category">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Category Modal (Add / Edit) -->
<div id="category-modal" class="modal-backdrop" aria-hidden="true" role="dialog">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 id="category-modal-title" class="modal-title">Add Category</h3>
      <button type="button" class="modal-close" onclick="closeCategoryModal()">&times;</button>
    </div>
    <form method="POST" action="<?= base_url('categories/save.php') ?>" id="category-form">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" id="cat-id" name="id" value="0">
      
      <div class="modal-body">
        <div class="form-group">
          <label for="cat-name" class="form-label required">Category Name</label>
          <input type="text" id="cat-name" name="name" class="form-control" placeholder="e.g. Subscriptions, Consulting, Gym" required maxlength="100">
        </div>

        <div class="form-group">
          <label for="cat-type" class="form-label required">Type</label>
          <select id="cat-type" name="type" class="form-select" required>
            <option value="expense">Expense Category</option>
            <option value="income">Income Source</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="cat-submit-btn">Save Category</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openCategoryModal(defaultType = 'expense') {
    document.getElementById('cat-id').value = '0';
    document.getElementById('cat-name').value = '';
    document.getElementById('cat-type').value = defaultType;
    document.getElementById('category-modal-title').textContent = 'Add New Category';
    document.getElementById('cat-submit-btn').textContent = 'Create Category';
    document.getElementById('category-modal').classList.add('open');
    document.getElementById('category-modal').setAttribute('aria-hidden', 'false');
    setTimeout(() => document.getElementById('cat-name').focus(), 100);
  }

  function editCategory(id, name, type) {
    document.getElementById('cat-id').value = id;
    document.getElementById('cat-name').value = name;
    document.getElementById('cat-type').value = type;
    document.getElementById('category-modal-title').textContent = 'Edit Category';
    document.getElementById('cat-submit-btn').textContent = 'Update Category';
    document.getElementById('category-modal').classList.add('open');
    document.getElementById('category-modal').setAttribute('aria-hidden', 'false');
    setTimeout(() => document.getElementById('cat-name').focus(), 100);
  }

  function closeCategoryModal() {
    document.getElementById('category-modal').classList.remove('open');
    document.getElementById('category-modal').setAttribute('aria-hidden', 'true');
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
