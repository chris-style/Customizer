<?php

FPD_Admin_Modal::output_header(
	'fpd-modal-shortcodes',
	__('Shortcode Builder', 'radykal'),
	''
);

?>
<table class="form-table radykal-form-settings">
	<thead>
		<tr>
			<td><?php _e('Shortcode', 'radykal'); ?></td>
			<td><?php _e('Attribute(s)', 'radykal'); ?></td>
		</tr>
	</thead>
	<tbody>
		<tr valign="top">
			<td>
				<textarea readonly="readyonly" id="fpd-sc-pd">[fpd] [fpd_form]</textarea>
				<p class="description"><?php _e('Place the product designer anywhere you want with these two shortcodes.<br>NOT NECESARRY FOR Woo PRODUCTS!', 'radykal'); ?></p>
			</td>
			<td>
				<h5><?php _e('Price Format (%d is the placeholder for the price)', 'radykal'); ?></h5>
				<input type="text" placeholder="<?php _e('e.g. $%d', 'radykal'); ?>" id="fpd-sc-pd-price" />
			</td>
		</tr>
		<tr valign="top">
			<td>
				<textarea readonly="readyonly" id="fpd-sc-action"></textarea>
				<p class="description"><?php _e('Place an action button anywhere in your page.', 'radykal'); ?></p>
			</td>
			<td id="fpd-action-attr">
				<div>
					<h5><?php _e('Type', 'radykal'); ?></h5>
					<select id="fpd-sc-action-type">
						<option disabled selected value><?php _e('Select Type', 'radykal'); ?></option>
					</select>
				</div>
				<div>
					<h5><?php _e('Layout', 'radykal'); ?></h5>
					<select id="fpd-sc-action-layout">
						<option disabled selected value><?php _e('Select Layout', 'radykal'); ?></option>
						<option value="icon-tooltip"><?php _e('Icon Tooltip', 'radykal'); ?></option>
						<option value="icon-text"><?php _e('Icon Text', 'radykal'); ?></option>
						<option value="text"><?php _e('Text', 'radykal'); ?></option>
					</select>
				</div>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<textarea readonly="readyonly" id="fpd-sc-module"></textarea>
				<p class="description"><?php _e('Place a module anywhere in your page.', 'radykal'); ?></p>
			</td>
			<td>
				<h5><?php _e('Module', 'radykal'); ?></h5>
				<select id="fpd-sc-module-type" style="margin-bottom: 10px;">
					<option disabled selected value><?php _e('Select Module', 'radykal'); ?></option>
					<option value="products"><?php _e('Products', 'radykal'); ?></option>
					<option value="images"><?php _e('Images', 'radykal'); ?></option>
					<option value="designs"><?php _e('Designs', 'radykal'); ?></option>
					<option value="text"><?php _e('Text', 'radykal'); ?></option>
					<option value="manage-layers"><?php _e('Manage Layers', 'radykal'); ?></option>
					<option value="text-layers"><?php _e('Text Layers', 'radykal'); ?></option>
					<?php do_action( 'fpd_shortcode_module_options' ); ?>
				</select>
				<h5><?php _e('Wrapper CSS Style', 'radykal'); ?></h5>
				<input type="text" placeholder="<?php _e('e.g. height: 500px; width: 300px;', 'radykal'); ?>" id="fpd-sc-module-css" class="widefat" />
			</td>
		</tr>
	</tbody>
</table>

<?php

	FPD_Admin_Modal::output_footer();

?>
