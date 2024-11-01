<?php
if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}

if(!current_user_can('manage_woocommerce')){
	wp_die(__('You do not have sufficient permissions to access this page.'));
}
if(isset($_POST['wooexp-currency']) && $_POST['wooexp-currency']){
    if(check_admin_referer('wooexp-currency_nonce', 'wooexp-currency') && wp_verify_nonce(sanitize_text_field($_POST['wooexp-currency']),'wooexp-currency_nonce')){
	    $api = isset($_POST['api-key']) ? sanitize_text_field($_POST['api-key']) : '';
	    update_option('_wooexp_currency',array('api'=>trim($api),'interval'=>'daily','symbol'=>'symbol-code'));
	    $this->wooexp_currency = array('api'=>$api,'interval'=>'daily','symbol'=>'symbol-code');
	    echo '<div class="notice notice-success is-dismissible">';
	    echo '<p>'.__('Setting saved!','wooexp-currency').'</p>';
	    echo '</div>';
    }
}
?>
<div class="wrap">
	<h2><?php echo __('Multi Currency','wooexp-currency'); ?></h2>
	<form method="post" action="<?php echo admin_url('admin.php?page=wooexp-currency'); ?>">
		<table class="form-table form-table-currency" data-default="<?php echo $this->currency; ?>" data-currencies="<?php echo htmlspecialchars(wp_json_encode($this->currencies)); ?>" data-pos="<?php echo htmlspecialchars(wp_json_encode($this->currencies_pos)); ?>">
			<tr valign="top">
				<th scope="row"><?php echo __('Api Key','wooexp-currency'); ?></th>
				<td><input type="text" name="api-key" value="<?php echo isset($this->wooexp_currency['api']) ? $this->wooexp_currency['api'] : ''; ?>"><p>Get Free Api Key from <a target="_blank" href="https://fixer.io/signup/free">Fixer.io</a></p></td>
			</tr>
            <tr valign="top">
                <th scope="row"><?php echo __('Currency symbol','wooexp-currency'); ?></th>
                <td>
                    <select name="currency-symbol" disabled>
                        <option value="code"><?php echo __('Display Only Currency Code','wooexp-currency'); ?></option>
                        <option value="symbol"><?php echo __('Display Only Currency Symbol','wooexp-currency'); ?></option>
                        <option value="symbol-code" selected><?php echo __('Display Currency Symbol & Code','wooexp-currency'); ?></option>
                    </select> <span class="unlock-pro"><a href="https://wpexpertshub.com/plugins/multi-currency-pro-for-woocommerce/"><?php echo __('Unlock this in Pro Version','wooexp-currency'); ?></a></span>
                    <p><?php echo __('Display currency symbol in menu dropdown.','wooexp-currency'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __('Update Rates','wooexp-currency'); ?></th>
                <td>
                    <select name="update_rates" disabled>
                        <option value="daily" selected><?php echo __('Daily','wooexp-currency'); ?></option>
                    </select><span class="unlock-pro"><a href="https://wpexpertshub.com/plugins/multi-currency-pro-for-woocommerce/"><?php echo __('Unlock this in Pro Version','wooexp-currency'); ?></a></span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __('Polylang integration','wooexp-currency'); ?></th>
                <td>
                    <input type="checkbox" name="polylang-integration" value="1" disabled style="opacity:1;">
                    <span class="unlock-pro"><a href="https://wpexpertshub.com/plugins/multi-currency-pro-for-woocommerce/"><?php echo __('Unlock this in Pro Version','wooexp-currency'); ?></a></span>
                </td>
            </tr>
            <tr valign="top">
				<th scope="row" colspan="2"><?php echo __('Currencies','wooexp-currency'); ?></th>
			</tr>
			<tr valign="top">
				<th scope="row" colspan="2" class="wooexp-currency-top">
					<table class="wooexp-currency-list">
						<thead>
						<tr>
							<th><?php echo __('Currency','wooexp-currency'); ?></th>
							<th><?php echo __('Currency Position','wooexp-currency'); ?></th>
							<th><?php echo __('Thousand Separator','wooexp-currency'); ?></th>
							<th><?php echo __('Decimal Separator','wooexp-currency'); ?></th>
							<th><?php echo __('Number of Decimals','wooexp-currency'); ?></th>
							<th><?php echo sprintf(__('Exchange Rate (%s)','wooexp-currency'),$this->currency); ?></th>
							<th><?php echo __('Auto update','wooexp-currency'); ?></th>
							<th><?php echo __('Action','wooexp-currency'); ?></th>
						</tr>
						</thead>
                        <?php
                        echo Wooexperts_Multi_Currency_Init()->get_currency_table_body();
                        ?>
						<tfoot>
						<td colspan="8">
							<div class="wooexp-currency-foot">
                                <div class="wooexp-currency-notice"></div>
                                <span class="wooexp-currency-rate-time"><?php echo __('Last Updated: ','wooexp-currency').'<span>'.Wooexperts_Multi_Currency_Init()->get_last_update_time(); ?></span></span>
								<button data-security="<?php echo wp_create_nonce('wooexp-currency-ajax'); ?>" type="button" class="button button-secondary wooexp-currency-rates"><?php echo __('Update Rates','wooexp-currency'); ?></button>
								<button type="button" class="button button-primary wooexp-currency-new"><?php echo __('Add New Currency','wooexp-currency'); ?></button>
							</div>
						</td>
						</tfoot>
					</table>
				</th>
			</tr>
		</table>
		<input type="hidden" name="wooexp-currency" value="1">
        <?php wp_nonce_field( 'wooexp-currency_nonce','wooexp-currency'); ?>
		<?php submit_button(); ?>
	</form>
</div>
