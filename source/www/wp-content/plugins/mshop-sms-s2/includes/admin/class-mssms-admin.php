<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'MSSMS_Admin' ) ) :

	class MSSMS_Admin {
		protected $retry_result = array();

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		function admin_menu() {
			add_menu_page( __( '엠샵 문자 알림', 'mshop-sms-s2' ), __( '엠샵 문자 알림', 'mshop-sms-s2' ), 'manage_options', 'mssms_settings', '', MSSMS()->plugin_url() . '/assets/images/mshop-icon.png', '20.9387640384' );

			add_submenu_page( 'mssms_settings', __( '문자 기본 설정', 'mshop-sms-s2' ), __( '문자 기본 설정', 'mshop-sms-s2' ), 'manage_woocommerce', 'mssms_settings', array( 'MSSMS_Settings_Sms', 'output' ) );
			if ( MSSMS_Manager::is_enabled( 'sms' ) ) {
				add_submenu_page( 'mssms_settings', __( '문자 자동 발송', 'mshop-sms-s2' ), __( '문자 자동 발송', 'mshop-sms-s2' ), 'manage_woocommerce', 'mssms_settings_sms_send', array( 'MSSMS_Settings_SMS_Send', 'output' ) );
				add_submenu_page( 'mssms_settings', __( '문자 개별 발송', 'mshop-sms-s2' ), __( '문자 개별 발송', 'mshop-sms-s2' ), 'manage_woocommerce', 'mssms_send_single_message', array( 'MSSMS_Settings_Send', 'output' ) );
			}

			add_submenu_page( 'mssms_settings', __( '알림톡 기본 설정', 'mshop-sms-s2' ), __( '알림톡 기본 설정', 'mshop-sms-s2' ), 'manage_woocommerce', 'mssms_settings_plusfriend', array( 'MSSMS_Settings_Alimtalk', 'output' ) );
			if ( MSSMS_Manager::is_enabled( 'alimtalk' ) ) {
				add_submenu_page( 'mssms_settings', __( '알림톡 템플릿 설정', 'mshop-sms-s2' ), __( '알림톡 템플릿 설정', 'mshop-sms-s2' ), 'manage_woocommerce', 'mssms_settings_alimtalk_template', array( 'MSSMS_Settings_Alimtalk_Template', 'output' ) );
				add_submenu_page( 'mssms_settings', __( '알림톡 자동 발송', 'mshop-sms-s2' ), __( '알림톡 자동 발송', 'mshop-sms-s2' ), 'manage_woocommerce', 'mssms_settings_alimtalk_send', array( 'MSSMS_Settings_Alimtalk_Send', 'output' ) );
			}

			if ( MSSMS_Manager::is_enabled( 'sms' ) || MSSMS_Manager::is_enabled( 'alimtalk' ) ) {
				add_submenu_page( 'mssms_settings', __( '알림 발송 로그', 'mshop-sms-s2' ), __( '알림 발송 로그', 'mshop-sms-s2' ), 'manage_woocommerce', 'mssms_logs', array( 'MSSMS_Settings_Logs', 'output' ) );
			}
		}
	}

	new MSSMS_Admin();

endif;
