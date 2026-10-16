/**
 * Daily Expense Management System
 * Core Client Application Script (Vanilla ES6+)
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSidebar();
  initDeleteModals();
  initToastAutoDismiss();
  initCustomDateToggle();
});

/**
 * 1. Dark / Light Theme System with localStorage persistence
 */
function initTheme() {
  const toggleBtn = document.getElementById('theme-toggle-btn');
  const sunIcon = document.getElementById('theme-sun-icon');
  const moonIcon = document.getElementById('theme-moon-icon');

  if (!toggleBtn) return;

  const savedTheme = localStorage.getItem('expense_pulse_theme') || 'light';
  applyTheme(savedTheme);

  toggleBtn.addEventListener('click', () => {
    const isDark = document.body.classList.contains('dark-mode');
    const newTheme = isDark ? 'light' : 'dark';
    applyTheme(newTheme);
    localStorage.setItem('expense_pulse_theme', newTheme);
  });

  function applyTheme(theme) {
    if (theme === 'dark') {
      document.body.classList.add('dark-mode');
      if (sunIcon) sunIcon.classList.add('hidden');
      if (moonIcon) moonIcon.classList.remove('hidden');
    } else {
      document.body.classList.remove('dark-mode');
      if (sunIcon) sunIcon.classList.remove('hidden');
      if (moonIcon) moonIcon.classList.add('hidden');
    }
  }
}

/**
 * 2. Mobile Sidebar Collapse / Open Drawer
 */
function initSidebar() {
  const sidebar = document.getElementById('app-sidebar');
  const openBtn = document.getElementById('sidebar-open-btn');
  const closeBtn = document.getElementById('sidebar-close-btn');

  if (!sidebar) return;

  if (openBtn) {
    openBtn.addEventListener('click', () => {
      sidebar.classList.add('open');
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      sidebar.classList.remove('open');
    });
  }

  // Close sidebar on outer click on mobile
  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
      if (!sidebar.contains(e.target) && openBtn && !openBtn.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    }
  });
}

/**
 * 3. Delete Confirmation Modal System
 */
function initDeleteModals() {
  const deleteTriggers = document.querySelectorAll('.btn-delete-trigger');
  const modal = document.getElementById('confirm-modal');
  const modalForm = document.getElementById('modal-confirm-form');
  const modalItemId = document.getElementById('modal-item-id');
  const modalMessage = document.getElementById('modal-message');

  if (!modal || !modalForm) return;

  deleteTriggers.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const actionUrl = btn.getAttribute('data-action');
      const itemId = btn.getAttribute('data-id');
      const itemName = btn.getAttribute('data-name') || 'this item';

      modalForm.setAttribute('action', actionUrl);
      if (modalItemId) modalItemId.value = itemId;
      if (modalMessage) {
        modalMessage.innerHTML = `Are you sure you want to permanently delete <strong>${escapeHtml(itemName)}</strong>? This operation cannot be reversed.`;
      }

      openConfirmModal();
    });
  });
}

function openConfirmModal() {
  const modal = document.getElementById('confirm-modal');
  if (modal) {
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
  }
}

function closeConfirmModal() {
  const modal = document.getElementById('confirm-modal');
  if (modal) {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
  }
}

// Close modal on Escape key press
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeConfirmModal();
    const catModal = document.getElementById('category-modal');
    if (catModal && typeof closeCategoryModal === 'function') {
      closeCategoryModal();
    }
    const budgetModal = document.getElementById('budget-modal');
    if (budgetModal && typeof closeBudgetModal === 'function') {
      closeBudgetModal();
    }
  }
});

/**
 * 4. Toast Notifications Auto-Dismiss
 */
function initToastAutoDismiss() {
  const toasts = document.querySelectorAll('.toast');
  toasts.forEach((toast) => {
    setTimeout(() => {
      toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-10px)';
      setTimeout(() => toast.remove(), 300);
    }, 4500);
  });
}

/**
 * 5. Custom Date Range Toggle on Dashboard
 */
function initCustomDateToggle() {
  const toggleBtn = document.getElementById('toggle-custom-date-btn');
  const customForm = document.getElementById('custom-date-form');

  if (toggleBtn && customForm) {
    toggleBtn.addEventListener('click', () => {
      customForm.classList.toggle('open');
      if (customForm.classList.contains('open')) {
        const fromInput = customForm.querySelector('#from');
        if (fromInput) fromInput.focus();
      }
    });
  }
}

/**
 * Helper to escape HTML characters in dynamic strings
 */
function escapeHtml(str) {
  if (!str) return '';
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}
