<?php include 'header.php'; ?>

<h2>Login</h2>
<div class='login'>
    <?php if (isset($_GET['error'])): ?>
        <script>
            window.onload = function() {
                alert("<?php echo htmlspecialchars($_GET['error']); ?>");
            };
        </script>
    <?php endif; ?>
    <form action="login_action.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="password" required>
        <input type="submit" value="Login">
        
        <div style="transform: translateY(15px)">
            <a href="register.php">Register</a>
        </div>
    </form>
</div>
<?php include 'footer.php'; ?>
