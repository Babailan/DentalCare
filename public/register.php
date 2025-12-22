<?php include 'header.php'; ?>

<h2>Register</h2>
<?php if (isset($_GET['error'])): ?>
    <script>
        window.onload = function() {
            alert("<?php echo htmlspecialchars($_GET['error']); ?>");
        };
    </script>
<?php endif; ?>

<form id="registrationForm" action="register_action.php" method="post">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" placeholder="Full Name" required>
      
    <label for="ic">IC:</label>
    <input type="text" id="ic" name="ic" placeholder="030204012084" required>
      
    <label for="sex">Sex:</label>
    <select id="sex" name="sex" required>
        <option value="Male" selected>Male</option>
        <option value="Female">Female</option>
    </select>
      
    <label for="address">Address:</label>
    <input type="text" id="address" name="address" placeholder="Your Address" required>
      
    <label for="age">Age:</label>
    <input type="number" id="age" name="age" placeholder="Age" required>
      
    <label for="mobile_no">Mobile No:</label>
    <input type="text" id="mobile_no" name="mobile_no" placeholder="0123456789" required>
      
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" placeholder="email@example.com" required>
      
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" placeholder="username" required>
      
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" placeholder="password" required>
      
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" id="confirm_password" name="confirm_password" placeholder="confirm password" required>
      
    <input type="submit" value="Register">
</form>

<script>
    document.getElementById('registrationForm').addEventListener('submit', function(event) {
        const name = document.getElementById('name').value;
        const ic = document.getElementById('ic').value;
        const sex = document.getElementById('sex').value;
        const address = document.getElementById('address').value;
        const age = document.getElementById('age').value;
        const mobileNo = document.getElementById('mobile_no').value;
        const email = document.getElementById('email').value;
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Validate Name
        if (name.trim() === '') {
            alert('Name is required.');
            event.preventDefault();
            return false;
        }

        // Validate IC
        if (!/^\d{12}$/.test(ic)) {
            alert('IC must be a 12-digit number.');
            event.preventDefault();
            return false;
        }

        // Validate Sex
        if (sex !== 'Male' && sex !== 'Female') {
            alert('Please select a valid sex.');
            event.preventDefault();
            return false;
        }

        // Validate Address
        if (address.trim() === '') {
            alert('Address is required.');
            event.preventDefault();
            return false;
        }

        // Validate Age
        if (age < 1 || age > 120) {
            alert('Please enter a valid age.');
            event.preventDefault();
            return false;
        }

        // Validate Mobile Number
        if (!/^\d{10,11}$/.test(mobileNo)) {
            alert('Mobile number must be 10 or 11 digits.');
            event.preventDefault();
            return false;
        }

        // Validate Email
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            alert('Please enter a valid email address.');
            event.preventDefault();
            return false;
        }

        // Validate Username
        if (username.trim() === '') {
            alert('Username is required.');
            event.preventDefault();
            return false;
        }

        // Validate Password
        if (password.length < 8) {
            alert('Password must be at least 8 characters long.');
            event.preventDefault();
            return false;
        }

        // Validate Confirm Password
        if (password !== confirmPassword) {
            alert('Passwords do not match.');
            event.preventDefault();
            return false;
        }

        return true;
    });
</script>

<?php include 'footer.php'; ?>
