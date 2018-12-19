<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('FPD_Admin_Product_Builder') ) {

	class FPD_Admin_Product_Builder {

		public function output() {

			require_once(FPD_PLUGIN_ADMIN_DIR.'/modals/modal-edit-view-options.php');

			?>
			<div class="wrap" id="fpd-product-builder">

				<h2 class="fpd-clearfix">
					<?php _e('Product Builder', 'radykal'); ?>
					<?php fpd_admin_display_version_info(); ?>
				</h2>
				<?php

				global $wpdb, $woocommerce;

				$request_view_id = isset($_GET['view_id']) ? $_GET['view_id'] : NULL;

				//get all fancy products
				$fancy_products = array();
				if( fpd_table_exists(FPD_PRODUCTS_TABLE) ) {
					$fancy_products = $wpdb->get_results("SELECT * FROM ".FPD_PRODUCTS_TABLE." ORDER BY title ASC");
				}

				if(sizeof($fancy_products) == 0) {
					echo '<div class="updated"><p><strong>'.__('There are no products!', 'radykal').'</strong></p></div></div>';
					exit;
				}

				//save elements of view
				if(isset($_POST['save_elements'])) {

					check_admin_referer( 'fpd_save_elements' );

					$request_view_id = $_POST['view_id'];

					$elements = array();
					for($i=0; $i < sizeof($_POST['element_types']); $i++) {

						$element = array();

						$element['type'] = $_POST['element_types'][$i];
						$element['title'] = stripslashes($_POST['element_titles'][$i]);
						$element['source'] = stripslashes($_POST['element_sources'][$i]);
						$element['parameters'] = json_decode(stripslashes($_POST['element_parameters'][$i]));

						array_push($elements, $element);

					}

					$fancy_view = new FPD_View($request_view_id);
					$fancy_view->update( array('elements' => json_encode($elements, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)) );

					echo '<div class="updated"><p><strong>'.__('Elements saved.', 'radykal').'</strong></p></div>';

				}

				?>

				<div class="fpd-panel radykal-clearfix" id="fpd-product-builder-head">

					<p class="description"><?php _e( 'Select the view of a product:', 'radykal' ); ?></p>
					<select id="fpd-view-switcher" class="radykal-select2" style="width: 400px;">
						<?php


						if(is_array($fancy_products)) {
							foreach($fancy_products as $fancy_product_val) {

								$fancy_product_id = $fancy_product_val->ID;
								echo '<optgroup label="#'.$fancy_product_id.' - '.$fancy_product_val->title.'" id="'.$fancy_product_id.'">';
								$fancy_product = new FPD_Product($fancy_product_id);
								$views = $fancy_product->get_views();

								if(is_array($views)) {

									for($i=0; $i < sizeof($views); ++$i) {

										$view = $views[$i];

										//get first view
										if($request_view_id == NULL) {
											$request_view_id = $view->ID;
										}

										echo '<option value="'.$view->ID.'" '.selected( $request_view_id ,  $view->ID, false).'>'.$view->title.' - ('.$fancy_product_val->title.')</option>';
									}

								}
								echo '</optgroup>';

							}
						}

						?>
					</select>
					<?php

					//create instance of selected fancy view
					$fancy_view = new FPD_View( $request_view_id );
					$product_id = $fancy_view->get_product_id();

					//main ui layout
					$main_ui_layout = FPD_UI_Layout_Composer::get_layout(fpd_get_option('fpd_product_designer_ui_layout'));
					$plugin_options = $main_ui_layout['plugin_options'];

					//get dimensions from ui layout
					$stage_width =  $plugin_options['stageWidth'];
					$stage_height = $plugin_options['stageHeight'];

					//get dimensions from fancy product options
					$fancy_product = new FPD_Product($product_id);
					$fp_options = $fancy_product->get_options();
					if(isset($fp_options['stage_width']))
						$stage_width = (int) $fp_options['stage_width'];
					if(isset($fp_options['stage_height']))
						$stage_height = (int) $fp_options['stage_height'];

					$stage_width_temp = $stage_width;
					$stage_height_temp = $stage_height;

					//get dimensions from fancy view options
					$fv_options = $fancy_view->get_options();
					if(isset($fv_options['stage_width']))
						$stage_width = (int) $fv_options['stage_width'];
					if(isset($fv_options['stage_height']))
						$stage_height = (int) $fv_options['stage_height'];

					$mask_options = '';
					if(isset($fv_options['mask']))
						$mask_options = json_encode($fv_options['mask']);

					?>
					<div class="fpd-right">
						<a href="#" id="fpd-edit-view-options" class="button-secondary">
							<?php _e( 'Edit View Options', 'radykal' ); ?>
						</a>
					</div>

					<script type="text/javascript">

						var fpdGlobalProductBuilderOpts = {
							stageWidthTemp: <?php echo $stage_width_temp; ?>,
							stageHeightTemp: <?php echo $stage_height_temp; ?>,
							printingBox: <?php echo isset($fv_options['printing_box']) ? json_encode($fv_options['printing_box']) : 'null'; ?>,
						};

					</script>

				</div><!-- head panel -->

				<div id="fpd-manage-elements" class="fpd-panel radykal-clearfix">

					<div class="fpd-ui-blocker"></div>
					<h3 class="radykal-clearfix">
						<?php _e( 'Layers', 'radykal' ); ?>
						<a href="#" id="fpd-save-layers" class="button-primary right"><?php _e( 'Save', 'radykal' ); ?></a>
					</h3>
					<div id="fpd-add-element">
						<a href="#" class="add-new-h2" id="fpd-add-image-element"><?php _e( 'Add Image', 'radykal' ); ?></a>
						<a href="#" class="add-new-h2 fpd-admin-tooltip" id="fpd-add-upload-zone" title="<?php _e( 'The user can add one element into a placeholder image.', 'radykal' ); ?>"><?php _e( 'Add Upload Zone', 'radykal' ); ?></a>
						<a href="#" class="add-new-h2" id="fpd-add-text-element"><?php _e( 'Add Text', 'radykal' ); ?></a>
						<a href="#" class="add-new-h2" id="fpd-add-curved-text-element"><?php _e( 'Add Curved Text', 'radykal' ); ?></a>
						<a href="#" class="add-new-h2 fpd-admin-tooltip" id="fpd-add-text-box-element" title="<?php _e('Text box has a fixed width.', 'radykal'); ?>"><?php _e( 'Add Text Box', 'radykal' ); ?></a>
					</div>

					<form method="post" id="fpd-submit">
						<input type="submit" class="fpd-hidden" name="save_elements" />
						<?php wp_nonce_field( 'fpd_save_elements' ); ?>

						<input type="hidden" value="<?php echo $request_view_id; ?>" name="view_id" />
						<p class="description"><?php _e( 'You can change the order by dragging the items.', 'radykal' ); ?></p>
						<div id="fpd-elements-list" class="fpd-clearfix"></div>

					</form>

				</div><!-- Manage elements -->

				<!-- Product Stage -->
				<div class="fpd-panel">

					<div id="fpd-edit-parameters">
						<?php require_once(FPD_PLUGIN_ADMIN_DIR.'/views/html-product-builder-parameters-form.php'); ?>
					</div>

					<h3><?php _e('Canvas', 'radykal'); ?>
						<span class="description">
							<span id="fpd-stage-width-label"><?php echo $stage_width; ?></span>px *
							<span id="fpd-stage-height-label"><?php echo $stage_height; ?></span>px
						</span>
					</h3>

					<div class="fpd-clearfix">

						<div id="fpd-element-toolbar" class="fpd-clearfix">
							<span id="fpd-undo" class="fpd-admin-tooltip radykal-disabled" title="<?php _e('Undo', 'radykal'); ?>">
								<i class="fpd-admin-icon-undo"></i>
							</span>
							<span id="fpd-redo" class="fpd-admin-tooltip radykal-disabled" title="<?php _e('Redo', 'radykal'); ?>">
								<i class="fpd-admin-icon-redo"></i>
							</span>
							<span id="fpd-center-horizontal" class="fpd-admin-tooltip radykal-disabled fpd-element-toggle" title="<?php _e('Center Horizontal', 'radykal'); ?>">
								<i class="fpd-admin-icon-align-horizontal-middle"></i>
							</span>
							<span id="fpd-center-vertical" class="fpd-admin-tooltip radykal-disabled fpd-element-toggle" title="<?php _e('Center Vertical', 'radykal'); ?>">
								<i class="fpd-admin-icon-align-vertical-middle"></i>
							</span>
							<span id="fpd-ruler" class="fpd-admin-tooltip" title="<?php _e('Ruler', 'radykal'); ?>" data-action="ruler">
								<i class="fpd-admin-icon-ruler"></i>
							</span>
							<div class="fpd-button-modal">
								<span id="fpd-edit-mask" class="fpd-toolbar-btn fpd-toggle"><?php _e( 'Edit Mask', 'radykal' ); ?></span>
								<div id="fpd-mask-toolbar" class="fpd-dialog">
									<h4><?php _e('Use a SVG with one path as mask', 'radykal'); ?></h4>
									<table>
										<tr>
											<td>
												<?php _e( 'Image URL', 'radykal' ); ?>
											</td>
											<td>
												<div class="fpd-single-image-upload">
													<span class="fpd-remove"><span class="dashicons dashicons-minus"></span></span>
													<input type="hidden" name="url" />
												</div>
											</td>
										</tr>
										<tr>
											<td><?php _e( 'Left', 'radykal' ); ?></td>
											<td><input type="number" name="left" placeholder="0" min="0" /></td>
										</tr>
										<tr>
											<td><?php _e( 'Top', 'radykal' ); ?></td>
											<td><input type="number" name="top" placeholder="0" min="0" /></td>
										</tr>
										<tr>
											<td><?php _e( 'Scale-X', 'radykal' ); ?></td>
											<td><input type="number" name="scaleX" placeholder="1" min="0" step="0.01" /></td>
										</tr>
										<tr>
											<td><?php _e( 'Scale-Y', 'radykal' ); ?></td>
											<td><input type="number" name="scaleY" placeholder="1" min="0" step="0.01" /></td>
										</tr>
									</table>
									<button id="fpd-save-mask-options" class="button-secondary"><?php _e( 'Save', 'radykal' ); ?></button>
									<div class="fpd-ui-blocker"></div>
								</div>
							</div>

							<div class="fpd-button-modal">
								<span id="fpd-edit-printing-box" class="fpd-toolbar-btn fpd-toggle radykal-disabled"><?php _e( 'Edit Printing Box', 'radykal' ); ?></span>
								<div id="fpd-printing-box-toolbar" class="fpd-dialog">
									<h4><?php _e('Printing box', 'radykal'); ?></h4>
									<div class="radykal-columns-two">
										<div>
											<?php _e( 'Left', 'radykal' ); ?>
											<input type="number" name="left" placeholder="0" min="0" />
										</div>
										<div>
											<?php _e( 'Top', 'radykal' ); ?>
											<input type="number" name="top" placeholder="0" min="0" />
										</div>
									</div>
									<?php _e( 'Scale', 'radykal' ); ?>
									<input type="range" name="scale" min="0" max="5" step="0.01" class="widefat" />
									<div>
										<span id="fpd-printing-box-pixels" class="description"></span>
									</div>

									<button id="fpd-save-printing-box-options" class="button-secondary"><?php _e( 'Save', 'radykal' ); ?></button>
									<div class="fpd-ui-blocker"></div>
								</div>
							</div>

							<div id="fpd-preview-buttons" class="fpd-button-group radykal-clearfix" style="margin-left: 10px;">
								<span class="fpd-admin-tooltip" title="<?php _e( 'Preview: Tablet', 'radykal' ); ?>" data-value="tablet">
									<i class="fpd-admin-icon-tablet"></i>
								</span>
								<span class="fpd-admin-tooltip" title="<?php _e( 'Preview: Mobile Phone', 'radykal' ); ?>" data-value="phone">
									<i class="fpd-admin-icon-phone"></i>
								</span>
								<span class="fpd-admin-tooltip" title="<?php _e( 'Preview: Fit into container', 'radykal' ); ?>" data-value="fit">
									<i class="fpd-admin-icon-aspect-ratio"></i>
								</span>
							</div>

						</div>

					</div>

					<div id="fpd-preview-wrapper" data-stagewidth="<?php echo $stage_width; ?>" data-stageheight="<?php echo $stage_height; ?>" data-viewid="<?php echo $request_view_id; ?>" data-viewmask='<?php echo $mask_options; ?>'></div>

				</div>

			</div>
			<?php

		}

	}
}

return new FPD_Admin_Product_Builder();

?>