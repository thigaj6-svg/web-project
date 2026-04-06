<?php 
 $page_title = 'Dashboard';
require_once 'includes/header.php';
requireLogin();
?>

<div class="dashboard">
    <div class="dashboard-header">
        <div class="container">
            <h1>Welcome, <?php echo htmlspecialchars(getCurrentUserName()); ?></h1>
            <p>Manage your salon activities and view financial reports</p>
        </div>
    </div>
    
    <div class="container">
        <?php
        $conn = getConnection();
        
        // Get statistics
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        
        // Today's revenue
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE DATE(transaction_date) = ? AND status = 'completed'");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $todayRevenue = $stmt->get_result()->fetch_assoc()['total'];
        
        // Monthly revenue
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE DATE_FORMAT(transaction_date, '%Y-%m') = ? AND status = 'completed'");
        $stmt->bind_param("s", $thisMonth);
        $stmt->execute();
        $monthRevenue = $stmt->get_result()->fetch_assoc()['total'];
        
        // Monthly expenses
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = ?");
        $stmt->bind_param("s", $thisMonth);
        $stmt->execute();
        $monthExpenses = $stmt->get_result()->fetch_assoc()['total'];
        
        // Active staff count
        $staffCount = $conn->query("SELECT COUNT(*) as count FROM staff WHERE status = 'active'")->fetch_assoc()['count'];
        
        // Services count
        $servicesCount = $conn->query("SELECT COUNT(*) as count FROM services WHERE status = 'available'")->fetch_assoc()['count'];
        
        // Products count
        $productsCount = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'in_stock'")->fetch_assoc()['count'];
        
        // Pending appointments
        $pendingAppointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'scheduled'")->fetch_assoc()['count'];
        ?>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <p class="stat-label">Today's Revenue</p>
                <p class="stat-value"><?php echo formatCurrency($todayRevenue); ?></p>
            </div>
            <div class="stat-card stat-success">
                <p class="stat-label">Monthly Revenue</p>
                <p class="stat-value"><?php echo formatCurrency($monthRevenue); ?></p>
            </div>
            <div class="stat-card stat-warning">
                <p class="stat-label">Monthly Expenses</p>
                <p class="stat-value"><?php echo formatCurrency($monthExpenses); ?></p>
            </div>
            <div class="stat-card stat-success">
                <p class="stat-label">Net Profit</p>
                <p class="stat-value"><?php echo formatCurrency($monthRevenue - $monthExpenses); ?></p>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <p class="stat-label">Active Staff</p>
                <p class="stat-value"><?php echo $staffCount; ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Services Available</p>
                <p class="stat-value"><?php echo $servicesCount; ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Products in Stock</p>
                <p class="stat-value"><?php echo $productsCount; ?></p>
            </div>
            <div class="stat-card stat-warning">
                <p class="stat-label">Pending Appointments</p>
                <p class="stat-value"><?php echo $pendingAppointments; ?></p>
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="table-container mt-2">
            <div class="table-header">
                <h3>Recent Transactions</h3>
                <a href="transactions.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 10");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $statusClass = $row['status'] === 'completed' ? 'status-active' : 
                                              ($row['status'] === 'pending' ? 'status-pending' : 'status-inactive');
                                echo '<tr>
                                    <td>#' . $row['id'] . '</td>
                                    <td>' . htmlspecialchars($row['description']) . '</td>
                                    <td>' . ucfirst($row['transaction_type']) . '</td>
                                    <td class="currency">' . formatCurrency($row['amount']) . '</td>
                                    <td>' . formatDateTime($row['transaction_date']) . '</td>
                                    <td><span class="status-badge ' . $statusClass . '">' . ucfirst($row['status']) . '</span></td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center">No transactions found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if (isAdmin()): ?>
        <div class="mt-2">
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <a href="admin.php" class="card" style="text-align: center; padding: 2rem;">
                    <h4>Staff Management</h4>
                    <p>Manage salon staff</p>
                </a>
                <a href="services.php" class="card" style="text-align: center; padding: 2rem;">
                    <h4>Services & Products</h4>
                    <p>Add and manage offerings</p>
                </a>
                <a href="expenses.php" class="card" style="text-align: center; padding: 2rem;">
                    <h4>Expenses</h4>
                    <p>Track salon expenses</p>
                </a>
                <a href="reports.php" class="card" style="text-align: center; padding: 2rem;">
                    <h4>Reports</h4>
                    <p>View financial reports</p>
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <?php $conn->close(); ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>