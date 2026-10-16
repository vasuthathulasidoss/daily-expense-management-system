<?php
/**
 * Add Expense Entry
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Add Expense';
$currentPage = 'expenses';
$userId = (int)current_user_id();
$pdo = getDBConnection();

// Fetch Expense Categories for current user
$catStmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = :user_id AND type = 'expense' ORDER BY name ASC");
$catStmt->execute([':user_id' => $userId]);
$categories = $catStmt->fetchAll();

$errors = [];
$amount = '';
$categoryId = '';
$paymentMethod = 'UPI';
$date = date('Y-m-d');
$notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $amountRaw = trim($_POST['amount'] ?? '');
    $categoryIdRaw = trim($_POST['category_id'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'UPI');
    $date = trim($_POST['transaction_date'] ?? date('Y-m-d'));
    $notes = trim($_POST['notes'] ?? '');

    // Amount Validation
    if ($amountRaw === '' || !is_numeric($amountRaw) || (float)$amountRaw <= 0) {
        $errors['amount'] = 'Please enter a valid expense amount greater than 0.';
    } else {
        $amount = (float)$amountRaw;
    }

    // Category Validation
    if ($categoryIdRaw === '' || !ctype_digit($categoryIdRaw)) {
        $errors['category_id'] = 'Please select an expense category.';
    } else {
        $categoryId = (int)$categoryIdRaw;
        $checkCat = $pdo->prepare("SELECT id FROM categories WHERE id = :id AND user_id = :user_id AND type = 'expense' LIMIT 1");
        $checkCat->execute([':id' => $categoryId, ':user_id' => $userId]);
        if (!$checkCat->fetch()) {
            $errors['category_id'] = 'Selected category is invalid.';
        }
    }

    // Date Validation
    if (!is_valid_date($date)) {
        $errors['transaction_date'] = 'Please provide a valid date (YYYY-MM-DD).';
    }

    $validMethods = ['Cash', 'UPI', 'Card', 'Bank', 'Cheque', 'Other'];
    if (!in_array($paymentMethod, $validMethods, true)) {
        $paymentMethod = 'Cash';
    }

    if (empty($errors)) {
        $insert = $pdo->prepare("
            INSERT INTO transactions (user_id, category_id, type, amount, payment_method, transaction_date, notes, created_at)
            VALUES (:user_id, :category_id, 'expense', :amount, :payment_method, :transaction_date, :notes, NOW())
        ");
        $insert->execute([
            ':user_id' => $userId,
            ':category_id' => $categoryId,
            ':amount' => $amount,
            ':payment_method' => $paymentMethod,
            ':transaction_date' => $date,
            ':notes' => $notes ?: null
        ]);

        set_flash('success', 'Expense entry of ' . format_currency($amount) . ' added successfully.');
        redirect(base_url('expenses/index.php'));
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-container-card">
  <div class="card-header-styled">
    <div>
      <h1 class="card-title">Record Expense Entry</h1>
      <p class="card-subtitle">Log money spent on groceries, travel, bills, dining, or shopping.</p>
    </div>
    <a href="<?= base_url('expenses/index.php') ?>" class="btn btn-secondary">&larr; Back to Expenses</a>
  </div>

  <form method="POST" action="<?= base_url('expenses/add.php') ?>" class="entry-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

    <div class="form-grid-2">
      <!-- Amount -->
      <div class="form-group">
        <label for="amount" class="form-label required">Amount (<?= e(current_currency()) ?>)</label>
        <div class="input-prefix-wrapper">
          <span class="input-prefix"><?= e(current_currency()) ?></span>
          <input 
            type="number" 
            step="0.01" 
            min="0.01" 
            id="amount" 
            name="amount" 
            class="form-control form-control-lg font-mono tabular-nums <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" 
            value="<?= e((string)$amount) ?>" 
            placeholder="0.00" 
            required 
            autofocus
          >
        </div>
        <?php if (isset($errors['amount'])): ?>
          <span class="form-error"><?= e($errors['amount']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Category -->
      <div class="form-group">
        <div class="form-label-row">
          <label for="category_id" class="form-label required">Expense Category</label>
          <a href="<?= base_url('categories/index.php') ?>" class="form-helper-link">+ New Category</a>
        </div>
        <select id="category_id" name="category_id" class="form-select form-control-lg <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" required>
          <option value="">-- Choose Category --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= (string)$categoryId === (string)$cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['category_id'])): ?>
          <span class="form-error"><?= e($errors['category_id']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Date -->
      <div class="form-group">
        <label for="transaction_date" class="form-label required">Transaction Date</label>
        <input 
          type="date" 
          id="transaction_date" 
          name="transaction_date" 
          class="form-control <?= isset($errors['transaction_date']) ? 'is-invalid' : '' ?>" 
          value="<?= e($date) ?>" 
          max="<?= date('Y-m-d') ?>"
          required
        >
        <?php if (isset($errors['transaction_date'])): ?>
          <span class="form-error"><?= e($errors['transaction_date']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Payment Method -->
      <div class="form-group">
        <label for="payment_method" class="form-label required">Payment Method</label>
        <select id="payment_method" name="payment_method" class="form-select" required>
          <option value="UPI" <?= $paymentMethod === 'UPI' ? 'selected' : '' ?>>UPI / Google Pay / PhonePe</option>
          <option value="Card" <?= $paymentMethod === 'Card' ? 'selected' : '' ?>>Debit / Credit Card</option>
          <option value="Cash" <?= $paymentMethod === 'Cash' ? 'selected' : '' ?>>Cash</option>
          <option value="Bank" <?= $paymentMethod === 'Bank' ? 'selected' : '' ?>>Net Banking</option>
          <option value="Other" <?= $paymentMethod === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
      </div>
    </div>

    <!-- Notes -->
    <div class="form-group">
      <label for="notes" class="form-label">Notes & Description (Optional)</label>
      <textarea 
        id="notes" 
        name="notes" 
        rows="3" 
        class="form-control" 
        placeholder="e.g. Weekly vegetable haul, Uber to office, or broadband bill"
      ><?= e($notes) ?></textarea>
    </div>

    <div class="form-actions-row">
      <a href="<?= base_url('expenses/index.php') ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary-expense">Save Expense Entry</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
