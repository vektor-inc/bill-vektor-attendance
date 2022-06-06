<?php
/*
* 給与明細のカスタムフィールド（品目以外）
*/

class Attendance_Table_Custom_Fields {
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_metabox' ), 10, 2 );
		add_action( 'save_post', array( __CLASS__, 'save_custom_fields' ), 10, 2 );
	}

	// add meta_box.
	public static function add_metabox() {
		$id            = 'meta_box_bill_table';
		$title         = '勤怠項目';
		$callback      = array( __CLASS__, 'fields_form' );
		$screen        = 'attendance';
		$context       = 'advanced';
		$priority      = 'high';
		$callback_args = '';

		add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );

	}

	public static function fields_form() {
		global $post;

		$data = get_post_meta( $post->ID, 'attendance_table', true );

		// print '<pre style="text-align:left">';print_r($data);print '</pre>';

		$day_end = Bill_Attendance::get_end_day();

		$table_data = Bill_Attendance::create_initial_table();

		$generate = get_post_meta( $post->ID, 'attendance_generate' );
		if ( ! $data && ! $generate ) {
			// テーブルデータが未保存 && 生成フラグが立ってない場合 勤怠入力前なので戻す.
			return;
		}

		?>
		<div class="vk-custom-field-builder ちゃうけどな">
		<table class="table table-striped table-bordered">
		<tr>
			<th>日付</th>
			<th>曜日</th>
			<th>休日</th>
			<th>始業時間</th>
			<th>就業時間</th>
			<th>休憩時間</th>
			<th>実働時間</th>
			<th>備考</th>
		</tr>
		<?php

		$table      = '';
		$attendance = new Bill_Attendance();

		for ( $i = 1; $i <= $day_end; $i++ ) {
			if ( '日' === $table_data[ $i ]['youbi'] ) {
				$table .= '<tr class="bg-danger">';
			} elseif ( '土' === $table_data[ $i ]['youbi'] ) {
				$table .= '<tr class="bg-info">';
			} else {
				if ( isset( $table_data[ $i ]['holiday'] ) && 'koukyuu' === $table_data[ $i ]['holiday'] ) {
					$table .= '<tr class="bg-warning">';
				} else {
					$table .= '<tr>';
				}
			}

			$table .= '<td>' . $i . '</td>';
			$table .= '<td>' . $table_data[ $i ]['youbi'] . '</td>';

			$table .= '<td class="nowrap" style="white-space:nowrap">';

			// 祝日.
			$checked = '';
			if ( isset( $table_data[ $i ]['holiday'] ) ) {
				$checked = checked( $table_data[ $i ]['holiday'], 'syukujitsu', false );
			}
			$table .= '<label><input type="checkbox" id="attendance_table[' . $i . '][holiday]" name="attendance_table[' . $i . '][holiday]" value="syukujitsu"' . $checked . '> 祝日</label></br>';

			// 公休.
			$checked = '';
			if ( isset( $table_data[ $i ]['holiday'] ) ) {
				$checked = checked( $table_data[ $i ]['holiday'], 'koukyuu', false );
			}
			$table .= '<label><input type="checkbox" id="attendance_table[' . $i . '][holiday]" name="attendance_table[' . $i . '][holiday]" value="koukyuu"' . $checked . '> 公休</label></br>';

			// 有給.
			$checked = '';
			if ( isset( $table_data[ $i ]['holiday'] ) ) {
				$checked = checked( $table_data[ $i ]['holiday'], 'yuukyuu', false );
			}
			$table .= '<label><input type="checkbox" id="attendance_table[' . $i . '][holiday]" name="attendance_table[' . $i . '][holiday]" value="yuukyuu"' . $checked . '> 有給</label></br>';

			// 半給.
			$checked = '';
			if ( isset( $table_data[ $i ]['holiday'] ) ) {
				$checked = checked( $table_data[ $i ]['holiday'], 'hankyuu', false );
			}
			$table .= '<label><input type="checkbox" id="attendance_table[' . $i . '][holiday]" name="attendance_table[' . $i . '][holiday]" value="hankyuu"' . $checked . '> 半休</label>';

			$table .= '</td>';
			$table .= '<td><input class="flexible-field-item" type="text" id="attendance_table[' . $i . '][time_start]" name="attendance_table[' . $i . '][time_start]" value="' . $attendance->comvert_time( $table_data[ $i ]['time_start'] ) . '"></td>';
			$table .= '<td><input class="flexible-field-item" type="text" id="attendance_table[' . $i . '][time_end]" name="attendance_table[' . $i . '][time_end]" value="' . $attendance->comvert_time( $table_data[ $i ]['time_end'] ) . '"></td>';
			$table .= '<td><input class="flexible-field-item" type="text" id="attendance_table[' . $i . '][time_rest]" name="attendance_table[' . $i . '][time_rest]" value="' . $attendance->comvert_time( $table_data[ $i ]['time_rest'] ) . '"></td>';

			// 稼働時間.
			$table     .= '<td>';
			$time_total = $attendance->comvert_time_num( $table_data[ $i ]['time_end'] ) - $attendance->comvert_time_num( $table_data[ $i ]['time_start'] ) - $attendance->comvert_time_num( $table_data[ $i ]['time_rest'] );
			$table     .= $attendance->comvert_time( $time_total );
			$table     .= '</td>';

			$table .= '<td><input class="flexible-field-item" type="text" id="attendance_table[' . $i . '][bikou]" name="attendance_table[' . $i . '][bikou]" value="' . $table_data[ $i ]['bikou'] . '"></td>';
			$table .= '</tr>';
		}
		echo $table;
		?>
</table>
</div>
		<?php

		// $custom_fields_array = self::custom_fields_attendance_array();
		// VK_Custom_Field_Builder_Flexible_Table::form_table_flexible( $custom_fields_array );
	}

	public static function save_custom_fields() {

		// $custom_fields_array = self::custom_fields_array();
		// VK_Custom_Field_Builder::save_cf_value( $custom_fields_array );

		global $post;

		// 設定したnonce を取得（CSRF対策）.
		// $nonce_name             = 'noncename__' . $custom_fields_array['field_name'];
		// $noncename__bill_fields = isset( $_POST[ $nonce_name ] ) ? $_POST[ $nonce_name ] : null;

		// nonce を確認し、値が書き換えられていれば、何もしない（CSRF対策）.
		// if ( ! wp_verify_nonce( $noncename__bill_fields, wp_create_nonce( __FILE__ ) ) ) {
		// return;
		// }

		// 自動保存ルーチンかどうかチェック。そうだった場合は何もしない（記事の自動保存処理として呼び出された場合の対策）.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$field       = 'attendance_table';
		$field_value = ( isset( $_POST[ $field ] ) ) ? $_POST[ $field ] : '';

		// print '<pre style="text-align:left">';print_r($field_value);print '</pre>';
		// die();

		$post_id = '';
		if ( isset( $post->ID ) ) {
			$post_id = $post->ID;
		} elseif ( isset( $_POST['post_ID'] ) ) {
			$post_id = $_POST['post_ID'];
		}

		if ( ! get_post_meta( $post_id, $field ) ) {
			add_post_meta( $post_id, $field, $field_value, true );
			// 今入ってる値と違ってたらアップデートする.
		} elseif ( $field_value !== get_post_meta( $post_id, $field, true ) ) {
			update_post_meta( $post_id, $field, $field_value );
			// 入力がなかったら消す.
		} elseif ( $field_value == '' ) {
			delete_post_meta( $post_id, $field, get_post_meta( $post_id, $field, true ) );
		}
		// $data = get_post_meta( $post->ID, $field, true );
		// print '<pre style="text-align:left">';print_r($data);print '</pre>';
		// die();
	}

	public static function custom_fields_attendance_array() {
		$custom_fields_array = array(
			'field_name'        => 'attendance',
			'row_default'       => 3,
			'row_empty_display' => false,
			'items'             => array(
				'name'     => array(
					'type'             => 'text',
					'label'            => '項目',
					'align'            => 'left',
					'sanitize'         => 'wp_filter_post_kses',
					'display_callback' => '',
				),
				'price'    => array(
					'type'             => 'text',
					'label'            => '金額',
					'align'            => 'right',
					'class'            => 'price',
					'sanitize'         => 'wp_filter_post_kses',
					'display_callback' => 'bvsl_format_print',
				),
				'saijitsu' => array(
					'type'             => 'check',
					'label'            => '金額',
					'align'            => 'right',
					'class'            => 'price',
					'sanitize'         => 'wp_filter_post_kses',
					'display_callback' => 'bvsl_format_print',
				),
			),
		);
		return $custom_fields_array;
	}

	public static function custom_fields_koujyo_kazei_array() {
		$custom_fields_array = array(
			'field_name'        => 'kazei_koujyo',
			'row_default'       => 3,
			'row_empty_display' => false,
			'items'             => array(
				'name'  => array(
					'type'             => 'text',
					'label'            => '項目',
					'align'            => 'left',
					'sanitize'         => 'wp_filter_post_kses',
					'display_callback' => '',
				),
				'price' => array(
					'type'             => 'text',
					'label'            => '金額',
					'align'            => 'right',
					'sanitize'         => 'wp_filter_post_kses',
					'display_callback' => 'bvsl_format_print',
				),
			),
		);
		return $custom_fields_array;
	}

	public static function custom_fields_koujyo_hikazei_array() {
		$custom_fields_array = array(
			'field_name'        => 'hikazei_koujyo',
			'row_default'       => 3,
			'row_empty_display' => false,
			'items'             => array(
				'name'  => array(
					'type'             => 'text',
					'label'            => '項目',
					'align'            => 'left',
					'sanitize'         => 'wp_filter_post_kses',
					'display_callback' => '',
				),
				'price' => array(
					'type'             => 'text',
					'label'            => '金額',
					'align'            => 'right',
					'sanitize'         => 'wp_filter_post_kses',
					'display_callback' => 'bvsl_format_print',
				),
			),
		);
		return $custom_fields_array;
	}

}
Attendance_Table_Custom_Fields::init();
