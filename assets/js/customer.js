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

// Pagination state for customers table
let currentPage = 1;
let rowsPerPage = 10;

function getCustomerRows() {
    const $table = $('#customersTable');
    if (!$table.length) return [];
    const $tbody = $table.find('tbody');
    if (!$tbody.length) return [];
    return $tbody.find('tr').filter(function() {
        return $(this).attr('id') !== 'noResultsRow' && !$(this).hasClass('blank-row');
    }).toArray();
}

function updatePaginationDisplay(showing, total) {
    $('.pagination-count').text(`${showing} of ${total}`);
}

// Initialize table using existing server-rendered rows
function initCustomersTable() {
    currentPage = 1;
    filterTable(); // This will call updatePaginationButtons internally
}

// Filter table based on search and apply pagination
// Search only in: ការិយាល័យ (BRNAME - column 0) and អតិថិជនឈ្មោះ (Customer Name - column 8)
function filterTable() {
    const searchTerm = $('#searchInput').val().toLowerCase() || '';
    const rows = getCustomerRows();
    const $noResultsRow = $('#noResultsRow');
    const $tbody = $('#customersTable tbody');

    const filtered = rows.filter(function(row) {
        if (!searchTerm) return true;
        
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

    const total = filtered.length;

    if (total === 0) {
        $(rows).hide();
        // Remove existing blank rows
        $tbody.find('tr.blank-row').remove();
        $noResultsRow.show();
        currentPage = 1;
        updatePaginationDisplay(0, 0);
        updatePaginationButtons(1, 1); // Disable both buttons when no results
        return;
    }

    $noResultsRow.hide();

    const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    // Hide all rows first
    $(rows).hide();

    // Show filtered rows for current page
    const visibleRows = [];
    filtered.forEach(function(row, idx) {
        if (idx >= start && idx < end) {
            $(row).show();
            visibleRows.push(row);
        }
    });

    // Remove existing blank rows
    $tbody.find('tr.blank-row').remove();

    // Add blank rows to fill up to rowsPerPage
    const visibleCount = visibleRows.length;
    if (visibleCount < rowsPerPage && $tbody.length) {
        const blankRowsNeeded = rowsPerPage - visibleCount;
        for (let i = 0; i < blankRowsNeeded; i++) {
            const $blankRow = $('<tr>').addClass('blank-row').html('<td colspan="17"></td>');
            $tbody.append($blankRow);
        }
    }

    const showing = Math.min(rowsPerPage, total - start);
    updatePaginationDisplay(showing, total);
    
    // Update button states
    updatePaginationButtons(currentPage, totalPages);
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

// Select page rows
function selectPageRows(value, event) {
    if (event) {
        event.stopPropagation();
    }
    const $selectValue = $('.pagination-select-value');
    const $dropdown = $('#pageRowsDropdown');
    
    if (!$dropdown.length) {
        console.error('Dropdown not found');
        return;
    }
    
    const $items = $dropdown.find('.pagination-dropdown-item');
    
    $selectValue.text(value);
    
    // Update active state
    $items.removeClass('active').filter(function() {
        return $(this).text().trim() === value.toString();
    }).addClass('active');
    
    // Close dropdown
    $('.pagination-select-wrapper').removeClass('active');
    
    // Update pagination
    rowsPerPage = value;
    currentPage = 1;
    filterTable();
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

    // Wire up previous/next buttons
    $('#prevBtn').on('click', function() {
        const $btn = $(this);
        if (!$btn.prop('disabled') && currentPage > 1) {
                currentPage--;
                filterTable();
            }
        });

    $('#nextBtn').on('click', function() {
        const $btn = $(this);
            const rows = getCustomerRows();
        const searchTerm = $('#searchInput').val().toLowerCase() || '';
        const filtered = rows.filter(function(row) {
            const text = $(row).text().toLowerCase();
                return !searchTerm || text.includes(searchTerm);
            });
            const totalPages = Math.max(1, Math.ceil(filtered.length / rowsPerPage));
            
        if (!$btn.prop('disabled') && currentPage < totalPages) {
                currentPage++;
                filterTable();
            }
        });
    
    // Wire up search input
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
