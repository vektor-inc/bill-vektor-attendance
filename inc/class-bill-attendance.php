<?php
class Bill_Attendance {

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

	public static function get_end_day() {
		global $post;
		return date( 'd', strtotime( 'last day of ' . get_the_date( 'Y-m' ) ) );
	}

	public static function create_initial_table() {

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

		global $post;
		$saved_data = get_post_meta( $post->ID, 'attendance_table', true );
		if ( $saved_data && is_array( $saved_data ) ) {
			$table_data = $saved_data;
			for ( $i = 1; $i <= $day_end; $i++ ) {
				$timestamp                 = mktime( 0, 0, 0, $month, $i, $year );
				$youbi_num                 = date( 'w', $timestamp );
				$table_data[ $i ]['youbi'] = $week[ $youbi_num ];
				if ( ! empty( $table_data[ $i ]['holiday'] ) ) {
					// 半休以外の場合は勤務時間0.
					if ( 'hankyuu' !== $table_data[ $i ]['holiday'] ) {
						$table_data[ $i ]['time_start'] = self::comvert_time( 0 );
						$table_data[ $i ]['time_end']   = self::comvert_time( 0 );
						$table_data[ $i ]['time_rest']  = self::comvert_time( 0 );
					}
				}
			}
			return $table_data;
		}

		// 新規でまだ保存されていない場合.
		$table_data = array();
		for ( $i = 1; $i <= $day_end; $i++ ) {
			$timestamp                 = mktime( 0, 0, 0, $month, $i, $year );
			$youbi_num                 = date( 'w', $timestamp );
			$table_data[ $i ]['youbi'] = $week[ $youbi_num ];

			if ( '日' === $table_data[ $i ]['youbi'] || '土' === $table_data[ $i ]['youbi'] ) {
				$table_data[ $i ]['time_start'] = self::comvert_time( 0 );
				$table_data[ $i ]['time_end']   = self::comvert_time( 0 );
				$table_data[ $i ]['time_rest']  = self::comvert_time( 0 );
			} else {

				// 基準となる開始時間（10進数）.
				$time_start_base = 9.0;
				// 基準となる休憩時間（10進数）.
				$time_rest_base = 0.75;
				// 開始時間のゆらぎ（分）.
				$range_kinmu    = mt_rand( -15, 15 ) / 60;
				// 休憩時間のゆらぎ（分）.
				$range_rest     = mt_rand( -10, 10 ) / 60;

				$time_start = $time_start_base + $range_kinmu;
				$time_rest  = $time_rest_base + $range_rest;
				$time_end   = $time_start_base + $time_rest_base + 8.05 + $range_kinmu;

				$table_data[ $i ]['time_start'] = self::comvert_time( $time_start );
				$table_data[ $i ]['time_end']   = self::comvert_time( $time_end );
				$table_data[ $i ]['time_rest']  = self::comvert_time( $time_rest );

			}

			$table_data[ $i ]['bikou']          = '';
			$table_data[ $i ]['time_start_num'] = self::comvert_time_num( $table_data[ $i ]['time_start'] );
		}
		return $table_data;
	}

}
