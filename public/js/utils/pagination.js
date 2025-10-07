/**
 * Centralized Pagination Utility for managing table data with server-side pagination
 * @author KingLang-Booking
 */

function initializePagination(config) {
    const {
        tableBodyId,
        paginationContainerId,
        recordInfoId,
        defaultLimit = 10,
        defaultSortColumn = 'id',
        defaultSortOrder = 'asc',
        fetchDataFunction,
        renderRowsFunction,
        paginationType = 'standard', // Possible values: 'standard', 'advanced'
        showRecordInfo = true,
        pageRangeDisplayed = 5, // Number of page items to show in pagination
        className = '' // Additional CSS classes to add to the pagination element
    } = config;

    // Internal state
    const state = {
        currentPage: 1,
        limit: defaultLimit,
        sortColumn: defaultSortColumn,
        sortOrder: defaultSortOrder,
        filter: 'all',
        search: '',
        totalItems: 0,
        totalPages: 0,
        loading: false
    };

    // DOM Elements
    const tableBody = document.getElementById(tableBodyId);
    const paginationContainer = document.getElementById(paginationContainerId);
    const recordInfo = document.getElementById(recordInfoId);

    // Function to load data
    async function loadData() {
        if (state.loading) return;
        
        state.loading = true;
        console.log("Loading data with state:", { ...state });
        showLoading();
        
        try {
            const result = await fetchDataFunction({
                page: state.currentPage,
                limit: state.limit,
                sortColumn: state.sortColumn,
                sortOrder: state.sortOrder,
                status: state.filter,
                search: state.search
            });
            
            console.log("Data loaded:", result);
            
            if (result) {
                state.totalItems = result.total;
                state.totalPages = result.totalPages;
                
                renderRowsFunction(result.items);
                renderPagination();
                if (showRecordInfo) {
                    updateRecordInfo();
                }
            }
        } catch (error) {
            console.error("Error loading data:", error);
            tableBody.innerHTML = `<tr><td colspan="20" class="text-center text-danger">Error loading data. Please try again.</td></tr>`;
        } finally {
            state.loading = false;
            hideLoading();
        }
    }

    // Show loading indicator
    function showLoading() {
        tableBody.innerHTML = `<tr><td colspan="20" class="text-center">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div> Loading data...
        </td></tr>`;
    }

    // Hide loading indicator
    function hideLoading() {
        // Loading indicator will be replaced by the actual data
    }

    // Render pagination controls
    function renderPagination() {
        if (!paginationContainer) return;
        
        paginationContainer.innerHTML = '';
        
        // Don't show pagination if total items less than limit or only one page
        if (state.totalItems <= state.limit || state.totalPages <= 1) return;
        
        const ul = document.createElement('ul');
        ul.className = `pagination justify-content-center ${className}`;
        
        if (paginationType === 'standard') {
            renderStandardPagination(ul);
        } else if (paginationType === 'advanced') {
            renderAdvancedPagination(ul);
        }
        
        paginationContainer.appendChild(ul);
    }
    
    // Render standard pagination with prev, page numbers, next
    function renderStandardPagination(ul) {
        // Previous button
        appendPrevButton(ul);
        
        // Page numbers
        const startPage = Math.max(1, state.currentPage - Math.floor(pageRangeDisplayed / 2));
        const endPage = Math.min(state.totalPages, startPage + pageRangeDisplayed - 1);
        
        for (let i = startPage; i <= endPage; i++) {
            appendPageItem(ul, i);
        }
        
        // Next button
        appendNextButton(ul);
    }
    
    // Render advanced pagination with prev, first, ellipsis, page numbers, ellipsis, last, next
    function renderAdvancedPagination(ul) {
        // Previous button
        appendPrevButton(ul);
        
        // First page
        appendPageItem(ul, 1);
        
        // Calculate visible page range
        const halfRange = Math.floor(pageRangeDisplayed / 2);
        let startPage = Math.max(2, state.currentPage - halfRange);
        let endPage = Math.min(state.totalPages - 1, state.currentPage + halfRange);
        
        // Adjust range if needed
        if (endPage - startPage < pageRangeDisplayed - 2) {
            if (state.currentPage < state.totalPages / 2) {
                endPage = Math.min(state.totalPages - 1, startPage + pageRangeDisplayed - 3);
            } else {
                startPage = Math.max(2, endPage - pageRangeDisplayed + 3);
            }
        }
        
        // Add ellipsis if needed before start page
        if (startPage > 2) {
            appendEllipsis(ul);
        }
        
        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            appendPageItem(ul, i);
        }
        
        // Add ellipsis if needed after end page
        if (endPage < state.totalPages - 1) {
            appendEllipsis(ul);
        }
        
        // Last page (if not already added)
        if (state.totalPages > 1) {
            appendPageItem(ul, state.totalPages);
        }
        
        // Next button
        appendNextButton(ul);
    }
    
    // Helper function to append previous button
    function appendPrevButton(ul) {
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${state.currentPage === 1 ? 'disabled' : ''}`;
        
        const prevLink = document.createElement('a');
        prevLink.className = 'page-link';
        prevLink.href = '#';
        prevLink.innerHTML = '&laquo;';
        prevLink.setAttribute('aria-label', 'Previous');
        
        prevLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (state.currentPage > 1) {
                goToPage(state.currentPage - 1);
            }
        });
        
        prevLi.appendChild(prevLink);
        ul.appendChild(prevLi);
    }
    
    // Helper function to append page item
    function appendPageItem(ul, pageNumber) {
        const li = document.createElement('li');
        li.className = `page-item ${pageNumber === state.currentPage ? 'active' : ''}`;
        
        const link = document.createElement('a');
        link.className = 'page-link';
        link.href = '#';
        link.textContent = pageNumber;
        
        link.addEventListener('click', (e) => {
            e.preventDefault();
            goToPage(pageNumber);
        });
        
        li.appendChild(link);
        ul.appendChild(li);
    }
    
    // Helper function to append ellipsis
    function appendEllipsis(ul) {
        const li = document.createElement('li');
        li.className = 'page-item disabled';
        
        const span = document.createElement('span');
        span.className = 'page-link';
        span.textContent = '...';
        
        li.appendChild(span);
        ul.appendChild(li);
    }
    
    // Helper function to append next button
    function appendNextButton(ul) {
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${state.currentPage === state.totalPages ? 'disabled' : ''}`;
        
        const nextLink = document.createElement('a');
        nextLink.className = 'page-link';
        nextLink.href = '#';
        nextLink.innerHTML = '&raquo;';
        nextLink.setAttribute('aria-label', 'Next');
        
        nextLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (state.currentPage < state.totalPages) {
                goToPage(state.currentPage + 1);
            }
        });
        
        nextLi.appendChild(nextLink);
        ul.appendChild(nextLi);
    }

    // Update record info text
    function updateRecordInfo() {
        if (!recordInfo) return;
        
        const start = (state.currentPage - 1) * state.limit + 1;
        const end = Math.min(state.currentPage * state.limit, state.totalItems);
        
        if (state.totalItems === 0) {
            recordInfo.textContent = 'No records found';
        } else {
            recordInfo.textContent = `Showing ${start} to ${end} of ${state.totalItems} entries`;
        }
    }

    // Go to specific page
    function goToPage(page) {
        if (page < 1 || page > state.totalPages || page === state.currentPage) return;
        
        state.currentPage = page;
        loadData();
    }

    // Public methods for the API
    return {
        // Initialize and load data
        init() {
            loadData();
            return this;
        },
        
        // Refresh data with current parameters
        refresh() {
            state.currentPage = 1;
            loadData();
            return this;
        },
        
        // Set current page
        setPage(page) {
            goToPage(page);
            return this;
        },
        
        // Set sort parameters and refresh
        setSort(column, order) {
            if (column) state.sortColumn = column;
            if (order) state.sortOrder = order;
            state.currentPage = 1;
            loadData();
            return this;
        },
        
        // Set filter and refresh
        setFilter(filter) {
            state.filter = filter;
            state.currentPage = 1;
            loadData();
            return this;
        },
        
        // Set search term and refresh
        setSearchTerm(term) {
            state.search = term;
            state.currentPage = 1;
            loadData();
            return this;
        },
        
        // Set limit per page and refresh
        setLimit(limit) {
            state.limit = parseInt(limit, 10);
            state.currentPage = 1;
            loadData();
            return this;
        },
        
        // Get current state
        getState() {
            return { ...state };
        },
        
        // Render pagination directly
        renderPaginationOnly(totalPages, currentPage, containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            state.totalPages = totalPages;
            state.currentPage = currentPage;
            
            container.innerHTML = '';
            
            if (totalPages <= 1) return;
            
            const ul = document.createElement('ul');
            ul.className = `pagination justify-content-center ${className}`;
            
            if (paginationType === 'standard') {
                renderStandardPagination(ul);
            } else if (paginationType === 'advanced') {
                renderAdvancedPagination(ul);
            }
            
            container.appendChild(ul);
            
            return { 
                goToPage 
            };
        }
    };
}

/**
 * Function to create a standalone pagination element for direct use
 * @param {Object} options - Configuration options
 * @returns {Object} - Object with methods to control pagination
 */
function createPagination(options) {
    const {
        containerId,
        totalPages = 1,
        currentPage = 1,
        onPageChange,
        paginationType = 'standard',
        pageRangeDisplayed = 5,
        className = ''
    } = options;
    
    const container = document.getElementById(containerId);
    if (!container) return null;
    
    const state = {
        currentPage,
        totalPages
    };
    
    function renderPagination() {
        container.innerHTML = '';
        
        if (totalPages <= 1) return;
        
        const ul = document.createElement('ul');
        ul.className = `pagination justify-content-center ${className}`;
        
        if (paginationType === 'standard') {
            renderStandardPagination(ul);
        } else if (paginationType === 'advanced') {
            renderAdvancedPagination(ul);
        }
        
        container.appendChild(ul);
    }
    
    function renderStandardPagination(ul) {
        // Previous button
        appendPrevButton(ul);
        
        // Page numbers
        const startPage = Math.max(1, state.currentPage - Math.floor(pageRangeDisplayed / 2));
        const endPage = Math.min(state.totalPages, startPage + pageRangeDisplayed - 1);
        
        for (let i = startPage; i <= endPage; i++) {
            appendPageItem(ul, i);
        }
        
        // Next button
        appendNextButton(ul);
    }
    
    function renderAdvancedPagination(ul) {
        // Previous button
        appendPrevButton(ul);
        
        // First page
        appendPageItem(ul, 1);
        
        // Calculate visible page range
        const halfRange = Math.floor(pageRangeDisplayed / 2);
        let startPage = Math.max(2, state.currentPage - halfRange);
        let endPage = Math.min(state.totalPages - 1, state.currentPage + halfRange);
        
        // Adjust range if needed
        if (endPage - startPage < pageRangeDisplayed - 2) {
            if (state.currentPage < state.totalPages / 2) {
                endPage = Math.min(state.totalPages - 1, startPage + pageRangeDisplayed - 3);
            } else {
                startPage = Math.max(2, endPage - pageRangeDisplayed + 3);
            }
        }
        
        // Add ellipsis if needed before start page
        if (startPage > 2) {
            appendEllipsis(ul);
        }
        
        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            appendPageItem(ul, i);
        }
        
        // Add ellipsis if needed after end page
        if (endPage < state.totalPages - 1) {
            appendEllipsis(ul);
        }
        
        // Last page (if not already added)
        if (state.totalPages > 1) {
            appendPageItem(ul, state.totalPages);
        }
        
        // Next button
        appendNextButton(ul);
    }
    
    function appendPrevButton(ul) {
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${state.currentPage === 1 ? 'disabled' : ''}`;
        
        const prevLink = document.createElement('a');
        prevLink.className = 'page-link';
        prevLink.href = '#';
        prevLink.innerHTML = '&laquo;';
        prevLink.setAttribute('aria-label', 'Previous');
        
        prevLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (state.currentPage > 1) {
                goToPage(state.currentPage - 1);
            }
        });
        
        prevLi.appendChild(prevLink);
        ul.appendChild(prevLi);
    }
    
    function appendPageItem(ul, pageNumber) {
        const li = document.createElement('li');
        li.className = `page-item ${pageNumber === state.currentPage ? 'active' : ''}`;
        
        const link = document.createElement('a');
        link.className = 'page-link';
        link.href = '#';
        link.textContent = pageNumber;
        
        link.addEventListener('click', (e) => {
            e.preventDefault();
            goToPage(pageNumber);
        });
        
        li.appendChild(link);
        ul.appendChild(li);
    }
    
    function appendEllipsis(ul) {
        const li = document.createElement('li');
        li.className = 'page-item disabled';
        
        const span = document.createElement('span');
        span.className = 'page-link';
        span.textContent = '...';
        
        li.appendChild(span);
        ul.appendChild(li);
    }
    
    function appendNextButton(ul) {
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${state.currentPage === state.totalPages ? 'disabled' : ''}`;
        
        const nextLink = document.createElement('a');
        nextLink.className = 'page-link';
        nextLink.href = '#';
        nextLink.innerHTML = '&raquo;';
        nextLink.setAttribute('aria-label', 'Next');
        
        nextLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (state.currentPage < state.totalPages) {
                goToPage(state.currentPage + 1);
            }
        });
        
        nextLi.appendChild(nextLink);
        ul.appendChild(nextLi);
    }
    
    function goToPage(page) {
        if (page < 1 || page > state.totalPages || page === state.currentPage) return;
        
        state.currentPage = page;
        renderPagination();
        
        if (typeof onPageChange === 'function') {
            onPageChange(page);
        }
    }
    
    renderPagination();
    
    return {
        goToPage,
        getCurrentPage: () => state.currentPage,
        setTotalPages: (totalPages) => {
            state.totalPages = totalPages;
            renderPagination();
        },
        refresh: () => renderPagination()
    };
} 