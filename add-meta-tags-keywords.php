<?php
/**
 * Plugin Name: Add Meta Tag Keywords
 * Description: Add Meta Tag keywords for posts, pages, custom posts.
 * Version: 1.0.0
 * Author: Hema Rawat
 * Author URI: https://www.epiphanyinfotech.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

function AMTK_register() {

    $post_type_arr = array('post','page');

    global $wpdb;
    $prefix_posts = $wpdb->prefix.'posts';
    $post_type = $wpdb->get_results("SELECT DISTINCT post_type FROM ".$prefix_posts." WHERE post_type!='revision'");

    $singlearray = [];

    foreach($post_type as $key => $val1){
    	foreach($val1 as $meta_post_type){
    		$singlearray[] = $meta_post_type;
    	}
    }
   
    $all_post_type_arr = !empty($singlearray) ? $singlearray : $post_type_arr;		

	add_meta_box( 
		'mb-post-id',
		'Add Meta Tag Keywords',
		'AMTK_display_callback',
		$all_post_type_arr,			
		'side',
    );
	
}
add_action( 'add_meta_boxes', 'AMTK_register' );


function AMTK_display_callback( $post ) {      
?>

	<div class="metabox_box components-form-token-field">			   
	    <p class="meta-options metabox_field">
	        <input id="hrd_post_meta_tag_keywords" type="text" name="hrd_post_meta_tag_keywords" value="<?php echo esc_attr(get_post_meta(get_the_ID(),'hrd_post_meta_tag_keywords',true));?>" />
	        <br/>
	        <p>(Press "," or "↵" to create a tag.)</p> 
	    </p> 		   
	</div>

<?php

}

function AMTK_save_post( $post_id ) 
{

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; //We are not going to save when the page is getting autosaved

    if ( $parent_id = wp_is_post_revision( $post_id ) ) {
        $post_id = $parent_id;
        }

    $fields = [
        'hrd_post_meta_tag_keywords',
        ];

    foreach ( $fields as $field ) {
        if ( array_key_exists( $field, $_POST ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
        }
     }
}
add_action( 'save_post', 'AMTK_save_post' );



function AMTK_to_head(){
    global $post;
	$meta_keywords_str = get_post_meta($post->ID,'hrd_post_meta_tag_keywords', true);

	if(!empty($meta_keywords_str)){
		echo  "<meta name='keywords' content='".esc_attr($meta_keywords_str)."'>";
    }

}
add_action('wp_head', 'AMTK_to_head' );


function AMTK_js_script() {
   
	$plugin_data = get_plugin_data( __FILE__ );
	$ver = $plugin_data['Version'];

	wp_enqueue_script(
                   'jquery.amsify.suggestags',
                    plugin_dir_url( __FILE__ ) . 'js/jquery.amsify.suggestags.js', 
                    array('jquery'), 
                    $ver, 
                    false 
                );
 
	wp_enqueue_script(
                   'add_meta_tag_plugin_js',
                    plugin_dir_url( __FILE__ ) . 'js/add_meta_tag_plugin.js', 
                    array('jquery', 'jquery.amsify.suggestags'), 
                    $ver, 
                    false 
                );


}	
add_action('admin_enqueue_scripts', 'AMTK_js_script');
// add_action('wp_enqueue_scripts', 'AMTK_js_script');

function AMTK_css_style() {

	wp_enqueue_style( 
	             'amsify.suggestags', 
	              plugin_dir_url( __FILE__ ) . 'css/amsify.suggestags.css',
                );
	wp_enqueue_style( 
	             'add.meta.tag.keywords', 
	              plugin_dir_url( __FILE__ ) . 'css/add.meta.tag.keywords.css',
	              array('amsify.suggestags'), 
                );
}

add_action('admin_enqueue_scripts', 'AMTK_css_style');
// add_action('wp_enqueue_scripts', 'AMTK_css_style');



function AMTK_activate() {
	global $wpdb;
    $prefix_postmeta = $wpdb->prefix.'postmeta';
    $delete_meta_value = $wpdb->get_results("DELETE FROM ".$prefix_postmeta." WHERE meta_key = 'hrd_post_meta_tag_keywords' ");
}
register_activation_hook( __FILE__ , 'AMTK_activate' );





