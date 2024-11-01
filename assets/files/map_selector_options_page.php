<?php

function sobelz_map_selector_options_page($fields_name, $prefix_map_id, $sobelz_plugin_prefix)
{
    $language = get_locale();
    ?>
    <div class="wrap">
        <h2><b><?php echo esc_html__('Sobelz Map Settings', 'sobelz-map-selector'); ?></b></h2>
        <form method="post" action="options.php">
            <?php settings_fields('map_selector_options'); ?>
            <?php do_settings_sections('map_selector_options'); ?>

            <h3><b><?php echo esc_html__('Fields Id', 'sobelz-map-selector'); ?></b></h3>

            <table class="form-table">
                <tr valign="top">
                    <?php foreach ($fields_name as $name) : ?>
                        <?php 
                        $input_name = sanitize_key($sobelz_plugin_prefix . '_' . $name);
                        $default_value = $prefix_map_id . $name;
                        ?>
                        <td>
                            <label for="<?php echo esc_attr($input_name); ?>"><?php echo esc_html(strtoupper($name) . " ID:", 'sobelz-map-selector'); ?></label>
                            <input type="text" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr(get_option($input_name, $default_value)); ?>">

                        </td>
                    <?php endforeach; ?>
                </tr>
            </table>

            <hr>

            <h3><b><?php echo esc_html__('Default Settings', 'sobelz-map-selector'); ?></b></h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Map Color', 'sobelz-map-selector'); ?>:</th>
                    <td><input style="width:25%; height: 30px;" type="color" name="sobelz_map_selector_map_color" value="<?php echo esc_attr(get_option($sobelz_plugin_prefix.'_map_color', '#202380')); ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Submit Button Color', 'sobelz-map-selector'); ?>:</th>
                    <td><input style="width:25%; height: 30px;" type="color" name="sobelz_map_selector_submit_button_color" value="<?php echo esc_attr(get_option($sobelz_plugin_prefix.'_submit_button_color', '#00E099')); ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Display on Checkout Page', 'sobelz-map-selector'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="sobelz_map_selector_display_checkout" value="1" <?php checked(get_option($sobelz_plugin_prefix.'_display_checkout', true), true); ?>>
                            <?php echo esc_html__('Enable', 'sobelz-map-selector'); ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Search Box Text', 'sobelz-map-selector'); ?>:</th>
                    <td><input style="width:25%; height: 30px;" type="text" name="sobelz_map_selector_search_placeholder" value="<?php echo esc_html( get_option('sobelz_map_selector_search_placeholder', 'جست جو...') ); ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Submit Button Text', 'sobelz-map-selector'); ?>:</th>
                    <td><input style="width:25%; height: 30px;" type="text" name="sobelz_map_selector_submit_button_text" value="<?php echo esc_html( get_option('sobelz_map_selector_submit_button_text', 'تایید | تبدیل به آدرس') ); ?>"></td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
        <hr>

        <h3><b><?php echo esc_html__('Icons', 'sobelz-map-selector'); ?></b></h3>

        <?php
        $all_options = wp_load_alloptions();
        $pattern = '/^'.$sobelz_plugin_prefix.'.*icon$/';
        foreach ($all_options as $option_name => $option_value) {
            if (preg_match($pattern, $option_name)) {
                $without_prefix = str_replace($sobelz_plugin_prefix, '', $option_name);
                $replaced = ucwords(str_replace('_', ' ', $without_prefix));
                $replaced = esc_attr($replaced);
                sobelz_map_render_image_selector($option_name, $replaced);
            }
        }
        ?>
    </div>
    <?php
}


function sobelz_map_render_image_selector($option_name, $label) {
    if (!isset($_POST['submit']) && isset($_POST[$option_name])) {
        check_admin_referer('sobelz_map_nonce', 'nonce');

        update_option($option_name, sanitize_text_field($_POST[$option_name]));
        echo wp_json_encode(['status' => 'success']);
        exit;
    }

    wp_enqueue_media();
    ?>
    <div class='image-preview-wrapper'>
        <div>
            <h3><b><?php echo esc_html($label); ?></b></h3>
        </div>
        <img id='<?php echo esc_attr($option_name); ?>_preview' src='<?php echo esc_url(wp_get_attachment_url(get_option($option_name))); ?>' width='100'>
    </div>
    <input id="upload_<?php echo esc_html($option_name); ?>_button" type="button" class="button" value="<?php esc_html_e('Upload image'); ?>" />

    <script type='text/javascript'>
    jQuery(document).ready(function($) {
        $('#upload_<?php echo esc_html($option_name); ?>_button').on('click', function(event) {
            event.preventDefault();

            var file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select an image to upload',
                button: {
                    text: 'Use this image',
                },
                multiple: false
            });

            file_frame.on('select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();
                $('#' + '<?php echo esc_html($option_name); ?>_preview').attr('src', attachment.url).css('width', 'auto');
                $('#' + '<?php echo esc_html($option_name); ?>').val(attachment.id);

                // Perform AJAX call to update the option value
                var data = {
                    action: 'update_option_value',
                    submit: 1,
                    option_name: '<?php echo esc_html($option_name); ?>',
                    '<?php echo esc_html($option_name); ?>': attachment.id,
                    nonce: '<?php echo esc_html( wp_create_nonce('sobelz_map_nonce') ); ?>'
                };
                $.post(ajaxurl, data, function(response) {
                    console.log('Option updated successfully!');
                });
            });
            file_frame.open();
        });
    });
    </script>
    <?php
}

?>