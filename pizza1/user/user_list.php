<?php include '../view/header.php'; ?>
<main>
    <section>
    <h1>User List</h1>   
      <table>
            <tr>
                <th>Username</th>
                <th>Room</th>
                <th>&nbsp;</th>
            </tr>
            <?php foreach ($users as $user) : ?>
            <tr>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo $user['room']; ?></td>
                <td><form action="." method="post">
                    <input type="hidden" name="action"
                           value="delete_user">
                    <input type="hidden" name="user_id"
                           value="<?php echo $user['id']; ?>">        
                    <input type="submit" value="Delete">
                </form></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <p>
        <a href=".?action=show_add_form">Add User</a>
    </p>
    </section>
</main>
<?php include '../view/footer.php'; 
