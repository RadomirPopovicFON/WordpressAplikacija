<?php
/**
 * Order tracking form
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

?>

<form action="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" method="post" class="track_order">

	<p><?php _e( 'Da biste pratili Vašu porudžbinu molimo Vas unesite ID porudžbine u koloni ispod i kliknite na "Prati" dugme. ID možete naći na Vašem računu, kao i u potvrdnom e-mejlu koji bi trebalo da ste primili.', 'bazaar-lite' ); ?></p>

	<p class="form-row form-row-first"><label for="orderid"><?php _e( 'ID porudžbine', 'bazaar-lite' ); ?></label> <input class="input-text" type="text" name="orderid" id="orderid" placeholder="<?php _e( 'Iz e-mejla za porudžbinu.', 'bazaar-lite' ); ?>" /></p>
	<p class="form-row form-row-last"><label for="order_email"><?php _e( 'E-mail za porudžbinu', 'bazaar-lite' ); ?></label> <input class="input-text" type="text" name="order_email" id="order_email" placeholder="<?php _e( 'E-mail koji ste koristili za vreme logovanja.', 'bazaar-lite' ); ?>" /></p>
	<div class="clear"></div>

	<p class="form-row"><input type="submit" class="button" name="track" value="<?php _e( 'Prati', 'bazaar-lite' ); ?>" /></p>
	<?php wp_nonce_field( 'woocommerce-order_tracking' ); ?>

</form>
