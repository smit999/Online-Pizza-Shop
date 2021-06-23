<?php include '../view/header.php'; ?>
<main>
    <section>
    <h1>Add User</h1>
    <form action="index.php" method="post">
        <input type="hidden" name="action" value="add_user">
        <div id="data">
        <label>Username:</label>
        <input type="text" name="username" />
        <br>
        
        <label>Room:</label>
          <select name="room">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option   
                        value="<?php echo $i; ?>" > <?php echo $i; ?>
                    </option>
                <?php endfor; ?> 
            </select>
        </div>
        <br>
        <input class="submitbutton" type="submit" value="Add User" />
        <br>
        <br>
    </form>
    <p>
        <a href="index.php?action=list_users">View User List</a>
    </p>
  </section>
<?php include '../view/footer.php'; 