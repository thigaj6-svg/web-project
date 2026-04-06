<?php
 $page_title = 'Expenses Report';
require_once 'config/database.php';
require_once 'includes/auth.php';
requireAdmin();

 $conn = getConnection();

// Filters
 $category_filter = $_GET['category'] ?? '';
 $date_from = $_GET['date_from'] ?? '';
 $date_to = $_GET['date_to'] ?? '';

 $where = "1=1";
if ($category_filter) $where .= " AND category = '$category_filter'";
if ($date_from) $where .= " AND expense_date >= '$date_from'";
if ($date_to) $where .= " AND expense_date <= '$date_to'";

 $expenses = $conn->query("SELECT e.*, s.name as staff_name FROM expenses e LEFT JOIN staff s ON e.staff_id = s.id WHERE $where ORDER BY e.expense_date DESC");

 $totalExpenses = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expenses Report</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; margin: 0; padding: 20px; background: #f4f4f4; }
        .print-container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #666; }
        
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f4f4f4; }
        
        .total-row { font-weight: bold; background: #f9f9f9; }
        .btn-print { display: block; width: 100%; margin-top: 20px; padding: 10px; background: #2D2D2D; color: white; border: none; cursor: pointer; }
        .btn-back { display: block; width: 100%; margin-top: 10px; padding: 10px; background: #C9A87C; color: white; border: none; cursor: pointer; text-align: center; text-decoration: none; }

        @media print {
            body { background: white; padding: 0; }
            .print-container { box-shadow: none; width: 100%; }
            .btn-print, .btn-back { display: none; }
        }
    </style>
</head>
<body>

<div class="print-container">
    <div class="header">
        <h1>Glamour Salon</h1>
        <p>Expenses Report</p>
        <?php if($date_from || $date_to): ?>
            <p>Period: <?php echo $date_from ? date('M d, Y', strtotime($date_from)) : 'Start'; ?> to <?php echo $date_to ? date('M d, Y', strtotime($date_to)) : 'End'; ?></p>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th>Staff</th>
                <th style="text-align:right;">Amount (Kshs)</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $expenses->fetch_assoc()): 
                $totalExpenses += $row['amount'];
            ?>
                <tr>
                    <td><?php echo date('d M Y', strtotime($row['expense_date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['staff_name'] ?? '-'); ?></td>
                    <td style="text-align:right;"><?php echo number_format($row['amount'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">TOTAL EXPENSES:</td>
                <td style="text-align:right;"><?php echo number_format($totalExpenses, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <button class="btn-print" onclick="printAndClose()">Print Report</button>
    <a href="expenses.php" class="btn-back">Back to Expenses</a>
</div>

<script>
    function printAndClose() {
        window.print();
        setTimeout(function() { window.close(); }, 500);
    }
</script>

</body>
</html>