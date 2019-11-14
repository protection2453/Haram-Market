<?php

/*
=====================================================================================
                ﻿엠샵 문자 알림 / Copyright 2015 by CodeM(c)
=====================================================================================

  [ 우커머스 버전 지원 안내 ]

   워드프레스 버전 : WordPress 4.3.1

   우커머스 버전 : WooCommerce 2.4.7


  [ 코드엠 플러그인 라이센스 규정 ]

   (주)코드엠에서 개발된 워드프레스  플러그인을 사용하시는 분들에게는 다음 사항에 대한 동의가 있는 것으로 간주합니다.

   1. 코드엠에서 개발한 워드프레스 우커머스용 엠샵 문자 알림 플러그인의 저작권은 (주)코드엠에게 있습니다.
   
   2. 플러그인은 사용권을 구매하는 것이며, 프로그램 저작권에 대한 구매가 아닙니다.

   3. 플러그인을 구입하여 다수의 사이트에 복사하여 사용할 수 없으며, 1개의 라이센스는 1개의 사이트에만 사용할 수 있습니다. 
      이를 위반 시 지적 재산권에 대한 손해 배상 의무를 갖습니다.

   4. 플러그인은 구입 후 1년간 업데이트를 지원합니다.

   5. 플러그인은 워드프레스, 테마, 플러그인과의 호환성에 대한 책임이 없습니다.

   6. 플러그인 설치 후 버전에 관련한 운용 및 관리의 책임은 사이트 당사자에게 있습니다.

   7. 다운로드한 플러그인은 환불되지 않습니다.

=====================================================================================
*/

if ( ! class_exists( 'MSSMS_SMS' ) ) {

	class MSSMS_SMS {
		protected static $profiles = null;
		protected static $settings = null;
		protected static $templates = null;
		protected static function get_setting_by_target( $target ) {
			$options = get_option( 'mssms_sms_' . $target . '_options', array () );

			$options = array_filter( $options, function ( $option ) {
				return 'yes' == mssms_get( $option, 'enable' );
			} );

			$order_statuses = array_column( $options, 'order_status' );

			return array_combine( $order_statuses, $options );
		}
		protected static function get_settings( $target ) {
			if ( is_null( self::$settings ) ) {
				self::$settings['admin'] = self::get_setting_by_target( 'admin' );
				self::$settings['user']  = self::get_setting_by_target( 'user' );
			}

			return self::$settings[ $target ];
		}
		protected static function get_template( $code ) {
			if ( empty( $code ) ) {
				return null;
			}

			if ( is_null( self::$templates ) ) {
				self::$templates = get_option( 'mssms_template_lists', array () );
			}

			return mssms_get( self::$templates, $code );
		}
		protected static function get_message_by_order_status( $order_status, $target ) {
			$settings = self::get_settings( $target );

			$message = mssms_get( $settings, $order_status );

			return mssms_get( $message, 'message' );
		}
		protected static function get_resend_params( $profile, $order_status, $target ) {
			$resend_params = array (
				'isResend' => 'false'
			);

			if ( 'yes' == $profile['is_resend'] && ! empty( $profile['resend_send_no'] ) ) {
				$settings = self::get_settings( $target );
				$setting  = mssms_get( $settings, $order_status );

				if ( $setting ) {
					if ( 'alimtalk' == $setting['resend_method'] ) {
						$resend_params = array (
							'isResend'     => 'true',
							'resendSendNo' => $profile['resend_send_no']
						);
					} else if ( 'sms' == $setting['resend_method'] ) {
						$resend_params = array (
							'isResend'      => 'true',
							'resendSendNo'  => $profile['resend_send_no'],
							'resendTitle'   => '',
							'resendContent' => '',
						);
					}
				}
			}

			return $resend_params;
		}
		protected static function get_profile( $plus_id ) {
			if ( is_null( self::$profiles ) ) {
				$profiles = get_option( 'mssms_profile_lists', array () );

				$plus_ids = array_column( $profiles, 'plus_id' );

				self::$profiles = array_combine( $plus_ids, $profiles );
			}

			return mssms_get( self::$profiles, $plus_id );
		}
		public static function send_sms( $type, $title, $message, $recipients, $send_no = '', $request_date = '' ) {
			if ( empty( $send_no ) ) {
				$send_no = get_option( 'mssms_rep_send_no' );
			}

			if ( empty( $message ) || empty( $recipients ) || empty( $send_no ) ) {
				return;
			}

			return MSSMS_API_SMS::send_message( $type, $send_no, $title, $message, $recipients, $request_date );
		}

		protected static function get_sms_type( $message, $template_params ) {
			foreach ( $template_params as $key => $value ) {
				$message = str_replace( '{' . $key . '}', $value, $message );
			}

			return mb_strwidth( $message ) > 90 ? 'LMS' : 'SMS';
		}
		public static function send_message( $order_id, $old_status, $new_status, $target ) {
			$order = wc_get_order( $order_id );
			if ( ! apply_filters( 'mshop_sms_order_payment_method_check', true, $order->get_payment_method(), $new_status, $order ) ) {
				return;
			}

			$message = self::get_message_by_order_status( $new_status, $target );

			$messages = apply_filters( 'mssms_sms_message_template', array ( $message ), $old_status, $new_status, $target, $order );

			$messages = array_filter( $messages );

			if ( $order && ! empty( $messages ) ) {
				if ( 'admin' == $target ) {
					$recipients = MSSMS_Manager::get_admin_phone_numbers();
				} else {
					$recipients[] = $order->get_billing_phone();
				}

				$template_params = MSSMS_Manager::get_template_params( $order );

				$recipients = array_map( function ( $receiver ) use ( $template_params ) {
					return array (
						'receiver'        => $receiver,
						'template_params' => $template_params
					);
				}, $recipients );

				foreach ( $messages as $message ) {
					$type = self::get_sms_type( $message, $template_params );
					self::send_sms( $type, '', $message, $recipients, '', MSSMS_Manager::get_request_date() );
				}
			}
		}
		public static function send_message_to_admin( $order_id, $old_status, $new_status ) {
			self::send_message( $order_id, $old_status, $new_status, 'admin' );
		}
		public static function send_message_to_user( $order_id, $old_status, $new_status ) {
			self::send_message( $order_id, $old_status, $new_status, 'user' );
		}
		public static function send_vact_info( $order_id, $rcv_num, $vacc_bank_name, $vacc_num, $vacc_name, $vacc_input_name, $vacc_date ) {
			try {
				$order   = wc_get_order( $order_id );
				$message = self::get_message_by_order_status( 'pafw-send-vact-info', 'user' );

				if ( $order && ! empty( $message ) ) {
					$recipients[] = $rcv_num;

					$template_params = MSSMS_Manager::get_template_params( $order );

					$template_params = array_merge( $template_params, array (
						'가상계좌은행명'  => $vacc_bank_name,
						'가상계좌번호'   => $vacc_num,
						'가상계좌예금주'  => $vacc_name,
						'가상계좌입금자'  => $vacc_input_name,
						'가상계좌입금기한' => $vacc_date
					) );

					$recipients = array_map( function ( $receiver ) use ( $template_params ) {
						return array (
							'receiver'        => $receiver,
							'template_params' => $template_params
						);
					}, $recipients );

					$type = self::get_sms_type( $message, $template_params );

					self::send_sms( $type, '', $message, $recipients, '', MSSMS_Manager::get_request_date() );
				}
			} catch ( Exception $e ) {

			}
		}
		public static function send_custom_message( $receiver, $send_no, $message, $subject ) {
			try {
				$type = self::get_sms_type( $message, array () );

				$recipients = array (
					array (
						'receiver'        => $receiver,
						'template_params' => array ()
					)
				);

				self::send_sms( $type, $subject, $message, $recipients, $send_no, MSSMS_Manager::get_request_date() );

			} catch ( Exception $e ) {

			}
		}
	}
}


