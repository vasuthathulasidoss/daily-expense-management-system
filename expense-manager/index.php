<?php
/**
 * Application Entry Point
 * Daily Expense Management System
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

// Redirect to dashboard (or login via auth_check)
redirect(base_url('dashboard.php'));
