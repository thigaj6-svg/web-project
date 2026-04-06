<?php require_once 'config/database.php'; ?>
<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Glamour Salon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Live Wallpaper Canvas -->
    <canvas id="live-bg-canvas"></canvas>
    
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <span class="logo-icon">✦</span>
                Glamour Spur
            </a>
            
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
      <ul class="nav-menu" id="navMenu">
    <li><a href="index.php" class="nav-link">Home</a></li>
    <li><a href="listing.php" class="nav-link">Services</a></li>
    <li><a href="listing.php?type=products" class="nav-link">Products</a></li>
    <li><a href="book_appointment.php" class="nav-link">Book Now</a></li> <!-- NEW PUBLIC LINK -->
    
    <?php if (isLoggedIn()): ?>
        <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
        <li><a href="transactions.php" class="nav-link">Transactions</a></li>
        
        <?php if (isAdmin()): ?>
            <li><a href="appointments.php" class="nav-link">Appointments</a></li> <!-- NEW ADMIN LINK -->
            <li><a href="admin.php" class="nav-link">Admin</a></li>
            <li><a href="reports.php" class="nav-link">Reports</a></li>
        <?php endif; ?>
        <li><a href="logout.php" class="nav-link nav-logout">Logout</a></li>
    <?php else: ?>
        <li><a href="login.php" class="nav-link">Login</a></li>
        <li><a href="register.php" class="nav-link nav-cta">Register</a></li>
    <?php endif; ?>
</ul>
        </div>
    </nav>
    <main>