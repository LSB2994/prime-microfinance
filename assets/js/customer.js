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

// Pagination state for customers table
let currentPage = 1;
let rowsPerPage = 10;

function getCustomerRows() {
    const table = document.getElementById('customersTable');
    if (!table) return [];
    const tbody = table.querySelector('tbody');
    if (!tbody) return [];
    return Array.from(tbody.querySelectorAll('tr')).filter(row => 
        row.id !== 'noResultsRow' && !row.classList.contains('blank-row')
    );
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
    filterTable(); // This will call updatePaginationButtons internally
}

// Filter table based on search and apply pagination
// Search only in: ការិយាល័យ (BRNAME - column 0) and អតិថិជនឈ្មោះ (Customer Name - column 8)
function filterTable() {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const rows = getCustomerRows();
    const noResultsRow = document.getElementById('noResultsRow');
    const tbody = document.querySelector('#customersTable tbody');

    const filtered = rows.filter(row => {
        if (!searchTerm) return true;
        
        // Get cells for the two searchable columns
        const cells = row.querySelectorAll('td');
        if (cells.length < 9) return false;
        
        // Column 0: ការិយាល័យ (BRNAME)
        const brnameText = cells[0] ? cells[0].textContent.toLowerCase() : '';
        
        // Column 8: អតិថិជនឈ្មោះ (Customer Name - CNAMEKH/CNAMEEN)
        const customerNameText = cells[8] ? cells[8].textContent.toLowerCase() : '';
        
        // Search in both columns
        return brnameText.includes(searchTerm) || customerNameText.includes(searchTerm);
    });

    const total = filtered.length;

    if (total === 0) {
        rows.forEach(r => (r.style.display = 'none'));
        // Remove existing blank rows
        const existingBlankRows = tbody ? tbody.querySelectorAll('tr.blank-row') : [];
        existingBlankRows.forEach(r => r.remove());
        if (noResultsRow) noResultsRow.style.display = '';
        currentPage = 1;
        updatePaginationDisplay(0, 0);
        updatePaginationButtons(1, 1); // Disable both buttons when no results
        return;
    }

    if (noResultsRow) noResultsRow.style.display = 'none';

    const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    // Hide all rows first
    rows.forEach(r => (r.style.display = 'none'));

    // Show filtered rows for current page
    const visibleRows = [];
    filtered.forEach((row, idx) => {
        if (idx >= start && idx < end) {
            row.style.display = '';
            visibleRows.push(row);
        }
    });

    // Remove existing blank rows
    if (tbody) {
        const existingBlankRows = tbody.querySelectorAll('tr.blank-row');
        existingBlankRows.forEach(r => r.remove());
    }

    // Add blank rows to fill up to rowsPerPage
    const visibleCount = visibleRows.length;
    if (visibleCount < rowsPerPage && tbody) {
        const blankRowsNeeded = rowsPerPage - visibleCount;
        for (let i = 0; i < blankRowsNeeded; i++) {
            const blankRow = document.createElement('tr');
            blankRow.className = 'blank-row';
            blankRow.innerHTML = '<td colspan="17"></td>';
            tbody.appendChild(blankRow);
        }
    }

    const showing = Math.min(rowsPerPage, total - start);
    updatePaginationDisplay(showing, total);
    
    // Update button states
    updatePaginationButtons(currentPage, totalPages);
}

// Update pagination button states (enable/disable)
function updatePaginationButtons(page, totalPages) {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (prevBtn) {
        prevBtn.disabled = page <= 1;
    }
    
    if (nextBtn) {
        nextBtn.disabled = page >= totalPages;
    }
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

    // Apply pagination/filter on server-rendered rows
    initCustomersTable();

    // Wire up previous/next buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (!prevBtn.disabled && currentPage > 1) {
                currentPage--;
                filterTable();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            const rows = getCustomerRows();
            const filtered = rows.filter(row => {
                const searchInput = document.getElementById('searchInput');
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const text = row.textContent.toLowerCase();
                return !searchTerm || text.includes(searchTerm);
            });
            const totalPages = Math.max(1, Math.ceil(filtered.length / rowsPerPage));
            
            if (!nextBtn.disabled && currentPage < totalPages) {
                currentPage++;
                filterTable();
            }
        });
    }
});


