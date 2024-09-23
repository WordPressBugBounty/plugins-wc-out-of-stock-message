<?php

/*
    * Stock Out message widget
*/

class WCOSM_StockOut_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() 
    {
        parent::__construct(
            'stock_out_message', /* Base ID*/
            __( 'Out Of Stock Message', 'wcosm' ), /*Name*/
            array( 'description' => __( 'Out of Stock Product Message Settings', 'wcosm' ), ) /*Args*/
        );
    }

    // Creating widget front-end
  
    public function widget( $args, $instance ) 
    {
        if (!is_product()) {
            return;
        }

        global $post, $product;
        $get_saved_val      = get_post_meta( $post->ID, '_out_of_stock_msg', true);
        $global_checkbox    = get_post_meta($post->ID, '_wcosm_use_global_note', true);
        $global_note        = get_option('woocommerce_out_of_stock_message');
        $title = apply_filters( 'widget_title', $instance['title'] );


        if( $get_saved_val && !$product->is_in_stock() && $global_checkbox != 'yes') {
            echo $args['before_widget'];
            if ( ! empty( $title ) ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }
            printf( '<div class="outofstock-message">%s</div> <!-- /.outofstock-product_message -->', $get_saved_val );
            echo $args['after_widget'];
        }

        if( $global_checkbox == 'yes' && !$product->is_in_stock() ) {
            echo $args['before_widget'];
            if ( ! empty( $title ) ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }
            printf( '<div class="outofstock-message">%s</div> <!-- /.outofstock_global-message -->', $global_note );
            echo $args['after_widget'];
        }

    }
      

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) 
    {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) 
    {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        } else { 
            $title = __( 'Stock Out', 'wcosm' );
        }

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Message Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php     
    }

} 

/*class WCOSM_StockOut_Widget*/
