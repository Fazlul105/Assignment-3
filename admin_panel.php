<?php
require_once 'config.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$conn = getDBConnection();


$query = "SELECT a.id, a.client_name, a.address, a.phone, a.car_license, 
          a.car_engine, a.appointment_date, a.status, a.created_at,
          m.name as mechanic_name, m.id as mechanic_id, m.specialization
          FROM appointments a
          JOIN mechanics m ON a.mechanic_id = m.id
          ORDER BY a.appointment_date DESC, a.created_at DESC";

$appointments = $conn->query($query);


$mechanics = $conn->query("SELECT id, name, specialization FROM mechanics WHERE status = 'active'");


$statsQuery = "SELECT 
    COUNT(*) as total_appointments,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN appointment_date = CURDATE() THEN 1 ELSE 0 END) as today
    FROM appointments";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Car Workshop</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border-radius: 15px;
            color: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .stat-card.info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .stat-number {
            font-size: 3em;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 1em;
            opacity: 0.9;
            font-weight: 600;
        }
        
        .appointment-details {
            font-size: 0.9em;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-header">
            <h1>🛠️ Admin Dashboard</h1>
            <div class="admin-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn-logout">🚪 Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Dashboard -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_appointments']; ?></div>
                <div class="stat-label">Total Appointments</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card success">
                <div class="stat-number"><?php echo $stats['confirmed']; ?></div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-card info">
                <div class="stat-number"><?php echo $stats['today']; ?></div>
                <div class="stat-label">Today's Appointments</div>
            </div>
        </div>

        <div class="admin-panel">
            <h2>📋 Appointment Management</h2>
            
            <?php if ($appointments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Client Name</th>
                                <th>Phone</th>
                                <th>Vehicle Info</th>
                                <th>Date</th>
                                <th>Mechanic</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $row['id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['client_name']); ?></strong>
                                    </td>
                                    <td>📞 <?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td>
                                        <div class="appointment-details">
                                            🚗 <?php echo htmlspecialchars($row['car_license']); ?><br>
                                            <small>Engine: <?php echo htmlspecialchars($row['car_engine']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></strong><br>
                                        <small style="color: #6b7280;">Booked: <?php echo date('M d', strtotime($row['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['mechanic_name']); ?></strong><br>
                                        <small style="color: #6b7280;"><?php echo htmlspecialchars($row['specialization']); ?></small>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo $row['status']; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick="editAppointment(<?php echo $row['id']; ?>, 
                                                '<?php echo $row['appointment_date']; ?>', 
                                                <?php echo $row['mechanic_id']; ?>,
                                                '<?php echo $row['status']; ?>')" 
                                                class="btn-edit">✏️ Edit</button>
                                        
                                        <button onclick="deleteAppointment(<?php echo $row['id']; ?>)" 
                                                class="btn-delete">🗑️ Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data">No appointments found. Waiting for customers to book!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>✏️ Edit Appointment</h3>
            <form id="editForm" method="POST" action="update_appointment.php">
                <input type="hidden" id="edit_id" name="appointment_id">
                
                <div class="form-group">
                    <label for="edit_date">📅 Appointment Date</label>
                    <input type="date" id="edit_date" name="appointment_date" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_mechanic">👨‍🔧 Assign Mechanic</label>
                    <select id="edit_mechanic" name="mechanic_id" required>
                        <?php 
                        $mechanics->data_seek(0);
                        while($mech = $mechanics->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $mech['id']; ?>">
                                <?php echo $mech['name']; ?> - <?php echo $mech['specialization']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">📊 Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-submit">💾 Update Appointment</button>
            </form>
        </div>
    </div>

    <script src="admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>