<?php

/**
 * @wordpress-plugin
 * Plugin Name:       好用版擴充 MorePower Addon for WooCommerce
 * Plugin URI:        https://morepower.club/morepower-addon/
 * Description:       WooCommerce 好用版擴充，改善結帳流程與可變商品等區塊，讓 WooCommerce 更符合亞洲人使用習慣。
 * Version:           3.0.8
 * Author:            MorePower
 * Author URI:        https://morepower.club
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woomp
 * Domain Path:       /languages
 * WC requires at least: 5
 * WC tested up to: 5.6.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}


/**
 * Check WooCommerce exist
 */
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true)) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	function require_woocommerce_notice()
	{
		echo '<div class="error"><p>好用版擴充啟用失敗，需要安裝並啟用 WooCommerce 5.3 以上版本。</p></div>';
	}

	if (is_plugin_active(plugin_basename(__FILE__))) {
		deactivate_plugins(plugin_basename(__FILE__));
		add_action('admin_notices', 'require_woocommerce_notice');

		return;
	}
	add_action('admin_notices', 'require_woocommerce_notice');

	return;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WOOMP_VERSION', '3.0.0g');
define('WOOMP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOOMP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOOMP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WOOMP_ACTIVE_PLUGINS', apply_filters('active_plugins', get_option('active_plugins')));

require WOOMP_PLUGIN_DIR . 'vendor/autoload.php';

add_action('plugins_loaded', 'check_my_cookies');
function check_my_cookies() {
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woomp-activator.php
 */
function activate_woomp()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woomp-activator.php';
	Woomp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woomp-deactivator.php
 */
function deactivate_woomp()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woomp-deactivator.php';
	Woomp_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_woomp');
register_deactivation_hook(__FILE__, 'deactivate_woomp');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-woomp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woomp()
{

	$plugin = new Woomp();
	$plugin->run();
}

run_woomp();

/**
 * 指定 WC 結帳頁模板路徑
 *
 * @param string $template      Default template file path.
 * @param string $template_name Template file slug.
 * @param string $template_path Template file name.
 *
 * @return string The new Template file path.
 */
if (get_option('wc_woomp_setting_mode', 1) === 'onepage'
  || get_option('wc_woomp_setting_mode', 1) === 'twopage') {
	add_filter('wc_get_template', 'intercept_wc_template', 99, 3);
	function intercept_wc_template($template, $template_name, $template_path)
	{
		$template_directory = trailingslashit(plugin_dir_path(__FILE__)) . 'woocommerce/';
		$path               = $template_directory . $template_name;

		return file_exists($path) ? $path : $template;
	}
}

// 個人資料編輯畫面改為客製化
else if (get_option('wc_woomp_setting_mode', 1) === 'protype1') {

  // 移除姓氏必填
  add_filter ('woocommerce_save_account_details_required_fields', function ($required_fields) {
    unset ($required_fields['account_last_name']);
    return $required_fields;
  }, 99);

  // 使用客製化表單調整顯示
  add_filter('wc_get_template', function ($template, $template_name, $template_path) {

    if ($template_name == 'myaccount/form-edit-account.php') {
		  $template_directory = trailingslashit(plugin_dir_path(__FILE__)) . 'woocommerce/';
		  $path = $template_directory . $template_name;

      // 使用客製化模版時，調整必填欄位
      if (file_exists($path))
		    return $path;
    }

		return $template;

	}, 99, 3);
}

/**
 * 加入更新機制
 */
new WooMP_Updater();

/**
 * 引入 ry-woocommerce-tools
 */

if (!defined('RY_WT_VERSION')) {
	define('RY_WT_VERSION', '1.0.0');
	define('RY_WT_PLUGIN_URL', plugin_dir_url(__FILE__) . 'includes/ry-woocommerce-tools/');
	define('RY_WT_PLUGIN_DIR', plugin_dir_path(__FILE__) . 'includes/ry-woocommerce-tools/');
	define('RY_WT_PLUGIN_BASENAME', plugin_basename(__FILE__) . 'includes/ry-woocommerce-tools/');

	require_once RY_WT_PLUGIN_DIR . 'class.ry-wt.main.php';

	register_activation_hook(__FILE__, array('RY_WT', 'plugin_activation'));
	register_deactivation_hook(__FILE__, array('RY_WT', 'plugin_deactivation'));

	add_action('init', array('RY_WT', 'init'), 10);
}

/**
 * 引入 paynow-payment
 */
if (!defined('PAYNOW_PLUGIN_URL') && 'yes' === get_option('wc_woomp_setting_paynow_gateway')) {
	define('PAYNOW_PLUGIN_URL', plugin_dir_url(__FILE__) . 'includes/paynow-payment/');
	define('PAYNOW_PLUGIN_DIR', plugin_dir_path(__FILE__) . 'includes/paynow-payment/');
	define('PAYNOW_BASENAME', plugin_basename(__FILE__) . 'includes/paynow-payment/');

	/**
	 * Run PayNow Payment plugin.
	 *
	 * @return void
	 */
	function run_paynow_payment()
	{
		if (!class_exists('WC_Payment_Gateway')) {
			wp_die('WC_Payment_Gateway not found');
		}

		require_once PAYNOW_PLUGIN_DIR . 'includes/class-paynow-payment.php';
		Paynow_Payment::init();
	}

	add_action('plugins_loaded', 'run_paynow_payment');
}

/**
 * 引入 paynow-shipping
 */
if (!defined('PAYNOW_SHIPPING_PLUGIN_URL') && 'yes' === get_option('wc_woomp_setting_paynow_shipping')) {

	define('PAYNOW_SHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__) . 'includes/paynow-shipping/');
	define('PAYNOW_SHIPPING_PLUGIN_DIR', plugin_dir_path(__FILE__) . 'includes/paynow-shipping/');
	define('PAYNOW_SHIPPING_BASENAME', plugin_basename(__FILE__) . 'includes/paynow-shipping/');
	define('PAYNOW_SHIPPING_TEMPLATE_DIR', plugin_dir_path(__FILE__) . 'includes/paynow-shipping//templates/');

	/**
	 * Add PayNow shipping methods.
	 *
	 * @param array $methods Payment methods.
	 *
	 * @return array
	 */
	function add_paynow_shipping_methods($methods)
	{
		$methods['paynow_shipping_c2c_711']    = 'PayNow_Shipping_C2C_711';
		$methods['paynow_shipping_c2c_family'] = 'PayNow_Shipping_C2C_Family';
		$methods['paynow_shipping_c2c_hilife'] = 'PayNow_Shipping_C2C_Hilife';
		$methods['paynow_shipping_hd_tcat']    = 'PayNow_Shipping_HD_TCat';

		return $methods;
	}


	/**
	 * Initialize PayNow shipping.
	 *
	 * @return void
	 */
	function run_paynow_shipping()
	{
		if (!class_exists('WC_Shipping_Method')) {
			return;
		}

		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/class-paynow-shipping.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/admin/meta-boxes/class-paynow-shipping-order-meta-box.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/admin/meta-boxes/class-paynow-shipping-order-admin.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/utils/class-paynow-shipping-logistic-service.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/utils/class-paynow-shipping-order-meta.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/utils/class-paynow-shipping-status.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/utils/paynow-shipping-functions.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/shippings/abstract-paynow-shipping.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/shippings/class-paynow-shipping-c2c-711.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/shippings/class-paynow-shipping-c2c-family.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/shippings/class-paynow-shipping-c2c-hilife.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/shippings/class-paynow-shipping-hd-tcat.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/shippings/api/class-paynow-shipping-request.php';
		include_once PAYNOW_SHIPPING_PLUGIN_DIR . 'includes/shippings/api/class-paynow-shipping-response.php';

		add_filter('woocommerce_shipping_methods', 'add_paynow_shipping_methods');

		PayNow_Shipping_Order_Admin::instance();
		PayNow_Shipping::init();
		PayNow_Shipping_Request::init();
		PayNow_Shipping_Response::init();
	}

	add_action('plugins_loaded', 'run_paynow_shipping');
}

/**
 * 引入 paynow-invoice
 */
if (!defined('PAYNOW_EINVOICE_PLUGIN_URL') && 'yes' === get_option('wc_settings_tab_active_paynow_einvoice')) {
	define('PAYNOW_EINVOICE_PLUGIN_URL', plugin_dir_url(__FILE__) . 'includes/paynow-einvoice/');
	define('PAYNOW_EINVOICE_PLUGIN_DIR', plugin_dir_path(__FILE__) . 'includes/paynow-einvoice/');
	define('PAYNOW_EINVOICE_BASENAME', plugin_basename(__FILE__) . 'includes/paynow-einvoice/');

	/**
	 * Currently plugin version.
	 * Start at version 1.0.0 and use SemVer - https://semver.org
	 * Rename this for your plugin and update it as you release new versions.
	 */
	define('PAYNOW_EINVOICE_VERSION', '1.0.0');

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-paynow-einvoice-activator.php
	 */
	function activate_paynow_einvoice()
	{
		require_once PAYNOW_EINVOICE_PLUGIN_DIR . 'includes/class-paynow-einvoice-activator.php';
		Paynow_Einvoice_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-paynow-einvoice-deactivator.php
	 */
	function deactivate_paynow_einvoice()
	{
		require_once PAYNOW_EINVOICE_PLUGIN_DIR . 'includes/class-paynow-einvoice-deactivator.php';
		Paynow_Einvoice_Deactivator::deactivate();
	}

	register_activation_hook(__FILE__, 'activate_paynow_einvoice');
	register_deactivation_hook(__FILE__, 'deactivate_paynow_einvoice');

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require_once PAYNOW_EINVOICE_PLUGIN_DIR . 'includes/class-paynow-einvoice.php';


	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_paynow_einvoice()
	{

		$plugin = new Paynow_Einvoice();
		$plugin->run();
	}

	run_paynow_einvoice();
}

/**
 * 引入 line-pay
 */
require_once WOOMP_PLUGIN_DIR . 'includes/line-pay-for-woo/line-pay-for-woo.php';

/**
 * 引入 wmp-ecpay-invoice
 */
require_once WOOMP_PLUGIN_DIR . 'includes/woomp-ecpay-invoice/woomp-ecpay-invoice.php';

/**
 * 引入 wmp-ezpay-invoice
 */
require_once WOOMP_PLUGIN_DIR . 'includes/woomp-ezpay-invoice/woomp-ezpay-invoice.php';

/**
 * 引入 woomp-paynow-shipping
 */
if (!defined('WOOMP_PAYNOW_SHIPPING_PLUGIN_URL') && 'yes' === get_option('wc_woomp_setting_paynow_shipping')) {

	define('WOOMP_PAYNOW_SHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__) . 'includes/woomp-paynow-shipping/');
	define('WOOMP_PAYNOW_SHIPPING_PLUGIN_DIR', plugin_dir_path(__FILE__) . 'includes/woomp-paynow-shipping/');
	define('WOOMP_PAYNOW_SHIPPING_BASENAME', plugin_basename(__FILE__) . 'includes/woomp-paynow-shipping/');
	define('WOOMP_PAYNOW_SHIPPING_VERSION', '1.0.0');

	require_once WOOMP_PLUGIN_DIR . 'includes/woomp-paynow-shipping/woomp-paynow-shipping.php';
}

/**
 * 引入支付連
 */
if (!in_array('PChomePay-Cart-for-WooCommerce/pchomepay.php', WOOMP_ACTIVE_PLUGINS, true) && !in_array('PChomePay-Cart-for-WooCommerce-master/pchomepay.php', WOOMP_ACTIVE_PLUGINS, true)) {
	require_once WOOMP_PLUGIN_DIR . 'includes/PChomePay-Cart-for-WooCommerce/PChomePay.php';
}

if (in_array('woomp/woomp.php', WOOMP_ACTIVE_PLUGINS, true)) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	if (is_plugin_active('PChomePay-Cart-for-WooCommerce/pchomepay.php') || is_plugin_active('PChomePay-Cart-for-WooCommerce/pchomepay-master.php')) {
		deactivate_plugins('PChomePay-Cart-for-WooCommerce/pchomepay.php');
		deactivate_plugins('PChomePay-Cart-for-WooCommerce/pchomepay-master.php');
		function require_woocommerce_notice()
		{
			echo '<div class="notice notice-warning"><p>PChomePay Gateway for WooCommerce 已停用，請使用好用版擴充支付連金流</p></div>';
		}

		add_action('admin_notices', 'require_woocommerce_notice');

		return;
	}
}

/**
 * 引入統一金物流
 */
require_once WOOMP_PLUGIN_DIR . 'includes/payuni/payuni.php';
