# ⚠️ WARNING ⚠️

## **THIS IS A DEVELOP BRANCH – NEVER INSTALL MYTHICALDASH FROM HERE**

---

# MythicalDash V4

MythicalDash is a comprehensive client area/dashboard for Pterodactyl Game Panel, Pelican, and other platforms. It provides a powerful suite of tools for managing client servers, user accounts, billing, and much more.

[View the Documentation Here](https://docs.mythical.systems/docs/mythicaldash-v3-remastered/welcome)

---

## 🚀 Overview

MythicalDash provides hosting providers with a complete management solution that integrates with popular hosting panels. The dashboard offers a modern, responsive interface with extensive customization options, security features, and monetization tools.

---

## 🛣️ Roadmap

View the full roadmap [here](https://github.com/orgs/MythicalLTD/projects/5).

---

## ✨ Features & Configuration Options

### 🖥️ Core Platform
- **Multi-Panel Support** - Integration with Pterodactyl, Pelican, and other platforms
- **Plugin System** - Extendable functionality through powerful plugin architecture
- **User Management** - Complete user account management system
- **Ticketing System** (`ALLOW_TICKETS`) - Built-in support ticket management
- **API Support** - Comprehensive API for third-party integrations
- **Localization** (`APP_LANG`) - Multi-language support
- **Customization**
  - App Name (`APP_NAME`)
  - App URL (`APP_URL`)
  - App Logo (`APP_LOGO`)
  - Timezone (`APP_TIMEZONE`)
  - SEO Description (`SEO_DESCRIPTION`)
  - SEO Keywords (`SEO_KEYWORDS`)
  - Custom CSS/JS (`CUSTOM_CSS`, `CUSTOM_JS`)

### 🔒 Security Features
- **Turnstile Integration** (`TURNSTILE_ENABLED`) - Bot protection for forms
- **Mythical Zero Trust** (`MYTHICAL_ZERO_TRUST_ENABLED`) - Advanced security framework:
  - Server scan protection (`MYTHICAL_ZERO_TRUST_SERVER_SCAN_TOOL_ENABLED`)
  - IP whitelisting (`MYTHICAL_ZERO_TRUST_WHITELIST_IPS_ENABLED`)
  - TOR network blocking (`MYTHICAL_ZERO_TRUST_BLOCK_TOR_ENABLED`)
  - Enhanced security logging (`MYTHICAL_ZERO_TRUST_ENHANCED_LOGGING_ENABLED`)

### 📧 Communication Systems
- **SMTP Integration** (`SMTP_ENABLED`) - Email notifications and communications
- **Discord Integration** (`DISCORD_ENABLED`) - Account linking and authentication
- **GitHub Integration** (`GITHUB_ENABLED`) - Account linking and authentication
- **Social Media Links** - Connect via multiple platforms:
  - Discord, Twitter, GitHub, LinkedIn, Instagram, YouTube
  - TikTok, Facebook, Reddit, Telegram, WhatsApp

### 💰 Monetization Features
- **Credits/Coins System** - Virtual currency for the platform
- **Store System** (`STORE_ENABLED`) - Purchase server resources with coins
- **AFK Rewards** (`AFK_ENABLED`) - Earn coins by staying active
- **Code Redemption** (`CODE_REDEMPTION_ENABLED`) - Redeem promotional codes
- **Join For Rewards** (`J4R_ENABLED`) - Get rewards for joining
- **Referral System** (`REFERRALS_ENABLED`) - Earn coins by referring others
- **Link For Rewards** (`L4R_ENABLED`) - Earn by completing link tasks
- **Coins Sharing** (`ALLOW_COINS_SHARING`) - Transfer coins between users
- **Server Renewal** (`SERVER_RENEW_ENABLED`) - Extend server lifespan with coins

### 🔄 Link Services Integration
- **Linkvertise** (`L4R_LINKVERTISE_ENABLED`) - Monetize with shortlinks
- **ShareUS** (`L4R_SHAREUS_ENABLED`) - File sharing service
- **LinkPays** (`L4R_LINKPAYS_ENABLED`) - Link monetization
- **GyaniLinks** (`L4R_GYANILINKS_ENABLED`) - URL shortening

### 💳 Payment Processors
- **PayPal Integration** (`ENABLE_PAYPAL`) - Process payments via PayPal
- **Stripe Integration** (`ENABLE_STRIPE`) - Secure payment processing
- **Currency Configuration** (`CURRENCY`, `CURRENCY_SYMBOL`) - Set currency and symbols

### 🧮 Resource Management
- **Server Resource Controls**:
  - RAM (`MAX_RAM`, `DEFAULT_RAM`, `BLOCK_RAM`, `STORE_RAM_PRICE`)
  - Disk (`MAX_DISK`, `DEFAULT_DISK`, `BLOCK_DISK`, `STORE_DISK_PRICE`)
  - CPU (`MAX_CPU`, `DEFAULT_CPU`, `BLOCK_CPU`, `STORE_CPU_PRICE`)
  - Ports (`MAX_PORTS`, `DEFAULT_PORTS`, `BLOCK_PORTS`, `STORE_PORTS_PRICE`)
  - Databases (`MAX_DATABASES`, `DEFAULT_DATABASES`, `BLOCK_DATABASES`, `STORE_DATABASES_PRICE`)
  - Server Slots (`MAX_SERVER_SLOTS`, `DEFAULT_SERVER_SLOTS`, `BLOCK_SERVER_SLOTS`, `STORE_SERVER_SLOT_PRICE`)
  - Backups (`MAX_BACKUPS`, `DEFAULT_BACKUPS`, `BLOCK_BACKUPS`, `STORE_BACKUPS_PRICE`)
- **Server Management** (`ALLOW_SERVERS`) - Enable/disable server creation
- **Daily Backup System** (`DAILY_BACKUP_ENABLED`) - Automated backups

### 🤝 Community Features
- **Leaderboard System** (`LEADERBOARD_ENABLED`) - Competitive user rankings
- **Public Profiles** (`ALLOW_PUBLIC_PROFILES`) - User profile visibility
- **Early Supporters Program** (`EARLY_SUPPORTERS_ENABLED`) - Recognition for early adopters

### 🧰 Developer Tools
- **Event-Driven Architecture** - Hook into system events for custom functionality
- **Caching System** - Performance optimization through caching
- **Logging System** - Comprehensive activity logging
- **Fast UI** - Real-time chat capabilities
- **Custom Hooks** - Extend core functionality at various points
- **Telemetry** (`TELEMETRY_ENABLED`) - Usage tracking and analytics

---

### 🧩 Feature Comparison Table

| Feature                                      | MythicalDash | Heliactyl | HolaClient v2-mini | NorthClient |
|----------------------------------------------|--------------|-----------|--------------------|-------------|
| Basic Dashboard Functions                    | ✅           | ✅        | ✅                 | ✅          |
| J4R (Join for Rewards)                       | ❌           | ✅        | ✅                 | ✅          |
| L4R (Linkvertise, Shareus, etc.)             | ✅           | ❌        | ✅                 | ❌          |
| AFK Page                                     | ✅           | ✅        | ✅                 | ✅          |
| Referrals                                    | ✅           | ❌        | ✅                 | ❌          |
| Code Redemption                              | ✅           | ❌        | ✅                 | ❌          |
| Coin Sharing                                 | ✅           | ❌        | ✅                 | ❌          |
| Own SDK (Plugins)                            | ✅           | ❌        | ❌                 | ❌          |
| Full WebUI Management                        | ✅           | ❌        | ❌                 | ❌          |
| Live Application (API Based)                 | ✅           | ❌        | ❌                 | ❌          |
| Zero Trust AI Security                       | ✅           | ❌        | ❌                 | ❌          |
| At-a-Glance Analytics                        | ✅           | ❌        | ❌                 | ❌          |
| Full Activity Backlog                        | ✅           | ❌        | ❌                 | ❌          |
| Backup System + Cloud                        | ✅           | ❌        | ❌                 | ❌          |
| Leaderboards                                 | ✅           | ❌        | ❌                 | ❌          |
| Advanced Ticket System                       | ✅           | ❌        | ❌                 | ❌          |
| Coin Sharing with Fees                       | ✅           | ❌        | ❌                 | ❌          |
| Public/Private Profiles                      | ✅           | ❌        | ❌                 | ❌          |
| Cloudflare Turnstile                         | ✅           | ❌        | ❌                 | ❌          |
| Firewall (AntiVPN, AntiAlting, AntiProxy)    | ✅           | ❌        | ❌                 | ❌          |
| Pelican Panel Support (Alpha)                | ✅           | ❌        | ❌                 | ❌          |
| Linking (Discord, GitHub, Google, Facebook)  | ✅           | ❌        | ❌                 | ❌          |
| 2FA Security                                 | ✅           | ❌        | ❌                 | ❌          |
| Social Integration                           | ✅           | ❌        | ❌                 | ❌          |
| Custom Resource Selling                      | ✅           | ❌        | ❌                 | ❌          |
| Advanced Glass Lookup Technology             | ✅           | ❌        | ❌                 | ❌          |
| Max Resources                                | ✅           | ❌        | ❌                 | ❌          |
| Billing Integration (Stripe, PayPal)         | ✅           | ❌        | ❌                 | ❌          |
| Announcements Support                        | ✅           | ❌        | ❌                 | ❌          |
| Redirection Links                            | ✅           | ❌        | ❌                 | ❌          |
| Mail Templates (Editor)                      | ✅           | ❌        | ❌                 | ❌          |
| Departments Support                          | ✅           | ❌        | ❌                 | ❌          |
| Auto Pterodactyl Import                      | ✅           | ❌        | ❌                 | ❌          |
| Pterodactyl Wrappers (No More IDs)           | ✅           | ❌        | ❌                 | ❌          |
| Zero Data Deletion Policy                    | ✅           | ❌        | ❌                 | ❌          |
| Anti Overwrite (Lock Based)                  | ✅           | ❌        | ❌                 | ❌          |
| Health-Based Monitoring                      | ✅           | ❌        | ❌                 | ❌          |
| Built-in Debugger/Helper                     | ✅           | ❌        | ❌                 | ❌          |
| AI Assistant                                 | ✅           | ❌        | ❌                 | ❌          |
| Support PIN Integration                      | ✅           | ❌        | ❌                 | ❌          |
| Multiple Layout Support                      | ✅           | ❌        | ❌                 | ❌          |
| Server Splitting                             | ✅           | ❌        | ❌                 | ❌          |
| Multiple Language Support (EN, DE, FR, RO, ES, MD) | ✅     | ❌        | ❌                 | ❌          |

---

## 🤝 Contributing

Contributions are always welcome! For guidelines on how to contribute, please see [CONTRIBUTING.md](CONTRIBUTING.md).

## 🌐 Live Demo

Experience MythicalDash in action at our demo site:
- **Demo URL**: [https://mythicaldash-v3.mythical.systems](https://mythicaldash-v3.mythical.systems)

> Note: The demo site is reset periodically to ensure a clean testing environment for all users.


---

## 🧪 Installation (For Testing Only)

⚠️ **Warning**: This is a alpha version. Not recommended for production use!

> https://docs.mythical.systems/docs/mythicaldash-v3-remastered/os/pick

---

## 📜 Serial Code

```bash
1 490 650 284
```

## 📄 License

MythicalDash is licensed under the MythicalSystems License v2.0. See the LICENSE file for more details.

---

<p align="center">
  <b>Developed with ❤️ by MythicalSystems</b><br>
  <a href="https://www.mythical.systems">https://www.mythical.systems</a>
</p>
