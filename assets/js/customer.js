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

// Show loan repayment / customer-detail modal
async function showLoanRepayment(loanId) {
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

    // Show basic loading state for dynamic fields
    setText('detail-office', '...');
    setText('detail-acno', loanId || '');
    setText('detail-customer-account', '...');
    setText('detail-disburse-date', '...');
    setText('detail-deposit-account', '...');
    setText('detail-interest-rate', '...');
    setText('detail-loan-cycle', '...');
    setText('detail-customer-name', '...');
    setText('detail-coborrower-name', '...');
    setText('detail-maturity-date', '...');
    setText('detail-period', '...');
    setText('detail-loan-type', '');
    setText('detail-service-fee', '...');
    setText('detail-amount', '...');
    setText('detail-phone', '...');
    setText('detail-address', '...');

    try {
        // Load customer detail from Oracle via API
        const response = await fetch(`api/customer-detail?acno=${encodeURIComponent(loanId)}`, {
            credentials: 'include',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
            throw new Error('No customer detail found');
        }

        const d = data.data[0];

        // Map API fields to modal fields
        setText('detail-office', d.brname || '');
        setText('detail-acno', d.acno || loanId || '');
        setText('detail-customer-account', d.ctmid || '');
        setText('detail-disburse-date', d.disbursedt || '');
        setText('detail-deposit-account', d.depositacc || '');

        // Interest / IFC value
        if (d.ifcvalue != null) {
            setText('detail-interest-rate', d.ifcvalue);
        }

        // Loan cycle and type
        if (d.loancycle != null) {
            setText('detail-loan-cycle', `${d.loancycle}`);
        }
        setText('detail-loan-type', d.type_loan || '');

        // Customer and co-borrower names
        const cNameKh = d.cnamekh || '';
        const cNameEn = d.cnameen || '';
        const coNameKh = d.cobonamekh || '';
        const coNameEn = d.cobonameen || '';

        setText(
            'detail-customer-name',
            [cNameKh, cNameEn].filter(Boolean).join(' / ')
        );
        setText(
            'detail-coborrower-name',
            [coNameKh, coNameEn].filter(Boolean).join(' / ')
        );

        // Maturity, period, fee, amount, phone, address
        setText('detail-maturity-date', d.maturitydt || '');

        if (d.period != null) {
            setText('detail-period', `${d.period}`);
        }

        if (d.adminfeerate != null) {
            setText('detail-service-fee', d.adminfeerate);
        }

        if (d.dbamt != null) {
            setText('detail-amount', d.dbamt);
        }

        setText('detail-phone', d.phone || '');
        setText('detail-address', d.ctmaddress || '');
    } catch (error) {
        console.error('Failed to load customer detail:', error);
        // Optionally show an error message in the modal
        setText('detail-office', 'Error loading data');
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

// Pagination state for customers table
let currentPage = 1;
let rowsPerPage = 20;

function getCustomerRows() {
    const table = document.getElementById('customersTable');
    if (!table) return [];
    return Array.from(table.querySelectorAll('tbody tr'));
}

function updatePaginationDisplay(showing, total) {
    const label = document.querySelector('.pagination-count');
    if (label) {
        label.textContent = `${showing} of ${total}`;
    }
}

// Load customers list from API and populate table
async function loadCustomers() {
    const table = document.getElementById('customersTable');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    try {
        // Show loading row
        tbody.innerHTML = `
            <tr class="loading-row">
                <td colspan="17">Loading customers...</td>
            </tr>
        `;

        const response = await fetch('/api/customers', {
            credentials: 'include',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const json = await response.json();
        console.log('Customers API result:', json);

        if (!json.success || !Array.isArray(json.data)) {
            console.error('Invalid customers data from API');
            tbody.innerHTML = `
                <tr class="error-row">
                    <td colspan="17">Failed to load customers (invalid data). Check console for details.</td>
                </tr>
            `;
            updatePaginationDisplay(0, 0);
            return;
        }

        // Clear existing static rows
        tbody.innerHTML = '';

        json.data.forEach(item => {
            // Keys are lowercased by oracleFetchAll
            const c = item || {};

            const tr = document.createElement('tr');
            if (c.acno) {
                tr.onclick = () => showLoanRepayment(c.acno);
            }

            const customerName = [c.cnamekh, c.cnameen].filter(Boolean).join(' / ');
            const coborrowerName = [c.cobonamekh, c.cobonameen].filter(Boolean).join(' / ');

            tr.innerHTML = `
                <td>${c.brname || ''}</td>
                <td>${c.acno || ''}</td>
                <td>${c.ctmid || ''}</td>
                <td>${c.disbursedt || ''}</td>
                <td>${c.depositacc || ''}</td>
                <td>${c.ifcvalue != null ? c.ifcvalue : ''}</td>
                <td>${c.loancycle != null ? c.loancycle : ''}</td>
                <td>${c.coname || ''}</td>
                <td>${customerName}</td>
                <td>${coborrowerName}</td>
                <td>${c.maturitydt || ''}</td>
                <td>${c.period != null ? c.period : ''}</td>
                <td>${c.type_loan || ''}</td>
                <td>${c.adminfeerate != null ? c.adminfeerate : ''}</td>
                <td>${c.dbamt != null ? c.dbamt : ''}</td>
                <td>${c.phone || ''}</td>
                <td>
                    <div class="address-cell">
                        <p>${c.ctmaddress || ''}</p>
                    </div>
                </td>
            `;

            tbody.appendChild(tr);
        });

        // Reset pagination and apply
        currentPage = 1;
        filterTable();
    } catch (error) {
        console.error('Failed to load customers list:', error);
        if (tbody) {
            tbody.innerHTML = `
                <tr class="error-row">
                    <td colspan="17">Failed to load customers (API error). Check console for details.</td>
                </tr>
            `;
        }
        updatePaginationDisplay(0, 0);
    }
}

// Filter table based on search and apply pagination
function filterTable() {
    const searchInput = document.getElementById('searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const rows = getCustomerRows();

    const filtered = rows.filter(row => {
        const text = row.textContent.toLowerCase();
        return !searchTerm || text.includes(searchTerm);
    });

    const total = filtered.length;

    if (total === 0) {
        rows.forEach(r => (r.style.display = 'none'));
        currentPage = 1;
        updatePaginationDisplay(0, 0);
        return;
    }

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

    // Load customers from API and then apply pagination/filter
    loadCustomers();

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


