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

if ( ! class_exists( 'MSSMS_Settings_Alimtalk_Template' ) ) :

	class MSSMS_Settings_Alimtalk_Template {
		static function init() {
			add_filter( 'msshelper_get_mssms_template_list', array ( __CLASS__, 'get_template_list' ) );
		}

		static function get_plus_ids() {
			$profiles = get_option( 'mssms_profile_lists', array () );

			$plus_ids = array_column( $profiles, 'plus_id' );

			return array_combine( $plus_ids, $plus_ids );
		}

		static function get_template_list() {
			try {
				$profiles  = get_option( 'mssms_profile_lists', array () );
				$templates = array ();

				if ( empty( $templates ) ) {
					$templates = array ();

					foreach ( $profiles as $profile ) {
						$templates = array_merge( $templates, MSSMS_API_Kakao::get_template_list( $profile['plus_id'] ) );
					}
					$templates = array_combine( array_column( $templates, 'code' ), $templates );
					update_option( 'mssms_template_lists', json_decode( json_encode( $templates ), true ) );
				}

				return array_values( $templates );
			} catch ( Exception $e ) {

			}
		}

		static function update_settings() {
			include_once MSSMS()->plugin_path() . '/includes/admin/setting-manager/mssms-helper.php';

			$_REQUEST = array_merge( $_REQUEST, json_decode( stripslashes( $_REQUEST['values'] ), true ) );

			MSSMS_Helper::update_settings( self::get_template_setting() );

			wp_send_json_success();
		}

		static function get_template_setting() {
			ob_start();
			include( 'html/alimtalk-template-guide.php' );
			$guide = ob_get_clean();

			return array (
				'type'     => 'Page',
				'class'    => '',
				"showIf"   => array ( "mssms_use_alimtalk" => "yes" ),
				'title'    => __( '템플릿 설정', 'mshop-sms-s2' ),
				'elements' => array (
					array (
						"id"           => "mssms_template_list",
						"type"         => "SortableList",
						"title"        => __( "템플릿 목록", 'mshop-sms-s2' ),
						"listItemType" => "AlimtalkTemplateManager",
						"repeater"     => true,
						"slug"         => MSSMS()->slug(),
						"ajaxurl"      => admin_url( 'admin-ajax.php' ),
						"template"     => array (
							'rule_type' => 'alimtalk',
							'status'    => '',
							'buttons'   => array ()
						),
						"keyFields"    => array (
							'plus_id' => array (
								'type'  => 'text',
								'label' => __( '플러스친구 아이디', 'mshop-sms-s2' ),
							),
							'name'    => array (
								'type'  => 'text',
								'label' => __( '제목', 'mshop-sms-s2' ),
							),
						),
						"default"      => array (),
						"elements"     => array (
							'left'     => array (
								'type'              => 'Section',
								"hideSectionHeader" => true,
								"class"             => "eight wide column",
								'elements'          => array (
									array (
										"id"          => "plus_id",
										"title"       => __( "플러스친구 아이디", 'mshop-sms-s2' ),
										"className"   => "fluid",
										"type"        => "Select",
										"placeholder" => __( "플러스친구 아이디", 'mshop-sms-s2' ),
										"options"     => self::get_plus_ids()
									),
									array (
										"id"        => "name",
										"title"     => __( "제목", 'mshop-sms-s2' ),
										"className" => "fluid",
										"type"      => "Text"
									),
									array (
										"id"        => "content",
										"title"     => __( "내용", 'mshop-sms-s2' ),
										"className" => "fluid",
										"type"      => "TextArea",
										"rows"      => 10
									),
								)
							),
							'alimtalk' => array (
								"id"           => "buttons",
								"type"         => "SortableList",
								"class"        => "eight wide column",
								"title"        => __( "버튼설정", 'mshop-sms-s2' ),
								"listItemType" => "MShopRuleSortableList",
								"repeater"     => true,
								"template"     => array (
									'rule_type' => 'button_config',
								),
								"keyFields"    => array (
									'type' => array (
										'type'   => 'select',
										'label'  => '버튼타입',
										'option' => array (
											'DS' => __( '배송조회', 'mshop-sms-s2' ),
											'WL' => __( '웹링크', 'mshop-sms-s2' ),
											'AL' => __( '앱링크', 'mshop-sms-s2' ),
											'BK' => __( '봇키워드', 'mshop-sms-s2' ),
											'MD' => __( '메세지전달', 'mshop-sms-s2' ),
										),
									),
									'name' => array (
										'type'  => 'text',
										'label' => __( '버튼명', 'mshop-sms-s2' ),
									),
								),
								"default"      => array (),
								"elements"     => array (
									'type'              => 'Section',
									'class'             => 'eight wide column',
									"hideSectionHeader" => true,
									'elements'          => array (
										array (
											"id"          => "type",
											"title"       => __( "버튼타입", 'mshop-sms-s2' ),
											"className"   => "fluid",
											"type"        => "Select",
											"placeholder" => "버튼타입을 선택하세요",
											"options"     => array (
												'DS' => __( '배송조회', 'mshop-sms-s2' ),
												'WL' => __( '웹링크', 'mshop-sms-s2' ),
												'AL' => __( '앱링크', 'mshop-sms-s2' ),
												'BK' => __( '봇키워드', 'mshop-sms-s2' ),
												'MD' => __( '메세지전달', 'mshop-sms-s2' ),
											)
										),
										array (
											"id"        => "name",
											"title"     => __( '버튼 이름', 'mshop-sms-s2' ),
											"type"      => "Text",
											"className" => "fluid",
											'default'   => ''
										),
										array (
											"id"          => "linkMo",
											"showIf"      => array ( 'type' => 'WL' ),
											"title"       => __( '링크(모바일웹)', 'mshop-sms-s2' ),
											"type"        => "Text",
											"className"   => "fluid",
											'default'     => '',
											"placeholder" => "URL을 입력하세요.",
										),
										array (
											"id"          => "linkPc",
											"showIf"      => array ( 'type' => 'WL' ),
											"title"       => __( '링크(PC)', 'mshop-sms-s2' ),
											"type"        => "Text",
											"className"   => "fluid",
											'default'     => '',
											"placeholder" => "URL을 입력하세요.",
										),
										array (
											"id"          => "schemeIos",
											"showIf"      => array ( 'type' => 'AL' ),
											"title"       => __( '링크(안드로이드)', 'mshop-sms-s2' ),
											"type"        => "Text",
											"className"   => "fluid",
											'default'     => '',
											"placeholder" => "링크 주소를 입력하세요.",
										),
										array (
											"id"          => "schemeAndroid",
											"showIf"      => array ( 'type' => 'AL' ),
											"title"       => __( '링크(아이폰)', 'mshop-sms-s2' ),
											"type"        => "Text",
											"className"   => "fluid",
											'default'     => '',
											"placeholder" => "링크 주소를 입력하세요.",
										)
									)
								)
							)

						)
					),
					array(
						'type'           => 'Section',
						'hideSaveButton' => true,
						'title'          => __( '템플릿 작성 안내', 'mshop-sms-s2' ),
						'elements'       => array(
							array(
								"id"        => "alimtalk_template_guide",
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
			require_once MSSMS()->plugin_path() . '/includes/admin/setting-manager/mssms-helper.php';

			$settings = self::get_template_setting();

			self::enqueue_scripts();

			wp_localize_script( 'mshop-setting-manager', 'mshop_setting_manager', array (
				'element'     => 'mshop-setting-wrapper',
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'action'      => MSSMS()->slug() . '-update_alimtalk_template_settings',
				'settings'    => $settings,
				'slug'        => MSSMS()->slug(),
				'domain'      => preg_replace( '#^https?://#', '', home_url() ),
				'licenseInfo' => get_option( 'msl_license_' . MSSMS()->slug(), null )
			) );

			?>
            <script>
                jQuery( document ).ready( function () {
                    jQuery( this ).trigger( 'mshop-setting-manager', ['mshop-setting-wrapper', '100', <?php echo json_encode( MSSMS_Helper::get_settings( $settings ) ); ?>, null, null] );
                } );
            </script>

            <div id="mshop-setting-wrapper"></div>
			<?php
		}
	}

	MSSMS_Settings_Alimtalk_Template::init();

endif;






