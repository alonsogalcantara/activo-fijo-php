/**
 * Sorts a HTML table.
 * 
 * @param {string} tableId The ID of the table to sort.
 * @param {number} n The index of the column to sort by (0-based).
 * @param {string} type The type of data: 'string', 'number', 'date'. Default 'string'.
 */
function sortTable(tableId, n, type = 'string') {
    var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.getElementById(tableId);
    switching = true;
    // Set the sorting direction to ascending:
    dir = "asc";

    // Reset other headers icons if implemented (optional)
    // For now simple sort logic

    while (switching) {
        switching = false;
        rows = table.rows;
        /* Loop through all table rows (except the first, which contains table headers): */
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            /* Get the two elements you want to compare, one from current row and one from the next: */
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];

            let xVal = x ? (x.getAttribute('data-raw') || x.innerText.toLowerCase()) : '';
            let yVal = y ? (y.getAttribute('data-raw') || y.innerText.toLowerCase()) : '';

            if (type === 'number') {
                xVal = parseFloat(xVal) || 0;
                yVal = parseFloat(yVal) || 0;
            } else if (type === 'date') {
                // Expecting data-raw to be YYYY-MM-DD or similar parseable format
                xVal = new Date(xVal).getTime() || 0;
                yVal = new Date(yVal).getTime() || 0;
            }

            if (dir == "asc") {
                if (xVal > yVal) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (xVal < yVal) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            /* If a switch has been marked, make the switch and mark that a switch has been done: */
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            // Each time a switch is done, increase this count by 1:
            switchcount++;
        } else {
            /* If no switching has been done AND the direction is "asc", set the direction to "desc" and run the while loop again. */
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }

    // Update Header Icons
    updateSortIcons(tableId, n, dir);
}

function updateSortIcons(tableId, colIndex, dir) {
    const table = document.getElementById(tableId);
    const headers = table.rows[0].getElementsByTagName("TH");

    for (let i = 0; i < headers.length; i++) {
        // Remove existing arrow icons
        let icon = headers[i].querySelector('.sort-icon');
        if (icon) icon.remove();

        // Add icon to current column
        if (i === colIndex) {
            const arrow = document.createElement('i');
            arrow.className = `fas fa-sort-${dir === 'asc' ? 'up' : 'down'} sort-icon ml-1`;
            headers[i].appendChild(arrow);
        }
    }
}
