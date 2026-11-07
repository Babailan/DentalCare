<?php include 'header.php'; ?>

<div class="card card-bordered w-full max-w-sm bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center text-2xl mb-4">CIAT Dental Clinic Sign in</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error mb-4">
                <span><?php echo htmlspecialchars($_GET['error']); ?></span>
            </div>

            <?php include 'footer.php'; ?>
        <?php endif; ?>
        
        <form action="login_action.php" method="post" class="flex flex-col gap-4">
            <label class="floating-label">
                <input type="text" id="username" name="username" placeholder="Username" class="input w-full" required />
                <span>Username</span>
            </label>
            
            <label class="floating-label">
                <input type="password" id="password" name="password" placeholder="Password" class="input w-full" required />
                <span>Password</span>
            </label>
            
            <button type="submit" class="btn btn-primary w-full mt-2">Login</button>
        </form>
        
        <div class="text-center mt-4">
            <p class="text-sm text-base-content/70">Patient accounts are created by the clinic doctor. Please contact the clinic if you need an account.</p>
        </div>
    </div>
</div>
