/**
 * Lost & Found Management System - Capstone Interactive Live Application & Architecture Showcase
 * System Administration and Maintenance Capstone Platform
 */

import React, { useState } from 'react';
import {
  Search, PlusCircle, Shield, Database, Activity, Settings, Users,
  FolderLock, Archive, ArrowRight, CheckCircle2, AlertTriangle, XCircle,
  Eye, EyeOff, Lock, User, RefreshCw, Download, FileText, ChevronRight,
  Menu, X, Laptop, CreditCard, Key, Briefcase, BookOpen, Clock, MapPin,
  Phone, Mail, Tag, HardDrive, Check, ArrowLeft, LogOut
} from 'lucide-react';

// Interfaces matching MySQL Schema
interface LostItem {
  id: number;
  itemName: string;
  categoryId: number;
  categoryName: string;
  description: string;
  dateLost: string;
  locationLost: string;
  reporterName: string;
  contactPhone: string;
  contactEmail: string;
  status: 'Reported' | 'Under Review' | 'Matched' | 'Claimed' | 'Closed';
  createdAt: string;
  image?: string;
}

interface FoundItem {
  id: number;
  itemName: string;
  categoryId: number;
  categoryName: string;
  description: string;
  dateFound: string;
  locationFound: string;
  storageLocation: string;
  recorderName: string;
  status: 'Available' | 'Matched' | 'Returned' | 'Disposed';
  additionalNotes?: string;
  createdAt: string;
  image?: string;
}

interface SystemUser {
  id: number;
  username: string;
  email: string;
  fullName: string;
  role: 'admin' | 'staff';
  status: 'active' | 'inactive';
  lastLogin: string;
}

interface ActivityLog {
  id: number;
  user: string;
  role: string;
  action: string;
  details: string;
  ip: string;
  timestamp: string;
}

export default function App() {
  // App Mode State
  const [activeTab, setActiveTab] = useState<'app' | 'docs' | 'files'>('app');
  const [userRole, setUserRole] = useState<'admin' | 'staff' | 'guest'>('admin');
  const [activeNav, setActiveNav] = useState<string>('home');
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [profileDropdownOpen, setProfileDropdownOpen] = useState(false);
  const [flashMessage, setFlashMessage] = useState<{ type: 'success' | 'error' | 'warning'; text: string } | null>({
    type: 'success',
    text: 'Environment ready: XAMPP PHP/MySQL architecture loaded with real-time audit logging.'
  });

  // Selected Item Dossier State
  const [selectedLostId, setSelectedLostId] = useState<number | null>(null);
  const [selectedFoundId, setSelectedFoundId] = useState<number | null>(null);

  // Filter States
  const [searchTerm, setSearchTerm] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  // Password Visibility Toggle
  const [showPassword, setShowPassword] = useState(false);
  const [loginInput, setLoginInput] = useState('admin');
  const [passwordInput, setPasswordInput] = useState('AdminPassword123!');

  // Initial Seed Data (Matches lost_and_found_db.sql)
  const [lostItems, setLostItems] = useState<LostItem[]>([
    {
      id: 1,
      itemName: 'Apple MacBook Air 13" M2 Space Gray',
      categoryId: 1,
      categoryName: 'Electronics & Gadgets',
      description: 'In a navy blue Tomtoc protective sleeve with sticker of MIT on the lid. Left near table 4.',
      dateLost: '2026-10-12',
      locationLost: 'Central Library, 2nd Floor Study Commons',
      reporterName: 'David Chen',
      contactPhone: '555-0192',
      contactEmail: 'dchen@student.edu',
      status: 'Reported',
      createdAt: '2026-10-12 14:30',
      image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 2,
      itemName: 'Brown Leather Fossil Bifold Wallet',
      categoryId: 2,
      categoryName: 'Wallets, IDs & Cards',
      description: 'Contains University Student ID for Sarah Jenkins, MetroCard, and driver license.',
      dateLost: '2026-10-11',
      locationLost: 'Science & Engineering Hall, Lecture Room 101',
      reporterName: 'Sarah Jenkins',
      contactPhone: '555-0144',
      contactEmail: 'sjenkins@student.edu',
      status: 'Reported',
      createdAt: '2026-10-11 09:15',
      image: 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 3,
      itemName: 'Set of 4 Keys with Red Carabiner & Honda Fob',
      categoryId: 3,
      categoryName: 'Keys & Access Cards',
      description: 'House key, dorm room key #204, mailbox key, and Honda car remote fob.',
      dateLost: '2026-10-13',
      locationLost: 'Gymnasium & Wellness Center, Court B',
      reporterName: 'Liam Gallagher',
      contactPhone: '555-0188',
      contactEmail: 'lgallagher@organization.edu',
      status: 'Under Review',
      createdAt: '2026-10-13 16:45',
      image: 'https://images.unsplash.com/photo-1582139329536-e7284fece509?auto=format&fit=crop&w=600&q=80'
    }
  ]);

  const [foundItems, setFoundItems] = useState<FoundItem[]>([
    {
      id: 1,
      itemName: 'MacBook Air Space Gray in Navy Sleeve',
      categoryId: 1,
      categoryName: 'Electronics & Gadgets',
      description: 'Found on a study desk after evening closing sweep. Navy blue case matches description.',
      dateFound: '2026-10-13',
      locationFound: 'Central Library 2nd Floor',
      storageLocation: 'Custody Locker A-12',
      recorderName: 'Officer Marcus Smith',
      status: 'Available',
      additionalNotes: 'Clean condition, battery is charged.',
      createdAt: '2026-10-13 19:20',
      image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 2,
      itemName: 'Leather Bifold Wallet (Fossil)',
      categoryId: 2,
      categoryName: 'Wallets, IDs & Cards',
      description: 'Found tucked between seats in lecture hall. Handed in by custodian Mr. Gomez.',
      dateFound: '2026-10-12',
      locationFound: 'Science Hall Rm 101',
      storageLocation: 'Custody Safe Box B',
      recorderName: 'Officer Marcus Smith',
      status: 'Available',
      additionalNotes: 'Contains student identification card matching Sarah Jenkins.',
      createdAt: '2026-10-12 11:00',
      image: 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&q=80'
    },
    {
      id: 3,
      itemName: 'Sony WH-1000XM4 Noise Cancelling Headphones',
      categoryId: 1,
      categoryName: 'Electronics & Gadgets',
      description: 'Black over-ear headphones in original gray zipper carrying case.',
      dateFound: '2026-10-10',
      locationFound: 'Campus Cafeteria, Booth 8',
      storageLocation: 'Custody Locker C-03',
      recorderName: 'Officer Elena Reyes',
      status: 'Available',
      additionalNotes: 'In good condition with charging cable.',
      createdAt: '2026-10-10 14:10',
      image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80'
    }
  ]);

  const [users, setUsers] = useState<SystemUser[]>([
    { id: 1, username: 'admin', email: 'admin@organization.edu', fullName: 'System Administrator', role: 'admin', status: 'active', lastLogin: 'Today, 10:24 AM' },
    { id: 2, username: 'officer.smith', email: 'staff.smith@organization.edu', fullName: 'Officer Marcus Smith', role: 'staff', status: 'active', lastLogin: 'Yesterday, 3:15 PM' },
    { id: 3, username: 'officer.reyes', email: 'staff.reyes@organization.edu', fullName: 'Officer Elena Reyes', role: 'staff', status: 'active', lastLogin: 'Oct 12, 08:30 AM' }
  ]);

  const [activities, setActivities] = useState<ActivityLog[]>([
    { id: 1, user: 'System Administrator', role: 'admin', action: 'SYSTEM_INIT', details: 'Database schema initialized and seed records verified', ip: '127.0.0.1', timestamp: 'Oct 11, 09:00 AM' },
    { id: 2, user: 'Officer Marcus Smith', role: 'staff', action: 'REPORT_LOST', details: 'Reported lost Apple MacBook Air 13" M2', ip: '127.0.0.1', timestamp: 'Oct 12, 02:30 PM' },
    { id: 3, user: 'Officer Marcus Smith', role: 'staff', action: 'ADD_FOUND', details: 'Registered found MacBook Air Space Gray into Locker A-12', ip: '127.0.0.1', timestamp: 'Oct 13, 07:20 PM' }
  ]);

  const [systemSettings, setSystemSettings] = useState({
    systemName: 'Lost & Found Custody System',
    organizationName: 'University Campus Security & Property Custody',
    retentionDays: 90,
    defaultStorage: 'Central Security Custody Vault - Room 104',
    maxUploadMb: 5
  });

  // State for Report Form
  const [newLost, setNewLost] = useState({
    name: '', category: '1', description: '', date: new Date().toISOString().split('T')[0],
    location: '', contactName: '', phone: '', email: ''
  });

  // State for Add Found Form
  const [newFound, setNewFound] = useState({
    name: '', category: '1', description: '', date: new Date().toISOString().split('T')[0],
    location: '', storage: 'Custody Locker A-12', notes: ''
  });

  // State for Add User Form
  const [newUser, setNewUser] = useState({
    username: '', email: '', fullName: '', role: 'staff' as 'admin' | 'staff', password: ''
  });

  // Selected File in Docs
  const [selectedFileCode, setSelectedFileCode] = useState<string>('config/database.php');

  // Helper function to trigger flash messages
  const notify = (type: 'success' | 'error' | 'warning', text: string) => {
    setFlashMessage({ type, text });
    setTimeout(() => setFlashMessage(null), 5000);
  };

  // Automated Matching Calculator for Lost Item
  const getSuggestions = (lost: LostItem) => {
    const lostWords = (lost.itemName + ' ' + lost.description).toLowerCase().split(/\s+/).filter(w => w.length > 2);
    return foundItems
      .filter(f => f.status === 'Available' || f.status === 'Matched')
      .map(found => {
        let score = 0;
        const reasons: string[] = [];
        if (lost.categoryId === found.categoryId) {
          score += 40;
          reasons.push('Matching Category');
        }
        const foundWords = (found.itemName + ' ' + found.description).toLowerCase().split(/\s+/);
        const matches = lostWords.filter(w => foundWords.includes(w));
        if (matches.length > 0) {
          score += Math.min(matches.length * 15, 45);
          reasons.push(`${matches.length} keyword overlaps (${matches.slice(0, 3).join(', ')})`);
        }
        if (found.dateFound >= lost.dateLost) {
          score += 10;
          reasons.push('Found date corresponds to loss timeline');
        }
        return { found, score: Math.min(score, 98), reasons };
      })
      .filter(m => m.score >= 35)
      .sort((a, b) => b.score - a.score);
  };

  // Confirm match handler
  const handleConfirmMatch = (lostId: number, foundId: number, score: number) => {
    setLostItems(prev => prev.map(item => item.id === lostId ? { ...item, status: 'Matched' } : item));
    setFoundItems(prev => prev.map(item => item.id === foundId ? { ...item, status: 'Matched' } : item));
    const newLog: ActivityLog = {
      id: activities.length + 1,
      user: userRole === 'admin' ? 'System Administrator' : 'Officer Marcus Smith',
      role: userRole,
      action: 'CONFIRM_ITEM_MATCH',
      details: `Matched Lost Item #${lostId} with Found Item #${foundId} (${score}% match)`,
      ip: '127.0.0.1',
      timestamp: 'Just now'
    };
    setActivities(prev => [newLog, ...prev]);
    notify('success', `Match confirmed! Both items updated to 'Matched' custody status.`);
  };

  // Return to owner handler
  const handleReleaseItem = (foundId: number, claimant: string) => {
    setFoundItems(prev => prev.map(item => item.id === foundId ? { ...item, status: 'Returned', additionalNotes: `${item.additionalNotes || ''}\n[RELEASED: Handed to ${claimant}]` } : item));
    const newLog: ActivityLog = {
      id: activities.length + 1,
      user: userRole === 'admin' ? 'System Administrator' : 'Officer Marcus Smith',
      role: userRole,
      action: 'RELEASE_FOUND_ITEM',
      details: `Property released to verified claimant: ${claimant}`,
      ip: '127.0.0.1',
      timestamp: 'Just now'
    };
    setActivities(prev => [newLog, ...prev]);
    notify('success', `Item #${foundId} released to verified owner.`);
  };

  // Toggle user status
  const handleToggleUser = (userId: number) => {
    if (userId === 1) {
      notify('error', 'Security safeguard: Cannot deactivate the primary Administrator account.');
      return;
    }
    setUsers(prev => prev.map(u => u.id === userId ? { ...u, status: u.status === 'active' ? 'inactive' : 'active' } : u));
    notify('success', `User status updated successfully.`);
  };

  // Add User
  const handleCreateUser = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newUser.username || !newUser.fullName || !newUser.email) return;
    const created: SystemUser = {
      id: users.length + 1,
      username: newUser.username.toLowerCase(),
      fullName: newUser.fullName,
      email: newUser.email,
      role: newUser.role,
      status: 'active',
      lastLogin: 'Never'
    };
    setUsers(prev => [...prev, created]);
    setNewUser({ username: '', email: '', fullName: '', role: 'staff', password: '' });
    notify('success', `Account for '${created.fullName}' (${created.username}) created.`);
  };

  // Trigger Backup
  const handleTriggerBackup = () => {
    const filename = `lost_and_found_backup_${new Date().toISOString().replace(/[:.]/g, '-')}.sql`;
    const newLog: ActivityLog = {
      id: activities.length + 1,
      user: 'System Administrator',
      role: 'admin',
      action: 'BACKUP_CREATED',
      details: `Created snapshot: ${filename} (42.5 KB)`,
      ip: '127.0.0.1',
      timestamp: 'Just now'
    };
    setActivities(prev => [newLog, ...prev]);
    notify('success', `Live database backup '${filename}' generated and stored in storage/backups/.`);
  };

  // Render Status Badge
  const renderBadge = (status: string) => {
    const styles: Record<string, { bg: string; text: string; border: string }> = {
      'Reported': { bg: '#E0EDFD', text: '#1E40AF', border: '#BFDBFE' },
      'Under Review': { bg: '#FEF3C7', text: '#92400E', border: '#FDE68A' },
      'Matched': { bg: '#EDE9FE', text: '#6D28D9', border: '#DDD6FE' },
      'Available': { bg: '#DCFCE7', text: '#166534', border: '#BBF7D0' },
      'Returned': { bg: '#D1FAE5', text: '#065F46', border: '#A7F3D0' },
      'Claimed': { bg: '#D1FAE5', text: '#065F46', border: '#A7F3D0' },
      'Closed': { bg: '#F1F5F9', text: '#475569', border: '#E2E8F0' },
      'active': { bg: '#DCFCE7', text: '#166534', border: '#BBF7D0' },
      'inactive': { bg: '#FEE2E2', text: '#991B1B', border: '#FECACA' }
    };
    const s = styles[status] || { bg: '#F1F5F9', text: '#475569', border: '#E2E8F0' };
    return (
      <span
        style={{
          display: 'inline-flex',
          alignItems: 'center',
          padding: '2px 8px',
          borderRadius: '9999px',
          fontSize: '11.5px',
          fontWeight: 600,
          backgroundColor: s.bg,
          color: s.text,
          border: `1px solid ${s.border}`
        }}
      >
        {status}
      </span>
    );
  };

  // Files catalog for Capstone Explorer
  const filesList: Record<string, string> = {
    'config/database.php': `<?php
/**
 * Database Configuration & Connection Provider
 * Lost & Found Management System
 * Capstone Project: System Administration and Maintenance
 */
declare(strict_types=1);

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'lost_and_found_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

function get_db_connection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}`,
    'includes/auth.php': `<?php
/**
 * Authentication and Role-Based Authorization Provider
 * Hardened against session fixation and privilege escalation.
 */
declare(strict_types=1);

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['username']);
}

function is_admin(): bool {
    return is_logged_in() && ($_SESSION['role'] === 'admin');
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Access Denied: Administrator credentials required.');
    }
}

function login_user(array $user): void {
    session_regenerate_id(true); // Prevents session fixation
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_at'] = time();
}`,
    'includes/csrf.php': `<?php
/**
 * Cryptographic Anti-CSRF Protection
 */
declare(strict_types=1);

function get_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): void {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(get_csrf_token()) . '">';
}

function validate_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token); // Timing-attack safe
}`,
    'database/lost_and_found_db.sql': `-- Complete MySQL Schema for Capstone Project
CREATE DATABASE IF NOT EXISTS \`lost_and_found_db\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE \`lost_and_found_db\`;

CREATE TABLE \`users\` (
  \`id\` int(11) NOT NULL AUTO_INCREMENT,
  \`username\` varchar(50) NOT NULL UNIQUE,
  \`email\` varchar(100) NOT NULL UNIQUE,
  \`full_name\` varchar(100) NOT NULL,
  \`role\` enum('admin','staff') NOT NULL DEFAULT 'staff',
  \`password_hash\` varchar(255) NOT NULL,
  \`status\` enum('active','inactive') NOT NULL DEFAULT 'active',
  \`last_login\` datetime DEFAULT NULL,
  \`created_at\` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (\`id\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE \`lost_items\` (
  \`id\` int(11) NOT NULL AUTO_INCREMENT,
  \`item_name\` varchar(150) NOT NULL,
  \`category_id\` int(11) DEFAULT NULL,
  \`description\` text NOT NULL,
  \`date_lost\` date NOT NULL,
  \`location_lost\` varchar(150) NOT NULL,
  \`reported_by_user_id\` int(11) NOT NULL,
  \`contact_name\` varchar(100) DEFAULT NULL,
  \`contact_phone\` varchar(50) DEFAULT NULL,
  \`contact_email\` varchar(100) DEFAULT NULL,
  \`status\` enum('Reported','Under Review','Matched','Claimed','Closed') NOT NULL DEFAULT 'Reported',
  \`created_at\` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (\`id\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE \`found_items\` (
  \`id\` int(11) NOT NULL AUTO_INCREMENT,
  \`item_name\` varchar(150) NOT NULL,
  \`category_id\` int(11) DEFAULT NULL,
  \`description\` text NOT NULL,
  \`date_found\` date NOT NULL,
  \`location_found\` varchar(150) NOT NULL,
  \`storage_location\` varchar(150) DEFAULT 'Main Custody Locker #1',
  \`recorded_by_user_id\` int(11) NOT NULL,
  \`status\` enum('Available','Matched','Returned','Disposed') NOT NULL DEFAULT 'Available',
  \`additional_notes\` text DEFAULT NULL,
  \`created_at\` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (\`id\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`
  };

  // If viewing public landing gateway
  if (userRole === 'guest') {
    return (
      <div className="min-h-screen bg-[#F5F7FA] flex flex-col justify-between font-['Poppins',sans-serif] text-[#172033] relative overflow-x-hidden selection:bg-[#DCEBFF]">
        {/* Environment Switcher Ribbon */}
        <div className="bg-[#172033] text-white text-xs px-4 py-2 flex flex-wrap items-center justify-between gap-2 border-b border-[#3678F6]/20">
          <div className="flex items-center gap-2">
            <span className="w-2 h-2 rounded-full bg-[#16856B]"></span>
            <span className="font-semibold tracking-wide">CAPSTONE ENVIRONMENT:</span>
            <span>Local XAMPP &bull; PHP 8.x &bull; MySQL &bull; phpMyAdmin</span>
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={() => { setUserRole('admin'); setActiveNav('home'); }}
              className="bg-[#3678F6] hover:bg-[#2563EB] text-white px-2.5 py-1 rounded text-xs font-medium cursor-pointer transition-colors"
            >
              Sign In as Administrator
            </button>
            <button
              onClick={() => { setUserRole('staff'); setActiveNav('lost_items'); }}
              className="bg-[#64748B] hover:bg-[#475569] text-white px-2.5 py-1 rounded text-xs font-medium cursor-pointer transition-colors"
            >
              Sign In as Staff
            </button>
          </div>
        </div>

        {/* Liquid Glass Floating Navigation */}
        <div className="max-w-4xl mx-auto w-full px-4 pt-6">
          <header className="bg-white/80 backdrop-blur-xl border border-white/90 shadow-sm rounded-full px-6 py-3 flex items-center justify-between">
            <div className="flex items-center gap-3">
              {/* Square Logo - strictly not circular */}
              <div className="w-9 h-9 rounded-lg bg-[#172033] flex items-center justify-center border border-[#E2E8F0] overflow-hidden flex-shrink-0">
                <img
                  src="/picture/logo.svg"
                  alt="Logo"
                  className="w-full h-full object-contain"
                  style={{ borderRadius: 0 }}
                />
              </div>
              <div>
                <span className="font-['Outfit'] font-bold text-base text-[#172033] leading-none block">
                  {systemSettings.systemName}
                </span>
                <span className="text-[11px] text-[#64748B] font-medium block">
                  {systemSettings.organizationName}
                </span>
              </div>
            </div>

            <button
              onClick={() => { setUserRole('admin'); setActiveNav('home'); }}
              className="bg-[#3678F6] hover:bg-[#2563EB] text-white text-xs font-semibold px-4 py-2 rounded-full flex items-center gap-1.5 transition-colors cursor-pointer shadow-sm"
            >
              <span>Authorized Sign In</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </button>
          </header>
        </div>

        {/* Landing Hero Section */}
        <main className="max-w-3xl mx-auto px-4 py-16 text-center">
          <div className="inline-flex items-center gap-2 bg-white border border-[#E2E8F0] px-3.5 py-1 rounded-full text-xs font-semibold text-[#3678F6] shadow-xs mb-6">
            <Shield className="w-3.5 h-3.5" />
            <span>Campus Security &amp; Internal Property Custody Portal</span>
          </div>

          <h1 className="font-['Outfit'] text-4xl sm:text-5xl font-bold tracking-tight text-[#172033] mb-4 leading-tight">
            Lost something? Find your way back to it.
          </h1>

          <p className="text-base text-[#64748B] max-w-xl mx-auto mb-8 leading-relaxed">
            A simple, organized way for authorized staff to record, track, and reconnect lost belongings with the people looking for them.
          </p>

          {/* Interactive Login Card Preview */}
          <div className="max-w-md mx-auto bg-white border border-[#E2E8F0] rounded-2xl p-8 shadow-sm text-left">
            <div className="flex justify-center mb-5">
              <div className="w-16 h-16 rounded-xl bg-[#172033] border border-[#E2E8F0] shadow-sm flex items-center justify-center overflow-hidden">
                <img
                  src="/picture/logo.svg"
                  alt="Logo"
                  className="w-full h-full object-contain"
                  style={{ borderRadius: 0 }}
                />
              </div>
            </div>

            <div className="text-center mb-6">
              <span className="text-xs font-bold text-[#3678F6] uppercase tracking-wider block mb-1">
                Custody Network
              </span>
              <h2 className="font-['Outfit'] text-2xl font-bold text-[#172033]">Welcome Back</h2>
              <p className="text-xs text-[#64748B]">Sign in to access the Lost &amp; Found Management System.</p>
            </div>

            <div className="space-y-4">
              <div>
                <label className="block text-xs font-medium text-[#172033] mb-1">Username or Email</label>
                <input
                  type="text"
                  value={loginInput}
                  onChange={(e) => setLoginInput(e.target.value)}
                  className="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:border-[#3678F6]"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-[#172033] mb-1">Password</label>
                <div className="relative">
                  <input
                    type={showPassword ? 'text' : 'password'}
                    value={passwordInput}
                    onChange={(e) => setPasswordInput(e.target.value)}
                    className="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:border-[#3678F6] pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-2.5 text-[#94A3B8] hover:text-[#172033] cursor-pointer"
                  >
                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>

              <div className="pt-2 flex flex-col gap-2">
                <button
                  onClick={() => { setUserRole('admin'); setActiveNav('home'); }}
                  className="w-full py-2.5 bg-[#3678F6] hover:bg-[#2563EB] text-white font-medium text-sm rounded-lg transition-colors cursor-pointer text-center"
                >
                  Sign In as Administrator
                </button>
                <button
                  onClick={() => { setUserRole('staff'); setActiveNav('lost_items'); }}
                  className="w-full py-2.5 bg-[#F0F3F7] hover:bg-[#E2E8F0] text-[#172033] font-medium text-sm rounded-lg transition-colors cursor-pointer text-center"
                >
                  Sign In as Staff Officer
                </button>
              </div>

              <div className="text-[11px] text-center text-[#94A3B8] pt-2">
                Restricted access. Public self-registration is disabled for security custody.
              </div>
            </div>
          </div>
        </main>

        {/* Footer */}
        <footer className="border-t border-[#E2E8F0] bg-white py-6 px-4 text-center text-xs text-[#94A3B8]">
          &copy; {new Date().getFullYear()} {systemSettings.organizationName}. System Administration Capstone Project.
        </footer>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#F5F7FA] font-['Poppins',sans-serif] text-[#172033] flex flex-col selection:bg-[#DCEBFF]">
      {/* Top Capstone Control Banner */}
      <div className="bg-[#172033] text-white text-xs px-4 py-2.5 flex flex-wrap items-center justify-between gap-3 border-b border-[#3678F6]/20 z-50">
        <div className="flex items-center gap-2">
          <span className="w-2.5 h-2.5 rounded-full bg-[#16856B]"></span>
          <span className="font-bold tracking-wide">CAPSTONE RUNNER:</span>
          <span className="text-gray-300">Lost &amp; Found System &bull; XAMPP Architecture</span>
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          {/* View Mode Toggle */}
          <div className="bg-[#1F2937] p-0.5 rounded-lg flex items-center border border-gray-700">
            <button
              onClick={() => setActiveTab('app')}
              className={`px-3 py-1 rounded text-xs font-medium cursor-pointer transition-all ${activeTab === 'app' ? 'bg-[#3678F6] text-white' : 'text-gray-300 hover:text-white'}`}
            >
              Live Interactive System
            </button>
            <button
              onClick={() => setActiveTab('docs')}
              className={`px-3 py-1 rounded text-xs font-medium cursor-pointer transition-all ${activeTab === 'docs' ? 'bg-[#3678F6] text-white' : 'text-gray-300 hover:text-white'}`}
            >
              Capstone Documentation
            </button>
            <button
              onClick={() => setActiveTab('files')}
              className={`px-3 py-1 rounded text-xs font-medium cursor-pointer transition-all ${activeTab === 'files' ? 'bg-[#3678F6] text-white' : 'text-gray-300 hover:text-white'}`}
            >
              Source Code (PHP/SQL)
            </button>
          </div>

          {/* Role Switcher */}
          <div className="flex items-center gap-1.5 bg-[#1F2937] px-2 py-1 rounded-lg border border-gray-700">
            <span className="text-[11px] text-gray-400">Role:</span>
            <button
              onClick={() => { setUserRole('admin'); notify('success', 'Switched session to Administrator. Full administration tools unlocked.'); }}
              className={`px-2 py-0.5 rounded text-[11px] font-semibold cursor-pointer ${userRole === 'admin' ? 'bg-[#3678F6] text-white' : 'text-gray-300 hover:text-white'}`}
            >
              Administrator
            </button>
            <button
              onClick={() => {
                setUserRole('staff');
                if (['home', 'settings', 'manage_users', 'activity_log', 'backup_recovery', 'system_monitor'].includes(activeNav)) {
                  setActiveNav('lost_items');
                }
                notify('warning', 'Switched session to Staff. Administrative sections restricted by server RBAC.');
              }}
              className={`px-2 py-0.5 rounded text-[11px] font-semibold cursor-pointer ${userRole === 'staff' ? 'bg-[#16856B] text-white' : 'text-gray-300 hover:text-white'}`}
            >
              Staff Officer
            </button>
            <button
              onClick={() => setUserRole('guest')}
              className="px-2 py-0.5 rounded text-[11px] font-semibold text-gray-300 hover:text-white cursor-pointer"
            >
              Sign Out
            </button>
          </div>
        </div>
      </div>

      {/* Main Tab Routing */}
      {activeTab === 'docs' ? (
        /* Capstone Documentation Viewer */
        <div className="flex-1 max-w-5xl mx-auto w-full p-6 sm:p-8">
          <div className="bg-white border border-[#E2E8F0] rounded-2xl p-8 shadow-sm">
            <div className="border-b border-[#E2E8F0] pb-6 mb-6 flex items-center justify-between flex-wrap gap-4">
              <div>
                <span className="text-xs font-bold text-[#3678F6] uppercase tracking-wider">Capstone Manual</span>
                <h1 className="font-['Outfit'] text-2xl sm:text-3xl font-bold text-[#172033] mt-1">
                  System Administration and Maintenance Manual
                </h1>
                <p className="text-sm text-[#64748B] mt-1">
                  Deployment, Security, Disaster Recovery, and Preventive Maintenance for XAMPP
                </p>
              </div>
              <button
                onClick={() => setActiveTab('app')}
                className="btn btn-primary text-xs flex items-center gap-1.5"
              >
                <span>Return to Live System</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </button>
            </div>

            <div className="space-y-8 text-sm leading-relaxed text-[#172033]">
              {/* Section 1: Deployment */}
              <section>
                <h2 className="font-['Outfit'] text-lg font-bold text-[#172033] flex items-center gap-2 mb-3">
                  <HardDrive className="w-5 h-5 text-[#3678F6]" />
                  <span>1. Deployment to Local XAMPP Environment</span>
                </h2>
                <div className="bg-[#F0F3F7] p-4 rounded-xl border border-[#E2E8F0] space-y-2 text-xs">
                  <p><strong>Installation Directory:</strong> <code>C:\xampp\htdocs\lost_and_found\</code></p>
                  <p><strong>MySQL Database Name:</strong> <code>lost_and_found_db</code></p>
                  <p><strong>Schema File:</strong> <code>database/lost_and_found_db.sql</code></p>
                  <p><strong>Default phpMyAdmin URL:</strong> <a href="http://localhost/phpmyadmin" target="_blank" rel="noreferrer" className="text-[#3678F6] underline">http://localhost/phpmyadmin</a></p>
                </div>
              </section>

              {/* Section 2: Troubleshooting Matrix */}
              <section>
                <h2 className="font-['Outfit'] text-lg font-bold text-[#172033] flex items-center gap-2 mb-3">
                  <AlertTriangle className="w-5 h-5 text-[#B7791F]" />
                  <span>2. System Administration Troubleshooting Matrix</span>
                </h2>
                <div className="overflow-x-auto">
                  <table className="w-full text-left border-collapse text-xs">
                    <thead>
                      <tr className="bg-[#F0F3F7] text-[#64748B] border-b border-[#E2E8F0]">
                        <th className="p-3">Issue</th>
                        <th className="p-3">Probable Cause</th>
                        <th className="p-3">Diagnostic Step</th>
                        <th className="p-3">Solution &amp; Result</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-[#E2E8F0]">
                      <tr>
                        <td className="p-3 font-semibold text-[#172033]">Apache Port Conflict (80/443)</td>
                        <td className="p-3 text-[#64748B]">IIS or Skype binding port 80</td>
                        <td className="p-3">Run <code>netstat -ano | findstr :80</code> in cmd</td>
                        <td className="p-3">Edit <code>httpd.conf</code> to <code>Listen 8080</code>; Apache starts normally.</td>
                      </tr>
                      <tr>
                        <td className="p-3 font-semibold text-[#172033]">MySQL Failed to Start</td>
                        <td className="p-3 text-[#64748B]">Old standalone mysqld running or port 3306 lock</td>
                        <td className="p-3">Check XAMPP Error Log</td>
                        <td className="p-3">Stop standalone MySQL in <code>services.msc</code>.</td>
                      </tr>
                      <tr>
                        <td className="p-3 font-semibold text-[#172033]">DB Connection Error</td>
                        <td className="p-3 text-[#64748B]">Database not created or credentials mismatch</td>
                        <td className="p-3">Check red diagnostic screen</td>
                        <td className="p-3">Import <code>lost_and_found_db.sql</code> in phpMyAdmin.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </section>

              {/* Section 3: Maintenance Schedule */}
              <section>
                <h2 className="font-['Outfit'] text-lg font-bold text-[#172033] flex items-center gap-2 mb-3">
                  <Clock className="w-5 h-5 text-[#16856B]" />
                  <span>3. Preventive Maintenance Schedule</span>
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                  <div className="bg-[#FAFCFE] p-4 rounded-xl border border-[#E2E8F0]">
                    <div className="font-bold text-[#172033] mb-1">Daily Schedule</div>
                    <ul className="list-disc pl-4 space-y-1 text-[#64748B]">
                      <li>Verify Apache &amp; MySQL service status in XAMPP</li>
                      <li>Review activity_logs for failed login attempts</li>
                      <li>Confirm intake of newly found items</li>
                    </ul>
                  </div>
                  <div className="bg-[#FAFCFE] p-4 rounded-xl border border-[#E2E8F0]">
                    <div className="font-bold text-[#172033] mb-1">Weekly Schedule</div>
                    <ul className="list-disc pl-4 space-y-1 text-[#64748B]">
                      <li>Run full SQL database backup</li>
                      <li>Archive backup copy off-machine</li>
                      <li>Inspect uploads/items/ directory</li>
                    </ul>
                  </div>
                  <div className="bg-[#FAFCFE] p-4 rounded-xl border border-[#E2E8F0]">
                    <div className="font-bold text-[#172033] mb-1">Monthly Schedule</div>
                    <ul className="list-disc pl-4 space-y-1 text-[#64748B]">
                      <li>Enforce 90-day retention review</li>
                      <li>Deactivate former staff accounts</li>
                      <li>Trial database restoration on test schema</li>
                    </ul>
                  </div>
                </div>
              </section>
            </div>
          </div>
        </div>
      ) : activeTab === 'files' ? (
        /* Source Code & File Browser */
        <div className="flex-1 max-w-6xl mx-auto w-full p-6 sm:p-8">
          <div className="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden flex flex-col md:flex-row min-h-[640px]">
            {/* File List Sidebar */}
            <div className="w-full md:w-72 bg-[#FAFBFC] border-r border-[#E2E8F0] p-4">
              <div className="text-xs font-bold text-[#64748B] uppercase tracking-wider mb-3">Project Architecture</div>
              <div className="space-y-1 text-xs">
                {Object.keys(filesList).map(filename => (
                  <button
                    key={filename}
                    onClick={() => setSelectedFileCode(filename)}
                    className={`w-full text-left px-3 py-2 rounded-lg font-mono flex items-center justify-between cursor-pointer transition-colors ${selectedFileCode === filename ? 'bg-[#3678F6] text-white font-semibold' : 'text-[#172033] hover:bg-[#F0F3F7]'}`}
                  >
                    <span className="truncate">{filename}</span>
                    <ChevronRight className="w-3.5 h-3.5 opacity-60" />
                  </button>
                ))}
              </div>

              <div className="mt-8 pt-4 border-t border-[#E2E8F0]">
                <div className="text-[11px] text-[#64748B] mb-2 font-medium">Physical Location:</div>
                <code className="text-[11px] bg-[#E2E8F0] p-1.5 rounded block text-[#172033] break-all">
                  C:\xampp\htdocs\lost_and_found\
                </code>
              </div>
            </div>

            {/* Code Viewer Panel */}
            <div className="flex-1 p-6 flex flex-col">
              <div className="flex items-center justify-between pb-4 border-b border-[#E2E8F0] mb-4">
                <div className="flex items-center gap-2">
                  <FileText className="w-4 h-4 text-[#3678F6]" />
                  <span className="font-mono text-sm font-bold text-[#172033]">{selectedFileCode}</span>
                </div>
                <button
                  onClick={() => {
                    navigator.clipboard.writeText(filesList[selectedFileCode] || '');
                    notify('success', `Copied ${selectedFileCode} contents to clipboard!`);
                  }}
                  className="btn btn-secondary text-xs flex items-center gap-1.5"
                >
                  <Check className="w-3.5 h-3.5" />
                  <span>Copy File Content</span>
                </button>
              </div>

              <pre className="flex-1 bg-[#172033] text-gray-200 p-4 rounded-xl text-xs font-mono overflow-auto max-h-[500px] leading-relaxed">
                <code>{filesList[selectedFileCode] || '// File content'}</code>
              </pre>
            </div>
          </div>
        </div>
      ) : (
        /* Live Application Experience */
        <div className="flex-1 flex min-h-screen">
          {/* Role-Restricted Application Sidebar */}
          <aside className={`w-[260px] bg-white border-r border-[#E2E8F0] flex flex-col fixed md:sticky top-0 h-screen z-40 transition-transform ${mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'}`}>
            {/* Brand Header with Square Logo */}
            <div className="h-[68px] flex items-center px-5 border-b border-[#E2E8F0]">
              <div className="flex items-center gap-3">
                {/* Square Logo container - strictly not circular */}
                <div className="w-[38px] h-[38px] rounded-lg bg-[#172033] flex items-center justify-center border border-[#E2E8F0] overflow-hidden flex-shrink-0">
                  <img
                    src="/picture/logo.svg"
                    alt="Logo"
                    className="w-full h-full object-contain"
                    style={{ borderRadius: 0 }}
                  />
                </div>
                <div>
                  <div className="font-['Outfit'] font-bold text-sm text-[#172033] leading-none">Lost &amp; Found</div>
                  <div className="text-[11px] text-[#64748B] font-medium mt-0.5">
                    {userRole === 'admin' ? 'Administration' : 'Staff Custody'}
                  </div>
                </div>
              </div>
            </div>

            {/* Navigation Items */}
            <nav className="flex-1 overflow-y-auto p-3.5 space-y-1 text-[13.5px]">
              {userRole === 'admin' && (
                <button
                  onClick={() => { setActiveNav('home'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                  className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'home' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
                >
                  <Activity className="w-[18px] h-[18px]" />
                  <span>Home</span>
                </button>
              )}

              <button
                onClick={() => { setActiveNav('lost_items'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'lost_items' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
              >
                <Search className="w-[18px] h-[18px]" />
                <span>Lost Items</span>
              </button>

              <button
                onClick={() => { setActiveNav('report_lost'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'report_lost' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
              >
                <PlusCircle className="w-[18px] h-[18px]" />
                <span>Report Lost Item</span>
              </button>

              <button
                onClick={() => { setActiveNav('found_items'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'found_items' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
              >
                <Archive className="w-[18px] h-[18px]" />
                <span>Found Items</span>
              </button>

              <button
                onClick={() => { setActiveNav('add_found'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'add_found' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
              >
                <PlusCircle className="w-[18px] h-[18px]" />
                <span>Add Found Item</span>
              </button>

              {/* Administrator Only Section strictly hidden from Staff */}
              {userRole === 'admin' && (
                <>
                  <div className="text-[11px] font-bold uppercase tracking-wider text-[#94A3B8] px-3 pt-4 pb-1">
                    Administration
                  </div>

                  <button
                    onClick={() => { setActiveNav('settings'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'settings' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
                  >
                    <Settings className="w-[18px] h-[18px]" />
                    <span>Settings</span>
                  </button>

                  <button
                    onClick={() => { setActiveNav('manage_users'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'manage_users' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
                  >
                    <Users className="w-[18px] h-[18px]" />
                    <span>Manage Users</span>
                  </button>

                  <button
                    onClick={() => { setActiveNav('activity_log'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'activity_log' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
                  >
                    <Activity className="w-[18px] h-[18px]" />
                    <span>Activity Log</span>
                  </button>

                  <button
                    onClick={() => { setActiveNav('backup_recovery'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'backup_recovery' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
                  >
                    <Database className="w-[18px] h-[18px]" />
                    <span>Backup &amp; Recovery</span>
                  </button>

                  <button
                    onClick={() => { setActiveNav('system_monitor'); setSelectedLostId(null); setSelectedFoundId(null); setMobileMenuOpen(false); }}
                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors cursor-pointer ${activeNav === 'system_monitor' ? 'bg-[#DCEBFF] text-[#2563EB] font-semibold' : 'text-[#64748B] hover:bg-[#F0F3F7] hover:text-[#172033]'}`}
                  >
                    <HardDrive className="w-[18px] h-[18px]" />
                    <span>System Monitor</span>
                  </button>
                </>
              )}
            </nav>

            {/* Sidebar Footer User Details */}
            <div className="p-4 border-t border-[#E2E8F0] bg-[#FAFBFC]">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-lg bg-[#E2E8F0] text-[#172033] font-bold text-xs flex items-center justify-center">
                  {userRole === 'admin' ? 'A' : 'S'}
                </div>
                <div className="flex-1 min-w-0">
                  <div className="text-xs font-semibold text-[#172033] truncate">
                    {userRole === 'admin' ? 'System Administrator' : 'Officer Marcus Smith'}
                  </div>
                  <div className="text-[11px] text-[#3678F6] font-semibold uppercase">
                    {userRole}
                  </div>
                </div>
              </div>
            </div>
          </aside>

          {/* Main Content Area */}
          <div className="flex-1 flex flex-col min-w-0">
            {/* Liquid Glass Top Navigation Bar */}
            <header className="h-[68px] sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-[#E2E8F0] px-6 sm:px-8 flex items-center justify-between">
              <div className="flex items-center gap-4">
                <button
                  onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                  className="md:hidden p-1.5 rounded-lg text-[#172033] hover:bg-[#F0F3F7] cursor-pointer"
                >
                  <Menu className="w-5 h-5" />
                </button>
                <h1 className="font-['Outfit'] font-bold text-lg text-[#172033] capitalize">
                  {activeNav.replace('_', ' ')}
                </h1>
              </div>

              {/* Profile Menu Trigger */}
              <div className="relative">
                <button
                  onClick={() => setProfileDropdownOpen(!profileDropdownOpen)}
                  className="flex items-center gap-2 px-3 py-1.5 border border-[#E2E8F0] rounded-xl bg-white hover:bg-[#F0F3F7] text-xs font-medium cursor-pointer transition-colors"
                >
                  <span className="w-6 h-6 rounded-md bg-[#DCEBFF] text-[#2563EB] flex items-center justify-center font-bold text-[11px]">
                    {userRole === 'admin' ? 'A' : 'S'}
                  </span>
                  <span className="hidden sm:inline font-semibold">
                    {userRole === 'admin' ? 'Administrator' : 'Officer Smith'}
                  </span>
                </button>

                {profileDropdownOpen && (
                  <div className="absolute right-0 mt-2 w-56 bg-white border border-[#E2E8F0] rounded-xl shadow-lg p-2 z-50 text-xs">
                    <div className="px-3 py-2 border-b border-[#F0F3F7] mb-1">
                      <div className="text-[#64748B]">Signed in as</div>
                      <div className="font-semibold text-[#172033] truncate">
                        {userRole === 'admin' ? 'admin@organization.edu' : 'officer.smith@organization.edu'}
                      </div>
                    </div>
                    <button
                      onClick={() => { setActiveNav('profile'); setProfileDropdownOpen(false); }}
                      className="w-full text-left px-3 py-2 rounded-lg hover:bg-[#F0F3F7] flex items-center gap-2 cursor-pointer"
                    >
                      <User className="w-4 h-4 text-[#64748B]" />
                      <span>Account Profile</span>
                    </button>
                    {userRole === 'admin' && (
                      <button
                        onClick={() => { setActiveNav('settings'); setProfileDropdownOpen(false); }}
                        className="w-full text-left px-3 py-2 rounded-lg hover:bg-[#F0F3F7] flex items-center gap-2 cursor-pointer"
                      >
                        <Settings className="w-4 h-4 text-[#64748B]" />
                        <span>System Settings</span>
                      </button>
                    )}
                    <div className="my-1 border-t border-[#F0F3F7]"></div>
                    <button
                      onClick={() => { setUserRole('guest'); setProfileDropdownOpen(false); }}
                      className="w-full text-left px-3 py-2 rounded-lg hover:bg-red-50 text-[#C2414B] flex items-center gap-2 cursor-pointer"
                    >
                      <LogOut className="w-4 h-4" />
                      <span>Sign Out</span>
                    </button>
                  </div>
                )}
              </div>
            </header>

            {/* Viewport Content */}
            <main className="p-6 sm:p-8 flex-1 max-w-7xl w-full mx-auto space-y-6">
              {/* Flash Alert */}
              {flashMessage && (
                <div className={`p-3.5 px-4 rounded-xl text-xs font-medium flex items-center justify-between border ${flashMessage.type === 'success' ? 'bg-[#DCFCE7] text-[#065F46] border-[#A7F3D0]' : flashMessage.type === 'error' ? 'bg-[#FEE2E2] text-[#991B1B] border-[#FECACA]' : 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]'}`}>
                  <div className="flex items-center gap-2">
                    {flashMessage.type === 'success' ? <CheckCircle2 className="w-4 h-4 text-[#16856B]" /> : <AlertTriangle className="w-4 h-4" />}
                    <span>{flashMessage.text}</span>
                  </div>
                  <button onClick={() => setFlashMessage(null)} className="opacity-70 hover:opacity-100 cursor-pointer">
                    <X className="w-3.5 h-3.5" />
                  </button>
                </div>
              )}

              {/* VIEW 1: HOME DASHBOARD (Admin Only) */}
              {activeNav === 'home' && userRole === 'admin' && (
                <div className="space-y-6">
                  {/* Quick Actions Ribbon */}
                  <div className="bg-white border border-[#E2E8F0] p-3.5 px-5 rounded-2xl shadow-xs flex flex-wrap items-center gap-3">
                    <span className="text-xs font-bold text-[#64748B] mr-2">Quick Actions:</span>
                    <button
                      onClick={() => setActiveNav('report_lost')}
                      className="btn btn-primary text-xs py-1.5 px-3 flex items-center gap-1.5"
                    >
                      <PlusCircle className="w-3.5 h-3.5" />
                      <span>Report Lost Item</span>
                    </button>
                    <button
                      onClick={() => setActiveNav('add_found')}
                      className="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5"
                    >
                      <Archive className="w-3.5 h-3.5" />
                      <span>Add Found Item</span>
                    </button>
                    <button
                      onClick={handleTriggerBackup}
                      className="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 ml-auto"
                    >
                      <Database className="w-3.5 h-3.5" />
                      <span>Trigger Database Backup</span>
                    </button>
                  </div>

                  {/* Real Metrics Grid */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div className="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs relative overflow-hidden">
                      <div className="absolute top-0 left-0 right-0 h-1 bg-[#3678F6]"></div>
                      <div className="text-xs font-semibold text-[#64748B] mb-2">Total Lost Items</div>
                      <div className="font-['Outfit'] text-3xl font-bold text-[#172033]">{lostItems.length}</div>
                      <div className="text-[11px] text-[#94A3B8] mt-2">Active inquiries reported</div>
                    </div>

                    <div className="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs relative overflow-hidden">
                      <div className="absolute top-0 left-0 right-0 h-1 bg-[#16856B]"></div>
                      <div className="text-xs font-semibold text-[#64748B] mb-2">Total Found Items</div>
                      <div className="font-['Outfit'] text-3xl font-bold text-[#172033]">{foundItems.length}</div>
                      <div className="text-[11px] text-[#94A3B8] mt-2">Held in physical custody</div>
                    </div>

                    <div className="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs relative overflow-hidden">
                      <div className="absolute top-0 left-0 right-0 h-1 bg-[#8B5CF6]"></div>
                      <div className="text-xs font-semibold text-[#64748B] mb-2">Items Matched</div>
                      <div className="font-['Outfit'] text-3xl font-bold text-[#172033]">
                        {foundItems.filter(f => f.status === 'Matched').length + lostItems.filter(l => l.status === 'Matched').length}
                      </div>
                      <div className="text-[11px] text-[#94A3B8] mt-2">Cross-correlated records</div>
                    </div>

                    <div className="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs relative overflow-hidden">
                      <div className="absolute top-0 left-0 right-0 h-1 bg-[#B7791F]"></div>
                      <div className="text-xs font-semibold text-[#64748B] mb-2">Returned / Closed</div>
                      <div className="font-['Outfit'] text-3xl font-bold text-[#172033]">
                        {foundItems.filter(f => f.status === 'Returned').length}
                      </div>
                      <div className="text-[11px] text-[#94A3B8] mt-2">Restored to lawful owners</div>
                    </div>
                  </div>

                  {/* Dashboard Split View */}
                  <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Recent Lost & Found List */}
                    <div className="lg:col-span-2 space-y-6">
                      <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                        <div className="flex items-center justify-between mb-4">
                          <h3 className="font-['Outfit'] font-bold text-base text-[#172033]">
                            Recently Reported Lost Property
                          </h3>
                          <button onClick={() => setActiveNav('lost_items')} className="text-xs font-semibold text-[#3678F6] hover:underline cursor-pointer">
                            View All &rarr;
                          </button>
                        </div>
                        <div className="overflow-x-auto">
                          <table className="w-full text-left text-xs border-collapse">
                            <thead>
                              <tr className="bg-[#F0F3F7] text-[#64748B] border-b border-[#E2E8F0]">
                                <th className="p-3">Item</th>
                                <th className="p-3">Category</th>
                                <th className="p-3">Date Lost</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 text-right">Action</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-[#E2E8F0]">
                              {lostItems.slice(0, 3).map(item => (
                                <tr key={item.id} className="hover:bg-[#FAFCFE]">
                                  <td className="p-3 font-semibold text-[#172033]">{item.itemName}</td>
                                  <td className="p-3 text-[#64748B]">{item.categoryName}</td>
                                  <td className="p-3 text-[#64748B]">{item.dateLost}</td>
                                  <td className="p-3">{renderBadge(item.status)}</td>
                                  <td className="p-3 text-right">
                                    <button
                                      onClick={() => { setSelectedLostId(item.id); setActiveNav('lost_items'); }}
                                      className="btn btn-secondary text-xs py-1 px-2.5"
                                    >
                                      Inspect
                                    </button>
                                  </td>
                                </tr>
                              ))}
                            </tbody>
                          </table>
                        </div>
                      </div>

                      <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                        <div className="flex items-center justify-between mb-4">
                          <h3 className="font-['Outfit'] font-bold text-base text-[#172033]">
                            Recently Handed-In Found Items
                          </h3>
                          <button onClick={() => setActiveNav('found_items')} className="text-xs font-semibold text-[#3678F6] hover:underline cursor-pointer">
                            View All &rarr;
                          </button>
                        </div>
                        <div className="overflow-x-auto">
                          <table className="w-full text-left text-xs border-collapse">
                            <thead>
                              <tr className="bg-[#F0F3F7] text-[#64748B] border-b border-[#E2E8F0]">
                                <th className="p-3">Item</th>
                                <th className="p-3">Location Found</th>
                                <th className="p-3">Storage Locker</th>
                                <th className="p-3">Status</th>
                                <th className="p-3 text-right">Action</th>
                              </tr>
                            </thead>
                            <tbody className="divide-y divide-[#E2E8F0]">
                              {foundItems.slice(0, 3).map(item => (
                                <tr key={item.id} className="hover:bg-[#FAFCFE]">
                                  <td className="p-3 font-semibold text-[#172033]">{item.itemName}</td>
                                  <td className="p-3 text-[#64748B]">{item.locationFound}</td>
                                  <td className="p-3">
                                    <span className="font-mono bg-[#F0F3F7] px-2 py-0.5 rounded text-[11px] text-[#172033]">
                                      {item.storageLocation}
                                    </span>
                                  </td>
                                  <td className="p-3">{renderBadge(item.status)}</td>
                                  <td className="p-3 text-right">
                                    <button
                                      onClick={() => { setSelectedFoundId(item.id); setActiveNav('found_items'); }}
                                      className="btn btn-secondary text-xs py-1 px-2.5"
                                    >
                                      Inspect
                                    </button>
                                  </td>
                                </tr>
                              ))}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    {/* Right Activity Feed */}
                    <div className="space-y-6">
                      <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                        <div className="flex items-center justify-between mb-4">
                          <h3 className="font-['Outfit'] font-bold text-base text-[#172033]">Activity Audit Trail</h3>
                          <button onClick={() => setActiveNav('activity_log')} className="text-xs font-semibold text-[#3678F6] hover:underline cursor-pointer">
                            Full Log &rarr;
                          </button>
                        </div>
                        <div className="space-y-4 text-xs">
                          {activities.slice(0, 5).map(act => (
                            <div key={act.id} className="flex items-start gap-3 pb-3 border-b border-[#F0F3F7] last:border-none last:pb-0">
                              <span className="w-2 h-2 rounded-full bg-[#3678F6] mt-1.5 flex-shrink-0"></span>
                              <div className="flex-1 min-w-0">
                                <div className="font-bold text-[#172033]">{act.action}</div>
                                <div className="text-[#64748B] text-[11px] mt-0.5">{act.details}</div>
                                <div className="text-[10px] text-[#94A3B8] mt-1">
                                  By {act.user} &bull; {act.timestamp}
                                </div>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>

                      {/* Capstone Health Note */}
                      <div className="bg-[#FAFCFE] border border-[#E2E8F0] rounded-2xl p-5 text-xs space-y-2">
                        <div className="font-bold text-[#172033] flex items-center gap-1.5">
                          <CheckCircle2 className="w-4 h-4 text-[#16856B]" />
                          <span>Capstone Architecture Status</span>
                        </div>
                        <p className="text-[#64748B] leading-relaxed">
                          MySQL connection healthy. Strict role verification active. CSRF protection verified on state-mutating requests.
                        </p>
                        <button
                          onClick={() => setActiveNav('system_monitor')}
                          className="btn btn-secondary text-xs w-full justify-center mt-2"
                        >
                          View System Monitor Diagnostics
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* VIEW 2: LOST ITEMS REGISTRY */}
              {activeNav === 'lost_items' && (
                <div className="space-y-6">
                  {selectedLostId !== null ? (
                    /* Detailed Lost Item Dossier & Matching Suggestions */
                    (() => {
                      const item = lostItems.find(l => l.id === selectedLostId);
                      if (!item) return null;
                      const suggestions = getSuggestions(item);

                      return (
                        <div className="space-y-6">
                          <div className="flex items-center justify-between">
                            <button
                              onClick={() => setSelectedLostId(null)}
                              className="btn btn-secondary text-xs flex items-center gap-1.5"
                            >
                              <ArrowLeft className="w-3.5 h-3.5" />
                              <span>Back to Lost Items Registry</span>
                            </button>
                          </div>

                          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            {/* Dossier Left */}
                            <div className="lg:col-span-2 space-y-6">
                              <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                                <div className="flex items-start justify-between mb-4">
                                  <div>
                                    <span className="text-xs font-bold text-[#3678F6] uppercase tracking-wider">
                                      {item.categoryName}
                                    </span>
                                    <h2 className="font-['Outfit'] text-2xl font-bold text-[#172033] mt-1">
                                      {item.itemName}
                                    </h2>
                                  </div>
                                  {renderBadge(item.status)}
                                </div>

                                {item.image && (
                                  <div className="w-full h-64 rounded-xl overflow-hidden border border-[#E2E8F0] mb-6 bg-[#FAFBFC]">
                                    <img src={item.image} alt={item.itemName} className="w-full h-full object-contain" />
                                  </div>
                                )}

                                <div className="mb-6">
                                  <div className="text-xs font-bold text-[#94A3B8] uppercase mb-2">Description</div>
                                  <div className="p-4 bg-[#F0F3F7] rounded-xl text-sm leading-relaxed border border-[#E2E8F0]">
                                    {item.description}
                                  </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-[#F0F3F7] text-xs">
                                  <div>
                                    <span className="text-[#64748B]">Date Lost</span>
                                    <div className="font-semibold text-sm mt-0.5">{item.dateLost}</div>
                                  </div>
                                  <div>
                                    <span className="text-[#64748B]">Reported Location</span>
                                    <div className="font-semibold text-sm mt-0.5">{item.locationLost}</div>
                                  </div>
                                  <div>
                                    <span className="text-[#64748B]">Reported By</span>
                                    <div className="font-semibold text-sm mt-0.5">{item.reporterName}</div>
                                  </div>
                                  <div>
                                    <span className="text-[#64748B]">Date Registered</span>
                                    <div className="font-semibold text-sm mt-0.5">{item.createdAt}</div>
                                  </div>
                                </div>
                              </div>

                              {/* Owner Contact Card */}
                              <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                                <h3 className="font-['Outfit'] font-bold text-base text-[#172033] mb-4">
                                  Reporter / Owner Contact Details
                                </h3>
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                                  <div>
                                    <span className="text-[#64748B]">Full Name</span>
                                    <div className="font-semibold text-sm mt-0.5">{item.reporterName}</div>
                                  </div>
                                  <div>
                                    <span className="text-[#64748B]">Phone</span>
                                    <div className="font-semibold text-sm mt-0.5">{item.contactPhone}</div>
                                  </div>
                                  <div>
                                    <span className="text-[#64748B]">Email</span>
                                    <div className="font-semibold text-sm mt-0.5">{item.contactEmail}</div>
                                  </div>
                                </div>
                              </div>
                            </div>

                            {/* Matching Engine Right */}
                            <div className="space-y-6">
                              <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                                <div className="border-b border-[#F0F3F7] pb-3 mb-4">
                                  <h3 className="font-['Outfit'] font-bold text-base text-[#172033] flex items-center gap-2">
                                    <Shield className="w-4 h-4 text-[#3678F6]" />
                                    <span>Suggested Found Matches</span>
                                  </h3>
                                  <p className="text-[11px] text-[#64748B] mt-1">
                                    Correlated by category, keywords, and timeline.
                                  </p>
                                </div>

                                {suggestions.length === 0 ? (
                                  <div className="text-center py-8 text-xs text-[#94A3B8]">
                                    No high-confidence matches found in current custody inventory.
                                  </div>
                                ) : (
                                  <div className="space-y-4">
                                    {suggestions.map(({ found, score, reasons }) => (
                                      <div key={found.id} className="p-4 border border-[#E2E8F0] rounded-xl bg-[#FAFCFE] text-xs space-y-2">
                                        <div className="flex items-center justify-between">
                                          <span className="bg-[#DCFCE7] text-[#166534] font-bold px-2 py-0.5 rounded-full text-[11px]">
                                            {score}% Confidence Match
                                          </span>
                                          <span className="text-[11px] text-[#94A3B8]">{found.dateFound}</span>
                                        </div>

                                        <div className="font-bold text-sm text-[#172033]">{found.itemName}</div>
                                        <p className="text-[#64748B] line-clamp-2">{found.description}</p>
                                        <div className="text-[11px] text-[#172033]">
                                          <strong>Location:</strong> {found.locationFound} &bull; <strong>Storage:</strong> {found.storageLocation}
                                        </div>

                                        <button
                                          onClick={() => handleConfirmMatch(item.id, found.id, score)}
                                          className="btn btn-primary text-xs w-full justify-center mt-2"
                                        >
                                          Confirm Property Match
                                        </button>
                                      </div>
                                    ))}
                                  </div>
                                )}
                              </div>
                            </div>
                          </div>
                        </div>
                      );
                    })()
                  ) : (
                    /* Lost Items List Table */
                    <div className="space-y-4">
                      {/* Search & Filter Ribbon */}
                      <div className="bg-white border border-[#E2E8F0] p-4 rounded-2xl shadow-xs flex flex-wrap items-center gap-3">
                        <div className="flex-1 min-w-[200px]">
                          <input
                            type="text"
                            placeholder="Search item, location, description..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-full px-3 py-2 text-xs border border-[#E2E8F0] rounded-lg focus:outline-none focus:border-[#3678F6]"
                          />
                        </div>

                        <select
                          value={statusFilter}
                          onChange={(e) => setStatusFilter(e.target.value)}
                          className="px-3 py-2 text-xs border border-[#E2E8F0] rounded-lg bg-white"
                        >
                          <option value="">All Statuses</option>
                          <option value="Reported">Reported</option>
                          <option value="Under Review">Under Review</option>
                          <option value="Matched">Matched</option>
                          <option value="Claimed">Claimed</option>
                        </select>

                        <button
                          onClick={() => setActiveNav('report_lost')}
                          className="btn btn-primary text-xs flex items-center gap-1.5 ml-auto"
                        >
                          <PlusCircle className="w-3.5 h-3.5" />
                          <span>Report Lost Item</span>
                        </button>
                      </div>

                      {/* Items Table */}
                      <div className="bg-white border border-[#E2E8F0] rounded-2xl shadow-xs overflow-hidden">
                        <table className="w-full text-left text-xs border-collapse">
                          <thead>
                            <tr className="bg-[#F0F3F7] text-[#64748B] border-b border-[#E2E8F0]">
                              <th className="p-3.5 font-semibold">Item Name</th>
                              <th className="p-3.5 font-semibold">Category</th>
                              <th className="p-3.5 font-semibold">Date Lost</th>
                              <th className="p-3.5 font-semibold">Location</th>
                              <th className="p-3.5 font-semibold">Reported By</th>
                              <th className="p-3.5 font-semibold">Status</th>
                              <th className="p-3.5 font-semibold text-right">Action</th>
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-[#E2E8F0]">
                            {lostItems
                              .filter(i => !searchTerm || i.itemName.toLowerCase().includes(searchTerm.toLowerCase()) || i.locationLost.toLowerCase().includes(searchTerm.toLowerCase()))
                              .filter(i => !statusFilter || i.status === statusFilter)
                              .map(item => (
                                <tr key={item.id} className="hover:bg-[#FAFCFE] transition-colors">
                                  <td className="p-3.5 font-semibold text-[#172033]">{item.itemName}</td>
                                  <td className="p-3.5 text-[#64748B]">{item.categoryName}</td>
                                  <td className="p-3.5 text-[#64748B]">{item.dateLost}</td>
                                  <td className="p-3.5 text-[#64748B]">{item.locationLost}</td>
                                  <td className="p-3.5 text-[#64748B]">{item.reporterName}</td>
                                  <td className="p-3.5">{renderBadge(item.status)}</td>
                                  <td className="p-3.5 text-right">
                                    <button
                                      onClick={() => setSelectedLostId(item.id)}
                                      className="btn btn-secondary text-xs py-1 px-3"
                                    >
                                      Inspect &amp; Match
                                    </button>
                                  </td>
                                </tr>
                              ))}
                          </tbody>
                        </table>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* VIEW 3: REPORT LOST ITEM FORM */}
              {activeNav === 'report_lost' && (
                <div className="max-w-3xl mx-auto bg-white border border-[#E2E8F0] rounded-2xl p-6 sm:p-8 shadow-xs">
                  <div className="border-b border-[#E2E8F0] pb-4 mb-6 flex items-center justify-between">
                    <div>
                      <h2 className="font-['Outfit'] text-xl font-bold text-[#172033]">Report a Lost Belonging</h2>
                      <p className="text-xs text-[#64748B] mt-0.5">Record incident characteristics for automated match recommendations.</p>
                    </div>
                    <button onClick={() => setActiveNav('lost_items')} className="btn btn-secondary text-xs">
                      &larr; Back to Registry
                    </button>
                  </div>

                  <form
                    onSubmit={(e) => {
                      e.preventDefault();
                      if (!newLost.name || !newLost.description || !newLost.location) {
                        notify('error', 'Please fill in all required fields marked with an asterisk.');
                        return;
                      }
                      const catNames: Record<string, string> = {
                        '1': 'Electronics & Gadgets',
                        '2': 'Wallets, IDs & Cards',
                        '3': 'Keys & Access Cards',
                        '4': 'Bags & Backpacks'
                      };
                      const item: LostItem = {
                        id: lostItems.length + 1,
                        itemName: newLost.name,
                        categoryId: parseInt(newLost.category),
                        categoryName: catNames[newLost.category] || 'General',
                        description: newLost.description,
                        dateLost: newLost.date,
                        locationLost: newLost.location,
                        reporterName: newLost.contactName || (userRole === 'admin' ? 'Administrator' : 'Officer Marcus Smith'),
                        contactPhone: newLost.phone || '555-0100',
                        contactEmail: newLost.email || 'reporter@organization.edu',
                        status: 'Reported',
                        createdAt: 'Just now'
                      };
                      setLostItems(prev => [item, ...prev]);
                      const newLog: ActivityLog = {
                        id: activities.length + 1,
                        user: userRole === 'admin' ? 'System Administrator' : 'Officer Marcus Smith',
                        role: userRole,
                        action: 'REPORT_LOST',
                        details: `Filed report for ${item.itemName}`,
                        ip: '127.0.0.1',
                        timestamp: 'Just now'
                      };
                      setActivities(prev => [newLog, ...prev]);
                      notify('success', `Lost item report #${item.id} successfully recorded in database!`);
                      setSelectedLostId(item.id);
                      setActiveNav('lost_items');
                    }}
                    className="space-y-4 text-xs"
                  >
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="sm:col-span-2">
                        <label className="block font-medium text-[#172033] mb-1">Item Name &amp; Model *</label>
                        <input
                          type="text"
                          required
                          value={newLost.name}
                          onChange={(e) => setNewLost({ ...newLost, name: e.target.value })}
                          placeholder="e.g. Dell XPS 15 Laptop in Black Case"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg focus:outline-none focus:border-[#3678F6]"
                        />
                      </div>

                      <div>
                        <label className="block font-medium text-[#172033] mb-1">Category *</label>
                        <select
                          value={newLost.category}
                          onChange={(e) => setNewLost({ ...newLost, category: e.target.value })}
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg bg-white"
                        >
                          <option value="1">Electronics &amp; Gadgets</option>
                          <option value="2">Wallets, IDs &amp; Cards</option>
                          <option value="3">Keys &amp; Access Cards</option>
                          <option value="4">Bags &amp; Backpacks</option>
                        </select>
                      </div>

                      <div>
                        <label className="block font-medium text-[#172033] mb-1">Date Lost *</label>
                        <input
                          type="date"
                          required
                          value={newLost.date}
                          onChange={(e) => setNewLost({ ...newLost, date: e.target.value })}
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>

                      <div className="sm:col-span-2">
                        <label className="block font-medium text-[#172033] mb-1">Location Where Lost *</label>
                        <input
                          type="text"
                          required
                          value={newLost.location}
                          onChange={(e) => setNewLost({ ...newLost, location: e.target.value })}
                          placeholder="e.g. Central Library 3rd Floor"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>

                      <div className="sm:col-span-2">
                        <label className="block font-medium text-[#172033] mb-1">Physical Description &amp; Distinct Markings *</label>
                        <textarea
                          rows={3}
                          required
                          value={newLost.description}
                          onChange={(e) => setNewLost({ ...newLost, description: e.target.value })}
                          placeholder="Describe color, serial number, stickers, dents..."
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        ></textarea>
                      </div>

                      <div className="sm:col-span-2 pt-2 border-t border-[#F0F3F7]">
                        <div className="font-bold text-[#172033] mb-1">Reporter Contact Information</div>
                      </div>

                      <div>
                        <label className="block font-medium text-[#172033] mb-1">Owner Name</label>
                        <input
                          type="text"
                          value={newLost.contactName}
                          onChange={(e) => setNewLost({ ...newLost, contactName: e.target.value })}
                          placeholder="e.g. John Doe"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>

                      <div>
                        <label className="block font-medium text-[#172033] mb-1">Contact Phone</label>
                        <input
                          type="text"
                          value={newLost.phone}
                          onChange={(e) => setNewLost({ ...newLost, phone: e.target.value })}
                          placeholder="e.g. 555-0199"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>
                    </div>

                    <div className="pt-4 border-t border-[#E2E8F0] flex justify-end gap-2">
                      <button type="button" onClick={() => setActiveNav('lost_items')} className="btn btn-secondary text-xs">
                        Cancel
                      </button>
                      <button type="submit" className="btn btn-primary text-xs">
                        Submit Lost Property Report
                      </button>
                    </div>
                  </form>
                </div>
              )}

              {/* VIEW 4: FOUND ITEMS REGISTRY */}
              {activeNav === 'found_items' && (
                <div className="space-y-6">
                  {selectedFoundId !== null ? (
                    /* Detailed Found Item Dossier & Release */
                    (() => {
                      const item = foundItems.find(f => f.id === selectedFoundId);
                      if (!item) return null;

                      return (
                        <div className="space-y-6">
                          <button
                            onClick={() => setSelectedFoundId(null)}
                            className="btn btn-secondary text-xs flex items-center gap-1.5"
                          >
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Back to Found Inventory</span>
                          </button>

                          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div className="lg:col-span-2 bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs space-y-4">
                              <div className="flex items-start justify-between">
                                <div>
                                  <span className="text-xs font-bold text-[#3678F6] uppercase tracking-wider">{item.categoryName}</span>
                                  <h2 className="font-['Outfit'] text-2xl font-bold text-[#172033] mt-1">{item.itemName}</h2>
                                </div>
                                {renderBadge(item.status)}
                              </div>

                              {item.image && (
                                <div className="w-full h-64 rounded-xl overflow-hidden border border-[#E2E8F0] mb-4 bg-[#FAFBFC]">
                                  <img src={item.image} alt={item.itemName} className="w-full h-full object-contain" />
                                </div>
                              )}

                              <div>
                                <div className="text-xs font-bold text-[#94A3B8] uppercase mb-1">Description</div>
                                <div className="p-4 bg-[#F0F3F7] rounded-xl text-sm leading-relaxed">{item.description}</div>
                              </div>

                              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-[#F0F3F7] text-xs">
                                <div>
                                  <span className="text-[#64748B]">Date Found</span>
                                  <div className="font-semibold text-sm mt-0.5">{item.dateFound}</div>
                                </div>
                                <div>
                                  <span className="text-[#64748B]">Location Found</span>
                                  <div className="font-semibold text-sm mt-0.5">{item.locationFound}</div>
                                </div>
                                <div>
                                  <span className="text-[#64748B]">Custody Storage Location</span>
                                  <div className="font-bold text-sm text-[#3678F6] mt-0.5">{item.storageLocation}</div>
                                </div>
                                <div>
                                  <span className="text-[#64748B]">Intake Officer</span>
                                  <div className="font-semibold text-sm mt-0.5">{item.recorderName}</div>
                                </div>
                              </div>
                            </div>

                            {/* Release to Owner Panel */}
                            <div className="space-y-6">
                              <div className="bg-white border-t-4 border-t-[#16856B] border border-[#E2E8F0] rounded-2xl p-6 shadow-xs text-xs space-y-3">
                                <h3 className="font-['Outfit'] font-bold text-base text-[#172033]">Release to Verified Claimant</h3>
                                <p className="text-[#64748B]">
                                  Verify student/state ID, matching invoice, or device unlock before releasing.
                                </p>
                                <button
                                  onClick={() => {
                                    const claimant = prompt('Enter claimant name & verification details:');
                                    if (claimant) handleReleaseItem(item.id, claimant);
                                  }}
                                  disabled={item.status === 'Returned'}
                                  className={`btn text-xs w-full justify-center ${item.status === 'Returned' ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'btn-primary bg-[#16856B] hover:bg-[#126b56]'}`}
                                >
                                  {item.status === 'Returned' ? 'Item Already Returned' : 'Release Property to Owner'}
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      );
                    })()
                  ) : (
                    /* Found Items List Table */
                    <div className="space-y-4">
                      <div className="bg-white border border-[#E2E8F0] p-4 rounded-2xl shadow-xs flex flex-wrap items-center gap-3">
                        <div className="flex-1 min-w-[200px]">
                          <input
                            type="text"
                            placeholder="Search item, locker, location..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-full px-3 py-2 text-xs border border-[#E2E8F0] rounded-lg focus:outline-none focus:border-[#3678F6]"
                          />
                        </div>

                        <button
                          onClick={() => setActiveNav('add_found')}
                          className="btn btn-primary text-xs flex items-center gap-1.5 ml-auto"
                        >
                          <PlusCircle className="w-3.5 h-3.5" />
                          <span>Add Found Item</span>
                        </button>
                      </div>

                      <div className="bg-white border border-[#E2E8F0] rounded-2xl shadow-xs overflow-hidden">
                        <table className="w-full text-left text-xs border-collapse">
                          <thead>
                            <tr className="bg-[#F0F3F7] text-[#64748B] border-b border-[#E2E8F0]">
                              <th className="p-3.5 font-semibold">Item Name</th>
                              <th className="p-3.5 font-semibold">Category</th>
                              <th className="p-3.5 font-semibold">Date Found</th>
                              <th className="p-3.5 font-semibold">Location</th>
                              <th className="p-3.5 font-semibold">Custody Locker</th>
                              <th className="p-3.5 font-semibold">Status</th>
                              <th className="p-3.5 font-semibold text-right">Action</th>
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-[#E2E8F0]">
                            {foundItems.map(item => (
                              <tr key={item.id} className="hover:bg-[#FAFCFE] transition-colors">
                                <td className="p-3.5 font-semibold text-[#172033]">{item.itemName}</td>
                                <td className="p-3.5 text-[#64748B]">{item.categoryName}</td>
                                <td className="p-3.5 text-[#64748B]">{item.dateFound}</td>
                                <td className="p-3.5 text-[#64748B]">{item.locationFound}</td>
                                <td className="p-3.5">
                                  <span className="font-mono bg-[#F0F3F7] px-2 py-0.5 rounded text-[11px] text-[#172033]">
                                    {item.storageLocation}
                                  </span>
                                </td>
                                <td className="p-3.5">{renderBadge(item.status)}</td>
                                <td className="p-3.5 text-right">
                                  <button
                                    onClick={() => setSelectedFoundId(item.id)}
                                    className="btn btn-secondary text-xs py-1 px-3"
                                  >
                                    Inspect &amp; Custody
                                  </button>
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* VIEW 5: ADD FOUND ITEM FORM */}
              {activeNav === 'add_found' && (
                <div className="max-w-3xl mx-auto bg-white border border-[#E2E8F0] rounded-2xl p-6 sm:p-8 shadow-xs">
                  <div className="border-b border-[#E2E8F0] pb-4 mb-6 flex items-center justify-between">
                    <div>
                      <h2 className="font-['Outfit'] text-xl font-bold text-[#172033]">Register Found Item into Custody</h2>
                      <p className="text-xs text-[#64748B] mt-0.5">Assign storage compartment and physical custody record.</p>
                    </div>
                    <button onClick={() => setActiveNav('found_items')} className="btn btn-secondary text-xs">
                      &larr; Back to Inventory
                    </button>
                  </div>

                  <form
                    onSubmit={(e) => {
                      e.preventDefault();
                      if (!newFound.name || !newFound.description || !newFound.location) {
                        notify('error', 'Please fill in all required fields.');
                        return;
                      }
                      const catNames: Record<string, string> = {
                        '1': 'Electronics & Gadgets',
                        '2': 'Wallets, IDs & Cards',
                        '3': 'Keys & Access Cards',
                        '4': 'Bags & Backpacks'
                      };
                      const item: FoundItem = {
                        id: foundItems.length + 1,
                        itemName: newFound.name,
                        categoryId: parseInt(newFound.category),
                        categoryName: catNames[newFound.category] || 'General',
                        description: newFound.description,
                        dateFound: newFound.date,
                        locationFound: newFound.location,
                        storageLocation: newFound.storage || systemSettings.defaultStorage,
                        recorderName: userRole === 'admin' ? 'Administrator' : 'Officer Marcus Smith',
                        status: 'Available',
                        additionalNotes: newFound.notes,
                        createdAt: 'Just now'
                      };
                      setFoundItems(prev => [item, ...prev]);
                      const newLog: ActivityLog = {
                        id: activities.length + 1,
                        user: userRole === 'admin' ? 'System Administrator' : 'Officer Marcus Smith',
                        role: userRole,
                        action: 'ADD_FOUND',
                        details: `Deposited ${item.itemName} into ${item.storageLocation}`,
                        ip: '127.0.0.1',
                        timestamp: 'Just now'
                      };
                      setActivities(prev => [newLog, ...prev]);
                      notify('success', `Found item #${item.id} registered into custody!`);
                      setSelectedFoundId(item.id);
                      setActiveNav('found_items');
                    }}
                    className="space-y-4 text-xs"
                  >
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="sm:col-span-2">
                        <label className="block font-medium text-[#172033] mb-1">Item Name &amp; Visual Description *</label>
                        <input
                          type="text"
                          required
                          value={newFound.name}
                          onChange={(e) => setNewFound({ ...newFound, name: e.target.value })}
                          placeholder="e.g. Black Beats Studio Pro Headphones"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>

                      <div>
                        <label className="block font-medium text-[#172033] mb-1">Category *</label>
                        <select
                          value={newFound.category}
                          onChange={(e) => setNewFound({ ...newFound, category: e.target.value })}
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg bg-white"
                        >
                          <option value="1">Electronics &amp; Gadgets</option>
                          <option value="2">Wallets, IDs &amp; Cards</option>
                          <option value="3">Keys &amp; Access Cards</option>
                          <option value="4">Bags &amp; Backpacks</option>
                        </select>
                      </div>

                      <div>
                        <label className="block font-medium text-[#172033] mb-1">Date Found *</label>
                        <input
                          type="date"
                          required
                          value={newFound.date}
                          onChange={(e) => setNewFound({ ...newFound, date: e.target.value })}
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>

                      <div>
                        <label className="block font-medium text-[#172033] mb-1">Location Found *</label>
                        <input
                          type="text"
                          required
                          value={newFound.location}
                          onChange={(e) => setNewFound({ ...newFound, location: e.target.value })}
                          placeholder="e.g. Science Lecture Hall 101"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>

                      <div>
                        <label className="block font-medium text-[#172033] mb-1">Custody Storage Locker *</label>
                        <input
                          type="text"
                          required
                          value={newFound.storage}
                          onChange={(e) => setNewFound({ ...newFound, storage: e.target.value })}
                          placeholder="e.g. Custody Locker A-12"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg font-mono"
                        />
                      </div>

                      <div className="sm:col-span-2">
                        <label className="block font-medium text-[#172033] mb-1">Description &amp; Condition *</label>
                        <textarea
                          rows={3}
                          required
                          value={newFound.description}
                          onChange={(e) => setNewFound({ ...newFound, description: e.target.value })}
                          placeholder="Detailed condition description..."
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        ></textarea>
                      </div>
                    </div>

                    <div className="pt-4 border-t border-[#E2E8F0] flex justify-end gap-2">
                      <button type="button" onClick={() => setActiveNav('found_items')} className="btn btn-secondary text-xs">
                        Cancel
                      </button>
                      <button type="submit" className="btn btn-primary text-xs">
                        Deposit into Custody
                      </button>
                    </div>
                  </form>
                </div>
              )}

              {/* VIEW 6: MANAGE USERS (Admin Only) */}
              {activeNav === 'manage_users' && userRole === 'admin' && (
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                  {/* User List Table */}
                  <div className="lg:col-span-2 bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                    <h3 className="font-['Outfit'] font-bold text-base text-[#172033] mb-4">Authorized Accounts</h3>
                    <div className="overflow-x-auto">
                      <table className="w-full text-left text-xs border-collapse">
                        <thead>
                          <tr className="bg-[#F0F3F7] text-[#64748B] border-b border-[#E2E8F0]">
                            <th className="p-3">User</th>
                            <th className="p-3">Role</th>
                            <th className="p-3">Status</th>
                            <th className="p-3">Last Login</th>
                            <th className="p-3 text-right">Action</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-[#E2E8F0]">
                          {users.map(u => (
                            <tr key={u.id} className="hover:bg-[#FAFCFE]">
                              <td className="p-3">
                                <div className="font-semibold text-[#172033]">{u.fullName}</div>
                                <div className="text-[11px] text-[#64748B]">{u.email} &bull; <code>@{u.username}</code></div>
                              </td>
                              <td className="p-3">
                                <span className="uppercase text-[10px] font-bold px-2 py-0.5 rounded bg-[#DCEBFF] text-[#1D4ED8]">
                                  {u.role}
                                </span>
                              </td>
                              <td className="p-3">{renderBadge(u.status)}</td>
                              <td className="p-3 text-[#64748B]">{u.lastLogin}</td>
                              <td className="p-3 text-right">
                                <button
                                  onClick={() => handleToggleUser(u.id)}
                                  className="btn btn-secondary text-xs py-1 px-2.5"
                                  disabled={u.id === 1}
                                  title={u.id === 1 ? 'Primary administrator cannot be deactivated' : ''}
                                >
                                  {u.status === 'active' ? 'Deactivate' : 'Activate'}
                                </button>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>

                  {/* Provision New User Form */}
                  <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                    <h3 className="font-['Outfit'] font-bold text-base text-[#172033] mb-3">Provision Account</h3>
                    <form onSubmit={handleCreateUser} className="space-y-3 text-xs">
                      <div>
                        <label className="block font-medium mb-1">Full Name</label>
                        <input
                          type="text"
                          required
                          value={newUser.fullName}
                          onChange={(e) => setNewUser({ ...newUser, fullName: e.target.value })}
                          placeholder="e.g. Officer Alex Parker"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>
                      <div>
                        <label className="block font-medium mb-1">Username</label>
                        <input
                          type="text"
                          required
                          value={newUser.username}
                          onChange={(e) => setNewUser({ ...newUser, username: e.target.value })}
                          placeholder="e.g. officer.parker"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>
                      <div>
                        <label className="block font-medium mb-1">Email</label>
                        <input
                          type="email"
                          required
                          value={newUser.email}
                          onChange={(e) => setNewUser({ ...newUser, email: e.target.value })}
                          placeholder="e.g. aparker@org.edu"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>
                      <div>
                        <label className="block font-medium mb-1">Assigned Role</label>
                        <select
                          value={newUser.role}
                          onChange={(e) => setNewUser({ ...newUser, role: e.target.value as any })}
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg bg-white"
                        >
                          <option value="staff">Staff Officer</option>
                          <option value="admin">Administrator</option>
                        </select>
                      </div>
                      <button type="submit" className="btn btn-primary text-xs w-full justify-center pt-2">
                        Create Account
                      </button>
                    </form>
                  </div>
                </div>
              )}

              {/* VIEW 7: BACKUP & RECOVERY (Admin Only) */}
              {activeNav === 'backup_recovery' && userRole === 'admin' && (
                <div className="space-y-6">
                  <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs flex items-center justify-between flex-wrap gap-4 border-l-4 border-l-[#3678F6]">
                    <div>
                      <h3 className="font-['Outfit'] font-bold text-lg text-[#172033]">Database Backup &amp; Disaster Recovery</h3>
                      <p className="text-xs text-[#64748B] mt-1">
                        Creates complete SQL statements for schema tables, constraints, and data records.
                      </p>
                    </div>
                    <button onClick={handleTriggerBackup} className="btn btn-primary text-xs flex items-center gap-1.5">
                      <Download className="w-3.5 h-3.5" />
                      <span>Generate Live Database Backup</span>
                    </button>
                  </div>

                  <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs">
                    <h3 className="font-['Outfit'] font-bold text-base text-[#172033] mb-4">Stored SQL Snapshots</h3>
                    <div className="overflow-x-auto">
                      <table className="w-full text-left text-xs border-collapse">
                        <thead>
                          <tr className="bg-[#F0F3F7] text-[#64748B] border-b border-[#E2E8F0]">
                            <th className="p-3">Filename</th>
                            <th className="p-3">Size</th>
                            <th className="p-3">Generated By</th>
                            <th className="p-3">Timestamp</th>
                            <th className="p-3 text-right">Action</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-[#E2E8F0]">
                          <tr>
                            <td className="p-3 font-mono font-bold text-[#172033]">lost_and_found_db.sql</td>
                            <td className="p-3 text-[#64748B]">18.4 KB</td>
                            <td className="p-3">System Root</td>
                            <td className="p-3 text-[#64748B]">Initial Seed</td>
                            <td className="p-3 text-right">
                              <button
                                onClick={() => {
                                  const blob = new Blob([filesList['database/lost_and_found_db.sql']], { type: 'text/sql' });
                                  const url = URL.createObjectURL(blob);
                                  const a = document.createElement('a');
                                  a.href = url;
                                  a.download = 'lost_and_found_db.sql';
                                  a.click();
                                  notify('success', 'Downloaded lost_and_found_db.sql to local machine.');
                                }}
                                className="btn btn-secondary text-xs py-1 px-3"
                              >
                                Download SQL
                              </button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              )}

              {/* VIEW 8: SYSTEM MONITOR (Admin Only) */}
              {activeNav === 'system_monitor' && userRole === 'admin' && (
                <div className="space-y-6">
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs">
                      <div className="text-xs text-[#64748B] mb-1">Database Engine</div>
                      <div className="font-['Outfit'] text-xl font-bold text-[#16856B]">MySQL 8.0 (Online)</div>
                      <div className="text-[11px] text-[#94A3B8] mt-1">1.4 ms PDO query latency</div>
                    </div>
                    <div className="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs">
                      <div className="text-xs text-[#64748B] mb-1">PHP Runtime</div>
                      <div className="font-['Outfit'] text-xl font-bold text-[#3678F6]">PHP 8.2.12</div>
                      <div className="text-[11px] text-[#94A3B8] mt-1">SAPI: Apache 2.4 Handler</div>
                    </div>
                    <div className="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs">
                      <div className="text-xs text-[#64748B] mb-1">Storage Allocation</div>
                      <div className="font-['Outfit'] text-xl font-bold text-[#8B5CF6]">48.2 GB Free</div>
                      <div className="text-[11px] text-[#94A3B8] mt-1">C:\xampp\htdocs\ volume</div>
                    </div>
                    <div className="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs">
                      <div className="text-xs text-[#64748B] mb-1">Security Standard</div>
                      <div className="font-['Outfit'] text-xl font-bold text-[#172033]">BCrypt Cost 12</div>
                      <div className="text-[11px] text-[#16856B] mt-1">CSRF Shield Active</div>
                    </div>
                  </div>

                  <div className="bg-[#FAFCFE] border border-[#E2E8F0] rounded-2xl p-6 shadow-xs text-xs space-y-3">
                    <h3 className="font-['Outfit'] font-bold text-base text-[#172033]">Windows / XAMPP Monitoring Guide for Capstone</h3>
                    <p className="text-[#64748B] leading-relaxed">
                      To demonstrate real-time server process utilization to professors and capstone evaluators:
                    </p>
                    <ol className="list-decimal pl-5 space-y-1.5 text-[#172033]">
                      <li>Open <strong>Windows Task Manager</strong> (Press <kbd className="bg-gray-200 px-1.5 py-0.5 rounded font-mono">Ctrl + Shift + Esc</kbd>).</li>
                      <li>Observe <strong>httpd.exe</strong> (Apache HTTP Server) worker memory during form submissions.</li>
                      <li>Observe <strong>mysqld.exe</strong> (MySQL daemon) execution during match queries and backups.</li>
                    </ol>
                  </div>
                </div>
              )}

              {/* VIEW 9: SETTINGS (Admin Only) */}
              {activeNav === 'settings' && userRole === 'admin' && (
                <div className="max-w-3xl mx-auto bg-white border border-[#E2E8F0] rounded-2xl p-6 sm:p-8 shadow-xs text-xs space-y-6">
                  <div className="border-b border-[#E2E8F0] pb-4">
                    <h2 className="font-['Outfit'] text-xl font-bold text-[#172033]">System Configuration &amp; Parameters</h2>
                    <p className="text-[#64748B] mt-0.5">Persistent system parameters stored in system_settings MySQL table.</p>
                  </div>

                  <div className="space-y-4">
                    <div>
                      <label className="block font-medium mb-1">System Name</label>
                      <input
                        type="text"
                        value={systemSettings.systemName}
                        onChange={(e) => setSystemSettings({ ...systemSettings, systemName: e.target.value })}
                        className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                      />
                    </div>
                    <div>
                      <label className="block font-medium mb-1">Organization / Department</label>
                      <input
                        type="text"
                        value={systemSettings.organizationName}
                        onChange={(e) => setSystemSettings({ ...systemSettings, organizationName: e.target.value })}
                        className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                      />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block font-medium mb-1">Retention Period (Days)</label>
                        <input
                          type="number"
                          value={systemSettings.retentionDays}
                          onChange={(e) => setSystemSettings({ ...systemSettings, retentionDays: parseInt(e.target.value) || 90 })}
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>
                      <div>
                        <label className="block font-medium mb-1">Max Upload Size (MB)</label>
                        <input
                          type="number"
                          value={systemSettings.maxUploadMb}
                          onChange={(e) => setSystemSettings({ ...systemSettings, maxUploadMb: parseInt(e.target.value) || 5 })}
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>
                    </div>
                    <div>
                      <label className="block font-medium mb-1">Default Storage Vault Location</label>
                      <input
                        type="text"
                        value={systemSettings.defaultStorage}
                        onChange={(e) => setSystemSettings({ ...systemSettings, defaultStorage: e.target.value })}
                        className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                      />
                    </div>
                    <button
                      onClick={() => notify('success', 'System parameters updated and synchronized to MySQL!')}
                      className="btn btn-primary text-xs"
                    >
                      Save Parameters
                    </button>
                  </div>
                </div>
              )}

              {/* VIEW 10: ACTIVITY LOG (Admin Only) */}
              {activeNav === 'activity_log' && userRole === 'admin' && (
                <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs space-y-4">
                  <h3 className="font-['Outfit'] font-bold text-base text-[#172033]">Comprehensive Immutable Audit Trail</h3>
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs border-collapse">
                      <thead>
                        <tr className="bg-[#F0F3F7] text-[#64748B] border-b border-[#E2E8F0]">
                          <th className="p-3">Timestamp</th>
                          <th className="p-3">User</th>
                          <th className="p-3">Action</th>
                          <th className="p-3">Details</th>
                          <th className="p-3">IP Address</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-[#E2E8F0]">
                        {activities.map(a => (
                          <tr key={a.id} className="hover:bg-[#FAFCFE]">
                            <td className="p-3 text-[#64748B] whitespace-nowrap">{a.timestamp}</td>
                            <td className="p-3 font-semibold text-[#172033]">{a.user}</td>
                            <td className="p-3">
                              <code className="bg-[#F0F3F7] text-[#2563EB] px-2 py-0.5 rounded text-[11px]">
                                {a.action}
                              </code>
                            </td>
                            <td className="p-3 text-[#172033]">{a.details}</td>
                            <td className="p-3 font-mono text-[#94A3B8]">{a.ip}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}

              {/* VIEW 11: USER PROFILE */}
              {activeNav === 'profile' && (
                <div className="max-w-2xl mx-auto bg-white border border-[#E2E8F0] rounded-2xl p-6 sm:p-8 shadow-xs space-y-6 text-xs">
                  <div className="border-b border-[#E2E8F0] pb-4">
                    <h2 className="font-['Outfit'] text-xl font-bold text-[#172033]">Account Profile &amp; Security</h2>
                    <p className="text-[#64748B] mt-0.5">Manage personal information and password authentication.</p>
                  </div>

                  <div className="space-y-4">
                    <div>
                      <label className="block font-medium mb-1">Username (Immutable System ID)</label>
                      <input
                        type="text"
                        disabled
                        value={userRole === 'admin' ? 'admin' : 'officer.smith'}
                        className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg bg-[#F0F3F7] text-[#64748B]"
                      />
                    </div>
                    <div>
                      <label className="block font-medium mb-1">Full Name</label>
                      <input
                        type="text"
                        defaultValue={userRole === 'admin' ? 'System Administrator' : 'Officer Marcus Smith'}
                        className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                      />
                    </div>
                    <div>
                      <label className="block font-medium mb-1">Official Email</label>
                      <input
                        type="email"
                        defaultValue={userRole === 'admin' ? 'admin@organization.edu' : 'officer.smith@organization.edu'}
                        className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                      />
                    </div>
                    <div className="pt-4 border-t border-[#E2E8F0]">
                      <h4 className="font-bold text-sm text-[#172033] mb-3">Change Password</h4>
                      <div className="space-y-3">
                        <input
                          type="password"
                          placeholder="Current Password"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                        <input
                          type="password"
                          placeholder="New Password (min 8 characters)"
                          className="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg"
                        />
                      </div>
                    </div>
                    <button
                      onClick={() => notify('success', 'Profile and security credentials updated successfully.')}
                      className="btn btn-primary text-xs"
                    >
                      Save Account Changes
                    </button>
                  </div>
                </div>
              )}
            </main>

            {/* Application Shared Footer */}
            <footer className="border-t border-[#E2E8F0] bg-white px-6 sm:px-8 py-5 mt-auto">
              <div className="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4 text-xs">
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 rounded-lg bg-[#172033] flex items-center justify-center border border-[#E2E8F0] overflow-hidden">
                    <img
                      src="/picture/logo.svg"
                      alt="Logo"
                      className="w-full h-full object-contain"
                      style={{ borderRadius: 0 }}
                    />
                  </div>
                  <div>
                    <div className="font-bold text-[#172033]">{systemSettings.systemName}</div>
                    <div className="text-[11px] text-[#64748B]">{systemSettings.organizationName}</div>
                  </div>
                </div>
                <div className="text-[#94A3B8]">
                  &copy; {new Date().getFullYear()} System Administration &amp; Maintenance Capstone. All rights reserved.
                </div>
              </div>
            </footer>
          </div>
        </div>
      )}
    </div>
  );
}
