<?php
class Bill_Attendance {

	/**
	 * 時間を 00 : 00 表記に変換する
	 *
	 * @param float $time_num .
	 * @return string $display_time
	 */
	public static function comvert_time( $time_num ) {

		preg_match( '/( : )/', $time_num, $matches );
		if ( ! $matches && 0 == $time_num ) {
			return '0 : 00';
		} elseif ( $matches ) {
			return $time_num;
		}

		$time_hour = floor( intval( $time_num ) );
		// 分に変換.
		$time_minites = ( $time_num - $time_hour ) * 60;
		if ( 60 === $time_minites ) {
			$time_hour++;
			$time_minites = '00';
		}
		$display_time = $time_hour . ' : ' . sprintf( '%02d', round( $time_minites ) );
		return $display_time;
	}

	/**
	 * 時間を少数の数値に変換する
	 *
	 * @param string $time_display 00 : 00 などの文字列 .
	 * @return float 数値
	 */
	public static function comvert_time_num( $time_display ) {
		preg_match( '/( : )/', $time_display, $matches );
		if ( $matches ) {
			$time_array = explode( ' : ', $time_display );
			if ( isset( $time_array[1] ) ) {
				$time_num = $time_array[0] + $time_array[1] / 60;
			}
		} else {
			$time_num = $time_display;
		}
		return (float) $time_num;
	}

	/**
	 * 該当の月の最終日を取得
	 *
	 * @return float | string : 日付
	 */
	public static function get_end_day() {
		global $post;
		return date( 'd', strtotime( 'last day of ' . get_the_date( 'Y-m' ) ) );
	}

	/**
	 * 初期の入力テーブルデータを作成
	 */
	public static function create_initial_table() {

		global $post;

		$defaults = array(
			'time_start_base'   => 9.0, // 基準となる開始時間（10進数）.
			'time_rest_base'    => 0.75, // 基準となる休憩時間（10進数）.
			'range_kinmu_minus' => -20, // 勤務開始時間のマイナス値（分）.
			'range_kinmu_plus'  => 30, // 勤務開始時間のプラス値（分）.
			'range_rest_minus'  => 0, // 休憩時間のマイナス値（分）.
			'range_rest_plus'   => 15, // 休憩時間のプラス値（分）.
		);

		foreach ( $defaults as $key => $value ) {
			$meta_value = get_post_meta( $post->ID, $key, true );
			if ( $meta_value || '0' === $meta_value ) {
				$args[$key] = floatval($meta_value);
			} else {
				$args[$key] = $value;
			}
		}

		$args = wp_parse_args( $args, $defaults );

		$day_end = self::get_end_day();
		$year    = get_the_date( 'Y' );
		$month   = get_the_date( 'm' );
		$week    = array(
			'日', // 0
			'月', // 1
			'火', // 2
			'水', // 3
			'木', // 4
			'金', // 5
			'土', // 6
		);

		$saved_data = get_post_meta( $post->ID, 'attendance_table', true );
		$generate   = get_post_meta( $post->ID, 'attendance_generate', true );

		$table_data = array();

		for ( $i = 1; $i <= $day_end; $i++ ) {

			// 曜日 .
			$timestamp                 = mktime( 0, 0, 0, $month, $i, $year );
			$youbi_num                 = date( 'w', $timestamp );
			$table_data[ $i ]['youbi'] = $week[ $youbi_num ];

			// 休日 .
			if ( ! empty( $saved_data[ $i ]['holiday'] ) ) {
				$table_data[ $i ]['holiday'] = $saved_data[ $i ]['holiday'];
			} else {
				$table_data[ $i ]['holiday'] = '';
			}

			// 時間 .
			if ( '日' === $table_data[ $i ]['youbi'] ||
				'土' === $table_data[ $i ]['youbi'] ||
				( ! empty( $table_data[ $i ]['holiday'] ) && 'hankyuu' !== $table_data[ $i ]['holiday'] )
				) {

				// 土日及び holiday が指定ありで半休以外の場合は勤務時間0.
				$table_data[ $i ]['time_start'] = self::comvert_time( 0 );
				$table_data[ $i ]['time_end']   = self::comvert_time( 0 );
				$table_data[ $i ]['time_rest']  = self::comvert_time( 0 );

			} else {

				// 自動生成の場合 .
				// ※ 半休の場合は時間が手動で入力されているので自動生成しない .
				if ( $generate && ( isset( $table_data[ $i ]['holiday'] ) && 'hankyuu' !== $table_data[ $i ]['holiday'] )
				) {

					// 開始時間のゆらぎ（分）.
					$range_kinmu = mt_rand( $args['range_kinmu_minus'], $args['range_kinmu_plus'] ) / 60;
					// 休憩時間のゆらぎ（分）.
					$range_rest = mt_rand( $args['range_rest_minus'], $args['range_rest_plus'] ) / 60;

					$time_start = $args['time_start_base'] + $range_kinmu;
					$time_rest  = $args['time_rest_base'] + $range_rest;
					$time_end   = $args['time_start_base'] + $args['time_rest_base'] + 8.0 + $range_kinmu;

					$table_data[ $i ]['time_start'] = self::comvert_time( $time_start );
					$table_data[ $i ]['time_end']   = self::comvert_time( $time_end );
					$table_data[ $i ]['time_rest']  = self::comvert_time( $time_rest );

				} else {
					$cols = array(
						'time_start',
						'time_end',
						'time_rest',
					);
					foreach ( $cols as $col ) {
						if ( isset( $saved_data[ $i ][ $col ] ) ) {
							$table_data[ $i ][ $col ] = $saved_data[ $i ][ $col ];
						} else {
							$table_data[ $i ][ $col ] = '';
						}
					}
				}
			}

			// 備考を生成 .
			if ( ! empty( $saved_data[ $i ]['bikou'] ) ) {
				$table_data[ $i ]['bikou'] = $saved_data[ $i ]['bikou'];
			} else {
				$table_data[ $i ]['bikou'] = '';
			}
		}
		return $table_data;
	}

}
