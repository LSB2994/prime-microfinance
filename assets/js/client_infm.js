// Toggle user menu
function toggleUserMenu() {
    const userProfile = document.querySelector('.user-profile-new');
    if (userProfile) {
        userProfile.classList.toggle('active');
    }
}

// Load client_infm data from API and populate table
async function loadClientInfm() {
    const table = document.getElementById('clientInfmTable');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    // Show loading state
    tbody.innerHTML = `
        <tr class="loading-row">
            <td colspan="19">Loading client backdate data...</td>
        </tr>
    `;

    try {
        const response = await fetch('/api/client_infm', {
            credentials: 'include',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const json = await response.json();
        console.log('ClientInfm API result:', json);

        if (!json.success || !Array.isArray(json.data)) {
            console.error('Invalid client_infm data from API');
            tbody.innerHTML = `
                <tr class="error-row">
                    <td colspan="19">Failed to load client backdate data (invalid data).</td>
                </tr>
            `;
            const countLabel = document.getElementById('clientInfmCount');
            if (countLabel) countLabel.textContent = 'Records: 0';
            return;
        }

        // Clear loading row
        tbody.innerHTML = '';

        json.data.forEach(row => {
            const c = row || {};
            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>${c.valuedt || ''}</td>
                <td>${c.branchid || ''}</td>
                <td>${c.br_id || ''}</td>
                <td>${c.br_name || ''}</td>
                <td>${c.s_micro_b_khr != null ? c.s_micro_b_khr : ''}</td>
                <td>${c.s_micro_b_usd != null ? c.s_micro_b_usd : ''}</td>
                <td>${c.s_small_b_khr != null ? c.s_small_b_khr : ''}</td>
                <td>${c.s_small_b_usd != null ? c.s_small_b_usd : ''}</td>
                <td>${c.s_staff_loan_khr != null ? c.s_staff_loan_khr : ''}</td>
                <td>${c.s_staff_loan_usd != null ? c.s_staff_loan_usd : ''}</td>
                <td>${c.s_total != null ? c.s_total : ''}</td>
                <td>${c.l_micro_b_khr != null ? c.l_micro_b_khr : ''}</td>
                <td>${c.l_micro_b_usd != null ? c.l_micro_b_usd : ''}</td>
                <td>${c.l_small_b_khr != null ? c.l_small_b_khr : ''}</td>
                <td>${c.l_small_b_usd != null ? c.l_small_b_usd : ''}</td>
                <td>${c.l_staff_loan_khr != null ? c.l_staff_loan_khr : ''}</td>
                <td>${c.l_staff_loan_usd != null ? c.l_staff_loan_usd : ''}</td>
                <td>${c.l_total != null ? c.l_total : ''}</td>
                <td>${c.total != null ? c.total : ''}</td>
            `;

            tbody.appendChild(tr);
        });

        const countLabel = document.getElementById('clientInfmCount');
        if (countLabel) countLabel.textContent = `Records: ${json.data.length}`;
    } catch (error) {
        console.error('Failed to load client_infm data:', error);
        tbody.innerHTML = `
            <tr class="error-row">
                <td colspan="19">Failed to load client backdate data (API error).</td>
            </tr>
        `;
        const countLabel = document.getElementById('clientInfmCount');
        if (countLabel) countLabel.textContent = 'Records: 0';
    }
}

// Filter client_infm table rows based on search input
function filterClientInfmTable() {
    const input = document.getElementById('clientInfmSearchInput');
    const term = input ? input.value.toLowerCase() : '';
    const table = document.getElementById('clientInfmTable');
    if (!table) return;

    const rows = Array.from(table.querySelectorAll('tbody tr'));
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = !term || text.includes(term);
        row.style.display = match ? '' : 'none';
        if (match) visibleCount++;
    });

    const countLabel = document.getElementById('clientInfmCount');
    if (countLabel) countLabel.textContent = `Records: ${visibleCount}`;
}

// Close user menu when clicking outside
document.addEventListener('click', function(event) {
    const userProfile = document.querySelector('.user-profile-new');
    const userMenu = document.getElementById('userMenu');

    if (userProfile && userMenu && !userProfile.contains(event.target)) {
        userProfile.classList.remove('active');
    }
});

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    loadClientInfm();
});


