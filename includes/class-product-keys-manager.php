<?php

class Product_Keys_Manager {

    public function __construct() {
        add_filter('woocommerce_product_data_tabs', array($this, 'add_keys_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'add_keys_panel'));
        add_action('woocommerce_process_product_meta', array($this, 'save_keys_data'));
    }

    public function add_keys_tab($tabs) {
        $tabs['keys_tab'] = array(
            'label' => __('Klucze', 'keys-for-wp-woo-fungies'),
            'target' => 'keys_options_panel',
            'class'  => array(),
        );
        return $tabs;
    }

    public function add_keys_panel() {
        echo '<div id="keys_options_panel" class="panel woocommerce_options_panel">';
        echo '<div class="options_group">';

        // Checkbox do włączania obsługi kluczy
        woocommerce_wp_checkbox(array(
            'id' => '_enable_keys',
            'label' => __('Włącz obsługę kluczy', 'keys-for-wp-woo-fungies'),
        ));

        // Selektor do wyboru gry
        $terms = get_terms('ksfp_game', array('hide_empty' => false));
        woocommerce_wp_select(array(
            'id' => '_selected_game',
            'label' => __('Gra', 'keys-for-wp-woo-fungies'),
            'options' => array_reduce($terms, function($options, $term) {
                $options[$term->slug] = $term->name;
                return $options;
            }, array('' => __('Wybierz grę', 'keys-for-wp-woo-fungies')))
        ));

        echo '</div>';
        echo '</div>';
    }

    public function save_keys_data($post_id) {
        $enable_keys = isset($_POST['_enable_keys']) ? 'yes' : 'no';
        update_post_meta($post_id, '_enable_keys', $enable_keys);

        if (isset($_POST['_selected_game'])) {
            update_post_meta($post_id, '_selected_game', sanitize_text_field($_POST['_selected_game']));
        }
    }
}

