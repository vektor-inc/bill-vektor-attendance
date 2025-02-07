<?php
/*
* 給与明細のカスタムフィールド（品目以外）
*/

class Attendance_Normal_Custom_Fields {
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_metabox' ), 10, 2 );
		add_action( 'save_post', array( __CLASS__, 'save_custom_fields' ), 10, 2 );
	}

	// add meta_box.
	public static function add_metabox() {

		$id            = 'meta_box_bill_normal';
		$title         = '給与明細基本項目';
		$callback      = array( __CLASS__, 'fields_form' );
		$screen        = 'attendance';
		$context       = 'advanced';
		$priority      = 'high';
		$callback_args = '';

		add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );
	}

	public static function fields_form() {
		global $post;
		$befor_custom_fields = '';
		$custom_fields_array = self::custom_fields_array();
		VK_Custom_Field_Builder::form_table( $custom_fields_array, $befor_custom_fields );

		echo '<h4>今月の基準時間</h4>';
		echo '<p>スタッフ毎の基準時間は<a href="' . admin_url( '/edit.php?post_type=staff' ) . '" target="_blank">こちら</a>から設定する事ができますので、こちらの欄は基本的に未入力でかまいません。<br>こちらに入力がある場合はこちらの時間が優先されます。</p>';

		$custom_fields_times = self::custom_fields_times();
		VK_Custom_Field_Builder::form_table( $custom_fields_times, $befor_custom_fields );
	}

	public static function save_custom_fields() {
		VK_Custom_Field_Builder::save_cf_value( self::custom_fields_array() );
		VK_Custom_Field_Builder::save_cf_value( self::custom_fields_times() );
	}

	public static function custom_fields_times() {
		$custom_fields_array = array(
			'time_start_base'   => array(
				'label'       => '基準となる開始時間（10進数）',
				'type'        => 'text',
				'description' => '',
				'required'    => false,
			),
			'time_rest_base'    => array(
				'label'       => '基準となる休憩時間（10進数）',
				'type'        => 'text',
				'description' => '',
				'required'    => false,
			),
			'range_kinmu_minus' => array(
				'label'       => '勤務開始時間のマイナス値（分）',
				'type'        => 'text',
				'description' => '',
				'required'    => false,
			),
			'range_kinmu_plus'  => array(
				'label'       => '勤務開始時間のプラス値（分）',
				'type'        => 'text',
				'description' => '',
				'required'    => false,
			),
			'range_rest_minus'  => array(
				'label'       => '休憩時間のマイナス値（分）',
				'type'        => 'text',
				'description' => '',
				'required'    => false,
			),
			'range_rest_plus'   => array(
				'label'       => '休憩時間のプラス値（分）',
				'type'        => 'text',
				'description' => '',
				'required'    => false,
			),
		);
		return $custom_fields_array;
	}

	public static function custom_fields_array() {

		$args        = array(
			'post_type'      => 'staff',
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		);
		$staff_posts = get_posts( $args );
		if ( $staff_posts ) {
			$staff = array( '' => '選択してください' );
			foreach ( $staff_posts as $key => $post ) {
				$staff[ $post->ID ] = $post->post_title;
			}
		} else {
			$staff = array( '0' => 'スタッフが登録されていません' );
		}

		$custom_fields_array = array(
			'attendance_staff'    => array(
				'label'       => 'スタッフ',
				'type'        => 'select',
				'description' => 'スタッフは<a href="' . admin_url( '/post-new.php?post_type=staff' ) . '" target="_blank">こちら</a>から登録してください。',
				'required'    => true,
				'options'     => $staff,
			),
			'attendance_generate' => array(
				'label'       => '勤怠自動入力',
				'type'        => 'checkbox',
				'description' => 'チェックが入っていると次回再読み込み時に自動で値が入ります',
				'required'    => true,
				'options'     => array(
					true => '時間を新規自動入力または再生成する',
				),
			),
		);
		return $custom_fields_array;
	}
}
Attendance_Normal_Custom_Fields::init();
