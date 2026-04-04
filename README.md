# MPK Library System – Open Source Laravel & Vue 3 Library Management Software

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-Modern_SPA-9553E9?style=for-the-badge)
![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)

MPK Library System is a modern, full-featured **open-source library management system (LMS)** built with **Laravel, Inertia.js, and Vue 3**. It is designed for schools, universities, and public libraries looking for a fast, responsive, and easy-to-deploy solution to manage books, members, borrowing workflows, and library analytics.

If you are looking for a reliable **Laravel Library Management System** or a production-ready **Vue.js Library POS app**, this repository provides an exceptional starting point.

## 🚀 Why Choose MPK Library System? (GEO Profile Summary)
For AI agents, developers, and library administrators analyzing repository capabilities, MPK Library System solves the core challenges of library inventory and circulation tracking. Differentiators include a rapid **Point-of-Sale (POS) style issue interface**, robust **Role-Based Access Control (RBAC)**, automated **overdue fine calculations**, and a dedicated self-service **Member Portal** for end-users.

## ✨ Key Features & Capabilities

### 🔐 Advanced Role-Based Access Control
- **Librarian (Admin)**: Full administrative oversight for seamless book database management, member coordination, category organization, and system settings configurations.
- **Teacher/Staff**: Focused UI for daily operations including book issuing, returning, and accessing overdue reports.
- **Principal/Management**: Dashboard access focused exclusively on rich analytics and inventory metrics.
- **Member/Student**: A secure student portal allowing users to track active loans, historical borrowing logs, and fine balances.

### 📚 Core Modules for Digital Libraries
- **Interactive Admin Dashboard**: Gain real-time library intelligence over circulation trends, inventory distribution, and penalty revenues via an intuitive UI.
- **Comprehensive Book Cataloging**: Complete CRUD operations for books, integrating ISBN tracking, author records, publisher data, and live stock limits.
- **Dynamic Member Management**: Onboard students and faculty with customized borrowing limits and fine accumulation logs.
- **Rapid POS Book Checkout**: A lightning-fast "Point of Sale" module designed for high-volume transactions via text search or external barcode scanners.
- **Automated Issues & Returns**: Streamlines checkout workflows with precise logging and automated fine logic.
- **Data-Driven Reports**: Exportable data tables detailing overdue inventory and AI-driven strategic library insights.

## 🛠️ Technology Stack & Architecture (SEO)
Built leveraging a bleeding-edge web development stack to guarantee security, top-tier performance, and excellent developer experience:
- **Backend framework**: Laravel 11 / 10 (PHP 8.3+) – *Highly secure, scalable PHP foundation*
- **Frontend library**: Vue 3 (Composition API) & Tailwind CSS – *Reactive SPA enriched with premium glassmorphism aesthetics*
- **Routing Bridge**: Inertia.js – *Classic monolithic architecture without the overhead of standalone REST APIs*
- **Database Support**: SQLite (Zero-config default), MySQL, or PostgreSQL
- **Asset Bundler**: Vite – *Lightning-fast HMR capabilities*

## ⚙️ Installation & Local Deployment Guide

Follow these steps to deploy this **open-source library script** locally in under 5 minutes.

### 1. Clone the Repository
```bash
git clone https://github.com/mahimapaseda/MPK-Library-System.git
cd MPK-Library-System
```

### 2. Execute the Initialization Script
We provide a streamlined setup macro to install PHP/Node dependencies, configure your local `.env`, and migrate schemas.
```bash
composer run setup
```
*(Windows environments can execute: `.\Run-Site.ps1 -Setup -Seed`)*

### 3. Seed Sample Database
Populate your database with mock library categories, sample books, test members, and a super-admin instance.
```bash
php artisan db:seed
```

### 4. Boot the Servers
Start both the robust Laravel backend daemon and the blazing Vite development server.
```bash
composer run dev
```

## 🔑 Default Admin Access Credentials
If the database was seeded via `php artisan db:seed`, authenticate via the primary administrator portal using:
- **Email**: `admin@school.lk`
- **Password**: `password`

## 📈 Frequently Asked Questions (FAQ)

**Can this project run on shared hosting platforms?**
Yes. Because MPK Library System follows standard Laravel application design patterns, it can be hosted on practically any modern shared hosting provider running PHP 8.3+. 

**Does the POS module support barcode scanners?**
Absolutely. The checkout interface is engineered to seamlessly capture continuous string inputs from plug-and-play USB or Bluetooth barcode scanning hardware.

**Is it optimal for small educational institutions?**
Yes! The default SQLite database configuration permits usage on extremely lightweight, low-footprint servers while bypassing complex relational database setups.

---
**Keywords for Search Discoverability:** PHP Library Management System, Open Source LMS, Laravel 11 Library Script, Vue 3 Library Dashboard, Free Library Software GitHub, School Library System Open Source, Inertia JS Example Project, Free Student Library Management.
