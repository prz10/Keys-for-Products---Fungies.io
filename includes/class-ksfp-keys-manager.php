<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Ksfp_Keys_Manager {


	public function __construct() {
		add_action( 'init', array( $this, 'register_game_post_type' ) );
		add_action( 'init', array( $this, 'register_game_taxonomy' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_metabox_data' ) );
		add_filter( 'manage_ksfp_game_key_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_ksfp_game_key_posts_custom_column', array( $this, 'fill_custom_columns' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	public function register_game_post_type() {
		$labels = array(
			'name'          => esc_html__( 'Klucze do gier', 'keys-for-wp-woo-fungies' ),
			'singular_name' => esc_html__( 'Klucz do gry', 'keys-for-wp-woo-fungies' ),
			'add_new'       => esc_html__( 'Dodaj nowy', 'keys-for-wp-woo-fungies' ),
			'add_new_item'  => esc_html__( 'Dodaj nowy klucz', 'keys-for-wp-woo-fungies' ),
		);

		$args = array(
			'public'        => false,
			'show_in_rest'  => false,
			'show_ui'       => true,
			'labels'        => $labels,
			'menu_position' => 81,
			'supports'      => array( 'title' ),
		);
		register_post_type( 'ksfp_game_key', $args );
	}

	public function register_game_taxonomy() {
		$labels = array(
			'name'          => esc_html__( 'Gry', 'keys-for-wp-woo-fungies' ),
			'singular_name' => esc_html__( 'Gra', 'keys-for-wp-woo-fungies' ),
		);

		$args = array(
			'public'       => false,
			'show_in_rest' => false,
			'hierarchical' => true,
			'labels'       => $labels,
			'show_ui'      => true,
			'show_in_menu' => true,
			'query_var'    => true,
			'show_ui'      => true,
		);

		register_taxonomy( 'ksfp_game', array( 'ksfp_game_key' ), $args );
	}

	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=ksfp_game_key',
			esc_html__( 'Import', 'keys-for-wp-woo-fungies' ),
			esc_html__( 'Import', 'keys-for-wp-woo-fungies' ),
			'manage_options',
			'import_page_slug',
			array( $this, 'import_page_content' )
		);
	}

	public function import_page_content() {
		$isSubmitted = isset( $_POST['submit'] );
		$verify      = $isSubmitted ? wp_verify_nonce( isset( $_POST['import_csv_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['import_csv_nonce'] ) ) : false, 'import_csv' ) : false;

		if ( $isSubmitted && ! $verify ) {
			print esc_html__( 'Error during import', 'keys-for-wp-woo-fungies' );
			exit;
		}

		$csv_file = isset( $_FILES['csv_file'] ) ? $_FILES['csv_file'] : false;
		$isCsv    = $csv_file ? pathinfo( $csv_file['name'], PATHINFO_EXTENSION ) === 'csv' : false;
		$isTypeOk = $csv_file ? $csv_file['type'] === 'text/csv' : false;

		$onlyCsv = $csv_file && $isCsv && $isTypeOk;

		if ( $csv_file && $csv_file['error'] == UPLOAD_ERR_OK && $onlyCsv ) {
			$file_path = $csv_file['tmp_name'];

			$this->process_csv_file( $file_path );

			echo '<div class="updated"><p>' . esc_html__( 'Success', 'keys-for-wp-woo-fungies' ) . '</p></div>';
		} elseif ( ( $csv_file && $csv_file['error'] !== UPLOAD_ERR_OK ) || ( ! $onlyCsv && $csv_file ) ) {
			echo '<div class="error"><p>' . esc_html__( 'Error during import', 'keys-for-wp-woo-fungies' ) . '</p></div>';
		}

		include_once plugin_dir_path( __FILE__ ) . '../templates/import-template.php';
	}

	public function add_custom_columns( $columns ) {
		$new_columns             = array();
		$new_columns['game_key'] = esc_html__('Klucz', 'keys-for-wp-woo-fungies' );
		$new_columns['game']     = esc_html__('Gra', 'keys-for-wp-woo-fungies' );
		$new_columns['status']   = esc_html__('Status', 'keys-for-wp-woo-fungies' );
		$new_columns['date']     = esc_html__('Data dodania', 'keys-for-wp-woo-fungies' );

		return $new_columns;
	}

	public function fill_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'game_key':
				echo esc_html( get_post_meta( $post_id, 'ksfp_game_key', true ) );
				break;
			case 'game':
				$terms = get_the_terms( $post_id, 'ksfp_game' );
				echo $terms ? esc_html( $terms[0]->name ) : esc_html__( 'Brak gry', 'keys-for-wp-woo-fungies' );
				break;
			case 'status':
				$status = get_post_meta( $post_id, 'ksfp_game_key_status', true );
				echo $status === 'active' ? esc_html__( 'Aktywny', 'keys-for-wp-woo-fungies' ) : esc_html__( 'Nieaktywny', 'keys-for-wp-woo-fungies' );
				break;
			case 'date':
				echo esc_html( get_the_date( '', $post_id ) );
				break;
		}
	}

	public function add_metaboxes() {
		add_meta_box(
			'game_key_metabox',
			esc_html__( 'Klucz gry', 'keys-for-wp-woo-fungies' ),
			array( $this, 'render_key_metabox' ),
			'ksfp_game_key',
			'normal',
			'high'
		);

		add_meta_box(
			'post_purchase_description_metabox',
			esc_html__( 'Opis po zakupie', 'keys-for-wp-woo-fungies' ),
			array( $this, 'render_post_purchase_description_metabox' ),
			'ksfp_game_key',
			'normal',
			'default'
		);

		add_meta_box(
			'game_key_status_metabox',
			esc_html__( 'Status klucza', 'keys-for-wp-woo-fungies' ),
			array( $this, 'render_key_status_metabox' ),
			'ksfp_game_key',
			'side',
			'default'
		);
	}

	public function render_key_metabox( $post ) {
		wp_nonce_field( 'game_key_save_metabox_data', 'game_key_metabox_nonce' );
		$key_value = get_post_meta( $post->ID, 'ksfp_game_key', true );
		echo '<style>#ksfp_game_key_field { width: 100%; }</style>';
		echo '<input type="text" id="ksfp_game_key_field" name="ksfp_game_key_field" value="' . esc_attr( $key_value ) . '" required>';
	}

	public function render_post_purchase_description_metabox( $post ) {
		wp_nonce_field( 'game_key_save_metabox_data', 'post_purchase_description_metabox_nonce' );
		$description_value = get_post_meta( $post->ID, '_post_purchase_description', true );
		wp_editor( $description_value, 'post_purchase_description_field', array( 'textarea_name' => 'post_purchase_description_field' ) );
	}

	public function render_key_status_metabox( $post ) {
		wp_nonce_field( 'game_key_save_metabox_data', 'game_key_status_metabox_nonce' );

		$status_value = get_post_meta( $post->ID, 'ksfp_game_key_status', true );
		echo '<select id="game_key_status_field" name="game_key_status_field">';
		echo '<option value="active" ' . selected( $status_value, 'active', false ) . '>' . esc_html__( 'Aktywny', 'keys-for-wp-woo-fungies' ) . '</option>';
		echo '<option value="inactive" ' . selected( $status_value, 'inactive', false ) . '>' . esc_html__( 'Nieaktywny', 'keys-for-wp-woo-fungies' ) . '</option>';
		echo '</select>';
	}

	public function save_metabox_data( $post_id ) {

		if ( ! isset( $_POST['game_key_metabox_nonce'] )
			|| ! isset( $_POST['post_purchase_description_metabox_nonce'] )
			|| ! isset( $_POST['game_key_status_metabox_nonce'] )
		) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['game_key_metabox_nonce'] ) ), 'game_key_save_metabox_data' )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['post_purchase_description_metabox_nonce'] ) ), 'game_key_save_metabox_data' )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['game_key_status_metabox_nonce'] ) ), 'game_key_save_metabox_data' )
		) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! update_post_meta( $post_id, '_is_new_post', '1' ) ) {
			update_post_meta( $post_id, '_is_new_post', '0' );
		}

		if ( ! is_plugin_version_paid() ) {
			$taxonomies = wp_get_post_terms( $post_id, 'ksfp_game', array( 'fields' => 'ids' ) );
			if ( empty( $taxonomies ) ) {
				set_transient( 'my_plugin_admin_notice', esc_html__('Wybór gry jest wymagany.', 'keys-for-wp-woo-fungies' ), 45 );
				wp_die( esc_html__( 'Wybór gry jest wymagany.', 'keys-for-wp-woo-fungies' ) );
			}

			$args  = array(
				'post_type'   => 'ksfp_game_key',
				'numberposts' => -1,
				'tax_query'   => array(
					array(
						'taxonomy' => 'ksfp_game',
						'field'    => 'term_id',
						'terms'    => $taxonomies,
					),
				),
				'post_status' => 'any',
				'exclude'     => array( $post_id ),
			);
			$posts = get_posts( $args );

			// if (count($posts) >= 1 && get_post_meta($post_id, '_is_new_post', true) === '1') {
			// wp_delete_post($post_id, true);
			// set_transient('my_plugin_admin_notice', esc_html__'Osiągnięto limit wersji darmowej dodanych kluczy dla wybranej gry.', 'keys-for-wp-woo-fungies'), 45);
			// wp_die(esc_html__('Osiągnięto limit wersji darmowej dodanych kluczy dla wybranej gry.', 'keys-for-wp-woo-fungies'));
			// }
		}

		if ( isset( $_POST['ksfp_game_key_field'] ) ) {
			update_post_meta( $post_id, 'ksfp_game_key', sanitize_text_field( wp_unslash( ( $_POST['ksfp_game_key_field'] ) ) ) );
		}

		if ( isset( $_POST['post_purchase_description_field'] ) ) {
			update_post_meta( $post_id, '_post_purchase_description', sanitize_textarea_field( wp_unslash( $_POST['post_purchase_description_field'] ) ) );
		}

		if ( isset( $_POST['game_key_status_field'] ) ) {
			update_post_meta( $post_id, 'ksfp_game_key_status', sanitize_text_field( wp_unslash( $_POST['game_key_status_field'] ) ) );
		}
	}

	private function prepare_csv_to_associative_arr( $filePath ) {
		$file   = file( $filePath );
		$rows   = array_map( 'str_getcsv', $file );
		$header = array_shift( $rows );
		$csv    = array();
		foreach ( $rows as $row ) {
			$csv[] = array_combine( $header, $row );
		}
		return $csv;
	}


	private function process_csv_file( $file_path ) {
		$file_path = sanitize_text_field( $file_path );
		$csvToArr  = $this->prepare_csv_to_associative_arr( $file_path );

		if ( ! $csvToArr || empty( $csvToArr ) ) {
			return;
		}
		$header = array_keys( $csvToArr[0] );
		$header = array_map( 'sanitize_text_field', $header );

		$header     = array_map( 'strtolower', $header );
		$name_index = in_array( 'nazwa', $header ) ? 'nazwa' : ( in_array( 'name', $header ) ? 'name' : false );
		$key_index  = in_array( 'klucz', $header ) ? 'klucz' : ( in_array( 'key', $header ) ? 'key' : false );

		if ( $name_index === false ) {
			$name_index = 0;
		}

		if ( $key_index === false ) {
			$key_index = 1;
		}

		foreach ( $csvToArr as $row ) {
			$name = isset( $row[ $name_index ] ) ? $row[ $name_index ] : '';
			$key  = isset( $row[ $key_index ] ) ? $row[ $key_index ] : '';

			$name = sanitize_title( $name );
			$key  = sanitize_text_field( $key );

			$term = term_exists( $name, 'ksfp_game' );

			if ( ! $term ) {
				$term_args = array(
					'description' => '',
					'slug'        => sanitize_title( $name ),
				);
				wp_insert_term( $name, 'ksfp_game', $term_args );
			}

			$term    = get_term_by( 'name', $name, 'ksfp_game' );
			$term_id = $term->term_id;

			$this->add_game_key( $key, $term_id );
		}
	}

	private function add_game_key( $key, $term_id ) {
		$key     = sanitize_text_field( $key );
		$term_id = sanitize_text_field( $term_id );

		$args          = array(
			'post_type'  => 'ksfp_game_key',
			'meta_query' => array(
				array(
					'key'   => 'ksfp_game_key',
					'value' => $key,
				),
			),
		);
		$existing_keys = get_posts( $args );

		if ( empty( $existing_keys ) ) {
			$post_args = array(
				'post_title' => $key,
				'post_type'  => 'ksfp_game_key',
				'tax_input'  => array(
					'ksfp_game' => array( $term_id ),
				),
			);
			$post_id   = wp_insert_post( $post_args );
			update_post_meta( $post_id, 'ksfp_game_key_status', 'active' );
			update_post_meta( $post_id, 'ksfp_game_key', $key );
		}
	}
}
