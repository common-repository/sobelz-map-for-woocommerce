<?php
/*
Plugin Name:  Sobelz Map Selector
Plugin URI:   https://sobelz.com/
Description:  The Sobelz map plugin allows users to use the map to select a location instead of entering the location manually.
Short Description: A Map Plugin for select Location.
Version:      1.0.0
Author:       Sobelz
License:      GPLv2 or later
Text Domain:  sobelz-map-selector
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

include_once('assets/files/map_selector_sc_function.php');
include_once('assets/files/map_selector_options_page.php');
class sobelz_map_SobelzMapPlugin 
{
    private $prefix_map_id;
    private $sobelz_plugin_prefix;
    private $fields_name = ['country', 'state', 'city', 'address_1','address_2', 'latitude', 'longitude'];

    public function __construct() 
    {
        $this->prefix_map_id = "#billing_";
        $this->sobelz_plugin_prefix = "sobelz_map_selector";
        add_action('admin_init', array($this, 'sobelz_map_selector_register_settings'));
        register_activation_hook( __FILE__, array($this, 'sobelz_map_plugin_activation'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_map_selector_style'), 22345);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_map_selector_script'), 15);
        add_filter('script_loader_tag', array($this, 'moduleTypeScripts'), 15, 2);
        add_action('admin_menu', array($this, 'sobelz_map_selector_options_menu'));
        add_action('wp_ajax_update_option_value', array($this, 'update_option_value_callback'));
        add_filter('woocommerce_checkout_fields',array($this,  'change_billing_fields_type'), 15, 2);
        add_action('woocommerce_form_field_text', array($this, 'add_map_on_checkout_fields'), 15, 2);
        add_shortcode('sobelz_map_selector_shortcode', array($this, 'sobelz_map_selector_shortcode_function'));
		add_action('woocommerce_checkout_update_order_meta', array($this, 'save_lat_long_to_order_meta'));
        add_action('plugins_loaded', array($this, 'load_sobelz_map_selector_textdomain'));
        register_deactivation_hook(__FILE__, array($this, 'sobelz_map_plugin_deactivation'));
    }
    
    public function sobelz_map_plugin_activation() 
    {
            
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('This plugin requires WooCommerce. Please install and activate WooCommerce to use this plugin.');
        }
        
        function sobelz_map_allow_svg_upload( $mimes ) {
            $mimes['svg'] = 'image/svg+xml';
            return $mimes;
        }
        add_filter( 'upload_mimes', 'sobelz_map_allow_svg_upload' );
    
        $images_directory = plugin_dir_path(__FILE__). 'assets/images/';
        if (is_dir($images_directory)) {
            $image_files = glob($images_directory . '*.{jpg,jpeg,png,gif,svg}', GLOB_BRACE);
    
            foreach ($image_files as $image_file) {
                $image_data = file_get_contents($image_file);
                $filename = basename($image_file); 
    
                $existing_attachment = $this->sobelz_get_attachment_id_by_filename($filename);
                $image_name_without_extension = pathinfo($filename, PATHINFO_FILENAME);
                
                if (!$existing_attachment) {
                    $image_extension = pathinfo($filename, PATHINFO_EXTENSION);
    
                    $upload = wp_upload_bits($filename, null, $image_data);
    
                    if (!$upload['error']) {
                        $wp_filetype = wp_check_filetype($filename, null);
                        $attachment = array(
                            'post_mime_type' => $wp_filetype['type'],
                            'post_title' => sanitize_file_name($filename),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );
    
                        $attach_id = wp_insert_attachment($attachment, $upload['file']);
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                        update_option($image_name_without_extension.'_icon', $attach_id);
                    }
                } else {
                    update_option($image_name_without_extension.'_icon', $existing_attachment);
                }
            }
        } else {
            wp_die('Directory not found');
        }
    }
    
    public function sobelz_get_attachment_id_by_filename($filename) 
    {
        global $wpdb;
        $filename = strtolower($filename); 
        $filename = str_replace('.','-',$filename);
        $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND post_name = %s", $filename));
        return $attachment_id;
    }
    
    public function sobelz_map_plugin_deactivation()
    {
        $all_options = wp_load_alloptions();
    
        foreach ($all_options as $option_name => $option_value) {
            if (strpos($option_name, $this->sobelz_plugin_prefix) === 0) {
                delete_option($option_name);
            }
        }
    }

    public function enqueue_map_selector_style()
    {
        wp_enqueue_style(
            'sobelz-map-selector-css',
            plugin_dir_url(__FILE__) . 'css/style.css',
        );
    }
    
    public function enqueue_map_selector_script() 
    {
        wp_enqueue_script(
            'sobelz-map-selector-js',
            plugin_dir_url(__FILE__) . 'js/script.js', 
            array(),
            '1.0.1',
            true
        );

        wp_script_add_data('sobelz-map-selector-js', 'type', 'module');
    }

    public function moduleTypeScripts($tag, $handle) 
    {
        $tyype = wp_scripts()->get_data($handle, 'type');
        if ($tyype) {
            $tag = str_replace('src', 'type="' . esc_attr($tyype) . '" src', $tag);
        }
        return $tag;
    }

    public function sobelz_map_selector_options_menu() 
    {
        add_menu_page(
            'Sobelz Map Settings', 
            esc_html__('Sobelz Map', 'sobelz-map-selector'),
            'manage_options', 
            'sobelz-map-selector-settings', 
            array($this, 'sobelz_map_selector_options_page'), 
            'dashicons-location'
        );
    }

    public function sobelz_map_selector_register_settings() 
    {
        foreach ($this->fields_name as $field_name) {
            $option_name = $this->sobelz_plugin_prefix . '_' . $field_name;
            register_setting('map_selector_options', $option_name);
          
            $default_value = "#billing_" . $field_name;
          
            $current_value = get_option($option_name);
          
            if (empty($current_value) && empty(get_option(str_replace($this->sobelz_plugin_prefix . "_", "", $option_name)))) {
              update_option($option_name, $default_value);
            }
        } 

        register_setting(
            'map_selector_options', $this->sobelz_plugin_prefix.'_search_placeholder');
    
        register_setting(
            'map_selector_options', $this->sobelz_plugin_prefix.'_submit_button_text'); 
        
        register_setting(
            'map_selector_options', $this->sobelz_plugin_prefix.'_map_color');
    
        register_setting(
            'map_selector_options', $this->sobelz_plugin_prefix.'_submit_button_color');
    

        register_setting(
            'map_selector_options', $this->sobelz_plugin_prefix.'_display_checkout',
            array(
                'type' => 'boolean',
                'default' => false,
                'sanitize_callback' => 'sanitize_text_field'
            )
        );
    }

    public function sobelz_map_selector_options_page() 
    {
        return sobelz_map_selector_options_page($this->fields_name, $this->prefix_map_id, $this->sobelz_plugin_prefix );
    }

    //images
    public function update_option_value_callback() 
    {
        // 1. بررسی صحت nonce
        if (!check_ajax_referer('sobelz_map_nonce', 'nonce')) {
          wp_send_json_error(['message' => 'Invalid nonce']);
          exit;
        }
      
        if (!isset($_POST['submit'], $_POST['option_name'])) {
          wp_send_json_error(['message' => 'Missing data']);
          exit;
        }
      
        $option_name = sanitize_key($_POST['option_name']);
      
        $option_value = sanitize_text_field($_POST[$option_name]);
      
        update_option($option_name, $option_value);
        wp_send_json_success(['message' => 'Option updated successfully']);
      
    }
    
    public function sobelz_map_selector_shortcode_function($atts) 
    {
        return sobelz_map_selector_shortcode_function($atts,$this->fields_name, $this->prefix_map_id, $this->sobelz_plugin_prefix );
    }

	public function save_lat_long_to_order_meta($order_id) 
    {
        if (!isset($_POST['sobelz_map_selector_nonce'])) {
            wp_send_json_error(['message' => 'Missing security nonce.']);
            return;
        }

        if (!wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sobelz_map_selector_nonce'] ) ), 'woocommerce-process_checkout') && !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sobelz_map_selector_nonce'] ) ), 'sobelz_map_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            return;
        }

        $latitude = sanitize_text_field($_POST['billing_latitude']);
        $longitude = sanitize_text_field($_POST['billing_longitude']);

        if (!empty($latitude) && !preg_match('/^-?\d+(\.\d+)?$/', $latitude)) {
            wp_send_json_error(['message' => 'Invalid latitude format.']);
            return;
        }

        if (!empty($longitude) && !preg_match('/^-?\d+(\.\d+)?$/', $longitude)) {
            wp_send_json_error(['message' => 'Invalid longitude format.']);
            return;
        }

        if (!empty($latitude)) {
            update_post_meta($order_id, 'billing_latitude', $latitude);
        }

        if (!empty($longitude)) {
            update_post_meta($order_id, 'billing_longitude', $longitude);
        }
    }

    public function display_map_selector_on_checkout() 
    {
        $display_on_checkout = get_option('sobelz_map_selector_display_checkout', true);
        if ($display_on_checkout) {
            return do_shortcode('[sobelz_map_selector_shortcode]');
        }
    }

    public function add_map_on_checkout_fields( $field, $key ) 
    {
        if ( is_checkout() && $key == 'billing_address_1' ) {
            do_shortcode('[sobelz_map_selector_shortcode]');
        }
        return $field;
    }


    public function change_billing_fields_type($fields) 
    {
        $fields['billing']['billing_country']['type'] = 'text';
        $fields['billing']['billing_state']['type'] = 'text';
        $fields['billing']['billing_city']['type'] = 'text';

        return $fields;
    }

    public function load_sobelz_map_selector_textdomain() 
    {
        load_plugin_textdomain('sobelz-map-selector', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

}

$sobelz_map_plugin = new sobelz_map_SobelzMapPlugin();