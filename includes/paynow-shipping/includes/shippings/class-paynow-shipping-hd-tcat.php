<?php
/**
 * PayNow Shipping TCAT class file.
 *
 * @package paynow
 */

defined( 'ABSPATH' ) || exit;

/**
 * PayNow_Shipping_HD_TCat class.
 */
class PayNow_Shipping_HD_TCat extends PayNow_Abstract_Shipping_Method {

	/**
	 * Constructor
	 *
	 * @param integer $instance_id The instance_id.
	 */
	public function __construct( $instance_id = 0 ) {

		parent::__construct();

		$this->instance_id        = absint( $instance_id );
		$this->id                 = 'paynow_shipping_hd_tcat';
		$this->method_title       = __( 'PayNow Shipping TCat', 'paynow-shipping' );
		$this->method_description = __( 'PayNow Shipping TCat', 'paynow-shipping' );
		$this->logistic_service   = PayNow_Shipping_Logistic_Service::TCAT;

		$this->init();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * Check if this shipping method available or not.
	 *
	 * @param array $package The shipping package array.
	 * @return boolean
	 */
	public function is_available( $package ) {

		$max_amount   = 100000;
		$is_available = $this->is_enabled();

		$total = WC()->cart->get_cart_contents_total();
		if ( $total >= $max_amount ) {
			$is_available = false;
		}

		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );

	}

	/**
	 * Initialize shipping method
	 *
	 * @return void
	 */
	public function init() {

		$this->instance_form_fields = include PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/settings/settings-paynow-shipping-hd-tcat.php';
		$this->init_settings();

		$this->title                    = $this->get_option( 'title' );
		$this->cost                     = $this->get_option( 'cost' );
		$this->free_shipping_requires   = $this->get_option( 'free_shipping_requires' );
		$this->free_shipping_min_amount = $this->get_option( 'free_shipping_min_amount', 0 );
		$this->type                     = $this->get_option( 'type', 'class' );

	}

}
