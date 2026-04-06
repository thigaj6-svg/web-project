<?php 
 $page_title = 'Appointments';
require_once 'includes/header.php';
requireAdmin();

 $conn = getConnection();
 $message = '';

// Handle Status Update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if (in_array($action, ['completed', 'cancelled', 'scheduled'])) {
        $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $id);
        $stmt->execute();
        $message = "Appointment status updated to " . ucfirst($action);
    }
}

// Fetch Appointments
 $filter = $_GET['filter'] ?? 'all';
 $where = "1=1";
if ($filter === 'upcoming') $where .= " AND (status = 'scheduled' AND appointment_date >= CURDATE())";
if ($filter === 'completed') $where .= " AND status = 'completed'";
if ($filter === 'cancelled') $where .= " AND status = 'cancelled'";

 $sql = "SELECT a.*, s.name as service_name, st.name as staff_name 
        FROM appointments a 
        LEFT JOIN services s ON a.service_id = s.id 
        LEFT JOIN staff st ON a.staff_id = st.id 
        WHERE $where 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

 $appointments = $conn->query($sql);
?>

<div class="page-header">
    <div class="page-header-content container">
        <h1>Appointments</h1>
        <p>Manage client bookings</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card mb-2" style="padding: 1rem;">
            <a href="appointments.php?filter=all" class="btn btn-sm <?php echo $filter == 'all' ? 'btn-primary' : 'btn-outline'; ?>">All</a>
            <a href="appointments.php?filter=upcoming" class="btn btn-sm <?php echo $filter == 'upcoming' ? 'btn-primary' : 'btn-outline'; ?>">Upcoming</a>
            <a href="appointments.php?filter=completed" class="btn btn-sm <?php echo $filter == 'completed' ? 'btn-primary' : 'btn-outline'; ?>">Completed</a>
            <a href="appointments.php?filter=cancelled" class="btn btn-sm <?php echo $filter == 'cancelled' ? 'btn-primary' : 'btn-outline'; ?>">Cancelled</a>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3>Booking List</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Stylist</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['client_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($row['client_phone']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['service_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['staff_name'] ?? 'Any'); ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($row['appointment_date'])); ?><br>
                                    <small><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></small>
                                </td>
                                <td>
                                    <span class="status-badge 
                                        <?php 
                                            if($row['status'] == 'scheduled') echo 'status-pending'; 
                                            elseif($row['status'] == 'completed') echo 'status-active'; 
                                            else echo 'status-inactive'; 
                                        ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'scheduled'): ?>
                                        <a href="?action=completed&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Complete</a>
                                        <a href="?action=cancelled&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger">Cancel</a>
                                    <?php elseif ($row['status'] === 'cancelled'): ?>
                                        <a href="?action=scheduled&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline">Rebook</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php $conn->close(); ?>
<?php require_once 'includes/footer.php'; ?>