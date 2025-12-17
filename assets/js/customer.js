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

// Show loan repayment / customer-detail modal (using data attributes from the row)
function showLoanRepayment(rowEl) {
    const modal = document.getElementById('loanModal');
    if (!modal) return;

    modal.classList.add('active');

    // Helper to safely set text
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value != null && value !== '' ? value : '';
        }
    };

    if (!rowEl || !rowEl.dataset) return;

    const d = rowEl.dataset;

    // Map dataset fields to modal fields
    setText('detail-office', d.brname || '');
    setText('detail-acno', d.acno || '');
    setText('detail-customer-account', d.ctmid || '');
    setText('detail-disburse-date', d.disbursedt || '');
    setText('detail-deposit-account', d.depositacc || '');

    if (d.ifcvalue != null) {
        setText('detail-interest-rate', d.ifcvalue);
    }

    if (d.loancycle != null) {
        setText('detail-loan-cycle', d.loancycle);
    }

    setText('detail-customer-name', d.customerName || '');
    setText('detail-coborrower-name', d.coborrowerName || '');

    setText('detail-maturity-date', d.maturitydt || '');

    if (d.period != null) {
        setText('detail-period', d.period);
    }

    setText('detail-loan-type', d.typeLoan || '');

    if (d.adminfeerate != null) {
        setText('detail-service-fee', d.adminfeerate);
    }

    if (d.dbamt != null) {
        setText('detail-amount', d.dbamt);
    }

    setText('detail-phone', d.phone || '');
    setText('detail-address', d.ctmaddress || '');
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

// Pagination state for customers table
let currentPage = 1;
let rowsPerPage = 10;

function getCustomerRows() {
    const table = document.getElementById('customersTable');
    if (!table) return [];
    const tbody = table.querySelector('tbody');
    if (!tbody) return [];
    return Array.from(tbody.querySelectorAll('tr')).filter(row => row.id !== 'noResultsRow');
}

function updatePaginationDisplay(showing, total) {
    const label = document.querySelector('.pagination-count');
    if (label) {
        label.textContent = `${showing} of ${total}`;
    }
}

// Initialize table using existing server-rendered rows
function initCustomersTable() {
    currentPage = 1;
    filterTable();
}

// Filter table based on search and apply pagination
function filterTable() {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const rows = getCustomerRows();
    const noResultsRow = document.getElementById('noResultsRow');

    const filtered = rows.filter(row => {
        const text = row.textContent.toLowerCase();
        return !searchTerm || text.includes(searchTerm);
    });

    const total = filtered.length;

    if (total === 0) {
        rows.forEach(r => (r.style.display = 'none'));
        if (noResultsRow) noResultsRow.style.display = '';
        currentPage = 1;
        updatePaginationDisplay(0, 0);
        return;
    }

    if (noResultsRow) noResultsRow.style.display = 'none';

    const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    rows.forEach(r => (r.style.display = 'none'));

    filtered.forEach((row, idx) => {
        if (idx >= start && idx < end) {
            row.style.display = '';
        }
    });

    const showing = Math.min(rowsPerPage, total - start);
    updatePaginationDisplay(showing, total);
}

// Toggle user menu
function toggleUserMenu() {
    const userProfile = document.querySelector('.user-profile-new');
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
    
    // Update pagination
    rowsPerPage = value;
    currentPage = 1;
    filterTable();
}


// Close user menu when clicking outside
document.addEventListener('click', function(event) {
    const userProfile = document.querySelector('.user-profile-new');
    const userMenu = document.getElementById('userMenu');
    const pageRowsWrapper = document.querySelector('.pagination-select-wrapper');
    
    // Close user menu
    if (userProfile && userMenu && !userProfile.contains(event.target)) {
        userProfile.classList.remove('active');
    }
    
    // Close page rows dropdown when clicking outside
    if (pageRowsWrapper && !pageRowsWrapper.contains(event.target)) {
        pageRowsWrapper.classList.remove('active');
    }
});

// Handle login form submission and table init
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

    // Simple loading progress bar animation
    const loadingBar = document.getElementById('pageLoadingBar');
    if (loadingBar) {
        loadingBar.style.width = '60%';
        setTimeout(() => {
            loadingBar.style.width = '100%';
            loadingBar.style.opacity = '0';
            setTimeout(() => {
                loadingBar.style.display = 'none';
            }, 300);
        }, 200);
    }

    // Hide table loading overlay once ready
    const tableLoading = document.getElementById('customersTableLoading');
    if (tableLoading) {
        tableLoading.style.display = 'none';
    }

    // Apply pagination/filter on server-rendered rows
    initCustomersTable();

    // Wire up previous/next buttons if present
    const paginationButtons = document.querySelectorAll('.pagination-new .pagination-btn');
    if (paginationButtons.length >= 2) {
        const prevBtn = paginationButtons[0];
        const nextBtn = paginationButtons[paginationButtons.length - 1];

        prevBtn.addEventListener('click', function() {
            currentPage--;
            filterTable();
        });

        nextBtn.addEventListener('click', function() {
            currentPage++;
            filterTable();
        });
    }
});


