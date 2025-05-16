<?php

function pco_is_dev(): bool {
    if (defined('WP_ENVIRONMENT_TYPE')) {
        return WP_ENVIRONMENT_TYPE === 'development';
    }

    if (defined('WP_ENV')) {
        return WP_ENV === 'development';
    }

    return false;
}

function pco_data_dir(): string {
    return dirname(__DIR__, 3) . '/resources/plugin-data';
}

function pco_categories_file(): string {
    return pco_data_dir() . '/pco-categories.json';
}

function pco_plugin_map_file(): string {
    return pco_data_dir() . '/pco-plugin-map.json';
}

function pco_get_plugin_categories_map(): array {
    $file = pco_plugin_map_file();
    return file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];
}

function pco_get_defined_categories(): array {
    $file = pco_categories_file();
    return file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];
}

function pco_set_plugin_categories_map(array $map): void {
    if (!pco_is_dev()) return;
    file_put_contents(pco_plugin_map_file(), json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function pco_set_defined_categories(array $cats): void {
    if (!pco_is_dev()) return;
    file_put_contents(pco_categories_file(), json_encode($cats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function pco_initialize_flatfiles(): void {
    if (!pco_is_dev()) {
        return;
    }

    $dir = pco_data_dir();
    $catFile = pco_categories_file();
    $mapFile = pco_plugin_map_file();

    if (!file_exists($dir)) {
        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
            error_log("[Plugin Category Organizer] Failed to create data dir: $dir");
            return;
        }
    }

    // Create categories.json if it doesn't exist
    if (!file_exists($catFile)) {
        $defaultCats = ['unnasigned' => 'Unnasigned'];
        file_put_contents($catFile, json_encode($defaultCats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    // Create plugin map if it doesn't exist
    if (!file_exists($mapFile)) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();
        $map = [];

        foreach ($plugins as $pluginPath => $pluginData) {
            $map[$pluginPath] = 'unnasigned';
        }

        file_put_contents($mapFile, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

