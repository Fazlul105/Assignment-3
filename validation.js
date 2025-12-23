
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('appointmentForm');
    
    
    const urlParams = new URLSearchParams(window.location.search);
    const messageDiv = document.getElementById('message');
    
    
    <?php if(isset($_SESSION['success'])): ?>
        showMessage('<?php echo addslashes($_SESSION['success']); ?>', 'success');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        showMessage('<?php echo addslashes($_SESSION['error']); ?>', 'error');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
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

function showMessage(message, type) {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
    messageDiv.style.display = 'block';
    
    
    setTimeout(function() {
        messageDiv.style.display = 'none';
    }, 5000);
}