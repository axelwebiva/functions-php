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


// Add content to empty cart page
add_action( 'woocommerce_cart_is_empty', 'show_content_empty_cart' );
function show_content_empty_cart() {
   echo "<style>.empty-cart-template {display:block !important}</style>";
}

// Auto uncheck "Ship to a different address"
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

// Add "+" and "-" button to shop
function appel_styles_et_scripts() {
  wp_enqueue_script( 'mon-fichier-javascript', get_stylesheet_directory_uri() . '/woocommerce/quantity-input-V1.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'appel_styles_et_scripts' );
wp_enqueue_style('styleCSS', get_stylesheet_directory_uri(). '/woocommerce/quantity-input-V1.css');

/* cart-auto-update-V1 script with jQuery as a dependency, enqueued in the footer */
add_action('wp_enqueue_scripts', 'tutsplus_enqueue_custom_js');
function tutsplus_enqueue_custom_js() {
    wp_enqueue_script('custom', get_stylesheet_directory_uri().'/woocommerce/cart-auto-update-V1.js', 
    array('jquery'), false, true);
}

/**
 * Rename "home" in woocommerce breadcrumb
 */
add_filter( 'woocommerce_breadcrumb_defaults', 'wcc_change_breadcrumb_home_text' );
function wcc_change_breadcrumb_home_text( $defaults ) {
    // Change the breadcrumb home text from 'Home' to 'Shop'
	$defaults['home'] = 'Shop';
	return $defaults;
}

/**
 * Replace the home link URL
 */
add_filter( 'woocommerce_breadcrumb_home_url', 'woo_custom_breadrumb_home_url' );
function woo_custom_breadrumb_home_url() {
    return 'https://boulangerie.webivadev.ch/boutique/';
}


// /**
//  * Chanages the "# in stock" text to "#" for WooCommerce
//  */
// add_filter( 'woocommerce_get_availability_text', 'webiva_custom_get_availability_text', 99, 2 );
// function webiva_custom_get_availability_text( $availability, $product ) {
//    $stock = $product->get_stock_quantity();
//    if ( $product->is_in_stock() && $product->managing_stock() ) $availability = '' . $stock;
//    return $availability;
// }
add_filter( 'woocommerce_get_price_html', 'change_variable_products_price_display', 10, 2 );
function change_variable_products_price_display( $price, $product ) {

    // Only for variable products type
    if( ! $product->is_type('variable') ) return $price;

    $prices = $product->get_variation_prices( true );

    if ( empty( $prices['price'] ) )
        return apply_filters( 'woocommerce_variable_empty_price_html', '', $product );

    $min_price = current( $prices['price'] );
    $max_price = end( $prices['price'] );
    $prefix_html = '<span class="price-prefix">' . __('A partir de: ') . '</span>';

    $prefix = $min_price !== $max_price ? $prefix_html : ''; // HERE the prefix

    return apply_filters( 'woocommerce_variable_price_html', $prefix . wc_price( $min_price ) . $product->get_price_suffix(), $product );
}
