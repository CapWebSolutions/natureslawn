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
			// $new_headers['new_size'] = 'new_size';
		}

	}
	return $new_headers;
}
add_filter( 'wc_customer_order_csv_export_order_headers', 'sv_wc_csv_export_add_product_attributes_column' );

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
add_filter( 'wc_customer_order_csv_export_order_line_item', 'sv_wc_csv_export_add_product_to_order_line_item', 10, 3 );

/**
 * Add the product attributes in the format:
 *
 * attribute name=attribute value 1, attribute value 2, etc.
 *
 * @param array $order_data
 * @param array $item
 * @return array
 */
function sv_wc_csv_export_add_product_attributes( $order_data, $item ) {

	$order_data['product_attributes'] = '';
    // $order_data['new_size'] = 'zzz';
	$count                            = 1;

	foreach ( array_keys( $item['product']->get_attributes() ) as $attribute ) {
		
		// $order_data['product_attributes'] .= str_replace( 'pa_', '', $attribute ) . '=' . $item['product']->get_attribute( $attribute );
		// add a semicolon divider if there are multiple attributes and it's not the last one
		if ( count( $item['product']->get_attributes() ) > 1 && $count !== count( $item['product']->get_attributes() ) ) {
			$order_data['product_attributes'] .= ';';
		}
		$count++;
		
    // The identifier we want is $item['product']->get_attribute( $attribute )

    	// $order_data['new_size'] = $item['product']->get_attribute( $attribute );

		// $order_data['new_size'] = $item['product']->get_attribute( 'size' );


	}
	return $order_data;
}
add_filter( 'wc_customer_order_csv_export_order_row_one_row_per_item', 'sv_wc_csv_export_add_product_attributes', 10, 2 );
