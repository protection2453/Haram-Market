<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MSSMS_Manager' ) ) {

	class MSSMS_Manager {
		protected static $admin_list = null;
		protected static $send_no_list = null;
		protected static $send_block_times = null;

		protected static $is_enabled = null;
		protected static $order_statuses = null;


		public static function is_enabled( $service ) {
			if ( is_null( self::$is_enabled ) ) {
				self::$is_enabled = array (
					'sms'      => get_option( 'mshop_sms_enable', 'no' ),
					'alimtalk' => get_option( 'mssms_use_alimtalk', 'no' ),
				);
			}

			return 'yes' == self::$is_enabled[ $service ];
		}

		public static function get_point() {
			try {
				$license_info = MSSMS()->license_manager->get_license();

				if ( $license_info && intval( preg_replace( '~\D~', '', $license_info->point ) ) > 0 ) {
					return sprintf( '<span style="color:#2185d0">%s 포인트</span><br>문자 발송을 위한 포인트는 <a target="_blank" href="https://www.codemshop.com/shop/sms_out/">코드엠샵 홈페이지</a>에서 충전하실 수 있습니다.', $license_info->point );
				} else {
					return sprintf( '<span style="color:red">보유포인트가 없습니다.<br><a target="_blank" href="https://www.codemshop.com/shop/sms_out/">코드엠샵 홈페이지</a>에서 포인트 충전 후 이용해주세요.</span>' );
				}
			}catch (Exception $e ) {
				return $e->getMessage();
			}
		}
		public static function get_admin_list() {
			if ( is_null( self::$admin_list ) ) {
				$admin_list = get_option( 'mssms_admins', array () );

				self::$admin_list = array_filter( $admin_list, function ( $admin ) {
					return 'yes' == mssms_get( $admin, 'enable' ) && ! empty( mssms_get( $admin, 'phone' ) );
				} );
			}

			return self::$admin_list;
		}
		public static function get_admin_phone_numbers() {
			$admin_list = self::get_admin_list();

			return array_column( $admin_list, 'phone' );
		}
		public static function get_send_no_list() {
			if ( is_null( self::$send_no_list ) ) {
				$send_no_list = get_option( 'mssms_send_no_list', array () );

				self::$send_no_list = array_filter( $send_no_list, function ( $send_no ) {
					return 'Y' == mssms_get( $send_no, 'use_yn' ) && 'N' == mssms_get( $send_no, 'block_yn' );
				} );
			}

			return self::$send_no_list;
		}
		public static function get_send_nos() {
			$send_no_list = self::get_send_no_list();

			return array_column( $send_no_list, 'send_no' );
		}


		public static function get_time_options() {
			$options = array ();
			for ( $i = 0; $i <= 24; $i++ ) {
				$hour                   = sprintf( '%02d', $i );
				$options[ '_' . $hour ] = $hour . ':00';
			}

			return $options;
		}

		public static function get_order_statuses() {
			if ( is_null( self::$order_statuses ) ) {
				self::$order_statuses = array ();
				foreach ( wc_get_order_statuses() as $status => $label ) {
					self::$order_statuses[ str_replace( 'wc-', '', $status ) ] = $label;
				}

				self::$order_statuses['pafw-send-vact-info'] = __( '가상계좌 무통장 입금 알림', 'mshop-sms-s2' );

				self::$order_statuses = apply_filters( 'mssms_order_statuses', self::$order_statuses );
			}

			return self::$order_statuses;
		}
		public static function get_template_params( $order = null ) {
			$product_name = array ();
			$product_info = '';
			if ( $order ) {
				$order_items = $order->get_items();
				$order_item  = current( $order_items );
				$product_info = $order_item->get_name();
				if ( count( $order_items ) > 1 ) {
					$product_info = sprintf( "%s외 %d건", $product_info, count( $order_items ) - 1 );
				}
				foreach ( $order_items as $order_item ) {
					$product = $order_item->get_product();
					if ( $product && ! empty( $product->get_sku() ) ) {
						$product_name[] = sprintf( '[%s] %s', $product->get_sku(), $order_item->get_name() );
					} else {
						$product_name[] = $order_item->get_name();
					}
				}
			}

			return apply_filters( 'mssms_get_template_params', array (
				'쇼핑몰명'  => get_option( "blogname" ),
				'고객명'   => $order ? $order->get_billing_first_name() . $order->get_billing_last_name() : '',
				'주문금액'  => $order ? number_format( $order->get_total(), wc_get_price_decimals() ) : '',
				'주문번호'  => $order ? $order->get_order_number() : '',
				'상품명'   => implode( ',', $product_name ),
				'주문일'   => $order ? date( 'Y-m-d', $order->get_date_created()->getTimestamp() ) : '',
				'상품정보'  => $product_info,
				'송장번호'  => apply_filters( 'mshop_sms_ship_number', '', $order->get_id() ),
				'배송업체명' => apply_filters( 'mshop_sms_shipping_company_name', '', $order->get_id() ),
			), $order );
		}
		public static function get_send_block_times() {
			if ( is_null( self::$send_block_times ) ) {
				self::$send_block_times = array ();

				$send_block_time_list = get_option( 'mssms_send_block_time', array () );
				$send_block_time_list = array_filter( $send_block_time_list, function ( $block_time ) {
					return 'yes' == mssms_get( $block_time, 'enable' ) && ! empty( $block_time['from'] ) && ! empty( $block_time['to'] );
				} );
				foreach ( $send_block_time_list as $block_time ) {
					$from = intval( preg_replace( '~\D~', '', $block_time['from'] ) );
					$to   = intval( preg_replace( '~\D~', '', $block_time['to'] ) );

					if ( $from > $to ) {
						self::$send_block_times[] = array ( 'from' => $from, 'to' => 24 );
						self::$send_block_times[] = array ( 'from' => 00, 'to' => $to );
					} else {
						self::$send_block_times[] = array ( 'from' => $from, 'to' => $to );
					}
				}
			}

			return self::$send_block_times;
		}
		public static function get_request_date() {
			$request_date = '';

			$hour        = intval( date( 'H', strtotime( current_time( 'mysql' ) ) ) );
			$adjust_hour = $hour;
			$block_times = self::get_send_block_times();

			foreach ( $block_times as $block_time ) {
				if ( $adjust_hour >= $block_time['from'] && $adjust_hour < $block_time['to'] ) {
					$adjust_hour = '24' == $block_time['to'] ? 0 : $block_time['to'];
				}
			}

			if ( $hour != $adjust_hour ) {
				if ( $adjust_hour > $hour ) {
					$request_date = date( sprintf( 'Y-m-d %02d:00', $adjust_hour ), strtotime( current_time( 'mysql' ) ) );
				} else {
					$request_date = date( sprintf( 'Y-m-d %02d:00', $adjust_hour ), strtotime( current_time( 'mysql' ) . '+1 days' ) );
				}
			}

			return $request_date;
		}
		public static function use_point_shortage_notification() {
			return 'yes' == get_option( 'mssms_send_point_shortage_notification', 'yes' );
		}
	}

}
