<!-- Customer Detail Modal -->
<div id="customerModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-actions">
                <button class="modal-btn-icon modal-btn-close" onclick="closeCustomerModal()" title="Close">
                    <svg width="20" height="20" viewBox="0 0 16 16" fill="none">
                        <path d="M12 4L4 12M4 4l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="modal-body">
            <div class="modal-document-content">
                <div class="modal-header-content">
                    <div class="modal-header-top">
                        <div class="modal-logo-section">
                            <div class="modal-logo">
                                <img src="<?php echo baseUrl('/assets/images/main_logo.png'); ?>" alt="PRIME MF Logo">
                            </div>
                        </div>
                        <div class="modal-qr-section">
                            <div class="modal-qr-frame" id="qrCodeContainer">
                                <img src="<?php echo baseUrl('/assets/images/svg/khqr_frame.svg'); ?>" alt="KHQR Frame" class="qr-frame-bg">
                                <canvas id="qrCodeCanvas" style="display: none;"></canvas>
                                <div id="qrCodeDisplay" class="qr-code-overlay">
                                    <div class="qr-currency-icon" id="qrCurrencyIcon">$</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-document-title">តារាងកាលវិភាគសងប្រាក់</div>
                </div>

                <div class="modal-info-section">
                    <table class="modal-info-table">
                        <colgroup>
                            <col style="width: 10%;">
                            <col style="width: 22%;">
                            <col style="width: 10%;">
                            <col style="width: 0px;">
                            <col style="width: 13%;">
                            <col style="width: 15%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="modalInfoTableBody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="modal-table-section">
                    <table class="repayment-table">
                        <colgroup>
                            <col style="width: 3%;">
                            <col style="width: 12%;">
                            <col style="width: 5%;">
                            <col style="width: 5%;">
                            <col style="width: 5%;">
                            <col style="width: 8%;">
                            <col style="width: 8%;">
                            <col style="width: 10%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">ល.រ</th>
                                <th>ថ្ងៃខែឆ្នាំសងប្រាក់</th>
                                <th class="text-center">ចំនួនថ្ងៃ</th>
                                <th class="text-center">ប្រាក់ដើម</th>
                                <th class="text-center">ការប្រាក់</th>
                                <th class="text-center">សរុប</th>
                                <th class="text-center">សមតុល្យប្រាក់ដើម</th>
                                <th class="text-center">ផ្សេងៗ</th>
                            </tr>
                        </thead>
                        <tbody id="modalScheduleTableBody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer-content">
                    <div class="modal-signature-section">
                        <div class="modal-signature-block">
                            <div class="modal-footer-date" id="modalFooterDate">ថ្ងៃខែឆ្នាំ៖ </div>
                            <div class="modal-signature-label" style="margin-top: 8px;">រៀបចំដោយ៖</div>
                        </div>
                        <div class="modal-signature-block right">
                            <div class="modal-signature-label" style="margin-bottom: 20px !important;">ស្នាមមេដៃអ្នកទទួលប្រាក់</div>
                            <div class="modal-signature-name" id="modalSignatureName"></div>
                        </div>
                    </div>
                    <div class="modal-name-field-section">
                        <div class="modal-signature-label">ឈ្មោះ៖ <span class="modal-dotted-line">................................................................</span></div>
                    </div>
                    <div class="modal-notes-section">
                        <div class="modal-notes-title">កំណត់សំគាល់៖</div>
                        <ul class="modal-notes-list">
                            <li>- ការមិនគោរពតាមកិច្ចសន្យា គ្រឹះស្ថានមីក្រូហិរញ្ញវត្ថុ ប្រាយម៍ អិមអេហ្វ អិលធីឌី នឹងចាត់វិធានការតាមផ្លូវច្បាប់</li>
                            <li>- អតិថិជនត្រូវមកសងប្រាក់អោយបានទៀងទាត់តាមតារាងកាលវិភាគសងប្រាក់ដែលបានចែកជូនកំណត់ទុក</li>
                            <li>- ការទទួលប្រាក់ ៖ ត្រូវមកបង់ប្រាក់នៅការិយាល័យផ្ទាល់ ពីម៉ោង ៨: ០០ ព្រឹក ដល់ ៣: ៣០ រសៀល
                            </br>និងអាចបង់ប្រាក់តាមរយៈភ្នាក់ងារទ្រូម៉ាន់នី (​​True money) ដែលនៅជិតលោកអ្នក លេខកូដស្ថាប័ន (2031)
                            </br>និងតាមរយៈវីង (wing) លេខកូដស្ថាប័នសម្រាប់តារាងបង់ប្រាក់ជាដុល្លារ USD (6162)
                            និងតារាងបង់ប្រាក់ជាខ្មែរ KHR (6163)</li>
                            <li>- រាល់ការសងប្រាក់ចំថ្ងៃ ឈប់សំរាក ថ្ងៃសៅរ៍អាទិត្យ រឺ ថ្ងៃបុណ្យត្រូវមកសងមួយថ្ងៃ មុនកាលកំណត់ត្រូវសង</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <div class="footer-actions">
                <button class="btn-print" onclick="printDocument()">
                    <img src="<?php echo baseUrl('/assets/images/PrintButton.svg'); ?>" alt="Print" class="btn-print-icon">
                </button>
            </div>
        </div>
    </div>
</div>

