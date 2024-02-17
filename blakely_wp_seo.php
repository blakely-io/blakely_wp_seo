<?php
/*
Plugin Name: Blakely WP Customisations
Plugin URI: https://blakely.io
Description: Adds meta Description & Keywords to each page. Updates product category on shop page & removes WP meta. Removed permalink on cart page
Version: 1.1.4
Author: Blakely.io
Domain Path: /languages
Text Domain: blakely-wp-custom-plugin
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$Blakely_WP_SEO_Plugin = new Blakely_WP_SEO_Plugin();
$Blakely_WP_SEO_Plugin->run();

class Blakely_WP_SEO_Plugin{

	private $plugin_name;
	private $meta_key_desc;
	private $meta_key_keys;
	private $show_on_front;

	function __construct(){
		$this->plugin_name = 'blakely-wp-seo-plugin';
		//$this->meta_key = '_blakely-wp-seo-plugin';
		$this->meta_key_desc = '_blakely-wp-seo-plugin-desc';
		$this->meta_key_keys = '_blakely-wp-seo-plugin-keys';

		// tells if a page has been selected as front page
		// possible values 'posts' and 'page'
		$this->show_on_front = get_option( 'show_on_front' );
	}

	function run(){
		if( is_admin() ){
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'add_meta_boxes', array( $this, 'description_meta_box') );
			add_action( 'add_meta_boxes', array( $this, 'keywords_meta_box') );
			add_action( 'save_post', array( $this, 'save_meta' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqeue_my_script' ) );
			if( $this->show_on_front == 'posts' ){
				add_action( 'admin_init', array( $this, 'setting_page' ) );
			}
		}
		else{
			remove_action( 'wp_head', 'rel_canonical' );
            //Remove WordPress & WooCommerce Meta generator
            remove_action('wp_head', 'wp_generator');
			add_action( 'wp_head', array( $this, 'insert_description_meta_in_head' ) );
			add_action( 'wp_head', array( $this, 'insert_keywords_meta_in_head' ) );
			add_action( 'wp_head', 'rel_canonical' );
            //Only shows products containing a specific slug
            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )))) {
                //add_action( 'woocommerce_product_query', array( $this, 'limit_shop_categories' ) );
                add_action( 'wp_head', array( $this, 'remove_permalink_from_cart' ) );
            }
		}
	}

	function setting_page(){

		$option_group = 'general';
		$option_name = 'blakely-wp-seo-plugin_front';
		register_setting( $option_group, $option_name );

		$id = 'blakely_wp_seo';
		$title = __( 'Front page meta description', 'blakely-wp-seo-plugin' );
		$callback = array( $this, 'front_descr_input' );
		$page = 'general';
		$section = 'default';
		$args = array( 'label_for' => 'blakely-wp-seo-plugin_front' );
		add_settings_field( $id, $title, $callback, $page, $section, $args );
	}

	function front_descr_input(){
		$setting = get_option( 'blakely-wp-seo-plugin_front' );
		?>
<input type="text" size="40" name="blakely-wp-seo-plugin_front" id="blakely_wp_seo"
	class="regular-text" value="<?php print $setting ?>">
<p class="description" id="meta-description"><?php
	_e( 'Added by', 'blakely-wp-seo-plugin' ); ?> Blakely WP SEO plugin. <?php
	_e( 'Character count', 'blakely-wp-seo-plugin' ); ?>: <span id="blakely_wp_output"></span></p>
		<?php
	}

	function load_plugin_textdomain(){
		load_plugin_textdomain( 'blakely-wp-seo-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}


	// Add Meta Description to the <head> section 
	function insert_description_meta_in_head(){
		$description = '';
		if( ! is_paged() ){
			if( is_single() or is_page() ){
				global $post;
	//			print "<!-- Blakely WP Comments is_single() or is_page() \nID: $post->ID\n-->";
				$description = get_post_meta( $post->ID, $this->meta_key_desc, true );
			}
			elseif( is_tag() or is_category() or is_tax()){
				$remove = array( '<p>', '</p>' );
				$description = trim( str_replace( $remove, '', term_description() ) );
			}
			elseif( is_front_page() ){
				if( $this->show_on_front == 'posts' ){
					if( ! $description = get_option( 'blakely-wp-seo-plugin_front' ) ){
						$description = get_bloginfo( 'description', 'display' );
					}
				}
				else{
					$post_id = get_option( 'page_on_front' );
					$description = get_post_meta( $post_id, $this->meta_key_desc, true );
				}
			}
			elseif( is_home() ){
				$home_id = get_option( 'page_for_posts' );
	//			print "<!-- Blakely WP Comments is_home()\nID: $post->ID\n\$home_id: $home_id\n -->";
				$description = get_post_meta( $home_id, $this->meta_key_desc, true );
			}
		}
		if( $description ){
				?>

<!-- Blakely WP SEO plugin -->
<meta name="description" content="<?php print $description; ?>">
<!-- /Blakely WP SEO plugin -->

				<?php
		}
	}
	
	// Add Meta Keywords to the <head> section 
	function insert_keywords_meta_in_head(){
		$keywords = '';
		if( ! is_paged() ){
			if( is_single() or is_page() ){
				global $post;
	//			print "<!-- Blakely WP Comments is_single() or is_page() \nID: $post->ID\n-->";
				$keywords = get_post_meta( $post->ID, $this->meta_key_keys, true );
			}
			elseif( is_tag() or is_category() or is_tax()){
				$remove = array( '<p>', '</p>' );
				$keywords = trim( str_replace( $remove, '', term_keywords() ) );
			}
			elseif( is_front_page() ){
				if( $this->show_on_front == 'posts' ){
					if( ! $keywords = get_option( 'blakely-wp-seo-plugin_front' ) ){
						$keywords = get_bloginfo( 'keywords', 'display' );
					}
				}
				else{
					$post_id = get_option( 'page_on_front' );
					$keywords = get_post_meta( $post_id, $this->meta_key_keys, true );
				}
			}
			elseif( is_home() ){
				$home_id = get_option( 'page_for_posts' );
	//			print "<!-- Blakely WP Comments is_home()\nID: $post->ID\n\$home_id: $home_id\n -->";
				$keywords = get_post_meta( $home_id, $this->meta_key_keys, true );
			}
		}
		if( $keywords ){
				?>

<!-- Blakely WP SEO plugin -->
<meta name="keywords" content="<?php print $keywords; ?>">
<!-- /Blakely WP SEO plugin -->

				<?php
		}
	}



	//Admin Page Description Meta Box
	function description_meta_box(){
		$id = 'add_description';
		$title =  'Meta Description';
		$callback = array( $this, 'add_description_meta_box' );
		$context = 'normal';
		$priority = 'high';
		$callback_args = '';

		// get custom posttypes
		$args = array( 'public'   => true, '_builtin' => false );
		$output = 'names';
		$operator = 'and';
		$custom_posttypes = get_post_types( $args, $output, $operator );
		$builtin_posttypes = array( 'post', 'page' );
		$screens = array_merge( $builtin_posttypes, $custom_posttypes );
		foreach ( $screens as $screen ) {
			add_meta_box( $id, $title, $callback, $screen, $context,
				 $priority, $callback_args,
				 array( '__block_editor_compatible_meta_box' => false, ) );
		}
	}

	function add_description_meta_box(){
		wp_nonce_field( 'add_description_meta_box', 'add_description_meta_box_nonce' );
		$post_id = get_the_ID();
		$value = get_post_meta( $post_id, $this->meta_key_desc, true );?>
<div class="wp-editor-container">
<textarea class="wp-editor-area" id="blakely_wp_seo_description" name="add_description" cols="80" rows="5"><?php print $value; ?></textarea>
</div>
<p><?php
		_e( 'Add a meta description to your HTML code', 'blakely-wp-seo-plugin' ); ?>. <?php
		_e( 'Character count', 'blakely-wp-seo-plugin' ); ?>: <span id="blakely_wp_desc_output"></span></p>
		<?php
	}


	//Admin Page Keywords Meta Box
	function keywords_meta_box(){
		$id = 'add_keywords';
		$title =  'Meta Keywords';
		$callback = array( $this, 'add_keywords_meta_box' );
		$context = 'normal';
		$priority = 'high';
		$callback_args = '';

		// get custom posttypes
		$args = array( 'public'   => true, '_builtin' => false );
		$output = 'names';
		$operator = 'and';
		$custom_posttypes = get_post_types( $args, $output, $operator );
		$builtin_posttypes = array( 'post', 'page' );
		$screens = array_merge( $builtin_posttypes, $custom_posttypes );
		foreach ( $screens as $screen ) {
			add_meta_box( $id, $title, $callback, $screen, $context,
					$priority, $callback_args,
					array( '__block_editor_compatible_meta_box' => false, ) );
		}
	}

	function add_keywords_meta_box(){
		wp_nonce_field( 'add_keywords_meta_box', 'add_keywords_meta_box_nonce' );
		$post_id = get_the_ID();
		$value = get_post_meta( $post_id, $this->meta_key_keys, true );?>
<div class="wp-editor-container">
<textarea class="wp-editor-area" id="blakely_wp_seo_keywords" name="add_keywords" cols="80" rows="5"><?php print $value; ?></textarea>
</div>
<p><?php
		_e( 'Add a meta keywords to your HTML code', 'blakely-wp-seo-plugin' ); ?>. <?php
		_e( 'Character count', 'blakely-wp-seo-plugin' ); ?>: <span id="blakely_wp_keys_output"></span></p>
		<?php
	}

	
	function enqeue_my_script(){
		$handle = "char_count";
		$src = plugin_dir_url( __FILE__ ) . "character_count.js";
		$deps = array('jquery');
		$ver = null;
		$in_footer = true;
		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}

	//Save Description Meta Box
	function save_meta( $post_id ){
		if( ! isset( $_POST['add_description_meta_box_nonce'] ) && ! isset( $_POST['add_keywords_meta_box_nonce'] )){
			return;
		}
		if( ! wp_verify_nonce( $_POST['add_description_meta_box_nonce'], 'add_description_meta_box' ) && ! wp_verify_nonce( $_POST['add_keywords_meta_box_nonce'], 'add_keywords_meta_box' ) ){
			return;
		}
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if( ! isset( $_POST['add_description'] ) && ! isset( $_POST['add_keywords'] ) ){
			return;
		}
		//Only sanatises a single text field
		//$data = sanitize_text_field(	$_POST['add_description'] );
		
		//Updated finctionality Meta Description and Meta Keywords
		$values = array( $_POST['add_description'], $_POST['add_keywords'] );
		foreach ( $values as $value ){
			$data = sanitize_text_field( $value );
			//Updates post meta fields meta_key_desc and Meta_key_keys
			update_post_meta( $post_id, ( $value == $_POST['add_description'] )?( $this->meta_key_desc ):( $this->meta_key_keys ), $data );
		}

		//update_post_meta( $post_id, $this->meta_key, $data );
	}

    //Limits the displayed categories on the shop page
    //This needs to be updated so that the category(s) can be updated from WP admin
    function limit_shop_categories( $q ) {
        $tax_query = (array) $q->get( 'tax_query' );

        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array( 'wellbeing-course' ),
            'include_children' => true,
        );

        $q->set( 'tax_query', $tax_query );
    }

    //Remove permalink from Cart
    function remove_permalink_from_cart() {
        add_filter('woocommerce_cart_item_permalink','__return_false');
    }

} // class


?>
