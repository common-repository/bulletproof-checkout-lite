<?php

if (!function_exists('bulletproof_payment_gateway_plugin_notice')) {
    function bulletproof_payment_gateway_plugin_notice($msg)
    {
        echo '<div class="error"><p>' . $msg . '</p></div>';
    }
}

// Attach extra information to the Thank you page
add_action('woocommerce_thankyou',  'bulletproof_add_content_thankyou');
if (!function_exists('bulletproof_add_content_thankyou')) {

    function bulletproof_display_row_information_thankyou($title, $value)
    {

        if ($value != "") {
            $response = "<tr>";
            $response .= "<td><strong>" . $title . "</strong></td>";
            $response .= "<td>" . $value . "</td>";
            $response .= "</tr>";
            return $response;
        } else {
            return "";
        }
    }

    /**
     * Add extra information received to the WooCommerce Thank you page
     */

    function bulletproof_add_content_thankyou()
    {
        $transactionid = "";
        $subscriptionid = "";
        $last4 = "";
        $cctype = "";
        $gateway = "";
        $auth_approved = "";
        $allowed_cc_types = array("VISA", "MASTER", "AMEX", "DISCOVER", "MAESTRO", "JCB");

        if ((isset($_GET['transactionid'])) && ($_GET['transactionid'] != "")) {
            $transactionid = sanitize_text_field($_GET['transactionid']);
            if (!is_numeric($transactionid)) {
                $transactionid = "";
            }
        }
        if ((isset($_GET['subscriptionid'])) && ($_GET['subscriptionid'] != "")) {
            $subscriptionid = sanitize_text_field($_GET['subscriptionid']);
            if (!is_numeric($subscriptionid)) {
                $subscriptionid = "";
            }
        }
        if ((isset($_GET['last4'])) && ($_GET['last4'] != "")) {
            $last4 = sanitize_text_field($_GET['last4']);
            if (!is_numeric($last4)) {
                $last4 = "";
            }
        }
        if ((isset($_GET['cctype'])) && ($_GET['cctype'] != "")) {
            $cctype = strtoupper(sanitize_text_field($_GET['cctype']));
            // only are allowed specific cc types
            if (!in_array($cctype, $allowed_cc_types)) {
                $cctype = "";
            }
            if ($cctype == "MASTER") {
                $cctype = "MASTER CARD";
            } else {
                if ($cctype == "AMEX") {
                    $cctype = "AMERICAN EXPRESS";
                }
            }
        }
        if ((isset($_GET['gateway'])) && ($_GET['gateway'] != "")) {
            $gateway = strtoupper(sanitize_text_field($_GET['gateway']));
            if (($gateway != "BP") && ($gateway != "PAYFAC") && ($gateway != "PAYFAC_FLEX")) {
                $gateway = "";
            }
        }
        if ((isset($_GET['3ds_approved'])) && ($_GET['3ds_approved'] != "")) {
            $auth_approved = strtolower(sanitize_text_field($_GET['3ds_approved']));
            if (($auth_approved != "yes") && ($auth_approved != "no")) {
                $auth_approved = "";
            }
        }

        if ($gateway == "BP") {
            echo "<section id='bulletproof_information' name='bulletproof_information'>";
            echo "<table class='woocommerce-table' id='table_bulletproof_information' name='table_bulletproof_information'>";
            echo "<tbody>";

            echo bulletproof_display_row_information_thankyou("Transaction ID:", $transactionid);
            echo bulletproof_display_row_information_thankyou("Subscription ID:", $subscriptionid);
            if (($cctype != "") && ($last4 != "")) {
                echo bulletproof_display_row_information_thankyou("Credit Card:", $cctype . " XXXXXXXXXXXX" . $last4);
            }
            if ($auth_approved == "yes") {
                echo bulletproof_display_row_information_thankyou("Credit Card 3DS Authentication:", "This transaction has received 3DS authentication");
            }

            echo "</tbody>";
            echo "</table>";
            echo "</section>";
        }
    }
}




/**
 * Function to display API error notices.
 * This function displays any error notices generated during the settings save process.
 */
if (!function_exists('bulletproof_display_error_bulletproof_lite_gateway_api_refund_error')) {
    function bulletproof_display_error_bulletproof_lite_gateway_api_refund_error()
    {
        // Display error notices
        $message = get_transient('bulletproof_lite_gateway_api_refund_error');
        if ($message) {
?>
            <div class="notice notice-error is-dismissible">
                <p><?php esc_html($message); ?></p>
            </div>
<?php
            // Delete the transient to avoid displaying the message again
            delete_transient('bulletproof_lite_gateway_api_refund_error');
        }
    }
}

// Handle incoming messages to the checkout page
add_action('woocommerce_before_checkout_form', 'bulletproof_payment_gateway_checkout_msg');
if (!function_exists('bulletproof_payment_gateway_checkout_msg')) {
    function bulletproof_payment_gateway_checkout_msg()
    {
        if ((isset($_GET['msg'])) && ($_GET['msg'] != "")) {
            if (function_exists("wc_add_notice")) {
                wc_add_notice(__(sanitize_text_field(urldecode($_GET['msg'])), 'bulletproof-checkout-lite'), 'error');
            }
        }
    }
}


