<?php 
 $page_title = 'Reports';
require_once 'includes/header.php';
requireAdmin();

 $conn = getConnection();

// Date filters (default to current month)
 $date_from = $_GET['date_from'] ?? date('Y-m-01');
 $date_to = $_GET['date_to'] ?? date('Y-m-d');

// --- Summary Calculations ---
 $totalRevenue = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total FROM transactions 
    WHERE DATE(transaction_date) BETWEEN '$date_from' AND '$date_to' AND status = 'completed'
")->fetch_assoc()['total'];

 $totalExpenses = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total FROM expenses 
    WHERE DATE(expense_date) BETWEEN '$date_from' AND '$date_to'
")->fetch_assoc()['total'];

 $netProfit = $totalRevenue - $totalExpenses;

// --- Breakdown Queries ---

// 1. Revenue by Service Category (FIXED to show manual entries as 'Direct Sales')
 $revenueByService = $conn->query("
    SELECT 
        COALESCE(s.category, 'Direct Sales') as category, 
        COUNT(t.id) as count, 
        SUM(t.amount) as total 
    FROM transactions t 
    LEFT JOIN services s ON t.service_id = s.id 
    WHERE DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to' 
    AND t.status = 'completed'
    GROUP BY COALESCE(s.category, 'Direct Sales')
");

// 2. Revenue by Staff
 $revenueByStaff = $conn->query("
    SELECT 
        COALESCE(s.name, 'Unassigned') as name, 
        COUNT(t.id) as count, 
        SUM(t.amount) as total 
    FROM transactions t 
    LEFT JOIN staff s ON t.staff_id = s.id 
    WHERE DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to' 
    AND t.status = 'completed'
    GROUP BY COALESCE(s.name, 'Unassigned')
");

// 3. Expenses by Category
 $expensesByCategory = $conn->query("
    SELECT category, COUNT(id) as count, SUM(amount) as total 
    FROM expenses 
    WHERE DATE(expense_date) BETWEEN '$date_from' AND '$date_to'
    GROUP BY category
");

// 4. Payment Methods
 $paymentMethods = $conn->query("
    SELECT payment_method, COUNT(id) as count, SUM(amount) as total 
    FROM transactions 
    WHERE DATE(transaction_date) BETWEEN '$date_from' AND '$date_to' AND status = 'completed'
    GROUP BY payment_method
");
?>

<div class="page-header">
    <div class="page-header-content container">
        <h1>Financial Reports</h1>
        <p>View comprehensive financial analytics</p>
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span>/</span>
            <span>Reports</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Date Filter -->
        <div class="card mb-2" style="padding: 1.5rem;">
            <form method="GET" class="dashboard-grid" style="grid-template-columns: repeat(3, 1fr); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-input" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-input" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <a href="print_reports.php?date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" target="_blank" class="btn btn-outline">Print</a>
                </div>
            </form>
        </div>
        
        <!-- Summary Cards -->
        <div class="dashboard-grid mb-2">
            <div class="stat-card stat-success">
                <p class="stat-label">Total Revenue</p>
                <p class="stat-value"><?php echo formatCurrency($totalRevenue); ?></p>
            </div>
            <div class="stat-card stat-danger">
                <p class="stat-label">Total Expenses</p>
                <p class="stat-value"><?php echo formatCurrency($totalExpenses); ?></p>
            </div>
            <div class="stat-card <?php echo $netProfit >= 0 ? 'stat-success' : 'stat-danger'; ?>">
                <p class="stat-label">Net Profit</p>
                <p class="stat-value"><?php echo formatCurrency($netProfit); ?></p>
            </div>
        </div>
        
        <div id="printArea">
            <div class="dashboard-grid" style="grid-template-columns: repeat(2, 1fr);">
                <!-- Revenue by Service Category -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Revenue by Category</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Count</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($revenueByService->num_rows > 0) {
                                    while ($row = $revenueByService->fetch_assoc()) {
                                        echo '<tr>
                                            <td>' . htmlspecialchars($row['category']) . '</td>
                                            <td>' . $row['count'] . '</td>
                                            <td class="currency">' . formatCurrency($row['total']) . '</td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">No data for this period</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Revenue by Staff -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Revenue by Staff</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Staff Name</th>
                                    <th>Transactions</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($revenueByStaff->num_rows > 0) {
                                    while ($row = $revenueByStaff->fetch_assoc()) {
                                        echo '<tr>
                                            <td>' . htmlspecialchars($row['name']) . '</td>
                                            <td>' . $row['count'] . '</td>
                                            <td class="currency">' . formatCurrency($row['total']) . '</td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">No data for this period</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Expenses by Category -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Expenses by Category</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Count</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($expensesByCategory->num_rows > 0) {
                                    while ($row = $expensesByCategory->fetch_assoc()) {
                                        echo '<tr>
                                            <td>' . htmlspecialchars($row['category']) . '</td>
                                            <td>' . $row['count'] . '</td>
                                            <td class="currency">' . formatCurrency($row['total']) . '</td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">No expenses recorded</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Revenue by Payment Method</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th>Count</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($paymentMethods->num_rows > 0) {
                                    while ($row = $paymentMethods->fetch_assoc()) {
                                        echo '<tr>
                                            <td>' . ucfirst($row['payment_method']) . '</td>
                                            <td>' . $row['count'] . '</td>
                                            <td class="currency">' . formatCurrency($row['total']) . '</td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">No data for this period</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $conn->close(); ?>
<?php require_once 'includes/footer.php'; ?>