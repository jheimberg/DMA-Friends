<?php
/**
 * Plugin class that registers the Featured CPT.
 *
 */
class Featured_CPT_Setup extends WDSCPT_Setup {

	/**
	 * Holds copy of instance, so other plugins can remove our hooks.
	 *
	 * @since 1.0
	 * @link http://core.trac.wordpress.org/attachment/ticket/16149/query-standard-format-posts.php
	 * @link http://twitter.com/#!/markjaquith/status/66862769030438912
	 *
	 */
	static $instance;
	private $nonce = 'wdsfeatured_link';
	private $field = '_wdsfeatured_link';

	function __construct( $newargs = array() ) {

		self::$instance = $this;

		$newargs = is_array( $newargs ) ? $newargs : array();

		$args = wp_parse_args( $newargs, array(
			'post_type' => 'Featured Entry',
			'plural' => 'Featured Entries',
			'registered' => null,
			'post_type_args' => array(
				'public' => false,
				'exclude_from_search' => true,
				'menu_position' => 5,
				'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' )
			)
		) );

		$this->WDSCPT_Setup( $args['post_type'], $args['plural'], $args['registered'], $args['post_type_args'] );

		add_filter( 'enter_title_here', array( $this, 'title' ) );
		add_filter( 'manage_edit-'. $this->slug .'_columns',  array( $this, 'columns' ) );
		add_action( 'manage_posts_custom_column' ,  array( $this, 'displaycolumns' ) );
		add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_link' ), 10, 2);
		add_filter( 'pre_get_posts', array( $this, 'set_admin_order' ) );
	}

	function columns( $columns ){
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => 'Title',
			'date' => 'Date Published',
			'featured_link' => 'Links To:',
			'featured_thumbnail' => 'Thumbnail',
		);

		return $columns;
	}

	public function displaycolumns( $column ){
		global $post;
		switch ( $column ) {
			case 'featured_link':
				echo make_clickable( esc_url( get_post_meta( $post->ID, $this->field, true) ) );
			break;
			case 'featured_thumbnail':
				echo '<a style="position: relative;" href="'. get_edit_post_link() .'">';
					the_post_thumbnail( 'medium' );
				echo '</a>';
			break;
		}
	}

	public function title( $title ){

		$screen = get_current_screen();
		if ( $screen->post_type == $this->slug ) {
			$title = 'Featured Title';
		}

		return $title;
	}

	public function meta_boxes() {

		remove_meta_box( 'postimagediv', $this->slug, 'side' );
		add_meta_box('postimagediv', __('Featured Image'), 'post_thumbnail_meta_box', $this->slug, 'normal', 'high');

		add_meta_box( 'featured-linkbox', __('Link'), array( $this, 'link_box' ), $this->slug, 'normal', 'default' );
	}

	public function link_box() {
		wp_nonce_field( $this->nonce, $this->nonce );
		// get our saved link
		$link = esc_url( get_post_meta( get_the_ID(), $this->field, true ) );
		?>
		<label>
			<h4>Destination URL</h4>
			<input style="width:40%;" type="text" name="<?php echo $this->field; ?>" id="featured_link" value="<?php echo esc_url( $link ); ?>" class="widefat" />
			<p><em>Set a link for this <?php echo $this->single; ?>.</em></p>
			<?php do_action( 'wdscaroufredsel_cpt_metabox' ); ?>
		</label>
		<?php
	}

	public function save_link( $post_id ) {
		// verify this came from our screen and with proper authorization.

		if (
			!isset( $_POST[$this->field] )
			// check nonce
			|| !isset( $_POST[$this->nonce] )
			|| !wp_verify_nonce( $_POST[$this->nonce], $this->nonce)
			// make sure user is allowed to edit posts
			|| !current_user_can( 'edit_post', $post_id )
		)
			return;

		// The data
		$url = trim( $_POST[$this->field] );
		// OK, we're authenticated: we need to find, sanitize, and save the data
		$to_save = !empty( $url ) ? esc_url( $url ) : '';
		// update our post
		update_post_meta( $post_id, $this->field, $to_save );
		// delete our cpt query transient if it exists
		delete_transient( 'wds_cfs_cpt_data' );
		do_action( 'wdscaroufredsel_cpt_metabox_save', $post_id );
	}

	public function set_admin_order( $query ) {
		if ( !is_admin() || $query->query['post_type'] != $this->slug )
			return;
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}

}
