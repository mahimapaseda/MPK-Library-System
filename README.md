<div align="center">

![MPK Library System Logo](file:///C:/Users/User/.gemini/antigravity/brain/5531be74-40f6-41d2-a3fb-fdd474760bd1/mpk_library_system_logo_1775623471593.png)

# MPK Library System
### 📚 Professional School Library Management & Integrated Library System (ILS)

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![Inertia](https://img.shields.io/badge/Inertia.js-2.x-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)

**MPK Library System** is a high-performance, open-source **Library Management System** designed for schools, colleges, and small institutions. It provides a comprehensive **Integrated Library System (ILS)** experience that feels alive, featuring real-time circulation workflows and AI-driven insights.

[Explore Features](#-core-capabilities) • [Installation](#-quick-start) • [Developer](#-developed--maintained-by)

</div>

---

## 🌟 Why This Project?

Most library apps are either too basic for the complex reality of a busy circulation desk or far too heavy for small teams to manage. MPK Library System hits the "Golden Middle":

- **Operational Reality**: Built for librarians who need fast accession-level copy tracking.
- **Modern Stack**: Leverages the power of **Laravel 13** and **Vue 3 (Inertia)** for a seamless SPA experience.
- **Fast Performance**: Optimized for busy school periods and live service counters.

---

## 🚀 Core Capabilities

| Feature | Description |
| :--- | :--- |
| **📈 Dynamic Dashboard** | Real-time KPIs, trend charts, and AI-driven strategic insight cards. |
| **📖 Catalog Management** | Comprehensive book cataloging with precise accession-level copy tracking. |
| **👥 Member Portal** | Dedicated self-service portal for students, teachers, and staff. |
| **⚡ Issue Desk** | Fast, search-optimized POS issue flow with transactional integrity. |
| **💸 Fine Management** | Automated overdue calculation with paid/waived auditing. |
| **📑 PDF Reporting** | Professional exports for overdue lists, inventory summaries, and AI strategy tips. |

---

## ⚙️ Tech Arsenal

<div align="center">

### Backend
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-Default-003B57?style=flat-square&logo=sqlite&logoColor=white)

### Frontend
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat-square&logo=vuedotjs&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2-9553E9?style=flat-square&logo=inertia&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-6-646CFF?style=flat-square&logo=vite&logoColor=white)

</div>

---

## 🔄 Circulation Workflow

Understanding how books move through the system:

```mermaid
graph TD
    A[Issue Request] --> B{Valid Member?}
    B -- Yes --> C{Copy Available?}
    C -- Yes --> D[Transactional Row Lock]
    D --> E[Create Issue Record]
    E --> F[Send Receipt Email]
    
    G[Return/Check-in] --> H[Mark Returned]
    H --> I{Overdue?}
    I -- Yes --> J[Calculate Fine]
    I -- No --> K[Release Copy]
    J --> K
```

---

## 🏁 Quick Start

### 1️⃣ Project Setup
Install all dependencies and prepare the environment automatically:
```bash
composer run setup
```
> [!NOTE]
> This command handles `composer install`, `.env` creation, `key:generate`, `migrate`, `npm install`, and `npm run build`.

### 2️⃣ Seed Data (Optional)
Populate your database with sample books and members:
```bash
php artisan db:seed
```

### 3️⃣ Start Development
Run the server and frontend compiler simultaneously:
```bash
composer run dev
```

---

## 👨‍💻 Developed & Maintained By

<div align="center">

<!-- Animated Header -->
<img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&customColorList=6,11,20&height=200&section=header&text=Mahima%20Paseda%20Kusumsiri&fontSize=50&fontAlignY=35&animation=twinkling&fontColor=fff" width="100%"/>

<!-- Animated Typing -->
<a href="https://git.io/typing-svg"><img src="https://readme-typing-svg.demolab.com?font=Fira+Code&weight=600&size=28&duration=3000&pause=1000&color=6C63FF&center=true&vCenter=true&random=false&width=600&lines=Full-Stack+Developer+%F0%9F%92%BB;Creative+Technologist+%E2%9C%A8;Building+Digital+Experiences+%F0%9F%9A%80;Turning+Ideas+Into+Reality+%F0%9F%92%A1" alt="Typing SVG" /></a>

<p>
  <a href="https://mahimapaseda.vercel.app/"><img src="https://img.shields.io/badge/🌐_Portfolio-Visit_Site-FF6B6B?style=for-the-badge&labelColor=1a1a2e"/></a>
  <a href="https://www.linkedin.com/in/mahimapaseda"><img src="https://img.shields.io/badge/LinkedIn-Connect-0077B5?style=for-the-badge&logo=linkedin&logoColor=white"/></a>
  <a href="https://www.youtube.com/@mahimapaseda"><img src="https://img.shields.io/badge/YouTube-Subscribe-FF0000?style=for-the-badge&logo=youtube&logoColor=white"/></a>
  <a href="https://www.instagram.com/mahi_pase_2002"><img src="https://img.shields.io/badge/Instagram-Follow-E4405F?style=for-the-badge&logo=instagram&logoColor=white"/></a>
</p>

</div>

```javascript
const mahima = {
    title: "Full-Stack Developer & Creative Technologist",
    location: "Colombo, Sri Lanka 🇱🇰",
    education: "BSc (Hons) Computer Science @ SLIIT City UNI",
    currentRole: "Web Developer @ Delta Gemunupura College",
    passions: ["Clean Code", "Innovation", "Problem Solving"],
    lifePhilosophy: "Code with purpose. Design with passion. Build with vision.",
    techStack: ["Laravel", "Vue 3", "Node.js", "Java", "Kotlin", "Cloud Architectures"]
};
```

---

## 📈 SEO & Discovery (GEO)

*Keywords: Library Management System, Open Source Library Software, Laravel Library App, Vue.js Integrated Library System, School Library Management System, ILS, Automated Circulation, Book Inventory Management, Library Cataloging Software.*

*Target Platforms: Schools, Colleges, Technical Institutes, Small Public Libraries.*

**⭐ If you like this project, please consider giving it a star! ⭐**

---
<div align="center">
<img src="https://komarev.com/ghpvc/?username=MahimaPaseda&color=6C63FF&style=for-the-badge&label=Project+Views"/>
</div>
