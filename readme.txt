=== Sobelz Map Selector ===
Contributors: sobelz
Tags: map, location, WooCommerce, checkout, customization
Requires at least: 5.2
Tested up to: 6.4
Requires PHP: 5.6
Stable tag: 1.0.0
License: GPLv2 or later


== Description ==

The Sobelz Map plugin allows users to select locations on a map and integrates with the WooCommerce checkout page.
 By selecting a location by the user, the fields related to the location are filled.


= Features =

- Accurate location selection on the map.
- Integration with WooCommerce checkout fields.
- The appearance of the map can be adjusted:
  - Customize search box and button text.
  - Change map and button color.
  - Change map icons.
  - Personalize field IDs for a tailored experience.
  - Show or hide the map on the payment page.


== Installation ==

1. Upload the `sobelz-map` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the 'Map Selector Settings' page in the WordPress admin to configure the plugin.

Note: If the map is not displayed on the checkout page for you after installing the plugin, please replace the existing content on the checkout page with the shortcode [woocommerce_checkout].


== Configuration ==

1. Navigate to 'Map Selector' in the WordPress admin menu.
2. Adjust the plugin settings, including map color, submit button color, and other options.
3. Customize the map appearance and behavior to suit your needs.

== Support ==

For support and inquiries, please contact us at [sobelzcom@gmail.com](mailto:sobelzcom@gmail.com).

== License ==

Sobelz Map Plugin is licensed under the GPLv2 or later license.

== Contributing ==

Contributions are welcome! Feel free to submit issues or pull requests.

== Frequently Asked Questions ==

*Q: Is this plugin compatible with the latest version of WordPress?*
A: Yes, the plugin is tested and compatible with WordPress version 6.4.


== Changelog ==

= 1.0.0 =
* Initial release.


== Upgrade Notice ==

No specific upgrade notices at the moment.


== Screenshots ==

1. Plugin display after installation screenshot-1.png.
2. Plugin Settings Page screenshot-2.png.
3. Plugin Functionality Example screenshot-3.png.
4. Customizing Plugin Settings screenshot-4.png.


== Third Party Services ==

The map display and functionality in this plugin are powered by a compressed React file that utilizes the Nominatim API and Tile URL from OpenStreetMap.

### Nominatim API

The Nominatim API is employed to retrieve geographic information about places, including latitude and longitude. The plugin uses the following URL to make requests to the Nominatim API:

- Nominatim API Endpoint: [Nominatim API](https://nominatim.openstreetmap.org/search?format=json&accept-language=fa)

This URL specifies that the response should be in JSON format, and the language is set to Persian (fa).

The response from the Nominatim API is utilized to initialize the map.

### Default Map Location

The default location of the map is set to Iran. The tile images for the map are retrieved using the following URL:

- OpenStreetMap Tile URL: [OpenStreetMap Tiles](https://tile.openstreetmap.org/{z}/{x}/{y}.png)

Here, the {z} parameter specifies the zoom level, {x} specifies the x-coordinate of the map center, and {y} specifies the y-coordinate.

### User Interaction

Users can resize the map by dragging the edges.

## External Service Documentation

Please note that this plugin relies on the Nominatim API, Tile URL and OpenStreetMap services. It is important for users to be aware of the third-party service usage.

- [OpenStreetMap Terms of Use](https://www.openstreetmap.org/copyright)
- [Tile URL Usage Policy](https://operations.osmfoundation.org/policies/tiles/)
- [Nominatim API Usage Policy](https://operations.osmfoundation.org/policies/nominatim/)

By using this plugin, you agree to comply with the terms of use and policies of the external services mentioned above.

## Source Code

- [Github](https://github.com/sobelz/Soblez_map_for_woocommerce)



