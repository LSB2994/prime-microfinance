<?php /* Loan Repayment / Customer Detail Modal (shared partial for customer page) */ ?>

    <!-- Loan Repayment Modal -->
    <div id="loanModal" class="modal">
        <div class="modal-content printable-content loan-schedule-modal">
            <div class="modal-header">
                <button class="close-btn" onclick="closeLoanModal()">×</button>
            </div>
            <div class="modal-body loan-schedule-body">
                <!-- Header with Logo -->
                <div class="loan-schedule-logo-header">
                    <img src="https://www.figma.com/api/mcp/asset/ce1bf687-15fb-404d-918f-34d46c6c4bfc" alt="PRIME Logo" class="schedule-logo-img">
                </div>
                
                <!-- Title -->
                <h2 class="schedule-title-new">តារាងកាលវិភាគសងប្រាក់</h2>
                
                <!-- Loan Details Section -->
                <div class="loan-details-section-new">
                    <div class="loan-details-left-new">
                        <div class="detail-row-new">
                            <span class="detail-label-new">ការិយាល័យ</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-office">0002-010 500 224 PCT</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">គណនីឥណទាន</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-acno">00020255084000020</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">គណនីអតិថិជន</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-customer-account">00022004200</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">កាលបរិច្ឆេទផ្តល់កម្ចី</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-disburse-date">20/09/24</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">លេខគណនីសន្សំ</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-deposit-account">00020211002043083</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">អត្រាការប្រាក់</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-interest-rate">1.30 %/ 1ខែ</span>
                        </div>
                    </div>
                    <div class="loan-details-right-new">
                        <div class="detail-row-new">
                            <span class="detail-label-new">កម្ចីវគ្គទី</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-loan-cycle">4</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">អតិថិជនឈ្មោះ</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-customer-name">ឌុក ផល្លី / Duk Phally</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">អ្នករួមខ្ចីឈ្មោះ</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-coborrower-name">តូច ឡេណា / Touch Lena</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">កាលបរិចេ្ឆទបញ្ចប់នៃកម្ចី</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-maturity-date">20/09/24</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">រយៈពេលខ្ចី</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-period">84 ខែ</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">របៀបសងប្រាក់</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-loan-type"></span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">សេវាប្រចាំខែ</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new" id="detail-service-fee">0.40%</span>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Details -->
                <div class="loan-additional-details">
                    <div class="detail-row-new">
                        <span class="detail-label-new">ចំនួនទឹកប្រាក់</span>
                        <span class="detail-separator">៖</span>
                        <span class="detail-value-new detail-value-bold" id="detail-amount">12,000.00 ដុល្លារ</span>
                    </div>
                    <div class="detail-row-new">
                        <span class="detail-label-new">លេខទូរស័ព្ទ</span>
                        <span class="detail-separator">៖</span>
                        <span class="detail-value-new" id="detail-phone">(855) 010 500 224</span>
                    </div>
                    <div class="detail-row-new address-row">
                        <span class="detail-label-new">អាស័យដ្ឋានអ្នកខ្ចី:</span>
                        <span class="detail-separator">៖</span>
                        <span class="detail-value-new" id="detail-address">ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់ ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</span>
                    </div>
                </div>

                <!-- Repayment Schedule Table -->
                <div class="repayment-schedule-table-new">
                    <table class="schedule-table-new">
                        <thead>
                            <tr>
                                <th class="col-no">ល.រ</th>
                                <th class="col-date">ថ្ងៃខែឆ្នាំសងប្រាក់</th>
                                <th class="col-days">ចំនួនថ្ងៃ</th>
                                <th class="col-principal">ប្រាក់ដើម</th>
                                <th class="col-interest">ការប្រាក់</th>
                                <th class="col-total">សរុប</th>
                                <th class="col-balance"><p style="margin:0;padding:0;margin-bottom:12px;">សមតុល្យប្រាក់</p><p style="margin:0;padding:0;">ដើម</p></th>
                                <th class="col-other">ផ្សេងៗ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="initial-row">
                                <td>0</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td class="balance-cell">12,000.00</td>
                                <td>-</td>
                            </tr>
                            <?php 
                            $dates = ['សុក្រ', 'អង្គារ', 'ព្រហ', 'ច័ន្ទ', 'សុក្រ', 'សុក្រ', 'សុក្រ', 'សុក្រ', 'សុក្រ', 'សុក្រ', 'សុក្រ', 'សុក្រ'];
                            $balances = [11865.20, 11843.63, 11814.98, 11805.91, 11769.92, 11726.67, 11702.67, 11678.25, 11646.78, 11614.78, 11588.80, 11562.38];
                            for($i = 1; $i <= 12; $i++): 
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $dates[$i-1]; ?>, 04-10-2024</td>
                                <td>14</td>
                                <td class="text-right">134.80</td>
                                <td class="text-right">72.80</td>
                                <td class="text-right total-amount-cell">230.00</td>
                                <td class="text-right balance-cell" style="line-height: 1.2;"><?php echo number_format($balances[$i-1], 2); ?></td>
                                <td>-</td>
                            </tr>
                            <?php endfor; ?>
                            <tr class="summary-row-new">
                                <td class="summary-label">សរុប :</td>
                                <td></td>
                                <td class="summary-value">2540</td>
                                <td class="text-right summary-value">12,000.00</td>
                                <td class="text-right summary-value">11,148.51</td>
                                <td class="text-right summary-value total-amount-cell">26,578.82</td>
                                <td class="text-right balance-cell" style="line-height: 1.2;"></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Section -->
                <div class="loan-schedule-footer-new">
                    <div class="footer-left">
                        <div class="footer-date-row">
                            <span class="footer-label-new">ថ្ងៃខែឆ្នាំៈ</span>
                            <span class="footer-value-new">02/12/2025</span>
                        </div>
                        <div class="footer-prepared-row">
                            <span class="footer-label-new">រៀបចំដោយ: </span>
                        </div>
                        <div class="footer-name-row">
                            <span class="footer-label-new">ឈ្មោះ</span>
                            <span class="footer-dots">...........................................................</span>
                        </div>
                        <div class="footer-notes">
                            <p class="notes-title">កំណត់សំគាល់៖</p>
                            <p>- ការមិនគោរពតាមកិច្ចសន្យា គ្រឹះស្ថានមីក្រូហិរញ្ញវត្ថុ ប្រាយម៍ អិមអេហ្វ អិលធីឌី នឹងចាត់វិធានការតាមផ្លូវច្បាប់</p>
                            <p>- អតិថិជនត្រូវមកសងប្រាក់អោយបានទៀងទាត់តាមតារាងកាលវិភាគសងប្រាក់ដែលបានចែកជូនកំណត់ទុក</p>
                            <p>- ការទទួលប្រាក់ ៖ ត្រូវមកបង់ប្រាក់នៅការិយាល័យផ្ទាល់ ពីម៉ោង ៨: ០០ ព្រឹក ដល់ ៣: ៣០ រសៀល</p>
                            <p>- និងអាចបង់ប្រាក់តាមរយៈភ្នាក់ងារទ្រូម៉ាន់នី <em>(True money)</em> ដែលនៅជិតលោកអ្នក លេខកូដស្ថាប័ន <em>(2031)</em></p>
                            <p>- និងតាមរយៈវីង <em>(wing)</em> លេខកូដស្ថាប័នសម្រាប់តារាងបង់ប្រាក់ជាដុល្លារ USD (6162)</p>
                            <p>- និងតារាងបង់ប្រាក់ជាខ្មែរ <em>KHR (6163)</em></p>
                            <p>- រាល់ការសងប្រាក់ចំថ្ងៃ ឈប់សំរាក ថ្ងៃសៅរ៍អាទិត្យ រឺ ថ្ងៃបុណ្យត្រូវមកសងមួយថ្ងៃ មុនកាលកំណត់ត្រូវសង</p>
                        </div>
                    </div>
                    <div class="footer-right">
                        <div class="signature-section-new">
                            <p class="signature-label-new">ស្នាមមេដៃអ្នកទទួលប្រាក់</p>
                            <p class="signature-name-new">ឌុក ផល្លី / Duk Phally</p>
                        </div>
                        <div class="qr-code-section">
                            <div class="qr-code-box-new">
                                <div class="qr-code-inner">
                                    <img src="https://www.figma.com/api/mcp/asset/e5b35b0e-9b4f-4269-9b98-c9b7062f31ea" alt="KHQR Logo" class="qr-logo">
                                    <img src="https://www.figma.com/api/mcp/asset/fc064ff0-5b50-4ece-ae70-fd3bfecb1172" alt="QR Code" class="qr-code-img">
                                </div>
                            </div>
                            <p class="qr-label-new">Prime Microfinance</p>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="print-btn" onclick="printLoanSchedule()">
                        <img src="<?php echo baseUrl('/assets/images/PrintButton.svg'); ?>" alt="Print" class="print-btn-img">
                    </button>
                </div>
            </div>
        </div>
    </div>

