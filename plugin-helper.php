<?php

function pco_get_all_plugins() {
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    return get_plugins();
}

function pco_get_plugin_categories() {
    return get_option('pco_plugin_categories', []);
}

function pco_save_plugin_categories($data) {
    update_option('pco_plugin_categories', $data);
}
