<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Require login
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo baseUrl('/assets/css/style.css'); ?>">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="logo">
                <img src="<?php echo baseUrl('/assets/images/prime_logo.svg'); ?>" alt="PRIME Microfinance" class="prime-logo-img">
            </div>
            <nav>
                <a href="<?php echo baseUrl('/dashboard'); ?>" class="nav-item active">
                    <span class="nav-icon">🏠</span>
                    <span>Dashboard</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <img src="<?php echo baseUrl('/assets/images/webill365_logo_full.svg'); ?>" alt="WeBill365" class="webill-logo-img">
            </div>
        </div>
        
        <div class="main-content">
            <header class="top-header">
                <div class="search-container">
                    <div class="search-input-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchInput" placeholder="Name, Loan ID..." class="search-input" onkeyup="filterTable()">
                    </div>
                    <button class="filters-btn" onclick="toggleFilters()">
                        <span class="filter-icon" id="filterIcon">🔽</span>
                        <span>Filters</span>
                    </button>
                </div>
                <div class="user-profile" onclick="toggleUserMenu()">
                    <div class="profile-icon">👤</div>
                    <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                    <span class="dropdown-arrow">▼</span>
                    <div class="user-menu" id="userMenu">
                        <a href="<?php echo baseUrl('/logout'); ?>" class="user-menu-item">
                            <span>🚪</span>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Filters Panel -->
            <div class="filters-panel" id="filtersPanel">
                <div class="filters-content">
                    <div class="filter-group">
                        <label>Status</label>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox" value="overdue" class="status-filter" onchange="filterTable()">
                                <span class="status overdue">Overdue</span>
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="pending" class="status-filter" onchange="filterTable()">
                                <span class="status pending">Pending</span>
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="progress" class="status-filter" onchange="filterTable()">
                                <span class="status progress">Progress</span>
                            </label>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label>Sort By</label>
                        <select id="sortBy" class="filter-select" onchange="filterTable()">
                            <option value="name">Name</option>
                            <option value="loanid">Loan ID</option>
                            <option value="email">Email</option>
                            <option value="account">Account Number</option>
                            <option value="status">Status</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button class="filter-clear-btn" onclick="clearFilters()">Clear All</button>
                        <button class="filter-apply-btn" onclick="toggleFilters()">Apply</button>
                    </div>
                </div>
            </div>
            
            <div class="content-area">
                <div class="customers-table-container">
                    <h2>Customers</h2>
                    <table class="customers-table" id="customersTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>Name</th>
                                <th>Loan ID</th>
                                <th>Email address</th>
                                <th>Account Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr onclick="showLoanRepayment('123456789000')">
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">OR</div>
                                        <div>
                                            <div class="customer-name">Olivia Rhye</div>
                                            <div class="customer-handle">@olivia</div>
                                        </div>
                                    </div>
                                </td>
                                <td>123456789000</td>
                                <td>olivia@untitledui.com</td>
                                <td>1253826438200</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="status overdue">Overdue</span>
                                        <span class="delete-icon">🗑️</span>
                                    </div>
                                </td>
                            </tr>
                            <tr onclick="showLoanRepayment('987654321000')">
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">JD</div>
                                        <div>
                                            <div class="customer-name">John Doe</div>
                                            <div class="customer-handle">@johndoe</div>
                                        </div>
                                    </div>
                                </td>
                                <td>987654321000</td>
                                <td>john@example.com</td>
                                <td>9876543210000</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="status pending">Pending</span>
                                        <span class="delete-icon">🗑️</span>
                                    </div>
                                </td>
                            </tr>
                            <tr onclick="showLoanRepayment('456789123000')">
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">JS</div>
                                        <div>
                                            <div class="customer-name">Jane Smith</div>
                                            <div class="customer-handle">@janesmith</div>
                                        </div>
                                    </div>
                                </td>
                                <td>456789123000</td>
                                <td>jane@example.com</td>
                                <td>4567891230000</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="status progress">Progress</span>
                                        <span class="delete-icon">🗑️</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">AR</div>
                                        <div>
                                            <div class="customer-name">Alex Rivera</div>
                                            <div class="customer-handle">@alex</div>
                                        </div>
                                    </div>
                                </td>
                                <td>789123456000</td>
                                <td>alex@untitledui.com</td>
                                <td>7891234560000</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="status overdue">Overdue</span>
                                        <span class="delete-icon">🗑️</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">SM</div>
                                        <div>
                                            <div class="customer-name">Sarah Miller</div>
                                            <div class="customer-handle">@sarah</div>
                                        </div>
                                    </div>
                                </td>
                                <td>321654987000</td>
                                <td>sarah@untitledui.com</td>
                                <td>3216549870000</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="status pending">Pending</span>
                                        <span class="delete-icon">🗑️</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">TW</div>
                                        <div>
                                            <div class="customer-name">Tom Wilson</div>
                                            <div class="customer-handle">@tom</div>
                                        </div>
                                    </div>
                                </td>
                                <td>654987321000</td>
                                <td>tom@untitledui.com</td>
                                <td>6549873210000</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="status overdue">Overdue</span>
                                        <span class="delete-icon">🗑️</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">EB</div>
                                        <div>
                                            <div class="customer-name">Emma Brown</div>
                                            <div class="customer-handle">@emma</div>
                                        </div>
                                    </div>
                                </td>
                                <td>147258369000</td>
                                <td>emma@untitledui.com</td>
                                <td>1472583690000</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="status progress">Progress</span>
                                        <span class="delete-icon">🗑️</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">MJ</div>
                                        <div>
                                            <div class="customer-name">Mike Johnson</div>
                                            <div class="customer-handle">@mike</div>
                                        </div>
                                    </div>
                                </td>
                                <td>258369147000</td>
                                <td>mike@untitledui.com</td>
                                <td>2583691470000</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="status progress">Progress</span>
                                        <span class="delete-icon">🗑️</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">LD</div>
                                        <div>
                                            <div class="customer-name">Lisa Davis</div>
                                            <div class="customer-handle">@lisa</div>
                                        </div>
                                    </div>
                                </td>
                                <td>369147258000</td>
                                <td>lisa@untitledui.com</td>
                                <td>3691472580000</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="status progress">Progress</span>
                                        <span class="delete-icon">🗑️</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="pagination">
                        <button class="page-btn">← Previous</button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <span class="page-ellipsis">...</span>
                        <button class="page-btn">8</button>
                        <button class="page-btn">9</button>
                        <button class="page-btn">10</button>
                        <button class="page-btn">Next →</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loan Repayment Modal -->
    <div id="loanModal" class="modal">
        <div class="modal-content printable-content">
            <div class="modal-header">
                <div class="modal-logo">
                    <img src="<?php echo baseUrl('/assets/images/prime_logo.svg'); ?>" alt="PRIME Microfinance" class="modal-logo-img">
                </div>
                <button class="close-btn" onclick="closeLoanModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="loan-schedule-header">
                    <div class="institution-header">
                        <div class="institution-logo">
                            <img src="<?php echo baseUrl('/assets/images/prime_logo.svg'); ?>" alt="PRIME Microfinance" class="institution-logo-img">
                            <div class="institution-name">PRIME MF MICROFINANCE INSTITUTION LTD</div>
                        </div>
                        <div class="qr-code-header">
                            <div class="qr-code-box">
                                <div class="qr-placeholder-small">QR CODE</div>
                            </div>
                            <div class="qr-label-small">KHGC</div>
                        </div>
                    </div>
                    <h2 class="schedule-title">តារាងកាលវិភាគសងប្រាក់</h2>
                </div>
                
                <div class="loan-details-section">
                    <div class="loan-details-left">
                        <div class="detail-row">
                            <span class="detail-label">ករិយាល័យ:</span>
                            <span class="detail-value">0002-010 500 224 PCT</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">គណនីឥណទាន:</span>
                            <span class="detail-value">00020255084000020</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">គណនីអតិថិជន:</span>
                            <span class="detail-value">00022004200</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">កាលបរិច្ឆេទផ្តល់កម្ចី:</span>
                            <span class="detail-value">20/09/24</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">លេខគណនីសន្សំ:</span>
                            <span class="detail-value">0002001000043083</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">អត្រាការប្រាក់:</span>
                            <span class="detail-value">1.30% / ខែ</span>
                        </div>
                    </div>
                    <div class="loan-details-right">
                        <div class="detail-row">
                            <span class="detail-label">កម្ចីភ្លេទី:</span>
                            <span class="detail-value">14 / មន្ត្រីខណទាន / Trann Sotry (010 700 803)</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">អតិថិជនឈ្មោះ:</span>
                            <span class="detail-value">លោក ឌុក ផល្លី / Duk Phally</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">អ្នករួមន្ត្រីឈ្មោះ:</span>
                            <span class="detail-value">លោក ធូច លីណា / Touch Lena</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">កាលបរិច្ឆេទបញ្ចប់នៃកម្ចី:</span>
                            <span class="detail-value">20/09/24</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">រយៈពេល:</span>
                            <span class="detail-value">84 ខែ</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">ចំនួនទឹកប្រាក់:</span>
                            <span class="detail-value">12,000.00</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">លេខទូរស័ព្ទ:</span>
                            <span class="detail-value">(855) 010 500 224</span>
                        </div>
                    </div>
                </div>

                <div class="repayment-schedule-table">
                    <table class="schedule-table">
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
                            <tr>
                                <td>0</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>12,000.00</td>
                                <td>-</td>
                            </tr>
                            <?php for($i = 1; $i <= 12; $i++): ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td>04-10-2024</td>
                                <td>14</td>
                                <td>134.80</td>
                                <td>72.80</td>
                                <td class="total-amount">230.00</td>
                                <td><?php echo number_format(12000 - ($i * 134.80), 2); ?></td>
                                <td>-</td>
                            </tr>
                            <?php endfor; ?>
                            <tr class="summary-row">
                                <td colspan="2">សរុប</td>
                                <td>2540</td>
                                <td>12,000.00</td>
                                <td>11,348.51</td>
                                <td class="total-amount">26,578.82</td>
                                <td>11,562.38</td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="loan-schedule-footer">
                    <div class="footer-details">
                        <div class="footer-row">
                            <span class="footer-label">ថ្ងៃធ្វើកិច្ចសន្យា:</span>
                            <span class="footer-value">02/12/2025</span>
                        </div>
                        <div class="footer-row">
                            <span class="footer-label">រៀបចំដោយ:</span>
                            <span class="footer-value">Duk Phally</span>
                        </div>
                    </div>
                    <div class="terms-section">
                        <p>ការមិនគោរតាមកិច្ចសន្យា គ្រឹះស្ថានមីក្រូហិរញ្ញវត្ថុ ប្រាយមិនការភាពផ្លូវច្បាប់</p>
                        <p>អតិថិជនត្រូវសងប្រាក់ឱ្យបានទៀងទាត់តាមកាលវិភាគ</p>
                        <p>ការទូទាត់ដោយផ្ទាល់: ពីម៉ោង 8:00 ទៅ 15:30</p>
                        <p>True money: 2037, Wing: USD 8162</p>
                    </div>
                    <div class="signature-section">
                        <div class="signature-label">ស្នាមមេដៃអ្នកទទួលប្រាក់:</div>
                        <div class="signature-name">លោក ឌុក ផល្លី / Duk Phally</div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="print-btn" onclick="printLoanSchedule()">Print</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo baseUrl('/assets/js/main.js'); ?>"></script>
</body>
</html>

