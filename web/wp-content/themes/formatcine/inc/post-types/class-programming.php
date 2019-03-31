<?php
/**
 * Class Programming
 *
 * @package frmtcn
 */

/**
 * Programming class
 */
class Programming {

	/**
	 * The version of the theme.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this theme.
	 */
	private $theme_version;

	/**
	 * Construct function
	 *
	 * @param str $theme_version The theme version.
	 * @access public
	 */
	public function __construct( $theme_version ) {
		$this->theme_version = $theme_version;

		$this->register_post_type();

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'admin_head', array( $this, 'css' ) );
		add_action( 'admin_head', array( $this, 'admin_css' ) );
		add_filter( 'dashboard_glance_items', array( $this, 'at_a_glance' ) );

		add_filter( 'manage_programming_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_programming_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );

		add_filter( 'wp_insert_post_data', array( $this, 'change_title' ), 99, 2 );
	}


	/**
	 * Register Custom Post Type
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Programmations', 'Programmation Nom pluriel', 'frmtcn' ),
			'singular_name'         => _x( 'Programmation', 'Programmation Nom singulier', 'frmtcn' ),
			'menu_name'             => __( 'Programmations', 'frmtcn' ),
			'name_admin_bar'        => __( 'Programmation', 'frmtcn' ),
			'all_items'             => __( 'Toutes les programmations', 'frmtcn' ),
			'add_new_item'          => __( 'Ajouter une nouvelle programmation', 'frmtcn' ),
			'add_new'               => __( 'Ajouter', 'frmtcn' ),
			'new_item'              => __( 'Nouvelle programmation', 'frmtcn' ),
			'edit_item'             => __( 'Modifier la programmation', 'frmtcn' ),
			'update_item'           => __( 'Mettre à jour la programmation', 'frmtcn' ),
			'view_item'             => __( 'Voir la programmation', 'frmtcn' ),
			'view_items'            => __( 'Voir les programmations', 'frmtcn' ),
			'search_items'          => __( 'Chercher parmi les programmations', 'frmtcn' ),
			'not_found'             => __( 'Aucune programmation trouvée.', 'frmtcn' ),
			'not_found_in_trash'    => __( 'Aucune programmation trouvée dans la corbeille.', 'frmtcn' ),
			'featured_image'        => __( 'Image à la une', 'frmtcn' ),
			'set_featured_image'    => __( 'Mettre une image à la une', 'frmtcn' ),
			'remove_featured_image' => __( 'Retirer l\'image mise en avant', 'frmtcn' ),
			'use_featured_image'    => __( 'Mettre une image à la une', 'frmtcn' ),
			'insert_into_item'      => __( 'Insérer dans la programmation', 'frmtcn' ),
			'uploaded_to_this_item' => __( 'Ajouter à cette programmation', 'frmtcn' ),
			'items_list'            => __( 'Liste des programmations', 'frmtcn' ),
			'items_list_navigation' => __( 'Navigation de liste des programmations', 'frmtcn' ),
			'filter_items_list'     => __( 'Filtrer la liste des programmations', 'frmtcn' ),
		);

		$rewrite = array(
			'slug'       => 'programmations',
			'with_front' => true,
			'pages'      => true,
			'feeds'      => true,
		);

		$args = array(
			'label'               => 'programmation',
			'description'         => __( 'Les programmations', 'frmtcn' ),
			'labels'              => $labels,
			'supports'            => array( '' ),
			'taxonomies'          => array( 'school_tag', 'year' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-calendar-alt',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'post',
		);
		register_post_type( 'programming', $args );
	}

	/**
	 * CSS
	 *
	 * @return void
	 */
	public function css() {

		?>
		<style>
			#dashboard_right_now .programming-count:before { content: "\f508"; }
		</style>
		<?php
	}

	/**
	 * Admin CSS
	 *
	 * @return bool
	 */
	public function admin_css() {

		global $typenow;

		if ( 'programming' !== $typenow ) {
			return false;
		}

		?>
		<style>
			.fixed .title strong { display: none; }
			.fixed  th.column-title a { display: none; }
			.fixed .title .row-actions {
				left: 0;
				padding: 0;
			}
			.fixed .column-taxonomy-school_class { width: 160px; }

			.fixed .column-taxonomy-season { width: 80px; }
			.fixed .column-quarter { width: 160px; }

			.acf-field-post-object { min-height: 0!important; }
		</style>
		<?php
	}

	/**
	 * Add custom columns
	 *
	 * @param arr $columns Array of columns.
	 */
	public function add_custom_columns( $columns ) {

		$new_columns = array();

		$taxonomy_seasons      = $columns['taxonomy-season'];
		$taxonomy_school_class = $columns['taxonomy-school_class'];

		unset( $columns['date'] );
		unset( $columns['taxonomy-season'] );
		unset( $columns['taxonomy-school_class'] );

		foreach ( $columns as $key => $value ) {
			if ( 'title' === $key ) {
				$new_columns['taxonomy-season']       = $taxonomy_seasons;
				$new_columns['quarter']               = __( 'Trimestres' );
				$new_columns['taxonomy-school_class'] = $taxonomy_school_class;
				$new_columns['movie']                 = __( 'Film' );
			}
			$new_columns[ $key ] = $value;
		}
		return $new_columns;
	}


	/**
	 * Render custom columns
	 *
	 * @param str $column_name Array of column name.
	 * @param int $post_id The post ID.
	 */
	public function render_custom_columns( $column_name, $post_id ) {

		global $typenow;

		if ( 'programming' !== $typenow ) {
			return;
		}

		switch ( $column_name ) {
			case 'movie':
				$movie = get_field( 'movie', $post_id );

				if ( $movie ) {
					echo '<a href="' . get_edit_post_link( $post_id );
					echo '">' . $movie->post_title . '</a>';
				} else {
					echo '—';
				}
				break;

			case 'quarter':
				$quarter = get_field( 'quarter', $post_id );

				if ( $quarter ) {
					echo $quarter['label'];
				} else {
					echo '—';
				}
				break;
		}
	}


	/**
	 * "At a glance" items (dashboard widget): add the projects.
	 *
	 * @param arr $items Array of items.
	 */
	public function at_a_glance( $items ) {
		$post_type   = 'programming';
		$post_status = 'publish';
		$object      = get_post_type_object( $post_type );

		$num_posts = wp_count_posts( $post_type );
		if ( ! $num_posts || ! isset( $num_posts->{$post_status} ) || 0 === (int) $num_posts->{$post_status} ) {

			return $items;
		}
		$text = sprintf(
			_n( '%1$s %4$s%2$s', '%1$s %4$s%3$s', $num_posts->{$post_status} ),
			number_format_i18n( $num_posts->{$post_status} ),
			strtolower( $object->labels->singular_name ),
			strtolower( $object->labels->name ),
			'pending' === $post_status ? 'Pending ' : ''
		);
		if ( current_user_can( $object->cap->edit_posts ) ) {
			$items[] = sprintf( '<a class="%1$s-count" href="edit.php?post_status=%2$s&post_type=%1$s">%3$s</a>', $post_type, $post_status, $text );

		} else {
			$items[] = sprintf( '<span class="%1$s-count">%s</span>', $text );
		}

		return $items;
	}


	/**
	 * Change title
	 *
	 * Manually create the post title and post name since this content type
	 * doesn't have post title field.
	 *
	 * @param  arr $data Array of data.
	 * @param  arr $postarr Array of post.
	 * @return arr
	 */
	public function change_title( $data, $postarr ) {

		$screen = get_current_screen();

		if ( 'programming' !== $screen->post_type ) {
			return $data;
		}

		// Filtering Post.
		$post_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		// Grab some field value to serve as the post_title.
		$title = 'Programmation';

		/**
		 * Record the manually created post title to $data['post_title'] so
		 * WordPress will save it as post title
		 */
		$data['post_title'] = $title;

		// Create manually post_name using data from title.
		$slug              = sanitize_title_with_dashes( $title );
		$data['post_name'] = wp_unique_post_slug(
			$slug,
			$postarr['ID'],
			$postarr['post_status'],
			$postarr['post_type'],
			$postarr['post_parent']
		);

		// Remember this is a "filter", need to return the data back!
		return $data;
	}
}