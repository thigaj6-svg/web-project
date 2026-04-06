<?php
 $page_title = 'Financial Report';
require_once 'config/database.php';
require_once 'includes/auth.php';
requireAdmin();

 $conn = getConnection();

// Date filters (default to current month if not set)
 $date_from = $_GET['date_from'] ?? date('Y-m-01');
 $date_to = $_GET['date_to'] ?? date('Y-m-d');

// --- Data Queries (Same as reports.php) ---

// Summary
 $totalRevenue = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total FROM transactions 
    WHERE DATE(transaction_date) BETWEEN '$date_from' AND '$date_to' AND status = 'completed'
")->fetch_assoc()['total'];

 $totalExpenses = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total FROM expenses 
    WHERE DATE(expense_date) BETWEEN '$date_from' AND '$date_to'
")->fetch_assoc()['total'];

 $netProfit = $totalRevenue - $totalExpenses;

// Revenue by category
 $revenueByService = $conn->query("
    SELECT s.category, COUNT(t.id) as count, SUM(t.amount) as total 
    FROM transactions t 
    JOIN services s ON t.service_id = s.id 
    WHERE DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to' 
    AND t.status = 'completed'
    GROUP BY s.category
");

// Revenue by staff
 $revenueByStaff = $conn->query("
    SELECT s.name, COUNT(t.id) as count, SUM(t.amount) as total 
    FROM transactions t 
    JOIN staff s ON t.staff_id = s.id 
    WHERE DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to' 
    AND t.status = 'completed'
    GROUP BY s.id, s.name
");

// Expenses by category
 $expensesByCategory = $conn->query("
    SELECT category, COUNT(id) as count, SUM(amount) as total 
    FROM expenses 
    WHERE DATE(expense_date) BETWEEN '$date_from' AND '$date_to'
    GROUP BY category
");

// Payment methods
 $paymentMethods = $conn->query("
    SELECT payment_method, COUNT(id) as count, SUM(amount) as total 
    FROM transactions 
    WHERE DATE(transaction_date) BETWEEN '$date_from' AND '$date_to' AND status = 'completed'
    GROUP BY payment_method
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Report</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .report-container { max-width: 900px; margin: 0 auto; }
        .report-header { text-align: center; border-bottom: 2px solid #2D2D2D; padding-bottom: 20px; margin-bottom: 30px; }
        .report-header h1 { margin: 0; font-size: 24px; }
        .report-header p { margin: 5px 0 0; color: #666; }
        
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .summary-box { padding: 15px; background: #f9f9f9; border-radius: 5px; text-align: center; }
        .summary-box h3 { margin: 0 0 10px; font-size: 14px; text-transform: uppercase; }
        .summary-box p { margin: 0; font-size: 20px; font-weight: bold; }

        .section { margin-bottom: 30px; }
        .section h2 { font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f4f4f4; font-weight: bold; }
        
        .btn-print { display: block; width: 100%; margin-top: 20px; padding: 10px; background: #2D2D2D; color: white; border: none; cursor: pointer; font-size: 14px; }
        .btn-back { display: block; width: 100%; margin-top: 10px; padding: 10px; background: #C9A87C; color: white; border: none; cursor: pointer; font-size: 14px; text-align: center; text-decoration: none; }

        @media print {
            body { padding: 0; }
            .btn-print, .btn-back { display: none; }
        }
    </style>
</head>
<body>

<div class="report-container">
    <div class="report-header">
        <h1>Glamour Salon</h1>
        <p>Financial Report</p>
        <p>Period: <?php echo date('M d, Y', strtotime($date_from)); ?> to <?php echo date('M d, Y', strtotime($date_to)); ?></p>
    </div>

    <!-- Summary -->
    <div class="summary-grid">
        <div class="summary-box">
            <h3>Total Revenue</h3>
            <p>Kshs <?php echo number_format($totalRevenue, 2); ?></p>
        </div>
        <div class="summary-box">
            <h3>Total Expenses</h3>
            <p>Kshs <?php echo number_format($totalExpenses, 2); ?></p>
        </div>
        <div class="summary-box">
            <h3>Net Profit</h3>
            <p>Kshs <?php echo number_format($netProfit, 2); ?></p>
        </div>
    </div>

    <!-- Tables -->
    <div class="section">
        <h2>Revenue by Service Category</h2>
        <table>
            <thead><tr><th>Category</th><th>Count</th><th>Revenue</th></tr></thead>
            <tbody>
                <?php while($row = $revenueByService->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($row['category']); ?></td><td><?php echo $row['count']; ?></td><td>Kshs <?php echo number_format($row['total'], 2); ?></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Revenue by Staff</h2>
        <table>
            <thead><tr><th>Staff</th><th>Transactions</th><th>Revenue</th></tr></thead>
            <tbody>
                <?php while($row = $revenueByStaff->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($row['name']); ?></td><td><?php echo $row['count']; ?></td><td>Kshs <?php echo number_format($row['total'], 2); ?></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Expenses by Category</h2>
        <table>
            <thead><tr><th>Category</th><th>Count</th><th>Amount</th></tr></thead>
            <tbody>
                <?php while($row = $expensesByCategory->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($row['category']); ?></td><td><?php echo $row['count']; ?></td><td>Kshs <?php echo number_format($row['total'], 2); ?></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Revenue by Payment Method</h2>
        <table>
            <thead><tr><th>Method</th><th>Count</th><th>Revenue</th></tr></thead>
            <tbody>
                <?php while($row = $paymentMethods->fetch_assoc()): ?>
                    <tr><td><?php echo ucfirst($row['payment_method']); ?></td><td><?php echo $row['count']; ?></td><td>Kshs <?php echo number_format($row['total'], 2); ?></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <button class="btn-print" onclick="printAndClose()">Print Report</button>
    <a href="reports.php" class="btn-back">Back to Reports</a>
</div>

<script>
    function printAndClose() {
        window.print();
        setTimeout(function() { window.close(); }, 500);
    }
</script>

</body>
</html>