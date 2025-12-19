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

    if (d.ifcvalue != null && d.ifcvalue !== '') {
        const interestRate = formatInterestRate(d.ifcvalue);
        setText('detail-interest-rate', interestRate);
    } else {
        setText('detail-interest-rate', '');
    }

    if (d.loancycle != null && d.loancycle !== '') {
        setText('detail-loan-cycle', d.loancycle);
    } else {
        setText('detail-loan-cycle', '');
    }

    setText('detail-customer-name', d.customerName || '');
    setText('detail-coborrower-name', d.coborrowerName || '');

    setText('detail-maturity-date', d.maturitydt || '');

    if (d.period != null && d.period !== '') {
        const period = formatLoanPeriod(d.period);
        setText('detail-period', period);
    } else {
        setText('detail-period', '');
    }

    setText('detail-loan-type', d.typeLoan || '');

    if (d.adminfeerate != null && d.adminfeerate !== '') {
        const serviceFee = formatServiceFee(d.adminfeerate);
        setText('detail-service-fee', serviceFee);
    } else {
        setText('detail-service-fee', '');
    }

    if (d.dbamt != null && d.dbamt !== '') {
        const amount = formatAmount(d.dbamt);
        setText('detail-amount', amount);
    } else {
        setText('detail-amount', '');
    }
    
    if (d.phone != null && d.phone !== '') {
        const phone = formatPhone(d.phone);
        setText('detail-phone', phone);
    } else {
        setText('detail-phone', '');
    }

    setText('detail-address', d.ctmaddress || '');
    
    // Update footer customer name
    setText('detail-customer-name-footer', d.customerName || '');

    // Fetch and load repayment schedule dynamically
    if (d.acno) {
        loadRepaymentSchedule(d.acno);
    } else {
        // Clear schedule if no ACNO
        clearRepaymentSchedule();
    }
    
    // Generate and load QR code
    if (d.customerName && d.acno) {
        generateQRCode({
            payer_name: d.customerName,
            parent_account_no: d.acno,
            amount: d.dbamt || 0,
            currency_code: 'USD'
        });
    } else {
        // Clear QR code if no customer name or ACNO
        clearQRCode();
    }
}

// Load repayment schedule from database (not an API, just database query)
function loadRepaymentSchedule(acno) {
    const tbody = document.querySelector('#loanModal .payment-table-new tbody');
    if (!tbody) return;

    // Show loading state
    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">Loading schedule...</td></tr>';

    // Get data from database (not an API, just database query)
    const dataUrl = '/loan-schedule.php?acno=' + encodeURIComponent(acno);
    
    console.log('Fetching schedule data from database:', dataUrl);

    fetch(dataUrl)
        .then(response => {
            console.log('Database query response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    let errorMsg = 'Failed to fetch schedule (Status: ' + response.status + ')';
                    try {
                        const json = JSON.parse(text);
                        errorMsg = json.message || errorMsg;
                    } catch (e) {
                        if (text) {
                            errorMsg += ' - ' + text.substring(0, 100);
                        }
                    }
                    throw new Error(errorMsg);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                renderRepaymentSchedule(data.data.schedule, data.data.summary);
            } else {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: red; padding: 20px;">Error loading schedule: ' + (data.message || 'Unknown error') + '</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading repayment schedule from database:', error);
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: red; padding: 20px;">Error loading schedule: ' + error.message + '</td></tr>';
        });
}

// Render repayment schedule table
function renderRepaymentSchedule(schedule, summary) {
    const tbody = document.querySelector('#loanModal .payment-table-new tbody');
    if (!tbody) return;

    if (!schedule || schedule.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">No schedule data found.</td></tr>';
        return;
    }

    let html = '';

    // Initial balance row
    html += '<tr>';
    html += '<td></td>';
    html += '<td></td>';
    html += '<td></td>';
    html += '<td></td>';
    html += '<td></td>';
    html += '<td></td>';
    html += '<td class="text-right-new">' + formatScheduleNumber(summary.initialBalance) + '</td>';
    html += '<td></td>';
    html += '</tr>';

    // Schedule rows
    schedule.forEach((row, index) => {
        const dayName = row.dayname || '';
        const dueDate = row.duedate || '-';
        const dateDisplay = dayName && dueDate !== '-' ? dayName + ', ' + dueDate : dueDate;

        html += '<tr>';
        html += '<td>' + (row.dueno || (index + 1)) + '</td>';
        html += '<td class="text-left-new">' + dateDisplay + '</td>';
        html += '<td>' + formatScheduleNumber(row.period || '', 0) + '</td>';
        html += '<td class="text-right-new">' + formatScheduleNumber(row.principal || '') + '</td>';
        html += '<td class="text-right-new">' + formatScheduleNumber(row.interest || '') + '</td>';
        html += '<td class="text-right-new">' + formatScheduleNumber(row.totalamount || '') + '</td>';
        html += '<td class="text-right-new">' + formatScheduleNumber(row.bl || '') + '</td>';
        html += '<td></td>';
        html += '</tr>';
    });

    // Summary row
    html += '<tr class="summary-row-new">';
    html += '<td>សរុប :</td>';
    html += '<td></td>';
    html += '<td>' + formatScheduleNumber(summary.totalDays || 0, 0) + '</td>';
    html += '<td class="text-right-new">' + formatScheduleNumber(summary.totalPrincipal || 0) + '</td>';
    html += '<td class="text-right-new">' + formatScheduleNumber(summary.totalInterest || 0) + '</td>';
    html += '<td class="text-right-new">' + formatScheduleNumber(summary.totalAmount || 0) + '</td>';
    html += '<td></td>';
    html += '<td></td>';
    html += '</tr>';

    tbody.innerHTML = html;
}

// Clear repayment schedule
function clearRepaymentSchedule() {
    const tbody = document.querySelector('#loanModal .payment-table-new tbody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">No schedule data available.</td></tr>';
    }
}

// Format number for schedule display
function formatScheduleNumber(value, decimals = 2) {
    if (value === null || value === '' || value === '-') {
        return '-';
    }
    if (!isNumeric(value)) {
        return value;
    }
    return parseFloat(value).toFixed(decimals);
}

// Check if value is numeric
function isNumeric(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
}

// Format interest rate
function formatInterestRate(value) {
    if (!value || value === '') return '';
    const num = isNumeric(value) ? parseFloat(value).toFixed(2) : value;
    return num + ' %/1ខែ';
}

// Format loan period
function formatLoanPeriod(value) {
    if (!value || value === '') return '';
    return value + ' ខែ';
}

// Format service fee
function formatServiceFee(value) {
    if (!value || value === '') return '';
    const num = isNumeric(value) ? parseFloat(value).toFixed(2) : value;
    return num + '%';
}

// Format amount
function formatAmount(value) {
    if (!value || value === '') return '';
    const num = isNumeric(value) ? parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : value;
    return num + ' ដុល្លារ';
}

// Format phone number
function formatPhone(value) {
    if (!value || value === '') return '';
    // Remove any non-digit characters
    const phone = value.replace(/\D/g, '');
    if (!phone) return value;
    
    // Format: (855) 010 500 224
    let formatted = '';
    for (let i = 0; i < phone.length; i += 3) {
        if (i > 0) formatted += ' ';
        formatted += phone.substr(i, 3);
    }
    return '(855) ' + formatted.trim();
}

// Generate QR code using Webill API
function generateQRCode(params) {
    console.log('=== QR CODE GENERATION: Starting ===');
    
    const qrCodeElement = document.getElementById('qr-code-image');
    if (!qrCodeElement) {
        console.error('QR code element not found');
        return;
    }
    
    // Show loading state
    qrCodeElement.innerHTML = '<div style="text-align: center; padding: 10px; color: #666;">Loading QR...</div>';
    
    // Call Webill API via PHP proxy (this IS an external API call)
    // Webill API requires POST with JSON body
    const requestData = {
        payer_name: params.payer_name || '',
        parent_account_no: '1120000805758',
        payment_type: params.payment_type || '0',
        currency_code: params.currency_code || 'KHR',
        amount: params.amount || 0,
        remark: params.remark || '',
        khqr_name: params.khqr_name || '',
        request_id: params.request_id || 'req_' + Date.now()
    };
    
    console.log('QR Request Data:', requestData);
    
    // Call Webill API via PHP proxy (POST with JSON body)
    const apiUrl = '/api/qr-collection.php';
    console.log('Making API request to:', apiUrl);
    console.log('Note: Server will handle token request automatically');
    console.log('  - Token endpoint: /api/wbi/client/v1/auth/token');
    console.log('  - Token will be cached or requested as needed');
    
    const requestStartTime = Date.now();
    
    // Make POST request with JSON body
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
        .then(response => {
            const requestDuration = Date.now() - requestStartTime;
            console.log('API Response received');
            console.log('  - Status:', response.status, response.statusText);
            console.log('  - Duration:', requestDuration + 'ms');
            
            if (!response.ok) {
                console.error('API Error: Response not OK -', response.status);
                throw new Error('Failed to generate QR code');
            }
            
            return response.json();
        })
        .then(data => {
            console.log('QR Code Response:', {
                success: data.success,
                hasData: !!data.data,
                hasQRCode: !!(data.data && data.data.khqr_data_base64)
            });
            
            if (data.success && data.data && data.data.khqr_data_base64) {
                console.log('QR Code generated successfully');
                // Display QR code image
                const img = document.createElement('img');
                img.src = data.data.khqr_data_base64;
                img.alt = 'QR Code';
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'contain';
                
                qrCodeElement.innerHTML = '';
                qrCodeElement.appendChild(img);
                console.log('=== QR CODE GENERATION: SUCCESS ===');
            } else {
                console.warn('QR Code generation failed - no QR data in response');
                qrCodeElement.innerHTML = '<div style="text-align: center; padding: 10px; color: #999;">QR Code</div>';
            }
        })
        .catch(error => {
            console.error('=== QR CODE GENERATION: ERROR ===');
            console.error('Error:', error.message);
            qrCodeElement.innerHTML = '<div style="text-align: center; padding: 10px; color: #999;">QR Code</div>';
        });
}

// Clear QR code
function clearQRCode() {
    const qrCodeElement = document.getElementById('qr-code-image');
    if (qrCodeElement) {
        qrCodeElement.innerHTML = '<div style="text-align: center; padding: 10px; color: #999;">QR Code</div>';
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


