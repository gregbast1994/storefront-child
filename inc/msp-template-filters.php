<?php

defined( 'ABSPATH' ) || exit;


function msp_add_bootstrap_cols_to_product(){
    /**
     * adds bootstrap grid classes to all category and shop pages.
     */
    return ( is_product_category() || is_shop() ) ? array('col-6', 'col-sm-3', 'col-xl-2') : array();
}
add_filter('post_class', 'msp_add_bootstrap_cols_to_product', 30, 3);

function msp_woocommerce_product_loop_start(){
    /**
     * opens up any category and shop pages as a boostrap row.
     */
    return '<div id="msp-archive" class="row">';
}
add_filter('woocommerce_product_loop_start', 'msp_woocommerce_product_loop_start', 999);

function msp_woocommerce_product_loop_end(){
    /**
     * closes up any category and shop pages
     */
    return '</div>';
}
add_filter('woocommerce_product_loop_end', 'msp_woocommerce_product_loop_end', 999);

function msp_products_per_page(){
    /**
     * changes the default per_page value;
     */
    return 30;
}
add_filter('loop_shop_per_page', 'msp_products_per_page', 999);


add_filter( 'wc_add_to_cart_message_html', 'remove_add_to_cart_message' );
function remove_add_to_cart_message() {
    return;
}

add_filter( 'the_content', 'msp_maybe_add_tab_info' );
function msp_maybe_add_tab_info( $content ){
    /**
     * This filter grabs any additional information from yikes_product_tabs plugin.
     * I stopped using plugin.
     */

    if( is_product() ){
        global $product;
    
        $plugin_tabs = get_post_meta( $product->get_id(), 'yikes_woo_products_tabs' );

        if( ! empty( $plugin_tabs ) ){
            foreach( $plugin_tabs[0] as $tab ){
                $content .= '<h4 class="mb-2">'. $tab['title'] .'</h4>' . $tab['content'];
            }
        }
    }
    
    return $content;
}

add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'msp_add_form_control_to_select_boxes', 100 );
// Add class .form-control to default class for select tags
function msp_add_form_control_to_select_boxes( $args ){
    $args['class'] = 'form-control';
    return $args;
}

// add_filter( 'woocommerce_variable_price_html', 'msp_format_sale_price', 10, 3 );
function msp_format_sale_price( $price, $reg, $sale ){

    // only on single product pages
    if( ! is_product() ) return $price;

    
    if( ! is_numeric( $reg ) || ! is_numeric( $sale ) ){
        //strip down to just number for math
        $sale = substr(strip_tags($sale), 5);
        $reg = substr(strip_tags($reg), 5);
    }
    
    $price_messages = msp_get_price_messages( $sale );
    $savings = (float)$reg - (float)$sale;
    $percentage = round( ( (float)$reg - (float)$sale ) / (float)$reg * 100 ).'%';
    

    return  sprintf('<table class="msp-price"><tr><td>Was:</td><td><del>%s</del></td></tr><tr><td>With Deal:</td><td><ins>%s</ins> %s</td></tr><tr><td>You Save:</td><td> %s (%s)</td></tr></table>', 
            is_numeric($reg) ? wc_price( $reg ) : $reg, 
            is_numeric($sale) ? wc_price( $sale ) : $sale, 
            $price_messages, 
            wc_price($savings),
            $percentage
            );
}

// add_filter( 'woocommerce_product_price_class', 'msp_add_product_price_class' );
function msp_add_product_price_class( $class ){
    /**
     * Add custom css for easy replacement of active price div
     */
    $class .= ' msp-price';
    return $class;
}

// add_filter( 'woocommerce_get_price_html', 'msp_add_price_messages_to_price_html', 10, 2 );
function msp_add_price_messages_to_price_html( $price, $product ){
    $_price = floatval(substr(strip_tags($price), 5));
    $messages = msp_get_price_messages( $_price );
    return $price . $messages;
}
