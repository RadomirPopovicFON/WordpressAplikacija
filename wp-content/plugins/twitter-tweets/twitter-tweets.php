<?php
/*
 * Plugin Name: Twitter Tweets
 * Version: 1.5.5
 * Description: Display your latest tweets on WordPress blog from your Twitter account.
 * Author: WebLizar
 * Author URI: http://www.weblizar.com/
 * Plugin URI: http://www.weblizar.com/plugins/
 */
 
/**
 * Constant Values & Variables
 */
define("WEBLIZAR_TWITTER_PLUGIN_URL", plugin_dir_url(__FILE__));
define("twitter_tweets", "weblizar_twitter");

/**
 * Widget Code
 */
class WeblizarTwitter extends WP_Widget {
	function __construct() {
		parent::__construct(
		'weblizar_twitter', // Base ID
		'Twitter Tweets', // Name
		array( 'description' => __( 'Display latest tweets from your Twitter account', twitter_tweets )));
	}
    
	/*
     * Front-end display of widget.
     */
    public function widget( $args, $instance ) {
		// Outputs the content of the widget
		extract($args); // Make before_widget, etc available.
		$title = apply_filters('title', $instance['title']);		
	    echo $before_widget;
		if (!empty($title)) {	echo $before_title . $title . $after_title;	}
		$TwitterUserName    =   apply_filters( 'weblizar_twitter_user_name', $instance['TwitterUserName'] );
        $Theme              =   apply_filters( 'weblizar_twitter_theme', $instance['Theme'] );
        $Height             =   apply_filters( 'weblizar_twitter_height', $instance['Height'] );
        $Width              =   apply_filters( 'weblizar_twitter_width', $instance['Width'] );
        $LinkColor          =   apply_filters( 'weblizar_twitter_link_color', $instance['LinkColor'] );
        $ExcludeReplies     =   apply_filters( 'weblizar_twitter_exclude_replies', $instance['ExcludeReplies'] );
        $AutoExpandPhotos   =   apply_filters( 'weblizar_twitter_auto_expand_photo', $instance['AutoExpandPhotos'] );
        $TwitterWidgetId    =   apply_filters( 'weblizar_twitter_widget_id', $instance['TwitterWidgetId'] );
        ?>
        <div style="display:block;width:100%;float:left;overflow:hidden">
	    <a class="twitter-timeline" data-dnt="true" href="https://twitter.com/<?php echo $TwitterUserName; ?>"
			min-width="<?php echo $Width; ?>" 
			height="<?php echo $Height; ?>" 
			data-theme="<?php echo $Theme; ?>" 
			data-link-color="<?php echo $LinkColor; ?>" 
			data-widget-id="<?php echo $TwitterWidgetId; ?>">Twitter Tweets</a>
			<script>
				!function(d,s,id) {
					var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}
				} (document,"script","twitter-wjs");
			</script>
		</div>
        <?php
		echo $after_widget;
	}
    
	/*
     * Back-end widget form.
     */
    public function form( $instance ) {
		if ( isset( $instance[ 'TwitterUserName' ] ) ) {
			$TwitterUserName = $instance[ 'TwitterUserName' ];
		}  else  {
			$TwitterUserName = "";
		}
		if ( isset( $instance[ 'Theme' ] ) ) {
			$Theme = $instance[ 'Theme' ];
		}  else  {
			$Theme = "light";
		}
		if ( isset( $instance[ 'Height' ] ) )  {
			$Height = $instance[ 'Height' ];
		} else  {
			$Height = "450";
		}
		 
		if ( isset( $instance[ 'LinkColor' ] ) ) {
			$LinkColor = $instance[ 'LinkColor' ];
		} else  {
			$LinkColor = "#CC0000";
		}
		
		if ( isset( $instance[ 'ExcludeReplies' ] ) ) {
			$ExcludeReplies = $instance[ 'ExcludeReplies' ];
		} else {
			$ExcludeReplies = "yes";
		}
		
		if ( isset( $instance[ 'AutoExpandPhotos' ] ) ) {
			$AutoExpandPhotos = $instance[ 'AutoExpandPhotos' ];
		} else {
			$AutoExpandPhotos = "yes";
		}
		
		if ( isset( $instance[ 'TwitterWidgetId' ] ) ) {
			$TwitterWidgetId = $instance[ 'TwitterWidgetId' ];
		} else {
			$TwitterWidgetId = "";
		}
		
		if ( isset( $instance[ 'title' ] ) ) {
			 $title = $instance[ 'title' ];
		} else {
			 $title = __( 'Tweets', 'Widget Title Here' );
		} ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php _e( 'Enter Widget Title',twitter_tweets); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'TwitterUserName' ); ?>"><?php _e( 'Twitter Username' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'TwitterUserName' ); ?>" name="<?php echo $this->get_field_name( 'TwitterUserName' ); ?>" type="text" value="<?php echo esc_attr( $TwitterUserName ); ?>" placeholder="<?php _e( 'Enter Your Twitter Account Username',twitter_tweets); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'TwitterWidgetId' ); ?>"><?php _e( 'Twitter Widget Id' );?> (Required)</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'TwitterWidgetId' ); ?>" name="<?php echo $this->get_field_name( 'TwitterWidgetId' ); ?>" type="text" value="<?php echo esc_attr( $TwitterWidgetId ); ?>" placeholder="<?php _e( 'Enter Your Twitter Widget ID',twitter_tweets); ?>">
			Get Your Twitter Widget Id: <a href="https://weblizar.com/get-twitter-widget-id/" target="_blank">HERE</a></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'Theme' ); ?>"><?php _e( 'Theme' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'Theme' ); ?>" name="<?php echo $this->get_field_name( 'Theme' ); ?>">
				<option value="light" <?php if($Theme == "light") echo "selected=selected" ?>>Light</option>
				<option value="dark" <?php if($Theme == "dark") echo "selected=selected" ?>>Dark</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'Height' ); ?>"><?php _e( 'Height' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'Height' ); ?>" name="<?php echo $this->get_field_name( 'Height' ); ?>" type="text" value="<?php echo esc_attr( $Height ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'LinkColor' ); ?>"><?php _e( 'URL Link Color:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'LinkColor' ); ?>" name="<?php echo $this->get_field_name( 'LinkColor' ); ?>" type="text" value="<?php echo esc_attr( $LinkColor ); ?>">
			Find More Color Codes <a href="http://html-color-codes.info/" target="_blank">HERE</a>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'ExcludeReplies' ); ?>"><?php _e( 'Exclude Replies on Tweets' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'ExcludeReplies' ); ?>" name="<?php echo $this->get_field_name( 'ExcludeReplies' ); ?>">
				<option value="yes" <?php if($ExcludeReplies == "yes") echo "selected=selected" ?>>Yes</option>
				<option value="no" <?php if($ExcludeReplies == "no") echo "selected=selected" ?>>No</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'AutoExpandPhotos' ); ?>"><?php _e( 'Auto Expand Photos in Tweets' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'AutoExpandPhotos' ); ?>" name="<?php echo $this->get_field_name('AutoExpandPhotos' ); ?>">
				<option value="yes" <?php if($AutoExpandPhotos == "yes") echo "selected=selected" ?>>Yes</option>
				<option value="no" <?php if($AutoExpandPhotos == "no") echo "selected=selected" ?>>No</option>
			</select>
		</p>
		<?php    
	}
   /*
      Sanitize widget form values as they are saved.
      @see WP_Widget::update()
      @param array $new_instance Values just sent to be saved.
      @param array $old_instance Previously saved values from database.
      @return array Updated safe values to be saved.
    */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
		$title= sanitize_text_field( ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : 'Widget Title Here' );
		$TwitterUserName = sanitize_text_field( ( ! empty( $new_instance['TwitterUserName'] ) ) ? strip_tags( $new_instance['TwitterUserName'] ) : '' );
		$Theme = sanitize_option( 'theme', ( ! empty( $new_instance['Theme'] ) ) ? strip_tags( $new_instance['Theme'] ) : 'light' );
		$Height = sanitize_text_field( ( ! empty( $new_instance['Height'] ) ) ? strip_tags( $new_instance['Height'] ) : '450' );
		$Width = sanitize_text_field( ( ! empty( $new_instance['Width'] ) ) ? strip_tags( $new_instance['Width'] ) : '' );
		$Linkcolor = sanitize_option( ( ! empty( $new_instance['LinkColor'] ) ) ? strip_tags( $new_instance['LinkColor'] ) : '#CC0000' );
		$ExcludeReplies = sanitize_option( ( ! empty( $new_instance['ExcludeReplies'] ) ) ? strip_tags( $new_instance['ExcludeReplies'] ) : 'yes' );
		$AutoExpandPhotos = sanitize_option( ( ! empty( $new_instance['AutoExpandPhotos'] ) ) ? strip_tags( $new_instance['AutoExpandPhotos'] ) : 'yes' );
		$TwitterWidgetId = sanitize_text_field( ( ! empty( $new_instance['TwitterWidgetId'] ) ) ? strip_tags( $new_instance['TwitterWidgetId'] ) : '' ); 
	
        $instance['title'] 				= $title;
        $instance['TwitterUserName'] 	= $TwitterUserName;
        $instance['Theme'] 				= $Theme;
        $instance['Height'] 			= $Height;
        $instance['Width'] 				= $Width;
        $instance['LinkColor'] 			= $Linkcolor;
        $instance['ExcludeReplies'] 	= $ExcludeReplies;
        $instance['AutoExpandPhotos'] 	= $AutoExpandPhotos;
        $instance['TwitterWidgetId'] 	= $TwitterWidgetId;
        return $instance;
	}
} 
// end of class WeblizarTwitter

// register WeblizarTwitter widget
function WeblizarTwitterWidget() {
	register_widget( 'WeblizarTwitter' );
}
add_action( 'widgets_init', 'WeblizarTwitterWidget' );

/***
 * Shortcode Settings Menu
 */
function  Twitter_Menu()  {
	$AdminMenu = add_menu_page( 'Twitter Tweets', 'Twitter Tweets', 'administrator', 'Twitter', 'Twitter_by_weblizar_page_function', "dashicons-wordpress-alt");
}
add_action('admin_menu','Twitter_Menu');
function Twitter_by_weblizar_page_function() {
	wp_enqueue_script('jquery');
    wp_enqueue_style('weblizar-option-twiiter-style-css', WEBLIZAR_TWITTER_PLUGIN_URL .'css/weblizar-option-twiiter-style.css');
    wp_enqueue_style('recom', WEBLIZAR_TWITTER_PLUGIN_URL .'css/recom.css');
    wp_enqueue_script('weblizar-tab-js',WEBLIZAR_TWITTER_PLUGIN_URL .'js/option-js.js',array('jquery', 'media-upload', 'jquery-ui-sortable'));
	require_once("twiiter_help_body.php");
}

/***
 * Twitter Shortcode
 */
require_once("twitter-tweets_shortcode.php");
?>