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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MSSMS_Settings_SMS_Send' ) ) :

	class MSSMS_Settings_SMS_Send {

		protected static $default_messages = null;

		static function init() {
			add_filter( 'msshelper_get_mssms_sms_admin_options', array ( __CLASS__, 'get_send_options' ) );
			add_filter( 'msshelper_get_mssms_sms_user_options', array ( __CLASS__, 'get_send_options' ) );
		}

		static function get_default_settings( $target, $order_status ) {
			if ( is_null( self::$default_messages ) ) {
				self::$default_messages['admin'] = apply_filters( 'mssms_default_messages_for_admin', array (
					'on-hold'             => '[{쇼핑몰명}] 주문번호 #{주문번호} {상품명} - 입금확인중에 있습니다.',
					'order-received'      => '[{쇼핑몰명}] 주문번호 #{주문번호} {상품명} - 입금완료, 상품 준비를 진행 해 주세요.',
					'processing'          => '[{쇼핑몰명}] 주문번호 #{주문번호} {상품명} - 상품 배송을 준비 해 주세요.',
					'shipping'            => '[{쇼핑몰명}] 주문번호 #{주문번호} {상품명} - 배송이 접수되었습니다.',
					'delayed'             => '[{쇼핑몰명}] 주문번호 #{주문번호} {상품명} - 상품 배송 지연이 발생되고 있습니다.',
					'completed'           => '[{쇼핑몰명}] 주문번호 #{주문번호} {상품명} - 주문처리가 완료되었습니다.',
					'cancelled'           => '[{쇼핑몰명}] 주문번호 #{주문번호} {상품명} - 주문취소 완료되었습니다.',
					'refunded'            => '[{쇼핑몰명}] 주문번호 #{주문번호} {상품명} - 환불완료이 완료되었습니다.',
					'failed'              => '[{쇼핑몰명}] 주문번호 #{주문번호} {상품명} - 주문이 실패했습니다.',
					'pafw-send-vact-info' => __( '[{쇼핑몰명}] 주문번호 {주문번호} / {가상계좌은행명} {가상계좌번호} 예금주 : {가상계좌예금주} / 입금액 : {주문금액} / 입금자명 : {가상계좌입금자} / 입금기한 {가상계좌입금기한}까지 / 주의 : 입금자명, 입금액이 다른경우 입금처리가 완료되지 않습니다.', 'mshop-sms-s2' )
				) );
				self::$default_messages['user']  = apply_filters( 'mssms_default_messages_for_user', array (
					'on-hold'             => '[{쇼핑몰명}] {고객명}님 주문번호 #{주문번호} - 입금액 {주문금액}원 - 우리은행 111-1111-1111 예금주 홍길동 으로 입금 부탁드리겠습니다.',
					'order-received'      => '[{쇼핑몰명}] {고객명}님 주문번호 #{주문번호} - 입금이 확인되었습니다.',
					'processing'          => '[{쇼핑몰명}] 주문번호 #{주문번호} - 주문이 접수되었습니다. 상품을 준비중에 있습니다.',
					'shipping'            => '[{쇼핑몰명}] 주문번호 #{주문번호} {고객명}님 - 좋은 소식을 알려드립니다. 주문하신 상품이 배송중에 있습니다.',
					'delayed'             => '[{쇼핑몰명}] 주문번호 #{주문번호} {고객명]님 - 상품 준비가 늦어져 죄송합니다. 빠른 배송을 위해 최선을 다하겠습니다.',
					'completed'           => '[{쇼핑몰명}] {고객명}님 주문번호 #{주문번호} - 주문하신 상품이 마음에 드셨나요? 다음번에도 {쇼핑몰명}에서 뵐 수 있길 바라겠습니다. 감사합니다.',
					'cancelled'           => '[{쇼핑몰명}] {고객명}님 주문번호 #{주문번호} - 주문이 취소처리가 되었습니다.',
					'refunded'            => '[{쇼핑몰명}] {고객명}님 주문번호 #{주문번호} - 주문에 대한 환불처리가 완료되었습니다.',
					'failed'              => '[{쇼핑몰명}] {고객명}님 - 주문에 실패했습니다. 다시한번 주문을 시도 해 주세요. ^^',
					'pafw-send-vact-info' => __( '[{쇼핑몰명}] 주문번호 {주문번호} / {가상계좌은행명} {가상계좌번호} 예금주 : {가상계좌예금주} / 입금액 : {주문금액} / 입금자명 : {가상계좌입금자} / 입금기한 {가상계좌입금기한}까지 / 주의 : 입금자명, 입금액이 다른경우 입금처리가 완료되지 않습니다.', 'mshop-sms-s2' )
				) );
			}

			return mssms_get( self::$default_messages[ $target ], $order_status );
		}
		static function get_send_options() {
			$current_filter = current_filter();
			$option_name    = str_replace( 'msshelper_get_', '', $current_filter );
			$target         = preg_replace( '/^mssms_sms_|_options$/', '', $option_name );
			$order_statuses = MSSMS_Manager::get_order_statuses();

			$options = get_option( $option_name, array () );
			if ( empty( $options ) ) {
				foreach ( $order_statuses as $key => $value ) {
					$options[] = array (
						'order_status'      => $key,
						'order_status_name' => $value,
						'message'           => self::get_default_settings( $target, $key )
					);
				}
			}
			$options = array_combine( array_column( $options, 'order_status' ), $options );
			foreach ( $order_statuses as $key => $label ) {
				if ( isset( $options[ $key ] ) ) {
					$options[ $key ]['order_status_name'] = $label;
				} else {
					$options[ $key ] = array (
						'order_status'      => $key,
						'order_status_name' => $label,
						'message'           => ''
					);
				}
			}
			$options = array_intersect_key( $options, $order_statuses );

			$options = array_diff_key( $options, array_flip( apply_filters( 'mssms_sms_send_remove_order_status', array ( 'pending' ) ) ) );

			return array_values( $options );
		}

		static function update_settings() {
			include_once MSSMS()->plugin_path() . '/includes/admin/setting-manager/mssms-helper.php';

			$_REQUEST = array_merge( $_REQUEST, json_decode( stripslashes( $_REQUEST['values'] ), true ) );

			MSSMS_Helper::update_settings( self::get_setting_fields() );

			wp_send_json_success();
		}

		static function get_setting_fields() {
			return array (
				'type'     => 'Tab',
				'id'       => 'mnp-setting-tab',
				'elements' => array (
					self::get_admin_setting(),
					self::get_user_setting(),
					self::get_product_settings(),
					self::get_category_settings()
				)
			);
		}

		static function get_admin_setting() {
			ob_start();
			include( 'html/sms-message-guide-for-admin.php' );
			$guide = ob_get_clean();

			return array (
				'type'     => 'Page',
				'class'    => 'active',
				'title'    => __( '관리자 발송 설정', 'mshop-sms-s2' ),
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '자동 발송 설정', 'mshop-sms-s2' ),
						'elements' => array (
							array (
								"id"        => "mssms_sms_admin_options",
								"className" => "sms_admin_option_table",
								"type"      => "SortableTable",
								"repeater"  => true,
								"default"   => array (),
								"template"  => array (
									"enable" => "yes"
								),
								"elements"  => array (
									array (
										"id"        => "enable",
										"title"     => "활성화",
										"className" => "center aligned one wide column fluid",
										"type"      => "Toggle",
										"default"   => "yes"
									),
									array (
										"id"            => "order_status_name",
										"title"         => __( "주문상태", 'mshop-sms-s2' ),
										"className"     => "center aligned three wide column fluid",
										"cellClassName" => "center aligned",
										"type"          => "Label",
									),
									array (
										"id"          => "message",
										"title"       => __( "문자내용", 'mshop-sms-s2' ),
										"className"   => "center aligned fluid",
										"type"        => "TextArea",
										"placeholder" => "발송 문구를 입력하신 후 사용해주세요.",
										"rows"        => 3
									)
								)
							)
						)
					),
					array (
						'type'           => 'Section',
						'hideSaveButton' => true,
						'title'          => __( '발송 설정 안내', 'mshop-sms-s2' ),
						'elements'       => array (
							array (
								"id"        => "mssms_sms_admin_options_desc",
								"className" => "fluid",
								"type"      => "Label",
								"readonly"  => "yes",
								"default"   => $guide
							)
						)
					)
				)
			);
		}

		static function get_user_setting() {
			ob_start();
			include( 'html/sms-message-guide-for-user.php' );
			$guide = ob_get_clean();

			return array (
				'type'     => 'Page',
				'class'    => '',
				'title'    => __( '고객 발송 설정', 'mshop-sms-s2' ),
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '자동 발송 설정', 'mshop-sms-s2' ),
						'elements' => array (
							array (
								"id"        => "mssms_sms_user_options",
								"className" => "sms_user_option_table",
								"type"      => "SortableTable",
								"repeater"  => true,
								"default"   => array (),
								"template"  => array (
									"enable" => "yes"
								),
								"elements"  => array (
									array (
										"id"        => "enable",
										"title"     => "활성화",
										"className" => "center aligned one wide column fluid",
										"type"      => "Toggle",
										"default"   => "yes"
									),
									array (
										"id"            => "order_status_name",
										"title"         => __( "주문상태", 'mshop-sms-s2' ),
										"className"     => "center aligned three wide column fluid",
										"cellClassName" => "center aligned",
										"type"          => "Label",
									),
									array (
										"id"          => "message",
										"title"       => __( "문자내용", 'mshop-sms-s2' ),
										"className"   => "center aligned fluid",
										"type"        => "TextArea",
										"placeholder" => "발송 문구를 입력하신 후 사용해주세요.",
										"rows"        => 3
									)
								)
							)
						)
					),
					array (
						'type'           => 'Section',
						'hideSaveButton' => true,
						'title'          => __( '발송 설정 안내', 'mshop-sms-s2' ),
						'elements'       => array (
							array (
								"id"        => "mssms_sms_user_options_desc",
								"className" => "fluid",
								"type"      => "Label",
								"readonly"  => "yes",
								"default"   => $guide
							)
						)
					)
				)
			);
		}

		static function get_product_settings() {
			ob_start();
			include( 'html/sms-message-guide-for-product.php' );
			$guide = ob_get_clean();

			return array (
				'type'     => 'Page',
				'class'    => '',
				'title'    => __( '상품별 발송 설정', 'mshop-sms-s2' ),
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '고객 자동 발송 설정', 'mshop-sms-s2' ),
						'elements' => array (
							array (
								"id"        => "mssms_sms_product_options",
								"className" => "sms_admin_option_table",
								"type"      => "SortableTable",
								"repeater"  => true,
								"editable"  => true,
								"sortable"  => true,
								"default"   => array (),
								"template"  => array (
									"enable" => "yes",
									"method" => "replace",
								),
								"elements"  => array (
									array (
										"id"        => "enable",
										"title"     => "활성화",
										"className" => "center aligned one wide column fluid",
										"type"      => "Toggle",
										"default"   => "yes"
									),
									array (
										"id"           => "products",
										"title"        => __( "상품", "mshop-sms-s2" ),
										"className"    => " four wide column search fluid",
										'multiple'     => true,
										'search'       => true,
										'disableClear' => true,
										"type"         => "SearchSelect",
										"default"      => "",
										'action'       => 'action=' . MSSMS()->slug() . '-target_search&type=product',
										"placeholder"  => __( "상품을 선택하세요.", "mshop-sms-s2" )
									),
									array (
										"id"          => "order_status",
										"title"       => __( "주문상태", "mshop-sms-s2" ),
										"className"   => " two wide column search fluid",
										"type"        => "Select",
										"default"     => "",
										"options"     => mssms_get_order_statuses(),
										"placeholder" => __( "주문상태", "mshop-sms-s2" )
									),
									array (
										"id"          => "message",
										"title"       => __( "문자내용", 'mshop-sms-s2' ),
										"className"   => "center aligned fluid",
										"type"        => "TextArea",
										"placeholder" => "발송 문구를 입력하신 후 사용해주세요.",
										"rows"        => 3
									),
									array (
										"id"        => "method",
										"title"     => __( "발송방법", "mshop-sms-s2" ),
										"className" => " two wide column search fluid",
										"type"      => "Select",
										'options'   => array (
											'replace'    => __( '교체발송', 'mshop-sms-s2' ),
											'additional' => __( '추가발송', 'mshop-sms-s2' ),
											'concat'     => __( '병합발송', 'mshop-sms-s2' ),
										)
									)
								)
							)
						)
					),
					array (
						'type'           => 'Section',
						'hideSaveButton' => true,
						'title'          => __( '발송 설정 안내', 'mshop-sms-s2' ),
						'elements'       => array (
							array (
								"id"        => "mssms_sms_product_options_desc",
								"className" => "fluid",
								"type"      => "Label",
								"readonly"  => "yes",
								"default"   => $guide
							)
						)
					)
				)
			);
		}

		static function get_category_settings() {
			ob_start();
			include( 'html/sms-message-guide-for-product.php' );
			$guide = ob_get_clean();

			return array (
				'type'     => 'Page',
				'class'    => '',
				'title'    => __( '카테고리별 발송 설정', 'mshop-sms-s2' ),
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '고객 자동 발송 설정', 'mshop-sms-s2' ),
						'elements' => array (
							array (
								"id"        => "mssms_sms_category_options",
								"className" => "sms_admin_option_table",
								"type"      => "SortableTable",
								"repeater"  => true,
								"editable"  => true,
								"sortable"  => true,
								"default"   => array (),
								"template"  => array (
									"enable" => "yes",
									"method" => "replace",
								),
								"elements"  => array (
									array (
										"id"        => "enable",
										"title"     => "활성화",
										"className" => "center aligned one wide column fluid",
										"type"      => "Toggle",
										"default"   => "yes"
									),
									array (
										"id"           => "categories",
										"title"        => __( "카테고리", "mshop-sms-s2" ),
										"className"    => " four wide column search fluid",
										'multiple'     => true,
										'search'       => true,
										'disableClear' => true,
										"type"         => "SearchSelect",
										"default"      => "",
										'action'       => 'action=' . MSSMS()->slug() . '-target_search&type=category',
										"placeholder"  => __( "카테고리를 선택하세요.", "mshop-sms-s2" )
									),
									array (
										"id"          => "order_status",
										"title"       => __( "주문상태", "mshop-sms-s2" ),
										"className"   => " two wide column search fluid",
										"type"        => "Select",
										"default"     => "",
										"options"     => mssms_get_order_statuses(),
										"placeholder" => __( "주문상태", "mshop-sms-s2" )
									),
									array (
										"id"          => "message",
										"title"       => __( "문자내용", 'mshop-sms-s2' ),
										"className"   => "center aligned fluid",
										"type"        => "TextArea",
										"placeholder" => "발송 문구를 입력하신 후 사용해주세요.",
										"rows"        => 3
									),
									array (
										"id"        => "method",
										"title"     => __( "발송방법", "mshop-sms-s2" ),
										"className" => " two wide column search fluid",
										"type"      => "Select",
										'options'   => array (
											'replace'    => __( '교체발송', 'mshop-sms-s2' ),
											'additional' => __( '추가발송', 'mshop-sms-s2' ),
											'concat'     => __( '병합발송', 'mshop-sms-s2' ),
										)
									)
								)
							)
						)
					),
					array (
						'type'           => 'Section',
						'hideSaveButton' => true,
						'title'          => __( '발송 설정 안내', 'mshop-sms-s2' ),
						'elements'       => array (
							array (
								"id"        => "mssms_sms_category_options_desc",
								"className" => "fluid",
								"type"      => "Label",
								"readonly"  => "yes",
								"default"   => $guide
							)
						)
					)
				)
			);
		}

		static function enqueue_scripts() {
			wp_enqueue_script( 'underscore' );
			wp_enqueue_style( 'mshop-setting-manager', MSSMS()->plugin_url() . '/includes/admin/setting-manager/css/setting-manager.min.css' );
			wp_enqueue_script( 'mshop-setting-manager', MSSMS()->plugin_url() . '/includes/admin/setting-manager/js/setting-manager.min.js', array (
				'jquery',
				'jquery-ui-core'
			) );
		}
		public static function output() {
			require_once( ABSPATH . 'wp-admin/includes/dashboard.php' );

			wp_dashboard_setup();

			require_once MSSMS()->plugin_path() . '/includes/admin/setting-manager/mssms-helper.php';

			$settings = self::get_setting_fields();

			self::enqueue_scripts();

			wp_localize_script( 'mshop-setting-manager', 'mshop_setting_manager', array (
				'element'     => 'mshop-setting-wrapper',
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'action'      => MSSMS()->slug() . '-update_sms_send_settings',
				'settings'    => $settings,
				'slug'        => MSSMS()->slug(),
				'domain'      => preg_replace( '#^https?://#', '', home_url() ),
				'licenseInfo' => get_option( 'msl_license_' . MSSMS()->slug(), null )
			) );

			?>
            <script>
                jQuery( document ).ready( function () {
                    jQuery( this ).trigger( 'mshop-setting-manager', [ 'mshop-setting-wrapper', '100', <?php echo json_encode( MSSMS_Helper::get_settings( $settings ) ); ?>, null, null ] );
                } );
            </script>
            <style>
                .ui.table.sms_admin_option_table td,
                .ui.table.sms_user_option_table td {
                    border-top: 1px solid rgba(34, 36, 38, 0.1) !important;
                }
            </style>
            <div id="mshop-setting-wrapper"></div>
			<?php
		}
	}

	MSSMS_Settings_SMS_Send::init();
endif;

