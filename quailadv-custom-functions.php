<?php
/**
 * Plugin Name: QuailAdv Custom Functions
 * Plugin URI: http://quailadvertising.com
 * Description: This is an awesome custom plugin with functionality that I'd like to keep when switching things.
 * Author: Robert
 * Author URI: http://quailadvertising.com
 * Version: 0.1.0
 */

/* Change the login logo */
function my_custom_login_logo() {
    echo '<style type="text/css">
        h1 a { background-image:url('.get_bloginfo('template_directory').'/images/custom-login-logo.gif) !important; }
    </style>';
}

add_action('login_head', 'my_custom_login_logo');

/* Change the admin logo */
function custom_admin_logo() {
  echo '<style type="text/css">
          #header-logo { background-image: url('.get_bloginfo('template_directory').'/images/admin_logo.png) !important; }
        </style>';
}
add_action('admin_head', 'custom_admin_logo');

/* Disable WordPress Login Hints */
function no_wordpress_errors(){
  return 'GET OFF MY LAWN !! RIGHT NOW !!';
}
add_filter( 'login_errors', 'no_wordpress_errors' );

/* Keep logged in WordPress for a longer period */
add_filter( 'auth_cookie_expiration', 'stay_logged_in_for_1_year' );
function stay_logged_in_for_1_year( $expire ) {
  return 31556926; // 1 year in seconds
}

/* Replace "Howdy" with "Logged in as" in WordPress bar */
function replace_howdy( $wp_admin_bar ) {
    $my_account=$wp_admin_bar->get_node('my-account');
    $newtitle = str_replace( 'Howdy,', 'Logged in as', $my_account->title );
    $wp_admin_bar->add_node( array(
        'id' => 'my-account',
        'title' => $newtitle,
    ) );
}
add_filter( 'admin_bar_menu', 'replace_howdy',25 );

/* Change the footer text on WordPress dashboard */
function remove_footer_admin () {
  echo "Your own text";
} 

add_filter('admin_footer_text', 'remove_footer_admin');

/* WordPress Code Tweaks: Posts and Pages - */
/* Require a featured image before you can publish posts */
add_action('save_post', 'wpds_check_thumbnail');
add_action('admin_notices', 'wpds_thumbnail_error');

function wpds_check_thumbnail( $post_id ) {
  // change to any custom post type 
  if( get_post_type($post_id) != 'post' )
      return;

  if ( ! has_post_thumbnail( $post_id ) ) {
    // set a transient to show the users an admin message
    set_transient( "has_post_thumbnail", "no" );
    // unhook this function so it doesn't loop infinitely
    remove_action('save_post', 'wpds_check_thumbnail');
    // update the post set it to draft
    wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));

    add_action('save_post', 'wpds_check_thumbnail');
  } else {
    delete_transient( "has_post_thumbnail" );
  }
}

function wpds_thumbnail_error() {
  // check if the transient is set, and display the error message
  if ( get_transient( "has_post_thumbnail" ) == "no" ) {
    echo "<div id='message' class='error'><p><strong>You must add a Featured Image before publishing this. Don't panic, your post is saved.</strong></p></div>";
    delete_transient( "has_post_thumbnail" );
  }
}

/* Reduce Post Revisions */
define( 'WP_POST_REVISIONS', 3 );

/* Delay posting to my RSS feeds for 60 minutes */
function Delay_RSS_After_Publish($where) {
  global $wpdb;

  if (is_feed()) {
    $now = gmdate('Y-m-d H:i:s');
    $wait = '60';
    $device = 'MINUTE';
    $where.=" AND TIMESTAMPDIFF($device, $wpdb->posts.post_date_gmt, '$now') > $wait ";
  }
  return $where;
}

add_filter('posts_where', 'Delay_RSS_After_Publish');

/* Change the length of excerpts */
function custom_excerpt_length( $length ) {
  return 20;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

/* Change the post auto-save interval */
define( 'AUTOSAVE_INTERVAL', 45 );

/* WordPress Code Tweaks: Search - */
/* Exclude categories from search */
function SearchFilter($query) {
  if ( $query->is_search && ! is_admin() ) {
    $query->set('cat','8,15'); 
  }
  return $query; 
}
add_filter('pre_get_posts','SearchFilter');

/* Exclude pages from search - */
function modify_search_filter($query) {
  if ($query->is_search) {
    $query->set('post_type', 'post');
  }
  return $query;
}

add_filter('pre_get_posts','modify_search_filter');
?>
