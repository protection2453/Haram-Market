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

if ( ! class_exists( 'MSSMS_Kakao' ) ) {

	class MSSMS_Kakao {
		protected static $profiles = null;
		protected static $alimtalk_settings = null;
		protected static $templates = null;
		protected static function get_alimtalk_setting_by_target( $target ) {
			$options = get_option( 'mssms_alimtalk_' . $target . '_options', array () );

			$options = array_filter( $options, function ( $option ) {
				return 'yes' == mssms_get( $option, 'enable' );
			} );

			$order_statuses = array_column( $options, 'order_status' );

			return array_combine( $order_statuses, $options );
		}
		protected static function get_alimtalk_settings( $target ) {
			if ( is_null( self::$alimtalk_settings ) ) {
				self::$alimtalk_settings['admin'] = self::get_alimtalk_setting_by_target( 'admin' );
				self::$alimtalk_settings['user']  = self::get_alimtalk_setting_by_target( 'user' );
			}

			return self::$alimtalk_settings[ $target ];
		}
		public static function get_template( $code ) {
			if ( empty( $code ) ) {
				return null;
			}

			if ( is_null( self::$templates ) ) {
				self::$templates = get_option( 'mssms_template_lists', array () );
			}

			return mssms_get( self::$templates, $code );
		}
		protected static function get_template_code_by_order_status( $order_status, $target ) {
			$settings = self::get_alimtalk_settings( $target );

			$template = mssms_get( $settings, $order_status );

			return mssms_get( $template, 'template_code' );
		}
		protected static function get_resend_params( $profile, $order_status, $target ) {
			$resend_params = array (
				'isResend' => 'false'
			);

			if ( 'yes' == $profile['is_resend'] && ! empty( $profile['resend_send_no'] ) ) {
				$settings = self::get_alimtalk_settings( $target );
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
		public static function send_alimtalk( $template_code, $recipients, $template_params, $resend = false ) {
			$template = self::get_template( $template_code );

			if ( empty( $recipients ) || ! empty( $template ) ) {
				$profile = self::get_profile( $template['plus_id'] );

				if ( empty( $profile ) ) {
					return;
				}

				try {
					$messages = array ();

					if ( is_array( $resend ) ) {
						$is_resend     = 'true' == mssms_get( $resend, 'isResend' );
						$resend_params = $resend;
					} else {
						$is_resend = boolval( $resend );
					}

					if ( ! $is_resend ) {
						$resend_params = array ( 'isResend' => 'false' );
					} else if ( $is_resend && empty( $resend_params ) ) {
						$resend_params = array (
							'isResend'     => 'true',
							'resendSendNo' => $profile['resend_send_no']
						);
					}

					foreach ( $recipients as $recipient ) {
						$messages[] = array (
							'receiver'        => $recipient,
							'template_params' => $template_params,
							"resend"          => $resend_params
						);
					}

					MSSMS_API_Kakao::send_message( $template['plus_id'], $template['code'], MSSMS_Manager::get_request_date(), $messages );

				} catch ( Exception $e ) {
					echo $e->getMessage();
				}
			}
		}
		public static function send_message( $order_id, $old_status, $new_status, $target ) {
			$order = wc_get_order( $order_id );
			if ( ! apply_filters( 'mshop_sms_order_payment_method_check', true, $order->get_payment_method(), $new_status, $order ) ) {
				return;
			}

			$template_code = self::get_template_code_by_order_status( $new_status, $target );

			$template_infos = array (
				array (
					'template_code' => $template_code,
					'resend_params' => array ()
				)
			);

			$template_infos = apply_filters( 'mssms_alimtalk_message_template', $template_infos, $old_status, $new_status, $target, $order );

			if ( $order && ! empty( $template_infos ) ) {
				if ( 'admin' == $target ) {
					$recipients = MSSMS_Manager::get_admin_phone_numbers();
				} else {
					$recipients[] = $order->get_billing_phone();
				}
				$template_params = MSSMS_Manager::get_template_params( $order );

				foreach ( $template_infos as $template_info ) {
					if ( ! empty( $template_info['template_code'] ) ) {
						$template = self::get_template( $template_info['template_code'] );

						if ( $template ) {
							$profile = self::get_profile( $template['plus_id'] );
							if ( empty( $template_info['resend_params'] ) ) {
								$resend_params = self::get_resend_params( $profile, $new_status, $target );
							} else {
								$resend_params = $template_info['resend_params'];
							}

							self::send_alimtalk( $template_info['template_code'], $recipients, $template_params, $resend_params );
						}
					}
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
			$order         = wc_get_order( $order_id );
			$template_code = self::get_template_code_by_order_status( 'pafw-send-vact-info', 'user' );
			$template      = self::get_template( $template_code );

			if ( $order && ! empty( $template_code ) ) {
				$recipients[] = $rcv_num;

				$profile         = self::get_profile( $template['plus_id'] );
				$resend_params   = self::get_resend_params( $profile, 'pafw-send-vact-info', 'user' );
				$template_params = MSSMS_Manager::get_template_params( $order );

				$template_params = array_merge( $template_params, array (
					'가상계좌은행명'  => $vacc_bank_name,
					'가상계좌번호'   => $vacc_num,
					'가상계좌예금주'  => $vacc_name,
					'가상계좌입금자'  => $vacc_input_name,
					'가상계좌입금기한' => $vacc_date
				) );

				self::send_alimtalk( $template_code, $recipients, $template_params, $resend_params );
			}
		}
	}
}


