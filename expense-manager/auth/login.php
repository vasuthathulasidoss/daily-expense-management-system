<?php
/**
 * User Login
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect(base_url('dashboard.php'));
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, password_hash, currency, created_at FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'currency' => $user['currency'] ?? '₹',
                'created_at' => $user['created_at']
            ];

            set_flash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
            redirect(base_url('dashboard.php'));
        } else {
            $errors['auth'] = 'Invalid email or password. Please try again.';
        }
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In · Daily Expense Management System</title>
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
      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-subtitle">Sign in to manage your income, expenses, and monthly budget.</p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
        <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($errors['auth'])): ?>
      <div class="alert alert-danger" role="alert">
        <?= e($errors['auth']) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('auth/login.php') ?>" class="auth-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

      <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
          value="<?= e($email) ?>" 
          placeholder="e.g. demo@example.com"
          required 
          autocomplete="email"
          autofocus
        >
        <?php if (isset($errors['email'])): ?>
          <span class="form-error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <div class="form-label-row">
          <label for="password" class="form-label">Password</label>
        </div>
        <input 
          type="password" 
          id="password" 
          name="password" 
          class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
          placeholder="••••••••"
          required 
          autocomplete="current-password"
        >
        <?php if (isset($errors['password'])): ?>
          <span class="form-error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>

    <!-- Demo Credentials Quick Helper -->
    <div class="auth-demo-box">
      <div class="demo-box-header">
        <span class="demo-badge">Demo Account</span>
        <button type="button" class="btn-demo-fill" onclick="fillDemoCredentials()">Use Demo Account</button>
      </div>
      <p class="demo-info">
        <strong>Email:</strong> <code>demo@example.com</code><br>
        <strong>Password:</strong> <code>password123</code>
      </p>
    </div>

    <div class="auth-footer">
      <span>Don't have an account yet?</span>
      <a href="<?= base_url('auth/register.php') ?>" class="auth-link">Create Account</a>
    </div>
  </div>

  <script>
    function fillDemoCredentials() {
      document.getElementById('email').value = 'demo@example.com';
      document.getElementById('password').value = 'password123';
    }
  </script>
</body>
</html>
