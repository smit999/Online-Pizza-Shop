<?php
// the try/catch for these actions is in the caller, index.php

function get_sizes($db) {
    $query = 'SELECT * FROM menu_sizes';
    $statement = $db->prepare($query);
    $statement->execute();
    $sizes = $statement->fetchAll();
    return $sizes;    
}
