<?php global $post; ?>
<div class="bill-wrap">
	<div class="container">

		<h1 class="bill-title">勤怠表</h1>

		<p style="font-size:14px;">
		<?php echo get_the_date( 'Y年n月度' ); ?><br />
		<?php echo esc_html( get_the_title( $post->attendance_staff ) ); ?>
		</p>

		<table class="table table-bordered table-attendance">
		<tr>
			<th>日付</th>
			<th>曜日</th>
			<th>始業時間</th>
			<th>終業時間</th>
			<th>休憩時間</th>
			<th>実働時間</th>
			<th>備考</th>
		</tr>
		<?php
		$day_end    = Bill_Attendance::get_end_day();
		$table_data = Bill_Attendance::create_initial_table();
		$table      = '';
		for ( $i = 1; $i <= $day_end; $i++ ) {
			if ( isset( $table_data[ $i ]['holiday'] ) && 'syukujitsu' === $table_data[ $i ]['holiday'] ) {
				$table .= '<tr class="bg-danger">';
			} else {
				if ( '日' === $table_data[ $i ]['youbi'] ) {
					$table .= '<tr class="bg-danger">';
				} elseif ( '土' === $table_data[ $i ]['youbi'] ) {
					$table .= '<tr class="bg-info">';
				} else {
					if ( isset( $table_data[ $i ]['holiday'] ) ) {
						if ( 'koukyuu' === $table_data[ $i ]['holiday'] ) {
							$table .= '<tr class="bg-warning">';
						} elseif ( 'yuukyuu' === $table_data[ $i ]['holiday'] || 'hankyuu' === $table_data[ $i ]['holiday']  ) {
							$table .= '<tr class="bg-success">';
						}
					} else {
						$table .= '<tr>';
					}
				}
			}

			$table .= '<td class="text-right">' . $i . '</td>';
			$table .= '<td class="text-right">' . $table_data[ $i ]['youbi'] . '</td>';
			$table .= '<td class="text-right">' . Bill_Attendance::comvert_time( $table_data[ $i ]['time_start'] ) . '</td>';
			$table .= '<td class="text-right">' . Bill_Attendance::comvert_time( $table_data[ $i ]['time_end'] ) . '</td>';
			$table .= '<td class="text-right">' . Bill_Attendance::comvert_time( $table_data[ $i ]['time_rest'] ) . '</td>';

			$time_total = Bill_Attendance::comvert_time_num( $table_data[ $i ]['time_end'] ) - Bill_Attendance::comvert_time_num( $table_data[ $i ]['time_start'] ) - Bill_Attendance::comvert_time_num( $table_data[ $i ]['time_rest'] );
			$table     .= '<td class="text-right">' . Bill_Attendance::comvert_time( $time_total ) . '</td>';

			$bikou = '';
			if ( ! empty( $table_data[ $i ]['holiday'] ) ) {
				$holiday = array(
					'syukujitsu' => '祝日',
					'koukyuu'    => '公休',
					'yuukyuu'    => '有給',
					'hankyuu'    => '有給（半休）',
				);
				$bikou   = $holiday[ $table_data[ $i ]['holiday'] ];
			}
			if ( ! empty( $table_data[ $i ]['bikou'] ) ) {
				$bikou .= $table_data[ $i ]['bikou'];
			}
			$table .= '<td>' . $bikou . '</td>';
			$table .= '</tr>';
		}
		echo $table;
		?>
		</table>

		<?php if ( $post->bill_remarks ) : ?>
		<dl class="bill-remarks">
		<dt>備考</dt>
		<dd>
			<?php echo apply_filters( 'the_content', $post->bill_remarks ); ?>
		</dd>
		</dl>
		<?php endif; ?>
	</div><!-- [ /.container ] -->
</div><!-- [ /.bill-wrap ] -->
