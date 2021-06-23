<?php

function get_current_day($db) {
    $query = 'SELECT * FROM pizza_sys_tab';    
    $statement = $db->prepare($query);
    $statement->execute();    
    $currentday = $statement->fetch();
    $statement->closeCursor();    
    $current_day = $currentday['current_day'];
    return $current_day;
}

function increment_day($db){
    $query = 'UPDATE pizza_sys_tab SET current_day=current_day + 1';    
    $statement = $db->prepare($query);
    $statement->execute();    
    $statement->closeCursor();    
}



