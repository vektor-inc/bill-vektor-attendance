<?php
/*
* 給与明細のカスタムフィールド（品目以外）
*/

class Attendance_Staff_Custom_Fields {
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_metabox' ), 10, 2 );
		add_action( 'save_post', array( __CLASS__, 'save_custom_fields' ), 10, 2 );
	}

	// add meta_box
	public static function add_metabox() {

		$id            = 'meta_box_staff_attendance';
		$title         = 'スタッフ勤怠項目';
		$callback      = array( __CLASS__, 'fields_form' );
		$screen        = 'staff';
		$context       = 'advanced';
		$priority      = 'default';
		$callback_args = '';

		add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );

	}

	public static function fields_form() {
		global $post;

		$custom_fields_array = self::custom_fields_array();
		$befor_custom_fields = '';
		VK_Custom_Field_Builder::form_table( $custom_fields_array, $befor_custom_fields );
	}

	public static function save_custom_fields() {
		$custom_fields_array = self::custom_fields_array();
		// $custom_fields_array_no_cf_builder = arra();
		// $custom_fields_all_array = array_merge(  $custom_fields_array, $custom_fields_array_no_cf_builder );
		VK_Custom_Field_Builder::save_cf_value( $custom_fields_array );
	}

	public static function custom_fields_array() {

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

}
Attendance_Staff_Custom_Fields::init();
