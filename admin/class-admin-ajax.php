<?php

if(!class_exists('FPD_Admin_Ajax')) {

	class FPD_Admin_Ajax {

		public function __construct() {

			//general
			add_action( 'wp_ajax_fpd_dismissnotification', array( &$this, 'dismiss_notification' ) );

			//manage fancy products
			add_action( 'wp_ajax_fpd_newproduct', array( &$this, 'new_product' ) );
			add_action( 'wp_ajax_fpd_editproduct', array( &$this, 'edit_product' ) );
			add_action( 'wp_ajax_fpd_loadproductoptions', array( &$this, 'load_product_options' ) );
			add_action( 'wp_ajax_fpd_export', array( &$this, 'export_product' ) );
			add_action( 'wp_ajax_fpd_saveastemplate', array( &$this, 'save_as_template' ) );
			add_action( 'wp_ajax_fpd_removetemplate', array( &$this, 'remove_template' ) );
			add_action( 'wp_ajax_fpd_loadtemplate', array( &$this, 'create_views_from_template' ) );
			add_action( 'wp_ajax_fpd_duplicateproduct', array( &$this, 'duplicate_product' ) );
			add_action( 'wp_ajax_fpd_removeproduct', array( &$this, 'remove_product' ) );
			add_action( 'wp_ajax_fpd_newcategory', array( &$this, 'new_category' ) );
			add_action( 'wp_ajax_fpd_assigncategory', array( &$this, 'assign_category' ) );
			add_action( 'wp_ajax_fpd_editcategory', array( &$this, 'edit_category' ) );
			add_action( 'wp_ajax_fpd_removecategory', array( &$this, 'remove_category' ) );
			add_action( 'wp_ajax_fpd_newview', array( &$this, 'new_view' ) );
			add_action( 'wp_ajax_fpd_editview', array( &$this, 'edit_view' ) );
			add_action( 'wp_ajax_fpd_duplicateview', array( &$this, 'duplicate_view' ) );
			add_action( 'wp_ajax_fpd_removeview', array( &$this, 'remove_view' ) );
			add_action( 'wp_ajax_fpd_saveviews', array( &$this, 'save_views' ) );
			add_action( 'wp_ajax_fpd_loaddemo', array( &$this, 'load_demo' ) );

			//product builder
			add_action( 'wp_ajax_fpd_loadview', array( &$this, 'load_view' ) );
			add_action( 'wp_ajax_fpd_loadviewoptions', array( &$this, 'load_view_options' ) );

			//ui&layout composer
			add_action( 'wp_ajax_fpd_getcss', array( &$this, 'get_css' ) );

			//fancy designs
			add_action( 'wp_ajax_fpd_newdesigncategory', array( &$this, 'new_design_category' ) );
			add_action( 'wp_ajax_fpd_editdesigncategory', array( &$this, 'edit_design_category' ) );
			add_action( 'wp_ajax_fpd_deletedesigncategory', array( &$this, 'delete_design_category' ) );

			//order
			add_action( 'wp_ajax_fpd_imagefromdataurl', array( &$this, 'create_image_from_dataurl' ) );
			add_action( 'wp_ajax_fpd_imagefromsvg', array( &$this, 'create_image_from_svg' ) );
			add_action( 'wp_ajax_fpd_pdffromdataurl', array( &$this, 'create_pdf_from_dataurl' ) );
			add_action( 'wp_ajax_fpd_loadorderitemimages', array( &$this, 'load_order_item_images' ) );
			add_action( 'wp_ajax_fpd_downloaddpsource', array( &$this, 'download_dp_source' ) );
			add_action( 'wp_ajax_fpd_saveorder', array( &$this, 'save_order' ) );

			//shortcode order
			add_action( 'wp_ajax_fpd_removeshortcodeorder', array( &$this, 'remove_shortcode_order' ) );
			add_action( 'wp_ajax_fpd_getshortcodeorder', array( &$this, 'get_shortcode_order' ) );

		}

		public function dismiss_notification() {

			if ( !isset($_POST['name']) )
				die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$success = update_option( 'fpd_notification_' . $_POST['name'], true );

			echo json_encode(array(
				'name' => $_POST['name'],
				'success' => $success
			));

			die;

		}

		//create new design category
		public function new_design_category() {

			if ( !isset($_POST['title']) )
				die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$result = wp_insert_term( $_POST['title'], 'fpd_design_category' );

			if( is_wp_error($result) ) {

				echo json_encode(array(
					'error' => $result->get_error_message()
				));

			}
			else {

				echo json_encode(array(
					'message' => __('Category successfully reordered!', 'radykal'),
					'html' => FPD_Admin_Designs::get_category_item_html($result['term_id'], $_POST['title'])
				));

			}

			die;

		}

		//delete design category
		public function delete_design_category() {

			if ( !isset($_POST['category_id']) )
				die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$result = wp_delete_term( $_POST['category_id'], 'fpd_design_category' );
			delete_option( 'fpd_category_thumbnail_url_'.$_POST['category_id'] );

			if( is_wp_error($result) ) {

				echo json_encode(array(
					'error' => $result->get_error_message()
				));

			}
			else {

				echo json_encode(array(
					'message' => __('Category successfully deleted!', 'radykal'),
				));

			}

			die;

		}

		//edit title, parent and thumbnail of a design category
		public function edit_design_category() {

			if ( !isset($_POST['category_id']) )
				die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			if( isset($_POST['thumbnail']) ) {

				if( empty($_POST['thumbnail']) ) {
					$result = delete_option( 'fpd_category_thumbnail_url_'.$_POST['category_id'] );
				}
				else {
					$result = update_option( 'fpd_category_thumbnail_url_'.$_POST['category_id'], $_POST['thumbnail'] );
				}

			}
			else if( isset($_POST['title']) ) {

				$result = wp_update_term($_POST['category_id'], 'fpd_design_category', array(
					'name' => $_POST['title']
				));

			}
			else if( isset($_POST['parent_id']) ) {

				$result = wp_update_term($_POST['category_id'], 'fpd_design_category', array(
					'parent' => $_POST['parent_id']
				));

			}

			if( is_wp_error($result) || $result === false ) {

				echo json_encode(array(
					'error' => is_wp_error($result) ? $result->get_error_message() : __('Something went wrong. Please try again!', 'radykal')
				));

			}
			else {

				echo json_encode(array(
					'message' => __('Category successfully updated!', 'radykal'),
					'object' => $result
				));

			}

			die;

		}

		//load the view data for the product builder
		public function load_view() {

			if ( !isset($_POST['view_id']) )
			    die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			header('Content-Type: application/json');

			$fancy_view = new FPD_View((int) $_POST['view_id']);

			echo json_encode(
				array(
					'elements' => $fancy_view->get_elements()
				)
			);

			die;

		}

		//load options of a single view
		public function load_view_options() {

			if ( !isset($_POST['view_id']) )
			    die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			header('Content-Type: application/json');

			$fancy_view = new FPD_View((int) $_POST['view_id']);

			echo json_encode(
				array(
					'options' => $fancy_view->get_options()
				)
			);

			die;

		}

		//add a new product
		public function new_product() {

			if ( !isset($_POST['title']) )
			    die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			if ( class_exists( 'FPD_Product' ) ) {

				$options = isset($_POST['options']) ? $_POST['options'] : '';
				$thumbnail = isset($_POST['thumbnail']) ? $_POST['thumbnail'] : '';

				$id = FPD_Product::create( $_POST['title'], $options, $thumbnail );

				header('Content-Type: application/json');
				echo json_encode(
					array(
						'id' => $id,
						'message' => $id ? __('Product successfully created!', 'radykal') : __('Product could not be created. Please try again!', 'radykal'),
						'html' => FPD_Admin_Manage_Products::get_product_item_html( $id, $_POST['title'], '', $thumbnail )
					)
				);

			}

			die;

		}

		//edit title and thumbnail of a view
		public function edit_product() {

			if ( !isset($_POST['id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$columns = array();
			if ( class_exists( 'FPD_Product' ) ) {

				$fancy_product = new FPD_Product( $_POST['id'] );
				$columns = $fancy_product->update(
					isset($_POST['title']) ? $_POST['title'] : null,
					isset($_POST['options']) ? $_POST['options'] : null,
					isset($_POST['thumbnail']) ? $_POST['thumbnail'] : null
				);

			}

			header('Content-Type: application/json');

			if( !empty($columns) ) {
				echo json_encode(array(
					'columns' => $columns,
					'message' => __('Product Updated!', 'radykal'),
					'id' => $_POST['id']
				));
			}
			else {
				echo json_encode(0);
			}

			die;

		}

		//load options of a single product
		public function load_product_options() {

			if ( !isset($_POST['product_id']) )
			    die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			header('Content-Type: application/json');

			$fancy_product = new FPD_Product((int) $_POST['product_id']);

			echo json_encode(
				array(
					'options' => $fancy_product->get_options()
				)
			);

			die;

		}

		//duplicate product
		public function duplicate_product() {

			if ( !isset($_POST['new_id']) || !isset($_POST['source_id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$new_id = intval($_POST['new_id']);
			$source_id = intval($_POST['source_id']);
			$source_product = new FPD_Product( $source_id );

			header('Content-Type: application/json');
			echo json_encode($source_product->duplicate($new_id));

			die;

		}

		//remove a fancy product
		public function remove_product() {

			if ( !isset($_POST['id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			header('Content-Type: application/json');

			$fancy_product = new FPD_Product( $_POST['id'] );
			$result = $fancy_product->delete();
			echo json_encode($result);

			die;

		}

		public function assign_category() {

			if ( !isset($_POST['productID']) || !isset($_POST['categoryID']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			global $wpdb;
			$checked = intval($_POST['checked']);

			header('Content-Type: application/json');

			if( $checked ) {

				$fancy_category = new FPD_Category($_POST['categoryID']);
				$inserted = $fancy_category->add_product($_POST['productID']); //assign product to category

				echo json_encode($inserted);

			}
			else {

				$test = $wpdb->query( $wpdb->prepare("DELETE FROM ".FPD_CATEGORY_PRODUCTS_REL_TABLE." WHERE category_id=%d AND product_id=%d", $_POST['categoryID'], $_POST['productID']) );
				echo json_encode($test);
			}

			die;

		}

		//add a new category
		public function new_category() {

			if ( !isset($_POST['title']) )
			    die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			if ( class_exists( 'FPD_Category' ) ) {

				$id = FPD_Category::create( $_POST['title'] );

				header('Content-Type: application/json');
				echo json_encode(
					array(
						'id' => $id,
						'message' => $id ? __('Category successfully created!', 'radykal') : __('Category could not be created. Please try again!', 'radykal'),
						'html' => FPD_Admin_Manage_Products::get_category_item_html( $id, $_POST['title'] )
					)
				);

			}

			die;

		}

		//edit category title
		public function edit_category() {

			if ( !isset($_POST['id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$columns = array();
			if ( class_exists( 'FPD_Category' ) ) {

				$fancy_category = new FPD_Category( $_POST['id'] );
				$columns = $fancy_category->update(
					isset($_POST['title']) ? $_POST['title'] : false
				);

			}

			header('Content-Type: application/json');

			if( !empty($columns) ) {
				echo json_encode(array(
					'columns' => $columns,
					'message' => __('Category Updated!', 'radykal'),
					'id' => $_POST['id']
				));
			}
			else {
				echo json_encode(0);
			}

			die;

		}

		//remove a view from a fancy product
		public function remove_category() {

			if ( !isset($_POST['id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			header('Content-Type: application/json');

			$fancy_category = new FPD_Category( $_POST['id'] );
			$result = $fancy_category->delete();
			echo json_encode($result);

			die;

		}

		//add a new view to a fancy product
		public function new_view() {

			if ( !isset($_POST['title']) || !isset($_POST['product_id']) )
			    die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$title = trim($_POST['title']);
			$thumbnail = trim($_POST['thumbnail']);
			$product_id = trim($_POST['product_id']);
			$elements = isset($_POST['elements']) ? trim($_POST['elements']) : false;
			$add_to_library = isset($_POST['add_images_to_library']) ? (bool) intval($_POST['add_images_to_library']) : false;

			//check if thumbnail is base64 encoded, if yes, create and upload image to wordpress media library
			if(base64_encode(base64_decode($thumbnail, true)) === $thumbnail) {
				$thumbnail = fpd_admin_upload_image_to_wp($_POST['thumbnail_name'], $thumbnail, $add_to_library);
			}

			//check if elements are posted
		    if($elements !== false) {

			    $elements = json_decode(stripslashes($elements), true);

			    //loop through all elements
			    for($i=0;  $i < sizeof($elements); $i++) {

					$element = $elements[$i];

				    if( $element['type'] == 'image' ) {

						//get parts of source string
				    	$image_parts = explode(',', $element['source']);
				    	$type = @$image_parts[0]; //type of image
				    	$base64_image = @$image_parts[1]; //the base 64 encoded image string

						//check if string is base64 encoded
				    	if( !is_null($base64_image) && base64_encode(base64_decode($base64_image, true)) === $base64_image ) {

							if( isset($type) ) {
								if( strpos($type, 'png') !== false ) {
							    	$type = 'png';
						    	}
						    	else {
							    	$type = 'jpeg';
						    	}
							}

							$elements[$i]['source'] = fpd_admin_upload_image_to_wp($element['title'].'.'.$type, $base64_image, $add_to_library);
				    	}

				    }

			    }

				//serialize for database
			    $elements = serialize($elements);

		    }

		    //add view to fancy product
		    $fp = new FPD_Product($product_id);
			$view_id = $fp->add_view($title, $elements, $thumbnail);

			//send answer
			header('Content-Type: application/json');

			if($view_id) {
				echo json_encode(array('html' => FPD_Admin_Manage_Products::get_view_item_html($view_id, $thumbnail, $title)));
			}
			else {
				echo json_encode(0);
			}

			die;

		}

		//edit title and thumbnail of a view
		public function edit_view() {

			if ( !isset($_POST['id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$columns = array();

			if( isset($_POST['title']) ) {
				$columns['title'] = $_POST['title'];
			}

			if( isset($_POST['thumbnail']) ) {
				$columns['thumbnail'] = $_POST['thumbnail'];
			}

			if( isset($_POST['options']) ) {

				$fancy_view = new FPD_View( $_POST['id'] );
				$fv_options = $fancy_view->get_options();
				if( is_array($fv_options) && isset($_POST['replace_options']) )
					$fv_options = array_replace($fv_options, json_decode(stripslashes($_POST['options']), true));
				else
					$fv_options = json_decode(stripslashes($_POST['options']), true);

				$columns['options'] = $fv_options;
			}

			if ( !class_exists( 'FPD_View' ) )
				require_once(FPD_PLUGIN_DIR.'/inc/api/class-view.php');


			$fancy_view = new FPD_View( $_POST['id'] );
			$success = $fancy_view->update($columns);

			header('Content-Type: application/json');

			if( !empty($success) ) {
				echo json_encode(array(
					'columns' => $columns,
					'message' => __('View Updated!', 'radykal'),
					'id' => $_POST['id']
				));
			}
			else {
				echo json_encode(0);
			}

			die;

		}

		//duplicate view
		public function duplicate_view() {

			if ( !isset($_POST['id']) || !isset($_POST['title']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$id = trim($_POST['id']);
			$new_title = trim($_POST['title']);

			$fancy_view = new FPD_View( $id );
			$new_view_data = $fancy_view->duplicate( $new_title );

			header('Content-Type: application/json');
			if( $new_view_data !== false ) {
				echo json_encode(
					array( 'html' => FPD_Admin_Manage_Products::get_view_item_html( $new_view_data->ID, $new_view_data->thumbnail, $new_title ) )
				);
			}
			else {
				echo json_encode(0);
			}

			die;

		}

		//remove a view from a fancy product
		public function remove_view() {

			if ( !isset($_POST['id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$id = trim($_POST['id']);

			header('Content-Type: application/json');

			$fancy_view = new FPD_View($id);
			$result = $fancy_view->delete();
			echo json_encode($result);

			die;

		}

		public function save_views() {

			if ( !isset($_POST['ids']) )
			    exit;

		    check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

		    $ids = $_POST['ids'];

		    global $wpdb;

		    for($i = 0; $i < sizeof($ids); $i++) {

				$updated_rows = $wpdb->update(
				 	FPD_VIEWS_TABLE,
					 	array('view_order' => $i), //what
					 	array('ID' => intval($ids[$i])), //where
					 	array('%d'), //format what
					 	array('%d') //format where
				);

		    }

			header('Content-Type: application/json');

			if( $updated_rows !== false ) {
				echo json_encode(array(
					'message' => __('Product Updated!', 'radykal'),
				));
			}
			else {
				echo json_encode(0);
			}

			die;

		}

		public function load_demo() {

			if ( !isset($_POST['url']) )
			    exit;

		    check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			header('Content-Type: application/json');

			$json = fpd_admin_get_file_content($_POST['url']);

			if( $json !== false ) {

				echo json_encode(array(
					'url' => $_POST['url'],
					'json' => $json
				));

			}
			else {
				echo json_encode(0);
			}

			die;

		}

		public function get_css() {

			if ( !isset($_POST['primary_color']) )
			    exit;

		    check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			header('Content-Type: application/json');

			echo self::parse_css($_POST['primary_color'], $_POST['secondary_color']);

			die;

		}

		//add a new view to a fancy product
		public function save_as_template() {

			if ( !isset($_POST['title']) || !isset($_POST['product_id']) )
			    die;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$fancy_product = new FPD_Product( $_POST['product_id'] );
			$views = $fancy_product->get_views();
			foreach($views as $view) {
				unset($view->ID);
				unset($view->product_id);
				unset($view->view_order);
				unset($view->options);
			}
			$views = json_encode($views);

		    //create new template
			$template_id = FPD_Admin_Template::create( $_POST['title'], $views );

			//send answer
			header('Content-Type: application/json');

			if( $template_id ) {
				echo json_encode(array(
					'id' => $template_id,
					'views' => $views,
					'html' => FPD_Admin_Manage_Products::get_template_link_html(
						$template_id,
						$_POST['title'],
						$views
					),
					'message' => __('Template successfully created.', 'radykal')
				));
			}
			else {
				echo json_encode(array( 'error' => 1, 'message' => __('Template could not be stored. Please try again!', 'radykal') ));
			}

			die;

		}

		//remove template
		public function remove_template() {

			if ( !isset($_POST['id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$id = trim($_POST['id']);

			header('Content-Type: application/json');

			$result = FPD_Admin_Template::delete( $id );
			echo json_encode($result);

			die;

		}

		//load template
		public function create_views_from_template() {

			if ( !isset($_POST['id']) || !isset($_POST['product_id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$id = trim($_POST['id']);
			$product_id = trim($_POST['product_id']);

			header('Content-Type: application/json');

			try {

				$views = json_decode(FPD_Admin_Template::get_views( $id ), true);

				$fancy_product = new FPD_Product($product_id);
				$html = '';

				foreach($views as $view) {

					$view_id = $fancy_product->add_view(
						$view['title'],
						$view['elements'],
						$view['thumbnail']
					);

					$html .= FPD_Admin_Manage_Products::get_view_item_html($view_id, $view['thumbnail'], $view['title']);

				}

				echo json_encode( array(
					'html'	  => $html,
					'message' => __('Product successfully created!', 'radykal')
				));

			}
			catch(Exception $e) {

				echo json_encode( array(
					'error' => 1,
					'message' => __('Fancy Product could not be stored. Please try again!', 'radykal')
				));

			}

			die;

		}

		//creates an image from a data url
		public function create_image_from_dataurl() {

			if (
				!isset($_POST['order_id']) ||
				!isset($_POST['item_id']) ||
				!isset($_POST['data_url']) ||
				!isset($_POST['title']) ||
				!isset($_POST['format'])
			)
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$order_id = trim($_POST['order_id']);
			$item_id = trim($_POST['item_id']);
			$data_url = trim($_POST['data_url']);
			$title = sanitize_title( trim($_POST['title']) );
			$format = trim($_POST['format']);
			$dpi = isset($_POST['dpi']) ? intval($_POST['dpi']) : 300;

			//create fancy product orders directory
			if( !file_exists(FPD_ORDER_DIR) )
				wp_mkdir_p(FPD_ORDER_DIR);

			//create uploads dir
			$images_dir = FPD_ORDER_DIR.'images/';
			if( !file_exists($images_dir) )
				wp_mkdir_p($images_dir);

			//shortcode order
			if(empty($item_id)) {

				$shortcode_dir = FPD_ORDER_DIR.'images/_shortcode/';
				if( !file_exists($shortcode_dir) )
					wp_mkdir_p($shortcode_dir);

				$item_dir = $shortcode_dir.$order_id.'/';
				if( !file_exists($item_dir) )
					wp_mkdir_p($item_dir);

			}
			//wc order
			else {

				//create order dir
				$order_dir = $images_dir . $order_id . '/';
				if( !file_exists($order_dir) )
					wp_mkdir_p($order_dir);

				//create item dir
				$item_dir = $order_dir . $item_id . '/';
				if( !file_exists($item_dir) )
					wp_mkdir_p($item_dir);

			}

			$image_path = $item_dir.$title.'.'.$format;

			$image_exist = file_exists($image_path);

			//get the base-64 from data
			$base64_str = substr($data_url, strpos($data_url, ",")+1);
			//decode base64 string
			$decoded = base64_decode($base64_str);
			$result = file_put_contents($image_path, $decoded);

			if( $format == 'jpeg' ) {

				require_once(FPD_PLUGIN_ADMIN_DIR.'/inc/resampler.php');

				$source = imagecreatefromjpeg($image_path);
				list($width, $height) = getimagesize($image_path);
				$resampler = new Resampler;
				$im = $resampler->resample($source, $height, $width, $format, $dpi);
				file_put_contents($image_path, $im);

			}

			header('Content-Type: application/json');

			if( $result ) {
				$image_url = content_url( substr($image_path, strrpos($image_path, '/fancy_products_orders/')) );
				echo json_encode( array('code' => $image_exist ? 302 : 201, 'url' => $image_url, 'title' => $title) );
			}
			else {
				echo json_encode( array('code' => 500) );
			}

			die;

		}

		public function create_image_from_svg() {

			if ( !isset($_POST['order_id']) || !isset($_POST['item_id']) || !isset($_POST['svg']) || !isset($_POST['title']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			require_once(FPD_PLUGIN_ADMIN_DIR.'/inc/svglib/svglib.php');

			$order_id = trim($_POST['order_id']);
			$item_id = trim($_POST['item_id']);
			$svg = stripslashes(trim($_POST['svg']));
			$width = trim($_POST['width']);
			$height = trim($_POST['height']);
			$title = sanitize_title( trim($_POST['title']) );

			//create fancy product orders directory
			if( !file_exists(FPD_ORDER_DIR) )
				wp_mkdir_p(FPD_ORDER_DIR);

			//create uploads dir
			$images_dir = FPD_ORDER_DIR.'images/';
			if( !file_exists($images_dir) )
				wp_mkdir_p($images_dir);

			//shortcode order
			if(empty($item_id)) {

				$shortcode_dir = FPD_ORDER_DIR.'images/_shortcode/';
				if( !file_exists($shortcode_dir) )
					wp_mkdir_p($shortcode_dir);

				$item_dir = $shortcode_dir.$order_id.'/';
				if( !file_exists($item_dir) )
					wp_mkdir_p($item_dir);

			}
			//wc order
			else {

				//create order dir
				$order_dir = $images_dir . $order_id . '/';
				if( !file_exists($order_dir) )
					wp_mkdir_p($order_dir);

				//create item dir
				$item_dir = $order_dir . $item_id . '/';
				if( !file_exists($item_dir) )
					wp_mkdir_p($item_dir);

			}

			$image_path = $item_dir.$title.'.svg';

			$image_exist = file_exists($image_path);

			header('Content-Type: application/json');

			try {
				$svg = '<?xml version="1.0" encoding="UTF-8" standalone="no" ?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="'.$width.'" height="'.$height.'" xml:space="preserve">'.$svg.'</svg>';

				$svg_doc = new SVGDocument($svg);
				$svg_doc->asXML($image_path);

				$image_url = content_url( substr($image_path, strrpos($image_path, '/fancy_products_orders/')) );
				echo json_encode( array('code' => $image_exist ? 302 : 201, 'url' => $image_url, 'title' => $title) );
			}
			catch(Exception $e) {
				echo json_encode( array('code' => 500) );
			}

			die;

		}

		//creates a pdf from a data url
		public function create_pdf_from_dataurl() {

			if ( !isset($_POST['order_id']) || !isset($_POST['data_strings']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			if( !class_exists('TCPDF') ) {
				require_once(FPD_PLUGIN_ADMIN_DIR.'/inc/tcpdf/tcpdf.php');
			}

			//register_shutdown_function( array( &$this, 'get_server_errors' ) );

			$order_id = trim($_POST['order_id']);
			$item_id = trim($_POST['item_id']);
			//if memory limit is too small, a fatal php error will thrown here
			$data_strings = json_decode(stripslashes($_POST['data_strings']));

			$width = trim($_POST['width']);
			$height = trim($_POST['height']);
			$image_format = trim($_POST['image_format']);
			$orientation = trim($_POST['orientation']);
			$dpi = isset($_POST['dpi']) ? intval($_POST['dpi']) : 300;

			//create fancy product orders directory
			if( !file_exists(FPD_ORDER_DIR) )
				wp_mkdir_p(FPD_ORDER_DIR);

			//create pdf dir
			$pdf_dir = FPD_ORDER_DIR.'pdfs/';
			$pdf_path = $pdf_dir.$order_id.'_'.$item_id.'.pdf';
			if( !file_exists($pdf_dir) )
				wp_mkdir_p($pdf_dir);

			//shortcode order
			if(empty($item_id)) {
				$pdf_dir = FPD_ORDER_DIR.'pdfs/_shortcode/';
				$pdf_path = $pdf_dir.$order_id.'.pdf';
				if( !file_exists($pdf_dir) )
					wp_mkdir_p($pdf_dir);
			}

			$pdf = new TCPDF($orientation, 'mm', array($width, $height), true, 'UTF-8', false);

			// set document information
			$pdf->SetCreator( get_site_url() );
			$pdf->SetTitle($order_id);

			// remove default header/footer
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetMargins(0, 0, 0);
			$pdf->SetAutoPageBreak(true, 0);
			$pdf->setJPEGQuality(100);

			foreach($data_strings as $data_str) {
				$pdf->AddPage();
				if( $image_format == 'svg' ) {
					if( !class_exists('SVGDocument') )
						require_once(FPD_PLUGIN_ADMIN_DIR.'/inc/svglib/svglib.php');

					//$svg_doc = new SVGDocument($svg_data);
					//$svg_doc->asXML($svg_path);
					$pdf->ImageSVG('@'.$data_str);
				}
				else {
					$data_str = base64_decode(substr($data_str, strpos($data_str, ",") + 1));
					$pdf->Image('@'.$data_str,'', '', 0, 0, '', '', '', false, $dpi);
				}

			}

			if( isset($_POST['summary_json']) && !empty($_POST['summary_json']) && fpd_is_json($_POST['summary_json']) ) {

				$summary_json = json_decode(stripslashes($_POST['summary_json']), true);

				$pdf->AddPage();

				//parameter that will be displayed
				$includedParameters = array('fill', 'opacity', 'top', 'left', 'scaleX', 'scaleY', 'angle', 'fontFamily', 'fontSize', 'fontStyle', 'fontWeight', 'stroke', 'strokeWidth','price', 'sku');

				$html = '';
				//if only the current view is sent, put it into new array
				$views = isset($summary_json['title']) ? array($summary_json) : $summary_json;

				//loop all views
				foreach($views as $view) {

					$html .= '<h3>'.$view['title'].'</h3><table border="1" cellspacing="3" cellpadding="4">
					<thead>
						<tr>
							<th><strong>Element</strong></th>
							<th colspan="4"><strong>Properties</strong></th>
							<th width="60px"><strong>Type</strong></th>
						</tr>
					</thead>
					<tbody>';

					$viewElements = $view['elements'];

					//loop all view elements
					foreach($viewElements as $viewElement) {

						$elementParams = $viewElement['parameters'];
						$element_html = '<div>Content: '.(isset($elementParams['text']) ? $elementParams['text'] : $viewElement['source']).'</div>';

						foreach($includedParameters as $param) {

							if( isset($elementParams[$param]) ) {

								$value = is_array($elementParams[$param]) ? implode(' | ', $elementParams[$param]) : $elementParams[$param];
								if( $param === 'fill' )
									$value = strtoupper( fpd_get_hex_name($value) ) . ' ' . $value; //display hex name

								if( !empty($value) )
									$element_html .= '<i>'. strtoupper($param).':</i> '.$value.', ';

							}
						}

						$element_html = substr( $element_html, 0, -2 );

						$html .= '<tr><td>'.$viewElement['title'].'</td><td colspan="4">'.$element_html.'</td><td width="60px">'.$viewElement['type'].'</td></tr>';
					}

					$html .= '</tbody></table>';

				}

				$pdf->writeHTML($html, true, false, true, false, '');
				$pdf->lastPage();

			}

			$pdf->Output($pdf_path, 'F');

			$pdf_url = content_url( substr($pdf_path, strrpos($pdf_path, '/fancy_products_orders')) );

			header('Content-Type: application/json');
			echo json_encode( array('code' => 201, 'url' => $pdf_url) );

			die;

		}

		//load all images to an order based on order id and item id
		public function load_order_item_images() {

			if ( !isset($_POST['order_id']) || !isset($_POST['item_id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$order_id = trim($_POST['order_id']);
			$item_id = trim($_POST['item_id']);

			$pic_types = array("jpg", "jpeg", "png", "svg");

			header('Content-Type: application/json');

			//load shortcode order images
			if( empty($item_id) ) {
				$item_dir = FPD_ORDER_DIR . 'images/_shortcode/' . $order_id;
			}
			//load wc order images
			else {
				$item_dir = FPD_ORDER_DIR . 'images/' . $order_id . '/' . $item_id;
			}

			if( file_exists($item_dir) ) {

				$folder = opendir($item_dir);

				$images = array();
				$item_dir_url = substr($item_dir, strrpos($item_dir, '/fancy_products_orders/'));
				while ($file = readdir($folder) ) {
					if(in_array(substr(strtolower($file), strrpos($file,".") + 1),$pic_types)) {
						$images[] = content_url( $item_dir_url ) . '/' . $file;
					}
				}
				closedir($folder);


				echo json_encode( array( 'code' => 200, 'images' =>  $images) );

			}
			else {
				echo json_encode( array( 'code' => 201) );
			}

			die;

		}

		public function download_dp_source() {

			if (!isset($_POST['new_src']) || !isset($_POST['order_type']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			header('Content-Type: application/json');

			$new_src = $_POST['new_src'];

			$filename = time().'.jpeg';
			$dp_dir = FPD_WP_CONTENT_DIR . '/uploads/fpd_depositphotos/';
			$dp_dir_url = content_url( '/uploads/fpd_depositphotos/'.$filename );

			if( !file_exists($dp_dir) )
				wp_mkdir_p($dp_dir);

			if( copy($new_src, $dp_dir.$filename) ) {

				echo json_encode( array(
					'new_src' => $dp_dir_url,
					'message' =>  __('Depositphotos Image successfully replaced!', 'radykal'))
				);
			}


			die;
		}

		public function save_order() {

			if (!isset($_POST['order']) || !isset($_POST['order_type']) || !isset($_POST['id']) )
			    exit;

			$id = intval($_POST['id']);
			$order = stripslashes( $_POST['order'] );
			$order_type = $_POST['order_type'];

			$updated = false;
			if($order_type == 'wc') {

				$updated = wc_update_order_item_meta( $id, '_fpd_data', $order );

			}
			else if($order_type == 'shortcode') {

				$updated = FPD_Shortcode_Order::update( $id, array( 'views' => $order ) );

			}
			else if($order_type == 'gf' && isset($_POST['gf_field_id']) ) {

				$updated = GFAPI::update_entry_field( $id, intval($_POST['gf_field_id']), $order );

			}

			echo json_encode(array(
				'updated' => $updated,
				'message' => $updated ? __('Order successfully saved.', 'radykal') : __('Order changes could not be saved. Please try again!', 'radykal')
			));

			die;

		}

		//remove shortcode order by ID
		public function remove_shortcode_order() {

			if ( !isset($_POST['id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$id = trim($_POST['id']);

			header('Content-Type: application/json');

			$result = FPD_Shortcode_Order::delete( $id );
			echo json_encode($result);

			die;

		}

		//get shortcode order by ID
		public function get_shortcode_order() {

			if ( !isset($_POST['id']) )
			    exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$id = trim($_POST['id']);

			header('Content-Type: application/json');

			$result = FPD_Shortcode_Order::get_order( $id );
			echo json_encode($result);

			die;

		}

		public function get_server_errors() {

			$e = error_get_last();
			if( $e & (E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR) ) {
				header('Content-Type: application/json');
				echo json_encode($e);
			}

			die;

		}

		public static function parse_css( $primary_color='', $secondary_color='') {

			$result = FPD_UI_Layout_Composer::parse_css('@primaryColor: '.$primary_color.'; @secondaryColor: '.$secondary_color.';');

			if( !is_array($result) ) {

				return json_encode(array(
					'css' => $result
				));

			}
			else {

				return json_encode(array(
					'error' => $result['message']
				));

			}

		}

		public function export_product() {

			if ( !isset($_GET['id']) )
				exit;

			check_ajax_referer( 'fpd_ajax_nonce', '_ajax_nonce' );

			$product_id = $_GET['id'];
			//$product_id = 11;

			if( !class_exists('ZipArchive') ) {
				die;
			}

			$upload_dir = wp_upload_dir();
			$upload_dir = $upload_dir['basedir'];

			$exports_dir = $upload_dir . '/fpd_exports/';

			wp_mkdir_p( $exports_dir );

			//temp export dir
			$temp_export_dir = $exports_dir . 'product_' . $product_id;
			wp_mkdir_p( $temp_export_dir );

			$fp = new FPD_Product($product_id);

			//final_json
			$final_json = array();
			$final_json['title'] = $fp->get_title();
			$product_thumbnail = $fp->get_thumbnail();
			if( $source_name = fpd_admin_copy_file( $product_thumbnail, $temp_export_dir) ) {
				$product_thumbnail = $source_name;
			}
			$final_json['thumbnail'] = $product_thumbnail;
			$final_json['options'] = $fp->get_options();
			$final_json['views'] = array();

		    $views = $fp->get_views(false);

		    foreach($views as $view) {

				$elements = $view->elements;
				if( !is_array($elements) ) {
					continue;
				}
				for($i=0; $i < sizeof($elements); $i++) {

					$source = $elements[$i]['source'];

					if($elements[$i]['type'] == 'image' && base64_encode(base64_decode($source, true)) !== $source) {

						if( $source_name = fpd_admin_copy_file( $source, $temp_export_dir) ) {
							$elements[$i]['source'] = $source_name;
						}

					}

				}

				//final_view
				$final_view = array();
				$final_view['title'] = $view->title;
				$view_thumbnail = $view->thumbnail;
				if( $source_name = fpd_admin_copy_file( $view_thumbnail, $temp_export_dir) ) {
					$view_thumbnail = $source_name;
				}
				$final_view['thumbnail'] = $view_thumbnail;
				$final_view['elements'] = $elements;
				$fancy_view = new FPD_View($view->ID);
				$final_view['options'] = $fancy_view->get_options();

				array_push($final_json['views'], $final_view);

			}

			$fop = fopen($temp_export_dir . '/product.json', 'w');
			fwrite($fop, json_encode($final_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
			fclose($fop);

			$zipname =  'product_' . $product_id . '.zip';
			$zip_path =  $exports_dir . $zipname;
			$zip = new ZipArchive;
			$zip->open($zip_path, ZipArchive::CREATE);

			if ($handle = opendir($temp_export_dir)) {

		    	while (false !== ($entry = readdir($handle))) {

		        	if ($entry != "." && $entry != ".." && !strstr($entry,'.php')) {
		            	$zip->addFile($temp_export_dir . '/'. $entry, $entry);
		        	}

		      	}

			  	closedir($handle);
			}

		    $zip->close();

		    fpd_admin_delete_directory($temp_export_dir);

		    header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=$zipname");
			header("Content-length: " . filesize($zip_path));
			header("Pragma: no-cache");
			header("Expires: 0");
			readfile("$zip_path");

			unlink($zip_path);

			die;

		}

	}
}

new FPD_Admin_Ajax();

?>