<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Custom_Rides_List_Table extends WP_List_Table {

    function __construct() {
        parent::__construct(array(
            'singular' => 'ride',
            'plural'   => 'rides',
            'ajax'     => false
        ));
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'ride_type':
            case 'amount':
            case 'payment_type':
            case 'date':
                return $item->$column_name;
            default:
                return print_r($item, true);
        }
    }

    function get_columns() {
        $columns = array(
            'ride_type' => 'Ride Type',
            'amount'    => 'Amount',
            'payment_type'    => 'Payment Type',
            'date'      => 'Date'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'ride_type' => array('ride_type', false),
            'amount'    => array('amount', false),
            'payment_type'=> array('payment_type', false),
            'date'      => array('date', true) 
        );
        return $sortable_columns;
    }

    
    function prepare_items() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'rides';
        $per_page = 10;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));

        $paged = $this->get_pagenum();
        $offset = ($paged - 1) * $per_page;

        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'date';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        $search_term = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : "";

        $where_clause = '';
        
        // Get the values from the date inputs
        $start_date = isset($_REQUEST['start_date']) ? sanitize_text_field($_REQUEST['start_date']) : "";
        $end_date = isset($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['end_date']) : "";

        if (!empty($start_date) && !empty($end_date)) {
            // Use BETWEEN for date range search
            $where_clause .= $wpdb->prepare(" AND date BETWEEN %s AND %s", $start_date, $end_date);
        } elseif (!empty($search_term)) {
            $where_clause .= $wpdb->prepare(" AND date LIKE '%%%s%%'", $search_term);
        }

        $this->items = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE 1=1 $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset)
        );
    }
    
}
$custom_rides_table = new Custom_Rides_List_Table();
$custom_rides_table->prepare_items();

?>

<div class="wrap">
    <h2 style="margin-bottom:10px;">Rides List</h2>

    <form method="post" action="<?php echo admin_url('admin.php?page=rides-details'); ?>">

    <!-- Add date input fields -->
    <label for="start_date">From:</label>
    <input type="date" id="start_date" name="start_date">

    <label for="end_date">To:</label>
    <input type="date" id="end_date" name="end_date">

    <input type="submit" name="submit" class="button" value="Filter">
</form>


    <?php $custom_rides_table->display(); ?>
</div>


<!-- Another class for the Total of Rides -->

<?php
class Custom_Rides_Total extends WP_List_Table {

private $total_amounts = array();

function __construct() {
    parent::__construct(array(
        'singular' => 'ride',
        'plural'   => 'rides',
        'ajax'     => false
    ));
}

function get_columns() {
    $columns = array(
        'ride_type' => 'Ride Type',
        'amount'    => 'Total Amount',
        'ride'      => 'Particular Ride'
    );
    return $columns;
}

function prepare_items() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'rides';

    $rides = $wpdb->get_results("SELECT * FROM $table_name");

    // Calculate and store total amount for each ride type
    $this->total_amounts = array();

    foreach ($rides as $ride) {
        $ride_type = $ride->ride_type;
        if (!isset($this->total_amounts[$ride_type])) {
            $this->total_amounts[$ride_type] = 0;
        }
        $this->total_amounts[$ride_type] += $ride->amount;
    }

    $this->items = $rides;
}

function column_default($item, $column_name) {
    switch ($column_name) {
        case 'ride_type':
        case 'amount':
        case 'ride':
            return $item->$column_name;
        default:
            return print_r($item, true);
    }
}

function display_total_amounts() {
    ?>
    <div class="total-amounts">
        <h3>Total Amounts</h3>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Ride Type</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($this->total_amounts as $ride_type => $total_amount) {
                    echo "<tr>";
                    echo "<td>{$ride_type}</td>";
                    echo "<td>{$total_amount}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
}

$custom_rides_total = new Custom_Rides_Total();
$custom_rides_total->prepare_items();

?>

<div class="wrap">

<?php $custom_rides_total->display(); ?>

<?php $custom_rides_total->display_total_amounts(); ?>
</div>