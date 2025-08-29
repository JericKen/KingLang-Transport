// Get elements
const sidebar = document.getElementById('sidebar');
const content = document.getElementById('content');
const toggleBtn = document.getElementById('toggleBtn');
const toggleIcon = toggleBtn ? toggleBtn.querySelector('i') : null;

// Function to save sidebar state to localStorage and apply HTML classes
function saveSidebarState(isCollapsed) {
    localStorage.setItem('sidebarCollapsed', isCollapsed);
    
    // Update the root HTML classes for immediate effect
    if (isCollapsed === true || isCollapsed === 'true') {
        document.documentElement.classList.add('sidebar-collapsed');
        document.documentElement.classList.remove('sidebar-expanded');
    } else {
        document.documentElement.classList.add('sidebar-expanded');
        document.documentElement.classList.remove('sidebar-collapsed');
    }
}

// Function to toggle sidebar state
function toggleSidebar() {
    // Check current state from HTML element
    const isCurrentlyCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
    
    // Set the opposite state
    saveSidebarState(!isCurrentlyCollapsed);
    
    // Update sidebar and content classes for JS-based effects
    if (!isCurrentlyCollapsed) {
        // Collapsing
        sidebar.classList.add('collapsed');
        sidebar.classList.remove('expanded');
        if (content) content.classList.add('collapsed');
        if (toggleIcon) {
            toggleIcon.classList.remove('bi-chevron-left');
            toggleIcon.classList.add('bi-chevron-right');
        }
    } else {
        // Expanding
        sidebar.classList.remove('collapsed');
        sidebar.classList.add('expanded');
        if (content) content.classList.remove('collapsed');
        if (toggleIcon) {
            toggleIcon.classList.add('bi-chevron-left');
            toggleIcon.classList.remove('bi-chevron-right');
        }
    }
}

// Add toggle event listener
if (toggleBtn) {
    toggleBtn.addEventListener('click', toggleSidebar);
}

// Handle window resize
function checkWidth() {
    if (!sidebar) return;
    
    if (window.innerWidth <= 768) {
        // Always collapse on mobile
        saveSidebarState(true);
        sidebar.classList.add('collapsed');
        sidebar.classList.remove('expanded');
        if (content) content.classList.add('collapsed');
        if (toggleIcon) {
            toggleIcon.classList.remove('bi-chevron-left');
            toggleIcon.classList.add('bi-chevron-right');
        }
    }
}

// Set up tooltips
document.addEventListener("DOMContentLoaded", function() {
    // Set up Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Sync element classes with HTML root class
    const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        sidebar.classList.remove('expanded');
        if (content) content.classList.add('collapsed');
        if (toggleIcon) {
            toggleIcon.classList.remove('bi-chevron-left');
            toggleIcon.classList.add('bi-chevron-right');
        }
    } else {
        sidebar.classList.remove('collapsed');
        sidebar.classList.add('expanded');
        if (content) content.classList.remove('collapsed');
        if (toggleIcon) {
            toggleIcon.classList.add('bi-chevron-left');
            toggleIcon.classList.remove('bi-chevron-right');
        }
    }
});

// Handle window resize
window.addEventListener('resize', checkWidth);