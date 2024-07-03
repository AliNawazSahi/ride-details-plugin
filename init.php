<?php
/*
    Plugin Name: My Ride
    Description: This Plugin is for listing different ride details.
    Version: 0.1
    Author: Twin Dev
*/

register_activation_hook(__FILE__, 'your_plugin_activation');

function your_plugin_activation() {
    global $wpdb;

    $table_name1 = $wpdb->prefix . 'rides';

    $charset_collate = $wpdb->get_charset_collate();

    $sql1 = "CREATE TABLE $table_name1 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ride_type varchar(100) NOT NULL,
        payment_type varchar(100) NOT NULL,
        amount decimal(10, 2) NOT NULL,
        date DATE NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";


    $table_name2 = $wpdb->prefix . 'expense';

    $sql2 = "CREATE TABLE $table_name2 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_email varchar(100) NOT NULL,
        expense_type varchar(100) NOT NULL,
        expense decimal(10, 2) NOT NULL,
        date DATE NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
}



function enqueue_custom_scripts() {
    wp_register_script('custom-scripts', plugins_url('js/front_page.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('custom-scripts');
}

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function enqueue_front_page_styles() {
    wp_enqueue_style('front-page-style', plugin_dir_url(__FILE__) . 'css/front_page.css', array(), 0.1, 'all');

    wp_enqueue_style('front-page-style-responsive', plugin_dir_url(__FILE__) . 'css/front_page_responsive.css', array('front-page-style'), 0.1, 'screen and (min-width: 435px) and (max-width: 550px)');
    wp_enqueue_style('front-page-style-responsive2', plugin_dir_url(__FILE__) . 'css/front_page_responsive2.css', array('front-page-style'), 0.1, 'screen and (max-width: 465px)');
    wp_enqueue_style('front-page-style-responsive3', plugin_dir_url(__FILE__) . 'css/front_page_responsive3.css', array('front-page-style'), 0.1, 'screen and (min-width: 550px)');
    wp_enqueue_style('front-page-style-responsive4', plugin_dir_url(__FILE__) . 'css/front_page_responsive4.css', array('front-page-style'), 0.1, 'screen and (min-width: 900px)');
}

add_action('wp_enqueue_scripts', 'enqueue_front_page_styles');



function display_ride_details_form() {
    return include 'front_page.php';
}

function insert_expense_data($expense_type, $expense_amount, $expense_date) {
    global $wpdb;

    $table_name2 = $wpdb->prefix . 'expense';

    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;

    $wpdb->insert(
        $table_name2,
        array(
            'user_email' => $user_email,
            'expense_type' => $expense_type,
            'expense' => $expense_amount,
            'date' => $expense_date,
        ),
        array('%s', '%s', '%f', '%s')
    );
}

function handle_the_expense_form_submission() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_expense'])) {
        $expense_type = sanitize_text_field($_POST['expense_type']);
        $expense_amount = floatval($_POST['expense_amount']);
        $expense_date = sanitize_text_field($_POST['expense_date']);

        insert_expense_data($expense_type, $expense_amount, $expense_date);
    }
}

function display_ride_form_shortcode() {
    ob_start();

    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;

        if (in_array('subscriber', $user_roles) || in_array('administrator', $user_roles)) {
            display_ride_details_form();
            handle_the_expense_form_submission(); 
        } else {
            wp_redirect(wp_login_url());
            exit;
        }
    } else {
        wp_redirect(wp_login_url());
        exit;
    }

    return ob_get_clean();
}

// Hook to redirect subscribers after login
function redirect_subscriber_after_login($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles) && in_array('subscriber', $user->roles)) {
        return home_url("/ride");
    }
    return $redirect_to;
}
add_filter('login_redirect', 'redirect_subscriber_after_login', 10, 3);

add_shortcode('display_ride_form', 'display_ride_form_shortcode');

function add_rides_details_menu() {
    $capability = 'read';
    add_menu_page('Rides Details', 'Rides Details', $capability, 'rides-details', 'rides_details_fun','dashicons-car');
}

function rides_details_fun() {
    include 'rides_details.php';
}

add_action('admin_menu', 'add_rides_details_menu');

function add_expense_details_menu() {
    $capability = 'read';
    add_menu_page('Expense Details', 'Expense Details', $capability, 'expense-details', 'expense_details_fun','dashicons-list-view');
}

function expense_details_fun() {
    include 'expense_details.php';
}

add_action('admin_menu', 'add_expense_details_menu');

function remove_admin_menu_items() {
    global $menu;

    $current_user = wp_get_current_user();

    // Check if the current user is 'newadmin@gmail.com'
    if ($current_user->user_email === 'newadmin@gmail.com') {
        remove_menu_page('index.php'); 
        remove_menu_page('edit.php'); 
        remove_menu_page('upload.php'); 
        remove_menu_page('edit.php?post_type=page'); 
        remove_menu_page('edit-comments.php'); 
        remove_menu_page('themes.php'); 
        remove_menu_page('plugins.php');
        remove_menu_page('tools.php'); 
        remove_menu_page('options-general.php'); 
    }
}

add_action('admin_menu', 'remove_admin_menu_items');

?>
