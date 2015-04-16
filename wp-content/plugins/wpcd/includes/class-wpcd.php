<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WPCD {

	/**
	 * The single instance of WPCD.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'wpcd';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load admin CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new WPCD_Admin_API();
		}

		add_action( 'init', array( $this, 'register_post_types' ), 9 );
		add_action( 'init', array( $this, 'register_taxonomies' ), 9 );

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	} // End __construct ()

	public function register_post_types () {
		$post_types['team'] = $this->register_post_type( 'team', __( 'Teams', 'wpcd' ), __( 'Team', 'wpcd' ) );
		$post_types['resource'] = $this->register_post_type( 'resource', __( 'Resources', 'wpcd' ), __( 'Resource', 'wpcd' ) );

		$this->modify_post_types();
		$this->register_meta_boxes();
		$this->register_custom_fields();
	}

	public function modify_post_types () {
		add_filter( 'team_register_args', array( $this, 'post_type_args' ), 10, 2 );
		add_filter( 'resource_register_args', array( $this, 'post_type_args' ), 10, 2 );
	}

	public function post_type_args ( $args = array(), $post_type = '' ) {

		switch( $post_type ) {

			case 'team':
				$args['menu_icon'] = 'dashicons-groups';
				$args['supports'] = array( 'title', 'excerpt' );
			break;

			case 'resource':
				$args['menu_icon'] = 'dashicons-lightbulb';
				$args['rewrite'] = array( 'slug' => 'resources' );
			break;

		}

		return $args;
	}

	public function register_taxonomies () {
		$this->taxonomies['resource_cat'] = $this->register_taxonomy( 'resource_cat', __( 'Resource Categories', 'wpcd' ), __( 'Resource Category', 'wpcd' ), 'resource' );
		$this->taxonomies['resource_type'] = $this->register_taxonomy( 'resource_type', __( 'Resource Types', 'wpcd' ), __( 'Resource Type', 'wpcd' ), 'resource' );
		$this->modify_taxonomies();
	}

	public function modify_taxonomies () {
		add_filter( 'resource_cat_register_args', array( $this, 'taxonomy_args' ), 10, 3 );
		add_filter( 'resource_type_register_args', array( $this, 'taxonomy_args' ), 10, 3 );
	}

	public function taxonomy_args ( $args = array(), $taxonomy = '', $post_types = array() ) {

		switch( $taxonomy ) {
			case 'resource_cat':
				$args['rewrite'] = array( 'slug' => 'resource-category', 'hierarchical' => true );
			break;
			case 'resource_type':
				$args['rewrite'] = array( 'slug' => 'resource-type', 'hierarchical' => true );
			break;
		}
		return $args;
	}

	public function register_meta_boxes () {
		add_filter( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
	}

	public function add_meta_boxes ( $post_type = '', $post ) {
		$this->admin->add_meta_box( 'team-details', __( 'Team Details', 'wpcd' ), 'team' );
		$this->admin->add_meta_box( 'resource-details', __( 'Resource Details', 'wpcd' ), 'resource' );
	}

	public function register_custom_fields () {
		add_filter( 'team_custom_fields', array( $this, 'get_custom_fields' ), 10, 2 );
		add_filter( 'resource_custom_fields', array( $this, 'get_custom_fields' ), 10, 2 );
	}

	public function get_custom_fields ( $fields = array(), $post_type = '' ) {

		$fields['team'] = array(
			array(
				'id' 			=> '_team_icon',
				'metabox'		=> 'team-details',
				'label'			=> __( 'Team icon: ' , 'wpcd' ),
				'description'	=> '',
				'type'			=> 'text',
				'default'		=> '',
				'placeholder'	=> __( 'Dashicon name', 'wpcd' ),
				'class'			=> 'regular-text',
			),
			array(
				'id' 			=> '_team_url',
				'metabox'		=> 'team-details',
				'label'			=> __( 'Team URL: ' , 'wpcd' ),
				'description'	=> '',
				'type'			=> 'url',
				'default'		=> '',
				'placeholder'	=> __( 'http://', 'wpcd' ),
				'class'			=> 'regular-text',
			),
			array(
				'id' 			=> '_team_rss',
				'metabox'		=> 'team-details',
				'label'			=> __( 'RSS feed: ' , 'wpcd' ),
				'description'	=> '',
				'type'			=> 'url',
				'default'		=> '',
				'placeholder'	=> __( 'http://', 'wpcd' ),
				'class'			=> 'regular-text',
			),
			array(
				'id' 			=> '_contribution_notes',
				'metabox'		=> 'team-details',
				'label'			=> __( 'Contribution notes: ' , 'wpcd' ),
				'description'	=> __( 'A quick guide on how this team works on the day - HTML allowed.', 'wpcd' ),
				'type'			=> 'textarea',
				'default'		=> '',
				'placeholder'	=> '',
				'class'			=> 'large-text',
			),
		);

		$teams = get_posts( array( 'post_type' => 'team', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		$team_options[0] = __( 'None', 'wpcd' );
		foreach( $teams as $team ) {
			$team_options[ $team->ID ] = $team->post_title;
		}

		$fields['resource'] = array(
			array(
				'id' 			=> '_resource_url',
				'metabox'		=> 'resource-details',
				'label'			=> __( 'Resource URL: ' , 'wpcd' ),
				'description'	=> '',
				'type'			=> 'url',
				'default'		=> '',
				'placeholder'	=> __( 'http://', 'wpcd' ),
				'class'			=> 'regular-text',
			),
			array(
				'id' 			=> '_resource_team',
				'metabox'		=> 'resource-details',
				'label'			=> __( 'Contribution team: ' , 'wpcd' ),
				'description'	=> __( 'Contribution team that is relevant for this resource.', 'wpcd' ),
				'type'			=> 'select',
				'default'		=> 0,
				'options'		=> $team_options,
			),
		);

		if( isset( $fields[ $post_type ] ) ) {
			return $fields[ $post_type ];
		} else {
			return array();
		}
	}

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '' ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new WPCD_Post_Type( $post_type, $plural, $single, $description );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new WPCD_Taxonomy( $taxonomy, $plural, $single, $post_types );

		return $taxonomy;
	}

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'wpcd', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'wpcd';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main WPCD Instance
	 *
	 * Ensures only one instance of WPCD is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WPCD()
	 * @return Main WPCD instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
