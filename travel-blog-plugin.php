<?php
/**
 * Plugin Name: Travel Blog Plugin
 * Plugin URI: https://github.com/np2861996/
 * Description: Quick, easy, advance plugin. 
 * Author: nikhil patel
 * Author URI: https://nikhil_patel.net/
 * Text Domain: TBP
 * Version: 1.0.0
 *
 * @package TBP
 * @author nikhil patel
 */

 

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if (!defined('TBP_PLUGIN_DIRNAME')) {
    define('CREST_PLUGIN_DIRNAME', plugin_basename(dirname(__FILE__)));
}
if (!defined('TBP_PLUGIN_VERSION')) {
    define('TBP_PLUGIN_VERSION', '1.0.0');
}

// Plugin Path.
if ( ! defined( 'TBP_PLUGIN_PATH' ) ) {
	define( 'TBP_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );
}

//css file
function crest_scripts() {
    wp_enqueue_style( 'style-name', TBP_PLUGIN_PATH.'css/tbp-custom.css' );
}

add_action( 'wp_enqueue_scripts', 'crest_scripts' );

// Admin File
include 'inc/tbp-admin-settings.php';

/** 
*
* Add social share buttons on every images and videos
*
**/
add_filter( 'the_content', 'add_social_share_buttons' );

function add_social_share_buttons( $content ) {
    global $post;

    // Check if there's at least one image or video in the content
    if (strpos($content, '<img') === false && strpos($content, '<video') === false) {
        return $content;
    }

    // Get the post URL and title
    $post_url = urlencode(get_permalink());
    $post_title = str_replace( ' ', '%20', get_the_title());

    // Add fontawesome stylesheet
    wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css' );

    // Generate the share buttons HTML
    $share_html = '<div class="social-share-buttons">';
    $share_html .= '<span>Share:</span>';
    $share_html .= '<a href="https://www.facebook.com/sharer/sharer.php?u=' . $post_url . '"><i class="fa fa-facebook-square"></i></a>';
    $share_html .= '<a href="https://twitter.com/intent/tweet?text=' . $post_title . '&amp;url=' . $post_url . '"><i class="fa fa-twitter-square"></i></a>';
    $share_html .= '<a href="https://www.instagram.com/sharer/sharer.php?u=' . $post_url . '"><i class="fa fa-instagram"></i></a>';
    $share_html .= '</div>';

    // Add the share buttons HTML after every image or video in the content
    $content = str_replace('<img', $share_html . '<img', $content);
    $content = str_replace('<video', $share_html . '<video', $content);

    return $content;
}

/** 
*
* Blog Submit Form for users
*
*/
function travel_blog_submission_form() {
    if (! is_user_logged_in() ) {
        return '<div>Please log in to add contributions.</div>';
    }
    
    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" name="name" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="article">Article:</label>
        <textarea name="article" required></textarea>

        <label for="photos">Photos:</label>
        <input type="file" name="photos[]" accept="image/*" multiple>

        <label for="videos">Videos:</label>
        <input type="file" name="videos[]" accept="video/*" multiple>

        <input type="submit" name="user_article_submit" value="Submit">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode( 'travel_blog_submission', 'travel_blog_submission_form' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );
function handle_submission_form() {
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    if (isset($_POST['user_article_submit'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $article = $_POST['article'];
        $photos = $_FILES['photos'];
        $videos = $_FILES['videos'];

        $post_data = array(
            'post_title'   => $name,
            'post_content' => $article,
            'post_author'  => get_current_user_id(),
            'post_status'  => 'draft',
            'post_type'    => 'post',
        );

        $post_id = wp_insert_post( $post_data );

        if ( ! is_wp_error( $post_id ) ) {
            // Handle file uploads
           // Handle file uploads
           if ( isset( $photos['name'] ) ) {
            $upload_photos = array();
            foreach ( $photos['name'] as $key => $value ) {
                if ( $photos['name'][ $key ] ) {
                    $file = array(
                        'name'     => $photos['name'][ $key ],
                        'type'     => $photos['type'][ $key ],
                        'tmp_name' => $photos['tmp_name'][ $key ],
                        'error'    => $photos['error'][ $key ],
                        'size'     => $photos['size'][ $key ]
                    );

                    $upload_photos[] = media_handle_sideload( $file, $post_id );
                }
            }
            update_post_meta( $post_id, '_photos', $upload_photos );
        }

        if ( isset( $videos['name'] ) ) {
            $upload_videos = array();
            foreach ( $videos['name'] as $key => $value ) {
                if ( $videos['name'][ $key ] ) {
                    $file = array(
                        'name'     => $videos['name'][ $key ],
                        'type'     => $videos['type'][ $key ],
                        'tmp_name' => $videos['tmp_name'][ $key ],
                        'error'    => $videos['error'][ $key ],
                        'size'     => $videos['size'][ $key ]
                    );

                    $upload_videos[] = media_handle_sideload( $file, $post_id );
                }
            }
            update_post_meta( $post_id, '_videos', $upload_videos );
        }

        // Get attachment URLs and concatenate to post content
        $attachments = array_merge( $upload_photos, $upload_videos );
        $attachment_urls = array();
foreach ( $attachments as $attachment_id ) {
    $mime_type = get_post_mime_type($attachment_id);
    if (strpos($mime_type, 'image') === 0) {
        $attachment_urls[] = '<img src="' . wp_get_attachment_url( $attachment_id ) . '" />';
    } else {
        //$attachment_urls[] = wp_get_attachment_url( $attachment_id ) ;
        $attachment_urls[] = '<video controls=""  src="' . wp_get_attachment_url( $attachment_id ) . '" /></video>' ;
    }
}
$post_content = $article . "\n\n" . implode( "\n\n", $attachment_urls );
$post_content = wpautop( $post_content );

        // Update post content
        $post_data = array(
            'ID'           => $post_id,
            'post_content' => $post_content,
        );
        wp_update_post( $post_data );


            // Redirect to thank you page
            wp_redirect( home_url('/user-contributions/') );
            exit;
        }
    }
}
add_action( 'init', 'handle_submission_form' );

/** 
*
* User Profile Update Shortcode
*
**/
function user_profile_shortcode() {
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $first_name = $current_user->first_name;
        $last_name = $current_user->last_name;
        $nickname = $current_user->nickname;
        $travel_interests = get_user_meta( $current_user->ID, 'travel_interests', true );
        $preferences = get_user_meta( $current_user->ID, 'preferences', true );
        $bio = get_user_meta( $current_user->ID, 'description', true );
        $avatar = get_avatar_url( $current_user->ID );
        $preferences = get_user_meta( $current_user->ID, 'preferences', true );
        $disSucc = '';
        if ( isset( $_GET['updated'] ) && $_GET['updated'] === 'true' ){

            $disSucc = '<div class="alert alert-success">
            Your profile has been updated.
        </div>';
        }

        $form_html = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">
            <div class="form-group">
                <label for="first_name">' . esc_html__( 'First Name', 'text-domain' ) . '</label>
                <input type="text" class="form-control" name="first_name" id="first_name" value="' . esc_attr( $first_name ) . '" required>
            </div>
            <div class="form-group">
                <label for="last_name">' . esc_html__( 'Last Name', 'text-domain' ) . '</label>
                <input type="text" class="form-control" name="last_name" id="last_name" value="' . esc_attr( $last_name ) . '" required>
            </div>
            <div class="form-group">
                <label for="nickname">' . esc_html__( 'Nickname', 'text-domain' ) . '</label>
                <input type="text" class="form-control" name="nickname" id="nickname" value="' . esc_attr( $nickname ) . '" required>
            </div>
            <div class="form-group">
                <label for="bio">' . esc_html__( 'Biographical Info', 'text-domain' ) . '</label>
                <textarea class="form-control" name="bio" id="bio">' . esc_attr( $bio ) . '</textarea>
            </div>
            <div class="form-group">
                <label for="avatar">' . esc_html__( 'Profile Picture', 'text-domain' ) . '</label>
                <br>
                <img src="' . esc_attr( $avatar ) . '" width="100" height="100" alt="' . esc_attr( $current_user->display_name ) . '">
                <br>
                <p class="description">
									<a href="https://en.gravatar.com/" target="_blank">You can change your profile picture on Gravatar</a>.								</p>
            </div>
            <div class="form-group">
                <label for="travel_interests">' . esc_html__( 'Travel Interests', 'text-domain' ) . '</label>
                <select class="form-control" name="travel_interests" id="travel_interests">
                    <option value="adventure" ' . selected( $travel_interests, 'adventure', false ) . '>Adventure</option>
                    <option value="beach" ' . selected( $travel_interests, 'beach', false ) . '>Beach</option>
                    <option value="culture" ' . selected( $travel_interests, 'culture', false ) . '>Culture</option>
                </select>
            </div>
            <div class="form-group">
                <label for="preferences">' . esc_html__( 'Preferences', 'text-domain' ) . '</label>
                <select class="form-control" name="preferences" id="preferences">
                    <option value="budget" ' . selected( $preferences, 'budget', false ) . '>Budget</option>
                    <option value="luxury" ' . selected( $preferences, 'luxury', false ) . '>Luxury</option>
                    <option value="local" ' . selected( $preferences, 'local', false ) . '>Local</option>
                </select>
            </div>
            <button type="submit" name="submit_profile" class="btn btn-primary">' . esc_html__( 'Save Changes', 'text-domain' ) . '</button>
            <input type="hidden" name="action" value="save_user_profile">
            ' . wp_nonce_field( 'save_user_profile_nonce', 'save_user_profile_nonce' ) . '
        </form>'. $disSucc;

        return $form_html;
    } else {
        return esc_html__( 'Please log in to view your profile.', 'text-domain' );
    }
}

add_shortcode( 'user_profile', 'user_profile_shortcode' );

/** 
*
* Save and update user profile data
*
*/
function save_user_profile_data() {
    if ( isset( $_POST['submit_profile'] ) ) {
        $user_id = get_current_user_id();

        if ( $user_id ) {
            $first_name = sanitize_text_field( $_POST['first_name'] );
            $last_name = sanitize_text_field( $_POST['last_name'] );
            $nickname = sanitize_text_field( $_POST['nickname'] );
           // $bio = sanitize_textarea_field( $_POST['bio'] );
            $travel_interests = sanitize_text_field( $_POST['travel_interests'] );
            $preferences = sanitize_text_field( $_POST['preferences'] );
            $biographical_info = sanitize_text_field( $_POST['bio'] );

            update_user_meta( $user_id, 'first_name', $first_name );
            update_user_meta( $user_id, 'last_name', $last_name );
            update_user_meta( $user_id, 'travel_interests', $travel_interests );
            update_user_meta( $user_id, 'preferences', $preferences );
            update_user_meta( $user_id, 'nickname', $nickname );
            wp_update_user( array(
                'ID' => $user_id,
                'description' => $biographical_info,
            ) );
           

          // Handle profile picture upload
if ( ! empty( $_FILES['profile_picture'] ) ) {
    $file = $_FILES['profile_picture'];
    $upload_dir = wp_upload_dir();
    $allowed_types = array( 'image/jpeg', 'image/png', 'image/gif' );

    if ( in_array( $file['type'], $allowed_types ) ) {
        $file_name = sanitize_file_name( $file['name'] );
        $file_path = $upload_dir['path'] . '/' . $file_name;
        $file_url = $upload_dir['url'] . '/' . $file_name;

        if ( move_uploaded_file( $file['tmp_name'], $file_path ) ) {
            // Delete old profile picture
            $old_picture_id = get_user_meta( $user_id, 'profile_picture_id', true );
            if ( ! empty( $old_picture_id ) ) {
                wp_delete_attachment( $old_picture_id, true );
            }
            // Add new profile picture
            $attachment = array(
                'post_mime_type' => $file['type'],
                'post_title' => $file_name,
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $file_path );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            update_user_meta( $user_id, 'profile_picture_id', $attach_id );
        }
    }
}


            wp_redirect( '/profile/?updated=true' );
            exit();
        }
    }
}
add_action( 'init', 'save_user_profile_data' );

// Add custom fields to "About Yourself" section in user profile
function add_custom_user_profile_fields( $user ) {
    ?>
    <h3><?php esc_html_e( 'Custom Fields', 'text-domain' ); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="travel_interests"><?php esc_html_e( 'Travel Interests', 'text-domain' ); ?></label></th>
            <td>
                <input type="text" name="travel_interests" id="travel_interests" value="<?php echo esc_attr( get_user_meta( $user->ID, 'travel_interests', true ) ); ?>" class="regular-text" /><br />
                
            </td>
        </tr>
        <tr>
            <th><label for="preferences"><?php esc_html_e( 'Preferences', 'text-domain' ); ?></label></th>
            <td>
                <input type="text" name="preferences" id="preferences" value="<?php echo esc_attr( get_user_meta( $user->ID, 'preferences', true ) ); ?>" class="regular-text" /><br />
                
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'add_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'add_custom_user_profile_fields' );


// Save custom fields to user meta
function save_custom_user_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }
    update_user_meta( $user_id, 'travel_interests', sanitize_text_field( $_POST['travel_interests'] ) );
    update_user_meta( $user_id, 'preferences', sanitize_text_field( $_POST['preferences'] ) );
}
add_action( 'personal_options_update', 'save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_custom_user_profile_fields' );
function wp_custom_registration_form() {
    ob_start();

    if ( is_user_logged_in() ) {
        echo 'You are already logged in.';
    } else {
        $errors = array();
        $success = null;

        if ( isset( $_POST['register'] ) ) {
            $username = sanitize_user( $_POST['username'] );
            $email = sanitize_email( $_POST['email'] );
            $password = $_POST['password'];

            if ( empty( $username ) ) {
                $errors[] = 'Please enter a username.';
            }

            if ( empty( $email ) || ! is_email( $email ) ) {
                $errors[] = 'Please enter a valid email address.';
            }

            if ( empty( $password ) ) {
                $errors[] = 'Please enter a password.';
            }

            if ( ! empty( $errors ) ) {
                foreach ( $errors as $error ) {
                    echo '<div class="alert alert-danger">' . esc_html( $error ) . '</div>';
                }
            } else {
                $user_data = array(
                    'user_login' => $username,
                    'user_email' => $email,
                    'user_pass'  => $password
                );

                $user_id = wp_insert_user( $user_data );

                if ( is_wp_error( $user_id ) ) {
                    echo '<div class="alert alert-danger">' . $user_id->get_error_message() . '</div>';
                } else {
                    $success = 'Registration complete. You can now log in to your account. ';
                   //  ob_end_clean(); // Clear the buffer
                   
                    //exit;
                }
            }
        }

        if ( $success ) {
            echo '<div class="alert alert-success">' . esc_html( $success ) . '<a href="/login-user/">Login</a></div>';
            //wp_redirect( "/login-user/", 301 );
             //exit();
        }
        ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <?php do_action( 'register_form' ); ?>
            <button type="submit" name="register" class="btn btn-primary">Register</button>
        </form>

        <?php
    }

    return ob_get_clean();
}
add_shortcode( 'wp_register_form', 'wp_custom_registration_form' );

/** 
*
* Login Form shortcode
*
*/
function custom_login_form_shortcode() {
    if ( is_user_logged_in() ) {
        return '<p>You are already logged in.</p>';
    }

    $args = array(
        'redirect' => home_url(),
        'form_id' => 'loginform-custom',
        'label_username' => __( 'Username' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in' => __( 'Log In' ),
        'remember' => true
    );
    return wp_login_form( $args );
}
add_shortcode( 'custom_login_form', 'custom_login_form_shortcode' );

function add_loginout_link_to_menu( $items, $args ) {
    if ( $args->theme_location == 'primary' && ! is_user_logged_in() ) {
        $loginout_link = '<li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="/login-user/">Log In</a></li>';
        $register_link = ' <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="/registration-user/"> Register</a></li>';
        $items .= $loginout_link . '  '.$register_link;
    }
    return $items;
}
add_filter( 'wp_nav_menu_items', 'add_loginout_link_to_menu', 10, 2 );

function add_logout_link_to_menu( $items, $args ) {
    if ( $args->theme_location == 'primary' && is_user_logged_in() ) { // Change 'primary' to match the name of your menu location
        $items .= '<li class="menu-item"><a href="' . wp_logout_url( home_url() ) . '">Logout</a></li>';
    }
    return $items;
}
add_filter( 'wp_nav_menu_items', 'add_logout_link_to_menu', 10, 2 );

/** 
*
* enqueue scripts
*
*/
add_action( 'wp_enqueue_scripts', 'my_enqueue_scripts' );
function my_enqueue_scripts() {
  

    wp_enqueue_script( 'my-script', plugin_dir_url( __FILE__ ) . 'js/tbp.js', array( 'jquery' ), '1.0', true );
wp_localize_script( 'my-script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}


/** 
*
* Post filter shortcode using ajax
*
*/
function post_filter_shortcode() {
    ob_start(); ?>

    <form id="post-filter-form">
    <div>
        <label for="destination">Destination:</label>
        <?php wp_dropdown_categories( array( 'taxonomy' => 'destination', 'name' => 'destination', 'hide_empty' => false, 'show_option_all' => 'All destinations' ) ); ?>
    </div>

    <div>
        <label for="theme">Theme:</label>
        <?php wp_dropdown_categories( array( 'taxonomy' => 'theme', 'name' => 'theme', 'hide_empty' => false, 'show_option_all' => 'All themes' ) ); ?>
    </div>


        <div>
            <label for="category">Category:</label>
            <?php wp_dropdown_categories( array( 'taxonomy' => 'category', 'name' => 'category', 'hide_empty' => false, 'show_option_all' => 'All categories' ) ); ?>
        </div>

        <div>
            <label for="tag">Tag:</label>
            <?php wp_dropdown_categories( array( 'taxonomy' => 'post_tag', 'name' => 'tag', 'hide_empty' => false, 'show_option_all' => 'All tags' ) ); ?>
        </div>

        <div>
            <input type="hidden" name="action" value="post_filter">
            <button id="post-filter-submit" type="submit">Filter</button>
        </div>
    </form>

   

    <div id="post-filter-results">

        <?php 

        global $wpdb;
 $args = array(
    'post_type' => 'post',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC'
);

 // Retrieve the post details using the query
 $query = new WP_Query($args);
 if ($query->have_posts()) :
     while ($query->have_posts()) : $query->the_post();
         // Get the post title
         $title = get_the_title();
         
         // Get the post date
         $date = get_the_date('F j, Y');
         
         // Get the post author name
         $author_name = get_the_author_meta('display_name');
         
         // Get the post content
         $content = wp_trim_words(get_the_content(), 50, '...');
         
         // Get the post categories
         $categories = get_the_category();
         $category_list = '';
         if (!empty($categories)) {
             foreach ($categories as $category) {
                 $category_list .= '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>, ';
             }
             $category_list = rtrim($category_list, ', ');
         }
         
         // Get the post tags
         $tags = get_the_tags();
         $tag_list = '';
         if (!empty($tags)) {
             foreach ($tags as $tag) {
                 $tag_list .= '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . esc_html($tag->name) . '</a>, ';
             }
             $tag_list = rtrim($tag_list, ', ');
         }
         
         // Get the destination taxonomy terms
         $destinations = get_the_terms(get_the_ID(), 'destination');
         $destination_list = '';
         if (!empty($destinations)) {
             foreach ($destinations as $destination) {
                 $destination_list .= '<a href="' . esc_url(get_term_link($destination)) . '">' . esc_html($destination->name) . '</a>, ';
             }
             $destination_list = rtrim($destination_list, ', ');
         }
         
         // Get the theme taxonomy terms
         $themes = get_the_terms(get_the_ID(), 'theme');
         $theme_list = '';
         if (!empty($themes)) {
             foreach ($themes as $theme) {
                 $theme_list .= '<a href="' . esc_url(get_term_link($theme)) . '">' . esc_html($theme->name) . '</a>, ';
             }
             $theme_list = rtrim($theme_list, ', ');
         }

         $link = '<a href="' . esc_url(get_permalink()) . '">' . $title . '</a>';
         
        // Get the featured image
        $image = '';
        if (has_post_thumbnail()) {
            $image = '<a href="' . esc_url(get_permalink()) . '">' . get_the_post_thumbnail() . '</a>';
        }

         // Display the post details
         echo '<div class="main-post">';
         echo '<h1>' .  $link . '</h1>';
         echo '<div class="img">'. $image .'</div>';
         echo '<p>Posted on ' . $date . ' by ' . $author_name . '</p>';
         echo '<p>Categories: ' . $category_list . '</p>';
         echo '<p>Tags: ' . $tag_list . '</p>';
         echo '<p>Destinations: ' . $destination_list . '</p>';
         echo '<p>Themes: ' . $theme_list . '</p>';
         echo $content;
         echo '</div>';
     endwhile;
 endif;

  // Reset the query to restore the main query
  wp_reset_query();
        ?>

    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'post_filter', 'post_filter_shortcode' );


/** 
*
* Post filter ajax handler function
*
*/
function post_filter_ajax_handler() {

    global $wpdb;

    $destination = isset( $_POST['destination'] ) ? sanitize_text_field( $_POST['destination'] ) : '';
    $theme = isset( $_POST['theme'] ) ? sanitize_text_field( $_POST['theme'] ) : '';
    $category = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
    $tag = isset( $_POST['tag'] ) ? sanitize_text_field( $_POST['tag'] ) : '';

    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1
    );

    if ( $destination ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'destination',
            'field' => 'term_id',
                'terms' => $destination
        );
    }

    if ( $theme ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'theme',
            'field' => 'term_id',
            'terms' => $theme
        );
    }

    if ( $category ) {
        $args['cat'] = $category;
    }

    if ( $tag ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'post_tag',
            'field' => 'term_id',
            'terms' => $tag
        );
    }

    // Retrieve the post details using the query
 $query = new WP_Query($args);
 if ($query->have_posts()) :
     while ($query->have_posts()) : $query->the_post();
         // Get the post title
         $title = get_the_title();
         
         // Get the post date
         $date = get_the_date('F j, Y');
         
         // Get the post author name
         $author_name = get_the_author_meta('display_name');
         
         // Get the post content
         $content = wp_trim_words(get_the_content(), 50, '...');
         
         // Get the post categories
         $categories = get_the_category();
         $category_list = '';
         if (!empty($categories)) {
             foreach ($categories as $category) {
                 $category_list .= '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>, ';
             }
             $category_list = rtrim($category_list, ', ');
         }
         
         // Get the post tags
         $tags = get_the_tags();
         $tag_list = '';
         if (!empty($tags)) {
             foreach ($tags as $tag) {
                 $tag_list .= '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . esc_html($tag->name) . '</a>, ';
             }
             $tag_list = rtrim($tag_list, ', ');
         }
         
         // Get the destination taxonomy terms
         $destinations = get_the_terms(get_the_ID(), 'destination');
         $destination_list = '';
         if (!empty($destinations)) {
             foreach ($destinations as $destination) {
                 $destination_list .= '<a href="' . esc_url(get_term_link($destination)) . '">' . esc_html($destination->name) . '</a>, ';
             }
             $destination_list = rtrim($destination_list, ', ');
         }
         
         // Get the theme taxonomy terms
         $themes = get_the_terms(get_the_ID(), 'theme');
         $theme_list = '';
         if (!empty($themes)) {
             foreach ($themes as $theme) {
                 $theme_list .= '<a href="' . esc_url(get_term_link($theme)) . '">' . esc_html($theme->name) . '</a>, ';
             }
             $theme_list = rtrim($theme_list, ', ');
         }

         $link = '<a href="' . esc_url(get_permalink()) . '">' . $title . '</a>';
         
        // Get the featured image
        $image = '';
        if (has_post_thumbnail()) {
            $image = '<a href="' . esc_url(get_permalink()) . '">' . get_the_post_thumbnail() . '</a>';
        }

         // Display the post details
         echo '<div class="main-post">';
         echo '<h1>' .  $link . '</h1>';
         echo '<div class="img">'. $image .'</div>';
         echo '<p>Posted on ' . $date . ' by ' . $author_name . '</p>';
         echo '<p>Categories: ' . $category_list . '</p>';
         echo '<p>Tags: ' . $tag_list . '</p>';
         echo '<p>Destinations: ' . $destination_list . '</p>';
         echo '<p>Themes: ' . $theme_list . '</p>';
         echo $content;
         echo '</div>';
     endwhile;
 endif;

  // Reset the query to restore the main query
  wp_reset_query();

    die();

}

add_action( 'wp_ajax_post_filter_ajax_handler', 'post_filter_ajax_handler' );
add_action( 'wp_ajax_nopriv_post_filter_ajax_handler', 'post_filter_ajax_handler' );

