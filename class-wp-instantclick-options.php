<?php
// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class WP_InstantClick_Options
{

	const SLUG = 'instantclick';

    private $settings = array();

    public static function enable() {
        self::get_instance();
    }

    public static function get_instance() {
        static $instance = false;

        if ( !$instance ) {
            $class = get_class();
            $instance = new $class();
        }

        return $instance;
    }

	protected function __construct() {
        // Add the admin page and register the settings
		add_action( 'admin_menu', array( &$this, 'add_options_page' ) );
        add_action( 'admin_init', array( &$this, 'register_settings' ) );
        add_action( 'init', array( &$this, 'load_settings' ) );

        // Hook into InstantClick
        add_action( 'instantclick_before_init', array( &$this, 'output_script_before_init' ) );
        add_action( 'instantclick_after_init', array( &$this, 'output_script_after_init' ) );
        add_action( 'wp_enqueue_scripts', array( &$this, 'set_preload_method' ) );
        add_action( 'wp_enqueue_scripts', array( &$this, 'set_no_instant_scripts' ) );

        // Maybe remove conflicting CodeMirror script from DebugBar
        add_action( 'plugins_loaded', array( &$this, 'remove_debug_bar_codemirror' ) );
	}

    public function output_script_before_init() {
        $this->_output_script( 'before_init');
    }

    public function output_script_after_init() {
        $this->_output_script( 'after_init');
    }

        private function _output_script( $name ) {
            if ( !empty ( $this->settings[$name] ) )
                echo $this->_format_script_output( $this->settings[$name] );
        }

        /**
         * Indents scripts to match plugin script tag indentation
         */
        private function _format_script_output( $script ) {
            return preg_replace( "/(\r\n|\r|\n)/", "$0                ", $script ) . "\n";
        }

    public function set_preload_method() {
        if ( is_numeric( $this->settings['preload_on'] ) )
            WP_InstantClick::preload_on_hover( (int) $this->settings['preload_on'] );

        elseif ( 'mousedown' === $this->settings['preload_on'] )
            WP_InstantClick::preload_on_mousedown();

    }

    public function set_no_instant_scripts() {
        foreach ( $this->settings['no_instant_scripts'] as $handle ) {
            WP_InstantClick::no_instant( $handle );
        }
    }


    //
    // ADMIN PAGE
    //

    public function register_settings() {
        register_setting( self::SLUG, self::SLUG, array( &$this, 'sanitize_settings' ) );

        // General Settings
		add_settings_section( 'section_general', 'General Settings', array( &$this, 'section_general_desc' ), self::SLUG );

		add_settings_field(
            'preload_on',
            sprintf( '%s <p class="description">%s</p>',
                __( 'Preload On', 'instantclick'),
                __( "Set when InstantClick should start preloading the next link.", 'instantclick' )
            ),
            array( &$this, 'field_preload_on' ),
            self::SLUG,
            'section_general'
        );

		add_settings_field(
            'no_instant_scripts',
            sprintf( '%s <p class="description">%s</p>',
                __( 'No-Instant Scripts', 'instantclick'),
                __( "List the handles of scripts you don't want reloaded when InstantClick loads a page. Put each handle on a new line.", 'instantclick' )
            ),
            array( &$this, 'field_no_instant_scripts' ),
            self::SLUG,
            'section_general'
        );

        // Script Settings
		add_settings_section( 'section_scripts', __( 'Custom JavaScript', 'instantclick' ), array( &$this, 'section_scripts_desc' ), self::SLUG );

		add_settings_field(
            'before_script_init',
            __( 'Before Script Init', 'instantclick' ),
            array( &$this, 'field_script' ),
            self::SLUG,
            'section_scripts',
            array(
                'field' => 'before_init',
                'label_for' => 'instantclick-script-before_init'
            )
        );

		add_settings_field(
            'after_script_init',
            __( 'After Script Init', 'instantclick' ),
            array( &$this, 'field_script' ),
            self::SLUG,
            'section_scripts',
            array(
                'field' => 'after_init',
                'label_for' => 'instantclick-script-after_init'
            )
        );
    }

    public function load_settings() {
        $this->settings = shortcode_atts( apply_filters( 'instantclick_defaults', array(
            'before_init' => '',
            'after_init'  => '',
            'preload_on'  => 'hover',
            'no_instant_scripts' => array()
        ) ), (array) get_option( self::SLUG ) );
    }

    public function sanitize_settings( $settings ) {

        $allowed_preload_values = array( 'hover', '50', '100', 'mousedown' );

        if ( is_array( $settings['no_instant_scripts'] ) )
            $settings['no_instant_scripts'] = implode( ',', $settings['no_instant_scripts'] );

        return apply_filters( 'instantclick_sanitize_settings', array(
            'before_init' => trim( (string) $settings['before_init'] ),
            'after_init'  => trim( (string) $settings['after_init'] ),
            'preload_on'  => in_array( $settings['preload_on'], $allowed_preload_values ) ? $settings['preload_on'] : 'hover',
            'no_instant_scripts' => array_map( 'trim', explode( ",", str_replace( "\n", ',', $settings['no_instant_scripts'] ) ) )
        ), $settings );
    }

    public function section_general_desc() {
        echo "<p>" . __( 'InstantClick will dramatically speed up your website by starting to load the next page when a visitor hovers over the next link. InstantClick is now loaded on all your pages. Use the settings below to fine-tune the behaviour of InstantClick.', 'instantclick' ) . "</p>";
    }

    public function section_scripts_desc() {
        echo "<p>" . __( 'Sometimes you will need some extra JS to work with InstantClick. Use the editor below to add scripts before or after InstantClick initialization.', 'instantclick' ) . "</p>";
        echo "<p>" . sprintf(
            __( 'For more information on getting other scripts to play nice with InstantClick, see the %s.' ),
            sprintf( '<a href="http://instantclick.io/download" target="_blank">%s</a>', __( 'InstantClick documentation', 'instantclick' ) )
        ) . "</p>";

        // Hackish way of hiding the before and after fields only if JS is enabled
        echo '<div class="hide-next-form-table-if-js"></div>';
    }

    public function field_script( $args = array() ) {
        $field = $args['field'];
        $value = $this->settings[$field];
		?>

		<textarea name="<?php echo self::SLUG ?>[<?php echo $field ?>]" id="instantclick-script-<?php echo $field ?>" /><?php echo $value; ?></textarea>
		<?php
	}

    public function field_preload_on() {
        $current = $this->settings['preload_on'];

        $options = array(
            'hover'     => __( 'Hover (default)', 'instantclick' ),
            '50'        => __( 'Hover, start after 50ms', 'instantclick' ),
            '100'       => __( 'Hover, start after 100ms', 'instantclick' ),
            'mousedown' => __( 'Mousedown', 'instantclick' )
        );
        foreach( $options as $value => $label ) {
            $id = "instantclick-preload_on-$value";
            ?>

            <label for="<?php echo $id ?>" title="<?php echo esc_attr( $label ) ?>">
                <input type="radio" id="<?php echo $id ?>" name="<?php echo self::SLUG ?>[preload_on]" value="<?php echo $value; ?>"<?php checked( $value, $current ) ?>/>
                <?php echo $label ?>
            </label><br />
            <?php
        }
    }

    public function field_no_instant_scripts() {
        $scripts = $this->settings['no_instant_scripts'];

        if ( !is_array( $scripts ) )
            $scripts = array();

        ?>
            <textarea name="<?php echo self::SLUG ?>[no_instant_scripts]" class="instantclick-no-instant-scripts"><?php
                echo implode( "\n", $scripts );
                ?></textarea>
        <?php
    }

	public function add_options_page() {
		add_options_page(
            __( 'InstantClick Settings', 'instantclick' ),
            __( 'InstantClick', 'instantclick' ),
            'manage_options',
            self::SLUG,
            array( &$this, 'output_options_page' )
        );

        add_action( 'admin_print_scripts-settings_page_' . self::SLUG, array( &$this, 'enqueue_scripts' ) );
        add_action( 'admin_print_styles-settings_page_' . self::SLUG, array( &$this, 'enqueue_styles' ) );

	}

    public function enqueue_styles() {
        wp_enqueue_style( 'instantclick-admin', WP_InstantClick::get_url() . "css/admin.css", array(), WP_InstantClick::VERSION );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'codemirror', WP_InstantClick::get_url() . "js/codemirror-compressed.js", array(), '4.1', true );
        wp_enqueue_script( 'instantclick-admin', WP_InstantClick::get_url() . "js/instantclick-admin.js", array(), WP_InstantClick::VERSION, true );
    }

	public function output_options_page () {
		?>

        <div class="wrap">
            <h2><?php _e( 'InstantClick Settings', 'instantclick' ); ?></h2>

            <form method="post" action="options.php" id="instantclick-settings">
                <?php wp_nonce_field( 'update-options' ); ?>
                <?php settings_fields( self::SLUG ); ?>

                <?php do_settings_sections( self::SLUG ); ?>

                <div class="hide-if-no-js instantclick-script-editor">
                    <pre><span style="color:#708">&lt;script</span> <span style="color:#a50">data-no-instant</span><span style="color:#708">&gt;</span></pre>
                    <div id="instantclick-editor"></div>
                    <pre><span style="color:#708">&lt;/script&gt;</span></pre>
                </div>

                <?php submit_button(); ?>

            </form>
        </div>
        <?php
	}

    /**
     * From Code Snippets plugin
     * @see http://plugins.svn.wordpress.org/code-snippets/tags/1.9.1.1/code-snippets.php
     */
    public function remove_debug_bar_codemirror() {
		global $pagenow;

		if ( is_admin() && 'options-general.php' === $pagenow && isset( $_GET['page' ] ) && self::SLUG === $_GET['page'] ) {
            remove_action( 'debug_bar_enqueue_scripts', 'debug_bar_console_scripts' );
        }
	}
}