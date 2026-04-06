<?php 
$page_title = 'Details';
require_once 'includes/header.php';

$conn = getConnection();
$type = $_GET['type'] ?? 'service';
$id = intval($_GET['id'] ?? 0);

if ($type === 'service') {
    $item = $conn->query("SELECT * FROM services WHERE id = $id")->fetch_assoc();
    $pageTitle = $item ? $item['name'] : 'Service Not Found';
} else {
    $item = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
    $pageTitle = $item ? $item['name'] : 'Product Not Found';
}

$page_title = $pageTitle;

$images = [
    'https://images.unsplash.com/photo-1562322140-8baeacacf450?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'
];
?>

<?php if ($item): ?>
<div class="page-header" style="background-image: url('<?php echo $images[array_rand($images)]; ?>');">
    <div class="page-header-content container">
        <p class="tagline"><?php echo htmlspecialchars($item['category']); ?></p>
        <h1><?php echo htmlspecialchars($item['name']); ?></h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="listing.php?type=<?php echo $type; ?>s"><?php echo ucfirst($type); ?>s</a>
            <span>/</span>
            <span><?php echo htmlspecialchars($item['name']); ?></span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
            <div class="card" style="padding: 2rem;">
                <h3>Details</h3>
                <div class="divider" style="margin: 1rem 0;"></div>
                
                <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
                <p><strong>Price:</strong> <span class="currency"><?php echo formatCurrency($item['price']); ?></span></p>
                
                <?php if ($type === 'service'): ?>
                    <p><strong>Duration:</strong> <?php echo $item['duration']; ?> minutes</p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?php echo $item['status'] === 'available' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </p>
                <?php else: ?>
                    <p><strong>Cost Price:</strong> <?php echo formatCurrency($item['cost_price']); ?></p>
                    <p><strong>Stock:</strong> <?php echo $item['quantity']; ?> units</p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?php echo $item['quantity'] > 0 ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $item['quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                        </span>
                    </p>
                <?php endif; ?>
                
                <div class="divider" style="margin: 1.5rem 0;"></div>
                
                <h4>Description</h4>
                <p><?php echo htmlspecialchars($item['description'] ?? 'No description available.'); ?></p>
                
                <div style="margin-top: 2rem;">
                    <a href="listing.php?type=<?php echo $type; ?>s" class="btn btn-outline">Back to List</a>
                    <?php if (isAdmin()): ?>
                        <a href="services.php" class="btn btn-primary" style="margin-left: 10px;">Manage Items</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div style="height: 300px; background-size: cover; background-position: center; background-image: url('<?php echo $images[array_rand($images)]; ?>');"></div>
            </div>
        </div>
    </div>
</section>

<?php else: ?>
<div class="section">
    <div class="container text-center">
        <h2>Item Not Found</h2>
        <p>The requested item could not be found.</p>
        <a href="listing.php?type=<?php echo $type; ?>s" class="btn btn-primary mt-1">Back to List</a>
    </div>
</div>
<?php endif; ?>

<?php $conn->close(); ?>
<?php require_once 'includes/footer.php'; ?>
```