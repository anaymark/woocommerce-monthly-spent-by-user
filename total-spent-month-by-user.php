function total_spent_for_user_30days( $user_id=null ) {
    if ( empty($user_id) ){
        $user_id = get_current_user_id();
    }
    $today_year = date( 'Y' );
    $today_month = date( 'm' );
    $day = date( 'd' );
    if ($today_month == '01') {
        $month = '12';
        $year = $today_year - 1;
    } else{
        $month = $today_month - 1;
        $month = sprintf("%02d", $month);
        $year = $today_year - 1;
    }
    // ORDERS FOR LAST 30 DAYS (Time calculations)
    $now = strtotime('now');
    $gap_days = 30;
    $gap_days_in_seconds = 60*60*24*$gap_days;
    $gap_time = $now - $gap_days_in_seconds;
    $args = array(
        'post_type'   => 'shop_order',
        'post_status' => array( 'wc-completed' ),
        // all posts
        'numberposts' => -1,
        // for current user id
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'date_query' => array(
            //orders published on last 30 days
            'relation' => 'OR',
            array(
                'year' => $today_year,
                'month' => $today_month,
            ),
            array(
                'year' => $year,
                'month' => $month,
            ),
        ),
    );
    // GET ALL ORDERS
    $customer_orders = get_posts( $args );
    $count = 0;
    $total = 0;
    $no_orders_message = __('No orders this month.', 'mytheme');
    if (!empty($customer_orders)) {
        $customer_orders_date = array();
        foreach ( $customer_orders as $customer_order ){
            $customer_order_date = strtotime($customer_order->post_date);
            // PAST 30 DAYS
            if ( $customer_order_date > $gap_time ) {
                $customer_order_date;
                $order = new WC_Order( $customer_order->ID );
                $order_items = $order->get_items();
                $total += $order->get_total();
                // Going through each current customer items in the order
                foreach ( $order_items as $order_item ){
                   $count++;
                }
            }
        }
        $monthly_spent_by_user =  floatval( preg_replace( '#[^\d.]#', '', $total, $count ) );
        
        return $monthly_spent_by_user;
        
    } else {
        return $no_orders_message;         
    }
}

add_shortcode( 'spent-last-month', 'total_spent_for_user_30days' );



