<?php 
 $page_title = 'Login';
require_once 'config/database.php';
require_once 'includes/auth.php';

 $error = '';
 $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, username, email, password, full_name, role FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                loginUser($user);
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
        $conn->close();
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="page-header">
    <div class="page-header-content container">
        <h1>Welcome Back</h1>
        <p>Sign in to access your account</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="form-container">
            <div class="form-card">
                <div class="form-header">
                    <h2>Sign In</h2>
                    <p>Enter your credentials to continue</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm" onsubmit="return validateForm('loginForm')">
                    <div class="form-group">
                        <label class="form-label" for="username">Username or Email</label>
                        <input type="text" id="username" name="username" class="form-input" 
                               placeholder="Enter your username or email" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Sign In
                    </button>
                </form>
                
                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>