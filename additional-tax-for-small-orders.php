<?php
/**
 * Plugin Name: Additional Tax for Small Orders
 * Plugin URI: https://github.com/As-Tomas/Additional-Tax-for-Small-Orders
 * Description: Adds an additional tax of 250kr if the cart total is less than 1000kr.
 * Version: 1.0
 * Author: Tomas Bance
 * Author URI: https://github.com/As-Tomas
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

    // Use WC session to ensure notice is added only once per session
    if ($cart_total < 1000 && !WC()->session->get('additional_tax_notice_added')) {
        wc_add_notice('An additional tax of 250kr has been added to your cart because the total cart value is less than 1000kr.', 'notice');
        WC()->session->set('additional_tax_notice_added', true);
        error_log("Notice added.");
    }

    error_log("Current fees: " . print_r(WC()->cart->get_fees(), true));
}

add_action('woocommerce_cart_calculate_fees', 'check_cart_total_add_tax', 10, 1);

// Reset the session variable at the start of each new cart session
add_action('woocommerce_before_cart', function() {
    WC()->session->__unset('additional_tax_notice_added');
});



