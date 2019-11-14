<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
function mssms_get( $array, $key, $default = '' ) {
	return ! empty( $array[ $key ] ) ? $array[ $key ] : $default;
}
function mssms_get_alimtalk_template_list( $statuses = array ( 'APR' ) ) {
	$options   = array ();
	$templates = get_option( 'mssms_template_lists', array () );

	if ( ! empty( $templates ) ) {
		foreach ( $templates as $template ) {
			if ( in_array( $template['status'], $statuses ) ) {
				$options[ $template['code'] ] = $template['name'];
			}
		}
	}

	return $options;
}
function mssms_wpml_get_default_language() {
	if ( has_filter( 'wpml_object_id' ) ) {
		global $sitepress;

		return $sitepress->get_default_language();
	} else {
		return '';
	}
}
function mssms_get_order_statuses() {
	$order_statuses = array ();

	foreach ( wc_get_order_statuses() as $status => $status_name ) {
		$status                    = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
		$order_statuses[ $status ] = $status_name;
	}

	return $order_statuses;
}

if ( MSSMS_Manager::is_enabled( 'alimtalk' ) ) {
	add_action( 'woocommerce_order_status_changed', array ( 'MSSMS_Kakao', 'send_message_to_admin' ), 100, 3 );
	add_action( 'woocommerce_order_status_changed', array ( 'MSSMS_Kakao', 'send_message_to_user' ), 100, 3 );
	add_action( 'mssms_send_alimtalk', array ( 'MSSMS_Kakao', 'send_alimtalk' ), 10, 4 );
	add_action( 'send_vact_info', array ( 'MSSMS_Kakao', 'send_vact_info' ), 10, 7 );

	add_filter( 'mssms_alimtalk_message_template', array ( 'MSSMS_Message_By_Rule', 'get_alimtalk_message_template' ), 10, 5 );
}

if ( MSSMS_Manager::is_enabled( 'sms' ) ) {
	add_action( 'woocommerce_order_status_changed', array ( 'MSSMS_SMS', 'send_message_to_admin' ), 100, 3 );
	add_action( 'woocommerce_order_status_changed', array ( 'MSSMS_SMS', 'send_message_to_user' ), 100, 3 );
	add_action( 'mssms_send_message', array ( 'MSSMS_SMS', 'send_message' ), 10, 6 );
	add_action( 'send_vact_info', array ( 'MSSMS_SMS', 'send_vact_info' ), 10, 7 );

	add_filter( 'mssms_sms_message_template', array ( 'MSSMS_Message_By_Rule', 'get_sms_message_template' ), 10, 5 );
	add_action( 'mshop_send_sms', array ( 'MSSMS_SMS', 'send_custom_message' ), 10, 4 );
}
