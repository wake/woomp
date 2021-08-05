<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooMP_Checkout' ) ) {
	final class WooMP_Checkout {

		private static $scripts = array();

		/**
		 * 將購物車&小計置入結帳頁
		 */
		public function set_cart_in_checkout_page() {
			if ( is_wc_endpoint_url( 'order-received' ) ) {
				return;
			}
			echo do_shortcode( '[woocommerce_cart]' ); ?>
			<?php
		}

		/**
		 * 購物車轉跳結帳頁
		 */
		public function redirect_cart_page_to_checkout() {
			if ( is_cart() && WC()->cart->get_cart_contents_count() > 0 ) {
				?>
			<script>
				window.location.href="<?php echo wc_get_checkout_url(); ?>"
			</script>
				<?php
			}
		}

		/**
		 * 台灣縣市下拉選單
		 */
		public function set_tw_zipcode() {
			?>
			<script>
				jQuery(function($) {
					function updateValue( field ){
						$("#"+field+"_state").val($(".woocommerce-"+field+"-fields select[name=\'county\']").val());
						$("#"+field+"_city").val($(".woocommerce-"+field+"-fields select[name=\'district\']").val());
						$("#"+field+"_postcode").val($(".woocommerce-"+field+"-fields input[name=\'zipcode\']").val());
					}
					function updateField(field){
						$(".woocommerce-"+field+"-fields select[name=\'county\']").appendTo($("#"+field+"_state_field"));
						$(".woocommerce-"+field+"-fields select[name=\'district\']").appendTo($("#"+field+"_city_field"));
						$(".woocommerce-"+field+"-fields input[name=\'zipcode\']").appendTo($("#"+field+"_postcode_field"));
					} 
					$(document).ready(function($){
						if( $('#billing_country').val() === 'TW' && $('#shipping_country').val() === 'TW' ){
							
							$(".woocommerce-billing-fields,.woocommerce-shipping-fields").twzipcode();
							
							updateField("billing");
							updateField("shipping");
							
							$("select[name=\'county\'],select[name=\'district\']").change(function(){
								updateValue("billing");
								updateValue("shipping");
							})
	
							$("input[name=\'zipcode\']").keyup(function(){
								updateValue("billing");
								updateValue("shipping");
							})
	
							$("#billing_postcode,#billing_state,#billing_city,#shipping_postcode,#shipping_state,#shipping_city").hide();
						}
	
						$('#billing_country, #shipping_country').on('change',function(){
							if($(this).val() === 'TW'){
								$(".woocommerce-billing-fields,.woocommerce-shipping-fields").twzipcode();
								updateField("billing");
								updateField("shipping");
								$("select[name=\'county\'],select[name=\'district\'],input[name=\'zipcode\']").show();
								$("select[name=\'county\'],select[name=\'district\']").change(function(){
									updateValue("billing");
									updateValue("shipping");
								})
								$("input[name=\'zipcode\']").keyup(function(){updateValue();})
								$("#billing_postcode,#billing_state,#billing_city,#shipping_postcode,#shipping_state,#shipping_city").hide();
								$("select#billing_state + span, select#shipping_state + span").hide();
							} else {
								$("form.woocommerce-checkout").twzipcode('destory');
								$("select[name=\'county\'],select[name=\'district\'],input[name=\'zipcode\']").hide();
								$("#billing_postcode,#billing_state,#billing_city,#shipping_postcode,#shipping_state,#shipping_city").show();
								$("select#billing_state + span, select#shipping_state + span").show();
							}
						})
	
						// 同步帳單資訊與超商欄位資訊
						$('[name="billing_first_name"]').on('change',function (e) { 
							$('#shipping_first_name').val($(this).val())
						});
						$('[name="billing_last_name"]').on('change',function (e) { 
							$('#shipping_last_name').val($(this).val())
						});
						$('[name="billing_phone"]').on('change',function (e) { 
							$('#shipping_phone').val($(this).val())
						});
					})
				})
			</script>
			<?php
		}

		/**
		 * 國家欄位移到物流選擇上面
		 */
		public function set_country_to_top() {
			if ( get_option( 'wc_woomp_setting_billing_country_pos', 1 ) === 'yes' ) {
				?>
				<script>
					jQuery(function($){
					   $(document).ready(function(){
							$('#billing_country_field').prependTo('#order_review');
							$('#billing_country_field').css('margin-bottom','25px');
							$(document.body).on('updated_checkout', function (e, data) {
								if( $('#ship-to-different-address-checkbox').val() ){
									$('#shipping_country_field').prependTo('#order_review');
									$('#shipping_country_field').css('margin-bottom','25px');
									$('#billing_country_field').hide()
									$('#billing_country').val( $( '#shipping_country' ).val() )
								} else {
									$('#billing_country_field').show();
									$('#shipping_country_field').show();
								}
							})
							// 同步 billing & shipping 國家欄位
							$( '#shipping_country' ).change(function(){
								$( '#billing_country' ).val( $(this).val() )
								$('#select2-billing_country-container').text($( '#shipping_country option:selected').text())
								$('#select2-billing_country-container').attr('title',$( '#shipping_country option:selected').text())
							})
					   }) 
					})
				</script>
				<?php
			}
		}

		/**
		 * 更改購物車數量時自動更新金額
		 */
		public function set_quantity_update_cart() {
			?>
			<script>
			jQuery(function($){
				var updateCart = false;
				$(document).ready(function($){
					$('.woocommerce-cart-form').attr('action','<?php echo wc_get_checkout_url(); ?>')
					$('.woocommerce').on('change', 'input.qty', function(){
						setTimeout(() => {
							$("[name='update_cart']").trigger("click");
							updateCart = true
						}, 1000);
					});
				})
				// 處理購物車更新後，會把登入跟折價卷給吃掉
				jQuery(document.body).on('updated_cart_totals', function (e, data) {
					if( updateCart ){
						<?php if ( ! is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder', true ) ) : ?>
						$('.woocommerce-form-login-toggle').append('<div class="woocommerce-message" role="alert"><?php echo esc_html__( 'Returning customer?', 'woocommerce' ); ?> <a href="#" class="showlogin"><?php echo esc_html__( 'Click here to login', 'woocommerce' ); ?></a></div>');
						<?php endif; ?>
						<?php if ( 'yes' === get_option( 'woocommerce_enable_coupons', true ) ) : ?>
						$('.woocommerce-form-coupon-toggle').append('<div class="woocommerce-info"><?php echo esc_html__( 'Have a coupon?', 'woocommerce' ); ?> <a href="#" class="showcoupon"><?php echo esc_html__( 'Click here to enter your code', 'woocommerce' ); ?></a></div>')
						<?php endif; ?>
					}
				})
			})
			</script>
			<?php
		}

		/**
		 * 移動結帳按鈕&金流位置
		 */
		public function set_place_button_position() {
			?>
			  <script>  
			jQuery(function($){
				function placeCheckoutButton(){
					if(jQuery('#paymentWrap').length === 0){
						jQuery('table.shop_table.woocommerce-checkout-review-order-table').after('<table id="paymentWrap" style="margin-top: -24px; margin-bottom: -20px;"><tr><th style="width: 159px; font-weight: normal; padding: 0; font-size: 14px; text-align: left;">付款方式</th><td></td></tr></table>');
						jQuery('#payment').appendTo(jQuery('#paymentWrap td'))
					} else {
						jQuery( '#payment .woocommerce-terms-and-conditions-wrapper' ).remove(); // 移除重複的隱私權政策
					}
					if(jQuery('#placeOrderWrap').length === 0){
						jQuery('form.woocommerce-checkout #customer_details').append('<div id="placeOrderWrap"></div>')
						jQuery('.woocommerce-terms-and-conditions-wrapper,#place_order').appendTo(jQuery('#placeOrderWrap'));
					}
					jQuery('#paymentWrap #payment button').remove()
				}

				jQuery(document.body).on('updated_checkout', function (e, data) {
					placeCheckoutButton();
				})
			})    
			</script>
			<?php
		}

		/**
		 * woocommerce/inluces/class-wc-frontend-scripts.php
		 */
		private static function localize_script( $handle ) {
			if ( ! in_array( $handle, self::$wp_localize_scripts, true ) && wp_script_is( $handle ) ) {
				$data = self::get_script_data( $handle );

				if ( ! $data ) {
					return;
				}

				$name                        = str_replace( '-', '_', $handle ) . '_params';
				self::$wp_localize_scripts[] = $handle;
				wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
			}
		}

		/**
		 * woocommerce/inluces/class-wc-frontend-scripts.php
		 */
		private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = WC_VERSION, $in_footer = true ) {
			self::$scripts[] = $handle;
			wp_register_script( $handle, $path, $deps, $version, $in_footer );
		}

		/**
		 * woocommerce/inluces/class-wc-frontend-scripts.php
		 */
		private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = WC_VERSION, $in_footer = true ) {
			if ( ! in_array( $handle, self::$scripts, true ) && $path ) {
				self::register_script( $handle, $path, $deps, $version, $in_footer );
			}
			wp_enqueue_script( $handle );
		}

		/**
		 * woocommerce/inluces/class-wc-frontend-scripts.php
		 */
		private static function get_script_data( $handle ) {
			switch ( $handle ) {
				case 'wc-cart':
					$params = array(
						'ajax_url'                     => WC()->ajax_url(),
						'wc_ajax_url'                  => WC_AJAX::get_endpoint( '%%endpoint%%' ),
						'update_shipping_method_nonce' => wp_create_nonce( 'update-shipping-method' ),
						'apply_coupon_nonce'           => wp_create_nonce( 'apply-coupon' ),
						'remove_coupon_nonce'          => wp_create_nonce( 'remove-coupon' ),
					);
					break;
				default:
					$params = false;
			}
		}

		/**
		 * woocommerce/inluces/class-wc-frontend-scripts.php
		 */
		private static function register_scripts() {
			$register_scripts = array(
				'wc-cart' => array(
					'src'     => self::get_asset_url( 'assets/js/frontend/cart' . $suffix . '.js' ),
					'deps'    => array( 'jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n' ),
					'version' => $version,
				),
			);
		}

		/**
		 * woocommerce/inluces/class-wc-frontend-scripts.php
		 */
		public static function load_scripts() {
			if ( is_checkout() ) {
				self::enqueue_script( 'wc-cart' );
			}
		}

		/**
		 * woocommerce/inluces/class-wc-frontend-scripts.php
		 */
		public static function init() {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ), 20 );
		}



		/**
		 * 根據運送方式顯示資訊
		 */
		public function set_shipping_info() {
			?>
			<script>
			jQuery(function($){
				if( $("#CVSStoreName_field strong").text() === '' ){
					$('#ship-to-different-address-checkbox').prop('checked', false);
					$('.shipping_address').hide();
				}
				// 處理運送到不同地址取消勾選
				$('body').on('click', '#shipping_method li input', function(){
					if( 
						$('#shipping_method li input:checked').val() !== "ecpay_shipping" || 
						!$('#shipping_method li input:checked').val().includes('ry_ecpay_shipping_cvs') ||
						!$('#shipping_method li input:checked').val().includes('ry_newebpay_shipping_cvs') 
					){
						$('#ship-to-different-address-checkbox').prop('checked', false);
						$('.shipping_address').hide();
					}
				})
				function toggleBillingAddressField( status ){
					if( status === 'hide' ){
						$('.woocommerce-shipping-fields,h3#ship-to-different-address input,#billing_address_1_field,#billing_address_2_field,#billing_city_field,#billing_state_field,#billing_postcode_field').hide()
					} else {
						$('.woocommerce-shipping-fields,h3#ship-to-different-address input,#billing_address_1_field,#billing_address_2_field,#billing_city_field,#billing_state_field,#billing_postcode_field').show()
					}
				}	
				$(document.body).on('updated_checkout', function (e, data) {
					/**
					 * 針對物流方式顯示帳單與運送地址欄位
					 */
					let shippingMethodNum = $('#shipping_method li').length;
					let shippingMethodSelector = ( shippingMethodNum === 1 ) ? '' : ':checked';
					if( shippingMethodNum >= 1 ){
						if( $('#shipping_method li input' + shippingMethodSelector ).val() === "ecpay_shipping" || 
							$('#shipping_method li input' + shippingMethodSelector ).val().includes('ry_ecpay_shipping_cvs') ||
							$('#shipping_method li input' + shippingMethodSelector ).val().includes('ry_newebpay_shipping_cvs') 
						){
							toggleBillingAddressField('hide')
						} else {
							toggleBillingAddressField('show')	
						}
					}

				});
			})
			</script>
			<?php
		}

		public function set_shipping_field( $fields ) {
			$shipping_method = array(
				'ry_ecpay_shipping_cvs_711',
				'ry_ecpay_shipping_cvs_hilife',
				'ry_ecpay_shipping_cvs_family',
				'ry_newebpay_shipping_cvs',
			);

			foreach ( $shipping_method as $method ) {
				global $woocommerce;
				$chosen_methods  = wc_get_chosen_shipping_method_ids();
				$chosen_shipping = $chosen_methods[0];

				if ( $chosen_shipping == $method ) {
					$fields['billing']['billing_postcode']['required']     = false;
					$fields['billing']['billing_state']['required']        = false;
					$fields['billing']['billing_city']['required']         = false;
					$fields['billing']['billing_address_1']['required']    = false;
					$fields['shipping']['shipping_first_name']['required'] = false;
					$fields['shipping']['shipping_last_name']['required']  = false;
					$fields['shipping']['shipping_phone']['required']      = false;
				}
			}
			return $fields;
		}

		/**
		 * 修改購買按鈕文字
		 */
		public function custom_button_text( $button_text ) {
			return get_option( ' wc_woomp_setting_place_order_text' );
		}
	}

	/**
	 * 指定載入 Woo 客製範本寫外掛主檔案 woomp.php Line 93
	 */

}

$checkout = new WooMP_Checkout();

if ( 'yes' === get_option( 'wc_woomp_setting_replace', 1 ) ) {
	add_action( 'wp_head', array( $checkout, 'redirect_cart_page_to_checkout' ), 1 );
	add_action( 'woocommerce_before_checkout_form', array( $checkout, 'set_cart_in_checkout_page' ) );
	add_filter( 'woocommerce_after_checkout_form', array( $checkout, 'set_quantity_update_cart' ) );
	add_filter( 'woocommerce_after_checkout_form', array( $checkout, 'set_place_button_position' ) );
	add_filter( 'woocommerce_before_checkout_form', array( $checkout, 'set_shipping_info' ) );
	add_filter( 'woocommerce_checkout_fields', array( $checkout, 'set_shipping_field' ), 10000 );
}

if ( 'yes' === get_option( 'wc_woomp_setting_tw_address', 1 ) ) {
	add_filter( 'woocommerce_after_checkout_form', array( $checkout, 'set_tw_zipcode' ) );
}

if ( 'yes' === get_option( 'wc_woomp_setting_billing_country_pos', 1 ) ) {
	add_filter( 'woocommerce_after_checkout_form', array( $checkout, 'set_country_to_top' ) );
}

if ( ! empty( get_option( ' wc_woomp_setting_place_order_text' ) ) ) {
	add_filter( 'woocommerce_order_button_text', array( $checkout, 'custom_button_text' ), 99, 1 );
}

WooMP_Checkout::init();
