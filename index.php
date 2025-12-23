<?php
require_once 'config.php';


$message = '';
$messageType = '';

if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $messageType = 'success';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $messageType = 'error';
    unset($_SESSION['error']);
}


$conn = getDBConnection();
$today = date('Y-m-d');

$mechanicsQuery = "SELECT m.id, m.name, m.specialization, m.max_appointments,
    COUNT(a.id) as current_appointments
    FROM mechanics m
    LEFT JOIN appointments a ON m.id = a.mechanic_id 
    AND a.appointment_date = '$today' 
    AND a.status != 'cancelled'
    WHERE m.status = 'active'
    GROUP BY m.id";

$mechanicsResult = $conn->query($mechanicsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Workshop - Book Your Appointment</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🔧 Professional Car Workshop</h1>
            <p>Book your appointment with expert mechanics - Fast, Reliable, Trusted</p>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="appointment-form">
            <h2>📋 Book Your Appointment</h2>
            <p style="color: #6b7280; margin-bottom: 30px;">Fill in your details below and choose your preferred mechanic. We'll take care of the rest!</p>
            
            <form id="appointmentForm" method="POST" action="process_appointment.php">
                <div class="form-group">
                    <label for="client_name">👤 Full Name *</label>
                    <input type="text" id="client_name" name="client_name" 
                           placeholder="Enter your full name" required>
                    <span class="error" id="nameError"></span>
                </div>

                <div class="form-group">
                    <label for="address">🏠 Address *</label>
                    <textarea id="address" name="address" rows="3" 
                              placeholder="Enter your complete address" required></textarea>
                    <span class="error" id="addressError"></span>
                </div>

                <div class="form-group">
                    <label for="phone">📱 Phone Number *</label>
                    <input type="text" id="phone" name="phone" 
                           placeholder="Enter 10-15 digit phone number" required>
                    <span class="error" id="phoneError"></span>
                </div>

                <div class="form-group">
                    <label for="car_license">🚗 Car License Number *</label>
                    <input type="text" id="car_license" name="car_license" 
                           placeholder="e.g., ABC-1234" required>
                    <span class="error" id="licenseError"></span>
                </div>

                <div class="form-group">
                    <label for="car_engine">⚙️ Car Engine Number *</label>
                    <input type="text" id="car_engine" name="car_engine" 
                           placeholder="Enter alphanumeric engine number" required>
                    <span class="error" id="engineError"></span>
                </div>

                <div class="form-group">
                    <label for="appointment_date">📅 Appointment Date *</label>
                    <input type="date" id="appointment_date" name="appointment_date" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                    <span class="error" id="dateError"></span>
                </div>

                <div class="form-group">
                    <label for="mechanic_id">👨‍🔧 Select Your Preferred Mechanic *</label>
                    <select id="mechanic_id" name="mechanic_id" required>
                        <option value="">-- Choose a Mechanic --</option>
                        <?php while($mechanic = $mechanicsResult->fetch_assoc()): ?>
                            <?php 
                            $available = $mechanic['max_appointments'] - $mechanic['current_appointments'];
                            $statusIcon = $available > 0 ? '✅' : '❌';
                            ?>
                            <option value="<?php echo $mechanic['id']; ?>" 
                                    data-available="<?php echo $available; ?>"
                                    <?php echo $available <= 0 ? 'disabled' : ''; ?>>
                                <?php echo $statusIcon; ?> <?php echo $mechanic['name']; ?> - <?php echo $mechanic['specialization']; ?>
                                (<?php echo $available; ?> slots available today)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <span class="error" id="mechanicError"></span>
                </div>

                <button type="submit" class="btn-submit">🎯 Book My Appointment</button>
                <a href="admin_login.php" class="admin-link">🔐 Admin Login →</a>
            </form>
        </div>
    </div>

    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('appointmentForm');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateForm()) {
                        e.preventDefault();
                    }
                });
                
                
                document.getElementById('client_name').addEventListener('blur', validateName);
                document.getElementById('phone').addEventListener('blur', validatePhone);
                document.getElementById('car_engine').addEventListener('blur', validateEngine);
                document.getElementById('appointment_date').addEventListener('change', validateDate);
                document.getElementById('mechanic_id').addEventListener('change', validateMechanic);
            }
        });

        function validateForm() {
            let isValid = true;
            
            if (!validateName()) isValid = false;
            if (!validatePhone()) isValid = false;
            if (!validateEngine()) isValid = false;
            if (!validateDate()) isValid = false;
            if (!validateMechanic()) isValid = false;
            
            return isValid;
        }

        function validateName() {
            const name = document.getElementById('client_name').value.trim();
            const error = document.getElementById('nameError');
            const namePattern = /^[a-zA-Z ]{2,100}$/;
            
            if (name === '') {
                error.textContent = 'Name is required';
                return false;
            } else if (!namePattern.test(name)) {
                error.textContent = 'Name should contain only letters and spaces';
                return false;
            } else {
                error.textContent = '';
                return true;
            }
        }

        function validatePhone() {
            const phone = document.getElementById('phone').value.trim();
            const error = document.getElementById('phoneError');
            const phonePattern = /^[0-9]{10,15}$/;
            
            if (phone === '') {
                error.textContent = 'Phone number is required';
                return false;
            } else if (!phonePattern.test(phone)) {
                error.textContent = 'Phone should contain 10-15 digits only';
                return false;
            } else {
                error.textContent = '';
                return true;
            }
        }

        function validateEngine() {
            const engine = document.getElementById('car_engine').value.trim();
            const error = document.getElementById('engineError');
            const enginePattern = /^[A-Z0-9]{5,20}$/i;
            
            if (engine === '') {
                error.textContent = 'Car engine number is required';
                return false;
            } else if (!enginePattern.test(engine)) {
                error.textContent = 'Engine number should be alphanumeric (5-20 characters)';
                return false;
            } else {
                error.textContent = '';
                return true;
            }
        }

        function validateDate() {
            const date = document.getElementById('appointment_date').value;
            const error = document.getElementById('dateError');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const selectedDate = new Date(date);
            
            if (date === '') {
                error.textContent = 'Appointment date is required';
                return false;
            } else if (selectedDate < today) {
                error.textContent = 'Date cannot be in the past';
                return false;
            } else {
                error.textContent = '';
                return true;
            }
        }

        function validateMechanic() {
            const mechanic = document.getElementById('mechanic_id').value;
            const error = document.getElementById('mechanicError');
            
            if (mechanic === '') {
                error.textContent = 'Please select a mechanic';
                return false;
            } else {
                error.textContent = '';
                return true;
            }
        }

        
        setTimeout(function() {
            const message = document.querySelector('.message');
            if (message) {
                message.style.transition = 'all 0.5s ease';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-20px)';
                setTimeout(() => message.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>
<?php $conn->close(); ?>