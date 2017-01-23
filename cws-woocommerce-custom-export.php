<?php
/*
Plugin Name: Natures Lawn Custom Export
Plugin URI: https://github.com/MattRy/natureslawn
Description: Natures Lawn Custom Export
Author: Matt Ryan | Cap Web Solutions
Version: 2.1
Author URI: http://capwebsolutions.com
*/
/*
* Plugin to setup custom CSV export for WooCommerce order for Natures Lawn & Garden. 
* Requires WooCommerce Custom Order CSV Export Extension
*/


/**
 * The use of this snippet requires at least WooCommerce 2.2
 */


/** Helper Functions **********************************************************/

/**
 * Get item meta for orders
 *
 * @param array $order_ids array of order ids
 * @return array $all_item_meta array of all item meta keys for $order_ids
 */
function sv_wc_get_item_meta_for_orders( $order_ids ) {

	$all_item_meta = array();

	foreach ( $order_ids as $order_id ) {

		$order = wc_get_order( $order_id );

		// get line items
		foreach ( $order->get_items() as $item ) {
						$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
						$variationid = $item['_variation_id'];
						$productid = $item['_product_id'];
						echo $item_meta;
						$all_item_meta = array_merge( $all_item_meta, array_keys( $item_meta->get_formatted()) );
		}
	}
	return $all_item_meta;
}

/**
 * Insert the given element after the given key in the array
 *
 * @param array $array array to insert the given element into
 * @param string $insert_key key to insert given element after
 * @param array $element element to insert into array
 * @return array
 */
function sv_wc_array_insert_after( Array $array, $insert_key, Array $element ) {

	$new_array = array();

	foreach ( $array as $key => $value ) {

		$new_array[ $key ] = $value;

		if ( $insert_key == $key ) {

			foreach ( $element as $k => $v ) {
				$new_array[ $k ] = $v;
			}
		}
	}

	return $new_array;
}

/**
 * Display field value for new custom fields on the order edit page
 * new fields are shipping email and phone - required for UPS delivery
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
    echo "<div style='text-align:right'><strong>".__('Shipping Email').':</strong> ' . get_post_meta( $order->id, 'shipping_email', true ) . '</style></div>'; 

	echo "<div style='text-align:right'><strong>".__('Shipping Phone').':</strong> ' . get_post_meta( $order->id, 'shipping_phone', true ) . '</style></div>'; 
	
}


/*Add Custom Shipping Fields to Checkout Page
 *	Adds two custom fields to the shipping data - shipping phone number and shipping email address. Both of these are required for UPS 
 * shipping. Validate fields and save to order meta. If shipping to billing address then need to copy billing email/phone over to shipping  
 * email/phone.
*/
add_filter( 'woocommerce_checkout_fields' , 'cap_web_custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function cap_web_custom_override_checkout_fields( $fields ) {
     $fields['shipping']['shipping_phone'] = array(
        'label'     => __('Ship To Phone', 'woocommerce'),
    'placeholder'   => _x('Phone', 'placeholder', 'woocommerce'),
    'required'  => true,
    'class'     => array('form-row-wide'),
    'clear'     => true
     );
	
     $fields['shipping']['shipping_email'] = array(
        'label'     => __('Ship To Email', 'woocommerce'),
    'placeholder'   => _x('Email', 'placeholder', 'woocommerce'),
    'required'  => true,
    'class'     => array('form-row-wide'),
    'clear'     => true
     );

     return $fields;
}

// Update the order meta with custom field values

add_action( 'woocommerce_checkout_update_order_meta', 'cap_web_custom_checkout_field_update_order_meta' );
 
function cap_web_custom_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['shipping_phone'] ) ) {
        update_post_meta( $order_id, 'shipping_phone', sanitize_text_field( $_POST['shipping_phone'] ) );
	} else {
		update_post_meta( $order_id, 'shipping_phone', esc_attr( $_POST['billing_phone'] ) );
	}
    if ( ! empty( $_POST['shipping_email'] ) ) {
        update_post_meta( $order_id, 'shipping_email', sanitize_text_field( $_POST['shipping_email'] ) );
	} else {
		update_post_meta( $order_id, 'shipping_email', esc_attr( $_POST['billing_email'] ) );
	}
}

/**
 * Adds product attributes to the one row per item order export
 * CANNOT be used with non-one row per item formats
 */
/**
 * Add a 'product_attributes' column to the export file
 *
 * @param array $headers
 * @return array
 */
function sv_wc_csv_export_add_product_attributes_column( $headers ) {
	$new_headers = array();
	foreach ( $headers as $key => $header ) {
		$new_headers[ $key ] = $header;
		if ( 'item_name' === $key )  {
			$new_headers['product_attributes'] = 'product_attributes';
		}
	}
	return $new_headers;
}
// add_filter( 'wc_customer_order_csv_export_order_headers', 'sv_wc_csv_export_add_product_attributes_column' );

/**
 * Add the WC_Product object to the line item data for use by the one row per item
 * filter below
 *
 * @param array $line_item
 * @param array $_ item data, unused
 * @param \WC_Product $product
 * @return array
 */
function sv_wc_csv_export_add_product_to_order_line_item( $line_item, $_, $product ) {
	$line_item['product'] = $product;
	return $line_item;
}
// add_filter( 'wc_customer_order_csv_export_order_line_item', 'sv_wc_csv_export_add_product_to_order_line_item', 10, 3 );

/**
 * Add the product attributes in the format:
 *
 * attribute name=attribute value 1, attribute value 2, etc.
 *
 * @param array $order_data
 * @param array $item
 * @return array
 */
// function sv_wc_csv_export_add_product_attributes( $order_data, $item ) {
// 	$order_data['product_attributes'] = '';
// 	$count                            = 1;
// 	if ( ! is_object( $item['product'] ) ) {
// 		return $order_data;
// 	}
// 	foreach ( array_keys( $item['product']->get_attributes() ) as $attribute ) {
		
// 		$order_data['product_attributes'] .= str_replace( 'pa_', '', $attribute ) . '=' . $item['product']->get_attribute( $attribute );

// 		// add a semicolon divider if there are multiple attributes and it's not the last one
// 		if ( count( $item['product']->get_attributes() ) > 1 && $count !== count( $item['product']->get_attributes() ) ) {
// 			$order_data['product_attributes'] .= ';';
// 		}
// 		$count++;
	
// 	}
// 	return $order_data;
// }
// add_filter( 'wc_customer_order_csv_export_order_row_one_row_per_item', 'sv_wc_csv_export_add_product_attributes', 10, 2 );

// ref: https://docs.woocommerce.com/document/ordercustomer-csv-export-developer-documentation/

// add custom column headers
function cws_wc_csv_export_modify_column_headers( $column_headers ) { 
 
	$new_headers = array(
		'size_modified' => 'Size Modified',
		// add other column headers here in the format column_key => Column Name
	);
 
	return array_merge( $column_headers, $new_headers );
}
add_filter( 'wc_customer_order_csv_export_order_headers', 'cws_wc_csv_export_modify_column_headers' );

// set the data for each for custom columns
function wc_csv_export_modify_row_data( $order_data, $order, $csv_generator ) {
 
	$custom_data = array(
		'size_modified' => get_post_meta( $order->id, 'item_meta', true ),
		// add other row data here in the format column_key => data
	);
 
	$new_order_data   = array();
	$one_row_per_item = false;
	
	if ( version_compare( wc_customer_order_csv_export()->get_version(), '4.0.0', '<' ) ) {
		// pre 4.0 compatibility
		$one_row_per_item = ( 'default_one_row_per_item' === $csv_generator->order_format || 'legacy_one_row_per_item' === $csv_generator->order_format );
	} elseif ( isset( $csv_generator->format_definition ) ) {
		// post 4.0 (requires 4.0.3+)
		$one_row_per_item = 'item' === $csv_generator->format_definition['row_type'];
	}

	if ( $one_row_per_item ) {

		foreach ( $order_data as $data ) {
			$new_order_data[] = array_merge( (array) $data, $custom_data );
		}

	} else {

		$new_order_data = array_merge( $order_data, $custom_data );
	}

	return $new_order_data;
}
add_filter( 'wc_customer_order_csv_export_order_row', 'wc_csv_export_modify_row_data', 10, 3 );





/* Pull extraneous content off SIZE field 
 *
 */
 function cws_clean_size_field( $order_data, $order, $csv_generator ) {

	$custom_data = array(
		'size_modified' => get_post_meta( $order->id, 'item_meta', true ),
		// add other row data here in the format column_key => data
	);
	$new_order_data   = array();
	$one_row_per_item = false;
	// if ( version_compare( wc_customer_order_csv_export()->get_version(), '4.0.0', '<' ) ) {
	// 	// pre 4.0 compatibility
	// 	$one_row_per_item = ( 'default_one_row_per_item' === $csv_generator->order_format || 'legacy_one_row_per_item' === $csv_generator->order_format );
	// } elseif ( isset( $csv_generator->format_definition ) ) {
	// 	// post 4.0 (requires 4.0.3+)
	// 	$one_row_per_item = 'item' === $csv_generator->format_definition['row_type'];
	// }

	if ( $one_row_per_item ) {
		foreach ( $order_data as $data ) {
			$new_order_data[] = array_merge( (array) $data, $custom_data );
		}
	} else {
		$new_order_data = array_merge( $order_data, $custom_data );
	}
	return $new_order_data;
 }
 add_filter( 'wc_customer_order_csv_export_order_row', 'wc_csv_export_modify_row_data', 10, 3 );