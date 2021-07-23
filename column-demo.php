<?php
/*
Plugin Name: Column-Demo
Plugin URI:
Description:
Version: 1.0
Author: Arif Islam
Author URI: arifislam.techviewing.com
License: GPLv2 or later
Text Domain: column-demo
Domain Path: /languages/
*/

function clmd_load_textdomain() {
	load_plugin_textdomain( 'column-demo', false, dirname( __FILE__ ) . '/languages' );
}

add_action( 'plugin_loaded', 'clmd_load_textdomain' );

function clmd_manage_post_columns( $columns ) {

	$columns['post_id']   = __( 'Post ID', 'column-demo' );
	$columns['thumbnail'] = __( 'Thumbnail', 'column-demo' );
	$columns['wordn']     = __( 'Word Count', 'column-demo' );

	return $columns;
}

add_filter( 'manage_posts_columns', 'clmd_manage_post_columns' );
//add_filter( 'manage_pages_columns', 'clmd_manage_post_columns' );

function clmd_manage_post_column( $column, $post_id ) {
	if ( 'post_id' == $column ) {
		echo $post_id;
	} elseif ( 'thumbnail' == $column ) {
		$thumbnail = get_the_post_thumbnail( $post_id, array( 50, 50 ) );
		echo $thumbnail;
	} elseif ( 'wordn' == $column ) {
		/*$_post   = get_post( $post_id );
		$content = $_post->post_content;
		$wordn   = str_word_count( strip_tags( $content ) );*/
		$wordn = get_post_meta( $post_id, 'wordn', true );
		echo $wordn;
	}
}

add_action( 'manage_posts_custom_column', 'clmd_manage_post_column', 10, 2 );
//add_action( 'manage_pages_custom_column', 'clmd_manage_post_column', 10, 2 );

function clmd_post_sortable_columns( $columns ) {
	$columns['wordn'] = 'wordn';

	return $columns;
}

add_filter( 'manage_edit-post_sortable_columns', 'clmd_post_sortable_columns' );


/*function clmd_set_post_meta() {
	$_posts = get_posts( array(
		'posts_per_page' => -1,
		'post_type'     => 'post'
	) );

	foreach ( $_posts as $p ) {
		$content = $p->post_content;
		$wordn   = str_word_count( strip_tags( $content ) );
		update_post_meta( $p->ID, 'wordn', $wordn );
	}
}

add_action( 'init', 'clmd_set_post_meta' );*/

function clmd_post_sortable_column_data( $wpquery ) {
	if ( ! is_admin() ) {
		return;
	}

	$orderby = $wpquery->get( 'orderby' );

	if ( 'wordn' == $orderby ) {
		$wpquery->set( 'meta_key', 'wordn' );
		$wpquery->set( 'orderby', 'meta_value_num' );
	}
}

add_action( 'pre_get_posts', 'clmd_post_sortable_column_data' );

function clmd_update_wordn_on_save( $post_id ) {
	$p       = get_post( $post_id );
	$content = $p->post_content;
	$wordn   = str_word_count( strip_tags( $content ) );
	update_post_meta( $p->ID, 'wordn', $wordn );
}

add_action( 'save_post', 'clmd_update_wordn_on_save' );

function clmb_filter() {
	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] != 'post' ) {
		return;
	}
	$filter_value = isset( $_GET['filter_demo'] ) ? $_GET['filter_demo'] : '';
	$values       = array(
		"0" => __( "Selecte Post", "column-demo" ),
		"1" => __( "Some Posts", "column-demo" ),
		"2" => __( "Some Posts++", "column-demo" )
	)
	?>
    <select name="filter_demo" id="">
		<?php
		foreach ( $values as $key => $value ) {
			printf( " <option value='%s' %s>%s</option>", $key,
				$key == $filter_value ? "selected='selected'" : '',
				$value
			);
		}
		?>

    </select>
	<?php
}

add_action( 'restrict_manage_posts', 'clmb_filter' );

function clmb_filter_data( $wpquery ) {
	if ( ! is_admin() ) {
		return;
	}

	$filter_value = isset( $_GET['filter_demo'] ) ? $_GET['filter_demo'] : '';
	if ( '1' == $filter_value ) {
		$wpquery->set( 'post__in', array( 17, 34, 36 ) );
	}
}

add_action( 'pre_get_posts', 'clmb_filter_data' );

function clmb_word_count_filter() {
	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] != 'post' ) {
		return;
	}
	$filter_value = isset( $_GET['wordc_filter'] ) ? $_GET['wordc_filter'] : '';
	$values       = array(
		"0" => __( "Word Count", "column-demo" ),
		"1" => __( "?<200", "column-demo" ),
		"2" => __( "200-400", "column-demo" ),
		"3" => __( "?>400", "column-demo" )
	)
	?>
    <select name="wordc_filter" id="">
		<?php
		foreach ( $values as $key => $value ) {
			printf( " <option value='%s' %s>%s</option>", $key,
				$key == $filter_value ? "selected='selected'" : '',
				$value
			);
		}
		?>

    </select>
	<?php
}

add_action( 'restrict_manage_posts', 'clmb_word_count_filter' );


function clmb_word_count_filter_data( $wpquery ) {
	if ( ! is_admin() ) {
		return;
	}

	$filter_value = isset( $_GET['wordc_filter'] ) ? $_GET['wordc_filter'] : '';
	if ( '1' == $filter_value ) {
		$wpquery->set( 'meta_query', array(
			array(
				'key'     => 'wordn',
				'value'   => 200,
				'compare' => '<=',
				'type'    => 'NUMERIC'
			)
		) );
	} elseif ( '2' == $filter_value ) {
		$wpquery->set( 'meta_query', array(
			array(
				'key'     => 'wordn',
				'value'   => array( 200, 400 ),
				'compare' => 'BETWEEN',
				'type'    => 'NUMERIC'
			)
		) );
	} elseif ( '3' == $filter_value ) {
		$wpquery->set( 'meta_query', array(
			array(
				'key'     => 'wordn',
				'value'   => 200,
				'compare' => '>=',
				'type'    => 'NUMERIC'
			)
		) );
	}
}

add_action( 'pre_get_posts', 'clmb_word_count_filter_data' );

