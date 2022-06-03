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
						$table_data[ $i ]['time_start'] = self::comvert_time( 0 );
						$table_data[ $i ]['time_end']   = self::comvert_time( 0 );
						$table_data[ $i ]['time_rest']  = self::comvert_time( 0 );
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
				// 下村.
				$start_minute = sprintf( '%02d', mt_rand( 25, 35 ) );
				$end_minute   = sprintf( '%02d', mt_rand( 0, 30 ) );
				$rest_minute  = sprintf( '%02d', mt_rand( 30, 40 ) );

				$table_data[ $i ]['time_start'] = '9 : ' . $start_minute;
				$table_data[ $i ]['time_end']   = '18 : ' . $end_minute;
				$table_data[ $i ]['time_rest']  = '0 : ' . $rest_minute;
			}

			$table_data[ $i ]['bikou']          = '';
			$table_data[ $i ]['time_start_num'] = self::comvert_time_num( $table_data[ $i ]['time_start'] );
		}
		return $table_data;
	}

}
