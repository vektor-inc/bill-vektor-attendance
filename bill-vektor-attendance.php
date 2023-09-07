<?php
/**
 * Plugin Name:     BillVektor Attendance
 * Plugin URI:
 * Description:
 * Author:          Vektor,Inc.
 * Author URI:      https://billvektor.com/
 * Text Domain:     bill-vektor-attendance
 * Domain Path:     /languages
 * Version:         0.0.0
 *
 * @package         Bill_Vektor_attendance
 */


/**
 * テーマがBillVektorじゃない時は誤動作防止のために読み込ませない
 */
add_action(
	'after_setup_theme',
	function() {
		if ( ! function_exists( 'bill_get_post_type' ) ) {
			// 読み込まずに終了.
			return;
		}
	}
);

// Your code starts here.
// require_once 'inc/duplicate-doc.php';
// require_once 'inc/custom-field-attendance/custom-field-attendance.php';
// require_once 'inc/custom-field-receipt/custom-field-receipt-normal.php';

require_once 'inc/class-bill-attendance.php';
require_once 'inc/custom-field-setting/custom-field-attendance-normal.php';
require_once 'inc/custom-field-setting/custom-field-attendance-table.php';
require_once 'inc/custom-field-setting/custom-field-attendance-staff.php';

function bva_doc_change( $doc_change ) {
	if ( get_post_type() === 'attendance' ) {
		$doc_change = true;
	}
	 return $doc_change;
}
add_filter( 'bill-vektor-doc-change', 'bva_doc_change' );

function bva_doc_frame_attendance() {
	if ( get_post_type() === 'attendance' ) {
		require_once 'template-parts/doc/frame-attendance.php';
	}
}
add_action( 'bill-vektor-doc-frame', 'bva_doc_frame_attendance' );

/**
 * Add Post Type attendance
 *
 * @return void
 */
function bill_add_post_type_attendance() {
	register_post_type(
		'attendance',
		array(
			'labels'             => array(
				'name'         => '出勤簿',
				'edit_item'    => '出勤簿の編集',
				'add_new_item' => '出勤簿の作成',
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'has_archive'        => true,
			'supports'           => array( 'title' ),
			'menu_icon'          => 'dashicons-media-spreadsheet',
			'menu_position'      => 7,
		)
	);
	register_taxonomy(
		'attendance-cat',
		'attendance',
		array(
			'hierarchical'          => true,
			'update_count_callback' => '_update_post_term_count',
			'label'                 => '出勤簿カテゴリー',
			'singular_label'        => '出勤簿カテゴリー',
			'public'                => true,
			'show_ui'               => true,
		)
	);
}
add_action( 'init', 'bill_add_post_type_attendance', 0 );

function bva_remove_meta_boxes() {
	remove_meta_box( 'commentstatusdiv', 'attendance', 'normal' );
}
add_action( 'admin_menu', 'bva_remove_meta_boxes' );

function bva_bill_vektor_post_types_custom( $post_type_array ) {
		$post_type_array['attendance'] = '出勤簿';
		return $post_type_array;
}
add_filter( 'bill_vektor_post_types', 'bva_bill_vektor_post_types_custom' );

/**
 * アーカイブページのテンプレートを上書き
 *
 * @return void
 */
function bva_doc_change_attendance_archive() {
	if ( get_post_type() === 'attendance' && is_tax() ) {
		require_once 'template-parts/doc/frame-attendance-archive.php';
		die();
	}
}
add_action( 'template_redirect', 'bva_doc_change_attendance_archive' );

/**
 * 古い順に並び替え
 */
add_action(
	'pre_get_posts',
	function( $wp_query ) {
		/* 管理画面,メインクエリに干渉しないために必須 */
		if ( is_admin() || ! $wp_query->is_main_query() ) {
			return;
		}

		if ( $wp_query->is_tax( 'attendance-cat' ) ) {
			$wp_query->set( 'order', 'ASC' );
			$wp_query->set( 'posts_per_page', -1 );
			return;
		}
	}
);
