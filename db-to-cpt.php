<?php
/**
 * Plugin Name: DB to Custom Post Type
 * Plugin URI: https://wpxforce.com
 * Author: Muneeb Abdul Rauf
 * Author URI: https://wpxforce.com
 * Description: Move stuff from the custom table to CPTs
 * Version: 0.1.0
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: prefix-plugin-name
*/
global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_name = $wpdb->prefix . 'websites';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_email varchar(100) NOT NULL,
		title text NOT NULL,
		keyword text NOT NULL,
		link varchar(200) NOT NULL,
		banner longblob NOT NULL,
		description text NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

function create_website_cpt() {

    $labels = array(
        'name' => _x( 'Website', 'Post Type General Name', 'Website' ),
        'singular_name' => _x( 'Website', 'Post Type Singular Name', 'Website' ),
        'menu_name' => _x( 'Website', 'Admin Menu text', 'Website' ),
        'name_admin_bar' => _x( 'Website', 'Add New on Toolbar', 'Website' ),
        'archives' => __( 'Archive Website', 'Website' ),
        'attributes' => __( 'Attribute Website', 'Website' ),
        'parent_item_colon' => __( 'Parent item Website:', 'Website' ),
        'all_items' => __( 'All Websites', 'Website' ),
        'add_new_item' => __( 'Add new website', 'Website' ),
        'add_new' => __( 'New website', 'Website' ),
        'new_item' => __( 'Website item', 'Website' ),
        'edit_item' => __( 'Edit Website', 'Website' ),
        'update_item' => __( 'Update Website', 'Website' ),
        'view_item' => __( 'View Website', 'Website' ),
        'view_items' => __( 'View Websites', 'Website' ),
        'search_items' => __( 'Search Website', 'Website' ),
        'not_found' => __( 'Not found.', 'Website' ),
        'not_found_in_trash' => __( 'Not found in trash.', 'Website' ),
        'featured_image' => __( 'Featured Image', 'Website' ),
        'set_featured_image' => __( 'Set Featured Image', 'Website' ),
        'remove_featured_image' => __( 'Remove Featured Image', 'Website' ),
        'use_featured_image' => __( 'Use Featured Image', 'Website' ),
        'insert_into_item' => __( 'Insert into item', 'Website' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'Website' ),
        'items_list' => __( 'Items List', 'Website' ),
        'items_list_navigation' => __( 'Navigation', 'Website' ),
        'filter_items_list' => __( 'Filter Items List', 'Website' ),
    );
    $args = array(
        'label' => __( 'Website', 'Website' ),
        'description' => __( 'Website', 'Website' ),
        'labels' => $labels,
        'menu_icon' => 'dashicons-admin-tools',
        'supports' => array(),
        'taxonomies' => array(),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'exclude_from_search' => false,
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );
    register_post_type( 'Website', $args );

}
add_action( 'init', 'create_website_cpt', 0 );

add_action( 'admin_init', 'my_admin' );

function my_admin() {
    add_meta_box( 
        'website_meta_box',
        'Website Information',
        'display_website_meta_box',
        'website',
        'normal',
        'high'
    );
}

function display_website_review_meta_box() {
    ?>
    <table>
        <tr>
            <td style="width: 50%">UIID</td>
            <td><input type="text" size="40" name="websites" value="<?php echo get_post_meta( get_the_ID(), 'id', true ); ?>" readonly /></td>
        </tr>
        <tr>
            <td style="width: 50%">Keyword</td>
            <td><input type="text" size="40" name="websites" value="<?php echo get_post_meta( get_the_ID(), 'keyword', true ); ?>" readonly /></td>
        </tr>
        <tr>
            <td style="width: 50%">Link</td>
            <td><input type="text" size="40" name="websites" value="<?php echo get_post_meta( get_the_ID(), 'link', true ); ?>" readonly /></td>       
        </tr>
        <tr>
            <td style="width: 50%">Banner</td>
            <td><input type="file" size="40" name="websites" value="<?php echo get_post_meta( get_the_ID(), 'banner', true ); ?>" readonly /></td>
        </tr>
        <tr>
            <td style="width: 50%">Description</td>
            <td><input type="text" size="40" name="websites" value="<?php echo get_post_meta( get_the_ID(), 'description', true ); ?>" readonly /></td>       
        </tr>
    </table>
    <?php
}

add_action( 'wp', 'insert_into_website_cpt' );

function check_for_similar_meta_ids() {
    $id_arrays_in_cpt = array();

    $args = array(
        'post_type'      => 'website',
        'posts_per_page' => -1,
    );

    $loop = new WP_Query($args);
    while( $loop->have_posts() ) {
        $loop->the_post();
        $id_arrays_in_cpt[] = get_post_meta( get_the_ID(), 'id', true );
    }

    return $id_arrays_in_cpt;
}

function query_websites_table( $website_available_in_cpt_array ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'websites';

    if ( NULL === $website_available_in_cpt_array || 0 === $website_available_in_cpt_array || '0' === $website_available_in_cpt_array || empty( $website_available_in_cpt_array ) ) {
        $results = $wpdb->get_results("SELECT * FROM $table_name");
        return $results;
    } else {
        $ids = implode( ",", $website_available_in_cpt_array );
        $sql = "SELECT * FROM $table_name WHERE id NOT IN ( $ids )";
        $results = $wpdb->get_results( $sql );
        return $results;
    }
}
function insert_into_Website_cpt() {

    $website_available_in_cpt_array = check_for_similar_meta_ids();
    $database_results = query_websites_table( $website_available_in_cpt_array );

    if ( NULL === $database_results || 0 === $database_results || '0' === $database_results || empty( $database_results ) ) {
        return;
    }

    foreach ( $database_results as $result ) {
        $website_model = array(
            'post_title' => wp_strip_all_tags( $result->title),
            'meta_input' => array(
                'id'        	=> $result->id,
                'keyword'     => $result->keyword,
                'link'        => $result->link,
                'banner'      => $result->banner,
                'description' => $result->description,
            ),
            'post_type'   => 'website',
            'post_status' => 'publish',
        );
        wp_insert_post( $website_model );
    }
}