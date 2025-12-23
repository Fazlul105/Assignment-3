

function editAppointment(id, date, mechanicId, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_date').value = date;
    document.getElementById('edit_mechanic').value = mechanicId;
    document.getElementById('edit_status').value = status;
    

    const modal = document.getElementById('editModal');
    modal.style.display = 'block';
    
    
    setTimeout(() => {
        document.getElementById('edit_date').focus();
    }, 100);
}

function closeModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'none';
}

function deleteAppointment(id) {
    
    if (!confirm('⚠️ Are you sure you want to delete this appointment?\n\nThis action cannot be undone!')) {
        return;
    }
    
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"></span> Deleting...';
    button.disabled = true;
    
    const formData = new FormData();
    formData.append('appointment_id', id);
    
    fetch('delete_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            
            showNotification('✅ ' + data.message, 'success');
            
            
            const row = button.closest('tr');
            row.style.transition = 'all 0.5s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(-100px)';
            
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showNotification('❌ ' + data.message, 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        showNotification('❌ Error deleting appointment', 'error');
        console.error('Error:', error);
        button.innerHTML = originalText;
        button.disabled = false;
    });
}


function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `message ${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.maxWidth = '400px';
    notification.style.animation = 'slideInRight 0.5s ease';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transition = 'all 0.5s ease';
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100px)';
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
}


window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeModal();
    }
}


document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});


document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'all 0.5s ease';
            message.style.opacity = '0';
            message.style.transform = 'translateY(-20px)';
            setTimeout(function() {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    });
    
    
    const rows = document.querySelectorAll('.appointments-table tbody tr');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        setTimeout(() => {
            row.style.transition = 'all 0.5s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 50);
    });
});


document.getElementById('editForm')?.addEventListener('submit', function(e) {
    const date = document.getElementById('edit_date').value;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const selectedDate = new Date(date);
    
    if (selectedDate < today) {
        e.preventDefault();
        showNotification('❌ Date cannot be in the past', 'error');
        return false;
    }
    
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<span class="loading"></span> Updating...';
    submitBtn.disabled = true;
});


const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);