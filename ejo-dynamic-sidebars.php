<?php
/**
 * Plugin Name: EJO Dynamic Sidebars
 * Plugin URI: http://github.com/ejoweb/ejo-dynamic-sidebars
 * Description: Give user the option to chose sidebar on per-page base.
 * Version: 0.4
 * Author: Erik Joling
 * Author URI: http://www.ejoweb.nl/
 * License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * GitHub Plugin URI: https://github.com/EJOweb/ejo-dynamic-sidebars
 * GitHub Branch:     master
 *
 * Minimum PHP version: 5.3.0
 */

/**
 *
 */
final class EJO_Dynamic_Sidebars 
{
    //* Version number of this plugin
    public static $version = '0.4';

    //* Holds the instance of this class.
    protected static $_instance = null;

    //* Store the slug of this plugin
    public static $slug = 'ejo-dynamic-sidebars';

    //* Stores the directory path for this plugin.
    public static $dir;

    //* Stores the directory URI for this plugin.
    public static $uri;

    //* Returns the instance.
    public static function instance() 
    {
        if ( !self::$_instance )
            self::$_instance = new self;
        return self::$_instance;
    }

    //* Plugin setup.
    protected function __construct() 
    {
        //* Setup
        self::setup();

        //* Add Metabox
        add_action( 'add_meta_boxes', array( $this, 'add_dynamic_sidebar_metabox' ) );
		add_action( 'pre_post_update', array( $this, 'save_dynamic_sidebar' ) ); // save the custom fields. Save_post hook doesn't seem to be called when not changing the post

		//* Add options to ejo_theme_options
		add_action( 'ejo_theme_options', array( $this, 'ejo_dynamic_sidebar_options' ) );
    }

    //* Defines the directory path and URI for the plugin.
    protected static function setup() 
    {
        // Store directory path and url of this plugin
        self::$dir = plugin_dir_path( __FILE__ );
        self::$uri = plugin_dir_url(  __FILE__ );
    }

	//* Add Dynamic sidebar metabox
	public function add_dynamic_sidebar_metabox() 
	{
		add_meta_box( 'ejo_dynamic_sidebar_metabox', 'Kies de zijbalk', array( $this, 'render_dynamic_sidebar_metabox' ), 'page', 'side', 'default' );
		add_meta_box( 'ejo_dynamic_sidebar_metabox', 'Kies de zijbalk', array( $this, 'render_dynamic_sidebar_metabox' ), 'post', 'side', 'default' );

		//* Get public custom post-types
		$post_types = get_post_types( array(
			'public'   => true,
			'_builtin' => false
		)); 

		//* Add metabox for each custom post type
		foreach ( $post_types  as $post_type ) {
			add_meta_box( 'ejo_dynamic_sidebar_metabox', 'Kies de zijbalk', array( $this, 'render_dynamic_sidebar_metabox' ), $post_type, 'side', 'default' );
		}

	}

	//* The dynamic sidebar metabox
	public function render_dynamic_sidebar_metabox( $post ) 
	{
		//* Noncename needed to verify where the data originated
		wp_nonce_field( 'ejo-dynamic-sidebar-metabox-' . $post->ID, 'ejo-dynamic-sidebar-meta-nonce' );

		//* Get all registerd sidebars
		global $wp_registered_sidebars;
		?>

		<p>
			<select name="ejo-dynamic-sidebar">
				<option value="">--Standaard--</option>
				<?php

				$selected_sidebar = get_post_meta( $post->ID, '_ejo-dynamic-sidebar', true );

				foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {

					//* if registered widget-area has 'sidebar' in it's name
					if (strpos($sidebar_id,'sidebar') !== false) {

						$selected = selected($sidebar_id, $selected_sidebar, false);
						echo '<option value="' . $sidebar_id . '" ' . $selected . '>' . $sidebar['name'] . '</option>';
					}							
				}

				/* <option value="no-sidebar" <?php selected('no-sidebar', $selected_sidebar); ?>>-- Geen Zijbalk --</option>*/
				?>

			</select>
		</p>

	<?php
	}

	//* Manage saving Metabox Data
	public function save_dynamic_sidebar($post_id) 
	{
		//* Don't try to save the data under autosave, ajax, or future post.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		if ( defined( 'DOING_CRON' ) && DOING_CRON )
			return;

		//* Don't save if WP is creating a revision (same as DOING_AUTOSAVE?)
		if ( wp_is_post_revision( $post_id ) )
			return;

		//* Check that the user is allowed to edit the post
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		//* Verify where the data originated
		if ( !isset($_POST['ejo-dynamic-sidebar-meta-nonce']) || !wp_verify_nonce( $_POST['ejo-dynamic-sidebar-meta-nonce'], 'ejo-dynamic-sidebar-metabox-' . $post_id ) )
			return;

		$meta_key = '_ejo-dynamic-sidebar';

		if ( !empty( $_POST['ejo-dynamic-sidebar'] ) )
			update_post_meta( $post_id, $meta_key, $_POST['ejo-dynamic-sidebar'] );
		else
			delete_post_meta( $post_id, $meta_key );
	}

	//* Get sidebar
	public static function get_sidebar_id()
	{
		if ( is_home() && !empty(get_option( 'page_for_posts' )) ) 
			$post_id = get_option( 'page_for_posts' ); //Blogpage
		else 
			$post_id = get_the_ID();

		//* Check if there is a sidebar selected for this post
		$selected_sidebar = get_post_meta( $post_id, '_ejo-dynamic-sidebar', true );

		//* If no sidebar selected, get sidebar based on post-type
		if ( empty($selected_sidebar) ) {

			//* Get default sidebars of post-types
			$ejo_post_type_sidebar = get_option( 'ejo_dynamic_sidebars', '' );

			//* Get post-type
			$post_type = get_post_type();

			//* Get sidebar based on post-type
			$selected_sidebar = isset($ejo_post_type_sidebar[$post_type]) ? $ejo_post_type_sidebar[$post_type] : '';
		}

		//* Let theme provide fallback sidebar so it's not dependant on this plugins fallback

		return $selected_sidebar;
	}

	public function ejo_dynamic_sidebar_options()
	{
		require_once( self::$dir . 'includes/option-page.php');
	}

}

//* Call EJO Dynamic Sidebars
EJO_Dynamic_Sidebars::instance();

