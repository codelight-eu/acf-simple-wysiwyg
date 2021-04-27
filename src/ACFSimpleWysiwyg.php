<?php

namespace Codelight\ACFSimpleWysiwyg;

if (!defined('WPINC')) {
    die;
}

/**
 * Class ACFSimpleWysiwyg
 * @package Codelight\ACFSimpleWysiwyg
 */
class ACFSimpleWysiwyg extends \acf_field
{
    /**
     * Called by parent constructor
     */
    public function initialize()
    {
        $this->name = 'simple_wysiwyg';
        $this->label = __('Wysiwyg Editor (simple)', 'acf-simple-wysiwyg');
        $this->category = 'content';

        $this->add_filters();
    }

    /**
     * Set up filters to ensure shortcodes are processed all required actions taken,
     * but no <p> tags added automatically.
     */
    public function add_filters()
    {
        // wp-includes/class-wp-embed.php
        if (!empty($GLOBALS['wp_embed'])) {
            add_filter('acf_the_content_simple', array($GLOBALS['wp_embed'], 'run_shortcode'), 8);
            add_filter('acf_the_content_simple', array($GLOBALS['wp_embed'], 'autoembed'), 8);
        }

        // wp-includes/default-filters.php
        add_filter('acf_the_content_simple', 'capital_P_dangit', 11);
        add_filter('acf_the_content_simple', 'wptexturize');
        add_filter('acf_the_content_simple', 'wpautop');
        add_filter('acf_the_content_simple', 'convert_smilies', 20);

        // Removed in 4.4
        if (acf_version_compare('wp', '<', '4.4')) {
            add_filter('acf_the_content_simple', 'convert_chars');
        }
        
        // Added in 5.6
        if (function_exists('wp_filter_content_tags')) {
            add_filter('acf_the_content_simple', 'wp_filter_content_tags');
        // Added in 4.4
        } elseif (function_exists('wp_filter_content_tags')) {
            add_filter('acf_the_content_simple', 'wp_filter_content_tags');
        }

        add_filter('acf_the_content_simple', 'do_shortcode', 11);
    }

    /**
     * Run our custom content filter on output
     *
     * @param $value
     * @param $post_id
     * @param $field
     * @return mixed
     */
    function format_value($value, $post_id, $field)
    {
        // bail early if no value
        if (empty($value)) {
            return $value;
        }

        // apply filters
        $value = apply_filters('acf_the_content_simple', $value);

        // follow the_content function in /wp-includes/post-template.php
        $value = str_replace(']]>', ']]&gt;', $value);

        return $value;
    }

    /**
     * Render field in admin
     *
     * @param $field
     */
    function render_field($field)
    {
        $id = uniqid('simple_wysiwyg');
        add_filter('acf_the_editor_content', 'format_for_editor', 10, 2);
        $button = 'data-wp-editor-id="' . $id . '"';

        // This gives us exactly 3 lines without a scrollbar
        $height = 122;

        ?>
        <div id="wp-<?php echo $id; ?>-wrap"
             class="acf-editor-wrap1 wp-core-ui wp-editor-wrap tmce-active" data-toolbar="full">
            <div id="wp-<?php echo $id; ?>-editor-tools" class="wp-editor-tools hide-if-no-js">
                <?php if (user_can_richedit()): ?>
                    <div class="wp-editor-tabs">
                        <button id="<?php echo $id; ?>-tmce"
                                class="wp-switch-editor switch-tmce" <?php echo $button; ?>
                                type="button"><?php echo __('Visual', 'acf'); ?></button>
                        <button id="<?php echo $id; ?>-html"
                                class="wp-switch-editor switch-html" <?php echo $button; ?>
                                type="button"><?php echo _x('Text', 'Name for the Text editor tab (formerly HTML)', 'acf'); ?></button>
                    </div>
                <?php endif; ?>
            </div>
            <div id="wp-<?php echo $id; ?>-editor-container" class="wp-editor-container">
            <textarea id="<?php echo $id; ?>" class="wp-editor-area" name="<?php echo $field['name']; ?>"
                      <?php if ($height): ?>style="height:<?php echo $height; ?>px;"<?php endif; ?>><?php echo $field['value']; ?></textarea>
            </div>
        </div>
        <?php
    }

    /**
     * Render the JS in admin footer
     */
    function input_admin_footer()
    {
        $json = array();
        $toolbars = [
            'Full' => [
                1 => apply_filters('mce_buttons', [
                    'bold',
                    'italic',
                    'underline',
                    'link',
                    'unlink',
                    'forecolor',
                    'forecolorpicker',
                    'pastetext',
                    'removeformat',
                ], 'acf_content'),
            ]
        ];
        $toolbars = apply_filters( 'acf/fields/simple-wysiwyg/toolbars', $toolbars );

        if (empty($toolbars)) {
            return;
        }

        foreach ($toolbars as $label => $rows) {
            $label = sanitize_title($label);
            $label = str_replace('-', '_', $label);
            $json[$label] = array();
            if (!empty($rows)) {
                foreach ($rows as $i => $row) {
                    $json[$label][$i] = implode(',', $row);
                }
            }
        }
        ?>
        <script type="text/javascript" src="<?php echo ACF_SIMPLE_WYSIWYG_URL . '/assets/acf-simple-wysiwyg.js' ?>"></script>
        <script>acf.fields.simplewysiwyg.toolbars = <?php echo json_encode($json); ?>;</script>
        <?php
    }
}
