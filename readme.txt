=== LightMoving InnoDB Converter ===
Contributors: angelsrock
Tags: database, innodb, myisam, tables, performance
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.12
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Safely scan and bulk convert WordPress database tables from legacy storage engines such as MyISAM to InnoDB.

== Description ==

LightMoving InnoDB Converter helps modernize older WordPress databases that still contain tables using MyISAM or other legacy storage engines.

The plugin scans your WordPress database tables, identifies tables that are not using InnoDB, and provides a safe administrator-controlled bulk conversion workflow.

Features include:

* WordPress table engine scan
* Bulk conversion to InnoDB
* Selected-table conversion controls
* Backup confirmation workflow
* Required CONVERT confirmation step
* Table row and size display
* Clean conversion success logging
* Responsive modern admin interface
* Direct Tools link from the Plugins page
* No automatic conversion on activation

== Safety Features ==

* No automatic conversion on plugin activation
* Administrator-only access
* Backup confirmation checkbox
* Required CONVERT confirmation text
* Converts only selected tables using the active WordPress table prefix
* Displays current table engine, row count, and size
* Allows deselecting tables before conversion

== Important ==

Always create a complete database backup before converting database tables.

Large tables may require additional time depending on hosting resources. Conversion operations may temporarily lock tables while MySQL or MariaDB processes ALTER TABLE operations.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin
3. Go to Tools → InnoDB Converter
4. Review your database table engine status
5. Create a full database backup
6. Confirm and run the conversion

== Frequently Asked Questions ==

= Does this run automatically? =

No. The plugin never converts database tables automatically on activation.

= What does this convert? =

The plugin converts selected WordPress tables using the active table prefix to the InnoDB storage engine.

= Can I choose which tables are converted? =

Yes. Tables needing conversion are selected by default, but you can deselect any table before running the conversion.

= Should I create a backup first? =

Yes. Always create a complete database backup before running database conversion operations.

== Changelog ==







= 1.0.12 =
* Layout Improvements

= 1.0.11 =
* Performance Upgrades

= 1.0.10 =
* Updated Version for WordPress 7.1

= 1.0.9 =
* Updated for Wordpress 7.1

= 1.0.8 =
* Adjustments and Improvements

= 1.0.7 =
* Updated for WordPress 7.0.3

= 1.0.6 =
* Versioning for 7.0.2

= 1.0.5 =
* Updated POT

= 1.0.4 =
* Updated WordPress packaging

= 1.0.3 =
* Updated plugin packaging for WordPress 7.0.1

= 1.0.2 =
* Updated internationalization text domain to match the WordPress.org plugin slug.
* Updated plugin packaging folder to innodb-table-converter.

= 1.0.1 =
* Initial release
* Added WordPress table engine scanning
* Added selected-table InnoDB conversion workflow
* Added backup confirmation and CONVERT verification
* Added table size and row count display
* Added conversion success logging

== Upgrade Notice ==

= 1.0.1 =
Initial release.