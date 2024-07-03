<div id="main_container">
     
    <div id="tab_container" class="tab">
        <button id="tab_ride_button"
            class="tablinks active" onclick="openTab(event, 'ride')">Ride Details</button>
        <button id="tab_expense_button"
            class="tablinks" onclick="openTab(event, 'expense')">Expense Details</button>
    <a href='<?php echo wp_login_url(); ?>' id="logout_button">Log Out</a>

    </div>
    

    <form id="custom-rides-form" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" onsubmit="return validateForm();">

        <div id="ride"  class="tabcontent">

            <div id="ride_details">
                <div id="ride_details_container">
             <div id="ride_type">
                <label class="ride_type_heading" ><strong>Ride
                        Type:</strong></label>
                <label class="ride_type_label"><input type="radio"
                        name="ride_type" onchange="handleRideTypeChange(this);" value="Uber"  class="ride_type_radio_inputs"><span>&nbsp;
                        Uber</span></label>
                <label class="ride_type_label"><input type="radio"
                        name="ride_type" onchange="handleRideTypeChange(this);" value="Careem" class="ride_type_radio_inputs"><span>&nbsp; Careem</span></label>

                 <label class="ride_type_label"><input type="radio"
                        name="ride_type" onchange="handleRideTypeChange(this);" value="Yango" class="ride_type_radio_inputs"><span>&nbsp; Yango</span></label>
                <label class="ride_type_label"><input type="radio"
                         name="ride_type" onchange="handleRideTypeChange(this);" value="Private" class="ride_type_radio_inputs"><span>&nbsp; Private</span></label>

                <label class="ride_type_label"><input type="radio"
                        name="ride_type" onchange="handleRideTypeChange(this);" value="Other" class="ride_type_radio_inputs"><span>&nbsp; Other</span></label>
             </div>
             <div id="payment_type">
                <label id="payment_type_heading"><strong>Payment
                        Type:</strong></label>
                        <label class="payment_type_label">
                 <input class="payment_type_radio_input" type="radio" name="payment_type" value="Cash">
                 <span>&nbsp; Cash</span>
               </label>
                <label class="payment_type_label"><input type="radio"
                        name="payment_type" value="Card"class="payment_type_radio_input"><span>&nbsp; Card</span></label>
                <label class="payment_type_label"><input type="radio"
                        name="payment_type" value="Uber/Careem Card"class="payment_type_radio_input"><span>&nbsp; Uber/Careem Card</span></label>
             </div>
             </div>

                <label class="ride_amount_label" for="amount"><span>Ride
                        Amount</span></label>
                <input class="ride_amount_input" type="number" name="amount" step="0.01" required>
            </div>
        </div>

        
<div id="expense" class="tabcontent">
    <div id="expense_details">
        <label class="expense_type_heading"><strong>Expense Type:</strong></label>
        <label class="expense_type_label"><input class="expense_type_radio_input" type="radio"
                name="expense_type" value="Fuel" onclick=" handleExpenseTypeChange(this);"><span>&nbsp; Fuel</span></label>
        <label class="expense_type_label"><input class="expense_type_radio_input" type="radio"
                name="expense_type" value="Oil Change" onclick=" handleExpenseTypeChange(this);"><span>&nbsp; Oil Change</span></label>
        <label class="expense_type_label"><input class="expense_type_radio_input" type="radio"
                name="expense_type" value="Wheel alignment" onclick="handleExpenseTypeChange(this);" ><span>&nbsp; Wheel alignment</span></label>

        <label class="expense_type_label">
            <input class="expense_type_radio_input" type="radio" name="expense_type" value="Other" onchange="handleExpenseTypeChange(this);">
            <span>&nbsp; Other</span>
        </label>
        <input type="text" name="other_expense_type" id="other-expense-type"  placeholder="Enter Other Expense Type">

        <label id="expense_amount_label" for="expense"><span>Expense Amount</span></label>
        <input id="expense_amount_input" type="number" name="expense" step="0.01" required>
    </div>
</div>
        <input
    type="submit"
    id="submit-button"
    value="Submit"
    >
    </form>

</div>





<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ride_type']) && isset($_POST['amount']) && isset($_POST['payment_type'])) {
        handle_ride_form_submission();
    }

    if (isset($_POST['expense_type']) && isset($_POST['expense'])) {
        handle_expense_form_submission();
    }
}

function handle_ride_form_submission() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'rides';

    $ride_type = sanitize_text_field($_POST['ride_type']);
    $payment_type = sanitize_text_field($_POST['payment_type']);
    $amount = floatval($_POST['amount']);
    $date = current_time('mysql');
    $result = $wpdb->insert(
        $table_name,
        array(
            'ride_type' => $ride_type,
            'payment_type' => $payment_type,
            'amount'    => $amount,
            'date'      => $date,
        )
    );
}

function handle_expense_form_submission() {
    global $wpdb;

    $table_name2 = $wpdb->prefix . 'expense';
    $expense_type = sanitize_text_field($_POST['expense_type']);

    // If the selected expense type is "Other", use the value from the text input
    if ($expense_type === 'Other') {
        $expense_type = sanitize_text_field($_POST['other_expense_type']);
    }

    $expense = floatval($_POST['expense']);
    $date = current_time('mysql');

    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;

    $result = $wpdb->insert(
        $table_name2,
        array(
            'user_email'  => $user_email,
            'expense_type' => $expense_type,
            'expense'      => $expense,
            'date'         => $date,
        )
    );
}

?>


</div>
