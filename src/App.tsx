/**
 * Daily Expense Management System - Executive Edition
 * Ultra-Modern Financial Operations Dashboard & XAMPP Code Suite
 */

import React, { useState, useEffect, useMemo } from 'react';
import JSZip from 'jszip';
import {
  Wallet,
  TrendingUp,
  TrendingDown,
  Calendar,
  Download,
  Plus,
  Trash2,
  Edit3,
  Search,
  Filter,
  CheckCircle2,
  AlertTriangle,
  Moon,
  Sun,
  Printer,
  FileCode,
  FolderArchive,
  Copy,
  Check,
  ChevronLeft,
  ChevronRight,
  Shield,
  PieChart as PieIcon,
  BarChart3,
  Layers,
  Settings,
  LogOut,
  Menu,
  X,
  CreditCard,
  Building2,
  Smartphone,
  Banknote,
  ArrowUpRight,
  ArrowDownRight,
  Sparkles,
  SlidersHorizontal,
  RefreshCw,
  ExternalLink,
  ChevronDown
} from 'lucide-react';
import { SOURCE_FILES } from './sourceCatalog';

// -------------------------------------------------------------------
// Types & Interfaces
// -------------------------------------------------------------------
export interface UserProfile {
  id: number;
  name: string;
  email: string;
  currency: string;
  createdAt: string;
}

export interface Category {
  id: number;
  name: string;
  type: 'income' | 'expense';
  icon?: string;
  color?: string;
}

export interface Transaction {
  id: number;
  categoryId: number;
  type: 'income' | 'expense';
  amount: number;
  paymentMethod: 'UPI' | 'Card' | 'Cash' | 'Bank' | 'Cheque' | 'Other';
  date: string; // YYYY-MM-DD
  notes: string;
  createdAt: string;
}

export interface Budget {
  id: number;
  categoryId: number;
  month: number;
  year: number;
  amount: number;
}

export interface ToastMessage {
  id: string;
  type: 'success' | 'danger' | 'info' | 'warning';
  text: string;
}

// -------------------------------------------------------------------
// Seed Records
// -------------------------------------------------------------------
const INITIAL_USER: UserProfile = {
  id: 1,
  name: 'Alex Morgan',
  email: 'alex.morgan@finance.io',
  currency: '₹',
  createdAt: '2026-09-01 10:00:00'
};

const INITIAL_CATEGORIES: Category[] = [
  { id: 1, name: 'Salary & Compensation', type: 'income', color: '#16a34a' },
  { id: 2, name: 'Client Retainers', type: 'income', color: '#059669' },
  { id: 3, name: 'Equities & Dividends', type: 'income', color: '#0d9488' },
  { id: 4, name: 'Other Revenue', type: 'income', color: '#10b981' },
  { id: 5, name: 'Food & Gourmet', type: 'expense', color: '#f97316' },
  { id: 6, name: 'Commute & Travel', type: 'expense', color: '#6366f1' },
  { id: 7, name: 'Rent & Living', type: 'expense', color: '#dc2626' },
  { id: 8, name: 'Cloud & Utilities', type: 'expense', color: '#0284c7' },
  { id: 9, name: 'Tech & Lifestyle', type: 'expense', color: '#ec4899' },
  { id: 10, name: 'Medical & Wellness', type: 'expense', color: '#14b8a6' },
  { id: 11, name: 'Books & Learning', type: 'expense', color: '#8b5cf6' },
  { id: 12, name: 'Recreation & Events', type: 'expense', color: '#f59e0b' },
  { id: 13, name: 'General Sundries', type: 'expense', color: '#64748b' }
];

const getPastDate = (daysAgo: number): string => {
  const d = new Date();
  d.setDate(d.getDate() - daysAgo);
  return d.toISOString().split('T')[0];
};

const INITIAL_TRANSACTIONS: Transaction[] = [
  { id: 1, categoryId: 1, type: 'income', amount: 82500, paymentMethod: 'Bank', date: getPastDate(14), notes: 'Primary monthly compensation credit', createdAt: '2026-10-02 09:30:00' },
  { id: 2, categoryId: 2, type: 'income', amount: 24000, paymentMethod: 'UPI', date: getPastDate(6), notes: 'Design sprint milestone payout', createdAt: '2026-10-10 14:15:00' },
  { id: 3, categoryId: 3, type: 'income', amount: 5600, paymentMethod: 'Bank', date: getPastDate(2), notes: 'Vanguard Index dividend distribution', createdAt: '2026-10-14 11:20:00' },
  { id: 4, categoryId: 7, type: 'expense', amount: 22000, paymentMethod: 'Bank', date: getPastDate(14), notes: 'Apartment residential lease payment', createdAt: '2026-10-02 10:00:00' },
  { id: 5, categoryId: 8, type: 'expense', amount: 3200, paymentMethod: 'UPI', date: getPastDate(12), notes: 'Fiber broadband & power grid charges', createdAt: '2026-10-04 16:45:00' },
  { id: 6, categoryId: 5, type: 'expense', amount: 3850, paymentMethod: 'Card', date: getPastDate(10), notes: 'Whole Foods pantry provisions', createdAt: '2026-10-06 18:30:00' },
  { id: 7, categoryId: 6, type: 'expense', amount: 1950, paymentMethod: 'UPI', date: getPastDate(8), notes: 'Rapid metro transit & cab fare', createdAt: '2026-10-08 19:10:00' },
  { id: 8, categoryId: 9, type: 'expense', amount: 5499, paymentMethod: 'Card', date: getPastDate(7), notes: 'Ultra-wide monitor arm & desk pad', createdAt: '2026-10-09 13:00:00' },
  { id: 9, categoryId: 5, type: 'expense', amount: 1750, paymentMethod: 'UPI', date: getPastDate(5), notes: 'Artisan bakery & dinner gathering', createdAt: '2026-10-11 21:00:00' },
  { id: 10, categoryId: 12, type: 'expense', amount: 1100, paymentMethod: 'Card', date: getPastDate(3), notes: 'IMAX Cinema passes & concessions', createdAt: '2026-10-13 17:30:00' },
  { id: 11, categoryId: 10, type: 'expense', amount: 720, paymentMethod: 'Cash', date: getPastDate(1), notes: 'Apothecary essentials & supplements', createdAt: '2026-10-15 12:10:00' },
  { id: 12, categoryId: 5, type: 'expense', amount: 420, paymentMethod: 'UPI', date: getPastDate(0), notes: 'Cold brew & espresso roasts', createdAt: '2026-10-16 08:30:00' },
  { id: 13, categoryId: 6, type: 'expense', amount: 280, paymentMethod: 'Cash', date: getPastDate(0), notes: 'Downtown metro ride', createdAt: '2026-10-16 10:15:00' }
];

const now = new Date();
const currentMonth = now.getMonth() + 1;
const currentYear = now.getFullYear();

const INITIAL_BUDGETS: Budget[] = [
  { id: 1, categoryId: 5, month: currentMonth, year: currentYear, amount: 12000 },
  { id: 2, categoryId: 6, month: currentMonth, year: currentYear, amount: 5000 },
  { id: 3, categoryId: 7, month: currentMonth, year: currentYear, amount: 22000 },
  { id: 4, categoryId: 8, month: currentMonth, year: currentYear, amount: 4500 },
  { id: 5, categoryId: 9, month: currentMonth, year: currentYear, amount: 8000 },
  { id: 6, categoryId: 12, month: currentMonth, year: currentYear, amount: 3500 }
];

export default function App() {
  // Navigation & Theme
  const [activeTab, setActiveTab] = useState<'app' | 'source'>('app');
  const [currentNav, setCurrentNav] = useState<'dashboard' | 'income' | 'expenses' | 'transactions' | 'categories' | 'budgets' | 'reports' | 'profile'>('dashboard');
  const [isDarkMode, setIsDarkMode] = useState<boolean>(() => {
    return localStorage.getItem('ep_theme') !== 'light'; // Default to sleek modern dark theme
  });
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  // Core Data Stores
  const [user, setUser] = useState<UserProfile>(() => {
    const saved = localStorage.getItem('ep_user');
    return saved ? JSON.parse(saved) : INITIAL_USER;
  });
  const [categories, setCategories] = useState<Category[]>(() => {
    const saved = localStorage.getItem('ep_categories');
    return saved ? JSON.parse(saved) : INITIAL_CATEGORIES;
  });
  const [transactions, setTransactions] = useState<Transaction[]>(() => {
    const saved = localStorage.getItem('ep_transactions');
    return saved ? JSON.parse(saved) : INITIAL_TRANSACTIONS;
  });
  const [budgets, setBudgets] = useState<Budget[]>(() => {
    const saved = localStorage.getItem('ep_budgets');
    return saved ? JSON.parse(saved) : INITIAL_BUDGETS;
  });

  // Toasts
  const [toasts, setToasts] = useState<ToastMessage[]>([]);
  const addToast = (type: 'success' | 'danger' | 'info' | 'warning', text: string) => {
    const id = Math.random().toString(36).substring(2, 9);
    setToasts(prev => [...prev, { id, type, text }]);
    setTimeout(() => {
      setToasts(prev => prev.filter(t => t.id !== id));
    }, 3800);
  };

  // Sync with LocalStorage
  useEffect(() => { localStorage.setItem('ep_user', JSON.stringify(user)); }, [user]);
  useEffect(() => { localStorage.setItem('ep_categories', JSON.stringify(categories)); }, [categories]);
  useEffect(() => { localStorage.setItem('ep_transactions', JSON.stringify(transactions)); }, [transactions]);
  useEffect(() => { localStorage.setItem('ep_budgets', JSON.stringify(budgets)); }, [budgets]);
  useEffect(() => {
    localStorage.setItem('ep_theme', isDarkMode ? 'dark' : 'light');
    if (isDarkMode) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }, [isDarkMode]);

  // Formatter
  const fmt = (amount: number): string => {
    return `${user.currency} ${Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  };

  // Source Code Explorer
  const [selectedFile, setSelectedFile] = useState<string>('README.md');
  const [fileFilterSearch, setFileFilterSearch] = useState<string>('');
  const [isCopied, setIsCopied] = useState(false);
  const [isDownloadingZip, setIsDownloadingZip] = useState(false);

  // Modals
  const [isTxModalOpen, setIsTxModalOpen] = useState(false);
  const [editingTx, setEditingTx] = useState<Transaction | null>(null);
  const [txModalType, setTxModalType] = useState<'income' | 'expense'>('expense');

  const [isCategoryModalOpen, setIsCategoryModalOpen] = useState(false);
  const [editingCategory, setEditingCategory] = useState<Category | null>(null);

  const [isBudgetModalOpen, setIsBudgetModalOpen] = useState(false);
  const [selectedBudgetCat, setSelectedBudgetCat] = useState<Category | null>(null);

  const [confirmModal, setConfirmModal] = useState<{
    open: boolean;
    title: string;
    message: string;
    onConfirm: () => void;
  }>({
    open: false,
    title: '',
    message: '',
    onConfirm: () => {}
  });

  // Filter States
  const [dashboardPeriod, setDashboardPeriod] = useState<'today' | '7d' | 'this_month' | 'custom'>('this_month');
  const [customFrom, setCustomFrom] = useState(getPastDate(30));
  const [customTo, setCustomTo] = useState(getPastDate(0));

  const [txSearch, setTxSearch] = useState('');
  const [txTypeFilter, setTxTypeFilter] = useState<'all' | 'income' | 'expense'>('all');
  const [txCatFilter, setTxCatFilter] = useState<number>(0);
  const [txMethodFilter, setTxMethodFilter] = useState<string>('all');
  const [txDateFrom, setTxDateFrom] = useState<string>('');
  const [txDateTo, setTxDateTo] = useState<string>('');
  const [txSortBy, setTxSortBy] = useState<'date' | 'amount' | 'category'>('date');
  const [txSortDir, setTxSortDir] = useState<'asc' | 'desc'>('desc');
  const [txPage, setTxPage] = useState<number>(1);
  const perPage = 10;

  const [budgetMonth, setBudgetMonth] = useState<number>(currentMonth);
  const [budgetYear, setBudgetYear] = useState<number>(currentYear);

  const [reportMonth, setReportMonth] = useState<number>(currentMonth);
  const [reportYear, setReportYear] = useState<number>(currentYear);

  // -------------------------------------------------------------------
  // Computed Metrics
  // -------------------------------------------------------------------
  const periodDates = useMemo(() => {
    const todayStr = getPastDate(0);
    if (dashboardPeriod === 'today') {
      return { start: todayStr, end: todayStr };
    }
    if (dashboardPeriod === '7d') {
      return { start: getPastDate(7), end: todayStr };
    }
    if (dashboardPeriod === 'custom') {
      return { start: customFrom || '2000-01-01', end: customTo || '2099-12-31' };
    }
    // this_month
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
    return {
      start: `${y}-${m}-01`,
      end: `${y}-${m}-${lastDay}`
    };
  }, [dashboardPeriod, customFrom, customTo]);

  const periodTransactions = useMemo(() => {
    return transactions.filter(t => t.date >= periodDates.start && t.date <= periodDates.end);
  }, [transactions, periodDates]);

  const periodIncome = useMemo(() => {
    return periodTransactions.filter(t => t.type === 'income').reduce((acc, t) => acc + t.amount, 0);
  }, [periodTransactions]);

  const periodExpense = useMemo(() => {
    return periodTransactions.filter(t => t.type === 'expense').reduce((acc, t) => acc + t.amount, 0);
  }, [periodTransactions]);

  const periodNet = periodIncome - periodExpense;

  const allTimeBalance = useMemo(() => {
    return transactions.reduce((acc, t) => t.type === 'income' ? acc + t.amount : acc - t.amount, 0);
  }, [transactions]);

  const savingsRate = periodIncome > 0 ? (periodNet / periodIncome) * 100 : 0;

  const todayTransactions = useMemo(() => {
    const today = getPastDate(0);
    return transactions.filter(t => t.date === today);
  }, [transactions]);

  const recentTransactions = useMemo(() => {
    return [...transactions].sort((a, b) => b.date.localeCompare(a.date) || b.id - a.id).slice(0, 6);
  }, [transactions]);

  // Category Expense Breakdown
  const expenseByCategory = useMemo(() => {
    const map = new Map<number, number>();
    periodTransactions.filter(t => t.type === 'expense').forEach(t => {
      map.set(t.categoryId, (map.get(t.categoryId) || 0) + t.amount);
    });

    const list: { name: string; amount: number; pct: number; color: string }[] = [];
    let total = 0;
    map.forEach(val => { total += val; });

    map.forEach((amount, catId) => {
      const cat = categories.find(c => c.id === catId);
      list.push({
        name: cat?.name || 'Other',
        amount,
        pct: total > 0 ? (amount / total) * 100 : 0,
        color: cat?.color || '#ef4444'
      });
    });

    return list.sort((a, b) => b.amount - a.amount);
  }, [periodTransactions, categories]);

  // Cashflow Daily Pulse
  const dailyCashflow = useMemo(() => {
    const daysMap = new Map<string, { income: number; expense: number }>();
    const y = now.getFullYear();
    const m = now.getMonth() + 1;
    const daysInM = new Date(y, m, 0).getDate();

    for (let d = 1; d <= daysInM; d++) {
      const dayStr = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
      daysMap.set(dayStr, { income: 0, expense: 0 });
    }

    transactions.forEach(t => {
      if (daysMap.has(t.date)) {
        const item = daysMap.get(t.date)!;
        if (t.type === 'income') item.income += t.amount;
        else item.expense += t.amount;
      }
    });

    return Array.from(daysMap.entries()).map(([date, val]) => ({
      day: date.split('-')[2],
      income: val.income,
      expense: val.expense
    }));
  }, [transactions]);

  // Filtered Ledger
  const filteredTransactions = useMemo(() => {
    const list = transactions.filter(t => {
      if (txTypeFilter !== 'all' && t.type !== txTypeFilter) return false;
      if (txCatFilter !== 0 && t.categoryId !== txCatFilter) return false;
      if (txMethodFilter !== 'all' && t.paymentMethod !== txMethodFilter) return false;
      if (txDateFrom && t.date < txDateFrom) return false;
      if (txDateTo && t.date > txDateTo) return false;
      if (txSearch) {
        const q = txSearch.toLowerCase();
        const cat = categories.find(c => c.id === t.categoryId)?.name.toLowerCase() || '';
        const notes = (t.notes || '').toLowerCase();
        if (!cat.includes(q) && !notes.includes(q)) return false;
      }
      return true;
    });

    list.sort((a, b) => {
      let cmp = 0;
      if (txSortBy === 'date') cmp = a.date.localeCompare(b.date);
      else if (txSortBy === 'amount') cmp = a.amount - b.amount;
      else if (txSortBy === 'category') {
        const catA = categories.find(c => c.id === a.categoryId)?.name || '';
        const catB = categories.find(c => c.id === b.categoryId)?.name || '';
        cmp = catA.localeCompare(catB);
      }
      return txSortDir === 'asc' ? cmp : -cmp;
    });

    return list;
  }, [transactions, categories, txTypeFilter, txCatFilter, txMethodFilter, txDateFrom, txDateTo, txSearch, txSortBy, txSortDir]);

  const totalPages = Math.max(1, Math.ceil(filteredTransactions.length / perPage));
  const paginatedTransactions = useMemo(() => {
    const start = (txPage - 1) * perPage;
    return filteredTransactions.slice(start, start + perPage);
  }, [filteredTransactions, txPage]);

  // Budgets
  const categoryBudgets = useMemo(() => {
    return categories
      .filter(c => c.type === 'expense')
      .map(c => {
        const bg = budgets.find(b => b.categoryId === c.id && b.month === budgetMonth && b.year === budgetYear);
        const spent = transactions
          .filter(t => {
            if (t.categoryId !== c.id || t.type !== 'expense') return false;
            const d = new Date(t.date);
            return (d.getMonth() + 1) === budgetMonth && d.getFullYear() === budgetYear;
          })
          .reduce((sum, t) => sum + t.amount, 0);

        const limit = bg ? bg.amount : 0;
        const pct = limit > 0 ? (spent / limit) * 100 : 0;
        return {
          category: c,
          budgetAmount: limit,
          spent,
          pct,
          isOver: limit > 0 && spent > limit,
          remaining: limit - spent
        };
      });
  }, [categories, budgets, transactions, budgetMonth, budgetYear]);

  // Reports
  const reportData = useMemo(() => {
    const curTx = transactions.filter(t => {
      const d = new Date(t.date);
      return (d.getMonth() + 1) === reportMonth && d.getFullYear() === reportYear;
    });

    let prevM = reportMonth - 1;
    let prevY = reportYear;
    if (prevM === 0) {
      prevM = 12;
      prevY -= 1;
    }

    const prevTx = transactions.filter(t => {
      const d = new Date(t.date);
      return (d.getMonth() + 1) === prevM && d.getFullYear() === prevY;
    });

    const curIncome = curTx.filter(t => t.type === 'income').reduce((s, t) => s + t.amount, 0);
    const curExpense = curTx.filter(t => t.type === 'expense').reduce((s, t) => s + t.amount, 0);
    const curSavings = curIncome - curExpense;
    const curSavingsRate = curIncome > 0 ? (curSavings / curIncome) * 100 : 0;

    const prevIncome = prevTx.filter(t => t.type === 'income').reduce((s, t) => s + t.amount, 0);
    const prevExpense = prevTx.filter(t => t.type === 'expense').reduce((s, t) => s + t.amount, 0);
    const prevSavings = prevIncome - prevExpense;

    const calcDelta = (curr: number, prev: number) => {
      if (prev <= 0) return { val: curr > 0 ? '+100%' : '0%', positive: curr >= 0 };
      const diff = ((curr - prev) / prev) * 100;
      return { val: `${diff >= 0 ? '+' : ''}${diff.toFixed(1)}%`, positive: diff >= 0 };
    };

    const expMap = new Map<number, { amount: number; count: number }>();
    curTx.filter(t => t.type === 'expense').forEach(t => {
      const existing = expMap.get(t.categoryId) || { amount: 0, count: 0 };
      expMap.set(t.categoryId, { amount: existing.amount + t.amount, count: existing.count + 1 });
    });

    const expBreakdown = Array.from(expMap.entries()).map(([catId, val]) => {
      const cat = categories.find(c => c.id === catId);
      return {
        name: cat?.name || 'Unknown',
        amount: val.amount,
        count: val.count,
        share: curExpense > 0 ? (val.amount / curExpense) * 100 : 0
      };
    }).sort((a, b) => b.amount - a.amount);

    return {
      curIncome,
      curExpense,
      curSavings,
      curSavingsRate,
      prevIncome,
      prevExpense,
      prevSavings,
      incomeDelta: calcDelta(curIncome, prevIncome),
      expenseDelta: calcDelta(curExpense, prevExpense),
      savingsDelta: calcDelta(curSavings, prevSavings),
      expBreakdown,
      prevMonthName: new Date(prevY, prevM - 1).toLocaleString('default', { month: 'long' }),
      curMonthName: new Date(reportYear, reportMonth - 1).toLocaleString('default', { month: 'long' })
    };
  }, [transactions, categories, reportMonth, reportYear]);

  // CSV Exporter
  const exportCsv = () => {
    const rows = [
      ['ID', 'Date', 'Type', 'Category', 'Payment Method', 'Amount', 'Currency', 'Notes', 'Created At']
    ];

    filteredTransactions.forEach(t => {
      const cat = categories.find(c => c.id === t.categoryId);
      rows.push([
        String(t.id),
        t.date,
        t.type.toUpperCase(),
        cat ? cat.name : 'Uncategorized',
        t.paymentMethod,
        t.amount.toFixed(2),
        user.currency,
        `"${(t.notes || '').replace(/"/g, '""')}"`,
        t.createdAt
      ]);
    });

    const csvContent = '\uFEFF' + rows.map(e => e.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `expense_pulse_ledger_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    addToast('success', `Exported ${filteredTransactions.length} transaction rows to CSV.`);
  };

  // ZIP Builder
  const downloadXamppZip = async () => {
    setIsDownloadingZip(true);
    try {
      const zip = new JSZip();
      const folder = zip.folder('expense-manager') || zip;
      SOURCE_FILES.forEach(file => {
        folder.file(file.path, file.content);
      });
      const content = await zip.generateAsync({ type: 'blob' });
      const url = URL.createObjectURL(content);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'expense-manager.zip';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      addToast('success', 'Generated expense-manager.zip ready for XAMPP htdocs!');
    } catch (err) {
      console.error(err);
      addToast('danger', 'Failed to generate ZIP package.');
    } finally {
      setIsDownloadingZip(false);
    }
  };

  // Transaction Handler
  const handleSaveTx = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = e.currentTarget;
    const amountVal = parseFloat((form.elements.namedItem('amount') as HTMLInputElement).value);
    const catIdVal = parseInt((form.elements.namedItem('categoryId') as HTMLSelectElement).value, 10);
    const dateVal = (form.elements.namedItem('date') as HTMLInputElement).value;
    const methodVal = (form.elements.namedItem('paymentMethod') as HTMLSelectElement).value as any;
    const notesVal = (form.elements.namedItem('notes') as HTMLTextAreaElement).value;

    if (!amountVal || amountVal <= 0) {
      addToast('danger', 'Please enter a valid amount greater than 0.');
      return;
    }
    if (!catIdVal) {
      addToast('danger', 'Please select a valid category.');
      return;
    }

    if (editingTx) {
      setTransactions(prev => prev.map(t => t.id === editingTx.id ? {
        ...t,
        amount: amountVal,
        categoryId: catIdVal,
        date: dateVal,
        paymentMethod: methodVal,
        notes: notesVal
      } : t));
      addToast('success', `Updated transaction #${editingTx.id}`);
    } else {
      const newId = transactions.length > 0 ? Math.max(...transactions.map(t => t.id)) + 1 : 1;
      const newTx: Transaction = {
        id: newId,
        type: txModalType,
        amount: amountVal,
        categoryId: catIdVal,
        date: dateVal,
        paymentMethod: methodVal,
        notes: notesVal,
        createdAt: new Date().toISOString().replace('T', ' ').slice(0, 19)
      };
      setTransactions(prev => [newTx, ...prev]);
      addToast('success', `Logged ${txModalType} of ${fmt(amountVal)}`);
    }

    setIsTxModalOpen(false);
    setEditingTx(null);
  };

  const handleDeleteTx = (id: number) => {
    const item = transactions.find(t => t.id === id);
    setConfirmModal({
      open: true,
      title: 'Confirm Deletion',
      message: `Permanently delete this ${item?.type || 'entry'} of ${item ? fmt(item.amount) : ''}?`,
      onConfirm: () => {
        setTransactions(prev => prev.filter(t => t.id !== id));
        addToast('success', 'Transaction removed.');
        setConfirmModal(prev => ({ ...prev, open: false }));
      }
    });
  };

  // Category Handlers
  const handleSaveCategory = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = e.currentTarget;
    const name = (form.elements.namedItem('name') as HTMLInputElement).value.trim();
    const type = (form.elements.namedItem('type') as HTMLSelectElement).value as 'income' | 'expense';

    if (!name) return;

    if (editingCategory) {
      setCategories(prev => prev.map(c => c.id === editingCategory.id ? { ...c, name, type } : c));
      addToast('success', `Updated category "${name}"`);
    } else {
      const newId = categories.length > 0 ? Math.max(...categories.map(c => c.id)) + 1 : 1;
      setCategories(prev => [...prev, { id: newId, name, type }]);
      addToast('success', `Created ${type} category "${name}"`);
    }

    setIsCategoryModalOpen(false);
    setEditingCategory(null);
  };

  const handleDeleteCategory = (cat: Category) => {
    const linked = transactions.filter(t => t.categoryId === cat.id).length;
    if (linked > 0) {
      addToast('danger', `Cannot delete "${cat.name}": ${linked} transaction(s) are linked to it.`);
      return;
    }

    setConfirmModal({
      open: true,
      title: 'Delete Category',
      message: `Delete category "${cat.name}"?`,
      onConfirm: () => {
        setCategories(prev => prev.filter(c => c.id !== cat.id));
        setBudgets(prev => prev.filter(b => b.categoryId !== cat.id));
        addToast('success', `Category "${cat.name}" removed.`);
        setConfirmModal(prev => ({ ...prev, open: false }));
      }
    });
  };

  // Budget Handler
  const handleSaveBudget = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!selectedBudgetCat) return;

    const form = e.currentTarget;
    const amountVal = parseFloat((form.elements.namedItem('amount') as HTMLInputElement).value) || 0;

    if (amountVal > 0) {
      setBudgets(prev => {
        const filtered = prev.filter(b => !(b.categoryId === selectedBudgetCat.id && b.month === budgetMonth && b.year === budgetYear));
        const newId = prev.length > 0 ? Math.max(...prev.map(b => b.id)) + 1 : 1;
        return [...filtered, { id: newId, categoryId: selectedBudgetCat.id, month: budgetMonth, year: budgetYear, amount: amountVal }];
      });
      addToast('success', `Monthly cap for ${selectedBudgetCat.name} set to ${fmt(amountVal)}`);
    } else {
      setBudgets(prev => prev.filter(b => !(b.categoryId === selectedBudgetCat.id && b.month === budgetMonth && b.year === budgetYear)));
      addToast('info', `Budget cap cleared for ${selectedBudgetCat.name}.`);
    }

    setIsBudgetModalOpen(false);
    setSelectedBudgetCat(null);
  };

  // Payment Method Icon Helper
  const renderMethodBadge = (method: string) => {
    switch (method) {
      case 'Card':
        return <span className="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium"><CreditCard className="w-3 h-3 text-sky-500" /> Card</span>;
      case 'Bank':
        return <span className="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium"><Building2 className="w-3 h-3 text-indigo-500" /> Bank</span>;
      case 'UPI':
        return <span className="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium"><Smartphone className="w-3 h-3 text-emerald-500" /> UPI</span>;
      case 'Cash':
        return <span className="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium"><Banknote className="w-3 h-3 text-amber-500" /> Cash</span>;
      default:
        return <span className="text-[11px] text-slate-400">{method}</span>;
    }
  };

  // Filtered Source Files
  const filteredSourceFiles = useMemo(() => {
    if (!fileFilterSearch) return SOURCE_FILES;
    const q = fileFilterSearch.toLowerCase();
    return SOURCE_FILES.filter(f => f.path.toLowerCase().includes(q));
  }, [fileFilterSearch]);

  const currentFileContent = useMemo(() => {
    const f = SOURCE_FILES.find(file => file.path === selectedFile);
    return f ? f.content : '// File content not found';
  }, [selectedFile]);

  return (
    <div className={`min-h-screen ${isDarkMode ? 'dark bg-[#0a0f1d] text-slate-100' : 'bg-[#f4f6fa] text-slate-900'} font-sans flex flex-col selection:bg-blue-600 selection:text-white`}>
      
      {/* Toast Notifications */}
      <div className="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm pointer-events-none">
        {toasts.map(toast => (
          <div
            key={toast.id}
            className={`pointer-events-auto flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl shadow-xl border text-xs font-medium backdrop-blur-md transition-all duration-300 animate-in slide-in-from-top-2 ${
              toast.type === 'success' ? 'bg-emerald-600/90 text-white border-emerald-500/50' :
              toast.type === 'danger' ? 'bg-red-600/90 text-white border-red-500/50' :
              toast.type === 'warning' ? 'bg-amber-600/90 text-white border-amber-500/50' :
              'bg-blue-600/90 text-white border-blue-500/50'
            }`}
          >
            <span>{toast.text}</span>
            <button
              onClick={() => setToasts(prev => prev.filter(t => t.id !== toast.id))}
              className="text-white/80 hover:text-white text-sm leading-none font-bold ml-2"
            >
              &times;
            </button>
          </div>
        ))}
      </div>

      {/* Top Navbar */}
      <header className="sticky top-0 z-40 border-b border-slate-200/80 dark:border-slate-800/80 backdrop-blur-md bg-white/85 dark:bg-[#0d1326]/85 transition-colors">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-14">
          
          {/* Brand */}
          <div className="flex items-center gap-3">
            <button
              onClick={() => setMobileMenuOpen(true)}
              className="md:hidden p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400"
            >
              <Menu className="w-4 h-4" />
            </button>

            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-lg bg-linear-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-xs">
                <Wallet className="w-4 h-4" />
              </div>
              <div className="flex items-baseline gap-1.5">
                <span className="font-extrabold text-base tracking-tight bg-linear-to-r from-slate-900 to-slate-700 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">
                  ExpensePulse
                </span>
                <span className="text-[10px] font-mono uppercase tracking-wider text-blue-600 dark:text-blue-400 font-semibold">
                  PRO
                </span>
              </div>
            </div>
          </div>

          {/* Mode Switcher: Live App vs Source Code Explorer */}
          <div className="flex items-center gap-2 sm:gap-3">
            <div className="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 text-xs font-semibold">
              <button
                onClick={() => setActiveTab('app')}
                className={`px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer ${
                  activeTab === 'app'
                    ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold'
                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                }`}
              >
                Interactive System
              </button>
              <button
                onClick={() => setActiveTab('source')}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer ${
                  activeTab === 'source'
                    ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold'
                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                }`}
              >
                <FileCode className="w-3.5 h-3.5 text-blue-500" />
                <span>Source & XAMPP ZIP</span>
              </button>
            </div>

            {/* Currency Quick-Switcher */}
            <div className="hidden sm:flex items-center bg-slate-100 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 rounded-xl px-2 py-1">
              <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1.5">Currency:</span>
              <select
                value={user.currency}
                onChange={e => {
                  setUser(prev => ({ ...prev, currency: e.target.value }));
                  addToast('info', `Display currency changed to ${e.target.value}`);
                }}
                className="bg-transparent text-xs font-bold text-blue-600 dark:text-blue-400 focus:outline-none cursor-pointer"
              >
                <option value="₹">₹ INR</option>
                <option value="$">$ USD</option>
                <option value="€">€ EUR</option>
                <option value="£">£ GBP</option>
                <option value="¥">¥ JPY</option>
                <option value="AED">AED</option>
                <option value="SAR">SAR</option>
              </select>
            </div>

            {/* Dark/Light Toggle */}
            <button
              onClick={() => setIsDarkMode(!isDarkMode)}
              className="p-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors cursor-pointer"
              title="Toggle theme"
            >
              {isDarkMode ? <Sun className="w-4 h-4 text-amber-400" /> : <Moon className="w-4 h-4 text-slate-600" />}
            </button>
          </div>
        </div>
      </header>

      {/* Main Body */}
      {activeTab === 'source' ? (
        /* ===================================================================
           TAB: SOURCE CODE & XAMPP ZIP SUITE
           =================================================================== */
        <div className="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
          {/* Top Banner Card */}
          <div className="relative overflow-hidden bg-linear-to-r from-blue-900/30 via-slate-900 to-indigo-950/40 border border-blue-500/20 rounded-2xl p-6 sm:p-8 text-white shadow-xl">
            <div className="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
              <div className="space-y-2 max-w-2xl">
                <div className="flex items-center gap-2">
                  <span className="px-2.5 py-0.5 rounded-md bg-blue-500/20 text-blue-400 border border-blue-500/30 text-[11px] font-bold uppercase tracking-wider">
                    XAMPP / WAMP Production Suite
                  </span>
                  <span className="text-xs text-slate-400">· 32 Verified Source Files</span>
                </div>
                <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                  Native PHP 8+ & MySQL PDO Codebase
                </h1>
                <p className="text-xs sm:text-sm text-slate-300 leading-relaxed">
                  Strictly architected without frameworks: PDO prepared statements, CSRF protection, Bcrypt hashing, Chart.js analytics, and complete MySQL seed schema.
                </p>
              </div>

              <div className="shrink-0 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                <button
                  onClick={downloadXamppZip}
                  disabled={isDownloadingZip}
                  className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-lg shadow-blue-600/30 transition-all cursor-pointer"
                >
                  <FolderArchive className="w-4 h-4" />
                  <span>{isDownloadingZip ? 'Packaging Files...' : 'Download expense-manager.zip'}</span>
                </button>
              </div>
            </div>
          </div>

          {/* Setup Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-xl p-4 shadow-xs">
              <div className="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">01 · Target Folder</div>
              <div className="font-bold text-sm text-slate-900 dark:text-white mt-1">Copy to htdocs</div>
              <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Extract the zip into <code className="font-mono text-blue-600 dark:text-blue-400">C:/xampp/htdocs/expense-manager</code>
              </p>
            </div>

            <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-xl p-4 shadow-xs">
              <div className="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">02 · Start Services</div>
              <div className="font-bold text-sm text-slate-900 dark:text-white mt-1">Apache & MySQL</div>
              <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Open XAMPP Control Panel and start Apache and MySQL services.
              </p>
            </div>

            <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-xl p-4 shadow-xs">
              <div className="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">03 · Database</div>
              <div className="font-bold text-sm text-slate-900 dark:text-white mt-1">Import database.sql</div>
              <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Create DB <code className="font-mono">expense_manager</code> in phpMyAdmin and import the SQL file.
              </p>
            </div>

            <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-xl p-4 shadow-xs">
              <div className="text-[10px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">04 · Launch</div>
              <div className="font-bold text-sm text-slate-900 dark:text-white mt-1">http://localhost/...</div>
              <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Sign in with <code className="font-mono font-semibold">demo@example.com</code> / <code className="font-mono font-semibold">password123</code>
              </p>
            </div>
          </div>

          {/* Interactive File Browser */}
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-5 bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-xs">
            {/* Sidebar File Tree */}
            <div className="lg:col-span-4 border-r border-slate-200/80 dark:border-slate-800/80 p-4 max-h-[600px] flex flex-col">
              <div className="flex items-center justify-between mb-2">
                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Files ({SOURCE_FILES.length})</span>
                <span className="text-[11px] text-blue-500 font-mono">expense-manager/</span>
              </div>

              <div className="relative mb-3">
                <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400" />
                <input
                  type="text"
                  value={fileFilterSearch}
                  onChange={e => setFileFilterSearch(e.target.value)}
                  placeholder="Filter files..."
                  className="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900"
                />
              </div>

              <div className="space-y-0.5 overflow-y-auto flex-1 pr-1 font-mono text-xs">
                {filteredSourceFiles.map(file => {
                  const isSelected = selectedFile === file.path;
                  const ext = file.path.split('.').pop();
                  return (
                    <button
                      key={file.path}
                      onClick={() => setSelectedFile(file.path)}
                      className={`w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-left transition-colors cursor-pointer ${
                        isSelected
                          ? 'bg-blue-600 text-white font-semibold shadow-xs'
                          : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'
                      }`}
                    >
                      <span className="truncate">{file.path}</span>
                      <span className={`text-[10px] font-mono shrink-0 ml-2 ${isSelected ? 'text-blue-100' : 'text-slate-400'}`}>
                        {(file.content.length / 1024).toFixed(1)}k
                      </span>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Code Viewport */}
            <div className="lg:col-span-8 flex flex-col max-h-[600px]">
              <div className="px-4 py-3 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between">
                <div className="flex items-center gap-2 font-mono text-xs">
                  <span className="text-slate-400">path:</span>
                  <span className="font-bold text-blue-600 dark:text-blue-400">{selectedFile}</span>
                </div>
                <button
                  onClick={() => {
                    navigator.clipboard.writeText(currentFileContent);
                    setIsCopied(true);
                    setTimeout(() => setIsCopied(false), 2000);
                    addToast('success', `Copied ${selectedFile}`);
                  }}
                  className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                >
                  {isCopied ? <Check className="w-3.5 h-3.5 text-emerald-500" /> : <Copy className="w-3.5 h-3.5" />}
                  <span>{isCopied ? 'Copied' : 'Copy'}</span>
                </button>
              </div>

              <div className="flex-1 p-4 bg-[#080c18] text-slate-200 overflow-auto font-mono text-xs leading-relaxed">
                <pre><code>{currentFileContent}</code></pre>
              </div>
            </div>
          </div>
        </div>
      ) : (
        /* ===================================================================
           TAB: LIVE EXECUTIVE FINANCIAL SYSTEM
           =================================================================== */
        <div className="flex-1 flex overflow-hidden">
          
          {/* Executive Sidebar */}
          <aside className={`w-64 bg-white dark:bg-[#0d1326] border-r border-slate-200/80 dark:border-slate-800/80 flex flex-col shrink-0 fixed inset-y-0 left-0 z-30 transition-transform md:static md:translate-x-0 ${
            mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
          }`}>
            {/* Sidebar User Card */}
            <div className="p-4 border-b border-slate-200/80 dark:border-slate-800/80 bg-slate-50/60 dark:bg-slate-900/40 flex items-center justify-between">
              <div className="flex items-center gap-3 overflow-hidden">
                <div className="w-9 h-9 rounded-xl bg-linear-to-br from-blue-600 to-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                  {user.name.charAt(0)}
                </div>
                <div className="truncate">
                  <div className="font-bold text-xs text-slate-900 dark:text-white truncate">{user.name}</div>
                  <div className="text-[11px] text-slate-500 dark:text-slate-400 font-mono">Currency: {user.currency}</div>
                </div>
              </div>
              <button onClick={() => setMobileMenuOpen(false)} className="md:hidden text-slate-400">
                <X className="w-4 h-4" />
              </button>
            </div>

            {/* Navigation links */}
            <nav className="p-3 space-y-1 flex-1 overflow-y-auto">
              {[
                { id: 'dashboard', label: 'Dashboard', icon: Layers },
                { id: 'income', label: 'Income Management', icon: TrendingUp, color: 'text-emerald-500' },
                { id: 'expenses', label: 'Expense Management', icon: TrendingDown, color: 'text-red-500' },
                { id: 'transactions', label: 'All Transactions', icon: CreditCard },
                { id: 'categories', label: 'Categories', icon: PieIcon },
                { id: 'budgets', label: 'Monthly Budgets', icon: BarChart3 },
                { id: 'reports', label: 'Financial Reports', icon: Calendar },
                { id: 'profile', label: 'Settings & Profile', icon: Settings }
              ].map(item => {
                const Icon = item.icon;
                const isActive = currentNav === item.id;
                return (
                  <button
                    key={item.id}
                    onClick={() => { setCurrentNav(item.id as any); setMobileMenuOpen(false); }}
                    className={`w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 cursor-pointer text-left ${
                      isActive
                        ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/30'
                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white'
                    }`}
                  >
                    <Icon className={`w-4 h-4 ${isActive ? 'text-white' : item.color || 'text-slate-400'}`} />
                    <span>{item.label}</span>
                  </button>
                );
              })}
            </nav>

            {/* Bottom Info */}
            <div className="p-3 border-t border-slate-200/80 dark:border-slate-800/80 space-y-2">
              <div className="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800/60">
                <div className="flex items-center justify-between text-[11px] font-semibold text-slate-500">
                  <span>Savings Efficiency</span>
                  <span className={savingsRate >= 20 ? 'text-emerald-500' : 'text-amber-500'}>{savingsRate.toFixed(0)}%</span>
                </div>
                <div className="w-full h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full mt-2 overflow-hidden">
                  <div
                    className={`h-full rounded-full ${savingsRate >= 20 ? 'bg-emerald-500' : 'bg-amber-500'}`}
                    style={{ width: `${Math.min(100, Math.max(0, savingsRate))}%` }}
                  />
                </div>
              </div>
            </div>
          </aside>

          {/* Main View Area */}
          <main className="flex-1 flex flex-col min-w-0 overflow-y-auto">
            {/* Top Workspace Bar */}
            <div className="h-14 px-6 border-b border-slate-200/80 dark:border-slate-800/80 bg-white/60 dark:bg-[#0d1326]/60 backdrop-blur-xs flex items-center justify-between shrink-0">
              <div className="flex items-center gap-2 text-xs font-medium text-slate-400">
                <span>Finance</span>
                <span>/</span>
                <span className="font-bold text-slate-900 dark:text-white capitalize">{currentNav.replace('_', ' ')}</span>
              </div>

              {/* Quick Actions */}
              <div className="flex items-center gap-2">
                <button
                  onClick={() => {
                    setEditingTx(null);
                    setTxModalType('income');
                    setIsTxModalOpen(true);
                  }}
                  className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-emerald-500/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-xs font-bold transition-all cursor-pointer"
                >
                  <Plus className="w-3.5 h-3.5" />
                  <span>+ Income</span>
                </button>
                <button
                  onClick={() => {
                    setEditingTx(null);
                    setTxModalType('expense');
                    setIsTxModalOpen(true);
                  }}
                  className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 active:bg-red-700 text-white text-xs font-bold shadow-xs shadow-red-600/30 transition-all cursor-pointer"
                >
                  <Plus className="w-3.5 h-3.5" />
                  <span>+ Expense</span>
                </button>
              </div>
            </div>

            <div className="p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">

              {/* -------------------------------------------------------------
                  VIEW: DASHBOARD
                  ------------------------------------------------------------- */}
              {currentNav === 'dashboard' && (
                <div className="space-y-6">
                  {/* Period Filter Selector */}
                  <div className="flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 p-3 rounded-2xl shadow-xs">
                    <div className="flex items-center gap-2">
                      <span className="text-xs font-bold uppercase tracking-wider text-slate-400">Period:</span>
                      <div className="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-xs font-semibold">
                        {(['today', '7d', 'this_month', 'custom'] as const).map(p => (
                          <button
                            key={p}
                            onClick={() => setDashboardPeriod(p)}
                            className={`px-3 py-1 rounded-lg transition-all cursor-pointer ${
                              dashboardPeriod === p
                                ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold'
                                : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'
                            }`}
                          >
                            {p === 'today' ? 'Today' : p === '7d' ? 'Last 7 Days' : p === 'this_month' ? 'This Month' : 'Custom'}
                          </button>
                        ))}
                      </div>
                    </div>

                    {dashboardPeriod === 'custom' && (
                      <div className="flex items-center gap-2 text-xs">
                        <input
                          type="date"
                          value={customFrom}
                          onChange={e => setCustomFrom(e.target.value)}
                          className="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-xs"
                        />
                        <span className="text-slate-400">to</span>
                        <input
                          type="date"
                          value={customTo}
                          onChange={e => setCustomTo(e.target.value)}
                          className="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-xs"
                        />
                      </div>
                    )}
                  </div>

                  {/* 4 Executive KPI Cards */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Income */}
                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                      <div className="flex items-center justify-between">
                        <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Income</span>
                        <div className="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                          <TrendingUp className="w-4 h-4" />
                        </div>
                      </div>
                      <div className="mt-4 font-mono font-bold text-2xl lg:text-3xl text-emerald-600 dark:text-emerald-400 tabular-nums">
                        {fmt(periodIncome)}
                      </div>
                      <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                        <span>Recorded inflows for period</span>
                      </div>
                    </div>

                    {/* Expense */}
                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                      <div className="flex items-center justify-between">
                        <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Expenses</span>
                        <div className="w-8 h-8 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center">
                          <TrendingDown className="w-4 h-4" />
                        </div>
                      </div>
                      <div className="mt-4 font-mono font-bold text-2xl lg:text-3xl text-red-600 dark:text-red-400 tabular-nums">
                        {fmt(periodExpense)}
                      </div>
                      <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                        <span>Recorded outflows for period</span>
                      </div>
                    </div>

                    {/* Net Savings */}
                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                      <div className="flex items-center justify-between">
                        <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Net Savings</span>
                        <div className="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
                          <Wallet className="w-4 h-4" />
                        </div>
                      </div>
                      <div className={`mt-4 font-mono font-bold text-2xl lg:text-3xl tabular-nums ${periodNet >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'}`}>
                        {fmt(periodNet)}
                      </div>
                      <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                        <span className="font-semibold text-slate-700 dark:text-slate-300">{savingsRate.toFixed(1)}%</span>
                        <span>savings rate</span>
                      </div>
                    </div>

                    {/* Lifetime Balance */}
                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                      <div className="flex items-center justify-between">
                        <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Lifetime Balance</span>
                        <div className="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                          <Building2 className="w-4 h-4" />
                        </div>
                      </div>
                      <div className="mt-4 font-mono font-bold text-2xl lg:text-3xl text-slate-900 dark:text-white tabular-nums">
                        {fmt(allTimeBalance)}
                      </div>
                      <div className="text-[11px] text-slate-400 mt-1">
                        Cumulative ledger reserve
                      </div>
                    </div>
                  </div>

                  {/* Interactive Visualizations */}
                  <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    {/* Category Distribution */}
                    <div className="lg:col-span-5 bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs flex flex-col">
                      <div className="flex items-center justify-between mb-4">
                        <div>
                          <h3 className="font-bold text-sm text-slate-900 dark:text-white">Expense Breakdown</h3>
                          <p className="text-xs text-slate-400">Category allocation for active period</p>
                        </div>
                      </div>

                      {expenseByCategory.length === 0 ? (
                        <div className="flex-1 flex items-center justify-center text-xs text-slate-400 py-10">
                          No expense records in this timeframe.
                        </div>
                      ) : (
                        <div className="space-y-3.5 flex-1 flex flex-col justify-center">
                          {expenseByCategory.slice(0, 6).map((item, idx) => (
                            <div key={item.name} className="space-y-1">
                              <div className="flex items-center justify-between text-xs">
                                <div className="flex items-center gap-2">
                                  <span className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: item.color }} />
                                  <span className="font-semibold text-slate-800 dark:text-slate-200">{item.name}</span>
                                </div>
                                <div className="font-mono tabular-nums text-slate-600 dark:text-slate-400">
                                  {fmt(item.amount)} <span className="text-[10px] text-slate-400 font-normal">({item.pct.toFixed(1)}%)</span>
                                </div>
                              </div>
                              <div className="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div
                                  className="h-full rounded-full transition-all duration-300"
                                  style={{ width: `${item.pct}%`, backgroundColor: item.color }}
                                />
                              </div>
                            </div>
                          ))}
                        </div>
                      )}
                    </div>

                    {/* Daily Cashflow Chart */}
                    <div className="lg:col-span-7 bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs flex flex-col">
                      <div className="flex items-center justify-between mb-4">
                        <div>
                          <h3 className="font-bold text-sm text-slate-900 dark:text-white">Daily Cashflow Pulse</h3>
                          <p className="text-xs text-slate-400">Income (Green) vs Expenses (Red) per calendar day</p>
                        </div>
                        <div className="flex items-center gap-3 text-xs font-semibold">
                          <span className="flex items-center gap-1.5"><span className="w-2.5 h-2.5 rounded bg-emerald-500" /> In</span>
                          <span className="flex items-center gap-1.5"><span className="w-2.5 h-2.5 rounded bg-red-500" /> Out</span>
                        </div>
                      </div>

                      <div className="flex-1 flex items-end gap-1 sm:gap-1.5 pt-6 border-b border-slate-100 dark:border-slate-800 min-h-[200px]">
                        {dailyCashflow.slice(0, 24).map((d, i) => {
                          const maxVal = Math.max(...dailyCashflow.map(x => Math.max(x.income, x.expense)), 5000);
                          const incH = Math.min(100, (d.income / maxVal) * 100);
                          const expH = Math.min(100, (d.expense / maxVal) * 100);

                          return (
                            <div key={i} className="flex-1 flex flex-col items-center gap-1 h-full justify-end group relative">
                              {/* Hover Tooltip */}
                              <div className="absolute -top-12 opacity-0 group-hover:opacity-100 bg-slate-900 text-white text-[10px] px-2 py-1 rounded-md shadow-xl pointer-events-none transition-opacity whitespace-nowrap z-20 font-mono">
                                Day {d.day}: +{fmt(d.income)} / -{fmt(d.expense)}
                              </div>

                              <div className="w-full flex items-end justify-center gap-0.5 h-40">
                                {d.income > 0 && (
                                  <div className="w-2 bg-emerald-500 rounded-t-sm" style={{ height: `${Math.max(incH, 5)}%` }} />
                                )}
                                {d.expense > 0 && (
                                  <div className="w-2 bg-red-500 rounded-t-sm" style={{ height: `${Math.max(expH, 5)}%` }} />
                                )}
                              </div>
                              <span className="text-[9px] text-slate-400 font-mono">{d.day}</span>
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  </div>

                  {/* Tables Grid */}
                  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Today's Transactions */}
                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs">
                      <div className="flex items-center justify-between mb-3">
                        <h3 className="font-bold text-sm text-slate-900 dark:text-white">Today's Transactions</h3>
                        <span className="text-xs text-slate-400">{todayTransactions.length} items</span>
                      </div>

                      {todayTransactions.length === 0 ? (
                        <div className="py-8 text-center text-xs text-slate-400">
                          No transactions recorded yet today.
                        </div>
                      ) : (
                        <div className="divide-y divide-slate-100 dark:divide-slate-800/60">
                          {todayTransactions.map(t => {
                            const cat = categories.find(c => c.id === t.categoryId);
                            return (
                              <div key={t.id} className="py-2.5 flex items-center justify-between text-xs">
                                <div>
                                  <div className="font-semibold text-slate-900 dark:text-white">{cat?.name}</div>
                                  <div className="text-[11px] text-slate-400">{renderMethodBadge(t.paymentMethod)}</div>
                                </div>
                                <div className="text-right">
                                  <div className={`font-mono font-bold tabular-nums ${t.type === 'income' ? 'text-emerald-600' : 'text-red-600'}`}>
                                    {t.type === 'income' ? '+' : '-'} {fmt(t.amount)}
                                  </div>
                                </div>
                              </div>
                            );
                          })}
                        </div>
                      )}
                    </div>

                    {/* Recent 5 */}
                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs">
                      <div className="flex items-center justify-between mb-3">
                        <h3 className="font-bold text-sm text-slate-900 dark:text-white">Recent Activity</h3>
                        <button
                          onClick={() => setCurrentNav('transactions')}
                          className="text-xs font-bold text-blue-600 hover:underline cursor-pointer"
                        >
                          View Ledger &rarr;
                        </button>
                      </div>

                      <div className="divide-y divide-slate-100 dark:divide-slate-800/60">
                        {recentTransactions.map(t => {
                          const cat = categories.find(c => c.id === t.categoryId);
                          return (
                            <div key={t.id} className="py-2.5 flex items-center justify-between text-xs">
                              <div>
                                <div className="font-semibold text-slate-900 dark:text-white">{cat?.name}</div>
                                <div className="text-[11px] text-slate-400 font-mono">{t.date} · {t.paymentMethod}</div>
                              </div>
                              <div className="text-right">
                                <div className={`font-mono font-bold tabular-nums ${t.type === 'income' ? 'text-emerald-600' : 'text-red-600'}`}>
                                  {t.type === 'income' ? '+' : '-'} {fmt(t.amount)}
                                </div>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* -------------------------------------------------------------
                  VIEW: INCOME MANAGEMENT
                  ------------------------------------------------------------- */}
              {currentNav === 'income' && (
                <div className="space-y-5">
                  <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                      <h2 className="text-xl font-bold text-slate-900 dark:text-white">Income Entries</h2>
                      <p className="text-xs text-slate-400">Manage all revenue sources, salary credits, retainers, and dividends.</p>
                    </div>
                    <button
                      onClick={() => { setEditingTx(null); setTxModalType('income'); setIsTxModalOpen(true); }}
                      className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition-all cursor-pointer"
                    >
                      <Plus className="w-4 h-4" />
                      <span>+ Record Income</span>
                    </button>
                  </div>

                  <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-xs">
                    <table className="w-full text-xs text-left">
                      <thead className="bg-slate-50 dark:bg-slate-900/50 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                          <th className="p-3.5">Date</th>
                          <th className="p-3.5">Source Category</th>
                          <th className="p-3.5">Payment Method</th>
                          <th className="p-3.5">Notes</th>
                          <th className="p-3.5 text-right">Amount</th>
                          <th className="p-3.5 text-right">Actions</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60">
                        {transactions.filter(t => t.type === 'income').map(t => {
                          const cat = categories.find(c => c.id === t.categoryId);
                          return (
                            <tr key={t.id} className="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                              <td className="p-3.5 font-mono text-slate-500">{t.date}</td>
                              <td className="p-3.5 font-bold text-slate-900 dark:text-white">{cat?.name}</td>
                              <td className="p-3.5">{renderMethodBadge(t.paymentMethod)}</td>
                              <td className="p-3.5 text-slate-500 max-w-xs truncate">{t.notes || '—'}</td>
                              <td className="p-3.5 text-right font-mono font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                                + {fmt(t.amount)}
                              </td>
                              <td className="p-3.5 text-right space-x-1">
                                <button
                                  onClick={() => { setEditingTx(t); setTxModalType('income'); setIsTxModalOpen(true); }}
                                  className="p-1 hover:text-blue-500 text-slate-400 cursor-pointer"
                                >
                                  <Edit3 className="w-3.5 h-3.5" />
                                </button>
                                <button
                                  onClick={() => handleDeleteTx(t.id)}
                                  className="p-1 hover:text-red-500 text-slate-400 cursor-pointer"
                                >
                                  <Trash2 className="w-3.5 h-3.5" />
                                </button>
                              </td>
                            </tr>
                          );
                        })}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}

              {/* -------------------------------------------------------------
                  VIEW: EXPENSES MANAGEMENT
                  ------------------------------------------------------------- */}
              {currentNav === 'expenses' && (
                <div className="space-y-5">
                  <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                      <h2 className="text-xl font-bold text-slate-900 dark:text-white">Expense Entries</h2>
                      <p className="text-xs text-slate-400">Track and log expenditures, subscriptions, living costs, and bills.</p>
                    </div>
                    <button
                      onClick={() => { setEditingTx(null); setTxModalType('expense'); setIsTxModalOpen(true); }}
                      className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/30 transition-all cursor-pointer"
                    >
                      <Plus className="w-4 h-4" />
                      <span>+ Record Expense</span>
                    </button>
                  </div>

                  <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-xs">
                    <table className="w-full text-xs text-left">
                      <thead className="bg-slate-50 dark:bg-slate-900/50 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                          <th className="p-3.5">Date</th>
                          <th className="p-3.5">Category</th>
                          <th className="p-3.5">Payment Method</th>
                          <th className="p-3.5">Notes</th>
                          <th className="p-3.5 text-right">Amount</th>
                          <th className="p-3.5 text-right">Actions</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60">
                        {transactions.filter(t => t.type === 'expense').map(t => {
                          const cat = categories.find(c => c.id === t.categoryId);
                          return (
                            <tr key={t.id} className="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                              <td className="p-3.5 font-mono text-slate-500">{t.date}</td>
                              <td className="p-3.5 font-bold text-slate-900 dark:text-white">{cat?.name}</td>
                              <td className="p-3.5">{renderMethodBadge(t.paymentMethod)}</td>
                              <td className="p-3.5 text-slate-500 max-w-xs truncate">{t.notes || '—'}</td>
                              <td className="p-3.5 text-right font-mono font-bold tabular-nums text-red-600 dark:text-red-400">
                                - {fmt(t.amount)}
                              </td>
                              <td className="p-3.5 text-right space-x-1">
                                <button
                                  onClick={() => { setEditingTx(t); setTxModalType('expense'); setIsTxModalOpen(true); }}
                                  className="p-1 hover:text-blue-500 text-slate-400 cursor-pointer"
                                >
                                  <Edit3 className="w-3.5 h-3.5" />
                                </button>
                                <button
                                  onClick={() => handleDeleteTx(t.id)}
                                  className="p-1 hover:text-red-500 text-slate-400 cursor-pointer"
                                >
                                  <Trash2 className="w-3.5 h-3.5" />
                                </button>
                              </td>
                            </tr>
                          );
                        })}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}

              {/* -------------------------------------------------------------
                  VIEW: TRANSACTIONS LEDGER & CSV
                  ------------------------------------------------------------- */}
              {currentNav === 'transactions' && (
                <div className="space-y-4">
                  <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                      <h2 className="text-xl font-bold text-slate-900 dark:text-white">Transaction Master Ledger</h2>
                      <p className="text-xs text-slate-400">Unified financial ledger with instant search, multi-criteria filters, and CSV export.</p>
                    </div>
                    <button
                      onClick={exportCsv}
                      className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                    >
                      <Download className="w-3.5 h-3.5 text-blue-400" />
                      <span>Export CSV</span>
                    </button>
                  </div>

                  {/* Filter Toolbar */}
                  <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-4 space-y-3 shadow-xs">
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                      <div>
                        <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Search</label>
                        <div className="relative">
                          <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400" />
                          <input
                            type="text"
                            value={txSearch}
                            onChange={e => { setTxSearch(e.target.value); setTxPage(1); }}
                            placeholder="Keyword in notes or category..."
                            className="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-xs"
                          />
                        </div>
                      </div>

                      <div>
                        <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Type</label>
                        <select
                          value={txTypeFilter}
                          onChange={e => { setTxTypeFilter(e.target.value as any); setTxPage(1); }}
                          className="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-xs"
                        >
                          <option value="all">All Entries</option>
                          <option value="income">Income Only</option>
                          <option value="expense">Expense Only</option>
                        </select>
                      </div>

                      <div>
                        <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Category</label>
                        <select
                          value={txCatFilter}
                          onChange={e => { setTxCatFilter(parseInt(e.target.value, 10)); setTxPage(1); }}
                          className="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-xs"
                        >
                          <option value="0">All Categories</option>
                          {categories.map(c => (
                            <option key={c.id} value={c.id}>[{c.type}] {c.name}</option>
                          ))}
                        </select>
                      </div>

                      <div>
                        <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Payment Method</label>
                        <select
                          value={txMethodFilter}
                          onChange={e => { setTxMethodFilter(e.target.value); setTxPage(1); }}
                          className="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-xs"
                        >
                          <option value="all">All Methods</option>
                          <option value="UPI">UPI</option>
                          <option value="Card">Card</option>
                          <option value="Cash">Cash</option>
                          <option value="Bank">Bank Transfer</option>
                        </select>
                      </div>

                      <div>
                        <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date Range</label>
                        <div className="flex items-center gap-1">
                          <input
                            type="date"
                            value={txDateFrom}
                            onChange={e => { setTxDateFrom(e.target.value); setTxPage(1); }}
                            className="w-1/2 px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-transparent text-[11px]"
                          />
                          <input
                            type="date"
                            value={txDateTo}
                            onChange={e => { setTxDateTo(e.target.value); setTxPage(1); }}
                            className="w-1/2 px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-transparent text-[11px]"
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Ledger Table */}
                  <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-xs">
                    <table className="w-full text-xs text-left">
                      <thead className="bg-slate-50 dark:bg-slate-900/50 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                          <th className="p-3.5 cursor-pointer" onClick={() => { setTxSortBy('date'); setTxSortDir(txSortDir === 'asc' ? 'desc' : 'asc'); }}>
                            Date {txSortBy === 'date' ? (txSortDir === 'asc' ? '▲' : '▼') : ''}
                          </th>
                          <th className="p-3.5">Type</th>
                          <th className="p-3.5 cursor-pointer" onClick={() => { setTxSortBy('category'); setTxSortDir(txSortDir === 'asc' ? 'desc' : 'asc'); }}>
                            Category {txSortBy === 'category' ? (txSortDir === 'asc' ? '▲' : '▼') : ''}
                          </th>
                          <th className="p-3.5">Method</th>
                          <th className="p-3.5">Notes</th>
                          <th className="p-3.5 text-right cursor-pointer" onClick={() => { setTxSortBy('amount'); setTxSortDir(txSortDir === 'asc' ? 'desc' : 'asc'); }}>
                            Amount {txSortBy === 'amount' ? (txSortDir === 'asc' ? '▲' : '▼') : ''}
                          </th>
                          <th className="p-3.5 text-right">Actions</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60">
                        {paginatedTransactions.map(t => {
                          const cat = categories.find(c => c.id === t.categoryId);
                          return (
                            <tr key={t.id} className="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                              <td className="p-3.5 font-mono text-slate-500">{t.date}</td>
                              <td className="p-3.5">
                                <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${
                                  t.type === 'income' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-red-500/15 text-red-600 dark:text-red-400'
                                }`}>
                                  {t.type}
                                </span>
                              </td>
                              <td className="p-3.5 font-bold text-slate-900 dark:text-white">{cat?.name}</td>
                              <td className="p-3.5">{renderMethodBadge(t.paymentMethod)}</td>
                              <td className="p-3.5 text-slate-500 max-w-xs truncate">{t.notes || '—'}</td>
                              <td className={`p-3.5 text-right font-mono font-bold tabular-nums ${t.type === 'income' ? 'text-emerald-600' : 'text-red-600'}`}>
                                {t.type === 'income' ? '+' : '-'} {fmt(t.amount)}
                              </td>
                              <td className="p-3.5 text-right space-x-1">
                                <button
                                  onClick={() => { setEditingTx(t); setTxModalType(t.type); setIsTxModalOpen(true); }}
                                  className="p-1 hover:text-blue-500 text-slate-400 cursor-pointer"
                                >
                                  <Edit3 className="w-3.5 h-3.5" />
                                </button>
                                <button
                                  onClick={() => handleDeleteTx(t.id)}
                                  className="p-1 hover:text-red-500 text-slate-400 cursor-pointer"
                                >
                                  <Trash2 className="w-3.5 h-3.5" />
                                </button>
                              </td>
                            </tr>
                          );
                        })}
                      </tbody>
                    </table>

                    {/* Pagination Bar */}
                    {totalPages > 1 && (
                      <div className="p-3.5 bg-slate-50 dark:bg-slate-900/40 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span className="text-slate-500">Page {txPage} of {totalPages} ({filteredTransactions.length} entries)</span>
                        <div className="flex items-center gap-1 font-mono">
                          <button
                            onClick={() => setTxPage(p => Math.max(1, p - 1))}
                            disabled={txPage === 1}
                            className="px-2.5 py-1 rounded-lg border border-slate-300 dark:border-slate-700 disabled:opacity-30 cursor-pointer"
                          >
                            Prev
                          </button>
                          {Array.from({ length: totalPages }).map((_, i) => (
                            <button
                              key={i}
                              onClick={() => setTxPage(i + 1)}
                              className={`px-2.5 py-1 rounded-lg cursor-pointer ${txPage === i + 1 ? 'bg-blue-600 text-white font-bold' : 'border border-slate-200 dark:border-slate-700'}`}
                            >
                              {i + 1}
                            </button>
                          ))}
                          <button
                            onClick={() => setTxPage(p => Math.min(totalPages, p + 1))}
                            disabled={txPage === totalPages}
                            className="px-2.5 py-1 rounded-lg border border-slate-300 dark:border-slate-700 disabled:opacity-30 cursor-pointer"
                          >
                            Next
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              )}

              {/* -------------------------------------------------------------
                  VIEW: CATEGORY MANAGEMENT
                  ------------------------------------------------------------- */}
              {currentNav === 'categories' && (
                <div className="space-y-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <h2 className="text-xl font-bold text-slate-900 dark:text-white">Category Taxonomy</h2>
                      <p className="text-xs text-slate-400">Categories with existing transactions are locked against accidental deletion.</p>
                    </div>
                    <button
                      onClick={() => { setEditingCategory(null); setIsCategoryModalOpen(true); }}
                      className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 transition-all cursor-pointer"
                    >
                      <Plus className="w-4 h-4" />
                      <span>+ New Category</span>
                    </button>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* Expense Categories */}
                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs space-y-4">
                      <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span className="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                          <span className="w-2.5 h-2.5 rounded-full bg-red-500" />
                          <span>Expense Categories ({categories.filter(c => c.type === 'expense').length})</span>
                        </span>
                      </div>

                      <div className="divide-y divide-slate-100 dark:divide-slate-800/60">
                        {categories.filter(c => c.type === 'expense').map(c => {
                          const count = transactions.filter(t => t.categoryId === c.id).length;
                          return (
                            <div key={c.id} className="py-2.5 flex items-center justify-between text-xs">
                              <div>
                                <span className="font-bold text-slate-900 dark:text-white">{c.name}</span>
                                <span className="ml-2 text-[11px] text-slate-400">({count} records)</span>
                              </div>
                              <div className="flex items-center gap-2">
                                <button
                                  onClick={() => { setEditingCategory(c); setIsCategoryModalOpen(true); }}
                                  className="text-slate-400 hover:text-blue-500 p-1 cursor-pointer"
                                >
                                  <Edit3 className="w-3.5 h-3.5" />
                                </button>
                                <button
                                  onClick={() => handleDeleteCategory(c)}
                                  className={`p-1 ${count > 0 ? 'text-slate-300 dark:text-slate-700 cursor-not-allowed' : 'text-slate-400 hover:text-red-500 cursor-pointer'}`}
                                  title={count > 0 ? `Cannot delete (${count} transactions exist)` : 'Delete'}
                                >
                                  <Trash2 className="w-3.5 h-3.5" />
                                </button>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>

                    {/* Income Categories */}
                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs space-y-4">
                      <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span className="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                          <span className="w-2.5 h-2.5 rounded-full bg-emerald-500" />
                          <span>Income Categories ({categories.filter(c => c.type === 'income').length})</span>
                        </span>
                      </div>

                      <div className="divide-y divide-slate-100 dark:divide-slate-800/60">
                        {categories.filter(c => c.type === 'income').map(c => {
                          const count = transactions.filter(t => t.categoryId === c.id).length;
                          return (
                            <div key={c.id} className="py-2.5 flex items-center justify-between text-xs">
                              <div>
                                <span className="font-bold text-slate-900 dark:text-white">{c.name}</span>
                                <span className="ml-2 text-[11px] text-slate-400">({count} records)</span>
                              </div>
                              <div className="flex items-center gap-2">
                                <button
                                  onClick={() => { setEditingCategory(c); setIsCategoryModalOpen(true); }}
                                  className="text-slate-400 hover:text-blue-500 p-1 cursor-pointer"
                                >
                                  <Edit3 className="w-3.5 h-3.5" />
                                </button>
                                <button
                                  onClick={() => handleDeleteCategory(c)}
                                  className={`p-1 ${count > 0 ? 'text-slate-300 dark:text-slate-700 cursor-not-allowed' : 'text-slate-400 hover:text-red-500 cursor-pointer'}`}
                                  title={count > 0 ? `Cannot delete (${count} transactions exist)` : 'Delete'}
                                >
                                  <Trash2 className="w-3.5 h-3.5" />
                                </button>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* -------------------------------------------------------------
                  VIEW: MONTHLY BUDGETS
                  ------------------------------------------------------------- */}
              {currentNav === 'budgets' && (
                <div className="space-y-6">
                  <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                      <h2 className="text-xl font-bold text-slate-900 dark:text-white">Monthly Spending Limits</h2>
                      <p className="text-xs text-slate-400">Configure category ceilings. Over-budget thresholds highlight immediately.</p>
                    </div>

                    <div className="flex items-center gap-2 text-xs">
                      <select
                        value={budgetMonth}
                        onChange={e => setBudgetMonth(parseInt(e.target.value, 10))}
                        className="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#11182c] font-semibold"
                      >
                        {Array.from({ length: 12 }).map((_, i) => (
                          <option key={i + 1} value={i + 1}>
                            {new Date(2026, i).toLocaleString('default', { month: 'long' })}
                          </option>
                        ))}
                      </select>

                      <select
                        value={budgetYear}
                        onChange={e => setBudgetYear(parseInt(e.target.value, 10))}
                        className="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#11182c] font-semibold"
                      >
                        {[2025, 2026, 2027].map(y => (
                          <option key={y} value={y}>{y}</option>
                        ))}
                      </select>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    {categoryBudgets.map(item => (
                      <div
                        key={item.category.id}
                        className={`bg-white dark:bg-[#11182c] border rounded-2xl p-5 shadow-xs space-y-3.5 transition-all ${
                          item.isOver
                            ? 'border-red-500/80 bg-red-500/5 dark:bg-red-500/5'
                            : 'border-slate-200/80 dark:border-slate-800/80'
                        }`}
                      >
                        <div className="flex items-center justify-between">
                          <h3 className="font-bold text-sm text-slate-900 dark:text-white">{item.category.name}</h3>
                          {item.isOver ? (
                            <span className="text-[10px] font-bold text-red-600 bg-red-100 dark:bg-red-950/80 px-2 py-0.5 rounded-md">
                              Over by {fmt(Math.abs(item.remaining))}
                            </span>
                          ) : item.budgetAmount > 0 ? (
                            <span className="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                              {fmt(item.remaining)} left
                            </span>
                          ) : (
                            <span className="text-[11px] text-slate-400">Uncapped</span>
                          )}
                        </div>

                        {/* Progress Bar */}
                        <div className="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                          <div
                            className={`h-full rounded-full transition-all duration-300 ${
                              item.isOver ? 'bg-red-600' : item.pct >= 75 ? 'bg-amber-500' : 'bg-emerald-500'
                            }`}
                            style={{ width: `${item.budgetAmount > 0 ? Math.min(100, item.pct) : 0}%` }}
                          />
                        </div>

                        <div className="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-800">
                          <div>
                            <span className="block text-[10px] text-slate-400">Spent:</span>
                            <span className={`font-mono font-bold tabular-nums ${item.isOver ? 'text-red-600' : 'text-slate-800 dark:text-slate-200'}`}>
                              {fmt(item.spent)}
                            </span>
                          </div>

                          <div className="text-center">
                            <span className="block text-[10px] text-slate-400">Burn Rate:</span>
                            <span className="font-mono font-bold">
                              {item.budgetAmount > 0 ? `${item.pct.toFixed(0)}%` : '—'}
                            </span>
                          </div>

                          <div className="text-right">
                            <span className="block text-[10px] text-slate-400">Cap:</span>
                            <span className="font-mono font-bold text-slate-800 dark:text-slate-200">
                              {item.budgetAmount > 0 ? fmt(item.budgetAmount) : 'Not Set'}
                            </span>
                          </div>
                        </div>

                        <button
                          onClick={() => { setSelectedBudgetCat(item.category); setIsBudgetModalOpen(true); }}
                          className="w-full py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-xl transition-colors border border-dashed border-blue-300 dark:border-blue-800 cursor-pointer"
                        >
                          {item.budgetAmount > 0 ? 'Adjust Cap' : '+ Set Monthly Cap'}
                        </button>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* -------------------------------------------------------------
                  VIEW: FINANCIAL REPORTS
                  ------------------------------------------------------------- */}
              {currentNav === 'reports' && (
                <div className="space-y-6">
                  <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                      <h2 className="text-xl font-bold text-slate-900 dark:text-white">Financial Statement & Variance Report</h2>
                      <p className="text-xs text-slate-400">Month-over-month performance audit for {reportData.curMonthName} {reportYear}.</p>
                    </div>

                    <div className="flex items-center gap-2">
                      <select
                        value={reportMonth}
                        onChange={e => setReportMonth(parseInt(e.target.value, 10))}
                        className="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#11182c] text-xs font-semibold"
                      >
                        {Array.from({ length: 12 }).map((_, i) => (
                          <option key={i + 1} value={i + 1}>
                            {new Date(2026, i).toLocaleString('default', { month: 'long' })}
                          </option>
                        ))}
                      </select>

                      <button
                        onClick={() => window.print()}
                        className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold cursor-pointer"
                      >
                        <Printer className="w-3.5 h-3.5" />
                        <span>Print Statement</span>
                      </button>
                    </div>
                  </div>

                  {/* Summary Metric Strip */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs">
                      <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Income</span>
                      <div className="font-mono font-bold text-2xl text-emerald-600 mt-2">{fmt(reportData.curIncome)}</div>
                      <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                        <span className={reportData.incomeDelta.positive ? 'text-emerald-500 font-bold' : 'text-red-500 font-bold'}>
                          {reportData.incomeDelta.val}
                        </span>
                        <span>vs {reportData.prevMonthName}</span>
                      </div>
                    </div>

                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs">
                      <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Expense</span>
                      <div className="font-mono font-bold text-2xl text-red-600 mt-2">{fmt(reportData.curExpense)}</div>
                      <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                        <span className={!reportData.expenseDelta.positive ? 'text-emerald-500 font-bold' : 'text-red-500 font-bold'}>
                          {reportData.expenseDelta.val}
                        </span>
                        <span>vs {reportData.prevMonthName}</span>
                      </div>
                    </div>

                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs">
                      <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Net Surplus</span>
                      <div className={`font-mono font-bold text-2xl mt-2 ${reportData.curSavings >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                        {fmt(reportData.curSavings)}
                      </div>
                      <div className="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                        <span className={reportData.savingsDelta.positive ? 'text-emerald-500 font-bold' : 'text-red-500 font-bold'}>
                          {reportData.savingsDelta.val}
                        </span>
                        <span>vs {reportData.prevMonthName}</span>
                      </div>
                    </div>

                    <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs">
                      <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Savings Rate</span>
                      <div className={`font-mono font-bold text-2xl mt-2 ${reportData.curSavingsRate >= 20 ? 'text-emerald-600' : 'text-blue-600'}`}>
                        {reportData.curSavingsRate.toFixed(1)}%
                      </div>
                      <div className="text-[11px] text-slate-400 mt-1">
                        {reportData.curSavingsRate >= 20 ? 'Above target benchmark' : 'Target: ≥ 20%'}
                      </div>
                    </div>
                  </div>

                  {/* Category Breakdown Table */}
                  <div className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-xs">
                    <h3 className="font-bold text-sm text-slate-900 dark:text-white mb-3">Category Breakdown</h3>
                    <table className="w-full text-xs text-left">
                      <thead className="bg-slate-50 dark:bg-slate-900/50 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                          <th className="p-3">Category</th>
                          <th className="p-3 text-center">Count</th>
                          <th className="p-3 text-right">Share</th>
                          <th className="p-3 text-right">Total Spent</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60">
                        {reportData.expBreakdown.map(item => (
                          <tr key={item.name} className="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                            <td className="p-3 font-bold text-slate-900 dark:text-white">{item.name}</td>
                            <td className="p-3 text-center text-slate-400 font-mono">{item.count}</td>
                            <td className="p-3 text-right font-mono">{item.share.toFixed(1)}%</td>
                            <td className="p-3 text-right font-mono font-bold text-red-600">{fmt(item.amount)}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}

              {/* -------------------------------------------------------------
                  VIEW: SETTINGS & PROFILE
                  ------------------------------------------------------------- */}
              {currentNav === 'profile' && (
                <div className="max-w-2xl space-y-6">
                  <div>
                    <h2 className="text-xl font-bold text-slate-900 dark:text-white">Account Settings</h2>
                    <p className="text-xs text-slate-400">Manage display profile details and system defaults.</p>
                  </div>

                  <form
                    onSubmit={e => {
                      e.preventDefault();
                      const form = e.currentTarget;
                      const name = (form.elements.namedItem('name') as HTMLInputElement).value;
                      const email = (form.elements.namedItem('email') as HTMLInputElement).value;
                      const currency = (form.elements.namedItem('currency') as HTMLSelectElement).value;
                      setUser(prev => ({ ...prev, name, email, currency }));
                      addToast('success', 'Profile updated successfully.');
                    }}
                    className="bg-white dark:bg-[#11182c] border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-6 shadow-xs space-y-4 text-xs"
                  >
                    <div>
                      <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Display Name</label>
                      <input
                        type="text"
                        name="name"
                        defaultValue={user.name}
                        required
                        className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-xs font-semibold"
                      />
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                      <input
                        type="email"
                        name="email"
                        defaultValue={user.email}
                        required
                        className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-xs font-semibold"
                      />
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Active Currency Symbol</label>
                      <select
                        name="currency"
                        defaultValue={user.currency}
                        className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-xs font-bold"
                      >
                        <option value="₹">₹ (INR - Indian Rupee)</option>
                        <option value="$">$ (USD - US Dollar)</option>
                        <option value="€">€ (EUR - Euro)</option>
                        <option value="£">£ (GBP - British Pound)</option>
                        <option value="¥">¥ (JPY / CNY)</option>
                        <option value="AED">AED (UAE Dirham)</option>
                        <option value="SAR">SAR (Saudi Riyal)</option>
                      </select>
                    </div>

                    <button
                      type="submit"
                      className="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition-colors cursor-pointer"
                    >
                      Save Preferences
                    </button>
                  </form>
                </div>
              )}
            </div>
          </main>
        </div>
      )}

      {/* -------------------------------------------------------------
          MODALS & OVERLAYS
          ------------------------------------------------------------- */}
      {/* Transaction Modal */}
      {isTxModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
          <div className="bg-white dark:bg-[#11182c] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden text-xs">
            <div className="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
              <h3 className="font-bold text-sm text-slate-900 dark:text-white capitalize">
                {editingTx ? `Edit ${txModalType}` : `Record ${txModalType}`}
              </h3>
              <button onClick={() => setIsTxModalOpen(false)} className="text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">&times;</button>
            </div>

            <form onSubmit={handleSaveTx} className="p-5 space-y-3.5">
              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Amount ({user.currency}) *</label>
                <input
                  type="number"
                  step="0.01"
                  min="0.01"
                  name="amount"
                  defaultValue={editingTx ? editingTx.amount : ''}
                  placeholder="0.00"
                  required
                  autoFocus
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-base font-mono font-bold"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Category *</label>
                <select
                  name="categoryId"
                  defaultValue={editingTx ? editingTx.categoryId : ''}
                  required
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-xs font-semibold"
                >
                  <option value="">-- Choose Category --</option>
                  {categories.filter(c => c.type === txModalType).map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date *</label>
                  <input
                    type="date"
                    name="date"
                    defaultValue={editingTx ? editingTx.date : getPastDate(0)}
                    required
                    className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-xs"
                  />
                </div>

                <div>
                  <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Payment Method</label>
                  <select
                    name="paymentMethod"
                    defaultValue={editingTx ? editingTx.paymentMethod : 'UPI'}
                    className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-xs font-semibold"
                  >
                    <option value="UPI">UPI</option>
                    <option value="Card">Card</option>
                    <option value="Cash">Cash</option>
                    <option value="Bank">Bank Transfer</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Notes (Optional)</label>
                <textarea
                  name="notes"
                  defaultValue={editingTx ? editingTx.notes : ''}
                  rows={2}
                  placeholder="Notes, vendor or description..."
                  className="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-xs"
                />
              </div>

              <div className="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button
                  type="button"
                  onClick={() => setIsTxModalOpen(false)}
                  className="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className={`px-5 py-2 rounded-xl text-white font-bold cursor-pointer ${
                    txModalType === 'income' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-red-600 hover:bg-red-500'
                  }`}
                >
                  {editingTx ? 'Update Entry' : 'Save Entry'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Category Modal */}
      {isCategoryModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
          <div className="bg-white dark:bg-[#11182c] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden text-xs">
            <div className="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
              <h3 className="font-bold text-sm text-slate-900 dark:text-white">
                {editingCategory ? 'Edit Category' : 'Create Category'}
              </h3>
              <button onClick={() => setIsCategoryModalOpen(false)} className="text-slate-400 hover:text-white cursor-pointer">&times;</button>
            </div>

            <form onSubmit={handleSaveCategory} className="p-5 space-y-3.5">
              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Name</label>
                <input
                  type="text"
                  name="name"
                  defaultValue={editingCategory ? editingCategory.name : ''}
                  placeholder="e.g. Gym, Subscriptions, Consulting"
                  required
                  autoFocus
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-xs font-semibold"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Type</label>
                <select
                  name="type"
                  defaultValue={editingCategory ? editingCategory.type : 'expense'}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-xs font-semibold"
                >
                  <option value="expense">Expense Category</option>
                  <option value="income">Income Source</option>
                </select>
              </div>

              <div className="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button
                  type="button"
                  onClick={() => setIsCategoryModalOpen(false)}
                  className="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold cursor-pointer"
                >
                  Save Category
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Budget Modal */}
      {isBudgetModalOpen && selectedBudgetCat && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
          <div className="bg-white dark:bg-[#11182c] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden text-xs">
            <div className="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
              <h3 className="font-bold text-sm text-slate-900 dark:text-white">Configure Budget Cap</h3>
              <button onClick={() => setIsBudgetModalOpen(false)} className="text-slate-400 hover:text-white cursor-pointer">&times;</button>
            </div>

            <form onSubmit={handleSaveBudget} className="p-5 space-y-3.5">
              <p className="text-slate-400">
                Monthly spending target for <strong className="text-white">{selectedBudgetCat.name}</strong> in {budgetMonth}/{budgetYear}:
              </p>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Limit Amount ({user.currency})</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  name="amount"
                  defaultValue={budgets.find(b => b.categoryId === selectedBudgetCat.id && b.month === budgetMonth && b.year === budgetYear)?.amount || ''}
                  placeholder="0.00"
                  required
                  autoFocus
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent text-base font-mono font-bold"
                />
                <span className="text-[10px] text-slate-400 mt-1 block">Enter 0 to clear or remove the limit.</span>
              </div>

              <div className="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button
                  type="button"
                  onClick={() => setIsBudgetModalOpen(false)}
                  className="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold cursor-pointer"
                >
                  Save Cap
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Confirmation Modal */}
      {confirmModal.open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
          <div className="bg-white dark:bg-[#11182c] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden text-xs">
            <div className="p-4 border-b border-slate-200 dark:border-slate-800">
              <h3 className="font-bold text-sm text-slate-900 dark:text-white">{confirmModal.title}</h3>
            </div>
            <div className="p-5 text-slate-600 dark:text-slate-300">
              {confirmModal.message}
            </div>
            <div className="p-3 bg-slate-50 dark:bg-slate-900/40 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
              <button
                onClick={() => setConfirmModal(prev => ({ ...prev, open: false }))}
                className="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold cursor-pointer"
              >
                Cancel
              </button>
              <button
                onClick={confirmModal.onConfirm}
                className="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold cursor-pointer"
              >
                Yes, Delete
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
