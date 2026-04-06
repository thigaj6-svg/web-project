<?php 
 $page_title = 'Services & Products';
require_once 'includes/header.php';
requireAdmin();

 $conn = getConnection();
 $message = '';

// Handle Service/Product Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_service') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $duration = intval($_POST['duration']);
        $category = trim($_POST['category']);
        
        $stmt = $conn->prepare("INSERT INTO services (name, description, price, duration, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $name, $description, $price, $duration, $category);
        
        if ($stmt->execute()) {
            $message = 'Service added successfully!';
        }
    }
    
    if ($action === 'add_product') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $cost_price = floatval($_POST['cost_price']);
        $quantity = intval($_POST['quantity']);
        $category = trim($_POST['category']);
        
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, cost_price, quantity, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddis", $name, $description, $price, $cost_price, $quantity, $category);
        
        if ($stmt->execute()) {
            $message = 'Product added successfully!';
        }
    }
    
    if ($action === 'delete_service') {
        $id = intval($_POST['service_id']);
        $conn->query("DELETE FROM services WHERE id = $id");
        $message = 'Service deleted successfully!';
    }
    
    if ($action === 'delete_product') {
        $id = intval($_POST['product_id']);
        $conn->query("DELETE FROM products WHERE id = $id");
        $message = 'Product deleted successfully!';
    }
}

 $services = $conn->query("SELECT * FROM services ORDER BY category, name");
 $products = $conn->query("SELECT * FROM products ORDER BY category, name");
?>

<div class="page-header">
    <div class="page-header-content container">
        <h1>Services & Products</h1>
        <p>Manage salon offerings</p>
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span>/</span>
            <span>Services & Products</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Services -->
        <div class="table-container mb-2">
            <div class="table-header">
                <h3>Services</h3>
                <button class="btn btn-primary" onclick="openModal('addServiceModal')">Add Service</button>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $services->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><?php echo $row['duration']; ?> mins</td>
                                <td class="currency"><?php echo formatCurrency($row['price']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['status'] === 'available' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete('this service')">
                                        <input type="hidden" name="action" value="delete_service">
                                        <input type="hidden" name="service_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Products -->
        <div class="table-container">
            <div class="table-header">
                <h3>Products</h3>
                <button class="btn btn-primary" onclick="openModal('addProductModal')">Add Product</button>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td class="currency"><?php echo formatCurrency($row['cost_price']); ?></td>
                                <td class="currency"><?php echo formatCurrency($row['price']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['status'] === 'in_stock' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $row['quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete('this product')">
                                        <input type="hidden" name="action" value="delete_product">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Add Service Modal -->
<div class="modal-overlay" id="addServiceModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Service</h3>
            <button class="modal-close" onclick="closeModal('addServiceModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_service">
                
                <div class="form-group">
                    <label class="form-label">Service Name *</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <option value="Hair">Hair</option>
                        <option value="Nails">Nails</option>
                        <option value="Skin">Skin</option>
                        <option value="Color">Color</option>
                        <option value="Spa">Spa</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Price (Kshs) *</label>
                    <input type="number" name="price" class="form-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Duration (minutes) *</label>
                    <input type="number" name="duration" class="form-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addServiceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Service</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal-overlay" id="addProductModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Product</h3>
            <button class="modal-close" onclick="closeModal('addProductModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_product">
                
                <div class="form-group">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <option value="Hair Care">Hair Care</option>
                        <option value="Skin Care">Skin Care</option>
                        <option value="Nails">Nails</option>
                        <option value="Tools">Tools</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Cost Price (Kshs) *</label>
                    <input type="number" name="cost_price" class="form-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Selling Price (Kshs) *</label>
                    <input type="number" name="price" class="form-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Initial Stock Quantity *</label>
                    <input type="number" name="quantity" class="form-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>

<?php $conn->close(); ?>
<?php require_once 'includes/footer.php'; ?>