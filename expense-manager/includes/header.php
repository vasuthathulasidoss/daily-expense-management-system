<?php
/**
 * Global Header & Sidebar Navigation
 * Daily Expense Management System
 */

declare(strict_types=1);

if (!isset($pdo)) {
    require_once __DIR__ . '/../config/db.php';
}
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Daily Expense Management System';
$currentPage = $currentPage ?? '';
$user = current_user();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> · Expense Manager</title>
  
  <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Application CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
  
  <!-- Chart.js via CDN for visual analytics -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
</head>
<body class="app-body">
  <!-- Toast Notification Container -->
  <div id="toast-container" class="toast-container" aria-live="polite">
    <?php if ($flash): ?>
      <div class="toast toast-<?= e($flash['type']) ?>" role="alert">
        <span class="toast-message"><?= e($flash['message']) ?></span>
        <button type="button" class="toast-close" onclick="this.parentElement.remove()" aria-label="Close notification">&times;</button>
      </div>
    <?php endif; ?>
  </div>

  <div class="app-layout">
    <!-- Sidebar Navigation -->
    <aside id="app-sidebar" class="app-sidebar">
      <div class="sidebar-brand">
        <a href="<?= base_url('dashboard.php') ?>" class="brand-link">
          <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="20" height="14" x="2" y="5" rx="2"/>
            <line x1="2" x2="22" y1="10" y2="10"/>
          </svg>
          <span class="brand-text">ExpensePulse</span>
        </a>
        <button id="sidebar-close-btn" class="sidebar-toggle-btn mobile-only" aria-label="Close menu">&times;</button>
      </div>

      <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
        <div class="user-info">
          <span class="user-name"><?= e($user['name'] ?? 'User') ?></span>
          <span class="user-currency">Currency: <?= e(current_currency()) ?></span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a href="<?= base_url('dashboard.php') ?>" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
          </svg>
          <span>Dashboard</span>
        </a>

        <a href="<?= base_url('income/index.php') ?>" class="nav-item <?= $currentPage === 'income' ? 'active' : '' ?>">
          <svg class="nav-icon text-income" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
            <polyline points="17 6 23 6 23 12"></polyline>
          </svg>
          <span>Income</span>
        </a>

        <a href="<?= base_url('expenses/index.php') ?>" class="nav-item <?= $currentPage === 'expenses' ? 'active' : '' ?>">
          <svg class="nav-icon text-expense" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
            <polyline points="17 18 23 18 23 12"></polyline>
          </svg>
          <span>Expenses</span>
        </a>

        <a href="<?= base_url('transactions.php') ?>" class="nav-item <?= $currentPage === 'transactions' ? 'active' : '' ?>">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
          </svg>
          <span>All Transactions</span>
        </a>

        <a href="<?= base_url('categories/index.php') ?>" class="nav-item <?= $currentPage === 'categories' ? 'active' : '' ?>">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 9h16M4 15h16M10 3L8 21M16 3l-2 18"></path>
          </svg>
          <span>Categories</span>
        </a>

        <a href="<?= base_url('budgets.php') ?>" class="nav-item <?= $currentPage === 'budgets' ? 'active' : '' ?>">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 6v6l4 2"></path>
          </svg>
          <span>Monthly Budgets</span>
        </a>

        <a href="<?= base_url('reports.php') ?>" class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
          <span>Reports & Trends</span>
        </a>

        <a href="<?= base_url('profile.php') ?>" class="nav-item <?= $currentPage === 'profile' ? 'active' : '' ?>">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span>Settings & Profile</span>
        </a>
      </nav>

      <div class="sidebar-footer">
        <a href="<?= base_url('auth/logout.php') ?>" class="nav-item logout-link">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          <span>Log Out</span>
        </a>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="app-main">
      <!-- Top Bar -->
      <header class="app-header">
        <div class="header-left">
          <button id="sidebar-open-btn" class="sidebar-toggle-btn mobile-only" aria-label="Open sidebar menu">
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
          </button>
          <div class="header-breadcrumbs">
            <span class="crumb-root">Finance</span>
            <span class="crumb-sep">/</span>
            <span class="crumb-current"><?= e($pageTitle) ?></span>
          </div>
        </div>

        <div class="header-right">
          <!-- Theme Toggle -->
          <button id="theme-toggle-btn" class="btn btn-icon" title="Toggle Light / Dark mode" aria-label="Toggle theme">
            <svg id="theme-sun-icon" class="theme-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="5"></circle>
              <line x1="12" y1="1" x2="12" y2="3"></line>
              <line x1="12" y1="21" x2="12" y2="23"></line>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
              <line x1="1" y1="12" x2="3" y2="12"></line>
              <line x1="21" y1="12" x2="23" y2="12"></line>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg id="theme-moon-icon" class="theme-icon hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
          </button>

          <!-- Quick Actions -->
          <div class="header-actions">
            <a href="<?= base_url('income/add.php') ?>" class="btn btn-sm btn-outline-income">+ Income</a>
            <a href="<?= base_url('expenses/add.php') ?>" class="btn btn-sm btn-primary-expense">+ Expense</a>
          </div>
        </div>
      </header>

      <!-- Viewport Body Container -->
      <main class="page-container">
