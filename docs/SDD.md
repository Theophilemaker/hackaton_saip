---
title: "SAIP — Enterprise System Design Document"
subtitle: "Smart Agriculture Intelligence Platform"
author: "University of Rwanda · Business Information Technology"
date: "2026"
version: "1.0"
---

<div style="page-break-after: always;"></div>

# Cover Page

## Smart Agriculture Intelligence Platform (SAIP)

### Enterprise System Design Document (SDD)

**Version:** 1.0  
**Prepared by:** University of Rwanda — Business Information Technology  
**Target:** NISR Big Data Hackathon 2026  
**Document Type:** System Design Specification (IEEE-style)

---

<div style="page-break-after: always;"></div>

# Table of Contents

1. System Overview
2. High-Level Architecture
3. Component Architecture
4. UML Diagrams
5. Database Design
6. User Interface Design
7. AI Architecture
8. Security Design
9. Communication Architecture
10. GIS Architecture
11. Data Pipeline
12. Deployment Architecture
13. Folder Structure
14. Design Innovations

---

<div style="page-break-after: always;"></div>

# Chapter 1: System Overview

## 1.1 Purpose

SAIP is a web-based intelligent agriculture ecosystem that transforms Rwanda's agricultural statistics into practical tools for farmers, cooperatives, government institutions, exporters, and researchers.

## 1.2 System Combines

- Big Data Analytics
- Artificial Intelligence
- GIS Mapping
- Multilingual Support
- Social Networking
- Marketplace
- Government Analytics

## 1.3 Core Modules

| # | Module | Purpose |
|---|--------|---------|
| 1 | Dashboard | Personalized analytics |
| 2 | Weather AI | Forecast + advice |
| 3 | Crop Intelligence | Tracking + prediction |
| 4 | Farmer Connect | Chat + social |
| 5 | Marketplace | Trading |
| 6 | GIS Analytics | Interactive maps |

---

# Chapter 2: High-Level Architecture

The platform follows a **4-layer enterprise architecture**.

## 2.1 Presentation Layer

**Technology:** HTML5, CSS3, Vanilla ES6 JavaScript

**Responsibilities:**
- Responsive interface
- Theme customization (5 themes + custom)
- Language switching (4 languages)
- PWA support
- Glassmorphism UI

## 2.2 Application Layer

**Technology:** PHP 8+ (Custom MVC)

**Responsibilities:**
- Authentication (2FA, session, rate limiting)
- Business logic
- Chat + voice notes
- Marketplace transactions
- Role-based access control

## 2.3 AI & Analytics Layer

**Technology:** Rule-based engines + Python-ready

**Responsibilities:**
- Crop yield prediction
- Weather advice generation
- Disease detection (photo)
- Pest detection (photo)
- Auto-translation
- AgriBot chat assistant

## 2.4 Data Layer

**Technology:** MySQL 8+ (InnoDB, utf8mb4)

**Contains:**
- 122 interconnected tables
- NISR datasets (imported)
- External APIs (weather, translation)
- File storage (uploads/)

## 2.5 Architecture Diagram
<img width="1536" height="1024" alt="picture design" src="https://github.com/user-attachments/assets/eaab8cc3-4926-479a-a255-556b6a2ca913" />

