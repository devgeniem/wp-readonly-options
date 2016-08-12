<?php
/**
 * Plugin Name: Must Use Options
 * Description: Plugin which adds forced options through WP_MUST_USE_OPTIONS constant
 * Version: 1.0.0
 * Plugin URI: https://github.com/devgeniem/wp-must-use-options
 * Author: Onni Hakala / Geniem Oy
 * Author URI: https://github.com/onnimonni
 * License: MIT License
 */

namespace Geniem\helper;

class MustUseOptions {

  /**
   * Forces options from WP_MUST_USE_OPTIONS array to be predetermined
   * This is useful if some plugin doesn't allow defining options in wp-config.php
   *
   * @param $options array - List of forced options
   */
  static function set( $options ) {

    if ( is_array( $options ) ) {

        // Force mentioned options with filters
        foreach ( $options as $must_use_option => $must_use_value ) {

            // Always return this value for the option
            add_filter( "pre_option_{$must_use_option}", function() use ( $must_use_value ) {
                return $must_use_value;
            });

            // Always deny saving this value to the DB
            // wp-includes/option.php:280-291 stops updating this option if it's same
            add_filter( "pre_update_option_{$must_use_option}", function() use ( $must_use_value ) {
                return $must_use_value;
            });

            add_filter( "pre_update_option_{$must_use_option}", function() use ( $must_use_value ) {
                return $must_use_value;
            });
        }

        if (is_admin()) {
            self::admin_readonly_js( array_keys( $options ) );
        }

    }
  }

  /**
   * Set option input fields as readonly in admin pages so that users won't get confused
   *
   * @param array $input_element_ids - List of elements to set as readonly
   */
  static private function admin_readonly_js( $input_element_ids ) {
    add_action('admin_enqueue_scripts', function( $page_name ) use ( $input_element_ids ) {
        switch ($page_name) {

            // Enable readonly js fixer for all admin options pages
            case 'options-general.php'
            || 'options-writing.php'
            || 'options-reading.php'
            || 'options-discussion.php'
            || 'options-media.php'
            || 'options-permalink.php'
            || 'options.php':

            // Add javascript which turns $elements into readonly
            add_action( 'admin_footer', function() use ( $input_element_ids ) {
                // Show information about this plugin in element mouseover title for easier debugging
                $plugin_file = basename(__DIR__).'/'.basename(__FILE__);
                ?>
                <script>
                    (function() {
                        // Turn these input elements to readOnly to present that their values are forced
                        ['<?php echo implode($input_element_ids,"','"); ?>'].forEach(function(elementId) {
                            var element =  document.getElementById(elementId);
                            if (typeof(element) != 'undefined' && element != null) {
                              element.readOnly = true;
                              element.title = 'This option is forced in <?php echo $plugin_file;?>';
                            }
                        });
                    })();
                </script>
                <?php
            });

            // On other pages do nothing
            default:
            return;
        }
    });
  }

}

// Default variable which is always used
if ( defined( 'WP_MUST_USE_OPTIONS' ) ) {
    MustUseOptions::set( WP_MUST_USE_OPTIONS );
}
