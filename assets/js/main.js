// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-password');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '👁️';
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁';
    }
}

// Show loan repayment modal
function showLoanRepayment(loanId) {
    const modal = document.getElementById('loanModal');
    if (modal) {
        modal.classList.add('active');
        // You can fetch loan data here using loanId
        console.log('Loading repayment data for loan:', loanId);
    }
}

// Close loan repayment modal
function closeLoanModal() {
    const modal = document.getElementById('loanModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('loanModal');
    if (event.target === modal) {
        closeLoanModal();
    }
}

// Print loan schedule
function printLoanSchedule() {
    const modal = document.getElementById('loanModal');
    if (modal) {
        // Store original styles
        const originalDisplay = modal.style.display;
        const originalPosition = modal.style.position;
        const originalLeft = modal.style.left;
        const originalTop = modal.style.top;
        const originalWidth = modal.style.width;
        const originalHeight = modal.style.height;
        const originalZIndex = modal.style.zIndex;
        const originalBackground = modal.style.background;
        
        // Make modal visible and full screen for printing
        modal.style.display = 'block';
        modal.style.position = 'absolute';
        modal.style.left = '0';
        modal.style.top = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.zIndex = '9999';
        modal.style.background = 'white';
        
        // Hide close button and print button
        const closeBtn = modal.querySelector('.close-btn');
        const printBtn = modal.querySelector('.print-btn');
        const modalHeader = modal.querySelector('.modal-header');
        
        if (closeBtn) closeBtn.style.display = 'none';
        if (printBtn) printBtn.style.display = 'none';
        if (modalHeader) modalHeader.style.display = 'none';
        
        // Print
        window.print();
        
        // Restore original styles after printing
        setTimeout(() => {
            modal.style.display = originalDisplay;
            modal.style.position = originalPosition;
            modal.style.left = originalLeft;
            modal.style.top = originalTop;
            modal.style.width = originalWidth;
            modal.style.height = originalHeight;
            modal.style.zIndex = originalZIndex;
            modal.style.background = originalBackground;
            
            if (closeBtn) closeBtn.style.display = '';
            if (printBtn) printBtn.style.display = '';
            if (modalHeader) modalHeader.style.display = '';
        }, 100);
    }
}

// Toggle filters panel
function toggleFilters() {
    const filtersPanel = document.getElementById('filtersPanel');
    const filtersBtn = document.querySelector('.filters-btn');
    const filterIcon = document.getElementById('filterIcon');
    
    if (filtersPanel && filtersBtn) {
        filtersPanel.classList.toggle('active');
        filtersBtn.classList.toggle('active');
    }
}

// Filter table based on search and filters
function filterTable() {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('customersTable');
    const rows = table.querySelectorAll('tbody tr');
    const searchTerm = searchInput.value.toLowerCase();
    
    // Get selected status filters
    const statusFilters = Array.from(document.querySelectorAll('.status-filter:checked')).map(cb => cb.value);
    
    // Get sort option
    const sortBy = document.getElementById('sortBy').value;
    
    let visibleRows = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return;
        
        const name = cells[1]?.textContent.toLowerCase() || '';
        const loanId = cells[2]?.textContent.toLowerCase() || '';
        const email = cells[3]?.textContent.toLowerCase() || '';
        const account = cells[4]?.textContent.toLowerCase() || '';
        
        // Get status
        const statusElement = row.querySelector('.status');
        const status = statusElement ? statusElement.textContent.toLowerCase().trim() : '';
        
        // Check search term
        const matchesSearch = !searchTerm || 
            name.includes(searchTerm) || 
            loanId.includes(searchTerm) || 
            email.includes(searchTerm) || 
            account.includes(searchTerm);
        
        // Check status filter
        const matchesStatus = statusFilters.length === 0 || 
            (status.includes('overdue') && statusFilters.includes('overdue')) ||
            (status.includes('pending') && statusFilters.includes('pending')) ||
            (status.includes('progress') && statusFilters.includes('progress'));
        
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            visibleRows.push({row: row, name: name, loanId: loanId, email: email, account: account, status: status});
        } else {
            row.style.display = 'none';
        }
    });
    
    // Sort rows if needed
    if (sortBy && visibleRows.length > 0) {
        visibleRows.sort((a, b) => {
            let aVal, bVal;
            switch(sortBy) {
                case 'name': aVal = a.name; bVal = b.name; break;
                case 'loanid': aVal = a.loanId; bVal = b.loanId; break;
                case 'email': aVal = a.email; bVal = b.email; break;
                case 'account': aVal = a.account; bVal = b.account; break;
                case 'status': aVal = a.status; bVal = b.status; break;
                default: return 0;
            }
            return aVal.localeCompare(bVal);
        });
        
        // Reorder rows in DOM
        const tbody = table.querySelector('tbody');
        visibleRows.forEach(item => {
            tbody.appendChild(item.row);
        });
    }
}

// Clear all filters
function clearFilters() {
    // Clear search
    document.getElementById('searchInput').value = '';
    
    // Clear status filters
    document.querySelectorAll('.status-filter').forEach(cb => cb.checked = false);
    
    // Reset sort
    document.getElementById('sortBy').value = 'name';
    
    // Reapply filters
    filterTable();
}

// Toggle user menu
function toggleUserMenu() {
    const userProfile = document.querySelector('.user-profile');
    if (userProfile) {
        userProfile.classList.toggle('active');
    }
}

// Close user menu when clicking outside
document.addEventListener('click', function(event) {
    const userProfile = document.querySelector('.user-profile');
    const userMenu = document.getElementById('userMenu');
    const filtersPanel = document.getElementById('filtersPanel');
    const filtersBtn = document.querySelector('.filters-btn');
    
    // Close user menu
    if (userProfile && userMenu && !userProfile.contains(event.target)) {
        userProfile.classList.remove('active');
    }
    
    // Close filters panel when clicking outside
    if (filtersPanel && filtersBtn && 
        !filtersPanel.contains(event.target) && 
        !filtersBtn.contains(event.target)) {
        filtersPanel.classList.remove('active');
        filtersBtn.classList.remove('active');
    }
});

// Handle login form submission
document.addEventListener('DOMContentLoaded', function() {
    // Form will submit normally to server for proper authentication
    
    // Auto-hide flash messages after 5 seconds
    const flashMessage = document.querySelector('.flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
});

