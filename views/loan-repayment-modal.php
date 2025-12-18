<?php 
/* Loan Repayment Modal - Displays loan repayment schedule */

// Fetch schedule data with fixed ACNO
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/interceptor.php';

// Fixed ACNO value - change this to the ACNO you want to display
$fixedAcno = '00020255084000020';

$scheduleRows = [];
$scheduleError = null;
$summary = [
    'totalDays' => 0,
    'totalPrincipal' => 0,
    'totalInterest' => 0,
    'totalAmount' => 0,
    'initialBalance' => 0
];

try {
    $sql = "SELECT 
                DUENO,
                DAYNAME,
                DUEDATE,
                PERIOD,
                PRINCIPAL,
                INTEREST,
                TOTALAMOUNT,
                BL
            FROM VW_CREDIT_SCHEDULE
            WHERE ACNO = :acno
            ORDER BY DUENO";
    
    $scheduleRows = oracleFetchAll($sql, ['acno' => $fixedAcno]);
    
    // Calculate summary totals
    foreach ($scheduleRows as $row) {
        if (isset($row['period']) && is_numeric($row['period'])) {
            $summary['totalDays'] += (int)$row['period'];
        }
        if (isset($row['principal']) && is_numeric($row['principal'])) {
            $summary['totalPrincipal'] += (float)$row['principal'];
        }
        if (isset($row['interest']) && is_numeric($row['interest'])) {
            $summary['totalInterest'] += (float)$row['interest'];
        }
        if (isset($row['totalamount']) && is_numeric($row['totalamount'])) {
            $summary['totalAmount'] += (float)$row['totalamount'];
        }
    }
    
    // Get initial balance (first row's BL + first PRINCIPAL)
    if (!empty($scheduleRows) && isset($scheduleRows[0]['bl']) && is_numeric($scheduleRows[0]['bl'])) {
        $firstPrincipal = isset($scheduleRows[0]['principal']) && is_numeric($scheduleRows[0]['principal']) 
            ? (float)$scheduleRows[0]['principal'] 
            : 0;
        $firstBl = (float)$scheduleRows[0]['bl'];
        $summary['initialBalance'] = $firstBl + $firstPrincipal;
    }
} catch (Exception $e) {
    $scheduleError = $e->getMessage();
    logError('Failed to fetch loan schedule in modal', [
        'error' => $e->getMessage(),
        'acno' => $fixedAcno,
        'file' => __FILE__,
        'line' => $e->getLine()
    ]);
}

// Helper function to format date (DD-MM-YY)
function formatScheduleDateDisplay($dateString) {
    if (empty($dateString) || $dateString === null || $dateString === '') {
        return '-';
    }
    try {
        $timestamp = strtotime($dateString);
        if ($timestamp === false) {
            return $dateString;
        }
        $day = str_pad(date('d', $timestamp), 2, '0', STR_PAD_LEFT);
        $month = str_pad(date('m', $timestamp), 2, '0', STR_PAD_LEFT);
        $year = substr(date('Y', $timestamp), -2);
        return "{$day}-{$month}-{$year}";
    } catch (Exception $e) {
        return $dateString;
    }
}

// Helper function to format number
function formatScheduleNumberDisplay($value, $decimals = 2) {
    if ($value === null || $value === '' || $value === '-') {
        return '-';
    }
    if (!is_numeric($value)) {
        return $value;
    }
    return number_format((float)$value, $decimals, '.', '');
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@400;700&family=Inter:wght@400;500;600;700&display=swap');
    
    /* Modal container styling - override default modal styles */
    #loanModal.modal {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }
    
    #loanModal .modal-content.loan-schedule-modal-new {
        position: relative;
        width: 95% !important;
        max-width: 1000px !important;
        max-height: 95vh !important;
        margin: auto;
        padding: 0 !important;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border-radius: 12px;
        pointer-events: auto;
    }
    
    /* Ensure modal content doesn't block pointer events */
    #loanModal.modal * {
        pointer-events: auto;
    }
    
    #loanModal.modal .modal-content.loan-schedule-modal-new {
        pointer-events: auto;
    }
    
    .loan-schedule-modal-new {
        font-family: 'Inter', 'Noto Serif Khmer', 'Noto Sans KR', 'Dotum', 'Segoe UI', sans-serif;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    /* Header Section */
    .loan-schedule-modal-new .modal-header-new {
        position: relative;
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-shrink: 0;
        z-index: 1000;
    }
    
    .loan-schedule-modal-new .close-btn-new {
        width: 35px;
        height: 35px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #666;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.2s;
        z-index: 10001 !important;
        position: relative;
        pointer-events: auto !important;
        -webkit-user-select: none;
        user-select: none;
        touch-action: manipulation;
        margin: 0;
        padding: 0;
        outline: none;
    }
    
    .loan-schedule-modal-new .close-btn-new:hover {
        background: #f0f0f0;
        color: #000;
    }
    
    .loan-schedule-modal-new .close-btn-new:active {
        transform: scale(0.95);
    }
    
    /* Body Section */
    .loan-schedule-modal-new .modal-body-new {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0;
        position: relative;
        z-index: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }
    
    /* Footer Section */
    .loan-schedule-modal-new .modal-footer-new {
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-shrink: 0;
        z-index: 1000;
        position: relative;
    }
    
    .loan-schedule-modal-new .print-btn-new {
        background: #2563eb;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: background 0.2s;
        font-family: 'Inter', 'Noto Serif Khmer', 'Noto Sans KR', 'Dotum', 'Segoe UI', sans-serif;
        pointer-events: auto;
        -webkit-user-select: none;
        user-select: none;
        position: relative;
        z-index: 1001;
    }
    
    .loan-schedule-modal-new .print-btn-new:hover {
        background: #1d4ed8;
    }
    
    .loan-schedule-modal-new .print-btn-new:active {
        transform: scale(0.98);
    }
    
    .loan-schedule-modal-new .print-btn-new::before {
        content: "🖨️";
        font-size: 18px;
    }
    
    .loan-schedule-modal-new .document-new {
        width: 100%;
        max-width: 100%;
        margin: 0;
        background: white;
        padding: 20px;
        box-shadow: none;
        box-sizing: border-box;
        position: relative;
        z-index: 1;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    
    .loan-schedule-modal-new .header-new {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .loan-schedule-modal-new .logo-section-new {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .loan-schedule-modal-new .logo-box-new {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #2563eb 0%, #dc2626 100%);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 11px;
        text-align: center;
        padding: 6px;
    }
    
    .loan-schedule-modal-new .logo-text-new {
        display: flex;
        flex-direction: column;
    }
    
    .loan-schedule-modal-new .logo-text-new h1 {
        font-size: 16px;
        color: #2563eb;
        font-weight: 700;
        margin-bottom: 2px;
    }
    
    .loan-schedule-modal-new .logo-text-new .microfinance-new {
        font-size: 10px;
        color: #dc2626;
        font-weight: 600;
    }
    
    .loan-schedule-modal-new .logo-text-new .tagline-new {
        font-size: 8px;
        color: #666;
        font-style: italic;
        margin-top: 2px;
    }
    
    .loan-schedule-modal-new .khmer-title-new {
        font-size: 11px;
        color: #333;
        margin-top: 4px;
        line-height: 1.3;
    }
    
    .loan-schedule-modal-new .qr-section-new {
        background: #dc2626;
        color: white;
        padding: 10px;
        border-radius: 4px;
        text-align: center;
        min-width: 120px;
        flex-shrink: 0;
    }
    
    .loan-schedule-modal-new .qr-section-new h3 {
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 6px;
    }
    
    .loan-schedule-modal-new .qr-section-new p {
        font-size: 9px;
        margin-bottom: 8px;
    }
    
    .loan-schedule-modal-new .qr-code-new {
        width: 70px;
        height: 70px;
        background: white;
        margin: 0 auto;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000;
        font-size: 8px;
    }
    
    .loan-schedule-modal-new .main-title-new {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        color: #2563eb;
        margin: 20px 0;
    }
    
    .loan-schedule-modal-new .loan-details-new {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 20px;
        align-items: start;
    }
    
    @media (max-width: 1024px) {
        .loan-schedule-modal-new .loan-details-new {
            gap: 20px;
        }
        
        .loan-schedule-modal-new .detail-label-new {
            min-width: 130px;
            max-width: 130px;
        }
    }
    
    @media (max-width: 768px) {
        .loan-schedule-modal-new .loan-details-new {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }
    
    .loan-schedule-modal-new .detail-group-new {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }
    
    .loan-schedule-modal-new .detail-item-new {
        display: flex;
        flex-direction: row;
        gap: 80px;
        align-items: flex-start;
        min-height: 20px;
        margin-bottom: 4px;
    }
    
    .loan-schedule-modal-new .detail-label-new {
        font-weight: 600;
        color: #333;
        font-size: 11px;
        white-space: nowrap;
        flex-shrink: 0;
        min-width: 150px;
        max-width: 150px;
        padding-right: 8px;
        box-sizing: border-box;
    }
    
    .loan-schedule-modal-new .detail-value-new {
        color: #000;
        font-size: 11px;
        flex: 1;
        min-width: 0;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.5;
        hyphens: auto;
    }
    
    .loan-schedule-modal-new .address-value-new {
        line-height: 1.6;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .loan-schedule-modal-new .table-wrapper-new {
        width: 100%;
        max-height: calc(95vh - 500px);
        min-height: 300px;
        overflow-x: auto;
        overflow-y: auto;
        margin-bottom: 20px;
        -webkit-overflow-scrolling: touch;
        background-color: white;
        position: relative;
        display: block;
    }
    
    @media (max-height: 800px) {
        .loan-schedule-modal-new .table-wrapper-new {
            max-height: calc(95vh - 450px);
            min-height: 200px;
        }
    }
    
    @media (min-height: 900px) {
        .loan-schedule-modal-new .table-wrapper-new {
            max-height: 500px;
        }
    }
    
    .loan-schedule-modal-new .table-wrapper-new::-webkit-scrollbar {
        width: 8px;
    }
    
    .loan-schedule-modal-new .table-wrapper-new::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .loan-schedule-modal-new .table-wrapper-new::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .loan-schedule-modal-new .table-wrapper-new::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .loan-schedule-modal-new .payment-table-new {
        width: 100%;
        min-width: 800px;
        border-collapse: collapse;
        font-size: 11px;
        display: table;
        table-layout: auto;
    }
    
    .loan-schedule-modal-new .payment-table-new tbody {
        display: table-row-group;
    }
    
    .loan-schedule-modal-new .payment-table-new tr {
        display: table-row;
    }
    
    .loan-schedule-modal-new .payment-table-new td,
    .loan-schedule-modal-new .payment-table-new th {
        display: table-cell;
    }
    
    .loan-schedule-modal-new .payment-table-new thead {
        position: sticky;
        top: 0;
        z-index: 100;
        background-color: #f3f4f6;
    }
    
    .loan-schedule-modal-new .payment-table-new th {
        background-color: #f3f4f6;
        border: 1px solid #d1d5db;
        padding: 8px 6px;
        text-align: center;
        font-weight: 700;
        color: #000;
        font-size: 11px;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        white-space: nowrap;
    }
    
    .loan-schedule-modal-new .payment-table-new td {
        border: 1px solid #d1d5db;
        padding: 6px;
        text-align: center;
        font-size: 11px;
        white-space: nowrap;
    }
    
    .loan-schedule-modal-new .payment-table-new tr:nth-child(even) {
        background-color: #fafafa;
    }
    
    .loan-schedule-modal-new .payment-table-new .summary-row-new {
        background-color: #f3f4f6;
        font-weight: 700;
    }
    
    .loan-schedule-modal-new .payment-table-new .summary-row-new td:first-child {
        text-align: left;
        padding-left: 15px;
    }
    
    .loan-schedule-modal-new .text-left-new {
        text-align: left;
    }
    
    .loan-schedule-modal-new .text-right-new {
        text-align: right;
    }
    
    .loan-schedule-modal-new .footer-new {
        margin-top: 40px;
    }
    
    .loan-schedule-modal-new .footer-top-new {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    @media (max-width: 768px) {
        .loan-schedule-modal-new .footer-top-new {
            flex-direction: column;
        }
        
        .loan-schedule-modal-new .footer-right-new {
            text-align: left;
        }
    }
    
    .loan-schedule-modal-new .footer-left-new {
        display: flex;
        flex-direction: column;
        gap: 15px;
        font-size: 11px;
    }
    
    .loan-schedule-modal-new .footer-right-new {
        text-align: right;
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 11px;
    }
    
    .loan-schedule-modal-new .signature-line-new {
        border-bottom: 1px solid #000;
        min-width: 200px;
        display: inline-block;
        margin-left: 10px;
    }
    
    .loan-schedule-modal-new .notes-wrapper-new {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
        gap: 20px;
    }
    
    @media (max-width: 768px) {
        .loan-schedule-modal-new .notes-wrapper-new {
            flex-direction: column;
        }
    }
    
    .loan-schedule-modal-new .notes-new {
        flex: 1;
    }
    
    .loan-schedule-modal-new .print-button-container-new {
        display: flex;
        align-items: flex-end;
        padding-top: 20px;
    }
    
    .loan-schedule-modal-new .print-btn-inline-new {
        background: #2563eb;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        font-family: 'Inter', 'Noto Serif Khmer', 'Noto Sans KR', 'Dotum', 'Segoe UI', sans-serif;
        white-space: nowrap;
    }
    
    .loan-schedule-modal-new .print-btn-inline-new:hover {
        background: #1d4ed8;
    }
    
    .loan-schedule-modal-new .print-btn-inline-new::before {
        content: "🖨️";
        font-size: 16px;
    }
    
    .loan-schedule-modal-new .notes-new h4 {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 15px;
        color: #333;
    }
    
    .loan-schedule-modal-new .notes-new ul {
        list-style: none;
        padding-left: 0;
    }
    
    .loan-schedule-modal-new .notes-new li {
        margin-bottom: 12px;
        padding-left: 20px;
        position: relative;
        line-height: 1.6;
        font-size: 11px;
    }
    
    .loan-schedule-modal-new .notes-new li::before {
        content: "•";
        position: absolute;
        left: 0;
        color: #2563eb;
        font-weight: bold;
        font-size: 18px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1024px) {
        #loanModal .modal-content.loan-schedule-modal-new {
            width: 98%;
            max-width: 98%;
        }
        
        .loan-schedule-modal-new .document-new {
            padding: 12px;
        }
    }
    
    @media (max-width: 768px) {
        .loan-schedule-modal-new .header-new {
            flex-direction: column;
        }
        
        .loan-schedule-modal-new .qr-section-new {
            width: 100%;
            min-width: auto;
        }
        
        .loan-schedule-modal-new .table-wrapper-new {
            max-height: 300px;
        }
        
        .loan-schedule-modal-new .modal-header-new {
            padding: 10px 15px;
        }
        
        .loan-schedule-modal-new .close-btn-new {
            width: 30px;
            height: 30px;
            font-size: 18px;
        }
        
        .loan-schedule-modal-new .modal-footer-new {
            padding: 10px 15px;
        }
        
        .loan-schedule-modal-new .print-btn-new {
            padding: 10px 20px;
            font-size: 14px;
        }
    }
    
    @media print {
        .loan-schedule-modal-new .close-btn-new,
        .loan-schedule-modal-new .print-btn-new,
        .loan-schedule-modal-new .print-btn-inline-new {
            display: none !important;
        }
        
        .loan-schedule-modal-new .document-new {
            box-shadow: none;
            padding: 10px 15px;
            margin: 0;
            max-width: 100%;
            overflow: visible;
        }
        
        #loanModal .modal-content.loan-schedule-modal-new {
            max-width: 100%;
            max-height: none;
            overflow: visible;
        }
        
        .loan-schedule-modal-new .header-new {
            margin-bottom: 10px;
            padding-bottom: 10px;
            page-break-inside: avoid;
        }
        
        .loan-schedule-modal-new .main-title-new {
            margin: 10px 0;
            page-break-after: avoid;
        }
        
        .loan-schedule-modal-new .loan-details-new {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .loan-schedule-modal-new .table-wrapper-new {
            max-height: none !important;
            overflow: visible !important;
            height: auto !important;
            page-break-inside: auto;
            margin-bottom: 15px;
        }
        
        .loan-schedule-modal-new .payment-table-new {
            page-break-inside: auto;
            width: 100%;
        }
        
        .loan-schedule-modal-new .payment-table-new thead {
            display: table-header-group;
        }
        
        .loan-schedule-modal-new .payment-table-new tbody {
            display: table-row-group;
        }
        
        .loan-schedule-modal-new .payment-table-new tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        .loan-schedule-modal-new .payment-table-new th,
        .loan-schedule-modal-new .payment-table-new td {
            page-break-inside: avoid;
        }
        
        .loan-schedule-modal-new .footer-new {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        
        .loan-schedule-modal-new .footer-top-new {
            margin-bottom: 20px;
        }
        
        .loan-schedule-modal-new .notes-wrapper-new {
            margin-top: 15px;
            padding-top: 15px;
            page-break-inside: avoid;
        }
    }
</style>

<!-- Loan Repayment Modal -->
<div id="loanModal" class="modal">
    <div class="modal-content loan-schedule-modal-new">
        <!-- Header -->
        <header class="modal-header-new">
            <button class="close-btn-new" type="button" aria-label="Close" id="loanModalCloseBtn">×</button>
        </header>
        
        <!-- Body -->
        <div class="modal-body-new">
            <div class="document-new">
            <div class="header-new">
                <div class="logo-section-new">
                    <div class="logo-box-new">PRIME<br>MF</div>
                    <div class="logo-text-new">
                        <h1>PRIME MF</h1>
                        <div class="microfinance-new">Micro finance</div>
                        <div class="tagline-new">Join us, Succeed Together!</div>
                        <div class="khmer-title-new">គ្រឹះស្ថានមីក្រូហិរញ្ញវត្ថុ ប្រាយម៍ អិមអេហ្វ អិលធីឌី</div>
                    </div>
                </div>
                <div class="qr-section-new">
                    <h3>KHORN</h3>
                    <p>Prime Microfinance</p>
                    <div class="qr-code-new">QR CODE</div>
                </div>
            </div>
            
            <h2 class="main-title-new">តារាងកាលវិភាគសងប្រាក់</h2>
            
            <div class="loan-details-new">
                <div class="detail-group-new">
                    <div class="detail-item-new">
                        <span class="detail-label-new">ការិយាល័យ (Office):</span>
                        <span class="detail-value-new" id="detail-office">0002-010 500 224 PCT</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">គណនីឥណទាន (Loan Account):</span>
                        <span class="detail-value-new" id="detail-acno">000202550840000020</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">គណនីអតិថិជន (Customer Account):</span>
                        <span class="detail-value-new" id="detail-customer-account">00022004200</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">កាលបរិច្ឆេទផ្តល់កម្ចី (Loan Disbursement Date):</span>
                        <span class="detail-value-new" id="detail-disburse-date">20/09/24</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">លេខគណនីសន្សំ (Savings Account No):</span>
                        <span class="detail-value-new" id="detail-deposit-account">00020211002043083</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">អត្រាការប្រាក់ (Interest Rate):</span>
                        <span class="detail-value-new" id="detail-interest-rate">1.30 %/ខែ</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">អាស័យដ្ឋានអ្នកថ្មី (New Address):</span>
                        <span class="detail-value-new address-value-new" id="detail-address">ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់ ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</span>
                    </div>
                </div>
                
                <div class="detail-group-new">
                    <div class="detail-item-new">
                        <span class="detail-label-new">កម្ចីរគ្គទី (Loan Type/Round):</span>
                        <span class="detail-value-new" id="detail-loan-cycle">4 / មន្ត្រីគណទាន / Trann Sotry(010 700 933)</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">អតិថិជនឈ្មោះ (Customer Name):</span>
                        <span class="detail-value-new" id="detail-customer-name">ឌុក ផល្លី / Duk Phally</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">អ្នករួមន្ទីឈ្មោះ (Co-Borrower Name):</span>
                        <span class="detail-value-new" id="detail-coborrower-name">តូច ឡេណា / Touch Lena</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">កាលបរិច្ឆេទបញ្ចប់នៃកម្ចី (Loan End Date):</span>
                        <span class="detail-value-new" id="detail-maturity-date">20/09/24</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">រយៈពេលខ្លី (Duration):</span>
                        <span class="detail-value-new" id="detail-period">84 ខែ</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">របៀបសងប្រាក់ (Payment Method):</span>
                        <span class="detail-value-new" id="detail-loan-type"></span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">សេវាប្រចាំខែ (Monthly Service Fee):</span>
                        <span class="detail-value-new" id="detail-service-fee">84 ខែ</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">ចំនួនទឹកប្រាក់ (Amount):</span>
                        <span class="detail-value-new" id="detail-amount">12,000.00 ដុល្លារ</span>
                    </div>
                    <div class="detail-item-new">
                        <span class="detail-label-new">លេខទូរស័ព្ទ (Phone Number):</span>
                        <span class="detail-value-new" id="detail-phone">+(855) 010 500 224</span>
                    </div>
                </div>
            </div>
            
            <div class="table-wrapper-new">
                <table class="payment-table-new">
                    <thead>
                        <tr>
                            <th>ល.រ</th>
                            <th>ថ្ងៃខែឆ្នាំសងប្រាក់</th>
                            <th>ចំនួនថ្ងៃ</th>
                            <th>ប្រាក់ដើម</th>
                            <th>ការប្រាក់</th>
                            <th>សរុប</th>
                            <th>សមតុល្យប្រាក់ដើម</th>
                            <th>ផ្សេងៗ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($scheduleError): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: red; padding: 20px;">
                                    Error loading schedule: <?php echo htmlspecialchars($scheduleError); ?>
                                </td>
                            </tr>
                        <?php elseif (empty($scheduleRows)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 20px;">
                                    No schedule data found for ACNO: <?php echo htmlspecialchars($fixedAcno); ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <!-- Initial balance row -->
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-right-new"><?php echo formatScheduleNumberDisplay($summary['initialBalance']); ?></td>
                                <td></td>
                            </tr>
                            
                            <?php foreach ($scheduleRows as $index => $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['dueno'] ?? ($index + 1)); ?></td>
                                    <td class="text-left-new">
                                        <?php 
                                        $dayName = isset($row['dayname']) ? htmlspecialchars($row['dayname']) : '';
                                        $dueDate = isset($row['duedate']) ? formatScheduleDateDisplay($row['duedate']) : '-';
                                        if ($dayName && $dueDate !== '-') {
                                            echo $dayName . ', ' . $dueDate;
                                        } else {
                                            echo $dueDate;
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo formatScheduleNumberDisplay($row['period'] ?? '', 0); ?></td>
                                    <td class="text-right-new"><?php echo formatScheduleNumberDisplay($row['principal'] ?? ''); ?></td>
                                    <td class="text-right-new"><?php echo formatScheduleNumberDisplay($row['interest'] ?? ''); ?></td>
                                    <td class="text-right-new"><?php echo formatScheduleNumberDisplay($row['totalamount'] ?? ''); ?></td>
                                    <td class="text-right-new"><?php echo formatScheduleNumberDisplay($row['bl'] ?? ''); ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <!-- Summary row -->
                            <tr class="summary-row-new">
                                <td>សរុប :</td>
                                <td></td>
                                <td><?php echo formatScheduleNumberDisplay($summary['totalDays'], 0); ?></td>
                                <td class="text-right-new"><?php echo formatScheduleNumberDisplay($summary['totalPrincipal']); ?></td>
                                <td class="text-right-new"><?php echo formatScheduleNumberDisplay($summary['totalInterest']); ?></td>
                                <td class="text-right-new"><?php echo formatScheduleNumberDisplay($summary['totalAmount']); ?></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="footer-new">
                <div class="footer-top-new">
                    <div class="footer-left-new">
                        <div>
                            <span>ថ្ងៃខែឆ្នាំ <?php echo date('d/m/Y'); ?></span>
                        </div>
                        <div>
                            <span>រៀបចំដោយៈ</span>
                            <span class="signature-line-new"></span>
                        </div>
                        <div>
                            <span>ឈ្នោះ..........</span>
                            <span class="signature-line-new"></span>
                        </div>
                    </div>
                    <div class="footer-right-new">
                        <div>ស្នាមមេដៃអ្នកទទួលប្រាក់</div>
                        <div id="detail-customer-name-footer">ឌុក ផល្លី / Duk Phally</div>
                    </div>
                </div>
                
                <div class="notes-wrapper-new">
                    <div class="notes-new">
                        <h4>កំណត់សំគាល់</h4>
                        <ul>
                            <li>ការមិនគោរពតាមកិច្ចសន្យា គ្រឹះស្ថានមីក្រូហិរញ្ញវត្ថុ ប្រាយម៍ អិមអេហ្វ អិលធីឌី នឹងចាត់វិធានការតាមផ្លូវច្បាប់</li>
                            <li>អតិថិជនត្រូវមកសងប្រាក់អោយបានទៀងទាត់តាមតារាងកាលវិភាគសងប្រាក់ដែលបានចែកជូនកំណត់ទុក</li>
                            <li>ការទទួលប្រាក់ ៖ ត្រូវមកបង់ប្រាក់នៅការិយាល័យផ្ទាល់ ពីម៉ោង ៨: ០០ ព្រឹក ដល់ ៣: ៣០ រសៀល<br>និងអាចបង់ប្រាក់តាមរយៈភ្នាក់ងារទ្រូម៉ាន់នី (​​True money) ដែលនៅជិតលោកអ្នក លេខកូដស្ថាប័ន (2031)<br>និងតាមរយៈវីង (wing) លេខកូដស្ថាប័នសម្រាប់តារាងបង់ប្រាក់ជាដុល្លារ USD (6162)<br>និងតារាងបង់ប្រាក់ជាខ្មែរ KHR (6163)</li>
                            <li>រាល់ការសងប្រាក់ចំថ្ងៃ ឈប់សំរាក ថ្ងៃសៅរ៍អាទិត្យ រឺ ថ្ងៃបុណ្យត្រូវមកសងមួយថ្ងៃ មុនកាលកំណត់ត្រូវសង</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        </div>
        
        <!-- Footer -->
        <footer class="modal-footer-new">
            <button class="print-btn-new" onclick="window.print()" type="button">Print</button>
        </footer>
    </div>
</div>

<script>
// Ensure close button is clickable and table scroll works properly
(function() {
    function initModalClose() {
        const closeBtn = document.querySelector('#loanModal .close-btn-new, #loanModalCloseBtn');
        if (closeBtn) {
            // Remove any existing listeners
            const newCloseBtn = closeBtn.cloneNode(true);
            closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
            
            // Add click handler
            newCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Try multiple methods to close
                if (typeof closeLoanModal === 'function') {
                    closeLoanModal();
                } else {
                    const modal = document.getElementById('loanModal');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                    }
                }
                return false;
            }, true);
            
            // Also handle touch events for mobile
            newCloseBtn.addEventListener('touchend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof closeLoanModal === 'function') {
                    closeLoanModal();
                } else {
                    const modal = document.getElementById('loanModal');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                    }
                }
                return false;
            }, true);
        }
    }
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModalClose);
    } else {
        initModalClose();
    }
    
    // Also initialize when modal is shown (in case it's dynamically loaded)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const modal = document.getElementById('loanModal');
                if (modal && modal.classList.contains('active')) {
                    setTimeout(initModalClose, 100);
                }
            }
        });
    });
    
    const modal = document.getElementById('loanModal');
    if (modal) {
        observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
        initModalClose();
    }
    
    // Ensure table wrapper scrolls properly
    function initTableScroll() {
        const tableWrapper = document.querySelector('.loan-schedule-modal-new .table-wrapper-new');
        if (tableWrapper) {
            // Force scroll to work
            tableWrapper.style.overflowY = 'auto';
            tableWrapper.style.overflowX = 'auto';
            
            // Calculate proper height based on viewport
            // const modalContent = document.querySelector('#loanModal .modal-content.loan-schedule-modal-new');
            // if (modalContent) {
            //     const header = document.querySelector('.loan-schedule-modal-new .modal-header-new');
            //     const footer = document.querySelector('.loan-schedule-modal-new .modal-footer-new');
            //     const documentContent = document.querySelector('.loan-schedule-modal-new .document-new');
                
            //     if (header && footer && documentContent) {
            //         const headerHeight = header.offsetHeight;
            //         const footerHeight = footer.offsetHeight;
            //         const availableHeight = window.innerHeight * 0.95;
            //         const otherContentHeight = headerHeight + footerHeight + 400; // Approximate other content
            //         const tableMaxHeight = availableHeight - otherContentHeight;
                    
            //         if (tableMaxHeight > 200) {
            //             tableWrapper.style.maxHeight = tableMaxHeight + 'px';
            //         }
            //     }
            // }
        }
    }
    
    // Initialize table scroll
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initTableScroll, 200);
        });
    } else {
        setTimeout(initTableScroll, 200);
    }
    
    // Recalculate on window resize
    window.addEventListener('resize', function() {
        setTimeout(initTableScroll, 100);
    });
    
    // Recalculate when modal is shown
    if (modal) {
        const modalObserver = new MutationObserver(function() {
            if (modal.classList.contains('active')) {
                setTimeout(initTableScroll, 200);
            }
        });
        modalObserver.observe(modal, { attributes: true, attributeFilter: ['class'] });
    }
})();
</script>
