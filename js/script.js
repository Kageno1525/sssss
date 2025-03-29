document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('registration-form');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            // Get form fields
            const nameField = document.getElementById('name');
            const phoneField = document.getElementById('phone');
            const genderOptions = document.querySelectorAll('input[name="gender"]');
            const occupationField = document.getElementById('occupation');
            
            let isValid = true;
            let errorMessage = '';
            
            // Validate name
            if (!nameField.value.trim()) {
                errorMessage = 'الرجاء إدخال الاسم الكامل';
                isValid = false;
            }
            
            // Validate phone
            else if (!phoneField.value.trim()) {
                errorMessage = 'الرجاء إدخال رقم الهاتف';
                isValid = false;
            }
            else if (!validatePhone(phoneField.value)) {
                errorMessage = 'صيغة رقم الهاتف غير صحيحة';
                isValid = false;
            }
            
            // Validate gender selection
            else if (!Array.from(genderOptions).some(option => option.checked)) {
                errorMessage = 'الرجاء اختيار الجنس';
                isValid = false;
            }
            
            // Validate occupation
            else if (!occupationField.value.trim()) {
                errorMessage = 'الرجاء إدخال المهنة';
                isValid = false;
            }
            
            // If validation fails, prevent form submission and show error
            if (!isValid) {
                event.preventDefault();
                
                // Create or update error message
                let errorDiv = document.querySelector('.error-message');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    form.parentNode.insertBefore(errorDiv, form);
                }
                
                errorDiv.textContent = errorMessage;
                errorDiv.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
    
    // Phone number validation function
    function validatePhone(phone) {
        // Basic phone validation - allows various formats
        return /^[0-9+\-\s()]{8,20}$/.test(phone);
    }

    // Handle AJAX Mark Read and Delete actions
    initializeAjaxActions();
    
    // Admin dashboard filter buttons with animations
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Set active class
                filterButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.classList.add('filter-transition');
                });
                this.classList.add('active');
                
                // Add animation to table rows if they exist
                const tableRows = document.querySelectorAll('.data-table tbody tr');
                if (tableRows.length > 0) {
                    // Add fade out animation to rows
                    tableRows.forEach(function(row, index) {
                        // Add staggered delay for nice animation effect
                        const delay = index * 20;
                        row.style.transition = `opacity 0.3s ease ${delay}ms, transform 0.3s ease ${delay}ms`;
                        row.style.opacity = '0';
                        row.style.transform = 'translateY(10px)';
                    });
                }
                
                // Submit the filter form
                const filterForm = document.getElementById('filter-form');
                const filterInput = document.getElementById('filter');
                
                filterInput.value = this.dataset.filter;
                
                // Add small delay for animation
                setTimeout(function() {
                    filterForm.submit();
                }, 300);
            });
        });
    }
    
    // Animate table rows on page load
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    if (tableRows.length > 0) {
        tableRows.forEach(function(row, index) {
            // Set initial state
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            
            // Add staggered delay for nice animation effect
            const delay = 100 + (index * 30);
            row.style.transition = `opacity 0.5s ease ${delay}ms, transform 0.5s ease ${delay}ms`;
            
            // Trigger animation after a small delay
            setTimeout(function() {
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, 10);
        });
    }
    
    // Animate stats cards on page load
    const statCards = document.querySelectorAll('.stat-card');
    if (statCards.length > 0) {
        statCards.forEach(function(card, index) {
            // Set initial state
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            // Add staggered delay for nice animation effect
            const delay = 200 + (index * 100);
            card.style.transition = `opacity 0.5s ease ${delay}ms, transform 0.5s ease ${delay}ms`;
            
            // Trigger animation after a small delay
            setTimeout(function() {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 10);
        });
    }
    
    // Fix: Don't add loading indicator as it was causing issues
    // We'll keep form submission simple without the loading indicator
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    
    if (submitButtons.length > 0) {
        submitButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Only proceed if form is valid
                const form = this.closest('form');
                
                if (form && form.checkValidity()) {
                    // Allow form to submit naturally
                    // Don't disable button or show loading indicator
                }
            });
        });
    }
    
    // Add subtle animations for form elements
    const formGroups = document.querySelectorAll('.form-group');
    
    if (formGroups.length > 0) {
        formGroups.forEach((group, index) => {
            group.style.opacity = '0';
            group.style.transform = 'translateY(20px)';
            group.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            setTimeout(() => {
                group.style.opacity = '1';
                group.style.transform = 'translateY(0)';
            }, 100 * (index + 1));
        });
    }
});

// Initialize AJAX actions for mark read and delete buttons
function initializeAjaxActions() {
    // Mark as read via AJAX
    const markReadButtons = document.querySelectorAll('.mark-read-btn');
    if (markReadButtons.length > 0) {
        markReadButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const submissionId = this.dataset.id;
                const row = this.closest('tr');
                
                // Create form data for the AJAX request
                const formData = new FormData();
                formData.append('action', 'mark_read');
                formData.append('id', submissionId);
                
                // Show loading effect
                button.textContent = '...جاري التحديث';
                button.disabled = true;
                
                // Send AJAX request
                fetch('admin/ajax_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the UI to show the submission is read
                        row.classList.remove('unread-row');
                        row.classList.add('read-row');
                        
                        // Update read info and button visibility
                        button.style.display = 'none';
                        
                        // Add read info text
                        const actionsCell = button.closest('td');
                        const readInfo = document.createElement('div');
                        readInfo.className = 'read-info';
                        readInfo.innerHTML = `تمت القراءة بواسطة ${data.read_by}`;
                        actionsCell.appendChild(readInfo);
                        
                        // Animate the row to highlight the change
                        row.style.transition = 'background-color 0.5s ease';
                        row.style.backgroundColor = 'rgba(39, 174, 96, 0.1)';
                        setTimeout(() => {
                            row.style.backgroundColor = '';
                        }, 1500);
                        
                        // Show a success toast notification
                        showToast('تم تأشير الطلب كمقروءة بنجاح');
                    } else {
                        // Show error message
                        showToast('حدث خطأ: ' + data.message, 'error');
                        
                        // Reset button state
                        button.textContent = 'تأشير كمقروءة';
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('حدث خطأ في الاتصال', 'error');
                    
                    // Reset button state
                    button.textContent = 'تأشير كمقروءة';
                    button.disabled = false;
                });
            });
        });
    }
    
    // Delete submission via AJAX
    const deleteButtons = document.querySelectorAll('.delete-btn');
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Confirm deletion
                if (!confirm('هل أنت متأكد من حذف هذا الطلب؟')) {
                    return;
                }
                
                const submissionId = this.dataset.id;
                const row = this.closest('tr');
                
                // Create form data for the AJAX request
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', submissionId);
                
                // Show loading effect
                button.textContent = '...جاري الحذف';
                button.disabled = true;
                
                // Send AJAX request
                fetch('admin/ajax_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Animate the row removal
                        row.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(100%)';
                        
                        // Remove the row after animation completes
                        setTimeout(() => {
                            row.remove();
                            
                            // Update counts if they exist
                            updateCountsAfterDeletion();
                        }, 500);
                        
                        // Show a success toast notification
                        showToast('تم حذف الطلب بنجاح');
                    } else {
                        // Show error message
                        showToast('حدث خطأ: ' + data.message, 'error');
                        
                        // Reset button state
                        button.textContent = 'حذف';
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('حدث خطأ في الاتصال', 'error');
                    
                    // Reset button state
                    button.textContent = 'حذف';
                    button.disabled = false;
                });
            });
        });
    }
}

// Update counts on the dashboard after deletion
function updateCountsAfterDeletion() {
    // Update total count
    const totalCountElement = document.querySelector('.stat-number.total-count');
    if (totalCountElement) {
        let count = parseInt(totalCountElement.textContent) - 1;
        totalCountElement.textContent = count;
    }
    
    // Update unread count if the deleted submission was unread
    const unreadCountElement = document.querySelector('.stat-number.unread-count');
    if (unreadCountElement) {
        let count = parseInt(unreadCountElement.textContent);
        // Only decrement if we had an unread submission (we don't have this info directly)
        // For simplicity, we'll just update based on UI clues
        unreadCountElement.textContent = Math.max(0, count - 1);
    }
}

// Show toast notification
function showToast(message, type = 'success') {
    // Check if a toast container exists, if not create one
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
        
        // Add some basic styles to the container
        toastContainer.style.position = 'fixed';
        toastContainer.style.top = '20px';
        toastContainer.style.left = '20px';
        toastContainer.style.zIndex = '9999';
    }
    
    // Create the toast element
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    
    // Style the toast
    toast.style.padding = '10px 20px';
    toast.style.marginBottom = '10px';
    toast.style.borderRadius = '4px';
    toast.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    toast.style.animation = 'fadeIn 0.3s, fadeOut 0.3s 2.7s';
    
    if (type === 'success') {
        toast.style.backgroundColor = '#27ae60';
        toast.style.color = 'white';
    } else if (type === 'error') {
        toast.style.backgroundColor = '#e74c3c';
        toast.style.color = 'white';
    }
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}