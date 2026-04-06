<?php 
 $page_title = 'Admin Management';
require_once 'includes/header.php';
requireAdmin();

 $conn = getConnection();
 $message = '';

// Handle Staff Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_staff') {
        $name = trim($_POST['name']);
        $position = trim($_POST['position']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $salary = floatval($_POST['salary']);
        $hire_date = $_POST['hire_date'];
        
        $stmt = $conn->prepare("INSERT INTO staff (name, position, phone, email, salary, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssds", $name, $position, $phone, $email, $salary, $hire_date);
        
        if ($stmt->execute()) {
            $message = 'Staff member added successfully!';
        } else {
            $message = 'Error adding staff member.';
        }
    }
    
    if ($action === 'edit_staff') {
        $id = intval($_POST['staff_id']);
        $name = trim($_POST['name']);
        $position = trim($_POST['position']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $salary = floatval($_POST['salary']);
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE staff SET name=?, position=?, phone=?, email=?, salary=?, status=? WHERE id=?");
        $stmt->bind_param("ssssdsi", $name, $position, $phone, $email, $salary, $status, $id);
        
        if ($stmt->execute()) {
            $message = 'Staff member updated successfully!';
        }
    }
    
    if ($action === 'delete_staff') {
        $id = intval($_POST['staff_id']);
        $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = 'Staff member deleted successfully!';
        }
    }
}

// Get all staff
 $staffResult = $conn->query("SELECT * FROM staff ORDER BY created_at DESC");
?>

<div class="page-header">
    <div class="page-header-content container">
        <h1>Admin Management</h1>
        <p>Manage salon staff and operations</p>
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span>/</span>
            <span>Admin</span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Staff Management -->
        <div class="table-container">
            <div class="table-header">
                <h3>Staff Management</h3>
                <button class="btn btn-primary" onclick="openModal('addStaffModal')">Add New Staff</button>
            </div>
            <div class="table-responsive">
                <table class="data-table" id="staffTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Phone</th>
                            <th>Salary</th>
                            <th>Hire Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $staffResult->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['position']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td class="currency"><?php echo formatCurrency($row['salary']); ?></td>
                                <td><?php echo formatDate($row['hire_date']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="editStaff(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete('this staff member')">
                                        <input type="hidden" name="action" value="delete_staff">
                                        <input type="hidden" name="staff_id" value="<?php echo $row['id']; ?>">
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

<!-- Add Staff Modal -->
<div class="modal-overlay" id="addStaffModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Staff Member</h3>
            <button class="modal-close" onclick="closeModal('addStaffModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_staff">
                
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Position *</label>
                    <select name="position" class="form-select" required>
                        <option value="">Select Position</option>
                        <option value="Senior Stylist">Senior Stylist</option>
                        <option value="Stylist">Stylist</option>
                        <option value="Barber">Barber</option>
                        <option value="Nail Technician">Nail Technician</option>
                        <option value="Beautician">Beautician</option>
                        <option value="Receptionist">Receptionist</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-input" placeholder="07XX XXX XXX">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Monthly Salary (Kshs) *</label>
                    <input type="number" name="salary" class="form-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hire Date *</label>
                    <input type="date" name="hire_date" class="form-input" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addStaffModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Staff</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal-overlay" id="editStaffModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Staff Member</h3>
            <button class="modal-close" onclick="closeModal('editStaffModal')">&times;</button>
        </div>
        <form method="POST" id="editStaffForm">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_staff">
                <input type="hidden" name="staff_id" id="edit_staff_id">
                
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Position *</label>
                    <input type="text" name="position" id="edit_position" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" id="edit_phone" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Monthly Salary (Kshs) *</label>
                    <input type="number" name="salary" id="edit_salary" class="form-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="edit_status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editStaffModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Staff</button>
            </div>
        </form>
    </div>
</div>

<script>
function editStaff(staff) {
    document.getElementById('edit_staff_id').value = staff.id;
    document.getElementById('edit_name').value = staff.name;
    document.getElementById('edit_position').value = staff.position;
    document.getElementById('edit_phone').value = staff.phone || '';
    document.getElementById('edit_email').value = staff.email || '';
    document.getElementById('edit_salary').value = staff.salary;
    document.getElementById('edit_status').value = staff.status;
    openModal('editStaffModal');
}
</script>

<?php $conn->close(); ?>
<?php require_once 'includes/footer.php'; ?>
