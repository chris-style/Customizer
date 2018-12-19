<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('FPD_Settings_Advanced') ) {

	class FPD_Settings_Advanced {

		public static function get_options() {

			return apply_filters('fpd_general_settings', array(

				'misc' => array(

					array(
						'title' 	=> __( 'Customization Required', 'radykal' ),
						'description' 		=> __( 'The user must customize the initial elements of a Product in order to proceed.', 'radykal' ),
						'id' 		=> 'fpd_customization_required',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Canvas Touch Scrolling', 'radykal' ),
						'description'	 => __( 'Enable touch gesture to scroll on canvas.', 'radykal' ),
						'id' 		=> 'fpd_canvas_touch_scrolling',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Per-Pixel Detection', 'radykal' ),
						'description'	 => __( 'Object detection happens on per-pixel basis rather than on per-bounding-box. This means transparency of an object is not clickable.', 'radykal' ),
						'id' 		=> 'fpd_canvas_per_pixel_detection',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Fit Images In Canvas', 'radykal' ),
						'description'	 => __( 'If the image (custom uploaded or design) is larger than the canvas, it will be scaled down to fit into the canvas.', 'radykal' ),
						'id' 		=> 'fpd_fitImagesInCanvas',
						'default'	=> 'yes',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Upload zones always on top', 'radykal' ),
						'description'	 	=> __( 'Upload zones will be always on top of all elements.', 'radykal' ),
						'id' 		=> 'fpd_uploadZonesTopped',
						'default'	=> 'yes',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Unsaved Customizations Alert', 'radykal' ),
						'description'	 => __( 'The user will see a notification alert when he leaves the page without saving or adding the product to the cart.', 'radykal' ),
						'id' 		=> 'fpd_unsaved_customizations_alert',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Hide Dialog On Add', 'radykal' ),
						'description'	 => __( 'The dialog/off-canvas panel will be closed as soon as an element is added to the canvas.', 'radykal' ),
						'id' 		=> 'fpd_hide_dialog_on_add',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Lazy Load', 'radykal' ),
						'description'	 	=> __( 'Enable lazy loading for the images in the products and designs containers.', 'radykal' ),
						'id' 		=> 'fpd_lazy_load',
						'default'	=> 'yes',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Improved Image Resize Quality', 'radykal' ),
						'description'	 	=> __( 'Enable a filter that improves the quality of a resized bitmap image. This could take a long time for large images.', 'radykal' ),
						'id' 		=> 'fpd_improvedResizeQuality',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Responsive', 'radykal' ),
						'description'	 	=> __( 'Resizes the canvas and all elements in the canvas, so that all elements are displaying properly in the canvas container. This is useful, when your canvas is larger than the available space in the parent container.', 'radykal' ),
						'id' 		=> 'fpd_responsive',
						'default'	=> 'yes',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'In Canvas Text Editing', 'radykal' ),
						'description'	 => __( 'The user can edit the text via double click or tap(mobile).', 'radykal' ),
						'id' 		=> 'fpd_inCanvasTextEditing',
						'default'	=> 'yes',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Open Text Input On Select', 'radykal' ),
						'description'	 => __( 'The textarea in the toolbar to change an editbale text opens when the text is selected.', 'radykal' ),
						'id' 		=> 'fpd_openTextInputOnSelect',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Replace Colors In Color Group', 'radykal' ),
						'description'	 => __( ' As soon as an element with a color link group is added, the colours of this element will be used for the color group.', 'radykal' ),
						'id' 		=> 'fpd_replaceColorsInColorGroup',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Show Modal In Designer', 'radykal' ),
						'description'	 => __( 'Display some modals (info, qr-code etc.) in the designer instead in the whole page.', 'radykal' ),
						'id' 		=> 'fpd_openModalInDesigner',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Image Size Tooltip', 'radykal' ),
						'description'	 => __( 'Display the image size in pixels of the current selected image in a tooltip.', 'radykal' ),
						'id' 		=> 'fpd_imageSizeTooltip',
						'default'	=> 'no',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' 	=> __( 'Apply Fill When Replacing', 'radykal' ),
						'description'	 => __( 'When an element is replaced, apply fill(color) from replaced element to added element.', 'radykal' ),
						'id' 		=> 'fpd_applyFillWhenReplacing',
						'default'	=> 'yes',
						'type' 		=> 'checkbox',
						'admin_col' => 'four'
					),

					array(
						'title' => __( 'Text Control Padding', 'radykal' ),
						'description' 		=> __( 'The padding of the corner controls when a text element is selected.', 'radykal' ),
						'id' 		=> 'fpd_padding_controls',
						'css' 		=> 'width:60px;',
						'default'	=> '10',
						'type' 		=> 'number',
						'custom_attributes' => array(
							'min' 	=> 0,
							'step' 	=> 1
						)
					),

					array(
						'title' => __( 'Bounding Box Stroke Width', 'radykal' ),
						'description' 		=> __( 'The stroke width of the bounding box when an element is selected.', 'radykal' ),
						'id' 		=> 'fpd_bounding_box_stroke_width',
						'css' 		=> 'width:60px;',
						'default'	=> '1',
						'type' 		=> 'number',
						'custom_attributes' => array(
							'min' 	=> 0,
							'step' 	=> 1
						)
					),

					array(
						'title' => __( 'Highlight Editable Objects', 'radykal' ),
						'description' 		=> __( 'Highlight objects (editable texts and upload zones) with a dashed border. To enable this just define a hexadecimal color value.', 'radykal' ),
						'id' 		=> 'fpd_highlightEditableObjects',
						'css' 		=> 'width:100px;',
						'default'	=> '',
						'type' 		=> 'text'
					),


				), //layout-skin

				'troubleshooting' => array(

					array(
						'title' 	=> __( 'Debug Mode', 'radykal' ),
						'description' 		=> __( 'Enables Theme-Check modal and loads the unminified Javascript files.', 'radykal' ),
						'id' 		=> 'fpd_debug_mode',
						'default'	=> 'no',
						'type' 		=> 'checkbox'
					),

					array(
						'title' 	=> __( 'jQuery No Conflict Mode', 'radykal' ),
						'description' 		=> __( 'Turns on the jQuery no conflict mode. Turn it on if you are facing some Javascript issues.', 'radykal' ),
						'id' 		=> 'fpd_jquery_no_conflict',
						'default'	=> 'no',
						'type' 		=> 'checkbox'
					),

					array(
						'title' 	=> __( 'Test: FabricJS Version', 'radykal' ),
						'description' 		=> __( 'Please test Fancy Product Designer with the latest FabricJS. If you have any bug, please let us know in the support forum.', 'radykal' ),
						'id' 		=> 'fpd_fabric_version',
						'default'	=> '2.2',
						'type' 		=> 'radio',
						'options'   => array(
							'1.6.7'	 => __( '1.6.7', 'radykal' ),
							'2.2'	 => __( '2.+', 'radykal' ),
						)
					),

				),

			));
		}

	}
}

?>