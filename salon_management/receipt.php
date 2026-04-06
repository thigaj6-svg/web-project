<?php 
 $page_title = 'Receipt';
require_once 'config/database.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No transaction ID provided.");
}

 $conn = getConnection();
 $id = intval($_GET['id']);

// Fetch transaction details
 $sql = "SELECT t.*, s.name as staff_name 
        FROM transactions t 
        LEFT JOIN staff s ON t.staff_id = s.id 
        WHERE t.id = ?";
 $stmt = $conn->prepare($sql);
 $stmt->bind_param("i", $id);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Transaction not found.");
}

 $transaction = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?php echo $transaction['id']; ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .receipt-container {
            max-width: 300px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .receipt-header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
        }
        .receipt-header p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }
        .receipt-details {
            font-size: 12px;
            margin-bottom: 15px;
        }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .receipt-total {
            border-top: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
            padding: 10px 0;
            margin: 10px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .receipt-footer {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-top: 15px;
        }
        .status-paid {
            color: green;
            font-weight: bold;
        }
        .btn-print {
            display: block;
            width: 100%;
            margin-top: 20px;
            padding: 10px;
            background: #2D2D2D;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-back {
            display: block;
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            background: #C9A87C;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
        }
        
        @media print {
            body { background: white; padding: 0; }
            .receipt-container { box-shadow: none; width: 100%; max-width: 100%; }
            .btn-print, .btn-back { display: none; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="receipt-header">
        <h1>Glamour Salon</h1>
        <p>Nairobi, Kenya</p>
        <p>Tel: +254 717352485</p>
    </div>

    <div class="receipt-details">
        <div class="receipt-row">
            <span>Receipt #:</span>
            <span><?php echo $transaction['id']; ?></span>
        </div>
        <div class="receipt-row">
            <span>Date:</span>
            <span><?php echo date('d M Y H:i', strtotime($transaction['transaction_date'])); ?></span>
        </div>
        <div class="receipt-row">
            <span>Cashier:</span>
            <span><?php echo htmlspecialchars($transaction['staff_name'] ?? 'Admin'); ?></span>
        </div>
        <div class="receipt-row">
            <span>Customer:</span>
            <span><?php echo htmlspecialchars($transaction['client_name'] ?? 'Walk-in'); ?></span>
        </div>
    </div>

    <div style="border-top: 1px dashed #ccc; margin-bottom: 10px;"></div>

    <div class="receipt-details">
        <strong>Description:</strong><br>
        <?php echo htmlspecialchars($transaction['description']); ?>
    </div>

    <div class="receipt-row receipt-total">
        <span>TOTAL:</span>
        <span>Kshs <?php echo number_format($transaction['amount'], 2); ?></span>
    </div>

    <div class="receipt-details">
        <div class="receipt-row">
            <span>Payment Method:</span>
            <span><?php echo ucfirst($transaction['payment_method']); ?></span>
        </div>
        <div class="receipt-row">
            <span>Status:</span>
            <span class="status-paid"><?php echo ucfirst($transaction['status']); ?></span>
        </div>
    </div>

    <div class="receipt-footer">
        <p>Thank you for your business!</p>
        <p>We hope to see you again soon.</p>
    </div>

    <!-- Updated Button Logic -->
    <button class="btn-print" onclick="printAndClose()">Print Receipt</button>
    <a href="transactions.php" class="btn-back">Back to Transactions</a>
</div>

<script>
    function printAndClose() {
        window.print();
        // Attempt to close the window after printing
        // This works because we opened the receipt in a new tab
        setTimeout(function() {
            window.close();
        }, 500);
    }

    // Auto-print when the page loads (optional - remove if you want manual control)
    // window.onload = function() { window.print(); }
</script>

</body>
</html>