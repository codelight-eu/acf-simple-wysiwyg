<?php

// exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// check if class already exists
if (!class_exists('acf_field_cl_simple_wysiwyg')) :


    class acf_field_cl_simple_wysiwyg extends acf_field
    {
        function __construct($settings)
        {
            $this->name = 'cl_simple_wysiwyg';
            $this->label = __('Simple wysiwyg', 'acf-cl_simple_wysiwyg');
            $this->category = 'basic';
            $this->l10n = array(
                'error' => __('Error! Please enter a higher value', 'acf-cl_simple_wysiwyg'),
            );
            $this->settings = $settings;

            parent::__construct();

        }

        function render_field($field)
        {

            $id = uniqid('acf-cl-simple-wysiwyg');
            $button = '';
            $height = 100;

            ?>
            <div id="wp-<?php echo $id; ?>-wrap"
                 class="acf-editor-wrap1 wp-core-ui wp-editor-wrap tmce-active" data-toolbar="full">
                <div id="wp-<?php echo $id; ?>-editor-tools" class="wp-editor-tools hide-if-no-js">
                </div>
                <div id="wp-<?php echo $id; ?>-editor-container" class="wp-editor-container">
            <textarea id="<?php echo $id; ?>" class="wp-editor-area" name="<?php echo $field['name']; ?>"
                      <?php if ($height): ?>style="height:<?php echo $height; ?>px;"<?php endif; ?>><?php echo $field['value']; ?></textarea>
                </div>
            </div>
            <?php
        }

        function get_toolbars()
        {
            // vars
            $toolbars = array();
            $editor_id = 'acf_content';

            // Full
            $toolbars['Full'] = array(

                1 => apply_filters('mce_buttons', array(
                    'bold',
                    'italic',
                    'link',
                    'unlink',
                    'removeformat',
                    'forecolor',
                    'forecolorpicker',
                    'pastetext'
                ), $editor_id),
            );
            // return
            return $toolbars;
        }

        function input_admin_footer()
        {

// vars
            $json = array();
            $toolbars = $this->get_toolbars();

// bail ealry if no toolbars
            if (empty($toolbars)) {
                return;
            }


// loop through toolbars
            foreach ($toolbars as $label => $rows) {
                // vars
                $label = sanitize_title($label);
                $label = str_replace('-', '_', $label);
                // append to $json
                $json[$label] = array();
                // convert to strings
                if (!empty($rows)) {
                    foreach ($rows as $i => $row) {
                        $json[$label][$i] = implode(',', $row);
                    }
                }
            }
            ?>
            <script type="text/javascript" src="<?= plugins_url('acf-cl_simple_wysiwyg-v5.js', __FILE__); ?>"></script>
            <script>acf.fields.wysiwygsimple.toolbars = <?php echo json_encode($json); ?>;</script>
            <?php
        }
    }

// initialize
    new acf_field_cl_simple_wysiwyg($this->settings);

// class_exists check
endif;
?>

