<?php 
 $page_title = 'Transactions';
require_once 'includes/header.php';
requireLogin();

 $conn = getConnection();
 $message = '';

// Handle Transaction Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_transaction') {
        $transaction_type = $_POST['transaction_type'];
        $description = trim($_POST['description']);
        $amount = floatval($_POST['amount']);
        $client_name = trim($_POST['client_name']);
        $staff_id = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : null;
        $payment_method = $_POST['payment_method'];
        
        $stmt = $conn->prepare("INSERT INTO transactions (transaction_type, description, amount, client_name, staff_id, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsis", $transaction_type, $description, $amount, $client_name, $staff_id, $payment_method);
        
        if ($stmt->execute()) {
            $message = 'Transaction added successfully!';
        } else {
            $message = 'Error adding transaction: ' . $conn->error;
        }
    }
    
    if ($action === 'delete_transaction') {
        $id = intval($_POST['transaction_id']);
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = 'Transaction deleted successfully!';
        }
    }
}

// Filters
 $type_filter = $_GET['type'] ?? '';
 $status_filter = $_GET['status'] ?? '';
 $date_from = $_GET['date_from'] ?? '';
 $date_to = $_GET['date_to'] ?? '';

 $where = "1=1";
if ($type_filter) $where .= " AND t.transaction_type = '$type_filter'";
if ($status_filter) $where .= " AND t.status = '$status_filter'";
if ($date_from) $where .= " AND DATE(t.transaction_date) >= '$date_from'";
if ($date_to) $where .= " AND DATE(t.transaction_date) <= '$date_to'";

// Fetch transactions with staff names
 $transactions = $conn->query("SELECT t.*, s.name as staff_name FROM transactions t LEFT JOIN staff s ON t.staff_id = s.id WHERE $where ORDER BY t.transaction_date DESC");
 $staff = $conn->query("SELECT id, name FROM staff WHERE status = 'active'");

// Calculate totals
 $totalAmount = 0;
 $transactionsData = [];
while ($row = $transactions->fetch_assoc()) {
    $transactionsData[] = $row;
    if ($row['status'] === 'completed') {
        $totalAmount += $row['amount'];
    }
}
?>

<div class="page-header">
    <div class="page-header-content container">
        <h1>Transactions</h1>
        <p>View and manage all financial transactions</p>
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span>/</span>
            <span>Transactions</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success animate-fadeIn"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="card mb-2" style="padding: 1.5rem;">
            <form method="GET" class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="service" <?php echo $type_filter === 'service' ? 'selected' : ''; ?>>Service</option>
                        <option value="product" <?php echo $type_filter === 'product' ? 'selected' : ''; ?>>Product</option>
                        <option value="sale" <?php echo $type_filter === 'sale' ? 'selected' : ''; ?>>Sale</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-input" value="<?php echo $date_from; ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-input" value="<?php echo $date_to; ?>">
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="transactions.php" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>
        
        <div class="table-container">
            <div class="table-header">
                <div>
                    <h3>All Transactions</h3>
                    <p class="stat-label">Total Completed: <?php echo formatCurrency($totalAmount); ?></p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-outline" onclick="printReport('Transactions Report')">Print List</button>
                    <?php if (isAdmin()): ?>
                        <button class="btn btn-primary" onclick="openModal('addTransactionModal')">Add Transaction</button>
                    <?php endif; ?>
                </div>
            </div>
            <div id="printArea">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Client</th>
                                <th>Payment</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactionsData as $row): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td><?php echo formatDateTime($row['transaction_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><?php echo ucfirst($row['transaction_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['client_name'] ?? '-'); ?></td>
                                    <td><?php echo ucfirst($row['payment_method']); ?></td>
                                    <td class="currency"><?php echo formatCurrency($row['amount']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $row['status'] === 'completed' ? 'status-completed' : ($row['status'] === 'pending' ? 'status-pending' : 'status-inactive'); ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Receipt Button -->
                                        <a href="receipt.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-primary" style="margin-right: 5px; text-decoration: none;">
                                            Receipt
                                        </a>
                                        
                                        <!-- Admin Delete Button -->
                                        <?php if (isAdmin()): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirmDelete('this transaction')">
                                                <input type="hidden" name="action" value="delete_transaction">
                                                <input type="hidden" name="transaction_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($transactionsData)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No transactions found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Transaction Modal -->
<div class="modal-overlay" id="addTransactionModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Transaction</h3>
            <button class="modal-close" onclick="closeModal('addTransactionModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_transaction">
                
                <div class="form-group">
                    <label class="form-label">Transaction Type *</label>
                    <select name="transaction_type" class="form-select" required>
                        <option value="service">Service</option>
                        <option value="product">Product</option>
                        <option value="sale">Sale</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <input type="text" name="description" class="form-input" placeholder="e.g., Haircut and Blow Dry" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amount (Kshs) *</label>
                    <input type="number" name="amount" class="form-input currency-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Client Name</label>
                    <input type="text" name="client_name" class="form-input" placeholder="Walk-in Customer">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Staff</label>
                    <select name="staff_id" class="form-select">
                        <option value="">Select Staff</option>
                        <?php 
                        // Reset pointer for staff query
                        $staff->data_seek(0);
                        while ($s = $staff->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Payment Method *</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="card">Card</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addTransactionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Transaction</button>
            </div>
        </form>
    </div>
</div>

<?php $conn->close(); ?>
<?php require_once 'includes/footer.php'; ?>