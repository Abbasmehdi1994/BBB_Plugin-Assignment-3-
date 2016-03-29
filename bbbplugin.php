<?php
/**
    *Plugin Name: Big Boss Burger Plugin
    * Plugin URI: http://phoenix.sheridanc.on.ca/~ccit3433/
    *Description: A single pluggin that adds a custom post type, a widget, and a shortcode
    *VersionL 1.0
    *Author: Anton Titov, Abbas Mehdi, Marcus Lee
    * Author URI: http://phoenix.sheridanc.on.ca/~ccit3433/
    
*/

// creating a function to register a custom post type

function my_bbbplugin() {
    
    // labels array for custom post types
  $labels = array(
    'name'               => _x( 'Deals', 'post type general name' ),
    'singular_name'      => _x( 'Deal', 'post type singular name' ),
    'add_new'            => _x( 'Add New', 'Topping' ),
    'add_new_item'       => __( 'Add New Deal' ),
    'edit_item'          => __( 'Edit Deal' ),
    'new_item'           => __( 'New Dear' ),
    'all_items'          => __( 'All Deals' ),
    'view_item'          => __( 'View Deal' ),
    'search_items'       => __( 'Search Deals' ),
    'not_found'          => __( 'No deals found' ),
    'not_found_in_trash' => __( 'No deals found in the Trash' ), 
    'parent_item_colon'  => '',
    'menu_name'          => 'Deals'
  );
    
    // arguments for custom post types
  $args = array(
    'labels'                => $labels,
    'description'           => 'Holds our deals and deal specific data',
    'public'                => true,
    'publicly_queryable'   => true,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'menu_position'         => 5,
    'menu_icon'             => plugins_url('thumbnail.png', __FILE__),
    'query_var'             => true,
    'rewrite'               => array( 'slug' => 'deal' ),
    'capability_type'       => 'page', 
    'has_archive'           => false,
    'hierarchical'          => true,
    'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' )
    
  );
  register_post_type( 'deal', $args ); 
}


add_action( 'init', 'my_bbbplugin' );

function my_rewrite_flush() {
    // First, we "add" the custom post type via the above written function.
    // Note: "add" is written with quotes, as CPTs don't get added to the DB,
    // They are only referenced in the post_type column with a post entry, 
    // when you add a post of this CPT.
    my_bbbplugin();

    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'my_rewrite_flush' );

// Shortcode function to use along with custom post type widget
function BBB_shortcode( $atts ) {
    ob_start(); //ob_start to ensure loop output is in place on page
 
    // define attributes and their defaults
    extract( shortcode_atts( array (
        'type' => 'post',
        'order' => 'date',
        'orderby' => 'title',
        'posts' => -1, //Number of posts shown
        'deal' => '', //Deal tag
        'category' => '', //Default category
    ), $atts ) );
 
    // define query parameters based on attributes
    $options = array(
        'post_type' => $type,
        'order' => $order,
        'orderby' => $orderby,
        'posts_per_page' => $posts,
        'burger' => $burger,
        'category_name' => $category,
    );
    $query = new WP_Query( $options ); //Search loop for posts that match with attributes
    
    // run the loop based on the query
    if ( $query->have_posts() ) { ?>
        <ul class="burger-listing">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </li>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </ul>
    <?php $myvariable = ob_get_clean();
    return $myvariable;
    }
}

add_shortcode( 'list-posts', 'BBB_shortcode' );



/**
 * Adds recent_deals widget.
 */
class recent_deals extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'recent_deals', // Base ID
			__( 'Recent Deals', 'text_domain' ), // Name
			array( 'description' => __( 'Displays the 5 most recent deals', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

        // Query goes here
        
        $query_args = array (
            'post_type'      => 'deal',
            'posts_per_page' => 5,
            'orderby'        => 'modified',
                    
        );
        
        
        // The Query
            $the_query = new WP_Query( $query_args );

            // The Loop
            if ( $the_query->have_posts() ) {
                echo '<ul>';
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    echo '<li>';
                    echo '<a href="' . get_the_permalink() . '" rel="bookmark">';
                    echo get_the_post_thumbnail();
                    echo get_the_title();
                    echo '</a>';
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                // no posts found
            }
            /* Restore original Post Data */
            wp_reset_postdata();
        
        
        
        
        
        
        
        
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Recent Deals', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 *
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class recent_deals


// register recent_deals widget
function register_recent_deals_widget() {
    register_widget( 'recent_deals' );
}
add_action( 'widgets_init', 'register_recent_deals_widget' );
