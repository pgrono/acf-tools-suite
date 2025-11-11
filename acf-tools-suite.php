<?php
/**
 * Plugin Name:       ACF Tools Suite
 * Plugin URI:        https://github.com/pgrono
 * Description:       A set of tools for ACF: A code generator that intelligently generates loops with a list of actual subfields, and a field value Debugger.
 * Version:           1.1
 * Author:            Piotr Grono
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       acf-tools-suite
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =========================================================================
//  INITIALIZATION AND TRANSLATIONS
// =========================================================================

function ats_load_textdomain() {
    load_plugin_textdomain( 'acf-tools-suite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}
add_action( 'plugins_loaded', 'ats_load_textdomain' );


// =========================================================================
//  ADMIN MENU
// =========================================================================

function ats_add_admin_menu() {
    add_menu_page(
        __( 'ACF Tools', 'acf-tools-suite' ),
        __( 'ACF Tools', 'acf-tools-suite' ),
        'manage_options',
        'acf-tools',
        'ats_render_generator_page',
        'dashicons-admin-tools',
        81
    );
    add_submenu_page(
        'acf-tools',
        __( 'Code Generator', 'acf-tools-suite' ),
        __( 'Code Generator', 'acf-tools-suite' ),
        'manage_options',
        'acf-tools',
        'ats_render_generator_page'
    );
    add_submenu_page(
        'acf-tools',
        __( 'Field Debugger', 'acf-tools-suite' ),
        __( 'Field Debugger', 'acf-tools-suite' ),
        'manage_options',
        'acf-field-debugger',
        'ats_render_debugger_page'
    );
}
add_action( 'admin_menu', 'ats_add_admin_menu' );

// =========================================================================
//  PAGE 1: CODE GENERATOR
// =========================================================================

function ats_render_generator_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'ACF Code Snippet Generator', 'acf-tools-suite' ); ?></h1>
        <p><?php _e( 'Copy ready-made code snippets. The plugin automatically detects array-based fields (e.g., Repeater, Flexible Content) and generates loops for them with a list of actual subfields.', 'acf-tools-suite' ); ?></p>
        <?php
        if ( ! function_exists( 'acf_get_field_groups' ) ) {
            echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'Error:', 'acf-tools-suite' ) . '</strong> ' . esc_html__( 'The Advanced Custom Fields plugin is not active.', 'acf-tools-suite' ) . '</p></div>';
            return;
        }
        $all_field_groups = acf_get_field_groups();
        if ( ! $all_field_groups ) {
            echo '<div class="notice notice-warning"><p>' . esc_html__( 'No ACF field groups found.', 'acf-tools-suite' ) . '</p></div>';
            return;
        }
        $is_orphans_active = has_filter('orphan_replace');
        if ($is_orphans_active) {
            echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'The "Orphans" plugin is active. Additional code options with the `orphan_replace` filter are displayed.', 'acf-tools-suite' ) . '</p></div>';
        }
        
        $standard_groups = []; $options_groups = [];
        foreach ( $all_field_groups as $group ) {
            ats_is_options_page_group( $group ) ? $options_groups[] = $group : $standard_groups[] = $group;
        }
        
        if ( ! empty( $standard_groups ) ) {
            echo '<h2>' . esc_html__( 'Standard Fields (for posts, pages, etc.)', 'acf-tools-suite' ) . '</h2>';
            foreach ( $standard_groups as $group ) ats_display_fields_table($group, $is_orphans_active, false);
        }
        if ( ! empty( $options_groups ) ) {
            echo '<hr style="margin: 30px 0;"><h2>' . esc_html__( 'Options Page Fields', 'acf-tools-suite' ) . '</h2>';
            foreach ( $options_groups as $group ) ats_display_fields_table($group, $is_orphans_active, true);
        }
        ?>
    </div>
    <?php
}

function ats_display_fields_table($group, $is_orphans_active, $is_options = false) {
    $fields = acf_get_fields( $group['key'] );
    if ( ! $fields ) return;
    echo '<h3>' . esc_html__( 'Group:', 'acf-tools-suite' ) . ' ' . esc_html( $group['title'] ) . '</h3>';
    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr>
            <th style="width: 20%;"><?php _e( 'Field Label', 'acf-tools-suite' ); ?></th>
            <th style="width: 20%;"><?php _e( 'Field Name', 'acf-tools-suite' ); ?></th>
            <th><?php _e( 'Code to Copy', 'acf-tools-suite' ); ?></th>
        </tr></thead>
        <tbody>
            <?php foreach ( $fields as $field ) :
                $field_name = $field['name'];
                $get_field_params = $is_options ? "\"$field_name\", \"option\"" : "\"$field_name\"";
                
                $always_array = ['repeater', 'gallery', 'flexible_content', 'checkbox'];
                $sometimes_array = ['relationship', 'post_object', 'page_link', 'taxonomy', 'user'];
                $is_array_field = in_array($field['type'], $always_array) || (in_array($field['type'], $sometimes_array) && !empty($field['multiple']));
            ?>
            <tr>
                <td><strong><?php echo esc_html( $field['label'] ); ?></strong><br><small><?php _e('Type:', 'acf-tools-suite'); ?> <code><?php echo $field['type']; ?></code></small></td>
                <td><code><?php echo esc_html( $field_name ); ?></code></td>
                <td>
                    <?php if ( $is_array_field ) :
                        $loop_code = ats_generate_loop_snippet($field, $is_options);
                    ?>
                        <div class="ats-code-block">
                            <p><strong><?php _e( 'Loop (for array field):', 'acf-tools-suite' ); ?></strong></p>
                            <div class="ats-copy-wrapper">
                                <textarea readonly class="ats-code-area"><?= esc_textarea($loop_code) ?></textarea>
                                <button class="button ats-copy-btn" data-copy-text="<?php esc_attr_e( 'Copy', 'acf-tools-suite' ); ?>" data-copied-text="<?php esc_attr_e( 'Copied!', 'acf-tools-suite' ); ?>"><?php _e( 'Copy', 'acf-tools-suite' ); ?></button>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="ats-code-block"><p><strong><?php _e( 'Display (echo):', 'acf-tools-suite' ); ?></strong></p><div class="ats-copy-wrapper"><input type="text" readonly value="<?= esc_attr("<?= get_field($get_field_params) ?>") ?>"><button class="button ats-copy-btn" data-copy-text="<?php esc_attr_e( 'Copy', 'acf-tools-suite' ); ?>" data-copied-text="<?php esc_attr_e( 'Copied!', 'acf-tools-suite' ); ?>"><?php _e( 'Copy', 'acf-tools-suite' ); ?></button></div></div>
                        <div class="ats-code-block"><p><strong><?php _e( 'Assign to variable:', 'acf-tools-suite' ); ?></strong></p><div class="ats-copy-wrapper"><input type="text" readonly value="<?= esc_attr("\$$field_name = get_field($get_field_params);") ?>"><button class="button ats-copy-btn" data-copy-text="<?php esc_attr_e( 'Copy', 'acf-tools-suite' ); ?>" data-copied-text="<?php esc_attr_e( 'Copied!', 'acf-tools-suite' ); ?>"><?php _e( 'Copy', 'acf-tools-suite' ); ?></button></div></div>
                        <?php if ($is_orphans_active) : ?>
                            <div class="ats-code-block"><p><strong><?php _e( 'Display with Orphans filter:', 'acf-tools-suite' ); ?></strong></p><div class="ats-copy-wrapper"><input type="text" readonly value="<?= esc_attr("<?= apply_filters('orphan_replace', get_field($get_field_params) ); ?>") ?>"><button class="button ats-copy-btn" data-copy-text="<?php esc_attr_e( 'Copy', 'acf-tools-suite' ); ?>" data-copied-text="<?php esc_attr_e( 'Copied!', 'acf-tools-suite' ); ?>"><?php _e( 'Copy', 'acf-tools-suite' ); ?></button></div></div>
                            <div class="ats-code-block"><p><strong><?php _e( 'Variable with Orphans filter:', 'acf-tools-suite' ); ?></strong></p><div class="ats-copy-wrapper"><input type="text" readonly value="<?= esc_attr("\$$field_name = apply_filters('orphan_replace', get_field($get_field_params));") ?>"><button class="button ats-copy-btn" data-copy-text="<?php esc_attr_e( 'Copy', 'acf-tools-suite' ); ?>" data-copied-text="<?php esc_attr_e( 'Copied!', 'acf-tools-suite' ); ?>"><?php _e( 'Copy', 'acf-tools-suite' ); ?></button></div></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

function ats_generate_loop_snippet($field, $is_options = false) {
    $field_name = $field['name'];
    $params = $is_options ? "'$field_name', 'option'" : "'$field_name'";
    $snippet = '';

    switch ($field['type']) {
        case 'repeater':
            $snippet = "<?php if( have_rows($params) ): ?>\n";
            $snippet .= "    <?php while( have_rows($params) ): the_row(); ?>\n";
            if ( ! empty($field['sub_fields']) ) {
                foreach ($field['sub_fields'] as $sub_field) {
                    $snippet .= "        \$" . $sub_field['name'] . " = get_sub_field('" . $sub_field['name'] . "');\n";
                }
                $snippet .= "        <?php // You can now use the variables: \$" . implode(', \$', array_column($field['sub_fields'], 'name')) . " ?>\n";
            } else {
                $snippet .= "        <?php // No sub fields defined. ?>\n";
            }
            $snippet .= "    <?php endwhile; ?>\n";
            $snippet .= "<?php endif; ?>";
            break;

        case 'flexible_content':
            $snippet = "<?php if( have_rows($params) ): ?>\n";
            $snippet .= "    <?php while ( have_rows($params) ) : the_row(); ?>\n";
            if ( ! empty($field['layouts']) ) {
                $first_layout = true;
                foreach ($field['layouts'] as $layout) {
                    $if_statement = $first_layout ? 'if' : 'elseif';
                    $snippet .= "        <?php {$if_statement}( get_row_layout() == '{$layout['name']}' ): // Layout: {$layout['label']} ?>\n";
                    if ( ! empty($layout['sub_fields']) ) {
                        foreach ($layout['sub_fields'] as $sub_field) {
                            $snippet .= "            \$" . $sub_field['name'] . " = get_sub_field('" . $sub_field['name'] . "');\n";
                        }
                    } else {
                        $snippet .= "            <?php // No fields in this layout. ?>\n";
                    }
                    $snippet .= "        <?php endif; ?>\n";
                    $first_layout = false;
                }
            }
            $snippet .= "    <?php endwhile; ?>\n";
            $snippet .= "<?php endif; ?>";
            break;

        case 'gallery':
            $snippet = "<?php \$images = get_field($params); ?>\n";
            $snippet .= "<?php if( \$images ): ?>\n";
            $snippet .= "    <ul>\n";
            $snippet .= "        <?php foreach( \$images as \$image ): ?>\n";
            $snippet .= "            <li><img src=\"<?= esc_url(\$image['sizes']['thumbnail']); ?>\" alt=\"<?= esc_attr(\$image['alt']); ?>\" /></li>\n";
            $snippet .= "        <?php endforeach; ?>\n";
            $snippet .= "    </ul>\n";
            $snippet .= "<?php endif; ?>";
            break;

        case 'relationship':
        case 'post_object':
        case 'user':
            $var = ($field['type'] == 'user') ? '$users' : '$posts';
            $item_var = ($field['type'] == 'user') ? '$user' : '$post';
            $snippet = "<?php {$var} = get_field($params); ?>\n";
            $snippet .= "<?php if( {$var} ): ?>\n";
            $snippet .= "    <ul>\n";
            $snippet .= "    <?php foreach( {$var} as {$item_var} ): ?>\n";
            if ($item_var == '$post') $snippet .= "        <?php setup_postdata({$item_var}); ?>\n";
            $snippet .= "        <li><a href=\"<?php the_permalink(); ?>\"><?php the_title(); ?></a></li>\n";
            $snippet .= "    <?php endforeach; ?>\n";
            $snippet .= "    </ul>\n";
            if ($item_var == '$post') $snippet .= "    <?php wp_reset_postdata(); ?>\n";
            $snippet .= "<?php endif; ?>";
            break;
            
        default: // For checkbox, taxonomy, etc.
            $snippet = "<?php \$values = get_field($params); ?>\n";
            $snippet .= "<?php if( \$values ): ?>\n";
            $snippet .= "    <ul>\n";
            $snippet .= "        <?php foreach( \$values as \$value ): ?>\n";
            $snippet .= "            <li><?= is_array(\$value) ? esc_html(\$value['label']) : esc_html(\$value); ?></li>\n";
            $snippet .= "        <?php endforeach; ?>\n";
            $snippet .= "    </ul>\n";
            $snippet .= "<?php endif; ?>";
            break;
    }
    return $snippet;
}


// =========================================================================
//  PAGE 2: FIELD DEBUGGER
// =========================================================================

function ats_render_debugger_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'ACF Field Debugger', 'acf-tools-suite' ); ?></h1>
        <p><?php _e( 'Below is a raw dump of values from <strong>only non-empty</strong> ACF fields. Posts that do not have any filled fields are omitted from the list.', 'acf-tools-suite' ); ?></p>
        <?php
        echo '<h2>' . esc_html__( 'Options Page Fields', 'acf-tools-suite' ) . '</h2>';
        $all_option_fields = get_fields('option');
        $non_empty_option_fields = is_array($all_option_fields) ? array_filter($all_option_fields) : [];
        if ( ! empty($non_empty_option_fields) ) {
            echo '<div class="ats-debugger-output"><pre>' . esc_html(print_r($non_empty_option_fields, true)) . '</pre></div>';
        } else {
            echo '<p>' . esc_html__( 'No filled ACF fields on the options page.', 'acf-tools-suite' ) . '</p>';
        }
        echo '<hr style="margin: 30px 0;">';
        $post_types = get_post_types(['public' => true], 'objects');
        foreach ($post_types as $post_type) {
            $posts = get_posts(['post_type' => $post_type->name, 'posts_per_page' => -1, 'post_status' => 'any']);
            if ($posts) {
                $has_content_for_this_cpt = false;
                foreach ($posts as $p) {
                    $all_fields = get_fields($p->ID);
                    $non_empty_fields = is_array($all_fields) ? array_filter($all_fields) : [];
                    if ( ! empty($non_empty_fields) ) {
                        if (!$has_content_for_this_cpt) {
                            echo '<h2>' . esc_html__( 'Post Type:', 'acf-tools-suite' ) . ' ' . esc_html($post_type->labels->name) . '</h2>';
                            $has_content_for_this_cpt = true;
                        }
                        $edit_link = get_edit_post_link($p->ID);
                        $title = get_the_title($p->ID) ?: __( '(no title)', 'acf-tools-suite' );
                        echo '<h3><a href="' . esc_url($edit_link) . '">' . esc_html($title) . '</a> <small>(ID: ' . $p->ID . ')</small></h3>';
                        echo '<div class="ats-debugger-output"><pre>' . esc_html(print_r($non_empty_fields, true)) . '</pre></div>';
                    }
                }
            }
        }
        ?>
    </div>
    <?php
}


// =========================================================================
//  HELPER FUNCTIONS AND SCRIPTS
// =========================================================================

function ats_is_options_page_group($group) {
    if (empty($group['location'])) return false;
    foreach ($group['location'] as $rules) {
        foreach ($rules as $rule) {
            if (isset($rule['param']) && $rule['param'] === 'options_page') return true;
        }
    }
    return false;
}

function ats_add_admin_styles() {
    $screen = get_current_screen();
    if (strpos($screen->id, 'acf-tools') === false && strpos($screen->id, 'acf-field-debugger') === false) return;
    ?>
    <style>
        .ats-code-block { margin-bottom: 15px; } .ats-code-block:last-child { margin-bottom: 5px; } .ats-code-block p { margin: 0 0 5px; font-weight: 600; }
        .ats-copy-wrapper { display: flex; align-items: flex-start; } .ats-copy-wrapper input[type="text"], .ats-copy-wrapper textarea { flex-grow: 1; font-family: monospace; background-color: #f0f0f1; border: 1px solid #ddd; padding: 5px 8px; }
        .ats-copy-wrapper textarea { min-height: 150px; resize: vertical; white-space: pre; }
        .ats-copy-btn { margin-left: 8px; } .ats-copy-btn.copied { background-color: #2271b1; color: #fff; border-color: #2271b1; }
        .wp-list-table h3 { font-size: 1.2em; margin: 15px 0 10px; } .ats-debugger-output { background: #fdfdfd; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; max-height: 400px; overflow-y: auto; }
        .ats-debugger-output pre { margin: 0; padding: 0; font-size: 13px; }
    </style>
    <?php
}
add_action('admin_head', 'ats_add_admin_styles');

function ats_add_admin_scripts() {
    $screen = get_current_screen();
    if (strpos($screen->id, 'acf-tools') === false && strpos($screen->id, 'acf-field-debugger') === false) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.ats-copy-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const input = this.parentElement.querySelector('input[type="text"], textarea');
                navigator.clipboard.writeText(input.value).then(() => {
                    const originalText = this.dataset.copyText;
                    const copiedText = this.dataset.copiedText;
                    this.textContent = copiedText;
                    this.classList.add('copied');
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.classList.remove('copied');
                    }, 2000);
                });
            });
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'ats_add_admin_scripts');