<?php
/*
Plugin Name: Rareiio Post Like
Plugin URI: https://github.com/MoazIrfan/Rareiio-Post-Like
Version: 1.0
Author: Moaz Irfan
Description: Allow user add post like button above or below post content.
*/

/* Setup the plugin. */
add_action( 'plugins_loaded', 'rareiio_plugin_setup' );

/* Register plugin activation hook. */
register_activation_hook( __FILE__, 'rareiio_plugin_activation' );

/* Register plugin activation hook. */
register_deactivation_hook( __FILE__, 'rareiio_plugin_deactivation' );
/**
 * Do things on plugin activation.
 */
function rareiio_plugin_activation() {
	/* Flush permalinks. */
    flush_rewrite_rules();
}
/**
 * Flush permalinks on plugin deactivation.
 */
function rareiio_plugin_deactivation() {
    flush_rewrite_rules();
}
function rareiio_plugin_setup() {
// create custom plugin settings menu
/* Get the plugin directory URI. */
	define( 'RAREIIO_POST_LIKE_PLUGIN_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
	add_action('admin_menu', 'rareiio_plugin_create_menu');
}

function rareiio_plugin_create_menu() {

	//create new top-level menu
	add_menu_page('post Like button', 'Rareiio Post Like Settings', 'administrator', __FILE__, 'rareiio_plugin_settings_page' , plugins_url('/images/dash-icon.png', __FILE__) );

	//call register settings function
	add_action( 'admin_init', 'rareiio_register_plugin_settings' );
}
/**
* Enqueue scripts and styles
*/
function rareiio_script() {    
	
	wp_enqueue_style('rareiio-style', RAREIIO_POST_LIKE_PLUGIN_URI.'css/rareiio-style.css', false, '1.0', 'all');
	wp_enqueue_script( 'rareiio-script', RAREIIO_POST_LIKE_PLUGIN_URI . 'js/rareiio-script.js', array( 'jquery' ), 0.1, true );
	wp_localize_script('like_post', 'ajax_var', array(
	    'url' => admin_url('admin-ajax.php'),
	    'nonce' => wp_create_nonce('ajax-nonce')
	));
	
}
add_action( 'wp_head', 'rareiio_script' );
/**
* Plugin Settings
*/
function rareiio_register_plugin_settings() {
	//register our settings	
	register_setting( 'rareiio-plugin-settings-group', 'rareiio_add_post_like_button' );
	register_setting( 'rareiio-plugin-settings-group', 'rareiio_beforecontent_like_button' );
	register_setting( 'rareiio-plugin-settings-group', 'rareiio_aftercontent_like_button' );
}
/**
* Plugin Page
*/
function rareiio_plugin_settings_page() {
?>
<div class="wrap">
<h2>Add Post like button on post:</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'rareiio-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'rareiio-plugin-settings-group' ); ?>
    <table class="form-table">
	<?php 	$rareiio_add_post_like_button = get_option( 'rareiio_add_post_like_button' );	?>	
	
	<tr valign="top">
        <th scope="row">Add Post like button on post:</th>
        <td><input type='checkbox' id='rareiio_add_post_like_button' name='rareiio_add_post_like_button' value='1' <?php echo checked( $rareiio_add_post_like_button, 1, false );?> /></td>
    </tr>
	
	<?php 	$rareiio_beforecontent_like_button = get_option( 'rareiio_beforecontent_like_button' );	?>	
	
	<tr valign="top">
        <th scope="row">Add Like button Before content:</th>
        <td><input type='checkbox' id='rareiio_beforecontent_like_button' name='rareiio_beforecontent_like_button' value='1' <?php echo checked( $rareiio_beforecontent_like_button, 1, false );?> /></td>
    </tr>
	
	<?php 	$rareiio_aftercontent_like_button = get_option( 'rareiio_aftercontent_like_button' );	?>	
	
	<tr valign="top">
        <th scope="row">Add Like button After content:</th>
        <td><input type='checkbox' id='rareiio_aftercontent_like_button' name='rareiio_aftercontent_like_button' value='1' <?php echo checked( $rareiio_aftercontent_like_button, 1, false );?> /></td>
    </tr>
			
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php }

$rareiio_add_post_like_button = get_option('rareiio_add_post_like_button');

if(checked( $rareiio_add_post_like_button, 1, false )){	

	add_action('wp_ajax_nopriv_rareiio_post_like', 'rareiio_post_like');
	add_action('wp_ajax_rareiio_post_like', 'rareiio_post_like');
	
	
function rareiio_post_like()
{
    // Check for nonce security
    $nonce = $_POST['nonce'];
  
    if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
        die ( 'Busted!');
     
    if(isset($_POST['post_like']))
    {
        // Retrieve user IP address
        $ip = $_SERVER['REMOTE_ADDR'];
        $post_id = $_POST['post_id'];
         
        // Get voters'IPs for the current post
        $meta_IP = get_post_meta($post_id, "voted_IP");
        $voted_IP = $meta_IP[0];
 
        if(!is_array($voted_IP))
            $voted_IP = array();         
        
        // Get votes count for the current post
        $post_like_count = get_post_meta($post_id, "votes_count", true);
 
        // Use has already voted ?
        if(!rareiio_post_like_already($post_id))
        {
            $voted_IP[$ip] = time();
 
            // Save IP and increase votes count
           update_post_meta($post_id, "voted_IP", $voted_IP);
           update_post_meta($post_id, "votes_count", ++$post_like_count);             
           
            echo $post_like_count;

        }
        else
            echo "already";
    }
    exit;
}

function rareiio_post_like_already($post_id)
{
    global $timebeforerevote;
 
    // Retrieve post votes IPs
    $meta_IP = get_post_meta($post_id, "voted_IP");
    $voted_IP = $meta_IP[0];
     
    if(!is_array($voted_IP))
        $voted_IP = array();
         
    // Retrieve current user IP
    $ip = $_SERVER['REMOTE_ADDR'];
     
    // If user has already voted
    if(in_array($ip, array_keys($voted_IP)))
    {
        $time = $voted_IP[$ip];
        $now = time();
         
        // Compare between current time and vote time
        if(round(($now - $time) / 60) > $timebeforerevote)
            return false;
             
        return true;
    }
     
    return false;
    
}
function rareiio_post_like_button_html($atts)
	{
			
		$theme_name = get_current_theme();
		$post_atts = shortcode_atts( array(
        'post_id' => '',
		), $atts );
		$post_id =  $post_atts[ 'post_id' ];
	 
		$post_like_count = get_post_meta($post_id, "votes_count", true);
	 
		$output = '<p class="post-like">';
		if(rareiio_post_like_already($post_id))
			$output .= ' <span title="'.__('I like this article', $theme_name).'" class="like alreadyvoted"></span>';
		else
			$output .= '<a href="#" data-post_id="'.$post_id.'">
						<span  title="'.__('I like this article', $theme_name).'"class="qtip like"></span>						
					</a>';
		$output .= '<span class="count">'.$post_like_count.'</span><span  class="liked_msg"></span></p>';
		 
		return $output;
	}
	
add_shortcode( 'postlike', 'rareiio_post_like_button_html' );

	function rareiio_post_like_button_add_in_content($content) {
		$rareiio_beforecontent_like_button = get_option('rareiio_beforecontent_like_button');
		$rareiio_aftercontent_like_button = get_option('rareiio_aftercontent_like_button');	
		$aftercontent = $beforecontent = '[postlike post_id="'.get_the_id().'"]';	
		
		$fullcontent = $content;
		if(checked( $rareiio_beforecontent_like_button, 1, false ))
		{	
			$fullcontent = $beforecontent . $content;
		}
		
		if(checked( $rareiio_aftercontent_like_button, 1, false ))
		{	
			$fullcontent = $content . $aftercontent;
		}
		
		if(checked( $rareiio_aftercontent_like_button, 1, false ) && checked( $rareiio_beforecontent_like_button, 1, false ) )
		{	
			$fullcontent = $beforecontent . $content . $aftercontent;
		}
		
		return $fullcontent;
	}
add_filter('the_content', 'rareiio_post_like_button_add_in_content');

}
?>
