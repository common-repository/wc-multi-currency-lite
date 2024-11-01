<?php
global $_nav_menu_placeholder, $nav_menu_selected_id;
$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
?>
<div id="wooexp_currency_switch" class="posttypediv">
	<div id="tabs-panel-lang-switch" class="tabs-panel tabs-panel-active">
		<ul id="lang-switch-checklist" class="categorychecklist form-no-clear">
			<li>
				<label class="menu-item-title">
					<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php esc_html_e( 'Currency switcher', 'wooexp-currency' ); ?>
				</label>
				<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="wooexp_currency">
				<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php esc_html_e( 'Currency switcher', 'wooexp-currency' ); ?>">
				<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#wooexp_currency_switcher">
			</li>
		</ul>
	</div>
	<p class="button-controls">
		<span class="add-to-menu">
			<input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-wooexp_currency_switch">
			<span class="spinner"></span>
		</span>
	</p>
</div>
