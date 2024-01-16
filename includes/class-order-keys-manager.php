<?php

class Order_Keys_Manager {

    public function __construct() {
		add_action('woocommerce_order_status_processing', array($this, 'handle_order_processing'));
		add_action('woocommerce_admin_order_data_after_order_details', array($this, 'display_order_keys_info'));
    }

	public function handle_order_processing($order_id) {
		$order = wc_get_order($order_id);

		foreach ($order->get_items() as $item_id => $item) {
			$product_id = $item->get_product_id();
			$enable_keys = get_post_meta($product_id, '_enable_keys', true);

			if ('yes' === $enable_keys) {
				$existing_key = wc_get_order_item_meta($item_id, '_assigned_key', true);

				if (empty($existing_key)) {
					$key = $this->get_key_for_product($product_id);

					if ($key) {
						wc_add_order_item_meta($item_id, '_assigned_key', $key);
						$this->send_key_email($order, $item, $key);
					}
				}
			}
		}
	}

	private function get_key_for_product($product_id) {
		$game_slug = get_post_meta($product_id, '_selected_game', true);
		if (empty($game_slug)) {
			return false;
		}
		
		$game_term = get_term_by('slug', $game_slug, 'ksfp_game');
		if (!$game_term) {
			return false; 
		}

		$args = array(
			'post_type' => 'ksfp_game_key',
			'posts_per_page' => 1,
			'orderby' => 'rand',
			'tax_query' => array(
				array(
					'taxonomy' => 'ksfp_game',
					'field' => 'term_id',
					'terms' => $game_term->term_id,
				),
			),
			'meta_query' => array(
				array(
					'key' => '_game_key_status',
					'value' => 'active',
					'compare' => '='
				)
			)
		);

		$key_posts = get_posts($args);

		if (!empty($key_posts)) {
			$key_post = $key_posts[0];

			update_post_meta($key_post->ID, '_game_key_status', 'inactive');

			$key = get_post_meta($key_post->ID, '_game_key', true);
			return $key;
		}

		return false;
	}

	private function send_key_email($order, $item, $key) {
		$product_id = $item->get_product_id();
		$key_post_id = $this->get_key_post_id_by_key($key);

		if (!$key_post_id) {
			return;
		}

		$post_purchase_description = get_post_meta($key_post_id, '_post_purchase_description', true);
		$game_key = get_post_meta($key_post_id, '_game_key', true);

		$customer_email = $order->get_billing_email();

		$email_content = $post_purchase_description . "<br>" . __('Your game key: ', 'keys-for-wp-woo-fungies') . $game_key;

		$email_subject = __('Your game key', 'keys-for-wp-woo-fungies');

		wc_mail($customer_email, $email_subject, $email_content);
	}

	private function get_key_post_id_by_key($key) {

		$args = array(
			'post_type' => 'ksfp_game_key',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => '_game_key',
					'value' => $key,
					'compare' => '='
				)
			)
		);

		$key_posts = get_posts($args);
		if (!empty($key_posts)) {
			return $key_posts[0]->ID;
		}

		return false;
	}

	public function display_order_keys_info($order) {
		echo '<div class="order_keys_info_section">';
		echo '<h2>' . __('Informacje o kluczach', 'keys-for-wp-woo-fungies') . '</h2>';

		foreach ($order->get_items() as $item_id => $item) {
			$product_id = $item->get_product_id();
			$enable_keys = get_post_meta($product_id, '_enable_keys', true);

			if ('yes' === $enable_keys) {
				$key = wc_get_order_item_meta($item_id, '_assigned_key', true);
				if ($key) {
					$key_post_id = $this->get_key_post_id_by_key($key);
					if ($key_post_id) {
						$game_key = get_post_meta($key_post_id, '_game_key', true);

						echo '<p><strong>' . esc_html($item->get_name()) . ':</strong> ';
						echo esc_html__('Klucz:', 'keys-for-wp-woo-fungies') . ' ' . esc_html($game_key);
						echo '</p>';
					}
				}
			}
		}

		echo '</div>';
	}

}

