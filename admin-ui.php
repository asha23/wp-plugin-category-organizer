<?php

add_action('admin_menu', function () {
    add_plugins_page('Plugins by Category', 'Plugins by Category', 'manage_options', 'pco-plugin-categories', 'pco_render_admin_page');
});

function pco_render_admin_page() {
    $plugins = pco_get_all_plugins();
    $categories = pco_get_plugin_categories();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pco_plugin_categories'])) {
        check_admin_referer('pco_plugin_category_form');
        pco_save_plugin_categories($_POST['pco_plugin_categories']);
        $categories = $_POST['pco_plugin_categories'];
        echo '<div class="updated"><p>Plugin categories updated.</p></div>';
    }

    wp_enqueue_style('pco-style', plugins_url('assets/style.css', __FILE__));

    echo '<div class="wrap">';
    echo '<h1>Plugins by Category</h1>';
    echo '<form method="POST">';
    wp_nonce_field('pco_plugin_category_form');
    echo '<table class="form-table"><tbody>';

    foreach ($plugins as $pluginPath => $pluginData) {
        $currentCategory = $categories[$pluginPath] ?? '';
        echo '<tr>';
        echo '<th scope="row">' . esc_html($pluginData['Name']) . '</th>';
        echo '<td><input type="text" name="pco_plugin_categories[' . esc_attr($pluginPath) . ']" value="' . esc_attr($currentCategory) . '" placeholder="e.g. SEO, Performance" /></td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '<p><input type="submit" class="button button-primary" value="Save Categories" /></p>';
    echo '</form>';
    echo '<hr>';

    echo '<h2>Grouped Plugins</h2>';
    $grouped = [];

    foreach ($plugins as $pluginPath => $pluginData) {
        $category = $categories[$pluginPath] ?? 'Uncategorized';
        $grouped[$category][] = $pluginData;
    }

    ksort($grouped);
    foreach ($grouped as $category => $items) {
        echo '<h3>' . esc_html($category) . '</h3><ul>';
        foreach ($items as $plugin) {
            echo '<li>' . esc_html($plugin['Name']) . ' - ' . esc_html($plugin['Version']) . '</li>';
        }
        echo '</ul>';
    }

    echo '</div>';
}
