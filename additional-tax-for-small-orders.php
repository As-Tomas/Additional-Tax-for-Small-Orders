<?php

/**
 * Plugin Name: Additional Tax for Small Orders
 * Plugin URI: https://github.com/As-Tomas/Additional-Tax-for-Small-Orders
 * Description: Adds an additional tax of 250kr if the cart total is less than 1000kr.
 * Version: 1.0
 * Author: Tomas Bance
 * Author URI: https://github.com/As-Tomas
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function check_cart_total_add_tax()
{
    if (is_admin() && !defined('DOING_AJAX')) {
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
        if ($fee->name === 'Småordretillegg') {
            $tax_added = true;
            break;
        }
    }

    error_log("Tax added flag: " . ($tax_added ? "true" : "false"));

    if (!$tax_added && $cart_total < 1000) {
        WC()->cart->add_fee('Småordretillegg', 250, true); // true for ensure the fee is taxable (VAT is added to the fee)
        error_log("Additional tax applied.");
    }    
}

add_action('woocommerce_before_cart', 'check_cart_total_add_tax');


// Add notice in a visual-related hook
add_action('woocommerce_before_cart', 'add_custom_notice_to_cart');

function add_custom_notice_to_cart() {
    if (!isset(WC()->cart)) {
        return;
    }

    $cart_total = WC()->cart->subtotal;

    if ($cart_total < 1000 && !WC()->session->get('additional_tax_notice_added')) {
        wc_add_notice('Handle for 1000 kr eller mer for å slippe småordregebyret.', 'notice');
        WC()->session->set('additional_tax_notice_added', true);
    }
}