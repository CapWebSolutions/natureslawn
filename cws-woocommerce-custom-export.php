<?php
/*
Plugin Name: CWS WooCommerce Custom Export
Plugin URI: http://capwebsolutions.com
Description: CWS WooCommerce Custom Export
Author: Matt Ryan | Cap Web Solutions
Version: 2.0
Author URI: http://capwebsolutions.com
*/
/*
* Plugin to setup custom CSV export for WooCommerce order for Natures Lawn & Garden. 
* Requires WooCommerce Custom Order CSV Export Extension
*/

//	Reorder Columns in CSV Export
function wc_csv_export_reorder_columns( $column_headers ) {

	// remove shipping_email and shipping_phone from the original set of column headers, otherwise they will be duplicated
	unset( $column_headers['shipping_email'] );
	unset( $column_headers['shipping_phone'] );
	
	$new_column_headers = array();
	
	foreach ( $column_headers as $column_key => $column_name ) {
		
		$new_column_headers[ $column_key ] = $column_name;
		
		if ( 'shipping_company' == $column_key ) {
			
			// add shipping email and phone immediately after shipping company
			$new_column_headers['shipping_email'] = 'shipping_email';
			$new_column_headers['shipping_phone'] = 'shipping_phone';
		}
	}
	return $new_column_headers;
}
// add_filter( 'wc_customer_order_csv_export_order_headers', 'wc_csv_export_reorder_columns' );  // Removed 1/13/2017 

// remove a number of unneeded columns from the CSV export file
function wc_csv_export_remove_column( $column_headers ) {

	// the list of column keys can be found in ../woocommerce-customer-order-csv-export/includes/class-wc-customer-order-csv-export-generator.php

//	unset( $column_headers['shipping_total'] );
	unset( $column_headers['shipping_tax_total'] );
	unset( $column_headers['tax_total'] );
	unset( $column_headers['cart_discount'] );
	unset( $column_headers['discount_total'] );
	unset( $column_headers['order_total'] );
	unset( $column_headers['refunded_total'] );
	unset( $column_headers['order_currency'] );
	unset( $column_headers['payment_method'] );
	unset( $column_headers['billing_company'] );
	unset( $column_headers['billing_first_name'] );
	unset( $column_headers['billing_last_name'] );
	unset( $column_headers['billing_email'] );
	unset( $column_headers['billing_phone'] );
	unset( $column_headers['billing_address_1'] );
	unset( $column_headers['billing_address_2'] );
	unset( $column_headers['billing_postcode'] );
	unset( $column_headers['billing_city'] );
	unset( $column_headers['billing_state'] );
	unset( $column_headers['billing_country'] );
	unset( $column_headers['order_discount'] );
	unset( $column_headers['coupon_items'] );
	unset( $column_headers['download_permissions_granted'] );
	unset( $column_headers['order_notes'] );	
	return $column_headers;
}
add_filter( 'wc_customer_order_csv_export_order_headers', 'wc_csv_export_remove_column' );

/**
 * The use of this snippet requires at least WooCommerce 2.2
 */

/**
 * Alter the column headers for the orders CSV to split item_meta into separate columns
 *
 * Note that this change is only applied to the Default - One Row per Item format
 *
 * @param array $column_headers {
 *     column headers in key => name format
 *     to modify the column headers, ensure the keys match these and set your own values
 * }
 * @param WC_Customer_Order_CSV_Export_Generator $csv_generator, generator instance
 * @return array column headers in column_key => column_name format
 */
function sv_wc_csv_export_order_headers_split_item_meta( $column_headers, $csv_generator ) {

	if ( 'default_one_row_per_item' === $csv_generator->order_format ) {

		// remove item_meta column
		unset( $column_headers['item_meta'] );

		// get all item meta
		$all_item_meta = sv_wc_get_item_meta_for_orders( $csv_generator->ids );
				
		$item_meta_headers = array();

		foreach ( $all_item_meta as $meta_key ) {
			$item_meta_headers[ $meta_key ] = $meta_key;
		}

		$column_headers = sv_wc_array_insert_after( $column_headers, 'item_total', $item_meta_headers );
	}

	return $column_headers;
}
add_filter( 'wc_customer_order_csv_export_order_headers', 'sv_wc_csv_export_order_headers_split_item_meta', 10, 2 );


/**
 * CSV Order Export Line Item.
 *
 * Filter the individual line item entry to add the raw item for use in sv_wc_csv_export_order_row_one_row_per_item_split_item_meta()
 *
 * @param array $line_item {
 *     line item data in key => value format
 *     the keys are for convenience and not used for exporting. Make
 *     sure to prefix the values with the desired line item entry name
 * }
 *
 * @param array $item WC order item data
 * @return array $line_item
 */
function sv_wc_csv_export_order_line_item_add_raw_item( $line_item, $item, $product, $order, $csv_generator ) {
	if ( 'default_one_row_per_item' === $csv_generator->order_format ) {
		$line_item = array_merge( $line_item, array( 'raw_item' => $item ) );
	}
	return $line_item;
}
add_filter( 'wc_customer_order_csv_export_order_line_item', 'sv_wc_csv_export_order_line_item_add_raw_item', 10, 5 );

/**
 * CSV Order Export Row for One Row per Item.
 *
 * Filter the individual row data for the order export to add data for each item meta key
 *
 * @param array $order_data {
 *     order data in key => value format
 *     to modify the row data, ensure the key matches any of the header keys and set your own value
 * }
 * @param array $item
 * @param WC_Order $order WC Order object
 * @param WC_Customer_Order_CSV_Export_Generator $csv_generator, generator instance
 */
function sv_wc_csv_export_order_row_one_row_per_item_split_item_meta( $order_data, $item, $order, $csv_generator ) {

	$item_meta = new WC_Order_Item_Meta( $item['raw_item']['item_meta'] );

	foreach ( $item_meta->get_formatted() as $meta_key => $values ) {
		$order_data[ $meta_key ] = $values['value'];
	}

	return $order_data;
}
add_filter( 'wc_customer_order_csv_export_order_row_one_row_per_item', 'sv_wc_csv_export_order_row_one_row_per_item_split_item_meta', 10, 4 );


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

// add custom column headers for CSV export file
function wc_csv_export_modify_column_headers( $column_headers ) { 
 
	$new_headers = array(
		'shipping_phone' => 'Shipping Phone',
		'shipping_email' => 'Shipping Email',
		// add other column headers here in the format column_key => Column Name
	);
 
	return array_merge( $column_headers, $new_headers );
}
// add_filter( 'wc_customer_order_csv_export_order_headers', 'wc_csv_export_modify_column_headers' );      // Removed 1/13/2017 - no longer needed - Matt Ryan




// set the data for each of the new/custom columns
function wc_csv_export_modify_row_data( $order_data, $order, $csv_generator ) {
 
	$custom_data = array(
		'shipping_phone' => get_post_meta( $order->id, 'shipping_phone', true ),
		'shipping_email' => get_post_meta( $order->id, 'shipping_email', true ),
		// add other row data here in the format column_key => wc_csv_export_modify_row_data
	);
 
	$new_order_data = array();

	if ( isset( $csv_generator->order_format ) && ( 'default_one_row_per_item' == $csv_generator->order_format || 'legacy_one_row_per_item' == $csv_generator->order_format ) ) {

		foreach ( $order_data as $data ) {
			$new_order_data[] = array_merge( (array) $data, $custom_data );
		}

	} else {

		$new_order_data = array_merge( $order_data, $custom_data );
	}

	return $new_order_data;
}
// add_filter( 'wc_customer_order_csv_export_order_row', 'wc_csv_export_modify_row_data', 10, 3 );      // Removed 1/13/2017 - no longer needed - Matt Ryan

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