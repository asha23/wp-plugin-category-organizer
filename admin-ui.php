<?php

add_action('admin_menu', function () {
    add_plugins_page('Plugins by Category', 'Plugins by Category', 'manage_options', 'pco-plugin-categories', 'pco_render_admin_page');
});

function pco_render_admin_page() {
    $plugins = pco_get_all_plugins();
    $assigned = pco_get_plugin_categories_map();
    $categories = pco_get_defined_categories();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('pco_plugin_category_form');

        // Save plugin assignments
        if (isset($_POST['plugin_assignments'])) {
            pco_set_plugin_categories_map($_POST['plugin_assignments']);
            echo '<div class="updated"><p>Plugin assignments saved.</p></div>';
        }

        // Save category definitions
        if (isset($_POST['new_category'])) {
            $slug = sanitize_title($_POST['new_category']);
            $label = sanitize_text_field($_POST['new_category']);
            if ($slug && !isset($categories[$slug])) {
                $categories[$slug] = $label;
                pco_set_defined_categories($categories);
                echo '<div class="updated"><p>Category added.</p></div>';
            }
        }

        if (isset($_POST['delete_category'])) {
            $del_slug = sanitize_title($_POST['delete_category']);
            unset($categories[$del_slug]);
            pco_set_defined_categories($categories);
            // Clean plugin assignments
            $assigned = array_filter($assigned, fn($cat) => $cat !== $del_slug);
            pco_set_plugin_categories_map($assigned);
            echo '<div class="updated"><p>Category deleted.</p></div>';
        }
    }

    // UI
    echo '<div class="wrap"><h1>Plugins by Category</h1>';

    echo '<form method="POST">';
    wp_nonce_field('pco_plugin_category_form');

    echo '<h2>Manage Categories</h2><ul>';
    foreach ($categories as $slug => $label) {
        echo "<li><strong>$label</strong> (<code>$slug</code>) 
        <button name='delete_category' value='" . esc_attr($slug) . "' class='button-link-delete' onclick='return confirm(\"Delete this category?\")'>Delete</button></li>";
    }
    echo '</ul><input type="text" name="new_category" placeholder="Add new category" />
    <input type="submit" class="button button-secondary" value="Add" /></form><hr>';

    echo '<form method="POST">';
    wp_nonce_field('pco_plugin_category_form');
    echo '<h2>Assign Plugins to Categories</h2><table class="form-table"><tbody>';

    foreach ($plugins as $pluginPath => $pluginData) {
        $current = $assigned[$pluginPath] ?? '';
        echo '<tr><th scope="row">' . esc_html($pluginData['Name']) . '</th><td><select name="plugin_assignments[' . esc_attr($pluginPath) . ']">';
        echo '<option value="">Unassigned</option>';
        foreach ($categories as $slug => $label) {
            $selected = selected($slug, $current, false);
            echo "<option value='" . esc_attr($slug) . "' $selected>$label</option>";
        }
        echo '</select></td></tr>';
    }

    echo '</tbody></table><p><input type="submit" class="button button-primary" value="Save Assignments" /></p></form></div>';
}

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
    if (!is_admin() || !isset($_GET['pco_category'])) {
        return $plugins;
    }

    $filter = sanitize_title($_GET['pco_category']);
    $assigned = pco_get_plugin_categories_map();

    return array_filter($plugins, fn($k) => ($assigned[$k] ?? '') === $filter, ARRAY_FILTER_USE_KEY);
});