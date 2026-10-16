<?php
/**
 * User Registration
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect(base_url('dashboard.php'));
}

$errors = [];
$name = '';
$email = '';
$currency = '₹';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currency = trim($_POST['currency'] ?? '₹');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Full name is required.';
    } elseif (mb_strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }

    if ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }

    $validCurrencies = ['₹', '$', '€', '£', '¥', 'AED', 'SAR', 'CAD', 'AUD'];
    if (!in_array($currency, $validCurrencies, true)) {
        $currency = '₹';
    }

    // Check email uniqueness if format is valid
    if (empty($errors['email'])) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'An account with this email already exists.';
        }
    }

    if (empty($errors)) {
        $pdo = getDBConnection();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, currency, created_at) VALUES (:name, :email, :password_hash, :currency, NOW())");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':currency' => $currency
            ]);

            $newUserId = (int)$pdo->lastInsertId();

            // Seed default categories for this new user
            seed_user_default_categories($pdo, $newUserId);

            $pdo->commit();

            // Log in newly registered user
            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user'] = [
                'id' => $newUserId,
                'name' => $name,
                'email' => $email,
                'currency' => $currency,
                'created_at' => date('Y-m-d H:i:s')
            ];

            set_flash('success', 'Account created successfully! Default income and expense categories have been configured.');
            redirect(base_url('dashboard.php'));
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Registration Error: ' . $e->getMessage());
            $errors['general'] = 'An unexpected error occurred during registration. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account · Daily Expense Management System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="auth-body">
  <div class="auth-card-wrapper">
    <div class="auth-header">
      <div class="auth-brand">
        <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect width="20" height="14" x="2" y="5" rx="2"/>
          <line x1="2" x2="22" y1="10" y2="10"/>
        </svg>
        <span>ExpensePulse</span>
      </div>
      <h1 class="auth-title">Create an account</h1>
      <p class="auth-subtitle">Start tracking your income, expenses, and savings goals today.</p>
    </div>

    <?php if (isset($errors['general'])): ?>
      <div class="alert alert-danger" role="alert">
        <?= e($errors['general']) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('auth/register.php') ?>" class="auth-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

      <div class="form-group">
        <label for="name" class="form-label">Full Name</label>
        <input 
          type="text" 
          id="name" 
          name="name" 
          class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
          value="<?= e($name) ?>" 
          placeholder="e.g. Sarah Jenkins"
          required 
          autocomplete="name"
          autofocus
        >
        <?php if (isset($errors['name'])): ?>
          <span class="form-error"><?= e($errors['name']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
          value="<?= e($email) ?>" 
          placeholder="e.g. sarah@example.com"
          required 
          autocomplete="email"
        >
        <?php if (isset($errors['email'])): ?>
          <span class="form-error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="currency" class="form-label">Preferred Currency Symbol</label>
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
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password (min 6 characters)</label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
          placeholder="••••••••"
          required 
          autocomplete="new-password"
        >
        <?php if (isset($errors['password'])): ?>
          <span class="form-error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password_confirm" class="form-label">Confirm Password</label>
        <input 
          type="password" 
          id="password_confirm" 
          name="password_confirm" 
          class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" 
          placeholder="••••••••"
          required 
          autocomplete="new-password"
        >
        <?php if (isset($errors['password_confirm'])): ?>
          <span class="form-error"><?= e($errors['password_confirm']) ?></span>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Create Free Account</button>
    </form>

    <div class="auth-footer">
      <span>Already have an account?</span>
      <a href="<?= base_url('auth/login.php') ?>" class="auth-link">Sign In</a>
    </div>
  </div>
</body>
</html>
