// Customer Detail Modal Functions
function openCustomerModal(rowEl) {
    const $modal = $('#customerModal');
    if (!$modal.length) return;
    
    const $row = $(rowEl);
    
    // Get data from row attributes
    const data = {
        brname: $row.attr('data-brname') || '',
        acno: $row.attr('data-acno') || '',
        ctmid: $row.attr('data-ctmid') || '',
        disbursedt: $row.attr('data-disbursedt') || '',
        depositacc: $row.attr('data-depositacc') || '',
        ifcvalue: $row.attr('data-ifcvalue') || '',
        loancycle: $row.attr('data-loancycle') || '',
        coname: $row.attr('data-coname') || '',
        customerName: $row.attr('data-customer-name') || '',
        coborrowerName: $row.attr('data-coborrower-name') || '',
        maturitydt: $row.attr('data-maturitydt') || '',
        period: $row.attr('data-period') || '',
        typeLoan: $row.attr('data-type-loan') || '',
        adminfeerate: $row.attr('data-adminfeerate') || '',
        dbamt: $row.attr('data-dbamt') || '',
        phone: $row.attr('data-phone') || '',
        ctmaddress: $row.attr('data-ctmaddress') || ''
    };
    
    // Populate info table
    populateModalInfo(data);
    
    // Load QR collection data and generate QR code
    console.log('[Customer Modal] Opening customer modal - triggering QR collection load');
    loadQRCollection();
    
    // Load and populate schedule if acno exists
    if (data.acno) {
        loadRepaymentSchedule(data.acno);
    } else {
        clearRepaymentSchedule();
    }
    
    // Show modal
    $modal.addClass('active');
    $('body').css('overflow', 'hidden');
}

function populateModalInfo(data) {
    const $tbody = $('#modalInfoTableBody');
    if (!$tbody.length) return;
    
    // Format values
    const formatDate = function(dateStr) {
        if (!dateStr) return '';
        // Handle Oracle date format (DD-MM-YY or similar)
        // Try to parse as-is first (might already be formatted)
        if (dateStr.includes('/') || dateStr.includes('-')) {
            // Check if it's already in DD/MM/YY or DD-MM-YY format
            const parts = dateStr.split(/[\/\-]/);
            if (parts.length === 3) {
                const day = parts[0].padStart(2, '0');
                const month = parts[1].padStart(2, '0');
                const year = parts[2].length === 2 ? parts[2] : parts[2].slice(-2);
                return `${day}/${month}/${year}`;
            }
        }
        // Try to parse as Date object
        try {
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr;
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = String(date.getFullYear()).slice(-2);
            return `${day}/${month}/${year}`;
        } catch {
            return dateStr;
        }
    };
    
    const formatAmount = function(amt) {
        if (!amt || amt === '') return '';
        const num = parseFloat(amt);
        if (isNaN(num)) return amt;
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    
    const formatInterestRate = function(rate) {
        if (!rate || rate === '') return '';
        const num = parseFloat(rate);
        if (isNaN(num)) return rate;
        return num.toFixed(2) + ' %/1ខែ';
    };
    
    const formatPeriod = function(period) {
        if (!period || period === '') return '';
        return period + ' ខែ';
    };
    
    const formatServiceFee = function(fee) {
        if (!fee || fee === '') return '';
        const num = parseFloat(fee);
        if (isNaN(num)) return fee;
        return num.toFixed(2) + '%';
    };
    
    const formatPhone = function(phone) {
        if (!phone) return '';
        return phone.replace(/^\+?855/, '(855) ');
    };
    
    // Build info table rows
    $tbody.html(`
        <tr>
            <td class="modal-info-label">ការិយាល័យ</td>
            <td class="modal-info-value">៖ ${data.brname || ''}</td>
            <td colspan="2" class="modal-info-label">កម្ចីរគ្គទី</td>
            <td colspan="4" class="modal-info-value">៖ ${data.loancycle || ''} / មន្ត្រីឥណទាន / ${data.coname || ''}${data.phone ? '(' + formatPhone(data.phone) + ')' : ''}</td>
        </tr>
        <tr>
            <td class="modal-info-label modal-font-weight-bold">គណនីឥណទាន</td>
            <td class="modal-info-value modal-font-weight-bold">៖ ${data.acno || ''}</td>
            <td colspan="2" class="modal-info-label modal-font-weight-bold">អតិថិជនឈ្មោះ</td>
            <td colspan="4" class="modal-info-value modal-font-weight-bold">៖ ${data.customerName || ''}</td>
        </tr>
        <tr>
            <td class="modal-info-label modal-font-weight-bold">គណនីអតិថិជន</td>
            <td class="modal-info-value modal-font-weight-bold">៖ ${data.ctmid || ''}</td>
            <td colspan="2" class="modal-info-label">អ្នករួមខ្ចីឈ្មោះ</td>
            <td colspan="2" class="modal-info-value">៖ ${data.coborrowerName || ''}</td>
        </tr>
        <tr>
            <td class="modal-info-label">កាលបរិច្ឆេទផ្តល់កម្ចី</td>
            <td class="modal-info-value">៖ ${formatDate(data.disbursedt)}</td>
            <td colspan="2" class="modal-info-label">កាលបរិច្ឆេទបញ្ចប់នៃកម្ចី</td>
            <td colspan="2" class="modal-info-value">៖ ${formatDate(data.maturitydt)}</td>
        </tr>
        <tr>
            <td class="modal-info-label">លេខគណនីសន្សំ</td>
            <td class="modal-info-value">៖ ${data.depositacc || ''}</td>
            <td class="modal-info-label">រយៈពេលខ្លី</td>
            <td class="modal-info-label"></td>
            <td class="modal-info-value">៖ ${formatPeriod(data.period)}</td>
            <td class="modal-info-label">ចំនួនទឹកប្រាក់</td>
            <td class="modal-info-value modal-font-weight-bold">៖ ${formatAmount(data.dbamt)} ដុល្លារ</td>
        </tr>
        <tr>
            <td class="modal-info-label">អត្រាការប្រាក់</td>
            <td class="modal-info-value">៖ ${formatInterestRate(data.ifcvalue)}</td>
            <td class="modal-info-label">របៀបសងប្រាក់</td>
            <td class="modal-info-label"></td>
            <td class="modal-info-value">៖ ${data.typeLoan || 'QR Payment'}</td>
            <td class="modal-info-label">លេខទូរស័ព្ទ</td>
            <td class="modal-info-value">៖ ${formatPhone(data.phone)}</td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="modal-info-label">សេវាប្រចាំខែ</td>
            <td class="modal-info-label"></td>
            <td class="modal-info-value">៖ ${formatServiceFee(data.adminfeerate)}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="modal-info-label">អាស័យដ្ឋានអ្នកខ្ចី</td>
            <td colspan="5" class="modal-info-value">៖ ${data.ctmaddress || ''}</td>
        </tr>
    `);
    
    // Update signature name
    $('#modalSignatureName').text(data.customerName || '');
    
    // Update footer date
    const today = new Date();
    const day = String(today.getDate()).padStart(2, '0');
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const year = today.getFullYear();
    $('#modalFooterDate').text(`ថ្ងៃខែឆ្នាំ៖ ${day}/${month}/${year}`);
}

function loadRepaymentSchedule(acno) {
    const baseUrl = $('body').attr('data-base-url') || '';
    const url = `${baseUrl}/loan-schedule.php?acno=${encodeURIComponent(acno)}`;
    
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(result) {
            if (result.success && result.data) {
                renderRepaymentSchedule(result.data.schedule, result.data.summary);
            } else {
                clearRepaymentSchedule();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading repayment schedule:', error);
            clearRepaymentSchedule();
        }
    });
}

// Configuration constants
const WEBILL_CONFIG = {
    BASE_URL: 'https://apitest-va.webill365.com/kh',
    CLIENT_ID: 'aec6fef2e90e26975ec95abd18b1fb77',
    CLIENT_SECRET: '13fd6fea8da76e2cde034590b6c2c55a',
    TOKEN_ENDPOINT: '/api/wbi/client/v1/auth/token',
    QR_COLLECTION_ENDPOINT: '/api/wbi/client/v1/qr-collections',
    REQUEST_TIMEOUT: 30000, // 30 seconds
    MAX_RETRIES: 2
};

// Store payer name globally for QR code display
let currentPayerName = '';
let currentCurrency = 'USD';
let qrCollectionRequest = null; // Store request for cancellation

// Random names pool for payer name generation
const PAYER_NAMES = [
    'John Smith', 'Jane Doe', 'Michael Johnson', 'Sarah Williams', 'David Brown',
    'Emily Davis', 'Robert Miller', 'Jessica Wilson', 'William Moore', 'Ashley Taylor',
    'James Anderson', 'Amanda Thomas', 'Christopher Jackson', 'Melissa White', 'Daniel Harris',
    'Michelle Martin', 'Matthew Thompson', 'Stephanie Garcia', 'Anthony Martinez', 'Nicole Robinson',
    'Mark Clark', 'Rachel Rodriguez', 'Donald Lewis', 'Laura Lee', 'Steven Walker',
    'Kimberly Hall', 'Paul Allen', 'Lisa Young', 'Andrew King', 'Amy Wright',
    'Joshua Lopez', 'Angela Hill', 'Kenneth Scott', 'Brenda Green', 'Kevin Adams',
    'Pamela Baker', 'Brian Gonzalez', 'Emma Nelson', 'George Carter', 'Deborah Mitchell'
];

/**
 * Generate a random payer name
 * @returns {string} Random payer name
 */
function generateRandomPayerName() {
    return PAYER_NAMES[Math.floor(Math.random() * PAYER_NAMES.length)];
}

/**
 * Get WeBill365 access token
 * @returns {Promise<string>} Access token
 */
function getWebillAccessToken() {
    return new Promise(function(resolve, reject) {
        console.log('[QR Collection] Step 1: Requesting WeBill365 access token');
        console.log('[QR Collection] Token URL:', WEBILL_CONFIG.BASE_URL + WEBILL_CONFIG.TOKEN_ENDPOINT);
        
        qrCollectionRequest = $.ajax({
            url: WEBILL_CONFIG.BASE_URL + WEBILL_CONFIG.TOKEN_ENDPOINT,
            method: 'POST',
            contentType: 'application/json',
            timeout: WEBILL_CONFIG.REQUEST_TIMEOUT,
            headers: {
                'accept': '*/*'
            },
            data: JSON.stringify({
                client_id: WEBILL_CONFIG.CLIENT_ID,
                client_secret: WEBILL_CONFIG.CLIENT_SECRET
            }),
            dataType: 'json',
            success: function(tokenResult) {
                console.log('[QR Collection] Step 1: Token request successful');
                
                // Validate response structure
                if (!tokenResult || typeof tokenResult !== 'object') {
                    reject(new Error('Invalid token response format'));
                    return;
                }
                
                // Extract access token from response
                const accessToken = tokenResult.access_token || (tokenResult.data && tokenResult.data.access_token);
                
                if (!accessToken || typeof accessToken !== 'string') {
                    console.error('[QR Collection] Step 1: Failed to extract access token from response');
                    console.error('[QR Collection] Full token result:', tokenResult);
                    reject(new Error('Access token not found in response'));
                    return;
                }
                
                console.log('[QR Collection] Step 1: Access token obtained successfully');
                console.log('[QR Collection] Token (first 20 chars):', accessToken.substring(0, 20) + '...');
                resolve(accessToken);
            },
            error: function(xhr, status, error) {
                const errorMessage = status === 'timeout' 
                    ? 'Token request timed out' 
                    : `Token request failed: ${error}`;
                console.error('[QR Collection] Step 1: Token request failed');
                console.error('[QR Collection] Step 1: Error:', error);
                console.error('[QR Collection] Step 1: Status:', status);
                console.error('[QR Collection] Step 1: HTTP Status:', xhr.status);
                console.error('[QR Collection] Step 1: Response Text:', xhr.responseText);
                reject(new Error(errorMessage));
            }
        });
    });
}

/**
 * Create QR collection request
 * @param {string} accessToken - WeBill365 access token
 * @param {Object} qrData - QR collection data
 * @returns {Promise<Object>} QR collection response
 */
function createQRCollection(accessToken, qrData) {
    return new Promise(function(resolve, reject) {
        console.log(`[QR Collection] Step 2.${qrData.currency_code}: Sending QR collection request`);
        console.log(`[QR Collection] Step 2.${qrData.currency_code}: Request data:`, qrData);
        
        $.ajax({
            url: WEBILL_CONFIG.BASE_URL + WEBILL_CONFIG.QR_COLLECTION_ENDPOINT,
            method: 'POST',
            contentType: 'application/json',
            timeout: WEBILL_CONFIG.REQUEST_TIMEOUT,
            headers: {
                'accept': '*/*',
                'Authorization': `Bearer ${accessToken}`
            },
            data: JSON.stringify(qrData),
            dataType: 'json',
            success: function(result) {
                console.log(`[QR Collection] Step 2.${qrData.currency_code}: QR collection request successful`);
                
                // Validate response
                if (!result || typeof result !== 'object') {
                    reject(new Error('Invalid QR collection response format'));
                    return;
                }
                
                // Extract khqr_data from response
                const khqrData = result.khqr_data || (result.data && result.data.khqr_data);
                
                if (!khqrData || typeof khqrData !== 'string') {
                    console.error(`[QR Collection] Step 2.${qrData.currency_code}: khqr_data not found in response`);
                    reject(new Error('khqr_data not found in response'));
                    return;
                }
                
                console.log(`[QR Collection] Step 2.${qrData.currency_code}: Found khqr_data`);
                console.log(`[QR Collection] Step 2.${qrData.currency_code}: khqr_data length:`, khqrData.length);
                resolve(khqrData);
            },
            error: function(xhr, status, error) {
                const errorMessage = status === 'timeout'
                    ? 'QR collection request timed out'
                    : `QR collection request failed: ${error}`;
                console.error(`[QR Collection] Step 2.${qrData.currency_code}: QR collection request failed`);
                console.error(`[QR Collection] Step 2.${qrData.currency_code}: Error:`, error);
                console.error(`[QR Collection] Step 2.${qrData.currency_code}: Status:`, status);
                console.error(`[QR Collection] Step 2.${qrData.currency_code}: HTTP Status:`, xhr.status);
                console.error(`[QR Collection] Step 2.${qrData.currency_code}: Response Text:`, xhr.responseText);
                reject(new Error(errorMessage));
            }
        });
    });
}

/**
 * Load QR collection data and generate QR code
 */
function loadQRCollection() {
    console.log('[QR Collection] Starting QR collection load process...');
    
    // Cancel any existing request
    if (qrCollectionRequest && qrCollectionRequest.abort) {
        qrCollectionRequest.abort();
    }
    
    // Generate random payer name
    const randomPayerName = generateRandomPayerName();
    currentPayerName = randomPayerName;
    currentCurrency = 'USD';
    
    console.log('[QR Collection] Step 2: Generated random payer name:', randomPayerName);
    
    // Prepare QR collection data
    const qrCollectionData = {
        payer_name: randomPayerName,
        parent_account_no: '1120000106664',
        payment_type: '0',
        currency_code: 'USD',
        amount: 0,
        remark: '',
        khqr_name: '',
        request_id: '001'
    };
    
    // Get access token and create QR collection
    getWebillAccessToken()
        .then(function(accessToken) {
            console.log('[QR Collection] Step 2: Preparing QR collection request');
            console.log('[QR Collection] QR Collection URL:', WEBILL_CONFIG.BASE_URL + WEBILL_CONFIG.QR_COLLECTION_ENDPOINT);
            
            return createQRCollection(accessToken, qrCollectionData);
        })
        .then(function(khqrData) {
            console.log('[QR Collection] Step 3: QR collection completed successfully');
            console.log('[QR Collection] Step 3: QR data available, generating QR code...');
            console.log('[QR Collection] Step 3: QR data length:', khqrData.length);
            generateQRCode(khqrData);
        })
        .catch(function(error) {
            console.error('[QR Collection] Error:', error.message);
            console.error('[QR Collection] Full error:', error);
            
            // Show error in QR canvas
            const canvas = document.getElementById('qrCanvas');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ff0000';
                ctx.fillRect(0, 0, canvas.width || 92, canvas.height || 92);
                ctx.fillStyle = '#ffffff';
                ctx.font = '12px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('QR Error', (canvas.width || 92) / 2, (canvas.height || 92) / 2);
            }
        });
}

function closeCustomerModal() {
    // Cancel any pending QR collection requests
    if (qrCollectionRequest && qrCollectionRequest.abort) {
        qrCollectionRequest.abort();
        qrCollectionRequest = null;
        console.log('[Customer Modal] Cancelled pending QR collection request');
    }
    
    const $modal = $('#customerModal');
    if ($modal.length) {
        // Remove active class
        $modal.removeClass('active');
        // Remove any inline styles that might interfere
        $modal.css('display', '');
        // Restore body overflow
        $('body').css('overflow', '');
    }
}

function generateQRCode(dataBaseUrl) {
    console.log('[QR Code Generation] Step 4: Starting QR code generation');
    console.log('[QR Code Generation] Step 4: Input data length:', dataBaseUrl ? dataBaseUrl.length : 0);
    console.log('[QR Code Generation] Step 4: Input data (first 100 chars):', dataBaseUrl ? dataBaseUrl.substring(0, 100) + '...' : 'NULL');
    
    // Check if QRCode library is loaded, wait a bit if not
    let QRCodeLib = window.QRCode || (typeof QRCode !== 'undefined' ? QRCode : null);
    
    if (!QRCodeLib) {
        console.warn('[QR Code Generation] Step 4: QRCode library not loaded yet, waiting...');
        // Wait up to 3 seconds for library to load
        let attempts = 0;
        const checkInterval = setInterval(function() {
            QRCodeLib = window.QRCode || (typeof QRCode !== 'undefined' ? QRCode : null);
            attempts++;
            
            if (QRCodeLib) {
                clearInterval(checkInterval);
                console.log('[QR Code Generation] Step 4: QRCode library loaded after wait');
                proceedWithQRGeneration(dataBaseUrl, QRCodeLib);
            } else if (attempts >= 30) { // 3 seconds (30 * 100ms)
                clearInterval(checkInterval);
                console.error('[QR Code Generation] Step 4: QRCode library failed to load after 3 seconds');
                console.error('[QR Code Generation] Step 4: Available globals:', Object.keys(window).filter(k => k.toLowerCase().includes('qr')));
                const canvas = document.getElementById('qrCanvas');
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#ff0000';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.fillStyle = '#ffffff';
                    ctx.font = '12px Arial';
                    ctx.textAlign = 'center';
                    ctx.fillText('QR Error', canvas.width / 2, canvas.height / 2);
                }
            }
        }, 100);
        return;
    }
    
    console.log('[QR Code Generation] Step 4: QRCode library is available');
    proceedWithQRGeneration(dataBaseUrl, QRCodeLib);
}

function proceedWithQRGeneration(dataBaseUrl, QRCodeLib) {
    
    const canvas = document.getElementById('qrCanvas');
    if (!canvas) {
        console.error('[QR Code Generation] Step 4: QR code canvas (#qrCanvas) not found');
        return;
    }
    
    console.log('[QR Code Generation] Step 4: QR code canvas found');
    
    // Generate QR code
    const qrSize = 300; // Higher resolution for better quality
    console.log('[QR Code Generation] Step 4: QR code size:', qrSize);
    
    // Generate QR code using QRCode library
    console.log('[QR Code Generation] Step 4: Calling QRCode.toCanvas');
    console.log('[QR Code Generation] Step 4: QRCode options:', {
        width: qrSize,
        margin: 1,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        },
        errorCorrectionLevel: 'H'
    });
    
    QRCodeLib.toCanvas(canvas, dataBaseUrl, {
        width: qrSize,
        margin: 1,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        },
        errorCorrectionLevel: 'H'
    }, function(error) {
        if (error) {
            console.error('[QR Code Generation] Step 4: Error generating QR code');
            console.error('[QR Code Generation] Step 4: Error details:', error);
            console.error('[QR Code Generation] Step 4: Error message:', error.message);
            console.error('[QR Code Generation] Step 4: Error stack:', error.stack);
        } else {
            console.log('[QR Code Generation] Step 4: QR code generated successfully');
            console.log('[QR Code Generation] Step 4: QR code generation completed');
            
            // Get context after QR code is generated
            const ctx = canvas.getContext('2d');
            
            // Add USD currency icon to center of QR code
            function addCurrencyIcon(currencySymbol) {
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2;
                const iconRadius = 15; // Radius of the inner black circle (smaller)
                const padding = 6; // White padding around icon (outer white circle) (smaller)
                const borderWidth = 1.5; // Thin black border width
                
                // Draw white outer circle (background/padding)
                ctx.fillStyle = '#FFFFFF';
                ctx.beginPath();
                ctx.arc(centerX, centerY, iconRadius + padding, 0, 2 * Math.PI);
                ctx.fill();
                
                // Draw thin black circular border
                ctx.strokeStyle = '#000000';
                ctx.lineWidth = borderWidth;
                ctx.beginPath();
                ctx.arc(centerX, centerY, iconRadius + padding, 0, 2 * Math.PI);
                ctx.stroke();
                
                // Draw solid black inner circle
                ctx.fillStyle = '#000000';
                ctx.beginPath();
                ctx.arc(centerX, centerY, iconRadius, 0, 2 * Math.PI);
                ctx.fill();
                
                // Draw currency symbol in white, centered (bigger font)
                ctx.fillStyle = '#FFFFFF';
                ctx.font = `bold ${iconRadius * 1.6}px Arial, sans-serif`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(currencySymbol, centerX, centerY);
            }
            
            // Add currency icon based on current currency
            const currencySymbol = currentCurrency === 'USD' ? '$' : '៛';
            addCurrencyIcon(currencySymbol);
            console.log('[QR Code Generation] Step 4: Currency icon added:', currencySymbol);
            
            // Set canvas size after everything is drawn
            $(canvas).css({
                'width': '92px',
                'height': '92px'
            });
        }
    });
}

function renderRepaymentSchedule(schedule, summary) {
    const $tbody = $('#modalScheduleTableBody');
    if (!$tbody.length) return;
    
    let html = '';
    
    // Initial balance row
    if (summary && summary.initialBalance) {
        html += `
            <tr>
                <td class="text-center">0</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right bold">${formatNumber(summary.initialBalance)}</td>
                <td></td>
            </tr>
        `;
    }
    
    // Schedule rows
    if (schedule && Array.isArray(schedule)) {
        schedule.forEach(function(row) {
            if (row.dueno && row.dueno !== '') {
                html += `
                    <tr>
                        <td class="text-center">${row.dueno || ''}</td>
                        <td class="text-right">${formatScheduleDate(row.dayname, row.duedate)}</td>
                        <td class="text-center">${row.period || ''}</td>
                        <td class="text-right">${formatNumber(row.principal)}</td>
                        <td class="text-right">${formatNumber(row.interest)}</td>
                        <td class="text-right bold">${formatNumber(row.totalamount)}</td>
                        <td class="text-right">${formatNumber(row.bl)}</td>
                        <td class="text-right"></td>
                    </tr>
                `;
            }
        });
    }
    
    // Total row
    if (summary) {
        html += `
            <tr class="total-row">
                <td colspan="2" class="bold">សរុប : </td>
                <td class="text-center">${summary.totalDays || 0}</td>
                <td class="text-right">${formatNumber(summary.totalPrincipal)}</td>
                <td class="text-right">${formatNumber(summary.totalInterest)}</td>
                <td class="text-right">${formatNumber(summary.totalAmount)}</td>
                <td></td>
                <td></td>
            </tr>
        `;
    }
    
    $tbody.html(html);
}

function clearRepaymentSchedule() {
    $('#modalScheduleTableBody').html('<tr><td colspan="8" style="text-align: center; padding: 20px;">No schedule data available</td></tr>');
}

function formatScheduleDate(dayname, duedate) {
    if (!duedate) return '';
    const dayName = dayname || '';
    return dayName ? `${dayName}, ${duedate}` : duedate;
}

function formatNumber(value) {
    if (value === null || value === undefined || value === '') return '';
    const num = parseFloat(value);
    if (isNaN(num)) return value;
    return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function printDocument() {
    const $modal = $('#customerModal');
    if ($modal.length) {
        // Ensure modal is visible for printing
        $modal.css('display', 'flex');
        
        // Listen for afterprint to restore state
        window.addEventListener('afterprint', function restoreModalState() {
            // Remove the inline display style to restore normal state management
            $modal.css('display', '');
            // Remove this event listener after use
            window.removeEventListener('afterprint', restoreModalState);
        }, { once: true });
        
        setTimeout(function() {
            window.print();
        }, 100);
    } else {
        window.print();
    }
}

// Close modal button click handler (using jQuery for better event handling)
$(document).on('click', '.modal-btn-close', function(event) {
    event.preventDefault();
    event.stopPropagation();
    closeCustomerModal();
});

// Close modal when clicking outside
$(document).on('click', '#customerModal', function(event) {
    if ($(event.target).is('#customerModal')) {
        closeCustomerModal();
    }
});

// Close modal on Escape key
$(document).on('keydown', function(event) {
    if (event.key === 'Escape' || event.keyCode === 27) {
        const $modal = $('#customerModal');
        if ($modal.length && $modal.hasClass('active')) {
            closeCustomerModal();
        }
    }
});
