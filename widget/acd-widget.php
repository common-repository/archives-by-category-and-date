<?php
/**
 * Widget construction starts here.
 *
 * @since 1.0.0
 * @package  Archives by Category and Date
 */

/**
 * Widget class for plugin.
 */
class Acdpdw_Widget extends WP_Widget {
	/**
	 * Sets up the widgets name etc.
	 */
	function __construct() {
		parent::__construct(  // Base ID of  widget.
			'acdpdw_widget',  // Widget name will appear in UI.
			__( 'Archives by Category and Date Widget', 'archives-category-date' ), // Widget description.
			array( 'description' => __( 'Archive Widget to Categorize the archives date wise as well as category wise.', 'archives-category-date' ) )
		);
	}


	/**
	 * Function retrives date, months from url and displays archives link
	 *
	 * @param array $args Consisting date as well as category name.
	 * @param array $instance Filters posts by given parameters.
	 * @since 1.0.0
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo '<h2>'.esc_attr( $title ).'</h2>'.$args['after_title']; }
		$month_slug_type = get_archives_category_date_month_slug_type();
		$cats = get_categories();  // Loop through the categries.
		foreach ( $cats as $cat ) {  // Setup the cateogory ID.
			$cat_id = $cat->term_id;
			$args = array( 'cat' => $cat_id, 'posts_per_page' => -1 );
			$query = new WP_Query( $args );  // Start the wordpress loop!
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) : $query->the_post();
					if ( 'numeric' === $month_slug_type ) {
						$month_slug = get_the_time( __( 'm', 'archives-category-date' ) );
					} else {
						$month_slug = get_the_time( __( 'M', 'archives-category-date' ) );
					}
					$catarrays[] = $cat_id;
					$arrays[] = $cat_id.'_'.get_the_time( __( 'Y', 'archives-category-date' ) ).'-'. $month_slug;
				endwhile;
			endif;
			wp_reset_postdata();

		}	// Done the foreach statement.

		$cat_arrays = array_unique( $catarrays ); // Unique array consisting all the categories ID.
		$unique_arrays = array_unique( $arrays ); // Unique strings of categories id, archive month & year.
		$val = null;
		echo '<ul>';
		foreach ( $unique_arrays as $array ) {
			$keys = explode( '_', $array, 2 );
			$keys[] = array_unique( $keys );

			if ( strpos( $array, $keys[0] ) !== false ) {
				$monyr = substr( $array, strpos( $array, '_' ) + 1 ); // Separating month & year.
				$mon = substr( $monyr, strpos( $monyr, '-' ) + 1 );
				$yr = explode( '-',$monyr );
				if ( $keys[0] !== $val ) { // Making sure category name doesnot repeat, once it is printed.
					echo '<h4>'.get_cat_name( $keys[0] ).'</h4>'; // Category name displayed.
				}
				$date = $mon.$yr[0];
				$num_mon = date( 'm', strtotime( $date ) ); // Changing month-name to its numeric value.
				$slug = get_category( $keys[0] );
				$cat_slug = $slug->slug; // Getting category slug by id.
				$uri = get_bloginfo( 'url' ) . '/category/' . $cat_slug;

				$qvars = strtolower( $mon ).'-'.$yr[0]; // $qvars consists of month and year.
				if ( isset( $qvars ) ) { ?>
					<li><a href="<?php echo esc_url( add_query_arg( 'date', $qvars,  $uri ) );?>"><?php echo $mon.'-'.$yr[0]; // Archive link. ?></a></li> 
					<?php
				}
				$val = $keys[0];
			}
		}
		echo '</ul>';
	}


	/**
	 * Function shows the widget form in the widgets section
	 *
	 * @access public
	 * @param array $instance The widget options.
	 * @since 1.0.0
	 */
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Archives' ) );
			$title = $instance['title'];
		} else {
			$title = __( 'Archives', 'archives-category-date' );
		}
		?>
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Widget Title:','archives-category-date' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            </p>
			<?php
	}


	/**
	 * Function updates the title of the widget
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $new_instance The new options.
	 * @param array $old_instance The previous options.
	 * @return $instance title of the widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}


/**
 * Function registers the widget
 *
 * @since 1.0.0
 */
function archives_category_date_load_widget() {

	register_widget( 'Acdpdw_Widget' );

}
add_action( 'widgets_init', 'archives_category_date_load_widget' );
