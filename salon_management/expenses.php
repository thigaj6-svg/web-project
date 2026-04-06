<?php 
 $page_title = 'Expenses';
require_once 'includes/header.php';
requireAdmin();

 $conn = getConnection();
 $message = '';

// Handle Expense Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_expense') {
        $description = trim($_POST['description']);
        $amount = floatval($_POST['amount']);
        $category = trim($_POST['category']);
        $expense_date = $_POST['expense_date'];
        $staff_id = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : null;
        $notes = trim($_POST['notes']);
        
        $stmt = $conn->prepare("INSERT INTO expenses (description, amount, category, expense_date, staff_id, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssis", $description, $amount, $category, $expense_date, $staff_id, $notes);
        
        if ($stmt->execute()) {
            $message = 'Expense added successfully!';
        }
    }
    
    if ($action === 'delete_expense') {
        $id = intval($_POST['expense_id']);
        $conn->query("DELETE FROM expenses WHERE id = $id");
        $message = 'Expense deleted successfully!';
    }
}

// Filters
 $category_filter = $_GET['category'] ?? '';
 $date_from = $_GET['date_from'] ?? '';
 $date_to = $_GET['date_to'] ?? '';

 $where = "1=1";
if ($category_filter) $where .= " AND category = '$category_filter'";
if ($date_from) $where .= " AND expense_date >= '$date_from'";
if ($date_to) $where .= " AND expense_date <= '$date_to'";

 $expenses = $conn->query("SELECT e.*, s.name as staff_name FROM expenses e LEFT JOIN staff s ON e.staff_id = s.id WHERE $where ORDER BY e.expense_date DESC");
 $staff = $conn->query("SELECT id, name FROM staff WHERE status = 'active'");

// Calculate total
 $totalExpenses = 0;
 $expensesData = [];
while ($row = $expenses->fetch_assoc()) {
    $expensesData[] = $row;
    $totalExpenses += $row['amount'];
}
?>

<div class="page-header">
    <div class="page-header-content container">
        <h1>Expenses</h1>
        <p>Track and manage salon expenses</p>
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span>/</span>
            <span>Expenses</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="card mb-2" style="padding: 1.5rem;">
            <form method="GET" class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="Supplies" <?php echo $category_filter === 'Supplies' ? 'selected' : ''; ?>>Supplies</option>
                        <option value="Utilities" <?php echo $category_filter === 'Utilities' ? 'selected' : ''; ?>>Utilities</option>
                        <option value="Rent" <?php echo $category_filter === 'Rent' ? 'selected' : ''; ?>>Rent</option>
                        <option value="Salaries" <?php echo $category_filter === 'Salaries' ? 'selected' : ''; ?>>Salaries</option>
                        <option value="Maintenance" <?php echo $category_filter === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        <option value="Other" <?php echo $category_filter === 'Other' ? 'selected' : ''; ?>>Other</option>
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
                    <a href="expenses.php" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>
        
        <div class="table-container">
            <div class="table-header">
                <div>
                    <h3>All Expenses</h3>
                    <p class="stat-label">Total: <?php echo formatCurrency($totalExpenses); ?></p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <!-- UPDATED PRINT BUTTON -->
                    <a href="print_expenses.php?category=<?php echo $category_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" target="_blank" class="btn btn-outline">Print</a>
                    <button class="btn btn-primary" onclick="openModal('addExpenseModal')">Add Expense</button>
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
                                <th>Category</th>
                                <th>Staff</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expensesData as $row): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td><?php echo formatDate($row['expense_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo htmlspecialchars($row['staff_name'] ?? '-'); ?></td>
                                    <td class="currency"><?php echo formatCurrency($row['amount']); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirmDelete('this expense')">
                                            <input type="hidden" name="action" value="delete_expense">
                                            <input type="hidden" name="expense_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Expense Modal -->
<div class="modal-overlay" id="addExpenseModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Expense</h3>
            <button class="modal-close" onclick="closeModal('addExpenseModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_expense">
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <input type="text" name="description" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <option value="Supplies">Supplies</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Rent">Rent</option>
                        <option value="Salaries">Salaries</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amount (Kshs) *</label>
                    <input type="number" name="amount" class="form-input currency-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="expense_date" class="form-input" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Staff (Optional)</label>
                    <select name="staff_id" class="form-select">
                        <option value="">Select Staff</option>
                        <?php mysqli_data_seek($staff, 0); while ($s = $staff->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-textarea"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addExpenseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Expense</button>
            </div>
        </form>
    </div>
</div>

<?php $conn->close(); ?>
<?php require_once 'includes/footer.php'; ?>