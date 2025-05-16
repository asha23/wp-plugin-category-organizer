<?php

add_action('admin_menu', function () {
    add_plugins_page('Plugins by Category', 'Plugins by Category', 'manage_options', 'pco-plugin-categories', 'pco_render_admin_page');
});

function pco_render_admin_page() {
    $is_dev = pco_is_dev();

    $plugins = get_plugins();
    $assigned = pco_get_plugin_categories_map();
    $categories = pco_get_defined_categories();

    if ($is_dev && $_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('pco_plugin_category_form');

        if (isset($_POST['plugin_assignments'])) {
            pco_set_plugin_categories_map($_POST['plugin_assignments']);
            echo '<div class="updated"><p>Plugin assignments saved.</p></div>';
        }

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
            $assigned = array_filter($assigned, fn($v) => $v !== $del_slug);
            pco_set_defined_categories($categories);
            pco_set_plugin_categories_map($assigned);
            echo '<div class="updated"><p>Category deleted and assignments updated.</p></div>';
        }
    }

    echo '<div class="wrap"><h1>Plugins by Category</h1>';
    echo '<p>This tool reads/writes to <code>/public/plugin-data</code>.</p>';

    if ($is_dev) {
        echo '<form method="POST">';
        wp_nonce_field('pco_plugin_category_form');

        echo '<h2>Categories</h2><ul>';
        foreach ($categories as $slug => $label) {
            echo "<li><strong>$label</strong> (<code>$slug</code>) 
                <button name='delete_category' value='" . esc_attr($slug) . "' class='button-link-delete' onclick='return confirm(\"Delete this category?\")'>Delete</button></li>";
        }
        echo '</ul><input type="text" name="new_category" placeholder="Add new category" />
            <input type="submit" class="button button-secondary" value="Add" /></form><hr>';

        echo '<form method="POST">';
        wp_nonce_field('pco_plugin_category_form');
        echo '<h2>Assign Plugins</h2><table class="form-table"><tbody>';

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

        echo '</tbody></table><p><input type="submit" class="button button-primary" value="Save Assignments" /></p></form>';
    } else {
        echo '<p><em>This environment is read-only. Categories and assignments are managed in development only.</em></p>';
    }

    echo '<hr><h2>Grouped Plugin View</h2>';

    $grouped = [];
    foreach ($plugins as $pluginPath => $pluginData) {
        $category = $assigned[$pluginPath] ?? 'Uncategorized';
        $grouped[$category][] = $pluginData;
    }

    ksort($grouped);
    foreach ($grouped as $category => $items) {
        $label = $categories[$category] ?? $category;
        echo '<h3>' . esc_html($label) . '</h3><ul>';
        foreach ($items as $plugin) {
            echo '<li>' . esc_html($plugin['Name']) . ' <span style="color:#999;">(' . esc_html($plugin['Version']) . ')</span></li>';
        }
        echo '</ul>';
    }

    echo '</div>';
}
