<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('FPD_Order')) {

	class FPD_Order {

		public function __construct() {

			global $woocommerce;

			if( version_compare($woocommerce->version, '3.0.0', '>=') ) //WC 3.0
				add_action( 'woocommerce_new_order_item', array( &$this, 'add_order_item_meta'), 10, 2 );
			else
				add_action( 'woocommerce_add_order_item_meta', array( &$this, 'add_order_item_meta'), 10, 2 );

			//edit order item permalink, so it loads the customized product
			add_filter( 'woocommerce_order_item_permalink', array(&$this, 'change_order_item_permalink') , 10, 3 );

			//add additional links to order item
			add_action( 'woocommerce_order_item_meta_end', array(&$this, 'add_order_item_links') , 10, 4 );

			//add fpd_data to order-again action
			add_filter( 'woocommerce_order_again_cart_item_data', array(&$this, 'add_order_again_item_data') , 10, 3 );

		}

		//add order meta from the cart
		public function add_order_item_meta( $item_id, $item ) {

			$fpd_data = null;
			if( isset( $item->legacy_values['fpd_data'] ) )  // WC 3.0+
				$fpd_data = $item->legacy_values['fpd_data'];
			else if( isset( $item['fpd_data'] ) )  // WC <3.0
				$fpd_data = $item['fpd_data'];

			if( !is_null($fpd_data) ) {
				wc_add_order_item_meta( $item_id, '_fpd_data', $fpd_data['fpd_product'] );
			}

		}

		public function change_order_item_permalink( $permalink, $item, $order ) {

			//V3.4.9 stores data in _fpd_data
			$item_has_fpd = isset($item['fpd_data']) || isset($item['_fpd_data']);

			if( $item_has_fpd ) {

				$order_items = $order->get_items();
				$item_id = array_search($item, $order_items);

				if($item_id !== false) {

					$permalink = add_query_arg( array(
						'order' => method_exists($order,'get_id') ? $order->get_id() : $order->id,
						'item_id' => $item_id),
					$permalink );
				}
			}

			return $permalink;

		}

		public function add_order_item_links( $item_id, $item, $order, $plain_text=null ) {

			$product = $order->get_product_from_item( $item );

			//V3.4.9 stores data in _fpd_data
			$item_has_fpd = isset($item['fpd_data']) || isset($item['_fpd_data']);

			//view customized product link
			if( fpd_get_option('fpd_order_email_customization_link') && $item_has_fpd ) {

				$url = add_query_arg( array(
					'order' => method_exists($order,'get_id') ? $order->get_id() : $order->id,
					'item_id' => $item_id),
				$product->get_permalink($item) );

				echo sprintf( '<a href="%s" style="display: block; font-size: 0.9em; color: rgba(0,0,0,0.8);">%s</a>', $url, FPD_Settings_Labels::get_translation('woocommerce', 'order:_email_view_customized_product') );

			}

			//download button
			if( $item_has_fpd && $product->is_downloadable() && $order->is_download_permitted() ) {

				$url = add_query_arg( array(
					'order' => method_exists($order,'get_id') ? $order->get_id() : $order->id,
					'item_id' => $item_id),
				$product->get_permalink($item) );

				echo ' | <a href="'.esc_url( $url ).'" class="fpd-order-item-download" style="font-size: 0.85em; color: rgba(0,0,0,0.8);">Download</a>' ;
			}

			//show element props
			if( $item_has_fpd && fpd_get_option('fpd_order_show_element_props') ) {

				//V3.4.9: data stored in _fpd_data
				$fpd_data = isset($item['_fpd_data']) ? $item['_fpd_data'] : $item['fpd_data'];
				//V3.4.9: only order is stored in fpd_data
				$fpd_data = is_array($fpd_data) ? $fpd_data['fpd_product'] : $fpd_data;

				$order = json_decode(stripslashes($fpd_data), true);

				if( fpd_get_option('fpd_order_show_element_props') === 'used_colors' ) {
					echo '<div style="margin-top:10px;">'.implode('', FPD_Cart::get_display_elements( $order['product'], 'used_colors' )).'</div>';
				}
				else {

					$display_elements = FPD_Cart::get_display_elements( $order['product'] );
					foreach($display_elements as $display_element) {
						echo '<div style="margin: 10px 0;"><p style="font-weight: bol;font-size:0.95em; margin: 10px 0 0px;">'.$display_element['title'].':</p>'.$display_element['values'].'</div>';
					}

				}

			}

		}

		//add cart item meta when order again
		public function add_order_again_item_data( $cart_item_data, $item, $order ) {

			foreach ( $item->get_meta_data() as $meta ) {

				if($meta->key === '_fpd_data') {
					$cart_item_data['fpd_data']['fpd_product'] = $meta->value;
					$cart_item_data['fpd_data']['fpd_product_price'] = $order->get_item_total($item, true);
				}

			}

			return $cart_item_data;

		}

	}
}

new FPD_Order();

?>