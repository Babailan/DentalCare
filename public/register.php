<?php include 'header.php'; ?>

<div class="card card-bordered w-full max-w-2xl bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center text-2xl mb-4">Register</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error mb-4">
                <span><?php echo htmlspecialchars($_GET['error']); ?></span>
            </div>
        <?php endif; ?>
        
        <form id="registrationForm" action="register_action.php" method="post" class="flex flex-col gap-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="floating-label">
                    <input type="text" id="name" name="name" placeholder="Full Name" class="input input-bordered w-full" required />
                    <span>Name</span>
                </label>
                
                <label class="floating-label">
                    <input type="text" id="ic" name="ic" placeholder="030204012084" class="input input-bordered w-full" required />
                    <span>IC</span>
                </label>
                
                <label class="floating-label">
                    <select id="sex" name="sex" class="select select-bordered w-full" required>
                        <option value="Male" selected>Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <span>Sex</span>
                </label>
                
                <label class="floating-label">
                    <input type="number" id="age" name="age" placeholder="Age" class="input input-bordered w-full" required />
                    <span>Age</span>
                </label>
                
                <label class="floating-label">
                    <input type="text" id="mobile_no" name="mobile_no" placeholder="0123456789" class="input input-bordered w-full" required />
                    <span>Mobile No</span>
                </label>
                
                <label class="floating-label">
                    <input type="email" id="email" name="email" placeholder="email@example.com" class="input input-bordered w-full" required />
                    <span>Email</span>
                </label>
                
                <label class="floating-label md:col-span-2">
                    <input type="text" id="address" name="address" placeholder="Your Address" class="input input-bordered w-full" required />
                    <span>Address</span>
                </label>
                
                <label class="floating-label">
                    <input type="text" id="username" name="username" placeholder="username" class="input input-bordered w-full" required />
                    <span>Username</span>
                </label>
                
                <label class="floating-label">
                    <input type="password" id="password" name="password" placeholder="password" class="input input-bordered w-full" required />
                    <span>Password</span>
                </label>
                
                <label class="floating-label md:col-span-2">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="confirm password" class="input input-bordered w-full" required />
                    <span>Confirm Password</span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary w-full mt-2">Register</button>
        </form>
        
        <div class="text-center mt-4">
            <a href="login.php" class="link link-primary">Back to Login</a>
        </div>
    </div>
</div>

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
