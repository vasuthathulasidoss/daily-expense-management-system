<?php
/**
 * User Profile & Settings
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Profile & Settings';
$currentPage = 'profile';
$userId = (int)current_user_id();
$pdo = getDBConnection();

$profileErrors = [];
$passwordErrors = [];

// Fetch fresh user record
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$userRecord = $stmt->fetch();

$name = $userRecord['name'];
$email = $userRecord['email'];
$currency = $userRecord['currency'] ?? '₹';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';

    // Action 1: Update Profile Details (Name, Email, Currency)
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currency = trim($_POST['currency'] ?? '₹');

        if (empty($name)) {
            $profileErrors['name'] = 'Full name cannot be blank.';
        } elseif (mb_strlen($name) < 2) {
            $profileErrors['name'] = 'Name must be at least 2 characters.';
        }

        if (empty($email)) {
            $profileErrors['email'] = 'Email address cannot be blank.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profileErrors['email'] = 'Please enter a valid email address.';
        } else {
            // Check email uniqueness excluding self
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
            $checkEmail->execute([':email' => $email, ':id' => $userId]);
            if ($checkEmail->fetch()) {
                $profileErrors['email'] = 'This email address is already registered by another account.';
            }
        }

        $validCurrencies = ['₹', '$', '€', '£', '¥', 'AED', 'SAR', 'CAD', 'AUD'];
        if (!in_array($currency, $validCurrencies, true)) {
            $currency = '₹';
        }

        if (empty($profileErrors)) {
            $up = $pdo->prepare("UPDATE users SET name = :name, email = :email, currency = :currency WHERE id = :id");
            $up->execute([
                ':name' => $name,
                ':email' => $email,
                ':currency' => $currency,
                ':id' => $userId
            ]);

            // Refresh session
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['currency'] = $currency;

            set_flash('success', 'Profile and currency settings updated successfully.');
            redirect(base_url('profile.php'));
        }
    }

    // Action 2: Change Password
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword)) {
            $passwordErrors['current_password'] = 'Please enter your current password.';
        } elseif (!password_verify($currentPassword, $userRecord['password_hash'])) {
            $passwordErrors['current_password'] = 'The current password you entered is incorrect.';
        }

        if (empty($newPassword)) {
            $passwordErrors['new_password'] = 'Please enter a new password.';
        } elseif (strlen($newPassword) < 6) {
            $passwordErrors['new_password'] = 'New password must be at least 6 characters.';
        }

        if ($newPassword !== $confirmPassword) {
            $passwordErrors['confirm_password'] = 'New passwords do not match.';
        }

        if (empty($passwordErrors)) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $upPass = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $upPass->execute([':hash' => $newHash, ':id' => $userId]);

            set_flash('success', 'Your password has been changed successfully.');
            redirect(base_url('profile.php'));
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header-row">
  <div>
    <h1 class="page-heading">Account Settings & Profile</h1>
    <p class="page-subheading">Update your personal preferences, preferred currency symbol, and account credentials.</p>
  </div>
</div>

<div class="settings-grid">
  <!-- Profile & Currency Information Card -->
  <div class="form-container-card">
    <div class="card-header-styled">
      <div>
        <h2 class="card-title">Personal Details & Currency</h2>
        <p class="card-subtitle">Manage your display name, contact email, and active currency symbol.</p>
      </div>
    </div>

    <form method="POST" action="<?= base_url('profile.php') ?>" class="entry-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="action" value="update_profile">

      <div class="form-group">
        <label for="name" class="form-label required">Display Name</label>
        <input 
          type="text" 
          id="name" 
          name="name" 
          class="form-control <?= isset($profileErrors['name']) ? 'is-invalid' : '' ?>" 
          value="<?= e($name) ?>" 
          required
        >
        <?php if (isset($profileErrors['name'])): ?>
          <span class="form-error"><?= e($profileErrors['name']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="email" class="form-label required">Email Address</label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          class="form-control <?= isset($profileErrors['email']) ? 'is-invalid' : '' ?>" 
          value="<?= e($email) ?>" 
          required
        >
        <?php if (isset($profileErrors['email'])): ?>
          <span class="form-error"><?= e($profileErrors['email']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="currency" class="form-label required">Default Currency Symbol</label>
        <select id="currency" name="currency" class="form-select">
          <option value="₹" <?= $currency === '₹' ? 'selected' : '' ?>>₹ (INR - Indian Rupee)</option>
          <option value="$" <?= $currency === '$' ? 'selected' : '' ?>>$ (USD - US Dollar)</option>
          <option value="€" <?= $currency === '€' ? 'selected' : '' ?>>€ (EUR - Euro)</option>
          <option value="£" <?= $currency === '£' ? 'selected' : '' ?>>£ (GBP - British Pound)</option>
          <option value="¥" <?= $currency === '¥' ? 'selected' : '' ?>>¥ (JPY / CNY)</option>
          <option value="AED" <?= $currency === 'AED' ? 'selected' : '' ?>>AED (UAE Dirham)</option>
          <option value="SAR" <?= $currency === 'SAR' ? 'selected' : '' ?>>SAR (Saudi Riyal)</option>
          <option value="CAD" <?= $currency === 'CAD' ? 'selected' : '' ?>>CAD ($)</option>
          <option value="AUD" <?= $currency === 'AUD' ? 'selected' : '' ?>>AUD ($)</option>
        </select>
        <span class="form-hint">Used throughout all cards, tables, exports, and chart tooltips.</span>
      </div>

      <div class="form-actions-row">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>

  <!-- Security & Password Change Card -->
  <div class="form-container-card">
    <div class="card-header-styled">
      <div>
        <h2 class="card-title">Security & Password</h2>
        <p class="card-subtitle">Ensure your account uses a strong, secure passphrase.</p>
      </div>
    </div>

    <form method="POST" action="<?= base_url('profile.php') ?>" class="entry-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="action" value="change_password">

      <div class="form-group">
        <label for="current_password" class="form-label required">Current Password</label>
        <input 
          type="password" 
          id="current_password" 
          name="current_password" 
          class="form-control <?= isset($passwordErrors['current_password']) ? 'is-invalid' : '' ?>" 
          placeholder="••••••••"
          required
        >
        <?php if (isset($passwordErrors['current_password'])): ?>
          <span class="form-error"><?= e($passwordErrors['current_password']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="new_password" class="form-label required">New Password (min 6 characters)</label>
        <input 
          type="password" 
          id="new_password" 
          name="new_password" 
          class="form-control <?= isset($passwordErrors['new_password']) ? 'is-invalid' : '' ?>" 
          placeholder="••••••••"
          required
        >
        <?php if (isset($passwordErrors['new_password'])): ?>
          <span class="form-error"><?= e($passwordErrors['new_password']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="confirm_password" class="form-label required">Confirm New Password</label>
        <input 
          type="password" 
          id="confirm_password" 
          name="confirm_password" 
          class="form-control <?= isset($passwordErrors['confirm_password']) ? 'is-invalid' : '' ?>" 
          placeholder="••••••••"
          required
        >
        <?php if (isset($passwordErrors['confirm_password'])): ?>
          <span class="form-error"><?= e($passwordErrors['confirm_password']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-actions-row">
        <button type="submit" class="btn btn-primary">Update Password</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
