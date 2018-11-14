<?php
/**
 * WPML Helper
 *
 * Specific configuration for WPML
 *
 * @class 		WC_GZD_WPML_Helper
 * @category	Class
 * @author 		Marius Mateoc
 */
class WC_GZD_Compatibility_Woocommerce_Wholesale_Prices extends WC_GZD_Compatibility_Woocommerce_Role_Based_Pricing {

	public function __construct() {
		parent::__construct(
			'WooCommerce Wholesale Prices',
			'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php'
		);
	}

	public function calculate_unit_price( $product ) {
		// $product->recalculate_unit_price();

		// HACK: Partial implementation work only for one wholsale customer and don't calculate unit price on shop page
		$business_price = get_post_meta( $product->get_id(), 'wholesale_customer_wholesale_price', true);

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();

			$roles = ( array ) $user->roles;

			if ( in_array("wholesale_customer", $roles) && $business_price != 0 ) {
				// $base_country = WC()->countries->get_base_country();
				// $user_billing_country = WC()->customer->get_billing_country();
				// $user_shipping_country = WC()->customer->get_shipping_country();
				// $user_country = $user_shipping_country ?: $user_billing_country;

				// if ($base_country == $user_country) {

				//     if (is_cart() || is_checkout()) {
				//         // $wholesale_price = (float)$business_price;
				//         // $price_tax = $product->get_price_including_tax();
				//         // $product->recalculate_unit_price();

				//     } else {

				//         $wholesale_price = (float)$business_price;
				//         $product->recalculate_unit_price( array(
				//             'regular_price' => $wholesale_price,
				//             'price' => $wholesale_price,
				//         ) );

				//     }
				// } else {
					$wholesale_price = (float)$business_price;

					$product->recalculate_unit_price( array(
						'regular_price' => $wholesale_price,
						'price' => $wholesale_price,
					) );
				// }
			}
		} else {
			$product->recalculate_unit_price();
		}
	}
}
