// ============================================
// THEME SYSTEM
// ============================================

// Theme Toggle Button
const themeToggle = document.getElementById('themeToggle');

// Load Saved Theme
function initTheme() {

    const savedTheme = localStorage.getItem('crm_theme');

    if (savedTheme === 'dark') {

        document.body.classList.add('dark-theme');

        updateThemeIcon(true);
    }
}

// Toggle Theme
function toggleTheme() {

    document.body.classList.toggle('dark-theme');

    const isDark =
        document.body.classList.contains('dark-theme');

    // Save Theme Preference
    localStorage.setItem(
        'crm_theme',
        isDark ? 'dark' : 'light'
    );

    // Update Icon
    updateThemeIcon(isDark);
}

// Change Theme Icon
function updateThemeIcon(isDark) {

    if (!themeToggle) return;

    const icon = themeToggle.querySelector('i');

    icon.className = isDark
        ? 'fas fa-sun'
        : 'fas fa-moon';
}

// ============================================
    // EVENT LISTENER
// ============================================

themeToggle.addEventListener(
    'click',
    toggleTheme
);

// ============================================
// INITIALIZE THEME
// ============================================

document.addEventListener(
    'DOMContentLoaded',
    initTheme
);



// ============================================
// TOAST NOTIFICATION SYSTEM
// ============================================

// Show Notification
function showNotification(type, message) {

    // Toast Container
    const container =
        document.getElementById('toastContainer');

    // Stop if container does not exist
    if (!container) return;

    // Create Toast
    const toast = document.createElement('div');

    // Add Classes
    toast.className = `toast ${type}`;

    // Icons
    let icon = '';

    switch(type) {

        case 'success':
            icon = '<i class="fas fa-check-circle"></i>';
            break;

        case 'error':
            icon = '<i class="fas fa-exclamation-circle"></i>';
            break;

        case 'info':
            icon = '<i class="fas fa-info-circle"></i>';
            break;

        default:
            icon = '<i class="fas fa-bell"></i>';
    }

    // Toast HTML
    toast.innerHTML = `
        <div class="toast-icon">
            ${icon}
        </div>

        <div class="toast-message">
            ${message}
        </div>

        <button class="toast-close">
            &times;
        </button>
    `;

    // Add Toast to Container
    container.appendChild(toast);

    // Show Animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Auto Remove
    const autoRemove = setTimeout(() => {
        removeToast(toast);
    }, 4000);

    // Close Button
    toast
        .querySelector('.toast-close')
        .addEventListener('click', () => {

            clearTimeout(autoRemove);

            removeToast(toast);
        });
}

// ============================================
// REMOVE TOAST
// ============================================

function removeToast(toast) {

    toast.classList.remove('show');

    setTimeout(() => {

        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }

    }, 300);
}


// ============================================
// Sidebar Collapse / Expand
// ============================================

// Sidebar Collapse / Expand
// ============================================

document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
    const toggleBtn = document.getElementById("sidebarToggleBtn");

    // Toggle Sidebar Function
    function toggleSidebar() {
        sidebar.classList.toggle("collapsed");
        mainContent.classList.toggle("collapsed-layout");

        // Optional: Save sidebar state in localStorage
        const isCollapsed = sidebar.classList.contains("collapsed");
        localStorage.setItem("sidebarCollapsed", isCollapsed);
    }

    // Button Click Event
    toggleBtn.addEventListener("click", toggleSidebar);

    // Restore Saved State on Page Load
    const savedState = localStorage.getItem("sidebarCollapsed");

    if (savedState === "true") {
        sidebar.classList.add("collapsed");
        mainContent.classList.add("collapsed-layout");
    }
});

// ============================================
// User Management Dropdown
// ============================================

function initNavigation() {

    // User Management dropdown
    const usersMainBtn = document.getElementById('sidebarUsersLink');
    const usersSubmenu = document.getElementById('usersSubmenu');

    if (usersMainBtn && usersSubmenu) {

        usersMainBtn.addEventListener('click', (e) => {
            e.preventDefault();

            const caret = usersMainBtn.querySelector('.submenu-caret');

            // Open / Close submenu
            usersSubmenu.classList.toggle('open');

            const isOpen = usersSubmenu.classList.contains('open');

            if (isOpen) {

                if (caret) {
                    caret.style.transform = 'rotate(180deg)';
                }

            } else {

                if (caret) {
                    caret.style.transform = 'rotate(0deg)';
                }
            }
        });
    }

    // Main navigation handling
    navItems.forEach(item => {

        item.addEventListener('click', (e) => {

            // Ignore dropdown parent button
            if (item.id === 'sidebarUsersLink') return;

            e.preventDefault();

            navItems.forEach(nav => nav.classList.remove('active'));

            item.classList.add('active');

            const sectionId = item.getAttribute('data-section');

            contentSections.forEach(section => {
                section.classList.remove('active');
            });

            const targetedSec = document.getElementById(sectionId);

            if (targetedSec) {
                targetedSec.classList.add('active');
            }

            // User management section switching
            if (sectionId === 'users') {

                const subView = item.getAttribute('data-view');

                const formCard = document.getElementById('addUserFormCard');
                const listCard = document.getElementById('allUsersListCard');
                const gridWrapper = document.getElementById('userManagementGridWrapper');

                if (formCard && listCard && gridWrapper) {

                    if (subView === 'all') {

                        formCard.style.display = 'none';
                        listCard.style.display = 'block';
                        gridWrapper.style.gridTemplateColumns = '1fr';

                    } else if (subView === 'add') {

                        formCard.style.display = 'block';
                        listCard.style.display = 'none';
                        gridWrapper.style.gridTemplateColumns = '1fr';
                    }
                }
            }

            refreshSectionData(sectionId);
        });
    });

    // Top profile shortcut
    const topNavWidget = document.getElementById('topNavUserProfile');

    if (topNavWidget) {

        topNavWidget.addEventListener('click', () => {

            const profileLink = document.getElementById('sidebarProfileLink');

            if (profileLink) {
                profileLink.click();
            }
        });
    }
}

// ============================================
// App Startup
// ============================================

function initApp() {

    initTheme();

    // User dropdown navigation
    initNavigation();

    initEventListeners();

    addDummyData();

    checkAuthentication();
}

document.addEventListener('DOMContentLoaded', initApp);