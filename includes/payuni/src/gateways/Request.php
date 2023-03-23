<?php
/**
 * Payuni_Payment_Request class file
 *
 * @package payuni
 */

namespace PAYUNI\Gateways;

defined( 'ABSPATH' ) || exit;

/**
 * Generates payment form and redirect to payuni
 */
class Request {

	/**
	 * The gateway instance
	 *
	 * @var WC_Payment_Gateway
	 */
	protected $gateway;

	/**
	 * Constructor
	 *
	 * @param  WC_Payment_Gateway $gateway The payment gateway instance.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * Build transaction args.
	 *
	 * @param WC_Order $order The order object.
	 * @return array
	 */
	public function get_transaction_args( $order ) {

		$args = apply_filters(
			'payuni_transaction_args_' . $this->gateway->id,
			array(
				'MerID'      => $this->gateway->get_mer_id(),
				'MerTradeNo' => $order->get_order_number(),
				'TradeAmt'   => $order->get_total(),
				'Timestamp'  => time(),
				'UsrMail'    => $order->get_billing_email(),
				'ProdDesc'   => '商品名稱',
			),
			$order
		);

		$parameter['MerID']       = $this->gateway->get_mer_id();
		$parameter['Version']     = '1.0';
		$parameter['EncryptInfo'] = \Payuni\APIs\Payment::encrypt( $args );
		$parameter['HashInfo']    = \Payuni\APIs\Payment::hash_info( $parameter['EncryptInfo'] );

		return $parameter;
	}

	/**
	 * Generate the form and redirect to PayNow
	 *
	 * @param WC_Order $order The order object.
	 * @return void
	 */
	public function build_request( $order ) {
		$options = array(
			'method'  => 'POST',
			'timeout' => 60,
			'body'    => $this->get_transaction_args( $order ),
		);

		$response = wp_remote_request( $this->gateway->get_api_url() . $this->gateway->get_api_endpoint_url(), $options );

		$resp = json_decode( wp_remote_retrieve_body( $response ) );

		Response::init( $resp );
	}
}
