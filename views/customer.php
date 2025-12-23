<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/interceptor.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Require login handled by router interceptor for /customer

$customerRows = [];
$customerError = null;

try {
    // Single-file implementation: fetch customers directly from Oracle
    $sql = "SELECT * FROM bi.ctm_infor_v1";
    $customerRows = oracleFetchAll($sql);
} catch (Exception $e) {
    $customerError = $e->getMessage();
    // Log error instead of just displaying
    logError('Failed to fetch customers', [
        'error' => $e->getMessage(),
        'file' => __FILE__,
        'line' => $e->getLine()
    ]);
}

// Helper functions moved to includes/helpers.php
// All formatting functions moved to includes/helpers.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo baseUrl('/assets/images/logo.png'); ?>">
    <link rel="alternate icon" href="<?php echo baseUrl('/assets/images/logo.png'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Kantumruy+Pro:wght@400;500;600&family=Noto+Serif+Khmer:wght@400;500;600&family=Noto+Sans+KR:wght@400;500;600&family=Nunito:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo baseUrl('/assets/css/style.css'); ?>">
</head>
<body data-base-url="<?php echo htmlspecialchars(baseUrl(''), ENT_QUOTES); ?>">
    <div class="dashboard-container-new">
        <div class="sidebar-new">
            <div class="logo-section">
                <img src="<?php echo baseUrl('/assets/images/logo.png'); ?>" alt="PRIME Microfinance" class="logo-img-new">
            </div>
            <nav class="sidebar-nav">
                <a href="<?php echo baseUrl('/customer'); ?>" class="nav-item-new active">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 10L10 3L17 10M5 16H15C15.5523 16 16 15.5523 16 15V11C16 10.4477 15.5523 10 15 10H5C4.44772 10 4 10.4477 4 11V15C4 15.5523 4.44772 16 5 16Z" fill="black"/>
                    </svg>
                    <span>Customers</span>
                </a>
            </nav>
            <div class="sidebar-footer-new">
                <img src="<?php echo baseUrl('/assets/images/webill365_logo_full.svg'); ?>" alt="WeBill365" class="webill-logo-new">
            </div>
        </div>
        
        <div class="main-content-new">
            <header class="top-header-new">
                <div class="header-actions">
                    <div class="search-wrapper-new">
                        <div class="search-icon-new">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.5913 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M19 19L14.65 14.65" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <input type="text" id="searchInput" placeholder="ការិយាល័យ, អតិថិជនឈ្មោះ" class="search-input-new" onkeyup="filterTable()">
                    </div>
                </div>
                <div class="header-right">
                    <div class="user-profile-new" onclick="toggleUserMenu()">
                        <div class="profile-avatar-new">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" class="avatar-icon">
                                <circle cx="15" cy="15" r="15" fill="#E5E7EB"/>
                                <path d="M15 15C17.7614 15 20 12.7614 20 10C20 7.23858 17.7614 5 15 5C12.2386 5 10 7.23858 10 10C10 12.7614 12.2386 15 15 15Z" fill="#6B7280"/>
                                <path d="M15 17.5C10.5817 17.5 7 19.2386 7 21.5V25H23V21.5C23 19.2386 19.4183 17.5 15 17.5Z" fill="#6B7280"/>
                            </svg>
                        </div>
                        <span class="user-name-new"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                        <svg width="8" height="4" viewBox="0 0 8 4" fill="none" xmlns="http://www.w3.org/2000/svg" class="chevron-down">
                            <path d="M1 1L4 3L7 1" stroke="#1E1E1E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="user-menu-new" id="userMenu">
                            <a href="<?php echo baseUrl('/logout'); ?>" class="user-menu-item-new">Logout</a>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="content-area-new">
                <div class="customers-card">
                    <div class="customers-header">
                        <h2 class="customers-title">Customers</h2>
                    </div>
                    <div class="table-wrapper">
                        <table class="customers-table-new" id="customersTable">
                            <thead>
                                <tr>
                                    <th>ការិយាល័យ</th>
                                    <th>គណនីឥណទាន</th>
                                    <th>គណនីអតិថិជន</th>
                                    <th>កាលបរិច្ឆេទផ្តល់កម្ចី</th>
                                    <th>លេខគណនីសន្សំ</th>
                                    <th>អត្រាការប្រាក់</th>
                                    <th>កម្ចីវគ្គទី</th>
                                    <th>មន្ត្រីឥណទាន</th>
                                    <th>អតិថិជនឈ្មោះ</th>
                                    <th>អ្នករួមខ្ចីឈ្មោះ</th>
                                    <th>កាលបរិចេ្ឆទបញ្ចប់នៃកម្ចី</th>
                                    <th>រយៈពេលខ្ចី</th>
                                    <th>របៀបសងប្រាក់</th>
                                    <th>សេវាប្រចាំខែ</th>
                                    <th>ចំនួនទឹកប្រាក់</th>
                                    <th>លេខរស័ព្ទទូរស័ព្ទ</th>
                                    <th>អាស័យដ្ឋានអ្នកខ្ចី</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($customerError): ?>
                                    <tr class="error-row">
                                        <td colspan="17">Failed to load customers. <?php echo htmlspecialchars($customerError); ?></td>
                                    </tr>
                                <?php elseif (!empty($customerRows)): ?>
                                    <?php foreach ($customerRows as $row): ?>
                                        <?php
                                            // $row keys are lower-case from oracleFetchAll()
                                            $customerName = trim(
                                                implode(' / ', array_filter([
                                                    $row['cnamekh'] ?? '',
                                                    $row['cnameen'] ?? '',
                                                ]))
                                            );
                                            $coBorrowerName = trim(
                                                implode(' / ', array_filter([
                                                    $row['cobonamekh'] ?? '',
                                                    $row['cobonameen'] ?? '',
                                                ]))
                                            );
                                        ?>
                                        <tr
                                            data-brname="<?php echo htmlspecialchars($row['brname'] ?? '', ENT_QUOTES); ?>"
                                            data-acno="<?php echo htmlspecialchars($row['acno'] ?? '', ENT_QUOTES); ?>"
                                            data-ctmid="<?php echo htmlspecialchars($row['ctmid'] ?? '', ENT_QUOTES); ?>"
                                            data-disbursedt="<?php echo htmlspecialchars($row['disbursedt'] ?? '', ENT_QUOTES); ?>"
                                            data-depositacc="<?php echo htmlspecialchars($row['depositacc'] ?? '', ENT_QUOTES); ?>"
                                            data-ifcvalue="<?php echo htmlspecialchars($row['ifcvalue'] ?? '', ENT_QUOTES); ?>"
                                            data-loancycle="<?php echo htmlspecialchars($row['loancycle'] ?? '', ENT_QUOTES); ?>"
                                            data-customer-name="<?php echo htmlspecialchars($customerName, ENT_QUOTES); ?>"
                                            data-coborrower-name="<?php echo htmlspecialchars($coBorrowerName, ENT_QUOTES); ?>"
                                            data-maturitydt="<?php echo htmlspecialchars($row['maturitydt'] ?? '', ENT_QUOTES); ?>"
                                            data-period="<?php echo htmlspecialchars($row['period'] ?? '', ENT_QUOTES); ?>"
                                            data-type-loan="<?php echo htmlspecialchars($row['type_loan'] ?? '', ENT_QUOTES); ?>"
                                            data-adminfeerate="<?php echo htmlspecialchars($row['adminfeerate'] ?? '', ENT_QUOTES); ?>"
                                            data-dbamt="<?php echo htmlspecialchars($row['dbamt'] ?? '', ENT_QUOTES); ?>"
                                            data-phone="<?php echo htmlspecialchars($row['phone'] ?? '', ENT_QUOTES); ?>"
                                            data-ctmaddress="<?php echo htmlspecialchars((string)($row['ctmaddress'] ?? ''), ENT_QUOTES); ?>"
                                        >
                                            <?php
                                                // Format values and handle truncation with title
                                                $brname = truncateWithTitle($row['brname'] ?? '', 30);
                                                $acno = truncateWithTitle($row['acno'] ?? '', 30);
                                                $ctmid = truncateWithTitle($row['ctmid'] ?? '', 30);
                                                $disbursedt = truncateWithTitle($row['disbursedt'] ?? '', 30);
                                                $depositacc = truncateWithTitle($row['depositacc'] ?? '', 30);
                                                $ifcvalueFormatted = formatInterestRate($row['ifcvalue'] ?? '');
                                                $loancycle = truncateWithTitle($row['loancycle'] ?? '', 30);
                                                $coname = truncateWithTitle($row['coname'] ?? '', 30);
                                                $customerNameFormatted = truncateWithTitle($customerName, 30);
                                                $coBorrowerNameFormatted = truncateWithTitle($coBorrowerName, 30);
                                                $maturitydt = truncateWithTitle($row['maturitydt'] ?? '', 30);
                                                $periodFormatted = formatLoanPeriod($row['period'] ?? '');
                                                $typeLoan = truncateWithTitle($row['type_loan'] ?? '', 30);
                                                $adminfeerateFormatted = formatServiceFee($row['adminfeerate'] ?? '');
                                                $dbamtFormatted = formatAmount($row['dbamt'] ?? '');
                                                $phoneFormatted = formatPhone($row['phone'] ?? '');
                                                $addressVal = $row['ctmaddress'] ?? '';
                                                $addressFormatted = truncateWithTitle((string)$addressVal, 50);
                                            ?>
                                            <?php
                                                // Get full values for title attributes (just the value, no column header)
                                                $acnoFullTitle = $acno['title'] ? $acno['title'] : $acno['display'];
                                                $depositaccFullTitle = $depositacc['title'] ? $depositacc['title'] : $depositacc['display'];
                                                $conameFullTitle = $coname['title'] ? $coname['title'] : $coname['display'];
                                                $customerNameFullTitle = $customerNameFormatted['title'] ? $customerNameFormatted['title'] : $customerNameFormatted['display'];
                                                $coBorrowerNameFullTitle = $coBorrowerNameFormatted['title'] ? $coBorrowerNameFormatted['title'] : $coBorrowerNameFormatted['display'];
                                                // Always use full original value for title (not truncated)
                                                $typeLoanFullTitle = $row['type_loan'] ?? '';
                                                $dbamtFullTitle = $dbamtFormatted; // Use formatted value (same as display)
                                                $addressFullTitle = $addressFormatted['title'] ? $addressFormatted['title'] : $addressFormatted['display'];
                                            ?>
                                            <td<?php echo $brname['title'] ? ' title="' . htmlspecialchars($brname['title'], ENT_QUOTES) . '"' : ''; ?>><?php echo htmlspecialchars($brname['display']); ?></td>
                                            <td title="<?php echo htmlspecialchars($acnoFullTitle, ENT_QUOTES); ?>"><?php echo htmlspecialchars($acno['display']); ?></td>
                                            <td<?php echo $ctmid['title'] ? ' title="' . htmlspecialchars($ctmid['title'], ENT_QUOTES) . '"' : ''; ?>><?php echo htmlspecialchars($ctmid['display']); ?></td>
                                            <td<?php echo $disbursedt['title'] ? ' title="' . htmlspecialchars($disbursedt['title'], ENT_QUOTES) . '"' : ''; ?>><?php echo htmlspecialchars($disbursedt['display']); ?></td>
                                            <td title="<?php echo htmlspecialchars($depositaccFullTitle, ENT_QUOTES); ?>"><?php echo htmlspecialchars($depositacc['display']); ?></td>
                                            <td><?php echo htmlspecialchars($ifcvalueFormatted); ?></td>
                                            <td<?php echo $loancycle['title'] ? ' title="' . htmlspecialchars($loancycle['title'], ENT_QUOTES) . '"' : ''; ?>><?php echo htmlspecialchars($loancycle['display']); ?></td>
                                            <td title="<?php echo htmlspecialchars($conameFullTitle, ENT_QUOTES); ?>"><?php echo htmlspecialchars($coname['display']); ?></td>
                                            <td title="<?php echo htmlspecialchars($customerNameFullTitle, ENT_QUOTES); ?>"><?php echo htmlspecialchars($customerNameFormatted['display']); ?></td>
                                            <td title="<?php echo htmlspecialchars($coBorrowerNameFullTitle, ENT_QUOTES); ?>"><?php echo htmlspecialchars($coBorrowerNameFormatted['display']); ?></td>
                                            <td<?php echo $maturitydt['title'] ? ' title="' . htmlspecialchars($maturitydt['title'], ENT_QUOTES) . '"' : ''; ?>><?php echo htmlspecialchars($maturitydt['display']); ?></td>
                                            <td><?php echo htmlspecialchars($periodFormatted); ?></td>
                                            <td title="<?php echo htmlspecialchars($typeLoanFullTitle, ENT_QUOTES); ?>"><?php echo htmlspecialchars($typeLoan['display']); ?></td>
                                            <td><?php echo htmlspecialchars($adminfeerateFormatted); ?></td>
                                            <td title="<?php echo htmlspecialchars($dbamtFullTitle, ENT_QUOTES); ?>"><?php echo htmlspecialchars($dbamtFormatted); ?></td>
                                            <td><?php echo htmlspecialchars($phoneFormatted); ?></td>
                                            <td title="<?php echo htmlspecialchars($addressFullTitle, ENT_QUOTES); ?>">
                                                <div class="address-cell">
                                                    <p><?php echo htmlspecialchars($addressFormatted['display']); ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="17">No customers found.</td>
                                    </tr>
                                <?php endif; ?>
                                <tr id="noResultsRow" style="display: none; text-align: center;">
                                    <td colspan="17">No matching data found.</td>
                                </tr>
                        </table>
                    </div>
                </div>
                
                <div class="pagination-new">
                    <button class="pagination-btn" id="prevBtn">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.5 15L7.5 10L12.5 5" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Previous</span>
                    </button>
                    <div class="pagination-info">
                        <span class="pagination-label">Page rows</span>
                        <div class="pagination-select-wrapper">
                            <button type="button" class="pagination-select-btn" onclick="togglePageRowsDropdown(event); event.stopPropagation();">
                                <span class="pagination-select-value">10</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="pagination-chevron">
                                    <path d="M4 6L8 10L12 6" stroke="#1E1E1E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="pagination-dropdown" id="pageRowsDropdown">
                                <div class="pagination-dropdown-item active" onclick="selectPageRows(10, event); event.stopPropagation();">10</div>
                                <div class="pagination-dropdown-item" onclick="selectPageRows(20, event); event.stopPropagation();">20</div>
                                <div class="pagination-dropdown-item" onclick="selectPageRows(30, event); event.stopPropagation();">30</div>
                                <div class="pagination-dropdown-item" onclick="selectPageRows(40, event); event.stopPropagation();">40</div>
                                <div class="pagination-dropdown-item" onclick="selectPageRows(50, event); event.stopPropagation();">50</div>
                            </div>
                        </div>
                        <span class="pagination-count">10 of 100</span>
                    </div>
                    <button class="pagination-btn" id="nextBtn">
                        <span>Next</span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.5 15L12.5 10L7.5 5" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo baseUrl('/assets/js/customer.js'); ?>"></script>
</body>
</html>


