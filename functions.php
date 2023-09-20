/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function hello_elementor_child_enqueue_scripts() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts' );


/** Add shortcodes for copyright automatic year update using [year] **/
function year_shortcode() {
  $year = date('Y');
  return $year;
}
add_shortcode('year', 'year_shortcode');

//Change WP logo for login or reset password
function my_login_logo_one() { 
?> 
<style type="text/css"> 
body.login div#login h1 a {
 background-image: url(https://www.webiva.ch/wp-content/uploads/WEBIVA-Favicon.svg);
padding-bottom: 10px; 
} 
</style>
 <?php 
} add_action( 'login_enqueue_scripts', 'my_login_logo_one' );

/**
 * Custom admin login header link
 */
function custom_login_url() {
    return home_url( '/' );
}
add_filter( 'login_headerurl', 'custom_login_url' );


//Hides elementor template library from menu for user with editor permission
function remove_menus(){
	// get current login user's role
	$roles = wp_get_current_user()->roles;

	// test role
	if( !in_array('contributor',$roles)){
	return;
	}
	

	$ptype = 'elementor_library';
	
	//remove menu from site backend.
	remove_menu_page( 'edit-comments.php' ); // Comments 
	remove_menu_page( 'tools.php' ); // Tools
// 	remove_menu_page( 'edit-tags.php?taxonomy=elementor_library_category' ); // elementor category
// 	remove_menu_page( 'edit.php?post_type=elementor_library&tabs_group=library' ); // elementor category
	remove_menu_page( 'edit.php?post_type=elementor_library' ); // Elementor Templates
	remove_menu_page( 'elementor' ); // Elementor
// 	remove_submenu_page( 'edit.php?post_type={$ptype}', 'edit-tags.php?taxonomy=elementor_library_category&amp;post_type={$ptype}' );
	remove_menu_page( '/#menu-posts-elementor_library');
	
}
add_action( 'admin_menu', 'remove_menus' , 999 );


// Maps posts capability to elementor_library post type capabilities  
function editing_elementor_library_capability($args, $post_type)
{
  if ('elementor_library' === $post_type) {
    $args['map_meta_cap'] = true;
    $args['capability_type'] = 'elementor_library'; // You could set your capability type name here
  }
  return $args;
}
add_filter('register_post_type_args', 'editing_elementor_library_capability', 10, 2);

// Allow .vcf file upload on wordpress
function enable_vcard_upload( $mime_types=array() ){
	$mime_types['vcf'] = 'text/vcard';
	$mime_types['vcard'] = 'text/vcard';
	return $mime_types;
}
add_filter('upload_mimes', 'enable_vcard_upload' );


// BEGIN -----  Move all .vcf file upload to specific foler '/wp-content/uploads/'
add_filter('wp_handle_upload_prefilter', 'webiva_pre_upload');
add_filter('wp_handle_upload', 'webiva_post_upload');

function webiva_pre_upload($file){
    add_filter('upload_dir', 'webiva_custom_upload_dir');
    return $file;
}

function webiva_post_upload($fileinfo){
    remove_filter('upload_dir', 'webiva_custom_upload_dir');
    return $fileinfo;
}

function webiva_custom_upload_dir($path){    
    $extension = substr(strrchr($_POST['name'],'.'),1);
    if(!empty($path['error']) ||  $extension != 'vcf') { return $path; } //error or other filetype; do nothing. 
    $customdir = '';
    $path['path']    = str_replace($path['subdir'], '', $path['path']); //remove default subdir (year/month)
    $path['url']     = str_replace($path['subdir'], '', $path['url']);      
    $path['subdir']  = $customdir;
    $path['path']   .= $customdir; 
    $path['url']    .= $customdir;  
    return $path;
}
// END -----  Move all .vcf file upload to specific foler '/wp-content/uploads/'

/***  Auto redirect a user who isn't logged into the site ***/
add_filter( 'pre_get_posts', 'swpm_auto_redirect_non_members' );
function swpm_auto_redirect_non_members() {
    if (is_admin()){
        //Inside the admin dashboard. Nothing to do.
        return;
    }
    //We are on the front-end. Lets check visitor's login status.
    if( !SwpmMemberUtils::is_member_logged_in() && !is_page( array( 'membership-login', 'membership-join' )) ) {
        wp_redirect( 'https://vod.zpo-cpc.ch/member-login/' );
        exit;
    }
}
