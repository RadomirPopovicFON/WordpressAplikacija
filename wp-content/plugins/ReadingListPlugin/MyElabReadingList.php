<?php
/*
* Plugin Name: MyElab Reading List
* Plugin URL: http://www.elab.rs
* Description: Plugin for keeping track of reading, rating books and writing reviews
* Version: 1.0
* Author: Slavisa Perisic
* Author URI: http://slavisaperisic.net
* License: GPL2
*/

/*
 * Custom Post Type Registration
 * @param book - name of the custom post type
 */
function myelab_rlist_custom_post_type() {

	$labels = array(
		'name' => 'Books',
		'singular_name' => 'post type singular name',
		'add_new' => 'Add New', 'book',
		'add_new_item' => 'Add New Book',
		'edit_item' => 'Edit Book',
		'new_item' => 'New Book',
		'all_items' => 'All Books',
		'view_item' => 'View Book',
		'search_items' => 'Search Books',
		'not_found' => 'No books found',
		'not_found_in_trash' => 'No books found in Trash',
		'parent_item_colon' => '',
		'menu_name' => 'Books'
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => false,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'has_archive' => false,
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array( 'title', 'editor', 'thumbnail')
	);

	register_post_type('book', $args);
}

add_action( 'init', 'myelab_rlist_custom_post_type' );

/*
 * Including Scripts and Styles for the plugin
 * 
 */
function myelab_rlist_styles() {
	wp_register_style("rliststyle", plugins_url('css/style.css', __FILE__), "3.0", true );
	wp_enqueue_style("rliststyle");
}

add_action( 'wp_enqueue_scripts', 'myelab_rlist_styles' );

function myelab_rlist_scripts() {
	wp_deregister_script( "jquery" );
	wp_register_script("jquery", "http://code.jquery.com/jquery-2.0.3.min.js", "1.0", true );
	wp_enqueue_script("jquery");

	wp_register_script("jquerycycle2", plugins_url('js/jquery.cycle2.min.js', __FILE__), "1.0", true );
	wp_enqueue_script("jquerycycle2");

	wp_register_script("myelabrlistscript", plugins_url('js/script.js', __FILE__), "1.0", true );
	wp_enqueue_script("myelabrlistscript");
}

add_action( 'wp_enqueue_scripts', 'myelab_rlist_scripts' );

/*
 * Shortcode Function
 *
 */
function myelab_rlist_shortcode() {

	$options = get_option( 'myelab_rlist_settings' ); 

?>

<?php if($options["myelab_rlist_gridview_enable"]) : ?>
<div id="views">
	<form method="post" ajaxurl="<?php bloginfo("url") ?>/wp-admin/admin-ajax.php">
		<button id="listview" type="button">List</button>
		<button id="gridview" type="button">Grid</button>
	</form>
</div>
<?php endif; ?>

<div id="showlistview">
	<?php

		//create WP_Query with parameters
		$query = new WP_Query( 
			array( 
				'post_type'      => 'book',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC'
			) 
		);
		
		//get the post count
		$post_count = $query->post_count;
		
		//create a condition 
		if( $post_count > 0) :
		
			// Loop
			while ($query->have_posts()) : $query->the_post();
				
				$book_author = get_post_meta( get_the_ID(), "book_author", true );
				$book_pages = get_post_meta( get_the_ID(), "book_pages", true );
				$book_rating = get_post_meta( get_the_ID(), "book_rating", true );

			?>
			
			<div class="rlist clearfix">

				<h3><?php the_title(); ?></h3>

				<div class="block-left">
					<strong>by <?php echo $book_author; ?></strong>
					<span><?php the_post_thumbnail( "full" ) ?></span>
				</div>

				<div class="block-right">
					<header>
						<span>Rating: </span>
						<div class="stars stars<?php echo $book_rating; ?>"></div>
						<i><?php echo $book_pages; ?> pages</i>
					</header>
					<article><?php echo get_the_content(); ?></article>
				</div>

			</div>

			<?php

			endwhile;
			
			// Reset query to prevent conflicts
			wp_reset_query();
			
		else :
		?>

		<h2>There are no books to show. Please add some.</h2>

		<?php

		endif;

	?>

</div>
<div id="showgridview"></div>

<?php
	
}

add_shortcode("readinglist", "myelab_rlist_shortcode");

add_action( "add_meta_boxes", "myelab_rlist_showimage_handle");

function myelab_rlist_showimage_handle() {

	add_meta_box( "myelab_rlist_show_image", "Book Options", "myelab_rlist_show_image", "book", "side" );
	//add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null )

	function myelab_rlist_show_image($post) {

		$book_author = get_post_meta( $post->ID, "book_author", true );

		?>
			<fieldset>
				<label>Book Author</label>
				<input name="book_author" type="text" value="<?php echo (isset($book_author)) ? $book_author : ""; ?>" />
			</fieldset>
		<?php
		
		$book_rating = get_post_meta( $post->ID, "book_rating", true );

		?>
			<fieldset>
				<label>Rating</label>
				<select name="book_rating">
					<option value="1" <?php selected( $book_rating, 1 ); ?>>1 (Terrible)</option>
					<option value="2" <?php selected( $book_rating, 2 ); ?>>2 (Not Impressed)</option>
					<option value="3" <?php selected( $book_rating, 3 ); ?>>3 (Mediocre)</option>
					<option value="4" <?php selected( $book_rating, 4 ); ?>>4 (Really Good)</option>
					<option value="5" <?php selected( $book_rating, 5 ); ?>>5 (Amazing)</option>
				</select>
			</fieldset>
		<?php

		$book_pages = get_post_meta( $post->ID, "book_pages", true );

		?>
			<fieldset>
				<label>Number of pages</label>
				<input name="book_pages" type="text" value="<?php echo (isset($book_pages)) ? $book_pages : ""; ?>" />
			</fieldset>
		<?php

	}

}

add_action( "save_post", function() {

	global $post;
	if(isset($post))
		$id = $post->ID;

	if(isset($_POST["book_author"])) {
		update_post_meta( 
			$id, 
			"book_author", 
			$_POST["book_author"]
		);
	}

	if(isset($_POST["book_rating"])) {
		update_post_meta( 
			$id, 
			"book_rating", 
			$_POST["book_rating"]
		);
	}

	if(isset($_POST["book_pages"])) {
		update_post_meta( 
			$id, 
			"book_pages", 
			$_POST["book_pages"]
		);
	}

});

class myelab_rlist_widget extends WP_Widget {

    function myelab_rlist_widget() {
        parent::WP_Widget(false, $name = 'Book Slider');
    }
 
    function widget($args, $instance) { 
        extract( $args );

        $title 		= apply_filters('widget_title', $instance['title'] );
        $numberofqa = $instance['numberq'];
        $effect 	= $instance['slideeffect'];
        $timeoout 	= $instance['slidetimeout'];
        
        if ($title) {
        	$options = get_option( 'myelab_rlist_settings' );
    		echo "<h3><a href='".$options["myelab_rlist_widget_link"]."'>".$title."</a></h3>";
        }
	?>

		<div class="cycle-slideshow" 
		    data-cycle-fx=<?php echo $effect; ?>
		    data-cycle-timeout=<?php echo $timeoout; ?>
		    data-cycle-caption="#alt-caption"
		    data-cycle-caption-template="{{alt}}"
		    >
		    <?php
		    
		        $allposts = new WP_Query( 
		            array( 
		                "showposts" 			=> $numberofqa,
		                "post_type" 			=> "book"
		            ) 
		        );
		        
		        while($allposts->have_posts()) : $allposts->the_post(); 

			        $thumb_id = get_post_thumbnail_id();
			        $thumb_url = wp_get_attachment_image_src($thumb_id,'thumbnail-size', true);

				    ?>
				    
				    <img src="<?php echo $thumb_url[0]; ?>" alt="<?php the_title(); ?> by <?php echo get_post_meta( get_the_ID(), "book_author", true ); ?>" >
				    
				    <?php

		        endwhile;
		        wp_reset_query();           
		    ?>
		</div>
		<!-- empty element for caption -->
		<div id="alt-caption" class="center"></div>

	<?php

        echo $after_widget;

    }
 
    function update($new_instance, $old_instance) {     
        $instance = $old_instance;  
      
        $instance['title'] = strip_tags( $new_instance['title'] );  
        $instance['numberq'] = strip_tags( $new_instance['numberq'] );  
        $instance['slideeffect'] = strip_tags( $new_instance['slideeffect'] ); 
        $instance['slidetimeout'] = strip_tags( $new_instance['slidetimeout'] ); 
      
        return $instance; 
    }
 
    function form($instance) {  
        $defaults = 
	        array( 
	        	'title' => __('Widget Title', 'myelab_rlist'),
	        	'numberq' => __('', 'myelab_rlist'),
	        );
        $instance = wp_parse_args( (array) $instance, $defaults );
    ?>

        <p>
            <input placeholder="<?php echo $instance['title']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />  
        </p>
        <p>
        	<label for="<?php echo $this->get_field_id( 'numberq' ); ?>">Number of books to show:</label>
            <input placeholder="<?php echo $instance['numberq']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'numberq' ); ?>" name="<?php echo $this->get_field_name( 'numberq' ); ?>" value="<?php echo $instance['numberq']; ?>" style="width:10%;" />  
        </p>
        <p>
        	<label for="<?php echo $this->get_field_id( 'slidetimeout' ); ?>">Slide Transition time (miliseconds):</label>
            <input placeholder="<?php echo $instance['slidetimeout']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'slidetimeout' ); ?>" name="<?php echo $this->get_field_name( 'slidetimeout' ); ?>" value="<?php echo $instance['slidetimeout']; ?>" style="width:30%;" />
        </p>
        <p>
        	<label for="<?php echo $this->get_field_id( 'slideeffect' ); ?>">Sliding Effect</label>
        	<select name="<?php echo $this->get_field_name( 'slideeffect' ); ?>">
        		<option value="fade" <?php selected( $instance['slideeffect'], "fade" ); ?>>Fade</option>
        		<option value="fadeout" <?php selected( $instance['slideeffect'], "fadeout" ); ?>>FadeOut</option>
        		<option value="scrollHorz" <?php selected( $instance['slideeffect'], "scrollHorz" ); ?>>ScrollHorz</option>
        	</select>
        </p>

        <?php
    }
 
}

add_action('widgets_init', create_function('', 'return register_widget("myelab_rlist_widget");'));

//register settings
function myelab_theme_settings_init(){
    register_setting( 'myelab_rlist_settings', 'myelab_rlist_settings' );
}

//add settings page to menu
function myelab_add_settings_page() {
	add_menu_page('Reading List Settings', 'Reading List Settings', 'manage_options', 'settings', 'reading_list_settings_page');
}

//add actions
add_action( 'admin_init', 'myelab_theme_settings_init' );
add_action( 'admin_menu', 'myelab_add_settings_page' );


//start settings page
function reading_list_settings_page() {

	if ( ! isset( $_REQUEST['updated'] ) )
		$_REQUEST['updated'] = false;

	?>

	<div>

		<h2>Reading List Plugin Settings</h2>

		<?php
		//show saved options message
		if ( false !== $_REQUEST['updated'] ) : ?>
			<div><p><strong>Options saved</strong></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">

			<?php settings_fields( 'myelab_rlist_settings' ); ?>
			<?php $options = get_option( 'myelab_rlist_settings' ); ?>

			<!-- Option 1: Widget Link -->
			<fieldset>
				<label>Link for My Reading List Widget</label><br />
				<input id="myelab_rlist_settings[custom_logo]" type="text" size="36" name="myelab_rlist_settings[myelab_rlist_widget_link]" value="<?php esc_attr_e( $options['myelab_rlist_widget_link'] ); ?>" />
			</fieldset>
			<br />
			<!-- Option 2: Enable Grid View -->
			<fieldset>
				<label for="myelab_rlist_settings[myelab_rlist_gridview_enable]">Enable Grid View</label><br />
				<input id="myelab_rlist_settings[myelab_rlist_gridview_enable]" name="myelab_rlist_settings[myelab_rlist_gridview_enable]" type="checkbox" value="1" <?php checked( '1', $options['myelab_rlist_gridview_enable'] ); ?> /><br />
				Check this box if you would like to enable the grid view on the Reading List page
			</fieldset>
			<br />
			<fieldset>
				<input class="button-primary" name="submit" id="submit" value="Save Changes" type="submit">
			</fieldset>

		</form>

	</div><!-- END wrap -->

	<?php
}
//sanitize and validate
function options_validate( $input ) {
    global $select_options, $radio_options;
    if ( ! isset( $input['option1'] ) )
        $input['option1'] = null;
    $input['option1'] = ( $input['option1'] == 1 ? 1 : 0 );
    $input['sometext'] = wp_filter_nohtml_kses( $input['sometext'] );
    if ( ! isset( $input['radioinput'] ) )
        $input['radioinput'] = null;
    if ( ! array_key_exists( $input['radioinput'], $radio_options ) )
        $input['radioinput'] = null;
    $input['sometextarea'] = wp_filter_post_kses( $input['sometextarea'] );
    return $input;
}


function myelab_rlist_getgridview() {

	$query = new WP_Query( 
		array( 
			'post_type'      => 'book',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
		) 
	);
	
	//Get post type count
	$post_count = $query->post_count;
	
	// Displays FAQ info
	if( $post_count > 0) :
	
		// Loop
		while ($query->have_posts()) : $query->the_post();
			
			$book_author = get_post_meta( get_the_ID(), "book_author", true );
			$book_pages = get_post_meta( get_the_ID(), "book_pages", true );
			$book_rating = get_post_meta( get_the_ID(), "book_rating", true );

		?>
		
		<div class="rgrid">
			<span><?php the_post_thumbnail( "full" ) ?></span>
			<h3><?php the_title(); ?></h3>
			<strong>by <?php echo $book_author; ?></strong>
		</div>

		<?php

		endwhile;
		
	endif;
	
	// Reset query to prevent conflicts
	wp_reset_query();
	die();

}  

// creating Ajax call for WordPress  
add_action( 'wp_ajax_nopriv_gridView', 'myelab_rlist_getgridview' );  
add_action( 'wp_ajax_gridView', 'myelab_rlist_getgridview' );  

add_theme_support( 'post-thumbnails', array('book') );