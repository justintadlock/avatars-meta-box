<?php
/**
 * Handles the author avatars meta box.
 *
 * @package   AvatarsMetaBox
 * @version   1.0.0
 * @author    Justin Tadlock <justin@justintadlock.com>
 * @copyright Copyright (c) 2015, Justin Tadlock
 * @link      http://themehybrid.com/plugins/avatars-meta-box
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Meta box class.
 *
 * @since  1.0.0
 * @access public
 */
final class AMB_Meta_Box_Avatars {

	/**
	 * Sets up the appropriate actions.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @return void
	 */
	protected function __construct() {

		add_action( 'load-post.php',     array( $this, 'load' ) );
		add_action( 'load-post-new.php', array( $this, 'load' ) );
	}

	/**
	 * Fires on the page load hook to add actions specifically for the post and
	 * new post screens.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function load() {

		// Add custom meta boxes.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Enqueue scripts/styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Loads scripts and styles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue() {

		$screen = get_current_screen();

		// If the post type doesn't support `author`, bail.
		if ( ! isset( $screen->post_type ) || ! post_type_supports( $screen->post_type, 'author' ) )
			return;

		wp_enqueue_script( 'amb-meta-box' );
		wp_enqueue_style(  'amb-meta-box' );
	}

	/**
	 * Adds the meta box.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes( $post_type ) {

		// If the post type doesn't support `author`, bail.
		if ( ! post_type_supports( $post_type, 'author' ) )
			return;

		// Remove the core meta box.
		remove_meta_box( 'authordiv', $post_type, 'normal' );

		// Add our custom meta box.
		add_meta_box( 'amb-avatars-author', sprintf( esc_html__( 'Author: %s', 'avatars-meta-box' ), '<span class="amb-which-author"></span>' ), array( $this, 'meta_box' ), $post_type, 'normal', 'default' );
	}

	/**
	 * Outputs the meta box HTML.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $post
	 * @return void
	 */
	public function meta_box( $post ) {

		// Set up the main arguments for `get_users()`.
		$args = array( 'who' => 'authors' );

		// WP version 4.4.0 check. User `role__in` if we can.
		if ( method_exists( 'WP_User_Query', 'fill_query_vars' ) )
			$args = array( 'role__in' => $this->get_roles( $post->post_type ) );

		// Get the users allowed to be post author.
		$users = get_users( $args ); ?>

		<div class="amb-avatars">

		<?php foreach ( $users as $user ) : ?>

			<label>
				<input type="radio" value="<?php echo esc_attr( $user->ID ); ?>" name="post_author_override" <?php checked( $user->ID, $post->post_author ); ?> />

				<span class="screen-reader-text"><?php echo esc_html( $user->display_name ); ?></span>

				<?php echo get_avatar( $user->ID, 70 ); ?>
			</label>

		<?php endforeach; ?>

		</div><!-- .amb-avatars -->
	<?php }

	/**
	 * Returns an array of user roles that are allowed to edit, publish, or create
	 * posts of the given post type.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $post_type
	 * @global object  $wp_roles
	 * @return array
	 */
	public function get_roles( $post_type ) {
		global $wp_roles;

		$roles = array();
		$type  = get_post_type_object( $post_type );

		// Get the post type object caps.
		$caps = array( $type->cap->edit_posts, $type->cap->publish_posts, $type->cap->create_posts );
		$caps = array_unique( $caps );

		// Loop through the available roles.
		foreach ( $wp_roles->roles as $name => $role ) {

			foreach ( $caps as $cap ) {

				// If the role is granted the cap, add it.
				if ( isset( $role['capabilities'][ $cap ] ) && true === $role['capabilities'][ $cap ] ) {
					$roles[] = $name;
					break;
				}
			}
		}

		return $roles;
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) )
			$instance = new self;

		return $instance;
	}
}

AMB_Meta_Box_Avatars::get_instance();
