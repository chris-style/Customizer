<?php

FPD_Admin_Modal::output_header(
	'fpd-modal-edit-product-options',
	__('Product Options', 'radykal'),
	''
);

?>

<table class="form-table">
	<tbody>

		<?php

		radykal_output_option_item( array(
				'id' => 'stage_width',
				'title' => 'Canvas Width',
				'type' => 'number',
				'class' => 'large-text',
				'placeholder' => __('Canvas width from UI Layout', 'radykal'),
			)
		);

		radykal_output_option_item( array(
				'id' => 'stage_height',
				'title' => 'Canvas Height',
				'type' => 'number',
				'class' => 'large-text',
				'placeholder' => __('Canvas height from UI Layout', 'radykal')
			)
		);

		foreach( FPD_Product::get_all_products('ID, title') as $fpd_product) {
			$fpd_layout_options[$fpd_product->ID] = '#' . $fpd_product->ID . ' - ' . $fpd_product->title;
		}

		radykal_output_option_item( array(
				'id' => 'layouts_product_id',
				'title' => 'Layouts',
				'type' 		=> 'select',
				'css' => 'width: 100%',
				'placeholder' => __('Select a product', 'radykal'),
				'description' => __('The views of the selected product will be used as layout items in the layouts module.', 'radykal'),
				'options' => $fpd_layout_options
			)
		);

		do_action( 'fpd_product_options_form_end' );

		?>

	</tbody>
</table>

<?php
	FPD_Admin_Modal::output_footer(
		__('Set', 'radykal')
	);
?>
