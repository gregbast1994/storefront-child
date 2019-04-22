<?php 
defined( 'ABSPATH' ) || exit;

class User_History{
    /**
     * A class which records the ID of a page visited by a user. 
     * This data is stored as a $_SESSION and uploaded to the DB for logged in users.
     * 
     */

    public $data = array(
        'products' => array(),
        'categories' => array(),
        'searches' => array(),
        'orders' => array(),
    );

    function __construct(){
        add_action( 'init', array( $this, 'import_data' ) );
        add_action( 'template_redirect', array( $this, 'check_template' ) );
    }

    public function update_session(){
        foreach( $this->data as $key => $data ){
            $_SESSION['msp_' . $key] = $this->data[$key];
        }
    }
    
    /**
     * updates the $data array with user data from the db or session based on whether or not a user is logged in.
     * TODO: Possibly find a way to store $_SESSION on guests as well, like IP? How does woocommerce do it? Do they do it?
     */
    public function import_data(){
        foreach( $this->data as $key => $value ){
            $db_key = 'msp_' . $key;

            if( is_user_logged_in() ){
                $this->data[$key] = $this->unpackage( get_user_meta( get_current_user_id(), $db_key, true ) );

                // $this->data[$key] must be an array for the array_merge function.
                if( ! $this->data[$key] ) $this->data[$key] = array();
                if ( isset( $_SESSION[$db_key] ) ) array_merge( $this->data[$key], $_SESSION[$db_key] );
            } else {
                $this->data[$key] = ( isset( $_SESSION[$db_key] ) ) ? $_SESSION[$db_key] : array();
            }
        }
    }

    /**
     * records where the user is and updates db if nessicary.
     */
    public function check_template(){
        // if its not a product or category, we dont want none!
        if( ! is_product() && ! is_product_category() ) return;

        $category = $this->get_category();
        $this->build_item( $category );
        $this->update_session();
        $this->update_user_products_history();
    }

    public function build_item( $data ){
        global $post;
        $id = ( isset( $data->term_id ) ) ? $data->term_id : $post->ID;
        $type = ( isset( $data->term_id ) ) ? 'categories' : 'products';


        if( isset( $this->data[$type][$id] ) ){
            $arr = $this->data[$type][$id];
        } else {
            $arr = array( 
                'count' => 0,
             );
        }
        
        $arr['count']++;
        $arr['last_visit'] = time();
        $this->data[$type][$id] = $arr;

    }

    /**
     * checks if the user is logged in, if so, saves the session to the DB.
     */
    public function update_user_products_history(){
        if( is_user_logged_in() ){
            foreach( $this->data as $key => $data ){
                if( ! empty( $data ) ){
                    update_user_meta( get_current_user_id(), 'msp_' . $key, $this->package( $data ) );
                }
            }
        }
    }

    /**
     * packs up an array for saving to the DB
     * @param array $thing
     * @return string
     */
    public function package( $thing ){
        return base64_encode( serialize( $thing ) );
    }

    /**
     * unpacks a encoded serialzed string of data from the DB
     * @param string $thing
     * @return array
     */
    public function unpackage( $thing ){
        return unserialize( base64_decode( $thing ) );
    }

    /**
     * returns the $html generated by looping through the data['products'] array.
     * @param int $limit - the numbers of results we'd liked returned.
     */
    public function get_user_products_history( $limit = 20, $echo = false ){
        ob_start();

        $arr = $this->get_unique_items( $this->data['products'] );
        $arr = array_reverse( $arr );
        $limit = ( sizeof( $arr ) < $limit ) ? sizeof( $arr ) : $limit;

        echo '<div id="browsing-history-block" class="owl-carousel owl-theme">';

        for( $i = 0; $i < $limit; $i++){
            $product = wc_get_product( $arr[$i] );
            if( $product ){
                ?>
                <div class="text-center">
                    <a class="text-center mx-auto link-normal" href="<?php echo $product->get_permalink() ?>">
                        <?php echo $product->get_image(); ?>
                    </a>
                    <p class="price">$<?php echo $product->get_price(); ?></p>
                </div>
                <?php
            }
        }
        
        echo '</div><!-- #browsing-history-block -->';

        $html = ob_get_clean();

        echo $html;
        
    }

    public function get_unique_items( $arr ){
        $unique = array();
        foreach( $arr as $data ){
            if( ! empty( $data[0] ) ){
                array_push( $unique, $data[0] );
            }
        }
        

        // we use array_values becuase we dont want to preserve the keys.
        return array_values( array_unique( $unique ) );
    }

    public function get_user_categories_history(){
        return( $this->data['categories'] );
    }

    /**
    * Checks if we are in a category using the URI, if so, grab the slug of the next cat and return WP_Term
    * @return WP_Term $category
    */
    public function get_category(){
        $categories = explode( '/', $_SERVER['REQUEST_URI'] );
        if( $categories[1] = 'product-category' ){
            // store all the categories! or atleast the last...
            $category = get_terms( array(
                'slug' => $categories[2],
                'taxonomy' => 'product_cat',
            ));
            if( empty( $category ) ) return null;
        
        }
        return $category[0];
    }

}
$history = new User_History();