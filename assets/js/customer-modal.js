// Store current customer data for currency change
let currentCustomerData = null;

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
        ctmaddress: $row.attr('data-ctmaddress') || '',
        currency: $row.attr('data-currency') || 'USD' // Default to USD if not specified
    };
    
    // Store current customer data
    currentCustomerData = data;
    
    // Set currency selector value and display
    const $currencySelect = $('#currencySelect');
    const $currencyDisplay = $('#currencyDisplay');
    if ($currencySelect.length) {
        $currencySelect.val(data.currency.toUpperCase());
    }
    if ($currencyDisplay.length) {
        const currencyText = data.currency.toUpperCase() === 'KHR' ? 'KHR (៛)' : 'USD ($)';
        $currencyDisplay.text(currencyText);
    }
    
    // Populate info table
    populateModalInfo(data);
    
    // Load and populate schedule if acno exists
    if (data.acno) {
        loadRepaymentSchedule(data.acno);
    } else {
        clearRepaymentSchedule();
    }
    
    // Load QR code from API
    loadQRCode(data);
    
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
            <td colspan="2" class="modal-info-value">៖ ${data.loancycle || ''} / មន្ត្រីឥណទាន / ${data.coname || ''}${data.phone ? '(' + formatPhone(data.phone) + ')' : ''}</td>
        </tr>
        <tr>
            <td class="modal-info-label modal-font-weight-bold">គណនីឥណទាន</td>
            <td class="modal-info-value modal-font-weight-bold">៖ ${data.acno || ''}</td>
            <td colspan="2" class="modal-info-label modal-font-weight-bold">អតិថិជនឈ្មោះ</td>
            <td colspan="2" class="modal-info-value modal-font-weight-bold">៖ ${data.customerName || ''}</td>
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

function renderRepaymentSchedule(schedule, summary) {
    const $tbody = $('#modalScheduleTableBody');
    if (!$tbody.length) return;
    
    let html = '';
    
    // Initial balance row
    if (summary && summary.initialBalance) {
        html += `
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right">${formatNumber(summary.initialBalance)}</td>
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
                        <td>${formatScheduleDate(row.dayname, row.duedate)}</td>
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

// Get Webill API access token
function getWebillAccessToken(callback) {
    $.ajax({
        url: 'https://apitest-va.webill365.com/kh/api/wbi/client/v1/auth/token',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            client_id: 'aec6fef2e90e26975ec95abd18b1fb77',
            client_secret: '13fd6fea8da76e2cde034590b6c2c55a'
        }),
        dataType: 'json',
        success: function(result) {
            if (result.status && result.status.code === 200 && result.data && result.data.access_token) {
                callback(null, result.data.access_token);
            } else {
                callback('Failed to get access token', null);
            }
        },
        error: function(xhr, status, error) {
            callback('Error getting access token: ' + error, null);
        }
    });
}

// Helper function to generate QR code from khqr_data and add logo overlay
function generateQRCodeWithLogo(khqrData, currencyCode, callback) {
    if (typeof QRCode === 'undefined' || !QRCode.toCanvas) {
        console.error('QRCode library not loaded');
        callback(null);
        return;
    }
    
    const currency = (currencyCode || 'USD').toUpperCase();
    const qrSize = 240; // QR code size
    const logoRadius = Math.round(qrSize * 0.09); // ~9% of QR size
    
    // Create canvas for QR code
    const canvas = document.createElement('canvas');
    canvas.width = qrSize;
    canvas.height = qrSize;
    
    // Generate QR code from khqr_data
    QRCode.toCanvas(canvas, khqrData, {
        errorCorrectionLevel: 'H', // High error correction for logo overlay
        width: qrSize,
        margin: 1,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        }
    }, function(error) {
        if (error) {
            console.error('Error generating QR code:', error);
            callback(null);
            return;
        }
        
        const ctx = canvas.getContext('2d');
        const logoCenterX = qrSize / 2;
        const logoCenterY = qrSize / 2;
        
        // Draw circular white background for logo
        ctx.beginPath();
        ctx.arc(logoCenterX, logoCenterY, logoRadius, 0, 2 * Math.PI);
        ctx.fillStyle = '#FFFFFF';
        ctx.fill();
        
        // Draw circular black border
        ctx.beginPath();
        ctx.arc(logoCenterX, logoCenterY, logoRadius, 0, 2 * Math.PI);
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 1.5;
        ctx.stroke();
        
        // Draw currency symbol
        ctx.fillStyle = '#000000';
        const fontSize = Math.round(qrSize * 0.11); // ~11% of QR size
        ctx.font = 'bold ' + fontSize + 'px Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        // Currency symbol based on currency code
        const currencySymbol = currency === 'KHR' ? '៛' : '$';
        
        ctx.fillText(currencySymbol, logoCenterX, logoCenterY);
        
        // Convert canvas to base64 image
        const qrWithLogo = canvas.toDataURL('image/png');
        callback(qrWithLogo);
    });
}

function loadQRCode(data) {
    const $qrImage = $('#qrCodeImage');
    
    if (!$qrImage.length) return;
    
    // Show loading state
    $qrImage.attr('src', '');
    $qrImage.css('opacity', '0.5');
    
    // Step 1: Get access token
    getWebillAccessToken(function(tokenError, accessToken) {
        if (tokenError || !accessToken) {
            console.error('Failed to get access token:', tokenError);
            const baseUrl = $('body').attr('data-base-url') || '';
            $qrImage.attr('src', `${baseUrl}/assets/images/qr.png`);
            $qrImage.css('opacity', '1');
            return;
        }
        
        // Step 2: Determine currency and parent account number
        // Default to USD, but check if currency is specified in data
        const currency = (data.currency || 'USD').toUpperCase();
        let parentAccountNo = '';
        let currencyCode = 'USD';
        
        if (currency === 'KHR') {
            parentAccountNo = '1120000805758';
            currencyCode = 'KHR';
        } else {
            // Default to USD
            parentAccountNo = '1120000106664';
            currencyCode = 'USD';
        }
        
        // Step 3: Prepare data for QR collection API
        const requestData = {
            payer_name: data.customerName || 'Customer',
            parent_account_no: parentAccountNo,
            payment_type: '0',
            currency_code: currencyCode,
            amount: parseFloat(data.dbamt) || 0,
            remark: `Loan Account: ${data.acno || ''}`,
            khqr_name: '',
            request_id: data.acno || Date.now().toString()
        };
        
        // Step 4: Call QR collection API
        $.ajax({
            url: 'https://apitest-va.webill365.com/kh/api/wbi/client/v1/qr-collections',
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + accessToken,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(requestData),
            dataType: 'json',
            success: function(result) {
                console.log('QR collection API response:', result);
                if (result.status && result.status.code === 200 && result.data) {
                    // Use khqr_data to generate QR code with logo
                    if (result.data.khqr_data) {
                        console.log('Using khqr_data to generate QR code with logo');
                        generateQRCodeWithLogo(result.data.khqr_data, currencyCode, function(qrWithLogo) {
                            if (qrWithLogo) {
                                $qrImage.attr('src', qrWithLogo);
                                $qrImage.attr('alt', 'QR Code');
                                $qrImage.css('opacity', '1');
                            } else {
                                // Fallback to khqr_data_base64 if available
                                if (result.data.khqr_data_base64) {
                                    console.log('Falling back to khqr_data_base64');
                                    $qrImage.attr('src', result.data.khqr_data_base64);
                                    $qrImage.attr('alt', 'QR Code');
                                    $qrImage.css('opacity', '1');
                                } else {
                                    const baseUrl = $('body').attr('data-base-url') || '';
                                    $qrImage.attr('src', `${baseUrl}/assets/images/qr.png`);
                                    $qrImage.css('opacity', '1');
                                }
                            }
                        });
                    } else if (result.data.khqr_data_base64) {
                        // Fallback: use base64 if khqr_data is not available
                        console.log('Using khqr_data_base64 (khqr_data not available)');
                        $qrImage.attr('src', result.data.khqr_data_base64);
                        $qrImage.attr('alt', 'QR Code');
                        $qrImage.css('opacity', '1');
                    } else {
                        console.warn('QR collection API returned no QR data', result);
                        const baseUrl = $('body').attr('data-base-url') || '';
                        $qrImage.attr('src', `${baseUrl}/assets/images/qr.png`);
                        $qrImage.css('opacity', '1');
                    }
                } else {
                    console.warn('QR collection API returned error', result);
                    const baseUrl = $('body').attr('data-base-url') || '';
                    $qrImage.attr('src', `${baseUrl}/assets/images/qr.png`);
                    $qrImage.css('opacity', '1');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error calling QR collection API:', error);
                const baseUrl = $('body').attr('data-base-url') || '';
                $qrImage.attr('src', `${baseUrl}/assets/images/qr.png`);
                $qrImage.css('opacity', '1');
            }
        });
    });
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

function closeCustomerModal() {
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

// Handle currency selection change
$(document).on('change', '#currencySelect', function() {
    if (currentCustomerData) {
        // Update currency in current customer data
        const selectedCurrency = $(this).val();
        currentCustomerData.currency = selectedCurrency;
        
        // Update currency display text
        const $currencyDisplay = $('#currencyDisplay');
        if ($currencyDisplay.length) {
            const currencyText = selectedCurrency.toUpperCase() === 'KHR' ? 'KHR (៛)' : 'USD ($)';
            $currencyDisplay.text(currencyText);
        }
        
        // Regenerate QR code with new currency
        loadQRCode(currentCustomerData);
    }
});
