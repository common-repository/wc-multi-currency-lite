<?php
/*
Plugin Name: Multi Currency Lite For WooCommerce
Plugin URI: https://wpexpertshub.com
Description: Allows your customers to switch to their preferred currency and place order.
*Author: WpExperts Hub
*Version: 1.1
*Author URI: https://wpexpertshub.com
*Text Domain: wooexp-currency
*License: GPLv3
*WC requires at least: 5.4
*WC tested up to: 6.7
*Requires at least: 5.4
*Tested up to: 6.0
*Requires PHP: 7.2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if(!class_exists('Wooexperts_Multi_Currency')){

    class Wooexperts_Multi_Currency{

	    private $currency = '';
    	private $currencies = array();
    	private $currencies_pos = array();
    	private $wooexp_currencies = array();
	    private $default_currency_settings = array();
	    private $wooexp_currency = array();

	    protected static $_instance = null;

	    public static function instance(){
		    if (is_null(self::$_instance)) {
			    self::$_instance = new self();
		    }
		    return self::$_instance;
	    }

	    function __construct(){
            if(!defined('WOOEXP_MUTLTI_CURRENCY')){
                define('WOOEXP_MUTLTI_CURRENCY',1.1);
            }
		    register_deactivation_hook(__FILE__,array($this,'wooexp_currency_deactivation'));
		    add_action('wooexp_currency_loaded',array($this,'init'));
		    add_action('admin_init',array($this,'admin_init'));
		    add_action('wp',array($this,'check_rates'));
		    add_action('wooexp_currency_rates',array($this,'check_rates_init'));
	        add_action('admin_menu',array($this,'admin_menu'));
	        add_action('admin_enqueue_scripts',array($this,'admin_scripts'),999);
		    add_action('wp_enqueue_scripts',array($this,'front_scripts'),999);
	        add_action('wp_ajax_add_new_currency',array($this,'add_new_currency'));
		    add_action('wp_ajax_wooexp_currency_update_rates',array($this,'update_rates'));
		    add_action('wp_ajax_remove_currency',array($this,'remove_currency'));
		    add_action('admin_head-nav-menus.php',array($this,'add_currency_menu'));
		    add_filter('wp_get_nav_menu_items',array($this,'currency_switcher_menu'));
		    add_filter('nav_menu_link_attributes',array($this,'currency_nav_menu_link_attributes'),10,2);
		    add_filter('woocommerce_currency',array($this,'woo_set_currency'),999,1);
		    add_filter('woocommerce_price_format',array($this,'woo_set_price_format'),999,1);
		    add_filter('wc_price_args',array($this,'wooexp_currency_wc_price_args'),999,1);
			add_filter('formatted_woocommerce_price',array($this,'wooexp_currency_price'),999,5);
		    add_filter('plugin_action_links_'.plugin_basename(__FILE__),array($this,'wooexp_currency_links'),10,1);
		    do_action('wooexp_currency_loaded');
        }

        function admin_init(){
	        $this->currencies = get_woocommerce_currencies();
        }

	    function init(){

		    $this->wooexp_currency = get_option('_wooexp_currency');
		    $wooexp_currencies = get_option('_wooexp_currencies');
		    $this->wooexp_currencies = is_array($wooexp_currencies) && !empty($wooexp_currencies) ? $wooexp_currencies : array();
		    $this->currency = get_option('woocommerce_currency');
		    $this->currencies_pos = array(
			    'left'        => __('Left','wooexp-currency'),
			    'right'       => __('Right','wooexp-currency'),
			    'left_space'  => __('Left with space','wooexp-currency'),
			    'right_space' => __('Right with space','wooexp-currency'),
		    );

		    $default_currencey[$this->currency] = array(
		    	'currency_pos'=>get_option('woocommerce_currency_pos','left'),
			    'thousand_sep'=>get_option('woocommerce_price_thousand_sep',','),
			    'decimal_sep'=>get_option('woocommerce_price_decimal_sep','.'),
			    'decimal_num'=>get_option('woocommerce_price_num_decimals','2'),
			    'exchange_rate'=>1,
			    'auto_update'=>false,
			    'is_default'=>true,
		    );
		    if(is_array($this->wooexp_currencies) && !empty($this->wooexp_currencies)){
			    $this->wooexp_currencies = array_merge($default_currencey,$this->wooexp_currencies);
		    }
		    $default = $this->get_default_currency();
		    $this->default_currency_settings = isset($this->wooexp_currencies[$default]) ? $this->wooexp_currencies[$default] : $default_currencey[$this->currency];
		    $this->set_currencey_data();
	    }

        function admin_scripts(){
	        $screen = get_current_screen();
	        if($screen->id == 'woocommerce_page_wooexp-currency'){
		        wp_enqueue_style('wooexp-currency-style',$this->plugin_url().'/assets/css/admin.css');
		        wp_enqueue_style('fancybox',$this->plugin_url().'/assets/css/jquery.fancybox.min.css');
		        wp_enqueue_script('fancybox',$this->plugin_url().'/assets/js/jquery.fancybox.min.js',array('jquery'),WOOEXP_MUTLTI_CURRENCY,true);
		        wp_enqueue_script('jquery-blockui',$this->plugin_url().'/assets/js/jquery.blockUI.js',array('jquery'),WOOEXP_MUTLTI_CURRENCY,true);
		        wp_register_script('wooexp-currency-script',$this->plugin_url().'/assets/js/admin.js',array('jquery'),WOOEXP_MUTLTI_CURRENCY,true);
		        $trans = array(
			        'ajax_url' => admin_url('admin-ajax.php'),
			        'ajax_nonce' => wp_create_nonce('wooexp-currency-ajax'),
			        'curr_head' => __('Add New Currency','wooexp-currency'),
			        'pro_text' => __('Get pro version to add more currencies.','wooexp-currency'),
			        'curr_select' => __('Select Currency','wooexp-currency'),
			        'curr_pos' => __('Currency Position','wooexp-currency'),
			        'thousand_sep' => __('Thousand Separator','wooexp-currency'),
			        'decimal_sep' => __('Decimal Separator','wooexp-currency'),
			        'decimal_num' => __('Number of Decimals','wooexp-currency'),
			        'exchange_rate' => sprintf(__('Exchange Rate (%s)','wooexp-currency'),$this->currency),
			        'auto_update' => __('Enable Auto update','wooexp-currency'),
			        'add_currency' => __('Add Currency','wooexp-currency'),
		        );

		        wp_localize_script('wooexp-currency-script','wooexp_curr',$trans);
		        wp_enqueue_script('wooexp-currency-script');
	        }
        }

	    function wooexp_currency_links($links){
		    $new_links = array(
			    '<a href="'.admin_url('admin.php?page=wooexp-currency').'">'.__('Settings','wooexp-currency').'</a>',
		    );
		    return array_merge($new_links,$links);
	    }

	    function front_scripts(){
		    wp_enqueue_style('wooexp-currency-front',$this->plugin_url().'/assets/css/front.css');
		    wp_enqueue_script('wooexp-currency-script-front',$this->plugin_url().'/assets/js/front.js',array('jquery'),WOOEXP_MUTLTI_CURRENCY,true);
	    }

	    function admin_menu(){
		    add_submenu_page('woocommerce', __('Multi Currency','wooexp-currency'), __('Multi Currency', 'wooexp-currency'),'manage_woocommerce','wooexp-currency', array($this,'wooexp_currency_dashboard'));
	    }

	    function wooexp_currency_dashboard(){
		    include(dirname(__FILE__).'/includes/dashboard.php');
	    }

	    function plugin_url(){
		    return untrailingslashit(plugins_url('/', __FILE__));
	    }

	    function add_new_currency(){
		    $res = array('res'=>false,'notice'=>'');
		    if(check_ajax_referer('wooexp-currency-ajax','security')){
			    $wooexp_currencies = get_option('_wooexp_currencies');
			    $wooexp_currencies = is_array($wooexp_currencies) && !empty($wooexp_currencies) ? $wooexp_currencies : array();
			    $curr = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : '';
			    if(isset($_POST['currency']) && array_key_exists($curr,$this->currencies)){
				    $wooexp_currencies[$curr] = array();
			    }
			    if(isset($_POST['currency']) && array_key_exists($curr,$wooexp_currencies)){
				    $wooexp_currencies[$curr] = array(
					    'currency_pos'=>isset($_POST['currency_pos']) ? sanitize_text_field($_POST['currency_pos']) : 'left',
					    'thousand_sep'=>isset($_POST['thousand_sep']) ? sanitize_text_field($_POST['thousand_sep']) : ',',
					    'decimal_sep'=>isset($_POST['decimal_sep']) ? sanitize_text_field($_POST['decimal_sep']) : '.',
					    'decimal_num'=>isset($_POST['decimal_num']) ? sanitize_text_field($_POST['decimal_num']) : '2',
					    'exchange_rate'=>isset($_POST['exchange_rate']) ? sanitize_text_field($_POST['exchange_rate']) : 1,
					    'auto_update'=>isset($_POST['auto_update']) ? sanitize_text_field($_POST['auto_update']) : 0,
				    );
				    if(count($wooexp_currencies)<3){
					    update_option('_wooexp_currencies',$wooexp_currencies);
					    $this->set_currencey_data();
					    $res = array('res'=>true,'html'=>$this->get_currency_table_body(),'notice'=>'');
				    }
				    else
				    {
					    $notice = __('You Can add max 2 currencies in lite version.','wooexp-currency');
					    $res = array('res'=>true,'html'=>$this->get_currency_table_body(),'notice'=>$notice);
				    }
			    }
		    }


		    echo json_encode($res);
		    exit;
	    }

	    function get_currency_table_body(){
		    $wooexp_currencies = get_option('_wooexp_currencies');
		    $wooexp_currencies = is_array($wooexp_currencies) && !empty($wooexp_currencies) ? $wooexp_currencies : array();
		    ob_start();
		    include(dirname(__FILE__).'/includes/body.php');
		    $html = ob_get_clean();
		    return $html;
	    }

	    function remove_currency(){
		    $res = array('res'=>false);
		    if(check_ajax_referer('wooexp-currency-ajax','security')){
			    $wooexp_currencies = get_option('_wooexp_currencies');
			    $wooexp_currencies = is_array($wooexp_currencies) && !empty($wooexp_currencies) ? $wooexp_currencies : array();
			    $code = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : '';
			    if($code!='' && array_key_exists($code,$wooexp_currencies)){
				    unset($wooexp_currencies[$code]);
				    update_option('_wooexp_currencies',$wooexp_currencies);
				    $res = array('res'=>true,'html'=>$this->get_currency_table_body());
			    }
		    }
		    echo json_encode($res);
		    exit;
	    }

	    function update_currency($code){
		    $wooexp_currencies = get_option('_wooexp_currencies');
		    $wooexp_currencies = is_array($wooexp_currencies) && !empty($wooexp_currencies) ? $wooexp_currencies : array();
		    $res = array('res'=>false);
		    if(isset($code) && array_key_exists($code,$wooexp_currencies)){
			    unset($wooexp_currencies[$code]);
			    update_option('_wooexp_currencies',$wooexp_currencies);
		    }
	    }

	    function add_currency_menu(){
		    $param = array( 0 => 'This param will be passed to my_render_menu_metabox' );
		    add_meta_box('wooexp-currency-box',__('Currency Dropdown','wooexp-currency'), array($this,'wooexp_currency_box_init'),'nav-menus','side','default', $param );
	    }

	    function wooexp_currency_box_init(){
		    include(dirname(__FILE__).'/includes/menu.php');
	    }

	    function usort_menu_items( $a, $b ) {
		    return ( $a->menu_order < $b->menu_order ) ? -1 : 1;
	    }

	    function currency_switcher_menu($items){

		    if(doing_action('customize_register') || is_admin()){
			    return $items;
		    }
		    usort($items, array($this,'usort_menu_items'));

		    $new_items = array();
		    $offset = 0;
		    $i = 0;
		    foreach($items as $item){
			    if('wooexp_currency' == $item->type){
				    $item->title = $this->currency_menu_title($this->get_default_currency());
				    $item->currency = $this->get_default_currency();
				    $item->attr_title = '';
				    $item->url = '';
				    $item->classes = array('custom wooexp-currency-item wooexp-currency-parent');
				    $new_items[] = $item;
				    $offset++;
				    if(is_array($this->wooexp_currencies) && !empty($this->wooexp_currencies)){
				    	foreach($this->wooexp_currencies as $currency=>$currency_opts){
				    		if($this->get_default_currency()!=$currency){
				    			if($i>=2){
				    				continue;
							    }
							    $new_item = clone $item;
							    $new_item->ID = $new_item->ID . '-'.strtolower($currency);
							    $new_item->title = $this->currency_menu_title($currency);
							    $new_item->currency = $currency;
							    $new_item->attr_title = '';
							    $new_item->url = '';
							    $new_item->classes = array('custom wooexp-currency-item wooexp-currency-child');
							    $new_item->menu_item_parent = $item->db_id;
							    $new_item->db_id = 0;
							    $new_item->menu_order += $offset + $i++;
							    $new_items[] = $new_item;
							    $offset += $i - 1;
						    }
					    }
				    }
			    }
			    else
			    {
				    $item->menu_order += $offset;
				    $new_items[] = $item;
			    }
		    }
		    return $new_items;
	    }

	    function is_admin_product_edit_page(){
		    global $pagenow;
		    if ( is_admin() && 'post.php' === $pagenow && isset( $_GET['action'] ) && 'edit' === $_GET['action'] && 'product' === get_post_type() ) {
			    return true;
		    } elseif ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && 'woocommerce_load_variations' === $_REQUEST['action'] ) {
			    return true;
		    } else {
			    return false;
		    }
	    }

	    function get_default_currency(){

	    	if($this->is_admin_product_edit_page()){
			    return $this->currency;
		    }
		    elseif(isset($_COOKIE['wooexp_currency']) && array_key_exists($_COOKIE['wooexp_currency'],$this->wooexp_currencies)){
			    return $_COOKIE['wooexp_currency'];
		    }
		    else
		    {
			    return $this->currency;
		    }
	    }

	    function currency_nav_menu_link_attributes($atts,$item){
		    if(isset($item->currency)){
			    $atts['data-currency'] = esc_attr($item->currency);
		    }
		    return $atts;
	    }

	    function woo_set_currency($currency){
			return $this->get_default_currency();
	    }

	    function woo_set_price_format($format){
	    	if(isset($this->default_currency_settings['currency_pos']) && $this->default_currency_settings['currency_pos']){
			    $currency_pos = $this->default_currency_settings['currency_pos'];
			    switch ( $currency_pos ) {
				    case 'left':
					    $format = '%1$s%2$s';
					    break;
				    case 'right':
					    $format = '%2$s%1$s';
					    break;
				    case 'left_space':
					    $format = '%1$s&nbsp;%2$s';
					    break;
				    case 'right_space':
					    $format = '%2$s&nbsp;%1$s';
					    break;
			    }
		    }
		    return $format;
	    }

	    function wooexp_currency_wc_price_args($args){
	    	if(isset($this->default_currency_settings['decimal_sep']) && $this->default_currency_settings['decimal_sep']){
			    $args['decimal_separator'] = stripslashes($this->default_currency_settings['decimal_sep']);
		    }
		    if(isset($this->default_currency_settings['thousand_sep']) && $this->default_currency_settings['thousand_sep']){
			    $args['thousand_separator'] = stripslashes($this->default_currency_settings['thousand_sep']);
		    }
		    if(isset($this->default_currency_settings['decimal_num']) && $this->default_currency_settings['decimal_num']){
			    $args['decimals'] = absint($this->default_currency_settings['decimal_num']);
		    }
		    if(isset($this->default_currency_settings['currency_pos'])){
			    switch($this->default_currency_settings['currency_pos']){
				    case 'left':
					    $args['price_format'] = '%1$s%2$s';
					    break;
				    case 'right':
					    $args['price_format'] = '%2$s%1$s';
					    break;
				    case 'left_space':
					    $args['price_format'] = '%1$s&nbsp;%2$s';
					    break;
				    case 'right_space':
					    $args['price_format'] = '%2$s&nbsp;%1$s';
					    break;
			    }
		    }
		    return $args;
	    }

	    function wooexp_currency_price($number_format,$price,$decimals,$decimal_separator,$thousand_separator){
	    	if(isset($this->default_currency_settings['exchange_rate']) && !isset($this->default_currency_settings['is_default'])){
	    		$price = $price*$this->default_currency_settings['exchange_rate'];
			    $number_format = number_format($price,$decimals,$decimal_separator,$thousand_separator);
		    }
		    return $number_format;
	    }

	    function curl_get($url,$headers = array('Accept:application/json')){
		    $curl = curl_init();
		    curl_setopt_array($curl, array(
			    CURLOPT_TIMEOUT =>15,
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_CUSTOMREQUEST => 'GET',
			    CURLOPT_URL => $url,
			    CURLOPT_HTTPHEADER =>$headers
		    ));
		    $response = curl_exec($curl);
		    curl_close($curl);
		    $res = json_decode($response,true);
		    return $res;
	    }

	    function wooexp_currency_deactivation(){
		    wp_clear_scheduled_hook('wooexp_currency_rates');
	    }

	    function check_rates(){
		    if(!wp_next_scheduled('wooexp_currency_rates')){
			    wp_schedule_event(time(),'daily','wooexp_currency_rates');
		    }
	    }

	    function check_rates_init(){
		    $this->get_rates();
	    }

	    function update_rates(){

		    $res = array('res'=>false);
		    if(check_ajax_referer('wooexp-currency-ajax','security')){
			    $this->get_rates();
			    $res = array('res'=>true,'html'=>$this->get_currency_table_body(),'time'=>$this->get_last_update_time());
		    }
		    echo json_encode($res);
		    exit;
	    }

	    function get_last_update_time(){
		    $last_updated = get_option('_wooexp_currencies_last_update');
		    if($last_updated==''){
			    $last_updated = 'Never';
		    }
		    else
		    {
			    $last_updated = date('F j, Y g:i:s A',$last_updated);
		    }
		    return $last_updated;
	    }

	    function get_rates(){

		    $update_require = array();
		    $options = array();
		    if(is_array($this->wooexp_currencies) && !empty($this->wooexp_currencies)){
			    foreach($this->wooexp_currencies as $currency=>$currency_options){
				    if(!isset($currency_options['is_default'])){
					    $options[$currency]=$currency_options;
					    if(isset($currency_options['auto_update']) && $currency_options['auto_update']){
						    $update_require[]=$currency;
					    }
				    }
			    }
		    }

		    if(is_array($update_require) && !empty($update_require)){
			    if(isset($this->wooexp_currency['api']) &&  $this->wooexp_currency['api']!=''){
				    $url = 'https://api.apilayer.com/fixer/latest?base=USD&symbols='.implode(',',$update_require);
				    $header = array('apikey:'.$this->wooexp_currency['api']);
				    $rates = $this->curl_get($url,$header);
					$timestamp = current_time('timestamp',0);
				    if(isset($rates['success']) && $rates['success']){
					    $rates['rates'][$this->currency]=1;
					    if(is_array($rates['rates']) && !empty($rates['rates']) && array_key_exists($this->currency,$rates['rates'])){
						    $base_rate = $rates['rates'][$this->currency];
						    $timestamp = $rates['timestamp'];
						    foreach($rates['rates'] as $curr=>$rate){
							    if(in_array($curr,$update_require)){
								    $r = $rate/$base_rate;
								    $options[$curr]['exchange_rate'] = round($r,absint($options[$curr]['decimal_num']));
							    }
						    }
					    }
				    }
			    }
			    update_option('_wooexp_currencies',$options);
			    update_option('_wooexp_currencies_last_update',$timestamp);
		    }
	    }

	    function set_currencey_data(){
		    $wooexp_currencies = get_option('_wooexp_currencies');
		    $wooexp_currencies = is_array($wooexp_currencies) && !empty($wooexp_currencies) ? $wooexp_currencies : array();
	    	if(is_array($wooexp_currencies) && !empty($wooexp_currencies)){
			    $i=1;
	    		foreach($wooexp_currencies as $code=>$options){
				    $i++;
				    if($i>3){
					    $this->update_currency($code);
				    }
			    }
		    }
	    }

	    function currency_menu_title($code){
		    $code = get_woocommerce_currency_symbol($code).' ('.$code.')';
	    	return $code;
	    }

    }
}

if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
	function Wooexperts_Multi_Currency_Init(){
		return Wooexperts_Multi_Currency::instance();
	}
	Wooexperts_Multi_Currency_Init();
}
?>