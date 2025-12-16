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
    if (!table) return;
    
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    
    // Get selected status filters (if they exist)
    const statusFilters = Array.from(document.querySelectorAll('.status-filter:checked')).map(cb => cb.value);
    
    // Get sort option (if it exists)
    const sortByEl = document.getElementById('sortBy');
    const sortBy = sortByEl ? sortByEl.value : '';
    
    let visibleRows = [];
    let hiddenRows = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return;
        
        // New table structure: Office, Credit Account, Customer Account, Date, etc.
        const office = cells[0]?.textContent.toLowerCase() || '';
        const creditAccount = cells[1]?.textContent.toLowerCase() || '';
        const customerAccount = cells[2]?.textContent.toLowerCase() || '';
        const customerName = cells[8]?.textContent.toLowerCase() || '';
        const phone = cells[15]?.textContent.toLowerCase() || '';
        const address = cells[16]?.textContent.toLowerCase() || '';
        
        // Check search term against all searchable fields
        const matchesSearch = !searchTerm || 
            office.includes(searchTerm) || 
            creditAccount.includes(searchTerm) || 
            customerAccount.includes(searchTerm) ||
            customerName.includes(searchTerm) ||
            phone.includes(searchTerm) ||
            address.includes(searchTerm);
        
        // Status filter (if filters panel exists)
        const matchesStatus = statusFilters.length === 0;
        
        if (matchesSearch && matchesStatus) {
            visibleRows.push({
                row: row, 
                office: office,
                creditAccount: creditAccount,
                customerAccount: customerAccount,
                customerName: customerName
            });
        } else {
            hiddenRows.push(row);
        }
    });
    
    // Sort visible rows if needed
    if (sortBy && visibleRows.length > 0) {
        visibleRows.sort((a, b) => {
            let aVal, bVal;
            switch(sortBy) {
                case 'name': aVal = a.customerName; bVal = b.customerName; break;
                case 'loanid': aVal = a.creditAccount; bVal = b.creditAccount; break;
                case 'account': aVal = a.customerAccount; bVal = b.customerAccount; break;
                default: return 0;
            }
            return aVal.localeCompare(bVal);
        });
    }
    
    // Reorder rows in DOM: first visible (sorted), then hidden
    const tbody = table.querySelector('tbody');
    if (tbody) {
        // Clear tbody
        tbody.innerHTML = '';
        
        // Append visible rows (sorted)
        visibleRows.forEach(item => {
            item.row.style.display = '';
            tbody.appendChild(item.row);
        });
        
        // Append hidden rows
        hiddenRows.forEach(row => {
            row.style.display = 'none';
            tbody.appendChild(row);
        });
    }
}

// Apply filters and close panel
function applyFilters() {
    // Apply the filters
    filterTable();
    
    // Close the filters panel
    toggleFilters();
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

// Toggle page rows dropdown
function togglePageRowsDropdown(event) {
    if (event) {
        event.stopPropagation();
    }
    const wrapper = document.querySelector('.pagination-select-wrapper');
    if (wrapper) {
        wrapper.classList.toggle('active');
    }
}

// Select page rows
function selectPageRows(value, event) {
    if (event) {
        event.stopPropagation();
    }
    const selectValue = document.querySelector('.pagination-select-value');
    const dropdown = document.getElementById('pageRowsDropdown');
    
    if (!dropdown) {
        console.error('Dropdown not found');
        return;
    }
    
    const items = dropdown.querySelectorAll('.pagination-dropdown-item');
    
    if (selectValue) {
        selectValue.textContent = value;
    }
    
    // Update active state
    items.forEach(item => {
        item.classList.remove('active');
        if (item.textContent.trim() === value.toString()) {
            item.classList.add('active');
        }
    });
    
    // Close dropdown
    const wrapper = document.querySelector('.pagination-select-wrapper');
    if (wrapper) {
        wrapper.classList.remove('active');
    }
    
    // Here you can add logic to update the table pagination
    console.log('Page rows changed to:', value);
}


// Close user menu when clicking outside
document.addEventListener('click', function(event) {
    const userProfile = document.querySelector('.user-profile');
    const userMenu = document.getElementById('userMenu');
    const filtersPanel = document.getElementById('filtersPanel');
    const filtersBtn = document.querySelector('.filters-btn');
    const pageRowsWrapper = document.querySelector('.pagination-select-wrapper');
    
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
    
    // Close page rows dropdown when clicking outside
    if (pageRowsWrapper && !pageRowsWrapper.contains(event.target)) {
        pageRowsWrapper.classList.remove('active');
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

