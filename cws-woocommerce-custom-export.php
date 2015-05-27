<?php
/*
Plugin Name: CWS WooCommerce Custom Export
Plugin URI: http://capwebsolutions.com
Description: CWS WooCommerce Custom Export
Author: Matt Ryan | Cap Web Solutions
Version: 1.0
Author URI:http://mattry.com
*/
/*
* Plugin to setup custom CSV export for WooCommerce order for Natures Lawn & Garden. 
* Requires WooCommerce Custom Order CSV Export Extension
*/

// remove a column
function wc_csv_export_remove_column( $column_headers ) {

// the list of column keys can be found in /wp-content/plugins/woocommerce-customer-order-csv-export/includes/class-wc-customer-order-csv-export-generator.php
// Comment out the lines pertaining to the columns you want to REMAIN in the export
	unset( $column_headers['order_id'] );
//	unset( $column_headers['order_number'] );
//	unset( $column_headers['order_date'] );
//	unset( $column_headers['status'] );
//	unset( $column_headers['shipping_total'] );
	unset( $column_headers['shipping_tax_total'] );
	unset( $column_headers['tax_total'] );
	unset( $column_headers['cart_discount'] );
	unset( $column_headers['order_discount'] );
//	unset( $column_headers['discount_total'] );
//	unset( $column_headers['order_total'] );
	unset( $column_headers['refunded_total'] );
	unset( $column_headers['order_currency'] );
//	unset( $column_headers['payment_method'] );
//	unset( $column_headers['shipping_method'] );
	unset( $column_headers['customer_id'] );
//	unset( $column_headers['billing_first_name'] );
//	unset( $column_headers['billing_last_name'] );
//	unset( $column_headers['billing_company'] );
//	unset( $column_headers['billing_email'] );
//	unset( $column_headers['billing_phone'] );
//	unset( $column_headers['billing_address_1'] );
//	unset( $column_headers['billing_address_2'] );
//	unset( $column_headers['billing_postcode'] );
//	unset( $column_headers['billing_city'] );
//	unset( $column_headers['billing_state'] );
//	unset( $column_headers['billing_country'] );
//	unset( $column_headers['shipping_first_name'] );
//	unset( $column_headers['shipping_last_name'] );
//	unset( $column_headers['shipping_address_1'] );
//	unset( $column_headers['shipping_address_2'] );
//	unset( $column_headers['shipping_postcode'] );
//	unset( $column_headers['shipping_city'] );
//	unset( $column_headers['shipping_state'] );
//	unset( $column_headers['shipping_country'] );
	unset( $column_headers['shipping_company'] );
	unset( $column_headers['customer_note'] );
 	unset( $column_headers['order_notes'] );
  	unset( $column_headers['download_permissions'] );
  	unset( $column_headers['coupon_items'] );
  
	return $column_headers;
}
add_filter( 'wc_customer_order_csv_export_order_headers', 'wc_csv_export_remove_column' );


// reorder columns
function wc_csv_export_reorder_columns( $column_headers ) {

	// remove order total from the original set of column headers, otherwise it will be duplicated
//	unset( $column_headers['order_total'] );
  
	$new_column_headers = array();
	
	foreach ( $column_headers as $column_key => $column_name ) {
		
		$new_column_headers[ $column_key ] = $column_name;
		
		if ( 'order_number' == $column_key ) {
			
			// add order total immediately after order_number
			
//		  	$new_column_headers[' '] = ' '; // product number
		  	$new_column_headers['item_sku'] = 'item_sku';  // sku
//		  	$new_column_headers[' '] = ' ';  // weight
		  	$new_column_headers['item_name']     = 'item_name';  // product name
//		  	$new_column_headers[' '] = ' ';  // provider
//		  	$new_column_headers[' '] = ' ';  // price
		  	$new_column_headers['item_quantity'] = 'item_quantity';  // qty
		  	$new_column_headers['username'] = 'username';  // username
		  	$new_column_headers['order_total'] = 'order_total';  // total
		  	$new_column_headers['discount_total'] = 'discount_total';  // discount
//		  	$new_column_headers[' '] = ' ';  // coupon
//		  	$new_column_headers[' '] = ' ';  // saving
		  	$new_column_headers['shipping_method'] = 'shipping_method';  // delivery method
		  	$new_column_headers['shipping_method'] = 'shipping_method';  // shipping cost
		  	$new_column_headers['order_date'] = 'order_date';  // date
//		  	$new_column_headers[' '] = ' ';  // time
		  	$new_column_headers['status'] = 'status';  // status
		  	$new_column_headers['payment_method'] = 'payment_method';  // payment method
//		  	$new_column_headers[' '] = ' ';  // personal title
//		  	$new_column_headers[' '] = ' ';  // personal first
//		  	$new_column_headers[' '] = ' ';  // personal last
//		  	$new_column_headers[' '] = ' ';  // personal company
//		  	$new_column_headers[' '] = ' ';  // billing name
		  	$new_column_headers['billing_address_1'] = 'billing_address_1';  // billing address
		  	$new_column_headers['billing_address_2'] = 'billing_address_2';  // billing address2
		  	$new_column_headers['billing_city'] = 'billing_city';  // billing city
		  	$new_column_headers['billing_state'] = 'billing_state';  // billing state
		  	$new_column_headers['billing_country'] = 'billing_country';  // billing country
		  	$new_column_headers['billing_postcode'] = 'billing_postcode';  // billing postal code
//		  	$new_column_headers[' '] = ' ';  // shipping name
		  	$new_column_headers['shipping_address_1'] = 'shipping_address_1';  // shipping address
		  	$new_column_headers['shipping_address_2'] = 'shipping_address_2';  // shipping address2
		  	$new_column_headers['shipping_city'] = 'shipping_city';  // shipping city
		  	$new_column_headers['shipping_state'] = 'shipping_state';  // shipping state
		  	$new_column_headers['shipping_country'] = 'shipping_country';  // shipping country
  		  	$new_column_headers['shipping_postcode'] = 'shipping_postcode';  // shipping postal code
		  	$new_column_headers['billing_phone'] = 'billing_phone';  // phone
//		  	$new_column_headers[' '] = ' ';  // fax
//		  	$new_column_headers[' '] = ' ';  // web site
		  	$new_column_headers['billing_email'] = 'billing_email';  // email
		}
	}
	
	return $new_column_headers;
}
add_filter( 'wc_customer_order_csv_export_order_headers', 'wc_csv_export_reorder_columns' );


// rename a column
function wc_csv_export_rename_column( $column_headers ) {

	// rename the order_notes column to notes
	// make sure to not change the key (`order_notes`) in this case
	// as this matches the column to the relevant data
	// simply change the value of the array to change the column header that's exported
	$column_headers['order_notes'] = 'Notes'; 
 
 //		  	$column_headers[' '] = ' '; // product number
		  	$column_headers['item_sku'] = 'SKU';  // sku
//		  	$column_headers[' '] = ' ';  // weight
		  	$column_headers['item_name']     = 'PRODUCT NAME';  // product name
//		  	$column_headers[' '] = 'PROVIDER';  // provider
//		  	$column_headers[' '] = 'PRICE';  // price
		  	$column_headers['item_quantity'] = 'QTY';  // qty
		  	$column_headers['username'] = 'USERNAME';  // username
		  	$column_headers['order_total'] = 'TOTAL';  // total
		  	$column_headers['discount_total'] = 'DISCOUNT';  // discount
//		  	$column_headers[' '] = 'COUPON';  // coupon
//		  	$column_headers[' '] = 'SAVING';  // saving
		  	$column_headers['shipping_method'] = 'DELIVERY METHOD';  // delivery method
		  	$column_headers['shipping_cost'] = 'SHIPPING COST';  // shipping cost
		  	$column_headers['order_date'] = 'DATE';  // date
//		  	$column_headers[' '] = 'TIME';  // time
		  	$column_headers['status'] = 'STATUS';  // status
		  	$column_headers['payment_method'] = 'PAYMENT METHOD';  // payment method
//		  	$column_headers[' '] = 'PERSONAL TITLE';  // personal title
//		  	$column_headers[' '] = 'PERSONAL FIRST';  // personal first
//		  	$column_headers[' '] = 'PERSONAL LAST';  // personal last
//		  	$column_headers[' '] = 'PERSONAL COMPANY';  // personal company
//		  	$column_headers[' '] = 'BILLING NAME';  // billing name
		  	$column_headers['billing_address_1'] = 'BILLING ADDRESS';  // billing address
		  	$column_headers['billing_address_2'] = 'BILLING ADDRESS 2';  // billing address2
		  	$column_headers['billing_city'] = 'BILLING CITY';  // billing city
		  	$column_headers['billing_state'] = 'BILLING STATE';  // billing state
		  	$column_headers['billing_country'] = 'BILLING COUNTRY';  // billing country
		  	$column_headers['billing_postcode'] = 'BILLING POSTAL CODE';  // billing postal code
//		  	$column_headers[' '] = 'SHIPPING NAME';  // shipping name
		  	$column_headers['shipping_address_1'] = 'SHIPPING ADDRESS';  // shipping address
		  	$column_headers['shipping_address_2'] = 'SHIPPING ADDRESS 2';  // shipping address2
		  	$column_headers['shipping_city'] = 'SHIPPING CITY';  // shipping city
		  	$column_headers['shipping_state'] = 'SHIPPING STATE';  // shipping state
		  	$column_headers['shipping_country'] = 'SHIPPING COUNTRY';  // shipping country
  		  	$column_headers['shipping_postcode'] = 'SHIPPING POSTAL CODE';  // shipping postal code
		  	$column_headers['billing_phone'] = 'PHONE';  // phone
//		  	$column_headers[' '] = 'FAX';  // fax
//		  	$column_headers[' '] = 'WEB SITE';  // web site
		  	$column_headers['billing_email'] = 'E-MAIL';  // email 
  
	return $column_headers;
}
add_filter( 'wc_customer_order_csv_export_order_headers', 'wc_csv_export_rename_column' );