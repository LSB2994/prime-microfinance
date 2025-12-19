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
    if (d.customerName) {
        generateQRCode({
            payer_name: d.customerName,
            amount: d.dbamt || 0,
            currency_code: 'KHR'
        });
    } else {
        // Clear QR code if no customer name
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

// Generate QR code using Webill API (direct third-party API calls)
function generateQRCode(params) {
    console.log('=== QR CODE GENERATION: Starting ===');
    console.log('Step 1: Initializing QR code generation');
    
    const qrCodeElement = document.getElementById('qr-code-image');
    if (!qrCodeElement) {
        console.error('Step 1 ERROR: QR code element not found');
        return;
    }
    
    // Show loading state
    qrCodeElement.innerHTML = '<div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">Loading...</div>';
    
    // Prepare QR generation request data
    const qrRequestData = {
        payer_name: params.payer_name || '',
        parent_account_no: '1120000805758', // Fixed value
        payment_type: params.payment_type || '0',
        currency_code: params.currency_code || 'KHR',
        amount: params.amount || 0,
        remark: params.remark || '',
        khqr_name: params.khqr_name || '',
        request_id: params.request_id || 'req_' + Date.now()
    };
    
    console.log('Step 1: QR Request Data prepared:', qrRequestData);
    
    // Step 2: Request Token from third-party API
    console.log('Step 2: Requesting token from third-party API');
    console.log('  - Token API URL: https://apitest-va.webill365.com/kh/api/wbi/client/v1/auth/token');
    
    const tokenRequestData = {
        client_id: 'aec6fef2e90e26975ec95abd18b1fb77',
        client_secret: '13fd6fea8da76e2cde034590b6c2c55a'
    };
    
    console.log('Step 2: Token request payload:', {
        client_id: tokenRequestData.client_id,
        client_secret: '***' // Hide secret in logs
    });
    
    const tokenRequestStartTime = Date.now();
    
    // Step 2.1: Call token API
    fetch('https://apitest-va.webill365.com/kh/api/wbi/client/v1/auth/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': '*/*'
        },
        body: JSON.stringify(tokenRequestData)
    })
        .then(response => {
            const tokenRequestDuration = Date.now() - tokenRequestStartTime;
            console.log('Step 2.1: Token API response received');
            console.log('  - Status:', response.status, response.statusText);
            console.log('  - Duration:', tokenRequestDuration + 'ms');
            console.log('  - Headers:', Object.fromEntries(response.headers.entries()));
            
            if (!response.ok) {
                console.error('Step 2.1 ERROR: Token API response not OK -', response.status);
                return response.text().then(text => {
                    console.error('Step 2.1 ERROR: Response body:', text);
                    throw new Error('Failed to get token: ' + response.status);
                });
            }
            
            return response.json();
        })
        .then(tokenData => {
            console.log('Step 2.2: Token response parsed:', {
                hasData: !!tokenData.data,
                hasAccessToken: !!(tokenData.data && tokenData.data.access_token),
                tokenType: tokenData.data?.token_type || 'N/A',
                expiresIn: tokenData.data?.expires_in || 'N/A'
            });
            
            if (!tokenData.data || !tokenData.data.access_token) {
                console.error('Step 2.2 ERROR: No access token in response');
                console.error('Step 2.2 ERROR: Full response:', tokenData);
                throw new Error('No access token received');
            }
            
            const accessToken = tokenData.data.access_token;
            const tokenType = tokenData.data.token_type || 'Bearer';
            
            console.log('Step 2.3: Token extracted successfully');
            console.log('  - Token type:', tokenType);
            console.log('  - Token length:', accessToken.length);
            console.log('  - Token preview:', accessToken.substring(0, 20) + '...');
            
            // Step 3: Generate QR Code using the token
            console.log('Step 3: Generating QR code with token');
            console.log('  - QR API URL: https://apitest-va.webill365.com/kh/api/wbi/client/v1/qr-collections');
            console.log('  - QR Request Data:', qrRequestData);
            
            const qrRequestStartTime = Date.now();
            
            // Step 3.1: Call generateQR API with token
            return fetch('https://apitest-va.webill365.com/kh/api/wbi/client/v1/qr-collections', {
                method: 'POST',
                headers: {
                    'Authorization': tokenType + ' ' + accessToken,
                    'Content-Type': 'application/json',
                    'Accept': '*/*'
                },
                body: JSON.stringify(qrRequestData)
            })
                .then(qrResponse => {
                    const qrRequestDuration = Date.now() - qrRequestStartTime;
                    console.log('Step 3.1: QR API response received');
                    console.log('  - Status:', qrResponse.status, qrResponse.statusText);
                    console.log('  - Duration:', qrRequestDuration + 'ms');
                    console.log('  - Headers:', Object.fromEntries(qrResponse.headers.entries()));
                    
                    if (!qrResponse.ok) {
                        console.error('Step 3.1 ERROR: QR API response not OK -', qrResponse.status);
                        return qrResponse.text().then(text => {
                            console.error('Step 3.1 ERROR: Response body:', text);
                            throw new Error('Failed to generate QR code: ' + qrResponse.status);
                        });
                    }
                    
                    return qrResponse.json();
                })
                .then(qrData => {
                    console.log('Step 3.2: QR response parsed:', {
                        hasData: !!qrData.data,
                        hasKhqrDataBase64: !!(qrData.data && qrData.data.khqr_data_base64),
                        responseKeys: qrData.data ? Object.keys(qrData.data) : []
                    });
                    
                    // Handle nested data structure
                    let qrCodeBase64 = null;
                    if (qrData.data) {
                        if (qrData.data.data && qrData.data.data.khqr_data_base64) {
                            // Nested structure: { data: { data: { khqr_data_base64: ... } } }
                            qrCodeBase64 = qrData.data.data.khqr_data_base64;
                            console.log('Step 3.2: Found nested data structure');
                        } else if (qrData.data.khqr_data_base64) {
                            // Direct structure: { data: { khqr_data_base64: ... } }
                            qrCodeBase64 = qrData.data.khqr_data_base64;
                            console.log('Step 3.2: Found direct data structure');
                        }
                    }
                    
                    if (!qrCodeBase64) {
                        console.error('Step 3.2 ERROR: No QR code data found in response');
                        console.error('Step 3.2 ERROR: Full response:', qrData);
                        throw new Error('No QR code data in response');
                    }
                    
                    console.log('Step 3.3: QR code base64 data extracted');
                    console.log('  - Base64 length:', qrCodeBase64.length);
                    console.log('  - Base64 preview:', qrCodeBase64.substring(0, 50) + '...');
                    
                    // Step 4: Decode and display QR code
                    console.log('Step 4: Decoding and displaying QR code');
                    
                    // Check if base64 string already has data URL prefix
                    let qrImageSrc = qrCodeBase64;
                    if (!qrCodeBase64.startsWith('data:image')) {
                        // Add data URL prefix if not present
                        qrImageSrc = 'data:image/png;base64,' + qrCodeBase64;
                        console.log('Step 4.1: Added data URL prefix to base64 string');
                    } else {
                        console.log('Step 4.1: Base64 string already has data URL prefix');
                    }
                    
                    // Create and display image
                    const img = document.createElement('img');
                    img.src = qrImageSrc;
                    img.alt = 'QR Code';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'contain';
                    
                    // Handle image load
                    img.onload = function() {
                        console.log('Step 4.2: QR code image loaded successfully');
                        console.log('  - Image dimensions:', img.naturalWidth + 'x' + img.naturalHeight);
                    };
                    
                    img.onerror = function() {
                        console.error('Step 4.2 ERROR: Failed to load QR code image');
                        console.error('  - Image src length:', qrImageSrc.length);
                        console.error('  - Image src preview:', qrImageSrc.substring(0, 100) + '...');
                    };
                    
                    qrCodeElement.innerHTML = '';
                    qrCodeElement.appendChild(img);
                    
                    console.log('Step 4.3: QR code displayed in modal');
                    console.log('=== QR CODE GENERATION: SUCCESS ===');
                });
        })
        .catch(error => {
            console.error('=== QR CODE GENERATION: ERROR ===');
            console.error('Error details:', {
                message: error.message,
                stack: error.stack
            });
            qrCodeElement.innerHTML = '<div style="text-align: center; padding: 20px; color: #dc2626; font-size: 12px;">Error loading QR</div>';
        });
}

// Clear QR code
function clearQRCode() {
    const qrCodeElement = document.getElementById('qr-code-image');
    if (qrCodeElement) {
        qrCodeElement.innerHTML = '<div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">QR Code</div>';
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


