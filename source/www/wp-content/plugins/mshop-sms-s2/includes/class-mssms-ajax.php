<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class MSSMS_Ajax {

	static $slug;

	public static function init() {
		if ( defined( 'WP_DEBUG' ) && ! WP_DEBUG ) {
			@error_reporting( 0 );
		}
		self::$slug = MSSMS()->slug();
		self::add_ajax_events();
	}
	public static function add_ajax_events() {

		$ajax_events = array ();

		if ( is_admin() ) {
			$ajax_events = array_merge( $ajax_events, array (
				'update_settings'                   => false,
				'update_sms_settings'               => false,
				'update_sms_send_settings'          => false,
				'update_alimtalk_settings'          => false,
				'update_alimtalk_template_settings' => false,
				'update_alimtalk_send_settings'     => false,
				'alimtalk_authentication_request'   => false,
				'alimtalk_add_profile'              => false,
				'alimtalk_add_template'             => false,
				'alimtalk_modify_template'          => false,
				'alimtalk_request_template'         => false,
				'alimtalk_delete_template'          => false,
				'alimtalk_update_resend'            => false,
				'sms_register_send_no'              => false,
				'send_sms'                          => false,
				'get_users_by_role'                 => false,
				'user_search'                       => false,
				'get_send_results'                  => false,
				'send_messages'                     => false,
				'target_search'                     => false
			) );
		}

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			if ( is_string( $nopriv ) ) {
				add_action( 'wp_ajax_' . self::$slug . '-' . $ajax_event, $nopriv );
			} else {
				add_action( 'wp_ajax_' . self::$slug . '-' . $ajax_event, array ( __CLASS__, $ajax_event ) );

				if ( $nopriv ) {
					add_action( 'wp_ajax_nopriv_' . self::$slug . '-' . $ajax_event, array ( __CLASS__, $ajax_event ) );
				}
			}
		}
	}

	static function update_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		MSSMS_Settings::update_settings();
	}
	static function update_alimtalk_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		MSSMS_Settings_Alimtalk::update_settings();
	}
	static function update_alimtalk_template_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		MSSMS_Settings_Alimtalk_Template::update_settings();
	}
	static function update_sms_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		MSSMS_Settings_SMS::update_settings();
	}

	static function update_sms_send_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		MSSMS_Settings_SMS_Send::update_settings();
	}
	static function update_alimtalk_send_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		MSSMS_Settings_Alimtalk_Send::update_settings();
	}
	static function get_send_results() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		try {
			$mtype = mssms_get( $_REQUEST, 'mtype', 'all' );
			$state = mssms_get( $_REQUEST, 'state', 'all' );
			list( $date_from, $date_to ) = explode( ',', mssms_get( $_REQUEST, 'term' ) );

			$results = MSSMS_API_Kakao::get_send_result( '', 'all' == $mtype ? '' : $mtype, mssms_get( $_REQUEST, 'receiver' ), 'all' == $state ? '' : $state, mssms_get( $_REQUEST, 'page' ), $date_from, $date_to );

			wp_send_json_success( array (
					'total_count' => $results['total_count'],
					'results'     => $results['data']
				)
			);
		} catch ( Exception $e ) {
			wp_Send_json_error( $e->getMessage() );
		}
	}
	static function alimtalk_delete_template() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		try {
			MSSMS_API_Kakao::delete_template( $_REQUEST['plus_id'], $_REQUEST['code'] );

			wp_send_json_success();
		} catch ( Exception $e ) {
			$message = sprintf( '템플릿 삭제 오류 : %s', $e->getMessage() );
			wp_send_json_error( $message );

		}
	}
	static function alimtalk_request_template() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		try {
			MSSMS_API_Kakao::request_template( $_REQUEST['plus_id'], $_REQUEST['templtCode'] );

			wp_send_json_success();
		} catch ( Exception $e ) {
			$message = sprintf( '템플릿 삭제 오류 : %s', $e->getMessage() );
			wp_send_json_error( $message );

		}
	}
	static function alimtalk_modify_template() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		try {
			$buttons = array ();
			if ( ! empty( $_REQUEST['buttons'] ) ) {
				$idx = 1;
				foreach ( $_REQUEST['buttons'] as $button ) {
					$buttons[] = array (
						'ordering'      => $idx++,
						'type'          => mssms_get( $button, 'type' ),
						'name'          => mssms_get( $button, 'name' ),
						'linkMo'        => mssms_get( $button, 'linkMo' ),
						'linkPc'        => mssms_get( $button, 'linkPc' ),
						'schemeIos'     => mssms_get( $button, 'schemeIos' ),
						'schemeAndroid' => mssms_get( $button, 'schemeAndroid' )
					);
				}
			}

			MSSMS_API_Kakao::modify_template( $_REQUEST['plus_id'], $_REQUEST['code'], $_REQUEST['name'], $_REQUEST['content'], $buttons );

			wp_send_json_success();
		} catch ( Exception $e ) {
			$message = sprintf( '템플릿 수정 오류 : %s', $e->getMessage() );
			wp_send_json_error( $message );

		}
	}
	static function alimtalk_add_template() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		try {
			$buttons = array ();
			if ( ! empty( $_REQUEST['buttons'] ) ) {
				$idx = 1;
				foreach ( $_REQUEST['buttons'] as $button ) {
					$buttons[] = array (
						'ordering'      => $idx++,
						'type'          => mssms_get( $button, 'type' ),
						'name'          => mssms_get( $button, 'name' ),
						'linkMo'        => mssms_get( $button, 'linkMo' ),
						'linkPc'        => mssms_get( $button, 'linkPc' ),
						'schemeIos'     => mssms_get( $button, 'schemeIos' ),
						'schemeAndroid' => mssms_get( $button, 'schemeAndroid' )
					);
				}
			}

			$result = MSSMS_API_Kakao::add_template( $_REQUEST['plus_id'], $_REQUEST['name'], $_REQUEST['content'], $buttons );

			wp_send_json_success( $result );
		} catch ( Exception $e ) {
			$message = sprintf( '템플릿 등록 오류 : %s', $e->getMessage() );
			wp_send_json_error( $message );

		}
	}

	static function alimtalk_authentication_request() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		try {
			$params = json_decode( stripslashes( mssms_get( $_REQUEST, 'params' ) ), true );

			$plus_id            = $params['plus_id'];
			$admin_phone_number = $params['admin_phone_number'];
			$category           = $params['category'];

			if ( empty( $plus_id ) ) {
				throw new Exception( __( '플러스친구 아이디를 입력해주세요.' ) );
			}

			if ( empty( $admin_phone_number ) ) {
				throw new Exception( __( '관리자 전화번호를 입력해주세요.' ) );
			}

			if ( empty( $category ) ) {
				throw new Exception( __( '카테고리를 선택해주세요.' ) );
			}

			if ( 0 !== strpos( $plus_id, '@' ) ) {
				$plus_id = '@' . $plus_id;
			}

			MSSMS_API_Kakao::authentication_request( $plus_id, $admin_phone_number, $category );

			wp_send_json_success( array ( 'message' => __( "인증번호가 전송되었습니다.\n인증번호를 입력하신 후, 심사요청 버튼을 클릭해주세요.", 'mshop-sms-s2' ) ) );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	static function alimtalk_update_resend() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		try {
			$params = json_decode( stripslashes( mssms_get( $_REQUEST, 'params' ) ), true );

			$plus_id        = $params['plus_id'];
			$is_resend      = $params['is_resend'];
			$resend_send_no = $params['resend_send_no'];

			if ( 'yes' == $is_resend && empty( $resend_send_no ) ) {
				throw new Exception( __( '발신번호를 입력해주세요.' ) );
			}

			if ( 0 !== strpos( $plus_id, '@' ) ) {
				$plus_id = '@' . $plus_id;
			}

			MSSMS_API_Kakao::update_resend( $plus_id, $is_resend, $resend_send_no );

			wp_send_json_success( array ( 'message' => __( "대체발송 설정이 업데이트 되었습니다.", 'mshop-sms-s2' ) ) );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	static function alimtalk_add_profile() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		try {
			$params = json_decode( stripslashes( mssms_get( $_REQUEST, 'params' ) ), true );

			$plus_id            = $params['plus_id'];
			$admin_phone_number = $params['admin_phone_number'];
			$auth_number        = $params['auth_number'];
			$category           = $params['category'];

			if ( empty( $plus_id ) ) {
				throw new Exception( __( '플러스친구 아이디를 입력해주세요.' ) );
			}

			if ( empty( $admin_phone_number ) ) {
				throw new Exception( __( '관리자 전화번호를 입력해주세요.' ) );
			}

			if ( empty( $category ) ) {
				throw new Exception( __( '카테고리를 선택해주세요.' ) );
			}

			if ( empty( $auth_number ) ) {
				throw new Exception( __( '인증번호를 입력해주세요.' ) );
			}

			if ( 0 !== strpos( $plus_id, '@' ) ) {
				$plus_id = '@' . $plus_id;
			}

			MSSMS_API_Kakao::add_profile( $plus_id, $admin_phone_number, $category, $auth_number );

			wp_send_json_success( array ( 'message' => __( "플러스친구가 등록되었습니다.", 'mshop-sms-s2' ) ) );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	static function user_search() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		global $wpdb;

		$results = array ();

		$keyword = isset( $_REQUEST['args'] ) ? esc_attr( $_REQUEST['args'] ) : '';

		$sql = "SELECT user.ID
				FROM {$wpdb->users} user
				WHERE
				    user.user_login like '%{$keyword}%'
				    OR user.user_nicename like '%{$keyword}%'
				    OR user.display_name like '%{$keyword}%'
				    OR user.user_email like '%{$keyword}%'
				ORDER BY ID
				LIMIT 20";


		$user_ids = $wpdb->get_col( $sql );

		foreach ( $user_ids as $user_id ) {
			$user      = get_user_by( 'id', $user_id );
			$results[] = array (
				"value" => $user_id,
				"name"  => get_user_meta( $user->ID, 'billing_phone', true ) . ',' . $user->data->display_name
			);
		}

		$respose = array (
			'success' => true,
			'results' => $results
		);

		echo json_encode( $respose );

		die();
	}

	protected static function get_upload_dir_path() {
		$upload_dir = wp_get_upload_dir();
		$upload_dir_path = $upload_dir['basedir'] . '/mshop-sms/';

		if( ! file_exists( $upload_dir_path ) ) {
			mkdir( $upload_dir_path );
		}

		return $upload_dir_path;
	}

	protected static function get_upload_file_path( $attached_file ) {
		return self::get_upload_dir_path() . $attached_file['name'];
	}
	protected static function move_attached_file( $attached_file ) {
		mkdir( self::get_upload_dir_path() );

		if ( ! move_uploaded_file( $attached_file['tmp_name'], self::get_upload_file_path( $attached_file ) ) ) {
			throw new Exception( '발신번호 등록중 오류가 발생했습니다.', '10002' );
		}
	}

	static function sms_register_send_no() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		$attached_file = '';

		try {
			$params = json_decode( stripslashes( $_POST['params'] ), true );

			if ( empty( $params['send_no'] ) ) {
				throw new Exception( __( '발신번호를 입력해주세요.', 'mshop-sms-s2' ) );
			}
			$attached_file = current( $_FILES );
			if ( $attached_file['size'] > 300 * 1024 ) {
				throw new Exception( __( '첨부 파일의 최대 크기는 300K 입니다.', 'mshop-sms-s2' ) );
			}

			self::move_attached_file( $attached_file );

			MSSMS_API_SMS::register_send_no( $params['send_no'], self::get_upload_file_path( $attached_file ) );

			unlink( self::get_upload_file_path( $attached_file ) );
			wp_send_json_success( array ( 'message' => __( "발신번호 등록 요청이 접수되었습니다.", 'mshop-sms-s2' ) ) );
		} catch ( Exception $e ) {
			unlink( self::get_upload_file_path( $attached_file ) );

			wp_send_json_error( $e->getMessage() );
		}
	}

	static function send_messages() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		try {
			$request_date = '';

			if ( empty( $_REQUEST['sender_number'] ) ) {
				throw new Exception( __( '발신번호를 선택해주세요.', 'mshop-sms-s2' ) );
			}

			if ( empty( $_REQUEST['customer_list'] ) ) {
				throw new Exception( __( '수신자 정보를 입력해주세요.', 'mshop-sms-s2' ) );
			}

			if ( empty( $_REQUEST['messages'] ) ) {
				throw new Exception( __( '고객에게 보낼 메시지를 입력해주세요.', 'mshop-sms-s2' ) );
			}

			if ( empty( $_REQUEST['title'] ) ) {
				throw new Exception( __( '문자 제목을 입력 해 주세요.', 'mshop-sms-s2' ) );
			}

			$recipients = array_map( function ( $receiver ) {
				return array (
					'receiver'        => mssms_get( $receiver, 'phone_number' ),
					'template_params' => array (
						'고객명' => mssms_get( $receiver, 'customer_name' )
					)
				);
			}, $_REQUEST['customer_list'] );

			if ( 'reserved' == $_REQUEST['send_type'] ) {
				$request_date = $_REQUEST['request_date'];
			}

			MSSMS_SMS::send_sms( 'LMS', $_REQUEST['title'], stripslashes( $_REQUEST['messages'] ), $recipients, $_REQUEST['sender_number'], $request_date );

			if ( 'reserved' == $_REQUEST['send_type'] ) {
				wp_send_json_success( array ( 'message' => __( "문자가 예약되었습니다. 발송 결과는 발송 로그에서 확인 해 주세요.", 'mshop-sms-s2' ) ) );
			} else {
				wp_send_json_success( array ( 'message' => __( "고객에게 문자가 발송되었습니다. 발송결과는 발송로그에서 확인해주세요.", 'mshop-sms-s2' ) ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
	static function target_search_category( $depth = 0, $parent = 0 ) {
		$args = array ();

		if ( ! empty( $_REQUEST['args'] ) ) {
			$args['name__like'] = $_REQUEST['args'];
		}

		$results = self::make_taxonomy_tree( 'product_cat', $args );

		$respose = array (
			'success' => true,
			'results' => $results
		);

		echo json_encode( $respose );
		die();
	}
	static function make_taxonomy_tree( $taxonomy, $args, $depth = 0, $parent = 0, $paths = array () ) {
		$results = array ();

		$args['parent'] = $parent;
		$terms          = get_terms( $taxonomy, $args );

		foreach ( $terms as $term ) {
			$current_paths = array_merge( $paths, array ( $term->name ) );
			$results[]     = array (
				"name"  => '<span class="tree-indicator-desc">' . implode( '-', $current_paths ) . '</span><span class="tree-indicator" style="margin-left: ' . ( $depth * 8 ) . 'px;">' . $term->name . '</span>',
				"value" => $term->term_id
			);

			$results = array_merge( $results, self::make_taxonomy_tree( $taxonomy, $args, $depth + 1, $term->term_id, $current_paths ) );
		}

		return $results;
	}
	static function target_search_product_posts_title_like( $where, &$wp_query ) {
		global $wpdb;
		if ( $posts_title = $wp_query->get( 'posts_title' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE "%' . $posts_title . '%"';
		}

		return $where;
	}
	static function target_search_product() {
		$keyword = ! empty( $_REQUEST['args'] ) ? $_REQUEST['args'] : '';

		add_filter( 'posts_where', array ( __CLASS__, 'target_search_product_posts_title_like' ), 10, 2 );
		$args = array (
			'post_type'      => 'product',
			'posts_title'    => $keyword,
			'post_status'    => 'publish',
			'posts_per_page' => -1
		);

		$query = new WP_Query( $args );

		remove_filter( 'posts_where', array ( __CLASS__, 'target_search_product_posts_title_like' ) );

		$results = array ();

		foreach ( $query->posts as $post ) {
			$results[] = array (
				"name"  => $post->post_title,
				"value" => $post->ID
			);
		}
		$respose = array (
			'success' => true,
			'results' => $results
		);

		echo json_encode( $respose );

		die();
	}


	public static function target_search() {
		if ( ! empty( $_REQUEST['type'] ) ) {
			$type = $_REQUEST['type'];

			switch ( $type ) {
				case 'product' :
				case 'product-category' :
					self::target_search_product();
					break;
				case 'category' :
					self::target_search_category();
					break;
				default:
					die();
					break;
			}
		}
	}

	public static function get_users_by_role() {
		if ( ! current_user_can( 'manage_options' ) || empty( $_POST['search_role'] ) ) {
			die();
		}

		$role = $_POST['search_role'];

		$user_ids = get_users( array (
			'role'       => $role,
			'fields'     => 'ID',
			'number'     => -1,
			'meta_query' => array (
				'relation' => 'OR', // Optional, defaults to "AND"
				array (
					'key'     => 'mssms_agreement',
					'value'   => 'on',
					'compare' => '='
				),
				array (
					'key'     => 'mssms_agreement',
					'compare' => 'NOT EXISTS'
				)
			)
		) );

		$results = array_map( function ( $user_id ) {
			$billing_phone = get_user_meta( $user_id, 'billing_phone', true );

			if ( ! empty( $billing_phone ) /* && 'yes' == get_user_meta( $user_id, '_mssms_agree_to_receive', 'yes' ) */ ) {
				$first_name = get_user_meta( $user_id, 'billing_first_name', true );
				$last_name  = get_user_meta( $user_id, 'billing_last_name', true );

				return $billing_phone . ',' . $first_name . $last_name;
			}
		}, $user_ids );

		$results = array_filter( $results );

		if ( ! empty( $results ) ) {
			wp_send_json_success( array_filter( $results ) );
		} else {
			wp_send_json_success( __( '해당 등급에 해당하는 사용자가 없습니다.', 'mshop-sms-s2' ) );
		}
	}
}

MSSMS_Ajax::init();
