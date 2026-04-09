/**
 * Sorts a HTML table.
 * 
 * @param {string} tableId The ID of the table to sort.
 * @param {number} colIndex The index of the column to sort by (0-based).
 * @param {string} type The type of data: 'string', 'number', 'date'. Default 'string'.
 */
function sortTable(tableId, colIndex, type = 'string') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);
    
    // Determine sort direction
    let dir = 'asc';
    const currentDir = table.getAttribute('data-sort-dir');
    const currentCol = table.getAttribute('data-sort-col');
    
    if (currentCol == colIndex && currentDir === 'asc') {
        dir = 'desc';
    }
    
    table.setAttribute('data-sort-dir', dir);
    table.setAttribute('data-sort-col', colIndex);

    // Sort the rows
    rows.sort((a, b) => {
        const aVal = parseValue(a, colIndex, type);
        const bVal = parseValue(b, colIndex, type);

        if (aVal === bVal) return 0;
        
        const comparison = (aVal > bVal) ? 1 : -1;
        return dir === 'asc' ? comparison : -comparison;
    });

    // Re-append rows in sorted order
    rows.forEach(row => tbody.appendChild(row));

    // Update Header Icons
    updateSortIcons(table, colIndex, dir);
}

function parseValue(row, colIndex, type) {
    const cell = row.cells[colIndex];
    if (!cell) return type === 'number' ? -Infinity : '';

    let val = cell.getAttribute('data-raw');
    if (val === null) {
        val = cell.innerText.trim();
    }

    if (type === 'number') {
        return cleanNumber(val);
    } else if (type === 'date') {
        // Try parsing data-raw first (SQL format), then innerText
        const date = new Date(val);
        return isNaN(date.getTime()) ? 0 : date.getTime();
    } else {
        return val.toLowerCase();
    }
}

function cleanNumber(str) {
    if (!str) return 0;
    // Remove currency symbols, commas, and spaces
    const cleaned = str.replace(/[$, ]/g, '');
    const num = parseFloat(cleaned);
    return isNaN(num) ? 0 : num;
}

function updateSortIcons(table, colIndex, dir) {
    const headers = table.tHead.rows[0].cells;
    
    for (let i = 0; i < headers.length; i++) {
        // Remove existing arrow icons
        let icon = headers[i].querySelector('.sort-icon');
        if (icon) icon.remove();

        // Add icon to current column if it matches
        if (i === colIndex) {
            const arrow = document.createElement('i');
            arrow.className = `fas fa-sort-${dir === 'asc' ? 'up' : 'down'} sort-icon ml-1 text-blue-400`;
            headers[i].appendChild(arrow);
            headers[i].classList.add('text-blue-400'); // Highlight active logical column header if desired
        } else {
             headers[i].classList.remove('text-blue-400');
        }
    }
}
