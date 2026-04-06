<?php 
 $page_title = 'Services & Products';
require_once 'includes/header.php';

 $conn = getConnection();
 $type = $_GET['type'] ?? 'services';
 $category = $_GET['category'] ?? '';

// Corrected Validation Logic
 $validTypes = ['services', 'products'];
if (!in_array($type, $validTypes)) {
    $type = 'services';
}

// Build Query based on Type
if ($type === 'services') {
    $where = "status = 'available'";
    if ($category) $where .= " AND category = '$category'";
    $items = $conn->query("SELECT * FROM services WHERE $where ORDER BY category, name");
    $categories = $conn->query("SELECT DISTINCT category FROM services WHERE status = 'available'");
} else {
    $where = "quantity > 0"; // assuming products show if in stock
    if ($category) $where .= " AND category = '$category'";
    $items = $conn->query("SELECT * FROM products WHERE $where ORDER BY category, name");
    $categories = $conn->query("SELECT DISTINCT category FROM products WHERE quantity > 0");
}

 $images = [
    'https://images.unsplash.com/photo-1562322140-8baeacacf450?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1519014816548-bf5fe059798b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1560066984-138dadb4c035?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1595476108010-b4d1f102b1b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
];
?>

<div class="page-header">
    <div class="page-header-content container">
        <h1><?php echo ucfirst($type); ?></h1>
        <p>Browse our <?php echo $type; ?> offerings</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Type Toggle -->
        <div class="text-center mb-2">
            <a href="listing.php?type=services" class="btn <?php echo $type === 'services' ? 'btn-primary' : 'btn-outline'; ?>">Services</a>
            <a href="listing.php?type=products" class="btn <?php echo $type === 'products' ? 'btn-primary' : 'btn-outline'; ?>">Products</a>
        </div>
        
        <!-- Category Filter -->
        <div class="text-center mb-2">
            <a href="listing.php?type=<?php echo $type; ?>" class="btn btn-sm <?php echo !$category ? 'btn-primary' : 'btn-outline'; ?>">All</a>
            <?php if ($categories): ?>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <a href="listing.php?type=<?php echo $type; ?>&category=<?php echo urlencode($cat['category']); ?>" 
                       class="btn btn-sm <?php echo $category === $cat['category'] ? 'btn-primary' : 'btn-outline'; ?>">
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
        
        <!-- Items Grid -->
        <div class="services-grid">
            <?php 
            if ($items && $items->num_rows > 0):
                $i = 0;
                while ($row = $items->fetch_assoc()):
                    $img = $images[$i % count($images)];
            ?>
                <div class="card reveal">
                    <div class="card-image" style="background-image: url('<?php echo $img; ?>')"></div>
                    <div class="card-body">
                        <p class="card-subtitle"><?php echo htmlspecialchars($row['category']); ?></p>
                        <h3 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p class="card-text">
                            <?php 
                            $desc = $row['description'] ?? 'No description available';
                            echo htmlspecialchars(substr($desc, 0, 100));
                            if (strlen($desc) > 100) echo '...';
                            ?>
                        </p>
                        <?php if ($type === 'services'): ?>
                            <p class="card-text"><small>Duration: <?php echo $row['duration']; ?> minutes</small></p>
                        <?php else: ?>
                            <p class="card-text"><small>Stock: <?php echo $row['quantity']; ?> units</small></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <span class="card-price"><?php echo formatCurrency($row['price']); ?></span>
                        <a href="details.php?type=<?php echo rtrim($type, 's'); ?>&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline">View Details</a>
                    </div>
                </div>
            <?php 
                    $i++;
                endwhile; 
            else:
            ?>
                <div class="text-center" style="grid-column: 1 / -1;">
                    <p>No <?php echo $type; ?> found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php $conn->close(); ?>
<?php require_once 'includes/footer.php'; ?>