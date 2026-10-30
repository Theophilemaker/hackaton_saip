# 🌱 SAIP — Smart Agriculture Intelligence Platform

[![NISR Hackathon 2026](https://img.shields.io/badge/NISR-Big%20Data%20Hackathon%202026-green.svg)](#)
[![Track 5](https://img.shields.io/badge/Track-1%3A%20Agricultural%20Productivity-blue.svg)](#)
[![PHP Version](https://img.shields.io/badge/PHP-8.x-purple.svg)](#)
[![MySQL](https://img.shields.io/badge/Database-MySQL-orange.svg)](#)

> **"Empowering Farmers, Strengthening Agriculture, Building a Food Secure Rwanda."**

**Live Application:** [https://smartap.totalh.net](https://smartap.totalh.net)  
**Documentation:** [System Requirements (SRS)](./docs/SRS.md) | [System Design (SDD)](./docs/SDD.md)  
**youtube channel link of saip demo:[https://youtu.be/oXJgQIgDjxM](https://youtu.be/oXJgQIgDjxM)

---

## 📑 Table of Contents
1. [Executive Summary](#1-executive-summary)
2. [Problem Statement](#2-problem-statement)
3. [The SAIP Solution: 15 Modules](#3-the-saip-solution-15-modules)
4. [System Architecture & Tech Stack](#4-system-architecture--tech-stack)
5. [NISR Data Utilization & AI Methodology](#5-nisr-data-utilization--ai-methodology)
6. [Hackathon Evaluation Criteria](#6-hackathon-evaluation-criteria)
7. [Alignment with NST2 (2024-2030)](#7-alignment-with-nst2-2024-2029)
8. [Security, Privacy & Ethical AI](#8-security-privacy--ethical-ai)
9. [Deployment & Installation](#9-deployment--installation)
10. [Team & Declaration](#10-team--declaration)

---

## 1. Executive Summary

The **Smart Agriculture Intelligence Platform (SAIP)** is a multilingual, AI-powered digital ecosystem designed to transform Rwanda’s agricultural landscape. By synthesizing **NISR statistical datasets**, weather data, GIS layers, and real-time farmer-generated insights, SAIP bridges the critical gap between high-level national data and daily farming decisions.

Unlike fragmented agricultural tools, SAIP integrates **Crop Intelligence, Weather Forecasting, a Social Network, E-Commerce, and Government Analytics** into a single, unified platform. It empowers farmers with actionable localized advice, enables buyers to source efficiently, and provides government officials with real-time, evidence-based dashboards to monitor NST2 agricultural KPIs.

---

## 2. Problem Statement

Rwandan agriculture is hindered by data fragmentation, unpredictable climate patterns, and limited market access. While NISR produces vital agricultural statistics, this data is rarely translated into actionable, localized formats accessible to smallholder farmers.

**Key Challenges Addressed:**
*   **Language & Literacy Barriers:** Existing digital tools lack Kinyarwanda and Kiswahili support, excluding a significant portion of the farming population.
*   **Climate Vulnerability:** Farmers lack timely, hyper-local weather intelligence to optimize planting, irrigation, and fertilizer application.
*   **Market Inefficiencies:** Fragmented supply chains force farmers to accept suboptimal prices due to a lack of real-time price discovery and direct buyer access.
*   **Policy Blind Spots:** Government officials lack an integrated platform to visualize district-level production, climate risks, and market trends in real-time.

---

## 3. The SAIP Solution: 15 Modules

SAIP is architected around 15 interconnected modules, deployed across three distinct user layers:

### 👨‍🌾 A. Farmer & Cooperative Layer
1.  **Multilingual Dashboard:** Instant switching between Kinyarwanda, English, French, and Kiswahili. Fully customizable drag-and-drop UI.
2.  **Weather Intelligence:** AI-driven forecasts with actionable recommendations (e.g., "Delay fertilizer application by 24 hours due to expected rain").
3.  **Crop Intelligence:** Track crop health, yield history, and compare district production metrics.
4.  **AI Recommendation Engine (AgriBot):** A chatbot that provides localized advice on planting, pests, and market timing.
5.  **Pest & Disease Detection:** Users upload leaf photos; a CNN model identifies diseases (e.g., Potato Blight) and suggests treatments.
6.  **Farmer Connect:** A social network with private/group chat, voice notes, and auto-translation for cross-district collaboration.
7.  **District Forums:** Officer-verified community boards for sharing local agricultural knowledge.
8.  **Cooperative Management:** Tools for leaders to manage members, harvests, equipment, and financial contributions.
9.  **Smart Marketplace:** Direct farmer-to-buyer trading with negotiation, price discovery, and product listings.
10. **Logistics & Storage:** Monitor warehouse capacity, moisture levels, and coordinate transport.

### 🏛️ B. Government & Policy Layer (GARP)
11. **Government Analytics:** National KPIs (Production, Rainfall Coverage, Market Value) tracked against NST2 targets.
12. **GIS Analytics:** Interactive Leaflet maps displaying heatmaps for crop distribution, pest outbreaks, and irrigation coverage.
13. **Automated Reporting:** Generate PDF/Excel reports for districts, provinces, or the Ministry of Agriculture.
14. **Climate & Risk Alerts:** Early warning systems for floods, droughts, and pest outbreaks.

### 🔬 C. System & Research Layer
15. **NISR Analyst Portal:** Direct access to structured datasets, pivot tables, and statistical export tools for researchers.

---

## 4. System Architecture & Tech Stack

SAIP is built on a **4-layer enterprise MVC architecture**, ensuring high scalability, security, and maintainability.

### Technology Stack
| Layer | Technologies Used |
| :--- | :--- |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript, Chart.js, Leaflet GIS |
| **Backend** | PHP 8+ (Custom MVC Framework), PDO |
| **AI & Data** | Python 3, Scikit-learn, TensorFlow, Pandas |
| **Database** | MySQL 8.0 (InnoDB, utf8mb4) |
| **Deployment** |  InfinityFree / XAMPP (Local) |

### Database Architecture
The system utilizes a fully normalized (3NF) relational database consisting of **58 interconnected tables**, organized into 10 modules. It features:
*   **UUIDs** for secure API access.
*   **Foreign Key constraints** to prevent orphan records.
*   **Audit Triggers** for login history and data changes.
*   **Performance Indexes** on high-query columns (e.g., `users.email`, `weather.date`).
*   **Database Views** for complex reporting (e.g., `farmer_production_summary`).

---

## 5. NISR Data Utilization & AI Methodology

SAIP heavily leverages **NISR open datasets** and external APIs to drive its predictive models. Every AI prediction is accompanied by a **confidence score** and **data source attribution** to ensure transparency and trust.

### Data Sources
*   **NISR:** Agricultural surveys, district statistics, population data, household surveys.
*   **External APIs:** OpenWeather/Weather APIs, market price indices.
*   **GIS:** Rwanda district boundaries, elevation, land use.
*   **User-Generated:** Crop photos, harvest logs, marketplace transactions.

### AI Models Deployed
| Module | Model / Algorithm | Purpose |
| :--- | :--- | :--- |
| **Yield Prediction** | Random Forest / Gradient Boosting | Predict harvest yields based on historical NISR data, rainfall, and soil type. |
| **Disease Detection** | Convolutional Neural Network (CNN) | Image classification of crop diseases with 90%+ confidence. |
| **Price Forecasting** | Prophet / Time-Series Regression | Predict market prices based on seasonality, supply, and demand. |
| **Chat Translation** | NLP / Translation API | Auto-translate Farmer Connect messages between RW, EN, FR, SW. |

---

## 6. Hackathon Evaluation Criteria

| Evaluation Criterion (Weight) | How SAIP Addresses It |
| :--- | :--- |
| **Relevance to Theme (20%)** | Directly supports Track 5 by building a web-based platform that solves real-world agricultural challenges using NISR data. |
| **Data Utilization & Accuracy (25%)** | Integrates NISR datasets, weather APIs, and GIS layers. AI models are trained on historical data, and all insights show confidence scores and data sources. |
| **UI/UX (15%)** | Glassmorphism design, 5 built-in themes, fully mobile-responsive, drag-and-drop widgets, and **4-language support** (RW, EN, FR, SW). |
| **Creativity & Innovation (15%)** | Features like **AI-in-Chat**, **Auto-Translation**, **District Heatmaps**, and **Offline-First** architecture differentiate SAIP from standard dashboards. |
| **Impact & Scalability (25%)** | Designed to scale from a single farmer to national government deployment. Clear pathways to improve farmer income, reduce post-harvest losses, and support export growth. |

---

## 7. Alignment with NST2 and vision 2050

SAIP is explicitly designed to support the **National Strategy for Transformation (NST2)**:

*   **Pillar 1: Economic Transformation** 
    *   *Modernized Agriculture:* AI yield predictions and fertilizer optimization.
    *   *Digital Transformation:* A fully digital marketplace and government dashboard.
    *   *Export Promotion:* Tools for exporters to source "Grade A" produce and track logistics.
*   **Pillar 2: Social Transformation**
    *   *Digital Inclusion:* Kinyarwanda and Kiswahili interfaces ensure no farmer is left behind.
    *   *Knowledge Sharing:* Farmer Connect breaks down geographical and linguistic silos.
*   **Pillar 3: Transformational Governance**
    *   *Evidence-Based Policy:* The Government Analytics Portal provides real-time data for district and national planning.

---

## 8. Security, Privacy & Ethical AI

Because SAIP handles sensitive user data and government-adjacent analytics, enterprise-grade security is implemented:
*   **Authentication:** Passwords hashed using `bcrypt`; Role-Based Access Control (RBAC) enforced via middleware.
*   **Data Protection:** PDO prepared statements eliminate SQL injection; CSRF tokens protect all forms.
*   **Audit Logging:** Every login, data upload, and system change is logged.
*   **Ethical AI:** AI predictions are strictly **decision-support tools**. They never overwrite official NISR data. Official reports clearly distinguish between verified NISR statistics and AI-generated forecasts.

---

## 9. Deployment & Installation

The SAIP platform is fully deployed and accessible for live testing by the judges.

*   **Live Application:** [https://smartap.totalh.net](https://smartap.totalh.net)

### Local Installation Guide (For Reproducibility)
1.  Clone the repository: `git clone https://github.com/Theophilemaker/hackaton_saip.git`
2.  Move the project to your XAMPP/WAMP `htdocs` directory.
3.  Import the database: Open phpMyAdmin, create a database named `saip_enterprise`, and import the `database/saip_enterprise.sql` file.
4.  Configure environment variables: Rename  `config/config.php` and add your database credentials.
5.  Start Apache and MySQL, then navigate to `http://localhost/hackaton_saip/public`.

---
**the website credential you may use instead of creating account **
#agricultural officer,Email:sam@gmail.com,pword:sam1234567
#farmer Email:theophilen298@gmail.com pword:Test1234!
#cooperative leader Email:samson@gmail.com pword:samson1234
#exporter Email:shema@gmail.com pword:shema123
#reseacher Email: eric@gmail.com pword:eric123456
## 10. Team & Declaration

**Team Name:** Theophile NIRAGIRE and NSENGIYUMVA Daniel Project  
**Institution:** University of Rwanda students from department of Business Information Technology  
**Competition:** NISR Big Data Hackathon 2026

*We declare that this submission is our own original work, has never been submitted to any other competition or entity in the past, and that all applicable sources of reference are acknowledged in full. We grant NISR permission to use and reproduce this work with appropriate acknowledgment.*

