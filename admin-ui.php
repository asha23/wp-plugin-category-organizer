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
        echo '<h2>Assign Plugins</h2>';

        echo '<p><input type="submit" class="button button-primary" value="Save Assignments" /></p>';

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Plugin</th><th>Category</th></tr></thead><tbody>';

        ksort($categories);
        $category_keys = array_keys($categories);
        $plugins_by_category = [];

        foreach ($plugins as $pluginPath => $pluginData) {
            $cat = $assigned[$pluginPath] ?? '';
            $plugins_by_category[$cat][] = [
                'path' => $pluginPath,
                'name' => $pluginData['Name'],
                'version' => $pluginData['Version'],
            ];
        }

        foreach ($categories as $slug => $label) {
            if (!isset($plugins_by_category[$slug])) continue;
            foreach ($plugins_by_category[$slug] as $plugin) {
                echo '<tr>';
                echo '<td>' . esc_html($plugin['name']) . ' <span style="color:#999;">(' . esc_html($plugin['version']) . ')</span></td>';
                echo '<td><select name="plugin_assignments[' . esc_attr($plugin['path']) . ']">';
                echo '<option value="">Unassigned</option>';
                foreach ($categories as $cat_slug => $cat_label) {
                    $selected = selected($slug, $cat_slug, false);
                    echo "<option value='" . esc_attr($cat_slug) . "' $selected>$cat_label</option>";
                }
                echo '</select></td>';
                echo '</tr>';
            }
        }

        // Add unassigned ones
        foreach ($plugins as $pluginPath => $pluginData) {
            if (!isset($assigned[$pluginPath]) || !isset($categories[$assigned[$pluginPath]])) {
                echo '<tr>';
                echo '<td>' . esc_html($pluginData['Name']) . ' <span style="color:#999;">(' . esc_html($pluginData['Version']) . ')</span></td>';
                echo '<td><select name="plugin_assignments[' . esc_attr($pluginPath) . ']">';
                echo '<option value="" selected>Unassigned</option>';
                foreach ($categories as $slug => $label) {
                    echo "<option value='" . esc_attr($slug) . "'>$label</option>";
                }
                echo '</select></td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';
        echo '<p><input type="submit" class="button button-primary" value="Save Assignments" /></p>';
        echo '</form>';
    } else {
        echo '<p><em>Categories and assignments are managed in development only.</em></p>';
    }

    echo '</div>';
}
