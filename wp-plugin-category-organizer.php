<?php
/**
 * Plugin Name: Plugin Category Organizer
 * Description: Organize plugins by category using flat JSON files stored in /resources/plugin-data. Read-only in non-development environments.
 * Version: 1.0.0
 * Author: Ash
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