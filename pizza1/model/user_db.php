<?php

function get_users($db) {
    $query = 'SELECT id, username, room FROM shop_users';
    $statement = $db->prepare($query);
    $statement->execute();
    $users = $statement->fetchAll();
    return $users;    
}
function get_username($db, $user_id) {
    $query = 'SELECT username FROM shop_users where id = :user_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $user_id);
    $statement->execute();
    $user_row = $statement->fetch();
    return $user_row['username'];    
}
function get_room($db, $user_id) {
    $query = 'SELECT room FROM shop_users where id = :user_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $user_id);
    $statement->execute();
    $user_row = $statement->fetch();
    return $user_row['room'];    
}
function add_user($db, $username, $room)  
{
    $query = 'INSERT INTO shop_users
                 (username, room)
              VALUES
                 (:username, :room)';
    $statement = $db->prepare($query);
    $statement->bindValue(':username', $username);
     $statement->bindValue(':room', $room);
    $statement->execute();
    $statement->closeCursor();
}

function delete_user($db, $user_id)  
{
    $query = 'DELETE FROM shop_users
                 WHERE id = :user_id';
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $user_id);
    $statement->execute();
    $statement->closeCursor();
}