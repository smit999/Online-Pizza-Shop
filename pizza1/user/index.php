<?php
require('../model/database.php');
require('../model/user_db.php');
$action = filter_input(INPUT_POST, 'action');
if ($action == NULL) {
    $action = filter_input(INPUT_GET, 'action');
    if ($action == NULL) {
        $action = 'list_users';
    }
}
if ($action == 'list_users') {
    try {
        $users = get_users($db);
        include('user_list.php');
    } catch (PDOException $e) {
        $error_message = $e->getMessage();
        include('../errors/database_error.php');
        exit();
    }
} else if ($action == 'delete_user') {
    $user_id = filter_input(INPUT_POST, 'user_id', 
            FILTER_VALIDATE_INT);
     if ($user_id == NULL || $user_id == FALSE) {
        $error = "Missing or incorrect user.";
        include('../errors/error.php');
    } else {
        try {
            delete_user($db, $user_id);
        } catch (PDOException $e) {
            $error_message = $e->getMessage();
            include('../errors/database_error.php');
            exit();
        }
        header("Location: .");
    }
} else if ($action == 'show_add_form') {
    include('user_add.php');
} else if ($action == 'add_user') {
    $username = filter_input(INPUT_POST, 'username');
    $room = filter_input(INPUT_POST, 'room');
    if ($username == NULL || $username == FALSE) {
        $error = "Invalid username. Check all fields and try again.";
        include('../errors/error.php');
    } else {
        try {
            add_user($db, $username, $room);
        } catch (PDOException $e) {
            $error_message = $e->getMessage();
            include('../errors/database_error.php');
            exit();
        }
        header("Location: .");
    }
}
