<?php

function pco_get_all_plugins() {
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    return get_plugins();
}

function pco_get_plugin_categories_map(): array {
    return get_option('pco_plugin_categories', []);
}

function pco_set_plugin_categories_map(array $map): void {
    update_option('pco_plugin_categories', $map);
}

function pco_get_defined_categories(): array {
    return get_option('pco_categories', []);
}

function pco_set_defined_categories(array $cats): void {
    update_option('pco_categories', $cats);
}

