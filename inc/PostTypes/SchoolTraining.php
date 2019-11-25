<?php
/**
 * Class School Training
 *
 * @package Formatcine
 */

namespace Formatcine\PostTypes;

use Timber\{ Timber };

/**
 * School Training class
 */
class SchoolTraining {

	/**
	 * The version of the theme.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $theme_version The current version of this theme.
	 */
	private $theme_version;

	/**
	 * Construct function
	 *
	 * @param string $theme_version The theme version.
	 * @access public
	 */
	public function __construct( string $theme_version ) {
		$this->theme_version = $theme_version;

		$this->register_post_type();

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'admin_head', array( $this, 'admin_css' ) );

		add_filter( 'manage_school_training_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_school_training_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );

		// Ajax.
		add_action( 'wp_ajax_nopriv_ajax_load_school_trainings', array( $this, 'ajax_load_school_trainings' ) );
		add_action( 'wp_ajax_ajax_load_school_trainings', array( $this, 'ajax_load_school_trainings' ) );

		add_filter( 'wp_insert_post_data', array( $this, 'change_title' ), 99, 2 );
	}

	/**
	 * Register Custom Post Type
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Formations scolaires', 'Formation Nom pluriel', 'Formatcine' ),
			'singular_name'         => _x( 'Formation scolaire ', 'Formation Nom singulier', 'Formatcine' ),
			'menu_name'             => __( 'Formations scolaires', 'Formatcine' ),
			'name_admin_bar'        => __( 'Formation', 'Formatcine' ),
			'all_items'             => __( 'Toutes les formations scolaires', 'Formatcine' ),
			'add_new_item'          => __( 'Ajouter une nouvelle formation scolaire', 'Formatcine' ),
			'add_new'               => __( 'Ajouter', 'Formatcine' ),
			'new_item'              => __( 'Nouvelle formation scolaire', 'Formatcine' ),
			'edit_item'             => __( 'Modifier la formation scolaire', 'Formatcine' ),
			'update_item'           => __( 'Mettre à jour la formation scolaire', 'Formatcine' ),
			'view_item'             => __( 'Voir la formation scolaire', 'Formatcine' ),
			'view_items'            => __( 'Voir les formations scolaires', 'Formatcine' ),
			'search_items'          => __( 'Chercher parmi les formations', 'Formatcine' ),
			'not_found'             => __( 'Aucune formation scolaire trouvée.', 'Formatcine' ),
			'not_found_in_trash'    => __( 'Aucune formation scolaire trouvée dans la corbeille.', 'Formatcine' ),
			'featured_image'        => __( 'Image à la une', 'Formatcine' ),
			'set_featured_image'    => __( 'Mettre une image à la une', 'Formatcine' ),
			'remove_featured_image' => __( 'Retirer l\'image mise en avant', 'Formatcine' ),
			'use_featured_image'    => __( 'Mettre une image à la une', 'Formatcine' ),
			'insert_into_item'      => __( 'Insérer dans la formation scolaire', 'Formatcine' ),
			'uploaded_to_this_item' => __( 'Ajouter à cette formation scolaire', 'Formatcine' ),
			'items_list'            => __( 'Liste des formations', 'Formatcine' ),
			'items_list_navigation' => __( 'Navigation de liste des formations scolaires', 'Formatcine' ),
			'filter_items_list'     => __( 'Filtrer la liste des formations scolaires', 'Formatcine' ),
		);

		$rewrite = array(
			'slug'       => 'formations-scolaires',
			'with_front' => true,
			'pages'      => true,
			'feeds'      => true,
		);

		$args = array(
			'label'               => 'formation scolaire',
			'description'         => __( 'Les formations scolaires', 'Formatcine' ),
			'labels'              => $labels,
			'supports'            => array( 'thumbnail' ),
			'taxonomies'          => array( 'school_tag', 'year' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-welcome-learn-more',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'post',
		);
		register_post_type( 'school_training', $args );
	}


	/**
	 * Admin CSS
	 *
	 * @return void
	 */
	public function admin_css() {
		global $typenow;

		if ( 'school_training' !== $typenow ) {
			return;
		}

		?>
		<style>
			.fixed .title strong { display: none; }
			.fixed .title .row-actions {
				left: 0;
				padding: 0;
			}
			.fixed  th.column-title a { display: none; }
			.fixed .column-taxonomy-school_class { width: 160px; }
			.fixed .column-taxonomy-season { width: 80px; }

			.acf-field-taxonomy,
			.acf-field-date-picker,
			.acf-field-post-object {
				min-height: 0!important;
			}
		</style>
		<?php
	}


	/**
	 * Add custom columns
	 *
	 * @param arr $columns Array of columns.
	 */
	public function add_custom_columns( $columns ) {

		global $typenow;

		if ( 'school_training' !== $typenow ) {
			return;
		}

		$taxonomy_seasons      = $columns['taxonomy-season'];
		$taxonomy_school_class = $columns['taxonomy-school_class'];

		unset( $columns['date'] );
		unset( $columns['taxonomy-season'] );
		unset( $columns['taxonomy-school_class'] );

		$new_columns = array();

		foreach ( $columns as $key => $value ) {
			if ( 'title' === $key ) {
				$new_columns['taxonomy-season']       = $taxonomy_seasons;
				$new_columns['taxonomy-school_class'] = $taxonomy_school_class;
				$new_columns['movies']                = __( 'Films' );
			}
			$new_columns[ $key ] = $value;
		}

		return $new_columns;
	}


	/**
	 * Render custom columns
	 *
	 * @param str $column_name Column name.
	 * @param int $post_id The post ID.
	 */
	public function render_custom_columns( $column_name, $post_id ) {

		global $typenow;

		if ( 'school_training' !== $typenow ) {
			return;
		}

		switch ( $column_name ) {
			case 'movies':
				if ( get_field( 'movies', $post_id ) ) {
					$movies = get_field( 'movies', $post_id );
					$output = array();

					foreach ( $movies as $movie ) {
						$html  = '<a href="' . get_edit_post_link( $movie->ID );
						$html .= '">' . $movie->post_title . '</a>';

						array_push( $output, $html );
					}
					echo join( '<br>', $output );
				} else {
					echo '—';
				}

				break;
		}
	}


	/**
	 * Manually create the post title and post name since
	 * this content type doesn't have post title field.
	 *
	 * @param  array $data The data.
	 * @param  array $postarr The post array.
	 * @return array
	 */
	public function change_title( $data, $postarr ) {
		$screen = get_current_screen();

		if ( 'school_training' !== $screen->post_type ) {
			return $data;
		}

		// Filtering Post.
		$post_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		// Grab some field value to serve as the post_title.
		$title = 'Formation';

		/**
		 * Record the manually created post title to $data['post_title'] so
		 * WordPress will save it as post title.
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


	/**
	 * Ajax load school training
	 *
	 * Load school training with AJAX request.
	 *
	 * @author Jérémy Levron <jeremylevron@19h47.fr> (http://19h47.fr)
	 */
	public function ajax_load_school_trainings() {
		check_ajax_referer( 'security', 'nonce' );

		$school_class   = 0;
		$offset         = 0;
		$posts_per_page = -1;

		if ( isset( $_GET['school_class'] ) ) {
			$school_class = sanitize_text_field( wp_unslash( $_GET['school_class'] ) );
		}

		if ( isset( $_GET['offset'] ) ) {
			$offset = (int) sanitize_text_field( wp_unslash( $_GET['offset'] ) );
		}

		if ( isset( $_GET['posts_per_page'] ) ) {
			$posts_per_page = (int) sanitize_text_field( wp_unslash( $_GET['posts_per_page'] ) );
		}

		if ( isset( $_GET['season'] ) ) {
			$season = (int) sanitize_text_field( wp_unslash( $_GET['season'] ) );
		}

		$args = array(
			'post_type'      => 'school_training',
			'posts_per_page' => $posts_per_page,
			'offset'         => $offset,
			'post_status'    => 'publish',
			'meta_key'       => 'formation_date', // phpcs:ignore
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'tax_query'      => array( // phpcs:ignore
				array(
					'taxonomy' => 'season',
					'field'    => 'term_id',
					'terms'    => $season,
				),
			),
		);

		if ( 0 !== $school_class ) {
			$args['tax_query'] = array( // phpcs:ignore
				'relation' => 'AND',
				array(
					'taxonomy' => 'season',
					'field'    => 'term_id',
					'terms'    => $season,
					'operator' => 'IN',
				),
			);

			$school_class_query = array( 'relation' => 'OR' );

			foreach ( explode( ',', $school_class ) as $term_id ) {
				array_push(
					$school_class_query,
					array(
						'taxonomy' => 'school_class',
						'field'    => 'term_id',
						'terms'    => $term_id,
					)
				);
			}
			array_push( $args['tax_query'], $school_class_query );
		}

		$context                     = Timber::get_context();
		$context['school_trainings'] = Timber::get_posts( $args );

		Timber::render( 'partials/tease-school-training.html.twig', $context );

		// That's all folks!
		wp_die();
	}
}