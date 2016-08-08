<?php
/**
 * Shortcode For twitter tweet
 */ 
add_shortcode("TWTR", "twitter_tweet_shortcode");
function twitter_tweet_shortcode() {
	ob_start();
	$twitterSettings = unserialize(get_option("ali_twitter_shortcode"));
	
	if( isset($twitterSettings[ 'TwitterUserName' ] ) )  {
		$TwitterUserName = $twitterSettings[ 'TwitterUserName' ];
	} else $TwitterUserName = "";
	
	$Theme = "light";
	if (isset($twitterSettings[ 'Theme' ] ) ) {
		$Theme = $twitterSettings[ 'Theme' ];
	}
	
	$Height = "450";
	if ( isset($twitterSettings[ 'Height' ] ) ) {
		$Height = $twitterSettings[ 'Height' ];
	}
	
	$Width = "";
	if (isset($twitterSettings[ 'Width' ] ) ) {
		$Width = $twitterSettings[ 'Width' ];
	}
	
	$LinkColor = "#CC0000";
	if (isset( $twitterSettings[ 'LinkColor' ] ) )  {
		$LinkColor = $twitterSettings[ 'LinkColor' ];
	}
	
	$ExcludeReplies = "yes";
	if (isset( $twitterSettings[ 'ExcludeReplies' ] ) )  {
		$ExcludeReplies = $twitterSettings['ExcludeReplies' ];
	}
	
	$AutoExpandPhotos = "yes";
	if (isset( $twitterSettings[ 'AutoExpandPhotos' ] ) ) {
		$AutoExpandPhotos = $twitterSettings[ 'AutoExpandPhotos' ];
	}
	
	if (isset($twitterSettings[ 'TwitterWidgetId' ] ) ) {
		$TwitterWidgetId = $twitterSettings[ 'TwitterWidgetId' ];
	} else {
		$TwitterWidgetId = "";
	}
	
	$title = __( 'Widget Title Here', 'twitter_tweets' );
	if(isset($twitterSettings[ 'title' ] ) ) {
		$title = $twitterSettings[ 'title' ];
	}
	?>
	<div style="display:block;width:100%;float:left;overflow:hidden">
		<a class="twitter-timeline" data-dnt="true" href="https://twitter.com/<?php echo esc_attr($TwitterUserName); ?>" 
		min-width="<?php echo esc_attr($Width); ?>" 
		height="<?php echo esc_attr($Height); ?>" 
		data-theme="<?php echo esc_attr($Theme); ?>" 
		data-link-color="<?php echo esc_attr($LinkColor); ?>" 
		data-widget-id="<?php echo esc_attr($TwitterWidgetId); ?>">Twitter Tweets</a>
		<script>
			!function(d,s,id) {
				var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}
			} (document,"script","twitter-wjs");
		</script>
	</div>
	<?php
	return ob_get_clean();
}?>