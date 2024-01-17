<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly ?>

<link rel="stylesheet" href="<?php echo esc_html__( KSFP_KEYS_FOR_PRODUCTS_ASSETS_URL ); ?>/styles/style.css">

<div class="wrap" style='background-image: url(<?php echo esc_html__( KSFP_KEYS_FOR_PRODUCTS_IMAGES_URL ); ?>bg.png);'>
	<div class="header-holder">
		<div class="logo-holder">
			<img src="<?php echo esc_html__( KSFP_KEYS_FOR_PRODUCTS_IMAGES_URL ); ?>logowhite.png" alt="keys">
		</div>
	</div>
	<div class="content">
		<img src="<?php echo esc_html__( KSFP_KEYS_FOR_PRODUCTS_IMAGES_URL ); ?>fungies-logo.png" alt="keys">
		<h1>
			<?php echo esc_html__( 'Hej!', 'keys-for-wp-woo-fungies' ); ?>
		</h1>
		<p><?php echo esc_html__( 'Your plugin version:', 'keys-for-wp-woo-fungies' ); ?> <span
				class='version-plugin'><?php echo esc_html__( get_option( 'ksfp-keys-for-wp-woo-version' ) ); ?></span>
		</p>

	</div>

</div>