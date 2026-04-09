<?php
/**
 * Componente reutilizable: Barra de Búsqueda + Ordenamiento + Filtro por columna (Excel) + Paginación
 *
 * Pipeline completo: allRows → filter(search ∩ column-filters) → sort → paginate → render
 *
 * Parámetros en $params:
 *   - table_id          (string, requerido)
 *   - input_id          (string, opcional) Default: 'search_' + table_id
 *   - placeholder       (string, opcional) Default: 'Buscar...'
 *   - colspan           (int,    opcional) Default: 5
 *   - no_results        (string, opcional) Mensaje de lista vacía
 *   - sortable          (bool,   opcional) Default: true — habilita sort + filtro por columna
 *   - paginate          (bool,   opcional) Default: true
 *   - page_sizes        (array,  opcional) Default: [5,10,15,25,50,'all']
 *   - default_page_size (int,    opcional) Default: 10
 */
function renderSearchBar(array $params = []): void
{
    $tableId         = htmlspecialchars($params['table_id']          ?? 'dataTable');
    $inputId         = htmlspecialchars($params['input_id']          ?? 'search_' . $tableId);
    $placeholder     = htmlspecialchars($params['placeholder']       ?? 'Buscar...');
    $colspan         = (int) ($params['colspan']                     ?? 5);
    $noResultsMsg    = addslashes($params['no_results']              ?? 'No se encontraron resultados.');
    $sortable        = ($params['sortable']   ?? true) ? 'true' : 'false';
    $paginate        = ($params['paginate']   ?? true) ? 'true' : 'false';
    $pageSizes       = json_encode($params['page_sizes']             ?? [5, 10, 15, 25, 50, 'all']);
    $defaultPageSize = json_encode($params['default_page_size']      ?? 10);
    ?>
    <div class="search-bar-wrapper">
        <div class="search-bar-inner">
            <i class='bx bx-search search-bar-icon'></i>
            <input
                type="text"
                id="<?= $inputId ?>"
                class="search-bar-input"
                placeholder="<?= $placeholder ?>"
                autocomplete="off"
            >
            <button
                type="button"
                class="search-bar-clear"
                id="<?= $inputId ?>_clear"
                title="Limpiar búsqueda"
                style="display:none;"
            ><i class='bx bx-x'></i></button>
        </div>
        <span class="search-bar-counter" id="<?= $inputId ?>_counter"></span>
    </div>

    <script>
    (function () {

        /* ══ TableManager — define clase + CSS solo una vez por página ══ */
        if (!window.TableManager) {
            window.tableManagers = {};

            /* ── Estilos del filtro por columna (inyectados una sola vez) ── */
            (function injectStyles() {
                const css = `
                    /* Header cell layout */
                    .th-content {
                        display: flex;
                        align-items: center;
                        gap: 3px;
                        min-width: 0;
                    }
                    .th-label {
                        flex: 1;
                        min-width: 0;
                        cursor: pointer;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                    .th-label:hover { color: var(--primary-color, #4f46e5); }
                    .sort-indicator { cursor: pointer; }

                    /* Filter toggle button in header */
                    .col-filter-btn {
                        flex-shrink: 0;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 22px;
                        height: 22px;
                        padding: 0;
                        background: none;
                        border: 1px solid transparent;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 0.88rem;
                        color: var(--text-secondary, #6b7280);
                        transition: background 0.18s, color 0.18s, border-color 0.18s;
                        line-height: 1;
                    }
                    .col-filter-btn:hover {
                        background: rgba(79,70,229,.1);
                        border-color: rgba(79,70,229,.25);
                        color: var(--primary-color, #4f46e5);
                    }
                    .col-filter-btn.col-filter-active {
                        background: rgba(79,70,229,.14);
                        border-color: rgba(79,70,229,.35);
                        color: var(--primary-color, #4f46e5);
                    }

                    /* Dropdown panel */
                    .col-filter-panel {
                        position: fixed;
                        z-index: 9999;
                        min-width: 220px;
                        max-width: 280px;
                        background: var(--surface-color, #fff);
                        border: 1px solid var(--border-color, #e5e7eb);
                        border-radius: 12px;
                        box-shadow: 0 12px 32px rgba(0,0,0,0.14), 0 3px 10px rgba(0,0,0,0.08);
                        overflow: hidden;
                        animation: _cfpIn .15s cubic-bezier(.22,1,.36,1);
                    }
                    @keyframes _cfpIn {
                        from { opacity:0; transform:translateY(-8px) scale(.97); }
                        to   { opacity:1; transform:translateY(0)    scale(1);   }
                    }

                    /* Search within dropdown */
                    .col-filter-search-wrap {
                        padding: 10px 10px 8px;
                        border-bottom: 1px solid var(--border-color, #e5e7eb);
                    }
                    .col-filter-search {
                        width: 100%;
                        box-sizing: border-box;
                        padding: 6px 10px;
                        border: 1.5px solid var(--border-color, #e5e7eb);
                        border-radius: 7px;
                        font-size: 0.82rem;
                        font-family: inherit;
                        color: var(--text-primary, #111);
                        background: var(--background-color, #f3f4f6);
                        outline: none;
                        transition: border-color .2s, box-shadow .2s;
                    }
                    .col-filter-search:focus {
                        border-color: var(--primary-color, #4f46e5);
                        box-shadow: 0 0 0 3px rgba(79,70,229,.1);
                    }

                    /* Select all row */
                    .col-filter-select-all {
                        padding: 8px 12px;
                        border-bottom: 1px solid var(--border-color, #e5e7eb);
                        background: var(--background-color, #f9fafb);
                    }

                    /* Scrollable values list */
                    .col-filter-list {
                        max-height: 200px;
                        overflow-y: auto;
                        padding: 4px 0;
                    }
                    .col-filter-list::-webkit-scrollbar { width: 6px; }
                    .col-filter-list::-webkit-scrollbar-track { background: transparent; }
                    .col-filter-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }

                    /* Each filter item row */
                    .col-filter-item {
                        padding: 5px 12px;
                        transition: background .12s;
                    }
                    .col-filter-item:hover { background: var(--background-color, #f3f4f6); }
                    .col-filter-item label {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        cursor: pointer;
                        font-size: 0.84rem;
                        color: var(--text-primary, #111);
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        user-select: none;
                    }
                    .col-filter-item input[type=checkbox] {
                        flex-shrink: 0;
                        width: 14px;
                        height: 14px;
                        accent-color: var(--primary-color, #4f46e5);
                        cursor: pointer;
                    }
                    /* Select-all label style */
                    .col-filter-select-all label {
                        font-size: 0.79rem;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: .04em;
                        color: var(--text-secondary, #6b7280);
                    }

                    /* Footer buttons */
                    .col-filter-footer {
                        display: flex;
                        gap: 8px;
                        padding: 8px 10px;
                        border-top: 1px solid var(--border-color, #e5e7eb);
                        background: var(--background-color, #f9fafb);
                    }
                    .col-filter-clear, .col-filter-apply {
                        flex: 1;
                        padding: 6px 10px;
                        border-radius: 7px;
                        font-size: 0.82rem;
                        font-family: inherit;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all .2s;
                    }
                    .col-filter-clear {
                        background: none;
                        border: 1.5px solid var(--border-color, #e5e7eb);
                        color: var(--text-secondary, #6b7280);
                    }
                    .col-filter-clear:hover {
                        border-color: var(--danger-color, #ef4444);
                        color: var(--danger-color, #ef4444);
                        background: rgba(239,68,68,.05);
                    }
                    .col-filter-apply {
                        background: var(--primary-color, #4f46e5);
                        border: 1.5px solid var(--primary-color, #4f46e5);
                        color: #fff;
                    }
                    .col-filter-apply:hover {
                        background: var(--primary-hover, #4338ca);
                        border-color: var(--primary-hover, #4338ca);
                    }

                    /* Active-filter badge in pagination info */
                    .col-filter-count-badge {
                        display: inline-block;
                        font-size: 0.72rem;
                        font-weight: 700;
                        background: var(--primary-color, #4f46e5);
                        color: #fff;
                        border-radius: 999px;
                        padding: 2px 7px;
                        margin-left: 6px;
                        vertical-align: middle;
                        letter-spacing: .02em;
                    }

                    /* Override: sortable-th cursor comes from th-content children now */
                    .sortable-th { cursor: default !important; }
                `;
                const el = document.createElement('style');
                el.textContent = css;
                document.head.appendChild(el);
            })();

            /* ════════════════════════════════════════════════════════════
             *  TableManager
             * ════════════════════════════════════════════════════════════ */
            class TableManager {
                constructor(cfg) {
                    this.tableId        = cfg.tableId;
                    this.inputId        = cfg.inputId;
                    this.colspan        = cfg.colspan        || 5;
                    this.noResultsMsg   = cfg.noResultsMsg   || 'No se encontraron resultados.';
                    this.sortableFlag   = cfg.sortable       !== false;
                    this.paginateFlag   = cfg.paginate       !== false;
                    this.pageSizes      = cfg.pageSizes      || [5, 10, 15, 25, 50, 'all'];
                    this.pageSize       = cfg.defaultPageSize || 10;
                    this.sortCol        = -1;
                    this.sortDir        = 'asc';
                    this.currentPage    = 1;
                    this.searchQuery    = '';
                    this.columnFilters  = new Map(); /* colIdx → Set<string> */
                    this._activeDropdown = null;

                    this.table = document.getElementById(this.tableId);
                    if (!this.table) return;
                    this.tbody = this.table.querySelector('tbody');
                    this.thead = this.table.querySelector('thead');
                    this.allRows = Array.from(this.tbody.querySelectorAll('tr'));

                    this._setupSearch();
                    if (this.sortableFlag) this._setupSort();
                    if (this.paginateFlag) this._setupPagination();
                    this._render();
                }

                /* ── Search bar ──────────────────────────────────────── */
                _setupSearch() {
                    const input    = document.getElementById(this.inputId);
                    const clearBtn = document.getElementById(this.inputId + '_clear');
                    if (!input) return;

                    input.addEventListener('input', () => {
                        this.searchQuery = input.value.trim().toLowerCase();
                        if (clearBtn) clearBtn.style.display = this.searchQuery ? 'flex' : 'none';
                        this.currentPage = 1;
                        this._render();
                    });

                    clearBtn?.addEventListener('click', () => {
                        input.value      = '';
                        this.searchQuery = '';
                        clearBtn.style.display = 'none';
                        this.currentPage = 1;
                        input.focus();
                        this._render();
                    });

                    input.addEventListener('keydown', e => {
                        if (e.key === 'Escape') {
                            input.value      = '';
                            this.searchQuery = '';
                            if (clearBtn) clearBtn.style.display = 'none';
                            this.currentPage = 1;
                            this._render();
                        }
                    });
                }

                /* ── Sort + column-filter header setup ───────────────── */
                _setupSort() {
                    if (!this.thead) return;
                    const ths = this.thead.querySelectorAll('th');

                    ths.forEach((th, idx) => {
                        const labelText = th.textContent.trim();
                        th.innerHTML = '';
                        th.classList.add('sortable-th');

                        /* Wrapper */
                        const content = document.createElement('div');
                        content.className = 'th-content';

                        const labelEl = document.createElement('span');
                        labelEl.className = 'th-label';
                        labelEl.textContent = labelText;
                        labelEl.title = 'Ordenar por ' + labelText;

                        const sortInd = document.createElement('span');
                        sortInd.className = 'sort-indicator';
                        sortInd.innerHTML = "<i class='bx bx-sort'></i>";
                        sortInd.title = 'Ordenar';

                        const filterBtn = document.createElement('button');
                        filterBtn.type = 'button';
                        filterBtn.className = 'col-filter-btn';
                        filterBtn.title = 'Filtrar "' + labelText + '"';
                        filterBtn.setAttribute('data-col', idx);
                        filterBtn.innerHTML = "<i class='bx bx-filter-alt'></i>";

                        content.appendChild(labelEl);
                        content.appendChild(sortInd);
                        content.appendChild(filterBtn);
                        th.appendChild(content);

                        /* Sort: click on label or sort indicator */
                        [labelEl, sortInd].forEach(el => {
                            el.addEventListener('click', () => {
                                if (this.sortCol === idx) {
                                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                                } else {
                                    this.sortCol = idx;
                                    this.sortDir = 'asc';
                                }
                                this.currentPage = 1;
                                this._updateSortUI(ths, idx);
                                this._render();
                            });
                        });

                        /* Filter button */
                        filterBtn.addEventListener('click', e => {
                            e.stopPropagation();
                            this._toggleFilterDropdown(idx, filterBtn);
                        });
                    });
                }

                _updateSortUI(ths, activeIdx) {
                    ths.forEach((th, i) => {
                        const ind = th.querySelector('.sort-indicator');
                        if (!ind) return;
                        th.classList.remove('th-sorted');
                        if (i === activeIdx) {
                            th.classList.add('th-sorted');
                            ind.innerHTML = this.sortDir === 'asc'
                                ? "<i class='bx bx-sort-up'></i>"
                                : "<i class='bx bx-sort-down'></i>";
                        } else {
                            ind.innerHTML = "<i class='bx bx-sort'></i>";
                        }
                    });
                }

                /* ── Column filter dropdown ──────────────────────────── */
                _toggleFilterDropdown(colIdx, btn) {
                    const existing = document.getElementById('_cfp_' + this.tableId);
                    if (existing) {
                        const openCol = parseInt(existing.dataset.col);
                        this._closeFilterDropdown();
                        if (openCol === colIdx) return; /* Same col → just close */
                    }
                    this._openFilterDropdown(colIdx, btn);
                }

                _openFilterDropdown(colIdx, btn) {
                    const uniqueVals   = this._getUniqueValues(colIdx);
                    const activeFilter = this.columnFilters.get(colIdx);

                    /* ── Panel ── */
                    const panel = document.createElement('div');
                    panel.className = 'col-filter-panel';
                    panel.id = '_cfp_' + this.tableId;
                    panel.dataset.col = String(colIdx);

                    /* Search */
                    const searchWrap = document.createElement('div');
                    searchWrap.className = 'col-filter-search-wrap';
                    const searchInput = document.createElement('input');
                    searchInput.type = 'text';
                    searchInput.className = 'col-filter-search';
                    searchInput.placeholder = 'Buscar valor...';
                    searchWrap.appendChild(searchInput);

                    /* Select-all row */
                    const allSelected = !activeFilter;
                    const saId = '_csa_' + this.tableId + '_' + colIdx;
                    const saRow = document.createElement('div');
                    saRow.className = 'col-filter-item col-filter-select-all';
                    const saCb  = document.createElement('input');
                    saCb.type = 'checkbox';
                    saCb.id   = saId;
                    saCb.checked = allSelected;
                    const saLbl = document.createElement('label');
                    saLbl.htmlFor = saId;
                    saLbl.appendChild(saCb);
                    saLbl.appendChild(document.createTextNode('\u00a0(Seleccionar todo)'));
                    saRow.appendChild(saLbl);

                    /* Values list */
                    const listEl = document.createElement('div');
                    listEl.className = 'col-filter-list';

                    uniqueVals.forEach(val => {
                        const isChecked = !activeFilter || activeFilter.has(val);
                        const item = document.createElement('div');
                        item.className = 'col-filter-item';
                        item.dataset.v = val;

                        const cb  = document.createElement('input');
                        cb.type   = 'checkbox';
                        cb.value  = val;
                        cb.checked = isChecked;

                        const lbl = document.createElement('label');
                        lbl.appendChild(cb);
                        lbl.appendChild(document.createTextNode('\u00a0' + (val || '(vacío)')));
                        item.appendChild(lbl);
                        listEl.appendChild(item);
                    });

                    /* Footer */
                    const footer  = document.createElement('div');
                    footer.className = 'col-filter-footer';
                    const btnClear = document.createElement('button');
                    btnClear.type = 'button';
                    btnClear.className = 'col-filter-clear';
                    btnClear.textContent = 'Limpiar';
                    const btnApply = document.createElement('button');
                    btnApply.type = 'button';
                    btnApply.className = 'col-filter-apply';
                    btnApply.textContent = 'Aplicar';
                    footer.appendChild(btnClear);
                    footer.appendChild(btnApply);

                    panel.appendChild(searchWrap);
                    panel.appendChild(saRow);
                    panel.appendChild(listEl);
                    panel.appendChild(footer);
                    document.body.appendChild(panel);
                    this._positionDropdown(panel, btn);

                    /* ── Helpers ── */
                    const getValueCbs = () => Array.from(listEl.querySelectorAll('input[type=checkbox]'));

                    const syncSelectAll = () => {
                        const visible = getValueCbs().filter(c => c.closest('.col-filter-item').style.display !== 'none');
                        const cnt = visible.filter(c => c.checked).length;
                        saCb.checked       = visible.length > 0 && cnt === visible.length;
                        saCb.indeterminate = cnt > 0 && cnt < visible.length;
                    };
                    if (activeFilter) syncSelectAll();

                    /* Select-all */
                    saCb.addEventListener('change', () => {
                        getValueCbs().forEach(cb => {
                            if (cb.closest('.col-filter-item').style.display !== 'none') {
                                cb.checked = saCb.checked;
                            }
                        });
                    });
                    listEl.addEventListener('change', syncSelectAll);

                    /* Mini search */
                    searchInput.addEventListener('input', () => {
                        const q = searchInput.value.toLowerCase();
                        listEl.querySelectorAll('.col-filter-item').forEach(item => {
                            const match = (item.dataset.v || '').toLowerCase().includes(q);
                            item.style.display = match ? '' : 'none';
                        });
                        syncSelectAll();
                    });

                    /* Clear filter */
                    btnClear.addEventListener('click', () => {
                        this.columnFilters.delete(colIdx);
                        this._updateFilterUI();
                        this._closeFilterDropdown();
                        this.currentPage = 1;
                        this._render();
                    });

                    /* Apply filter */
                    btnApply.addEventListener('click', () => {
                        const selected = getValueCbs().filter(c => c.checked).map(c => c.value);
                        if (selected.length >= uniqueVals.length) {
                            this.columnFilters.delete(colIdx); /* All = no filter */
                        } else {
                            this.columnFilters.set(colIdx, new Set(selected));
                        }
                        this._updateFilterUI();
                        this._closeFilterDropdown();
                        this.currentPage = 1;
                        this._render();
                    });

                    /* Close on click outside */
                    const outsideClose = e => {
                        if (!panel.contains(e.target) && !btn.contains(e.target)) {
                            this._closeFilterDropdown();
                        }
                    };
                    setTimeout(() => document.addEventListener('mousedown', outsideClose), 50);
                    panel._oc = outsideClose;

                    /* Close on Escape */
                    const escClose = e => { if (e.key === 'Escape') this._closeFilterDropdown(); };
                    document.addEventListener('keydown', escClose);
                    panel._ec = escClose;

                    /* Close on scroll (table moves, panel stays fixed) */
                    window.addEventListener('scroll', () => this._closeFilterDropdown(), { once: true, passive: true });

                    this._activeDropdown = panel;
                    searchInput.focus();
                }

                _closeFilterDropdown() {
                    const panel = document.getElementById('_cfp_' + this.tableId);
                    if (!panel) return;
                    if (panel._oc) document.removeEventListener('mousedown', panel._oc);
                    if (panel._ec) document.removeEventListener('keydown',   panel._ec);
                    panel.remove();
                    this._activeDropdown = null;
                }

                _positionDropdown(panel, btn) {
                    const rect   = btn.getBoundingClientRect();
                    const panelW = 240;
                    const panelH = 340;

                    let left = rect.left;
                    let top  = rect.bottom + 6;

                    if (left + panelW > window.innerWidth - 8)  left = window.innerWidth - panelW - 8;
                    if (left < 4)                               left = 4;
                    if (top + panelH > window.innerHeight - 8)  top  = rect.top - panelH - 6;

                    panel.style.left  = left + 'px';
                    panel.style.top   = top  + 'px';
                    panel.style.width = panelW + 'px';
                }

                _getUniqueValues(colIdx) {
                    const vals = new Set();
                    this.allRows.forEach(row => {
                        const cell = row.cells[colIdx];
                        if (cell) vals.add(cell.textContent.trim());
                    });
                    return Array.from(vals).sort((a, b) => {
                        const na = parseFloat(a.replace(/[$,\s]/g, ''));
                        const nb = parseFloat(b.replace(/[$,\s]/g, ''));
                        if (!isNaN(na) && !isNaN(nb)) return na - nb;
                        return a.localeCompare(b, 'es', { sensitivity: 'base' });
                    });
                }

                _updateFilterUI() {
                    if (!this.thead) return;
                    this.thead.querySelectorAll('.col-filter-btn').forEach(btn => {
                        const col    = parseInt(btn.dataset.col);
                        const active = this.columnFilters.has(col);
                        btn.classList.toggle('col-filter-active', active);
                        btn.title = active
                            ? 'Filtro activo — click para modificar'
                            : 'Filtrar esta columna';
                    });
                }

                /* ── Pagination setup ────────────────────────────────── */
                _setupPagination() {
                    const container = this.table.closest('.table-container');
                    if (!container) return;
                    const card = container.parentElement;
                    if (!card) return;
                    const el = document.createElement('div');
                    el.className = 'table-pagination';
                    el.id = this.tableId + '_pagination';
                    card.appendChild(el);
                    this.paginationEl = el;
                }

                /* ── Core render pipeline ────────────────────────────── */
                _getFiltered() {
                    return this.allRows.filter(row => {
                        /* Global search */
                        if (this.searchQuery && !row.textContent.toLowerCase().includes(this.searchQuery)) {
                            return false;
                        }
                        /* Column filters — AND logic */
                        for (const [colIdx, allowed] of this.columnFilters) {
                            const cell = row.cells[colIdx];
                            const text = cell ? cell.textContent.trim() : '';
                            if (!allowed.has(text)) return false;
                        }
                        return true;
                    });
                }

                _getSorted(rows) {
                    if (this.sortCol < 0) return rows;
                    return [...rows].sort((a, b) => {
                        const ca = a.cells[this.sortCol];
                        const cb = b.cells[this.sortCol];
                        if (!ca || !cb) return 0;
                        const ta = ca.textContent.trim();
                        const tb = cb.textContent.trim();
                        const na = parseFloat(ta.replace(/[$,\s]/g, ''));
                        const nb = parseFloat(tb.replace(/[$,\s]/g, ''));
                        const numeric = !isNaN(na) && !isNaN(nb);
                        const cmp = numeric ? na - nb : ta.localeCompare(tb, 'es', { sensitivity: 'base' });
                        return this.sortDir === 'asc' ? cmp : -cmp;
                    });
                }

                _render() {
                    const filtered   = this._getFiltered();
                    const sorted     = this._getSorted(filtered);
                    const total      = sorted.length;
                    const sizeIsAll  = this.pageSize === 'all';
                    const effectSize = sizeIsAll ? total : this.pageSize;
                    const totalPages = effectSize > 0 ? Math.ceil(total / effectSize) : 1;

                    if (this.currentPage > totalPages) this.currentPage = Math.max(1, totalPages);

                    const start    = sizeIsAll ? 0 : (this.currentPage - 1) * this.pageSize;
                    const end      = sizeIsAll ? total : Math.min(start + this.pageSize, total);
                    const pageRows = sorted.slice(start, end);
                    const pageSet  = new Set(pageRows);

                    const noRow = document.getElementById(this.tableId + '_no_results');
                    if (noRow) noRow.remove();

                    this.allRows.forEach(r => (r.style.display = 'none'));
                    pageRows.forEach(r => { this.tbody.appendChild(r); r.style.display = ''; });
                    this.allRows.forEach(r => { if (!pageSet.has(r)) this.tbody.appendChild(r); });

                    if (total === 0 && this.allRows.length > 0) {
                        const nr  = document.createElement('tr');
                        nr.id = this.tableId + '_no_results';
                        const td  = document.createElement('td');
                        td.colSpan = this.colspan;
                        td.style.cssText = 'text-align:center;color:var(--text-secondary);padding:30px;';
                        const hasColFilter = this.columnFilters.size > 0;
                        td.innerHTML = "<i class='bx " + (hasColFilter ? 'bx-filter-alt' : 'bx-search-alt') + "' style='font-size:1.4rem;vertical-align:middle;margin-right:8px;opacity:.5;'></i>" + this.noResultsMsg;
                        nr.appendChild(td);
                        this.tbody.appendChild(nr);
                    }

                    /* Counter — shows for search OR column filters */
                    const counter = document.getElementById(this.inputId + '_counter');
                    if (counter) {
                        const active = this.searchQuery || this.columnFilters.size > 0;
                        if (active) {
                            counter.textContent = total + ' resultado' + (total !== 1 ? 's' : '');
                            counter.style.display = 'inline-block';
                        } else {
                            counter.style.display = 'none';
                        }
                    }

                    if (this.paginateFlag) this._renderPagination(total, totalPages, start, end);
                }

                /* ── Pagination UI ───────────────────────────────────── */
                _renderPagination(total, totalPages, start, end) {
                    if (!this.paginationEl) return;
                    const startD   = total === 0 ? 0 : start + 1;
                    const endD     = Math.min(end, total);
                    const tid      = this.tableId;
                    const fCount   = this.columnFilters.size;

                    const sizeOpts = this.pageSizes.map(s => {
                        const val = s === 'all' ? 'all' : s;
                        const lbl = s === 'all' ? 'Todos' : s;
                        const cur = this.pageSize === 'all' ? 'all' : this.pageSize;
                        const sel = String(val) === String(cur) ? 'selected' : '';
                        return `<option value="${val}" ${sel}>${lbl}</option>`;
                    }).join('');

                    let btns = '';
                    if (totalPages > 1) {
                        btns += `<button class="page-btn" ${this.currentPage===1?'disabled':''} onclick="window.tableManagers['${tid}'].goToPage(${this.currentPage-1})"><i class='bx bx-chevron-left'></i></button>`;
                        this._pageRange(this.currentPage, totalPages).forEach(p => {
                            btns += p === '...'
                                ? `<span class="page-ellipsis">…</span>`
                                : `<button class="page-btn${p===this.currentPage?' active':''}" onclick="window.tableManagers['${tid}'].goToPage(${p})">${p}</button>`;
                        });
                        btns += `<button class="page-btn" ${this.currentPage===totalPages?'disabled':''} onclick="window.tableManagers['${tid}'].goToPage(${this.currentPage+1})"><i class='bx bx-chevron-right'></i></button>`;
                    }

                    const filterBadge = fCount > 0
                        ? `<span class="col-filter-count-badge">${fCount} filtro${fCount!==1?'s':''} activo${fCount!==1?'s':''}</span>`
                        : '';

                    this.paginationEl.innerHTML = `
                        <div class="pagination-info">
                            Mostrando <strong>${startD}–${endD}</strong> de <strong>${total}</strong> registros${filterBadge}
                        </div>
                        <div class="pagination-controls">${btns}</div>
                        <div class="pagination-size">
                            <label class="pagination-label">Por página:</label>
                            <select class="page-size-select" onchange="window.tableManagers['${tid}'].setPageSize(this.value)">
                                ${sizeOpts}
                            </select>
                        </div>`;
                }

                _pageRange(cur, total) {
                    if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
                    const r = [];
                    if (cur <= 4) {
                        for (let i = 1; i <= 5; i++) r.push(i);
                        r.push('...', total);
                    } else if (cur >= total - 3) {
                        r.push(1, '...');
                        for (let i = total - 4; i <= total; i++) r.push(i);
                    } else {
                        r.push(1, '...');
                        for (let i = cur - 1; i <= cur + 1; i++) r.push(i);
                        r.push('...', total);
                    }
                    return r;
                }

                goToPage(page) {
                    this.currentPage = page;
                    this._render();
                    this.table.closest('.card')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }

                setPageSize(val) {
                    this.pageSize    = val === 'all' ? 'all' : parseInt(val, 10);
                    this.currentPage = 1;
                    this._render();
                }
            }

            window.TableManager = TableManager;
        }

        /* ── Instanciar para esta tabla, cuando el DOM esté listo ─── */
        document.addEventListener('DOMContentLoaded', function () {
            window.tableManagers['<?= $tableId ?>'] = new window.TableManager({
                tableId:         '<?= $tableId ?>',
                inputId:         '<?= $inputId ?>',
                colspan:         <?= $colspan ?>,
                noResultsMsg:    '<?= $noResultsMsg ?>',
                sortable:        <?= $sortable ?>,
                paginate:        <?= $paginate ?>,
                pageSizes:       <?= $pageSizes ?>,
                defaultPageSize: <?= $defaultPageSize ?>,
            });
        });

    })();
    </script>
    <?php
}
