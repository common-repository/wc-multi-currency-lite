<tbody>
<tr>
	<td><?php echo get_option('woocommerce_currency'); ?></td>
	<td><?php echo get_option('woocommerce_currency_pos','left'); ?></td>
	<td><?php echo get_option('woocommerce_price_thousand_sep',','); ?></td>
	<td><?php echo get_option('woocommerce_price_decimal_sep','.'); ?></td>
	<td><?php echo get_option('woocommerce_price_num_decimals','2'); ?></td>
	<td><?php echo '1'; ?></td>
	<td><input name="update" type="checkbox" disabled></td>
	<td><a class="wooexp-default-currency-remove" href="javascript:void(0);"><?php echo __('Remove','wooexp-currency'); ?></a></td>
</tr>
<?php
$i=1;
if(is_array($wooexp_currencies) && !empty($wooexp_currencies)){
    $security = wp_create_nonce('wooexp-currency-ajax');
	foreach($wooexp_currencies as $currency=>$currency_opts){
		$checked = isset($currency_opts['auto_update']) && $currency_opts['auto_update'] ? 'checked' : '';
		echo '<tr>';
		echo '<td>'.$currency.'</td>';
		echo '<td>'.$currency_opts['currency_pos'].'</td>';
		echo '<td>'.$currency_opts['thousand_sep'].'</td>';
		echo '<td>'.$currency_opts['decimal_sep'].'</td>';
		echo '<td>'.$currency_opts['decimal_num'].'</td>';
		echo '<td>'.$currency_opts['exchange_rate'].'</td>';
		echo '<td><input name="update" type="checkbox" '.$checked.'></td>';
		echo '<td><a data-security="'.$security.'" data-curr="'.$currency.'" class="remove-wooexp-currency" href="javascript:void(0);">'.__('Remove','wooexp-currency').'</a></td>';
		echo '</tr>';
		$i++;
		if($i>3){
			Wooexperts_Multi_Currency_Init()->update_currency($currency);
        }
	}
}
?>
</tbody>
