<?php   
function sobelz_map_selector_shortcode_function($atts, $fields_name, $prefix_map_id, $sobelz_plugin_prefix) {

    ob_start();
  
    ?>
    <div>
      <?php

	 // Add nonce field
      wp_nonce_field('sobelz_map_nonce', 'sobelz_map_selector_nonce');
	
      woocommerce_form_field('billing_latitude', array(
        'type' => 'hidden',
        'class' => array('hidden-field'),
        'default' => 'none',
      ));
  
      woocommerce_form_field('billing_longitude', array(
        'type' => 'hidden',
        'class' => array('hidden-field'),
        'default' => 'none',
      ));
  
      $input = [];
      $all_options = wp_load_alloptions();
      $pattern1 = '/^' . preg_quote($sobelz_plugin_prefix, '/') . '/';
      $pattern2 = '/^' . preg_quote($sobelz_plugin_prefix, '/') . '.*icon$/';
  
      foreach ($all_options as $option_name => $option_value) {
        $without_prefix = str_replace($sobelz_plugin_prefix . '_', '', $option_name);
  
        // Escaping user input
        $without_prefix_escaped = esc_attr($without_prefix);
  
        if (preg_match($pattern2, $option_name)) {
          $input[$without_prefix_escaped] = esc_url(wp_get_attachment_url($option_value));
        } elseif (preg_match($pattern1, $option_name)) {
          $input[$without_prefix_escaped] = esc_attr($option_value);
        }
      }
  
      $jsonInput = wp_json_encode($input, JSON_PRETTY_PRINT);
      echo '<pre id="data-map" style="display:none">' . wp_kses_post($jsonInput) . '</pre>';
      ?>
      <div id="MapWrapper"></div>
    </div>
  
    <?php
  
    echo ob_get_clean();
  }
  
?>