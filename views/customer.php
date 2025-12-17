<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/interceptor.php';

// Require login handled by router interceptor for /customer
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
<body>
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
                        <input type="text" id="searchInput" placeholder="Name, Loan ID..." class="search-input-new" onkeyup="filterTable()">
                    </div>
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
    
    <?php /* Loan repayment / customer detail modal shared partial for customer page */ ?>
    <?php include __DIR__ . '/customer-modal-partial.php'; ?>
    
    <script src="<?php echo baseUrl('/assets/js/customer.js'); ?>"></script>
</body>
</html>


