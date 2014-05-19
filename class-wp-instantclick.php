<?php

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

if ( !class_exists( 'WP_InstantClick' ) ):

    class WP_InstantClick
    {
        const VERSION = '1.0.1';
        const SCRIPT_HANDLE = 'instantclick';

        private static $no_instant = array( self::SCRIPT_HANDLE );

        private static $_enabled  = false;
        private static $_preload_method = '';

        public static function enable() {
            if ( self::is_enabled() )
                return;

            self::_add_action_or_do( 'init', array( __CLASS__, '_register_scripts' ), 1 );

            if ( !is_admin() )
                self::_add_action_or_do( 'wp_enqueue_scripts', array( __CLASS__, '_enqueue_scripts' ), 1 );

            self::$_enabled = true;
        }

            private static function _add_action_or_do( $hook, $callback, $priority = 10 ) {
                if ( did_action( $hook ) || doing_action( $hook ) )
                    call_user_func( $callback );
                else
                    add_action( $hook, $callback, $priority );
            }

        public static function is_enabled() {
            return (bool) self::$_enabled;
        }

        public static function no_instant( $handle ) {
            if ( !in_array( $handle, self::$no_instant ) )
                self::$no_instant[] = $handle;
        }

        public static function preload_on_mousedown() {
            self::$_preload_method = "'mousedown'";
        }

        public static function preload_on_hover( $delay ) {
            if ( is_numeric( $delay ) && $delay > 0 )
                self::$_preload_method = (int) $delay;
            else
                self::$_preload_method = 0;
        }

        //
        // HOOKABLES
        //

        public static function _register_scripts() {
            $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
            wp_register_script( self::SCRIPT_HANDLE, self::get_url() . "js/instantclick$min.js", array(), '3.0.1', true );
        }

            /**
             * Class should be able to be instantiated from a theme or plugin,
             * so we need to find out the url to the current folder in a
             * roundabout way.
             */
            public static function get_url() {
                // get and normalize framework dirname
                $dirname = str_replace( '\\' ,'/', dirname( __FILE__ ) ); // standardize slash
                $dirname = preg_replace( '|/+|', '/', $dirname );       // normalize duplicate slash

                // get and normalize WP content directory
                $wp_content_dir = str_replace( '\\', '/', WP_CONTENT_DIR );  // standardize slash

                // build relative url
                $relative_url = str_replace( $wp_content_dir, "", $dirname );

                // finally base url
                return trailingslashit( content_url() . $relative_url );
            }

        public static function _enqueue_scripts() {
            wp_enqueue_script( self::SCRIPT_HANDLE );
            add_filter( 'script_loader_src', array( __CLASS__, '_script_loader' ), 10, 2 );
        }

        public static function _script_loader( $src, $handle ) {
            if ( in_array( $handle, self::$no_instant ) ) {
                ?><script src='<?php echo $src ?>' data-no-instant></script>
                <?php

                $src = false;
            }

            if ( self::SCRIPT_HANDLE === $handle ) {
                add_action( 'wp_footer', array( __CLASS__, '_output_script_init' ), 9999 );
            }

            return $src;
        }

        public static function _output_script_init() {
            ?>

            <script data-no-instant>
                <?php do_action( 'instantclick_before_init' ); ?>

                InstantClick.init(<?php echo self::$_preload_method ?>);
                <?php do_action( 'instantclick_after_init' ); ?>

            </script>
            <?php
        }
    }

endif;