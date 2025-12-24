// Toggle password visibility
function togglePassword() {
    const $passwordInput = $('#password');
    const $toggleIcon = $('.toggle-password');
    
    if ($passwordInput.attr('type') === 'password') {
        $passwordInput.attr('type', 'text');
        $toggleIcon.text('👁️');
    } else {
        $passwordInput.attr('type', 'password');
        $toggleIcon.text('👁');
    }
}

// Pagination state for customers table (read from server-rendered data)
let currentPage = 1;
let rowsPerPage = 10;
let totalCount = 0;
let totalPages = 0;

// Get base URL for navigation
function getBaseUrl() {
    return $('body').data('base-url') || '';
}

// Navigate to page with pagination parameters
function navigateToPage(page, limit) {
    const baseUrl = getBaseUrl();
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    url.searchParams.set('limit', limit);
    window.location.href = url.toString();
}

// Initialize table using existing server-rendered rows
function initCustomersTable() {
    const $pagination = $('.pagination-new');
    
    if ($pagination.length) {
        // Get initial state from server-rendered data attributes
        currentPage = parseInt($pagination.data('current-page')) || 1;
        rowsPerPage = parseInt($pagination.data('limit')) || 10;
        totalCount = parseInt($pagination.data('total-count')) || 0;
        totalPages = parseInt($pagination.data('total-pages')) || 0;
    }
}

// Filter table based on search (client-side filtering for current page only)
// Search only in: ការិយាល័យ (BRNAME - column 0) and អតិថិជនឈ្មោះ (Customer Name - column 8)
function filterTable() {
    const searchTerm = $('#searchInput').val().toLowerCase() || '';
    const rows = getCustomerRows();
    const $noResultsRow = $('#noResultsRow');

    if (!searchTerm) {
        // No search term - show all rows
        $(rows).show();
        $noResultsRow.hide();
        return;
    }

    const filtered = rows.filter(function(row) {
        const $row = $(row);
        const $cells = $row.find('td');
        if ($cells.length < 9) return false;
        
        // Column 0: ការិយាល័យ (BRNAME)
        const brnameText = $cells.eq(0).text().toLowerCase();
        
        // Column 8: អតិថិជនឈ្មោះ (Customer Name - CNAMEKH/CNAMEEN)
        const customerNameText = $cells.eq(8).text().toLowerCase();
        
        // Search in both columns
        return brnameText.includes(searchTerm) || customerNameText.includes(searchTerm);
    });

    if (filtered.length === 0) {
        $(rows).hide();
        $noResultsRow.show();
    } else {
        $(rows).hide();
        $(filtered).show();
        $noResultsRow.hide();
    }
}

// Update pagination button states (enable/disable)
function updatePaginationButtons(page, totalPages) {
    const $prevBtn = $('#prevBtn');
    const $nextBtn = $('#nextBtn');
    
    $prevBtn.prop('disabled', page <= 1);
    $nextBtn.prop('disabled', page >= totalPages);
}

// Toggle user menu
function toggleUserMenu() {
    $('.user-profile-new').toggleClass('active');
}

// Toggle page rows dropdown
function togglePageRowsDropdown(event) {
    if (event) {
        event.stopPropagation();
    }
    $('.pagination-select-wrapper').toggleClass('active');
}

// Select page rows - reload page with new limit
function selectPageRows(value, event) {
    if (event) {
        event.stopPropagation();
    }
    
    // Validate value is in allowed list
    const allowedLimits = [10, 20, 30, 40, 50];
    if (!allowedLimits.includes(value)) {
        console.error('Invalid limit value:', value);
        return;
    }
    
    // Navigate to page 1 with new limit
    navigateToPage(1, value);
}

// Close user menu when clicking outside
$(document).on('click', function(event) {
    const $userProfile = $('.user-profile-new');
    const $userMenu = $('#userMenu');
    const $pageRowsWrapper = $('.pagination-select-wrapper');
    
    // Close user menu
    if ($userProfile.length && $userMenu.length && !$userProfile.is(event.target) && !$userProfile.has(event.target).length) {
        $userProfile.removeClass('active');
    }
    
    // Close page rows dropdown when clicking outside
    if ($pageRowsWrapper.length && !$pageRowsWrapper.is(event.target) && !$pageRowsWrapper.has(event.target).length) {
        $pageRowsWrapper.removeClass('active');
    }
});

// Handle login form submission and table init
$(document).ready(function() {
    // Form will submit normally to server for proper authentication
    
    // Auto-hide flash messages after 5 seconds
    const $flashMessage = $('.flash-message');
    if ($flashMessage.length) {
        setTimeout(function() {
            $flashMessage.css('opacity', '0');
            setTimeout(function() {
                $flashMessage.remove();
            }, 300);
        }, 5000);
    }

    // Apply pagination/filter on server-rendered rows
    initCustomersTable();

    // Wire up previous/next buttons - reload page with new page number
    $('#prevBtn').on('click', function() {
        const $btn = $(this);
        if (!$btn.prop('disabled') && currentPage > 1) {
            navigateToPage(currentPage - 1, rowsPerPage);
        }
    });

    $('#nextBtn').on('click', function() {
        const $btn = $(this);
        if (!$btn.prop('disabled') && currentPage < totalPages) {
            navigateToPage(currentPage + 1, rowsPerPage);
        }
    });
    
    // Wire up search input - client-side filtering only (for current page)
    $('#searchInput').on('keyup', function() {
        filterTable();
    });
    
    // Wire up user profile menu
    $('.user-profile-new').on('click', function(e) {
        e.stopPropagation();
        toggleUserMenu();
    });
    
    // Wire up pagination dropdown
    $('.pagination-select-btn').on('click', function(e) {
        e.stopPropagation();
        togglePageRowsDropdown(e);
    });
    
    // Wire up pagination dropdown items
    $(document).on('click', '.pagination-dropdown-item', function(e) {
        e.stopPropagation();
        const value = parseInt($(this).data('value') || $(this).text().trim());
        selectPageRows(value, e);
    });
});
