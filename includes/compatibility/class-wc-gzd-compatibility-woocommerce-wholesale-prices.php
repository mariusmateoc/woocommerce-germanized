<?php
/**
 * WWP Helper
 *
 * Specific configuration for WooCommerce Wholesale Prices
 *
 * @class 		WC_GZD_WWP_Helper
 * @category	Class
 * @author 		Marius Mateoc
 */
class WC_GZD_Compatibility_Woocommerce_Wholesale_Prices extends WC_GZD_Compatibility {

	public function __construct() {
		parent::__construct(
			'WooCommerce Wholesale Prices',
			'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php'
		);
	}

	public function load() {
		// Add filter to price output
		add_filter( 'woocommerce_get_price_html', array( $this, 'set_unit_price_product_filter' ), 200, 3 );

		// Filter seems to be removed due to low priority
		remove_filter( 'woocommerce_cart_item_price', 'wc_gzd_cart_product_unit_price', wc_gzd_get_hook_priority( 'cart_product_unit_price' ), 3 );
		remove_filter( 'woocommerce_cart_item_subtotal', 'wc_gzd_cart_product_unit_price', wc_gzd_get_hook_priority( 'cart_subtotal_unit_price' ), 3 );

		// Readd filter with higher priority
		add_filter( 'woocommerce_cart_item_price', 'wc_gzd_cart_product_unit_price', 500, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', 'wc_gzd_cart_product_unit_price', 500, 3 );

		// Filters to recalculate unit price during cart/checkout
		add_action( 'woocommerce_before_cart', array( $this, 'set_unit_price_filter' ), 10 );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'set_unit_price_filter' ), 10 );
		add_action( 'woocommerce_before_mini_cart', array( $this, 'set_unit_price_filter' ), 10 );
		add_action( 'woocommerce_gzd_review_order_before_cart_contents', array( $this, 'set_unit_price_filter' ), 10 );

		// Recalculate unit price before adding order item meta
        add_filter( 'woocommerce_gzd_order_item_unit_price', array( $this, 'unit_price_order_item' ), 10, 4 );

	}

	public function set_unit_price_product_filter( $html, $product ) {
		$this->set_unit_price_filter();

		return $html;
	}

	public function unit_price_order_item( $price, $gzd_product, $item, $order ) {
		$product_price = $order->get_item_subtotal( $item, true );

		$gzd_product->recalculate_unit_price( array(
			'regular_price' => $product_price,
			'price' => $product_price,
		) );

		return $gzd_product->get_unit_html();
	}

	public function set_unit_price_filter() {
		add_action( 'woocommerce_gzd_before_get_unit_price', array( $this, 'calculate_unit_price' ), 10, 1 );
		// Adjust variable from-to unit prices
		add_action( 'woocommerce_gzd_before_get_variable_variation_unit_price', array( $this, 'calculate_unit_price' ), 10, 1 );
    }

	public function calculate_unit_price( $product ) {
        // $product->recalculate_unit_price();

        // HACK: Partial implementation work only for one wholsale customer and don't calculate unit price on shop page
		$business_price = get_post_meta( $product->get_id(), 'wholesale_customer_wholesale_price', true);

        if( is_user_logged_in() ) {
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
