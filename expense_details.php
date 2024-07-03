<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Custom_Expense_List_Table extends WP_List_Table {

    function __construct() {
        parent::__construct(array(
            'singular' => 'expense',
            'plural'   => 'expenses',
            'ajax'     => false
        ));
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'expense_type':
            case 'expense':
            case 'date':
            case 'user_email':
                return $item->$column_name;
            default:
                return print_r($item, true);
        }
    }

    function get_columns() {
        $columns = array(
            'expense_type' => 'Expense Type',
            'expense'    => 'Expense',
            'user_email'  => 'Users',
            'date'      => 'Date'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'expense_type'      => array('expense_type', true), 
            'expense'      => array('expense', true) ,
            'user_email'      => array('user_email', true) ,
            'date'      => array('date', true) 
        );
        return $sortable_columns;
    }

   
    function prepare_items() {
        global $wpdb;
        
        $table_name2 = $wpdb->prefix . 'expense';
        $per_page = 10;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name2");
        
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
        $user_email = isset($_REQUEST['user_email']) ? sanitize_text_field($_REQUEST['user_email']) : "";
        $start_date = isset($_REQUEST['start_date']) ? sanitize_text_field($_REQUEST['start_date']) : "";
        $end_date = isset($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['end_date']) : "";
        
        $where_clause = '';
        
        if (!empty($search_term)) {
            $where_clause .= $wpdb->prepare(" AND (date LIKE '%%%s%%' OR user_email LIKE '%%%s%%')", $search_term, $search_term);
        }
        
        if (!empty($user_email)) {
            $where_clause .= $wpdb->prepare(" AND user_email = %s", $user_email);
        }
    
        if (!empty($start_date) && !empty($end_date)) {
            $where_clause .= $wpdb->prepare(" AND date BETWEEN %s AND %s", $start_date, $end_date);
        }
        
        $this->items = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name2 WHERE 1=1 $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset)
        );
    }
}    

$custom_expense_table = new Custom_Expense_List_Table();
$custom_expense_table->prepare_items();

?>

<div class="wrap">
    <h2 style="margin-bottom:10px;">Expense List</h2>

    <form method="post" action="<?php echo admin_url('admin.php?page=expense-details'); ?>">
    <!-- Existing date input fields -->
    <label for="start_date">From:</label>
    <input type="date" id="start_date" name="start_date">

    <label for="end_date">To:</label>
    <input type="date" id="end_date" name="end_date">

    <label for="user_email">By User:</label>
    <select id="user_email" name="user_email">
        <option value="">All Users</option>
        <?php
        $users = get_users();
        foreach ($users as $user) {
            echo "<option value='{$user->user_email}'>{$user->user_email}</option>";
        }
        ?>
    </select>

    <input type="submit" name="submit" class="button" style="font-size: 14.5px;" value="Filter">
</form>


    <?php $custom_expense_table->display(); ?>
</div>


<!-- Another class for the Total of Expense -->

<?php
class Custom_Expense_Total extends WP_List_Table {

private $total_amounts = array();

function __construct() {
    parent::__construct(array(
        'singular' => 'expense',
        'plural'   => 'expenses',
        'ajax'     => false
    ));
}

function get_columns() {
    $columns = array(
        'expense_type' => 'Expense Type',
        'expense'    => 'Expense',
        'date'      => 'Date'
    );
    return $columns;
}

function prepare_items() {
    global $wpdb;

    $table_name2 = $wpdb->prefix . 'expense';

    $expenses = $wpdb->get_results("SELECT * FROM $table_name2");

    $this->total_amounts = array();

    foreach ($expenses as $expense) {
        $expense_type = $expense->expense_type;
        if (!isset($this->total_amounts[$expense_type])) {
            $this->total_amounts[$expense_type] = 0;
        }
        $this->total_amounts[$expense_type] += $expense->expense;
    }

    $this->items = $expenses;
}

function column_default($item, $column_name) {
    switch ($column_name) {
        case 'expense_type':
        case 'expense':
            return $item->$column_name;
        default:
            return print_r($item, true);
    }
}

function display_total_amounts() {
    ?>
    <div class="total-amounts">
        <h3>Total Expense</h3>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Expense Type</th>
                    <th>Total Expense</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($this->total_amounts as $expense_type => $total_amount) {
                    echo "<tr>";
                    echo "<td>{$expense_type}</td>";
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

$custom_expense_total = new Custom_Expense_Total();
$custom_expense_total->prepare_items();

?>

<div class="wrap">
<?php $custom_expense_total->display(); ?>

<?php $custom_expense_total->display_total_amounts(); ?>
</div>