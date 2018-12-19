<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('FPD_Admin_Order')) {

	class FPD_Admin_Order {

		public function __construct() {

			add_filter( 'woocommerce_hidden_order_itemmeta', array( &$this, 'hide_fpd_meta' ) );
			add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
			add_action( 'woocommerce_before_order_itemmeta', array( &$this, 'admin_order_item_values' ), 10, 3 );

		}

		//hide fpd_data from meta displayment
		public function hide_fpd_meta( $meta_keys ) {

			array_push($meta_keys, '_fpd_data');
			return $meta_keys;

		}

		//add meta box to woocommerce orders
		public function add_meta_boxes() {

			add_meta_box(
				'fpd-order',
				__( 'Fancy Product Designer - Order Viewer', 'radykal' ),
				array( &$this, 'output_meta_box'),
				'shop_order',
				'normal',
				'default'
			);

		}

		//add a button to the ordered fancy product
		public function admin_order_item_values( $item_id, $item, $_product ) {

			if( is_object($_product) ) {

				global $post_id;
				$fpd_data = fpd_wc_get_order_item_meta( $item_id, $post_id );

				if( !empty($fpd_data) ) {

					?>
					<br />
					<a href="#" style="margin-top: 5px;" class='button button-secondary fpd-show-order-item' data-order_id='<?php echo $post_id; ?>' data-order_item_id='<?php echo $item_id; ?>'><?php _e( 'Load in Order Viewer', 'radykal' ); ?></a>
					<?php

				}

			}

		}

		public function output_meta_box()  {

			global $woocommerce;

			fpd_output_admin_notice(
				'info',
				 __( 'New ADMIN', 'radykal' ),
				 __( '<a href="http://admin.fancyproductdesigner.com/" target="_blank">We created a new online solution with an improved Order viewer that has much more feature than this one.</a>', 'radykal' ),
				 true,
				 'fpd_notice_new_admin',
				 true,
				 true
			);

			?>
			<div id="fpd-wc-order">
				<?php include( FPD_PLUGIN_ADMIN_DIR.'/views/html-order-viewer.php' ); ?>
			</div>
			<?php

		}

	}

}

new FPD_Admin_Order();

?>