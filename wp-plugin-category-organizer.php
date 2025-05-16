<?php
/**
 * Plugin Name:        	BrightLocal - Plugin Category Organizer
 * Description:         Plugin Category Organizer - Gives the ability to organize plugins by category
 * Author:              Ash Whiting for BrightLocal
 * Author URI:          https://brightlocal.com
 * Text Domain:         bl-plugin-category-organizer
 * Version:             0.0.1
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

 if (!defined('ABSPATH')) {
    exit;
}

define('PCO_PLUGIN_PATH', plugin_dir_path(__FILE__));

require_once PCO_PLUGIN_PATH . 'plugin-helper.php';
require_once PCO_PLUGIN_PATH . 'admin-ui.php';
require_once PCO_PLUGIN_PATH . 'plugin-filters.php';

register_activation_hook(__FILE__, 'pco_on_activate');

function pco_on_activate(): void {
    require_once PCO_PLUGIN_PATH . 'plugin-helper.php';
    pco_initialize_flatfiles();
}