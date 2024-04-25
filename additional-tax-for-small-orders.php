<?php
/**
 * Plugin Name: Additional Tax for Small Orders
 * Plugin URI: http://yourwebsite.com/
 * Description: Adds an additional tax of 250kr if the cart total is less than 1000kr.
 * Version: 1.0
 * Author: Your Name
 * Author URI: http://yourwebsite.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function check_cart_total_add_tax() {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        error_log("Exiting due to admin or non-AJAX call.");
        return;
    }

    if (!isset(WC()->cart) || WC()->cart === null) {
        error_log("Cart object not available or not initialized.");
        return;
    }

    static $notice_added = false; // Static variable to track if the notice has been added


    $cart_total = WC()->cart->subtotal;
    error_log("Cart total: " . $cart_total);

    // Check if the fee has already been added
    $fees = WC()->cart->get_fees();
    $tax_added = false;
    foreach ($fees as $fee) {
        if ($fee->name === 'Additional Tax') {
            $tax_added = true;
            break;
        }
    }

    error_log("Tax added flag: " . ($tax_added ? "true" : "false"));

    if (!$tax_added && $cart_total < 1000) {
        WC()->cart->add_fee('Additional Tax', 250);
        error_log("Additional tax applied.");
    }

    if ($cart_total < 1000 && !$notice_added) {
        wc_add_notice('An additional tax of 250kr has been added to your cart because the total cart value is less than 1000kr.', 'notice');
        $notice_added = true; // Set the flag to true after adding the notice
        error_log("Notice added.");
    }

    

    error_log("Current fees: " . print_r(WC()->cart->get_fees(), true));
}

add_action('woocommerce_cart_calculate_fees', 'check_cart_total_add_tax', 10, 1);






// Check if the fee has already been added
// $fees = WC()->cart->get_fees();
// $tax_added = false;
// foreach ( $fees as $fee ) {
//     if ( $fee->name === 'Additional Tax' ) {
//         $tax_added = true;
//         break;
//     }
// }

// // If fee hasn't been added and cart total is less than 1000, add the fee
// if ( ! $tax_added && WC()->cart->subtotal < 1000 ) {
//     WC()->cart->add_fee( 'Additional Tax', 250 );
//     wc_add_notice( 'An additional tax of 250kr has been added to your cart because the total cart value is less than 1000kr.', 'notice' );
// }