[LightMoving Innodb Converter](assets/banner-1544x500.png)

# 🛠 LightMoving InnoDB Converter

![Version](https://img.shields.io/badge/version-1.0.10)
![WordPress](https://img.shields.io/badge/WordPress-7.1-blue)
![License](https://img.shields.io/badge/license-GPL%20v2-green)

Safely scan and bulk convert WordPress database tables from legacy storage engines such as MyISAM to InnoDB.

---

## 🔍 Overview

LightMoving InnoDB Converter helps modernize older WordPress databases that still contain tables using MyISAM or other legacy storage engines.

The plugin scans your WordPress database tables, identifies tables needing conversion, and provides a safe administrator-controlled bulk conversion workflow.

---

## ⚡ Features

- WordPress table engine scan
- Bulk conversion to InnoDB
- Selected-table conversion controls
- Backup confirmation workflow
- Required CONVERT confirmation step
- Table row and size display
- Clean conversion logging
- Responsive modern admin interface
- No automatic conversion on activation
- Direct Tools link from Plugins page

---

## 🔒 Safety First

The plugin requires administrator access, backup confirmation, and manual CONVERT confirmation. No database conversion occurs automatically on activation.

---

## 📦 Installation

1. Upload the plugin to `/wp-content/plugins/`
2. Activate the plugin
3. Go to `Tools → InnoDB Converter`
4. Review your table engine status
5. Create a complete database backup
6. Run the selected-table conversion workflow

---

## 🔄 Conversion Workflow

The plugin scans WordPress tables using the active prefix, identifies tables not using InnoDB, allows selection/deselection, converts selected tables, and displays a clean conversion summary log.

---

## 🧠 Example Conversion Query

```sql
ALTER TABLE wp_posts ENGINE=InnoDB;
```

---

## 📜 Changelog

### 1.0.10 


- Updated Version for WordPress 7.1

### 1.0.7
- Updated for Wordpress 7.1

### 1.0.6
- Versioning for 7.0.2

### 1.0.5
- Updated POT

### 1.0.4
- Updated Wordpress packaging

### 1.0.3
- Updated plugin packaging for WordPress 7.1

### 1.0.2
- Updated internationalization text domain to match the WordPress.org plugin slug.
- Updated plugin packaging folder to innodb-table-converter.

### 1.0.1
- Initial release
- WordPress table engine scanning
- Selected-table InnoDB conversion workflow
- Backup confirmation system

---

## ⚖ License

GPL v2 or later


<!-- publisher:release:start -->
**Current Version:** 1.0.10

### 1.0.10

Publisher Payload: `5b333ae036c71dc8…`

Release packaged and verified with WordPress Plugin Publisher.
<!-- publisher:release:end -->
