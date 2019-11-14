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

if ( ! class_exists( 'MSSMS_API' ) ) {

	class MSSMS_API {

		const COMMAND_SEND         = 'send';
		const COMMAND_CANCEL       = 'cancel';
		const COMMAND_SMS_LIST     = 'sms_list';
		const COMMAND_REMAIN_COUNT = 'remain_count';

		private static function call( $query ) {
			$cl = curl_init();

			$url = 'http://message-api.codemshop.com/';
			curl_setopt( $cl, CURLOPT_URL, $url );
			curl_setopt( $cl, CURLOPT_POST, 1 );
			curl_setopt( $cl, CURLOPT_POSTFIELDS, $query );
			curl_setopt( $cl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $cl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $cl, CURLOPT_RETURNTRANSFER, true );

			$result = curl_exec( $cl );

			curl_close( $cl );

			return json_decode( $result );
		}

		public static function default_args() {
			$license_info = json_decode( get_option( 'msl_license_' . MSSMS()->slug(), null ), true );

			return array (
//				'license_key'    => $license_info['license_key'],
//				'activation_key' => $license_info['activation_key'],
//				'domain'         => $license_info['site_url'],
				'license_key'    => 'FB86-B0CA-C566-DC21-48AE-6717',
				'activation_key' => 'F560-9F97-21DA-A8FD-84BD-A759-F18D-982E',
				'domain'         => 'www.codemshop.com',
				'service'        => 'message',
				'version'        => '1.0',
			);
		}
		public static function send( $sender, $receiver, $message, $title, $destination = '', $reserved_time = null ) {
			$query = http_build_query(
				array_merge( self::default_args(),
					array (
						'command'     => self::COMMAND_SEND,
						'sender'      => $sender,
						'receiver'    => $receiver,
						'msg'         => $message,
						'title'       => $title,
						'destination' => $destination,
						'rdate'       => is_null( $reserved_time ) ? '' : date( 'Ymd', $reserved_time ),
						'rtime'       => is_null( $reserved_time ) ? '' : date( 'Hi', $reserved_time ),
					)
				)
			);

			return self::call( $query );
		}

	}
}


