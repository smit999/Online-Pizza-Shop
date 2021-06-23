<?php
function get_preparing_orders($db) {
    $query = 'SELECT * FROM pizza_orders where status=\'Preparing\'';
    $statement = $db->prepare($query);
    $statement->execute();
    $orders = $statement->fetchAll();
    $statement->closeCursor();
    add_username_to_orders($db, $orders);
    return $orders;  
}
function get_baked_orders($db) {
    $query = 'SELECT * FROM pizza_orders where status=\'Baked\'';
    $statement = $db->prepare($query);
    $statement->execute(); 
    $orders = $statement->fetchAll();
    $statement->closeCursor(); 
    add_username_to_orders($db, $orders);
    return $orders;  
}

function get_orders_for_day($db, $day) {
    $query = 'SELECT * FROM pizza_orders where day=:day';
    $statement = $db->prepare($query);
    $statement->bindValue(':day',$day);
    $statement->execute();
    $orders = $statement->fetchAll();
    $statement->closeCursor();   
    add_username_to_orders($db, $orders);
    return $orders;
}
function finish_orders_for_day($db, $day) {
    $query = 'UPDATE pizza_orders SET status=\'Finished\' WHERE day=:current_day';
    $statement = $db->prepare($query);
    $statement->bindValue(':current_day',$day);
    $statement->execute();
    $statement->closeCursor(); 
}

function add_username_to_orders($db, &$orders) {
     //use index loop when updating elements like this
    for ($i=0; $i<count($orders);$i++) {
        $username = get_username($db, $orders[$i]['user_id']);
        $orders[$i]['username'] = $username;
    }
}
function get_preparing_orders_by_user($db, $user_id) {
    $query = 'SELECT * FROM pizza_orders where status=\'Preparing\' and user_id =:user_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id',$user_id);
    $statement->execute();
    $orders = $statement->fetchAll();
    $statement->closeCursor(); 
    $orders = add_toppings_to_orders($db, $orders);
    // error_log('get_preparing_orders_by_user_id '. print_r($orders, true) );
    return $orders;    
}

function get_baked_orders_by_user($db, $user_id) {
    $query = 'SELECT * FROM pizza_orders where status=\'Baked\' and user_id=:user_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id',$user_id);
    $statement->execute();
    $orders = $statement->fetchAll();
    $statement->closeCursor(); 
    $orders1 = add_toppings_to_orders($db, $orders);     
    return $orders1;    
}
// helper to above two functions
function add_toppings_to_orders($db, $orders) {
      for ($i=0; $i<count($orders);$i++) {
        $toppings = get_order_toppings($db, $orders[$i]['id']);
        $orders[$i]['toppings'] = $toppings; // add toppings to order 
    } 
    return $orders;
}
// helper to above function
function get_order_toppings($db, $order_id) {
    $query = 'select topping from order_topping '
            . 'where order_id=:order_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':order_id',$order_id);
    $statement->execute();
    $toppings = $statement->fetchAll();
    $statement->closeCursor();
    // error_log('toppings '. print_r($toppings, true) );
    return $toppings;
}
function change_to_baked($db, $id) {
    $query = 'UPDATE pizza_orders SET status=\'Baked\' WHERE status=\'Preparing\' and id=:id';
    $statement = $db->prepare($query);
    $statement->bindValue(':id',$id);
    $statement->execute();
    $statement->closeCursor();     
}

function get_oldest_preparing_id($db) {
    $query = 'SELECT min(id) id FROM pizza_orders where status=\'Preparing\'';
    $statement = $db->prepare($query);
    $statement->execute();
    $id = $statement->fetch()['id'];
    $statement->closeCursor();
    return $id;     
}

function update_to_finished($db, $user_id) {
    $query = 'UPDATE pizza_orders SET status=\'Finished\' WHERE status = \'Baked\' and user_id = :user_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id',$user_id);
    $statement->execute();
    $statement->closeCursor();        
}

function add_order($db, $user_id,$size,$current_day,$status, $topping_ids) {
    error_log('add_order: size = '. $size);
    $query = 'INSERT INTO pizza_orders(user_id, size, day, status) '
            . 'VALUES (:user_id,:size,:current_day,:status)';
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id',$user_id);
    $statement->bindValue(':size',$size);
    $statement->bindValue(':current_day',$current_day);
    $statement->bindValue(':status',$status);
    $statement->execute();
    $statement->closeCursor(); 
    foreach ($topping_ids as $t) {
        add_order_topping($db, $t);
    }
}
// helper to add_order: uses last_insert_id() to pick up auto_increment value
function add_order_topping($db, $topping_id) {
    $topping_name = get_topping_name($db, $topping_id);
    $query = 'INSERT INTO order_topping(order_id, topping) '
            . 'VALUES (last_insert_id(),:topping)';
    $statement = $db->prepare($query);
    $statement->bindValue(':topping', $topping_name);
    $statement->execute();
    $statement->closeCursor();
}