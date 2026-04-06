<?php 
 $page_title = 'Home';
require_once 'includes/header.php'; 
?>

<section class="hero">
    <div class="hero-content">
        <p class="tagline">Welcome to Glamour Spur</p>
        <h1>Where Beauty Meets Elegance</h1>
        <p>Experience the finest salon services in Nairobi. Our expert stylists and beauticians are dedicated to making you look and feel your absolute best.</p>
        <div class="hero-buttons">
            <a href="listing.php" class="btn btn-primary">View Services</a>
            <a href="register.php" class="btn btn-secondary">Book Appointment</a>
        </div>
    </div>
</section>

<section class="services-section section">
    <div class="container">
        <div class="section-header reveal">
            <h2>Our Services</h2>
            <div class="divider"></div>
            <p>Discover our range of premium beauty services tailored to enhance your natural beauty</p>
        </div>
        
        <div class="services-grid">
            <?php
            $conn = getConnection();
            $sql = "SELECT * FROM services WHERE status = 'available' ORDER BY id DESC LIMIT 6";
            $result = $conn->query($sql);
            
            $images = [
                'https://images.unsplash.com/photo-1562322140-8baeacacf450?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1519014816548-bf5fe059798b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1560066984-138dadb4c035?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1595476108010-b4d1f102b1b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
            ];
            
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $img = $images[$i % count($images)];
                echo '
                <div class="card reveal">
                    <div class="card-image" style="background-image: url(\'' . $img . '\')"></div>
                    <div class="card-body">
                        <p class="card-subtitle">' . htmlspecialchars($row['category']) . '</p>
                        <h3 class="card-title">' . htmlspecialchars($row['name']) . '</h3>
                        <p class="card-text">' . htmlspecialchars(substr($row['description'] ?? '', 0, 80)) . '...</p>
                    </div>
                    <div class="card-footer">
                        <span class="card-price">' . formatCurrency($row['price']) . '</span>
                        <a href="details.php?type=service&id=' . $row['id'] . '" class="btn btn-sm btn-outline">View Details</a>
                    </div>
                </div>';
                $i++;
            }
            $conn->close();
            ?>
        </div>
        
        <div class="text-center mt-2 reveal">
            <a href="listing.php" class="btn btn-primary">View All Services</a>
        </div>
    </div>
</section>

<section class="features-section section">
    <div class="container">
        <div class="section-header reveal">
            <h2>Why Choose Us</h2>
            <div class="divider"></div>
            <p>We are committed to providing exceptional salon experiences</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-icon">✦</div>
                <h4>Expert Stylists</h4>
                <p>Our team consists of highly trained and experienced beauty professionals.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">❋</div>
                <h4>Premium Products</h4>
                <p>We use only the finest quality products for all our treatments.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">✧</div>
                <h4>Relaxing Atmosphere</h4>
                <p>Enjoy a serene and luxurious environment during your visit.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">❖</div>
                <h4>Affordable Prices</h4>
                <p>Quality beauty services at competitive prices in Nairobi.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background: var(--color-white);">
    <div class="container">
        <div class="section-header reveal">
            <h2>Our Products</h2>
            <div class="divider"></div>
            <p>Premium beauty products available for purchase</p>
        </div>
        
        <div class="services-grid">
            <?php
            $conn = getConnection();
            $sql = "SELECT * FROM products WHERE status = 'in_stock' ORDER BY id DESC LIMIT 4";
            $result = $conn->query($sql);
            
            $productImages = [
                'https://images.unsplash.com/photo-1556228720-195a672e8a03?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1596462502278-27bfdc403348?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
            ];
            
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $img = $productImages[$i % count($productImages)];
                echo '
                <div class="card reveal">
                    <div class="card-image" style="background-image: url(\'' . $img . '\')"></div>
                    <div class="card-body">
                        <p class="card-subtitle">' . htmlspecialchars($row['category']) . '</p>
                        <h3 class="card-title">' . htmlspecialchars($row['name']) . '</h3>
                        <p class="card-text">Stock: ' . $row['quantity'] . ' units available</p>
                    </div>
                    <div class="card-footer">
                        <span class="card-price">' . formatCurrency($row['price']) . '</span>
                        <a href="details.php?type=product&id=' . $row['id'] . '" class="btn btn-sm btn-outline">View</a>
                    </div>
                </div>';
                $i++;
            }
            $conn->close();
            ?>
        </div>
        
        <div class="text-center mt-2 reveal">
            <a href="listing.php?type=products" class="btn btn-outline">View All Products</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>