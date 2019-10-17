<?php 

defined( 'ABSPATH' ) || exit;

function get_actual_id( $product ) {
	/**
	* Checks if the object passed is a product or variation, returns appropriate ID
	* @param WC_Product|WC_Product_Variation $product
	* @return int - Product ID
	*/
	return ( $product instanceof WC_Product_Variation ) ? $this->get_variation_id($product) : $this->get_product_id($product);
}

function msp_get_products(){
	// we will pass post IDs and titles to this array
	$return = array();
 
	// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
	$search_results = new WP_Query( array( 
		's'=> $_GET['q'], // the search query
		'post_status' => 'publish', // if you don't want drafts to be returned
		'ignore_sticky_posts' => 1,
		'posts_per_page' => 50 // how much to show at once
	) );
	if( $search_results->have_posts() ) :
		while( $search_results->have_posts() ) : $search_results->the_post();	
			$title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
			$return[] = array( $search_results->post->ID, $title ); 
		endwhile;
	endif;
	echo json_encode( $return );
	die;
}

function msp_get_product_variation_data( $product_variations ){
	/** Takes in an array of Product variations, and grabs sku, price and label.
	 * @param array - List of product variations
	 * @return array - list of product variation attributes like sku, price and label
	 */
	$arr = array();
	foreach( $product_variations as $id ){
        $product = wc_get_product( $id );
        $arr[$id] = array(
            'sku' => $product->get_sku(),
            'price' => '$' . $product->get_price(),
            'attr' => wc_get_formatted_variation( $product->get_variation_attributes(), true, false, true ),
        );
	}
	return $arr;
}

function msp_get_product_image_src( $img_id, $size = 'medium' ){
    /** returns the src of a wp_attachment
	 * @param int $img_id - The ID of the image being passed.
	 * @param string $size - The size of the image returned
	 * @return string - image src
	 */
    $src = wp_get_attachment_image_src( $img_id, $size );
    return $src[0];
}

function msp_get_product_image_srcset( $img_id ){
	/**
	 * Calls msp_get_product_image_src() on a number image sizes
	 * @return array - array of srcs
	 */

	$srcset = array(
		'thumbnail' => msp_get_product_image_src( $img_id, 'woocommerce_thumbnail' ),
		'full' => msp_get_product_image_src( $img_id, 'woocommerce_single' ),
	);

    return $srcset;
}

function msp_get_product_image_src_by_product_id( $product_id ){
	/**
	 * returns the src of the WC_Product main image ID
	 * @param int $product_id
	 * @return string|null - either a src or null
	 */
    $product = wc_get_product( $product_id );
    $product_image_id = ( ! empty( $product ) ) ? $product->get_image_id() : 0;
    
    return ( ! empty( $product_image_id ) ) ? msp_get_product_image_src( $product_image_id ) : null;
}


function deslugify( $str ){
	/**
	 * Simply takes in a string, converts any _ to - and capitalizes each word in the string.
	 * @param string
	 * @return string
	 */
    return ucwords( str_replace( array('_', '-'), ' ', $str ) );
}

function msp_get_user_product_review( $p_id, $format = ARRAY_A ){
	/**
	 * returns a customers product review
	 * @param int $product_id - The id of a product.
	 */
	$comments = get_comments(array(
		'post_id' 						=> $p_id,
		'user_id' 						=> get_current_user_id(),
		'include_unapproved'  => false,
	));
	$comment = get_comment( $comments[0]->comment_ID , $format );
	return $comment;
}

function msp_customer_feedback( $order_id, $include_unapproved = false ){
	/**
	 * returns a customer store review connected to $order_id
	 * @param int $order_id - The ID of an order.
	 * @return WP_Comment
	 */
    $comments = get_comments(array(
		'post_id' 						=> 0,
		'user_id' 						=> get_current_user_id(),
		'type' 					=> 'store_review',
		'meta_key'				=> 'order_id',
		'meta_value'			=> $order_id,
		'include_unapproved'  => $include_unapproved,
	));
	if( isset( $comments[0] ) ) return( $comments[0] );
}

function msp_get_product_resources( $id ){
	/**
	 * used to get and unpack array of product resource links stored in DB.
	 * @return array
	 */
	return MSP::unpackage( get_post_meta( $id, '_msp_resources', true ) );
}

function msp_get_product_videos( $id ){
	/**
	 * used to get and unpack array of product video links stored in DB.
	 * @return array
	 */
	$arr = MSP::unpackage( get_post_meta( $id, '_msp_product_videos', true ) ); 
	return ( ! empty( $arr ) ) ? $arr : array();
}

function make_modal_btn( $args = array() ){
	/**
	 * A simple helper function used to properly format a button to work in conjunction with dynamic modals (/js/modal.js).
	 * @param array - $args - An array of arguments
	 * @return string - the HTML output of the button.
	 */
	$a_text = '<a data-toggle="modal" href="#msp_modal" data-title="%s" data-model="%s" data-action="%s" data-id="%d" class="%s">%s</a>';
	$button_text = '<button data-toggle="modal" data-target="#msp_modal" data-title="%s" data-model="%s" data-action="%s" data-id="%d" class="%s">%s</button>';
	$defaults = array(
		'type'	 => 'a',
		'class'	 => '',
		'text'   => 'text',
		'title'  => 'title',
		'model'  => '',
		'action' => '',
		'id'		 => '',
	);
	$args = wp_parse_args( $args, $defaults );

	$base_html = ( $args['type'] === 'a' ) ? $a_text : $button_text;
	
	echo sprintf( $base_html, $args['title'], $args['model'], $args['action'], $args['id'], $args['class'], $args['text'] );
}

function msp_get_product_pool( $product ){
	/**
	 * Checks whether the product has children ( variations ).
	 * @param WC_Product - $product
	 * @return array
	 */
    return ( $product->get_children() ) ? $product->get_children() : array( $product->get_id() );
}

function msp_get_product_metadata( $product_ids ){
	/**
	 * loops through an array where the key is the label and value is the meta_key
	 * @param array $product_ids - An array of ids.
	 * @return array $data_sets - An array of key => value pairs.
	 */
    $data_sets = array( 'mpn' => '_sku', 'gtin' => '_woocommerce_gpf_data' );
    foreach( $data_sets as $label => $meta_key ){
        $str = '';
        foreach( $product_ids as $id ){
            $product = wc_get_product( $id );
			$data = get_post_meta( $id, $meta_key, true );
			// if data is an array, we set $data to equal the string we want; specified by the $label attribute.
            if( is_array( $data ) && isset( $data[$label] ) ){
				$data = $data[$label];
			}

			if( ! empty( $data ) ) $str .= '<a href="'. $product->get_permalink() .'">'. $data .'</a>, ';
        }
        $data_sets[$label] = $str;
	}
    return $data_sets;
}

function msp_product_additional_information_html( $inner_html ){
	/**
	 * takes in an array of key : value pairs and displays them as a table row
	 * @param array - $inner_html - an array of key value pairs
	 */
    if( empty( $inner_html ) ) return;
    echo '<table>';
	foreach( $inner_html as $label => $value ) : ?>
		<?php if( ! empty( $value ) ) : ?>
			<tr class="woocommerce-product-attributes-item">
				<th class="woocommerce-product-attributes-item__label"><?php echo ucfirst($label); ?></th>
				<td class="woocommerce-product-attributes-item__value"><?php echo rtrim($value, ', ') ?></td>
			</tr>
		<?php endif; ?>
    <?php endforeach;
    echo '</table>';
}


function msp_get_current_category(){
	/**
	* Checks if we are in a category using the URI, if so, grab the slug of the next cat and return WP_Term
	* @return WP_Term $category
	*/
	global $wp_query;
	return $wp_query->get_queried_object();
}

function msp_get_category_children( $category = '' ){
	/**
	* Used to get the children of a product category
	* @return WP_Term $children - The children taxonomys of a product category
	*/
	if( ! is_product_category() && empty( $category ) ) return;
	$term = ( empty( $category ) ) ? get_queried_object() : get_term( $category );

	$children = get_terms( $term->taxonomy, array(
		'parent'    => $term->term_id,
		'hide_empty' => false
	));

	return $children;
}

function msp_get_question_count(){
    $questions = get_comments( array(
        'post_id' => get_the_ID(),
        'type' => 'product_question'
    ) );

    return sizeof( $questions );
}

function msp_get_customers_who_purchased_product( $product_id ){
    global $wpdb;
    $order_item = $wpdb->prefix . 'woocommerce_order_items';
    $order_item_meta = $wpdb->prefix . 'woocommerce_order_itemmeta';

    $sql = "SELECT DISTINCT u.id, u.display_name, u.user_email
            FROM $wpdb->users u, $wpdb->posts p, $order_item i, $order_item_meta meta
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND p.ID = i.order_id
            AND i.order_item_type = 'line_item'
            AND i.order_item_id = meta.order_item_id
            AND meta.meta_key = '_product_id'
            AND meta.meta_value = $product_id";
            
    return $wpdb->get_results( $sql );
}


function create_qty_breaks( $id, $price ){
	/**
	* Static rules for quickly spreading bulk discount around
	* @param int $id - Product/variation ID
	* @param string $price - regular price of the product
	*/
	if( 0 < $price && $price <= 7.5 ){
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 24 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 48 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 72 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	} elseif( 7.5 < $price && $price <= 15 ){
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 12 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 24 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 36 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	} elseif( 15 < $price && $price <= 60 ){
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 4 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 12 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	} elseif( $price >= 60 ){
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 3 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 6 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 9 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	} else {
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 12 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 24 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 36 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	}
  }

  function msp_add_category_images(){
	  /**
	   * Gets children of current category, then displays a slider for easy nav
	   */
    $categories = msp_get_category_children();
    msp_get_category_slider( $categories );
}

function msp_get_departments_silder(){
	/**
	 * Gets top-level categories, then displays a slider for easy navigation
	 */
    $categories = get_categories( array( 'taxonomy' => 'product_cat',
                                         'orderby' => 'name',
                                         'parent' => 0 ) );
    if( empty( $categories ) ) return;
    msp_get_category_slider( $categories, 'Shop by department' );
}

function msp_get_random_slider(){
	/**
	 * Gets children of current category, then displays a slider of products for that category
	 */
    $categories = get_categories( array( 'taxonomy' => 'product_cat',
                                         'orderby' => 'name',
                                         'parent' => 0 ) );
    $category = $categories[ rand( 0, sizeof( $categories ) - 1 ) ];
    $products = wc_get_products( array(
        'limit'    => 10,
        'category' => array( $category->slug ),
    ) );
    msp_get_products_slider( $products, $category->name );
}

function msp_get_featured_products_silder(){
	/**
	 * Gets featured products, and puts them into slider.
	 */
    $featured_products = wc_get_products( array(
        'limit'    => 10,
        'orderby'  => 'rand',
        'featured' => true
    ) );
    if( empty( $featured_products ) ) return;
    msp_get_products_slider( $featured_products, 'Essential PPE' );
}