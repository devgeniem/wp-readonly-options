<?php
/**
 * Plugin Name: WP Readonly Options
 * Description: Plugin which adds forced options through WP_READONLY_OPTIONS constant
 * Version: 1.2.2
 * Plugin URI: https://github.com/devgeniem/wp-must-use-options
 * Author: Onni Hakala / Geniem Oy
 * Author URI: https://github.com/onnimonni
 * License: GPLv2
 */

namespace Geniem\Helper;

class ReadonlyOptions {

    /**
     * Helper text which is set to all readonly element titles by javascript
     * Admin users can see this when they hover over readonly elements
     */
    static $hover_text = '';

    /**
     * Array off all $options. This is used to feed the options to javascript hack
     */
    static $options = [];

    /**
     * Setup hooks, filters and default options
     */
    static function init() {
        // This can be overridden if something else feels better
        self::$hover_text = 'This option is set readonly in ' . basename(__DIR__).'/'.basename(__FILE__);

        // Use javascript hack in admin option pages
        if ( is_admin() && ! defined('WP_READONLY_OPTIONS_NO_JS') ) {
            add_action('admin_enqueue_scripts', [ __CLASS__, 'set_admin_readonly_js' ] );
        }
    }

    /**
     * Helper to collect options from multiple uses of self::set()
     *
     * @param array $options - Options to be added
     */
    static function add_options( $options ) {
        self::$options = array_merge($options,self::$options);
    }

    /**
     * Forces options from WP_READONLY_OPTIONS array to be predetermined
     * This is useful if some plugin doesn't allow defining options in wp-config.php
     *
     * @param $options array - List of forced options
     */
    static function set( array $options ) {

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
            }

            // Add to all options which can be used later on in admin_footer hook
            self::add_options( $options );
        }
    }

    /**
     * Set option input fields as readonly in admin pages so that users won't get confused
     * hooks into: admin_enqueue_scripts
     *
     * @param string $page_name - Admin page name from admin_enqueue_scripts
     */
    static function set_admin_readonly_js( $page_name ) {
        switch ($page_name) {

            // Enable readonly js fixer for all admin options pages
            case 'options-general.php'
            || 'options-writing.php'
            || 'options-reading.php'
            || 'options-discussion.php'
            || 'options-media.php'
            || 'options-permalink.php'
            || 'options.php':

            $hover_text = self::$hover_text;
            $input_element_ids = array_keys( self::$options );

            // Add javascript which turns $elements into readonly
            add_action( 'admin_footer', function() use ( $input_element_ids, $hover_text ) {
                // Show information about this plugin in element mouseover title for easier debugging
                self::print_admin_readonly_js_script( $input_element_ids, $hover_text );
            });

            // On other pages do nothing
            default:
            return;
        }
    }

    /**
     * Outputs <script> tag which sets some elements to readonly state
     *
     * @param array $input_element_ids - List of elements to turn to readonly state
     * @param string $hover_text - Helper text for admin users which they can see when hovering over elements
     */
    static function print_admin_readonly_js_script( $input_element_ids, $hover_text ) {
        ?>
            <script>
                (function() {
                    // Turn these input elements to readOnly to present that their values are forced
                    ['<?php echo implode("','",$input_element_ids); ?>'].forEach(function(elementId) {
                        var el =  document.getElementById(elementId);
                        if ( typeof(el) != 'undefined' && el != null ) {
                            el.readOnly = true;
                            el.title = '<?php echo $hover_text;?>';

                            if ( el.type === 'checkbox' ) {
                                el.onclick = function() {
                                    return false;
                                };
                            }
                        }
                    });
                })();
            </script>
        <?php
    }

}

// Setup hooks
ReadonlyOptions::init();

// Default variable which is always used
if ( defined( 'WP_READONLY_OPTIONS' ) ) {
    if ( is_array( WP_READONLY_OPTIONS ) ) { // Use arrays with php7
        ReadonlyOptions::set( WP_READONLY_OPTIONS );
    } elseif ( is_serialized( WP_READONLY_OPTIONS ) ) { // Use serialized arrays in <php5
        ReadonlyOptions::set( unserialize( WP_READONLY_OPTIONS ) );
    }
}
