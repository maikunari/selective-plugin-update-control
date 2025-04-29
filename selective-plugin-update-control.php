<?php
/*
Plugin Name: Selective Plugin Update Control
Plugin URI: https://github.com/maikunari/disable-plugin-updates
Description: Allows selective disabling of plugin updates, giving administrators control over which plugins receive updates.
Version: 1.0.0
Author: maikunari
Author URI: https://github.com/maikunari  
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: selective-plugin-update-control
*/


// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Selective_Plugin_Update_Control {
    private $option_name = 'disabled_plugin_updates';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_filter('site_transient_update_plugins', [$this, 'disable_plugin_updates']);
    }

    // Add admin menu page
    public function add_admin_menu() {
        add_options_page(
            'Plugin Update Control',
            'Plugin Updates',
            'manage_options',
            'plugin-update-control',
            [$this, 'render_admin_page']
        );
    }

    // Register settings
    public function register_settings() {
        register_setting('plugin_update_control_group', $this->option_name);
    }

    // Render admin page
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Plugin Update Control</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('plugin_update_control_group');
                do_settings_sections('plugin_update_control_group');
                
                $disabled_plugins = get_option($this->option_name, []);
                $plugins = get_plugins();
                ?>
                <table class="form-table">
                    <tr>
                        <th>Plugin</th>
                        <th>Disable Updates</th>
                    </tr>
                    <?php foreach ($plugins as $plugin_file => $plugin_data): ?>
                        <tr>
                            <td><?php echo esc_html($plugin_data['Name']); ?></td>
                            <td>
                                <input type="checkbox" 
                                       name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($plugin_file); ?>]"
                                       value="1" 
                                       <?php checked(isset($disabled_plugins[$plugin_file])); ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    // Filter to disable updates for selected plugins
    public function disable_plugin_updates($value) {
        if (!isset($value) || !is_object($value)) {
            return $value;
        }

        $disabled_plugins = get_option($this->option_name, []);
        
        if (!empty($disabled_plugins)) {
            foreach (array_keys($disabled_plugins) as $plugin_file) {
                unset($value->response[$plugin_file]);
            }
        }

        return $value;
    }
}

// Initialize the plugin
new Selective_Plugin_Update_Control();
?>