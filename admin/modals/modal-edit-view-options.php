<?php

FPD_Admin_Modal::output_header(
	'fpd-modal-edit-view-options',
	__('View Options', 'radykal'),
	__('Here you can adjust the options for a single view. This allows, among other things to use different prices in different views.', 'radykal')
);

?>

<table class="form-table radykal-settings-form">
	<tbody>

		<?php

		radykal_output_option_item( array(
				'id' => 'stage_width',
				'title' => 'Canvas Width',
				'type' => 'number',
				'class' => 'large-text',
				'placeholder' => __('Canvas width from UI-Layout/Product Options', 'radykal')
			)
		);

		radykal_output_option_item( array(
				'id' => 'stage_height',
				'title' => 'Canvas Height',
				'type' => 'number',
				'class' => 'large-text',
				'placeholder' => __('Canvas height from UI-Layout/Product Options', 'radykal')
			)
		);

		radykal_output_option_item( array(
				'id' => 'designs_parameter_price',
				'title' => 'Custom Image Price',
				'type' => 'number',
				'placeholder' => fpd_get_option( 'fpd_designs_parameter_price' ),
				'description' => __('This price will be used for custom added images.', 'radykal')
			)
		);

		radykal_output_option_item( array(
				'id' => 'custom_texts_parameter_price',
				'title' => 'Custom Text Price',
				'type' => 'number',
				'placeholder' => fpd_get_option( 'fpd_custom_texts_parameter_price' ),
				'description' => __('This price will be used for custom added texts.', 'radykal')
			)
		);

		radykal_output_option_item( array(
				'id' => 'max_price',
				'title' => 'Maximum View Price',
				'type' => 'number',
				'placeholder' => -1,
				'description' => __('The maximum price that will be charged for the view. -1 will disable this option.', 'radykal')
			)
		);

		radykal_output_option_item( array(
				'id' => 'optional_view',
				'title' => 'Optional View',
				'type' => 'checkbox',
				'default' => 'no',
				'description' => __('The view is optional, the user must unlock the view and the prices for all element will be added to the total product price.', 'radykal')
			)
		);
		radykal_output_option_item( array(
				'id' => 'design_categories',
				'title' => 'Design Categories',
				'type' 		=> 'multiselect',
				'class'		=> 'radykal-select2',
				'placeholder' => __('Choose design categories'),
				'description' => __('You can choose specific design categories for this view.', 'radykal'),
				'css'		=> 'width: 100%',
				'options' => fpd_output_top_level_design_cat_options(false)
			)
		);

		radykal_output_option_item( array(
				'id' => 'disable_image_upload',
				'title' => 'Disable Image Module',
				'type' => 'checkbox',
				'default' => 'no'
			)
		);

		radykal_output_option_item( array(
				'id' => 'disable_custom_text',
				'title' => 'Disable Text Module',
				'type' => 'checkbox',
				'default' => 'no'
			)
		);

		radykal_output_option_item( array(
				'id' => 'disable_designs',
				'title' => 'Disable Designs Module',
				'type' => 'checkbox',
				'default' => 'no'
			)
		);

		radykal_output_option_item( array(
				'type' => 'section-title',
				'title' => 'PDF Output',
				'description' => __('Define the necessary information for the PDF generation in the <a href="https://admin.fancyproductdesigner.com" target="_blank">ADMIN solution</a>. After setting a format, a rectangular box will be added to the canvas. When exporting the order as PDF in the ADMIN solution, only the content in the printing box will be visible in the PDF!', 'radykal')
			)
		);

		radykal_output_option_item( array(
				'id' 			=> 'output_format',
				'title' 		=> 'Format',
				'type' 			=> 'select',
				'class'			=> 'radykal-select2',
				'css' 		=> 'width: 100%;',
				'description' 	=> __('Select a standard format or enter a custom size.', 'radykal'),
				'default'		=> '',
				'options' 		=> array(
					'' => __('None', 'radykal'),
					'man' => __('Set Custom Size', 'radykal'),
					'841x1189' => 'A0 (841x1189 mm ; 33.11x46.81 in)',
					'594x841' => 'A1 (594x841 mm ; 23.39x33.11 in)',
					'420x594' => 'A2 (420x594 mm ; 16.54x23.39 in)',
					'297x420' => 'A3 (297x420 mm ; 11.69x16.54 in)',
					'210x297' => 'A4 (210x297 mm ; 8.27x11.69 in)',
					'148x210' => 'A5 (148x210 mm ; 5.83x8.27 in)',
					'105x148' => 'A6 (105x148 mm ; 4.13x5.83 in)',
					'74x105' => 'A7 (74x105 mm ; 2.91x4.13 in)',
					'52x74' => 'A8 (52x74 mm ; 2.05x2.91 in)',
					'37x52' => 'A9 (37x52 mm; 1.46x2.05 in)',
					'26x37' => 'A10 (26x37 mm ; 1.02x1.46 in)',
					'18x26' => 'A11 (18x26 mm ; 0.71x1.02 in)',
					'13x18' => 'A12 (13x18 mm ; 0.51x0.71 in)',
					'89x51' => __('Standard US & Canada Business Card (89x51 mm ; 3.5x2 in)', 'radykal'),
					'85x55' => __('Standard EU Business Card (85x55 mm ; 3.35x2.2 in)', 'radykal'),
					'25x76' => __('Micro Business Card (25x76 mm ; 1x3 in)', 'radykal'),
					'57x57' => __('Square Business Card (57x57 mm ; 2.25x2.25 in)', 'radykal'),
					'83x51' => __('Folded Business Card (83x51 mm ; 3.25x2 in)', 'radykal'),
				)
			)
		);

		radykal_output_option_item( array(
				'id' => 'output_width',
				'title' => __('Printing Area Width in MM', 'radykal'),
				'type' => 'number',
				'class' => 'large-text',
				'placeholder' => '',
			)
		);

		radykal_output_option_item( array(
				'id' => 'output_height',
				'title' => __('Printing Area Height in MM', 'radykal'),
				'type' => 'number',
				'class' => 'large-text',
				'placeholder' => '',
			)
		);

		radykal_output_option_item( array(
				'id' => 'printing_box_visibility',
				'title' => __('Show Printing Box In Frontend', 'radykal'),
				'type' => 'checkbox',
				'default' => 'no'
			)
		);

/*
		radykal_output_option_item( array(
				'id' => 'output_background_template',
				'title' => __('Background PDF', 'radykal'),
				'type' => 'upload',
				'css' 		=> 'width:350px;',
				'placeholder' => '',
			)
		);
*/

		?>

	</tbody>
</table>

<?php
	FPD_Admin_Modal::output_footer(
		__('Save', 'radykal')
	);
?>