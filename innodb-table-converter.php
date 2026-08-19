<?php
/**
 * Plugin Name: LightMoving InnoDB Converter
 * Plugin URI: https://github.com/LightMoving/innodb-table-converter
 * Description: Safely scan and bulk convert WordPress database tables to the InnoDB storage engine.
 * Version: 1.0.14
 * Author: Debo Grim
 * Author URI: https://github.com/LightMoving
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: innodb-table-converter
 */

if (!defined('ABSPATH')) {
    exit;
}

class InnoDB_Table_Converter {
    const VERSION = '1.0.14';
    const PAGE_SLUG = 'innodb-table-converter';
    const NONCE_ACTION = 'innodb_table_converter_action';
    const TARGET_ENGINE = 'InnoDB';

    private $messages = array();
    private $errors = array();
    private $conversion_log = array();
    private $conversion_completed = false;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_tools_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_tools_page() {
        add_management_page(
            __('LightMoving InnoDB Converter', 'innodb-table-converter'),
            __('InnoDB Converter', 'innodb-table-converter'),
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if ('tools_page_' . self::PAGE_SLUG !== $hook) {
            return;
        }
        wp_register_style('innodb-table-converter-admin', false, array(), self::VERSION);
        wp_enqueue_style('innodb-table-converter-admin');
        wp_add_inline_style('innodb-table-converter-admin', $this->get_admin_css());
    }

    private function get_admin_css() {
        return '.innodb-converter-wrap{max-width:1180px}.innodb-converter-hero{background:linear-gradient(135deg,#112442 0%,#204e7a 55%,#216fae 100%);color:#fff;padding:26px 30px;border-radius:16px;margin:18px 0 20px;box-shadow:0 14px 34px rgba(17,36,66,.18)}.innodb-converter-hero h1{color:#fff;margin:0 0 8px;font-size:28px;line-height:1.2}.innodb-converter-hero p{max-width:850px;color:rgba(255,255,255,.88);margin:0;font-size:15px}.innodb-success-hero-banner{display:flex;align-items:center;gap:16px;width:100%;box-sizing:border-box;background:linear-gradient(135deg,#0fb83f 0%,#05a63c 55%,#059c36 100%);border:1px solid rgba(255,255,255,.38);border-radius:10px;padding:18px 20px;margin:16px 0 18px;color:#fff;box-shadow:inset 0 1px 0 rgba(255,255,255,.18),0 8px 20px rgba(0,0,0,.12)}.innodb-success-hero-icon{display:inline-flex;align-items:center;justify-content:center;flex:0 0 42px;width:42px;height:42px;border-radius:999px;background:rgba(255,255,255,.95);color:#0fa83d;font-size:28px;font-weight:800;line-height:1}.innodb-success-hero-banner h2{color:#fff;font-size:22px;line-height:1.25;margin:0 0 4px}.innodb-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(300px,.8fr);gap:22px;align-items:start}.innodb-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;box-shadow:0 7px 24px rgba(0,0,0,.04);overflow:hidden;margin-bottom:18px}.innodb-card-header{padding:18px 20px;border-bottom:1px solid #eef0f2;background:#f7f9fc}.innodb-card-header h2{margin:0 0 6px;font-size:18px}.innodb-card-header p{margin:0;color:#646970}.innodb-card-body{padding:20px}.innodb-status-good{color:#008a20;font-weight:600}.innodb-status-warn{color:#b26200;font-weight:600}.innodb-status-bad{color:#b32d2e;font-weight:600}.innodb-table-wrap{overflow-x:auto}.innodb-table td,.innodb-table th{vertical-align:middle}.innodb-code{background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:12px;white-space:pre-wrap;font-family:Consolas,Monaco,monospace;font-size:12px}.innodb-warning-box{background:#fff8e5;border:1px solid #dba617;border-left:5px solid #dba617;padding:14px 16px;border-radius:10px;margin-bottom:16px}.innodb-success-box{background:#edfaef;border:1px solid #46b450;border-left:5px solid #46b450;padding:14px 16px;border-radius:10px;margin-bottom:16px}.innodb-error-box{background:#fcf0f1;border:1px solid #d63638;border-left:5px solid #d63638;padding:14px 16px;border-radius:10px;margin-bottom:16px}.innodb-confirm-row{background:#f6f7f7;padding:16px;border-radius:10px;margin:14px 0}.innodb-conversion-summary{background:#edfaef;border:1px solid #46b450;border-radius:12px;padding:18px 20px;margin:18px 0;box-shadow:0 8px 24px rgba(0,0,0,.04)}.innodb-conversion-log{background:#fff;border:1px solid #46b450;border-radius:10px;margin-top:12px;max-height:260px;overflow:auto;padding:12px 14px}.innodb-conversion-log ul{margin:0;padding-left:0;list-style:none}.innodb-conversion-log li{position:relative;margin:4px 0;padding-left:28px;font-family:Consolas,Monaco,monospace;font-size:12px}.innodb-conversion-log li:before{content:"✓";position:absolute;left:0;top:-1px;display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#0fa83d;color:#fff;font-family:Arial,sans-serif;font-size:12px;font-weight:700}@media(max-width:960px){.innodb-grid{grid-template-columns:1fr}}';
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'innodb-table-converter'));
        }

        $this->handle_post_actions();
        $tables = $this->get_wordpress_tables();
        $needs_conversion = $this->get_tables_needing_conversion($tables);
        global $wpdb;
        ?>
        <div class="wrap innodb-converter-wrap">
            <div class="innodb-converter-hero">
                <h1><?php echo esc_html__('LightMoving InnoDB Converter', 'innodb-table-converter'); ?></h1>
                <?php if ($this->conversion_completed) : ?>
                    <div class="innodb-success-hero-banner"><span class="innodb-success-hero-icon" aria-hidden="true">✓</span><div><h2><?php echo esc_html__('Conversion Completed Successfully!', 'innodb-table-converter'); ?></h2><p><?php echo esc_html__('Selected WordPress database tables have been converted to the InnoDB storage engine.', 'innodb-table-converter'); ?></p></div></div>
                <?php endif; ?>
                <p><?php echo esc_html__('Scan and bulk convert WordPress database tables from legacy storage engines such as MyISAM to InnoDB.', 'innodb-table-converter'); ?></p>
            </div>
            <?php $this->render_notices(); ?>
            <div class="innodb-grid">
                <div>
                    <div class="innodb-card"><div class="innodb-card-header"><h2><?php echo esc_html__('Table Engine Status', 'innodb-table-converter'); ?></h2><p><?php echo esc_html__('Review current WordPress table storage engines before converting.', 'innodb-table-converter'); ?></p></div><div class="innodb-card-body"><table class="widefat striped"><tbody>
                    <tr><th><?php echo esc_html__('Database', 'innodb-table-converter'); ?></th><td><?php echo esc_html($this->get_database_name()); ?></td></tr>
                    <tr><th><?php echo esc_html__('WordPress Table Prefix', 'innodb-table-converter'); ?></th><td><code><?php echo esc_html($wpdb->prefix); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Target Engine', 'innodb-table-converter'); ?></th><td><code><?php echo esc_html(self::TARGET_ENGINE); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Tables Needing Conversion', 'innodb-table-converter'); ?></th><td><?php if (empty($needs_conversion)) : ?><span class="innodb-status-good"><?php echo esc_html__('None detected', 'innodb-table-converter'); ?></span><?php else : ?><span class="innodb-status-warn"><?php echo esc_html(count($needs_conversion)); ?></span><?php endif; ?></td></tr>
                    </tbody></table></div></div>

                    <div class="innodb-card"><div class="innodb-card-header"><h2><?php echo esc_html__('WordPress Tables', 'innodb-table-converter'); ?></h2><p><?php echo esc_html__('This tool scans tables using your current WordPress table prefix.', 'innodb-table-converter'); ?></p></div><div class="innodb-card-body">
                    <?php if (empty($tables)) : ?><div class="innodb-error-box"><?php echo esc_html__('No WordPress tables were found for the current table prefix.', 'innodb-table-converter'); ?></div><?php else : ?>
                    <div class="innodb-table-wrap"><table class="widefat striped innodb-table"><thead><tr><th><?php echo esc_html__('Convert', 'innodb-table-converter'); ?></th><th><?php echo esc_html__('Table', 'innodb-table-converter'); ?></th><th><?php echo esc_html__('Engine', 'innodb-table-converter'); ?></th><th><?php echo esc_html__('Rows', 'innodb-table-converter'); ?></th><th><?php echo esc_html__('Size', 'innodb-table-converter'); ?></th><th><?php echo esc_html__('Status', 'innodb-table-converter'); ?></th></tr></thead><tbody>
                    <?php foreach ($tables as $table) : ?>
                    <tr><td><?php if (!$this->table_is_innodb($table)) : ?><input type="checkbox" form="innodb-convert-form" name="innodb_tables[]" value="<?php echo esc_attr($table['name']); ?>" checked><?php else : ?>—<?php endif; ?></td><td><code><?php echo esc_html($table['name']); ?></code></td><td><?php echo esc_html($table['engine']); ?></td><td><?php echo esc_html(number_format_i18n(absint($table['rows']))); ?></td><td><?php echo esc_html(size_format(absint($table['size']))); ?></td><td><?php if ($this->table_is_innodb($table)) : ?><span class="innodb-status-good"><?php echo esc_html__('InnoDB ready', 'innodb-table-converter'); ?></span><?php else : ?><span class="innodb-status-warn"><?php echo esc_html__('Needs conversion', 'innodb-table-converter'); ?></span><?php endif; ?></td></tr>
                    <?php endforeach; ?>
                    </tbody></table></div><?php endif; ?></div></div>

                    <div class="innodb-card"><div class="innodb-card-header"><h2><?php echo esc_html__('Convert Tables', 'innodb-table-converter'); ?></h2><p><?php echo esc_html__('Run this only after making a complete database backup.', 'innodb-table-converter'); ?></p></div><div class="innodb-card-body"><div class="innodb-warning-box"><strong><?php echo esc_html__('Backup required.', 'innodb-table-converter'); ?></strong><?php echo esc_html__(' Converting table storage engines can lock tables temporarily and may take time on large sites. Make a full database backup before continuing.', 'innodb-table-converter'); ?></div>
                    <?php if (empty($needs_conversion)) : ?><div class="innodb-success-box"><?php echo esc_html__('Your WordPress tables already appear to be using InnoDB.', 'innodb-table-converter'); ?></div><?php else : ?>
                    <form method="post" id="innodb-convert-form"><?php wp_nonce_field(self::NONCE_ACTION, 'innodb_converter_nonce'); ?><input type="hidden" name="innodb_converter_action" value="convert"><div class="innodb-confirm-row"><p><label><input type="checkbox" name="innodb_confirm_backup" value="1" required> <?php echo esc_html__('I confirm that I have created a complete database backup.', 'innodb-table-converter'); ?></label></p><p><label for="innodb_confirm_text"><?php echo esc_html__('Type CONVERT to continue:', 'innodb-table-converter'); ?></label><br><input type="text" id="innodb_confirm_text" name="innodb_confirm_text" class="regular-text" autocomplete="off" required></p></div><p><button type="submit" class="button button-primary button-hero"><?php echo esc_html__('Convert Selected Tables to InnoDB', 'innodb-table-converter'); ?></button></p></form>
                    <?php endif; ?></div></div>
                </div>
                <aside><div class="innodb-card"><div class="innodb-card-header"><h2><?php echo esc_html__('What This Does', 'innodb-table-converter'); ?></h2></div><div class="innodb-card-body"><p><?php echo esc_html__('This tool converts selected WordPress database tables to the InnoDB storage engine.', 'innodb-table-converter'); ?></p><div class="innodb-code">ALTER TABLE wp_posts ENGINE=InnoDB;</div></div></div><div class="innodb-card"><div class="innodb-card-header"><h2><?php echo esc_html__('Safety Notes', 'innodb-table-converter'); ?></h2></div><div class="innodb-card-body"><ul><li><?php echo esc_html__('No conversion runs automatically on activation.', 'innodb-table-converter'); ?></li><li><?php echo esc_html__('Only administrators can access this tool.', 'innodb-table-converter'); ?></li><li><?php echo esc_html__('Only tables using the current WordPress prefix are listed.', 'innodb-table-converter'); ?></li><li><?php echo esc_html__('You can deselect tables before conversion.', 'innodb-table-converter'); ?></li><li><?php echo esc_html__('Large tables may take longer depending on hosting resources.', 'innodb-table-converter'); ?></li></ul></div></div></aside>
            </div>
        </div>
        <?php
    }

    private function handle_post_actions() {
        if (empty($_POST['innodb_converter_action'])) {
            return;
        }
        if (!current_user_can('manage_options')) {
            $this->errors[] = __('You do not have permission to perform this action.', 'innodb-table-converter');
            return;
        }
        if (empty($_POST['innodb_converter_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['innodb_converter_nonce'])), self::NONCE_ACTION)) {
            $this->errors[] = __('Security check failed. Please try again.', 'innodb-table-converter');
            return;
        }
        $action = sanitize_key(wp_unslash($_POST['innodb_converter_action']));
        if ('convert' !== $action) {
            return;
        }
        $confirmed_backup = !empty($_POST['innodb_confirm_backup']);
        $confirm_text = isset($_POST['innodb_confirm_text']) ? sanitize_text_field(wp_unslash($_POST['innodb_confirm_text'])) : '';
        if (!$confirmed_backup || 'CONVERT' !== $confirm_text) {
            $this->errors[] = __('Conversion was not started. Please confirm your backup and type CONVERT exactly.', 'innodb-table-converter');
            return;
        }
        $selected_tables = isset($_POST['innodb_tables']) && is_array($_POST['innodb_tables']) ? array_map('sanitize_text_field', wp_unslash($_POST['innodb_tables'])) : array();
        if (empty($selected_tables)) {
            $this->errors[] = __('No tables were selected for conversion.', 'innodb-table-converter');
            return;
        }
        $this->run_conversion($selected_tables);
    }

    private function run_conversion($selected_tables) {
        global $wpdb;
        $tables = $this->get_wordpress_tables();
        $allowed_tables = array();
        foreach ($tables as $table) {
            if (!$this->table_is_innodb($table)) {
                $allowed_tables[$table['name']] = $table;
            }
        }
        foreach ($selected_tables as $table_name) {
            if (empty($allowed_tables[$table_name])) {
                continue;
            }
            $table_sql = 'ALTER TABLE ' . $this->quote_identifier($table_name) . ' ENGINE=' . self::TARGET_ENGINE;
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared -- Intentional admin-confirmed ALTER TABLE operation using safely quoted table identifier and fixed engine constant.
            $result = $wpdb->query($table_sql);
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared
            if (false === $result) {
                $this->errors[] = sprintf(
                    /* translators: 1: database table name, 2: database error message. */
                    __('Table %1$s failed: %2$s', 'innodb-table-converter'),
                    $table_name,
                    $wpdb->last_error
                );
                continue;
            }
            $this->conversion_log[] = sprintf(
                /* translators: %s: converted database table name. */
                __('Converted table: %s', 'innodb-table-converter'),
                $table_name
            );
        }
        if (empty($this->errors)) {
            $this->conversion_completed = true;
        }
    }

    private function render_notices() {
        foreach ($this->errors as $error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
        if (!empty($this->conversion_log)) {
            echo '<div class="innodb-conversion-summary"><h2>' . esc_html__('Conversion Log', 'innodb-table-converter') . '</h2><p>' . esc_html__('The selected table conversion completed and the following actions were recorded:', 'innodb-table-converter') . '</p><div class="innodb-conversion-log"><ul>';
            foreach ($this->conversion_log as $log_entry) {
                echo '<li>' . esc_html($log_entry) . '</li>';
            }
            echo '</ul></div></div>';
            return;
        }
        foreach ($this->messages as $message) {
            echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
        }
    }

    private function get_database_name() {
        if (defined('DB_NAME') && DB_NAME) {
            return DB_NAME;
        }
        global $wpdb;
        return isset($wpdb->dbname) ? $wpdb->dbname : '';
    }

    private function get_wordpress_tables() {
        global $wpdb;
        $like = $wpdb->esc_like($wpdb->prefix) . '%';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional database metadata read for WordPress table engine status.
        $rows = $wpdb->get_results($wpdb->prepare('SHOW TABLE STATUS LIKE %s', $like), ARRAY_A);
        if (empty($rows)) {
            return array();
        }
        $tables = array();
        foreach ($rows as $row) {
            if (empty($row['Name'])) {
                continue;
            }
            $data_length = isset($row['Data_length']) ? absint($row['Data_length']) : 0;
            $index_length = isset($row['Index_length']) ? absint($row['Index_length']) : 0;
            $tables[] = array(
                'name' => $row['Name'],
                'engine' => isset($row['Engine']) ? $row['Engine'] : '',
                'rows' => isset($row['Rows']) ? absint($row['Rows']) : 0,
                'size' => $data_length + $index_length,
            );
        }
        return $tables;
    }

    private function get_tables_needing_conversion($tables) {
        $needs_conversion = array();
        foreach ($tables as $table) {
            if (!$this->table_is_innodb($table)) {
                $needs_conversion[] = $table;
            }
        }
        return $needs_conversion;
    }

    private function table_is_innodb($table) {
        if (empty($table['engine'])) {
            return false;
        }
        return 'innodb' === strtolower($table['engine']);
    }

    private function quote_identifier($identifier) {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'innodb_table_converter_action_links');

function innodb_table_converter_action_links($links) {
    $tools_link = '<a href="' . esc_url(admin_url('tools.php?page=innodb-table-converter')) . '">' . esc_html__('Tools', 'innodb-table-converter') . '</a>';
    array_unshift($links, $tools_link);
    return $links;
}

new InnoDB_Table_Converter();
