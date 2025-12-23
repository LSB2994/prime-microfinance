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
