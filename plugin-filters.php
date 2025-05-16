<?php

add_filter('views_plugins', function ($views) {
    $categories = pco_get_defined_categories();
    $assigned = pco_get_plugin_categories_map();

    foreach ($categories as $slug => $label) {
        $count = count(array_filter($assigned, fn($v) => $v === $slug));
        $url = add_query_arg('pco_category', $slug, admin_url('plugins.php'));
        $views[$slug] = "<a href='" . esc_url($url) . "'>" . esc_html($label) . " <span class='count'>($count)</span></a>";
    }

    return $views;
});

add_filter('all_plugins', function ($plugins) {
    if (!isset($_GET['pco_category'])) {
        return $plugins;
    }

    $filter = sanitize_title($_GET['pco_category']);
    $assigned = pco_get_plugin_categories_map();

    return array_filter($plugins, fn($k) => ($assigned[$k] ?? '') === $filter, ARRAY_FILTER_USE_KEY);
});
