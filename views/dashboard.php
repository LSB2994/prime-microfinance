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
    <link rel="icon" type="image/png" href="<?php echo baseUrl('/assets/images/logo.png'); ?>">
    <link rel="alternate icon" href="<?php echo baseUrl('/assets/images/logo.png'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600&family=Inter:wght@400;500&family=Nunito:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo baseUrl('/assets/css/style.css'); ?>">
</head>
<body>
    <div class="dashboard-container-new">
        <div class="sidebar-new">
            <div class="logo-section">
                <img src="<?php echo baseUrl('/assets/images/logo.png'); ?>" alt="PRIME Microfinance" class="logo-img-new">
            </div>
            <nav class="sidebar-nav">
                <a href="<?php echo baseUrl('/dashboard'); ?>" class="nav-item-new active">
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
                                <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M19 19L14.65 14.65" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <input type="text" id="searchInput" placeholder="Name, Loan ID..." class="search-input-new" onkeyup="filterTable()">
                    </div>
                    <button class="filters-btn-new" onclick="toggleFilters()">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6H16M6 10H14M8 14H12" stroke="black" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span>Filters</span>
                    </button>
                </div>
                <div class="header-right">
                    <div class="notification-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="user-profile-new" onclick="toggleUserMenu()">
                        <div class="profile-avatar-new">
                            <img src="https://www.figma.com/api/mcp/asset/594914a2-c283-4d3e-af82-0c46d446c573" alt="Profile" class="avatar-img">
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
                                <tr onclick="showLoanRepayment('00020255084000020')">
                                    <td>010 0002500 224 PCT</td>
                                    <td>00020255084000020</td>
                                    <td>00022004200</td>
                                    <td>20/09/24</td>
                                    <td>00020211002043083</td>
                                    <td>1.30 %/ 1ខែ</td>
                                    <td>4</td>
                                    <td>Trann Sotry(010 700 933)</td>
                                    <td>ឌុក ផល្លី / Duk Phally</td>
                                    <td>ឡេណា តូច / Touch Lena</td>
                                    <td>04/09/31</td>
                                    <td>84 ខែ</td>
                                    <td></td>
                                    <td>0.40%</td>
                                    <td>12,000.00 ដុល្លារ</td>
                                    <td>(855) 010 500 224</td>
                                    <td>
                                        <div class="address-cell">
                                            <p>ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់</p>
                                            <p>ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr onclick="showLoanRepayment('00020255084000021')">
                                    <td>010 0002500 224 PCT</td>
                                    <td>00020255084000020</td>
                                    <td>00022004200</td>
                                    <td>20/09/24</td>
                                    <td>00020211002043083</td>
                                    <td>1.30 %/ 1ខែ</td>
                                    <td>4</td>
                                    <td>Trann Sotry(010 700 933)</td>
                                    <td>ឌុក ផល្លី / Duk Phally</td>
                                    <td>ឡេណា តូច / Touch Lena</td>
                                    <td>04/09/31</td>
                                    <td>84 ខែ</td>
                                    <td></td>
                                    <td>0.40%</td>
                                    <td>12,000.00 ដុល្លារ</td>
                                    <td>(855) 010 500 224</td>
                                    <td>
                                        <div class="address-cell">
                                            <p>ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់</p>
                                            <p>ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr onclick="showLoanRepayment('00020255084000022')">
                                    <td>010 0002500 224 PCT</td>
                                    <td>00020255084000020</td>
                                    <td>00022004200</td>
                                    <td>20/09/24</td>
                                    <td>00020211002043083</td>
                                    <td>1.30 %/ 1ខែ</td>
                                    <td>4</td>
                                    <td>Trann Sotry(010 700 933)</td>
                                    <td>ឌុក ផល្លី / Duk Phally</td>
                                    <td>ឡេណា តូច / Touch Lena</td>
                                    <td>04/09/31</td>
                                    <td>84 ខែ</td>
                                    <td></td>
                                    <td>0.40%</td>
                                    <td>12,000.00 ដុល្លារ</td>
                                    <td>(855) 010 500 224</td>
                                    <td>
                                        <div class="address-cell">
                                            <p>ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់</p>
                                            <p>ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="highlighted-row" onclick="showLoanRepayment('00020255084000023')">
                                    <td>010 0002500 224 PCT</td>
                                    <td>00020255084000020</td>
                                    <td>00022004200</td>
                                    <td>20/09/24</td>
                                    <td>00020211002043083</td>
                                    <td>1.30 %/ 1ខែ</td>
                                    <td>4</td>
                                    <td>Trann Sotry(010 700 933)</td>
                                    <td>ឌុក ផល្លី / Duk Phally</td>
                                    <td>ឡេណា តូច / Touch Lena</td>
                                    <td>04/09/31</td>
                                    <td>84 ខែ</td>
                                    <td></td>
                                    <td>0.40%</td>
                                    <td>12,000.00 ដុល្លារ</td>
                                    <td>(855) 010 500 224</td>
                                    <td>
                                        <div class="address-cell">
                                            <p>ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់</p>
                                            <p>ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr onclick="showLoanRepayment('00020255084000024')">
                                    <td>010 0002500 224 PCT</td>
                                    <td>00020255084000020</td>
                                    <td>00022004200</td>
                                    <td>20/09/24</td>
                                    <td>00020211002043083</td>
                                    <td>1.30 %/ 1ខែ</td>
                                    <td>4</td>
                                    <td>Trann Sotry(010 700 933)</td>
                                    <td>ឌុក ផល្លី / Duk Phally</td>
                                    <td>ឡេណា តូច / Touch Lena</td>
                                    <td>04/09/31</td>
                                    <td>84 ខែ</td>
                                    <td></td>
                                    <td>0.40%</td>
                                    <td>12,000.00 ដុល្លារ</td>
                                    <td>(855) 010 500 224</td>
                                    <td>
                                        <div class="address-cell">
                                            <p>ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់</p>
                                            <p>ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr onclick="showLoanRepayment('00020255084000025')">
                                    <td>010 0002500 224 PCT</td>
                                    <td>00020255084000020</td>
                                    <td>00022004200</td>
                                    <td>20/09/24</td>
                                    <td>00020211002043083</td>
                                    <td>1.30 %/ 1ខែ</td>
                                    <td>4</td>
                                    <td>Trann Sotry(010 700 933)</td>
                                    <td>ឌុក ផល្លី / Duk Phally</td>
                                    <td>ឡេណា តូច / Touch Lena</td>
                                    <td>04/09/31</td>
                                    <td>84 ខែ</td>
                                    <td></td>
                                    <td>0.40%</td>
                                    <td>12,000.00 ដុល្លារ</td>
                                    <td>(855) 010 500 224</td>
                                    <td>
                                        <div class="address-cell">
                                            <p>ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់</p>
                                            <p>ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr onclick="showLoanRepayment('00020255084000026')">
                                    <td>010 0002500 224 PCT</td>
                                    <td>00020255084000020</td>
                                    <td>00022004200</td>
                                    <td>20/09/24</td>
                                    <td>00020211002043083</td>
                                    <td>1.30 %/ 1ខែ</td>
                                    <td>4</td>
                                    <td>Trann Sotry(010 700 933)</td>
                                    <td>ឌុក ផល្លី / Duk Phally</td>
                                    <td>ឡេណា តូច / Touch Lena</td>
                                    <td>04/09/31</td>
                                    <td>84 ខែ</td>
                                    <td></td>
                                    <td>0.40%</td>
                                    <td>12,000.00 ដុល្លារ</td>
                                    <td>(855) 010 500 224</td>
                                    <td>
                                        <div class="address-cell">
                                            <p>ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់</p>
                                            <p>ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="pagination-new">
                    <button class="pagination-btn">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.5 15L7.5 10L12.5 5" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Previous</span>
                    </button>
                    <div class="pagination-info">
                        <span class="pagination-label">Page rows</span>
                        <div class="pagination-select-wrapper">
                            <button type="button" class="pagination-select-btn" onclick="togglePageRowsDropdown(event); event.stopPropagation();">
                                <span class="pagination-select-value">20</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="pagination-chevron">
                                    <path d="M4 6L8 10L12 6" stroke="#1E1E1E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="pagination-dropdown" id="pageRowsDropdown">
                                <div class="pagination-dropdown-item" onclick="selectPageRows(10, event); event.stopPropagation();">10</div>
                                <div class="pagination-dropdown-item active" onclick="selectPageRows(20, event); event.stopPropagation();">20</div>
                                <div class="pagination-dropdown-item" onclick="selectPageRows(30, event); event.stopPropagation();">30</div>
                                <div class="pagination-dropdown-item" onclick="selectPageRows(40, event); event.stopPropagation();">40</div>
                                <div class="pagination-dropdown-item" onclick="selectPageRows(50, event); event.stopPropagation();">50</div>
                            </div>
                        </div>
                        <span class="pagination-count">10 of 100</span>
                    </div>
                    <button class="pagination-btn">
                        <span>Next</span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.5 15L12.5 10L7.5 5" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
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
                            <span class="detail-value-new">0002-010 500 224 PCT</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">គណនីឥណទាន</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">00020255084000020:</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">គណនីអតិថិជន</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">00022004200</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">កាលបរិច្ឆេទផ្តល់កម្ចី</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">20/09/24</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">លេខគណនីសន្សំ</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">00020211002043083</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">អត្រាការប្រាក់</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">1.30 %/ 1ខែ</span>
                        </div>
                    </div>
                    <div class="loan-details-right-new">
                        <div class="detail-row-new">
                            <span class="detail-label-new">កម្ចីវគ្គទី</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">4 / មន្ត្រីឥណទាន / Trann Sotry(010 700 933)</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">អតិថិជនឈ្មោះ</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">ឌុក ផល្លី / Duk Phally</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">អ្នករួមខ្ចីឈ្មោះ</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">តូច ឡេណា / Touch Lena</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">កាលបរិចេ្ឆទបញ្ចប់នៃកម្ចី</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">20/09/24</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">រយៈពេលខ្ចី</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">84 ខែ</span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">របៀបសងប្រាក់</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new"></span>
                        </div>
                        <div class="detail-row-new">
                            <span class="detail-label-new">សេវាប្រចាំខែ</span>
                            <span class="detail-separator">៖</span>
                            <span class="detail-value-new">84 ខែ</span>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Details -->
                <div class="loan-additional-details">
                    <div class="detail-row-new">
                        <span class="detail-label-new">ចំនួនទឹកប្រាក់</span>
                        <span class="detail-separator">៖</span>
                        <span class="detail-value-new detail-value-bold">12,000.00 ដុល្លារ</span>
                    </div>
                    <div class="detail-row-new">
                        <span class="detail-label-new">លេខទូរស័ព្ទ</span>
                        <span class="detail-separator">៖</span>
                        <span class="detail-value-new">(855) 010 500 224</span>
                    </div>
                    <div class="detail-row-new address-row">
                        <span class="detail-label-new">អាស័យដ្ឋានអ្នកខ្ចី:</span>
                        <span class="detail-separator">៖</span>
                        <span class="detail-value-new">ភូមិ ត្រពាំងអញ្ចាញ, ឃុំ/សង្កាត់ ត្រពាំងក្រសាំង, ស្រុក/ខ័ណ្ឌ ពោធិ៍សែនជ័យ, ខេត្ត/ក្រុង ភ្នំពេញ</span>
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
    
    <script src="<?php echo baseUrl('/assets/js/main.js'); ?>"></script>
</body>
</html>
