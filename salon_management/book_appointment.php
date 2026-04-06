<?php 
 $page_title = 'Book Appointment';
require_once 'includes/header.php';

 $conn = getConnection();
 $message = '';

// Handle Booking Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name = trim($_POST['client_name']);
    $client_phone = trim($_POST['client_phone']);
    $client_email = trim($_POST['client_email']);
    $service_id = intval($_POST['service_id']);
    $staff_id = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : NULL;
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO appointments (client_name, client_phone, client_email, service_id, staff_id, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiisss", $client_name, $client_phone, $client_email, $service_id, $staff_id, $appointment_date, $appointment_time, $notes);

    if ($stmt->execute()) {
        $message = 'Appointment booked successfully!';
    } else {
        $message = 'Error booking appointment: ' . $conn->error;
    }
}

// Get Services and Staff for dropdowns
 $services = $conn->query("SELECT id, name, price, duration FROM services WHERE status = 'available' ORDER BY name");
 $staff = $conn->query("SELECT id, name FROM staff WHERE status = 'active' ORDER BY name");
?>

<div class="page-header">
    <div class="page-header-content container">
        <h1>Book an Appointment</h1>
        <p>Schedule your next visit with us</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="form-container">
            <div class="form-card">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="POST" id="bookingForm">
                    <div class="form-group">
                        <label class="form-label">Your Name *</label>
                        <input type="text" name="client_name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="client_phone" class="form-input" placeholder="07XX XXX XXX" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="client_email" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Service *</label>
                        <select name="service_id" id="serviceSelect" class="form-select" required>
                            <option value="">Choose a service</option>
                            <?php while ($row = $services->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['price']; ?>">
                                    <?php echo htmlspecialchars($row['name']); ?> - <?php echo formatCurrency($row['price']); ?> (<?php echo $row['duration']; ?> mins)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Preferred Stylist</label>
                        <select name="staff_id" class="form-select">
                            <option value="">Any Available</option>
                            <?php while ($row = $staff->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Preferred Date *</label>
                        <input type="date" name="appointment_date" class="form-input" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Preferred Time *</label>
                        <input type="time" name="appointment_time" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-textarea" placeholder="Any special requests?"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Book Appointment</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php $conn->close(); ?>
<?php require_once 'includes/footer.php'; ?>