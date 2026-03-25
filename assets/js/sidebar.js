document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    
    const universalToggleBtn = document.getElementById('openSidebarUniversal');
    const legacyToggleBtn = document.getElementById('openSidebar'); 
    
    const closeBtn = document.getElementById('closeSidebar');
    const desktopToggleBtn = document.getElementById('desktopSidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    // Desktop Collapse functionality
    if (desktopToggleBtn) {
        desktopToggleBtn.addEventListener('click', () => {
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');
                
                // Always show burger icon
                const icon = desktopToggleBtn.querySelector('i');
                icon.className = 'ri-menu-line';
            }
        });
    }

    // Mobile Open/Close functionality
    function openSidebar() {
        if (sidebar) sidebar.classList.add('active');
        if (overlay) overlay.classList.add('active');
        document.body.classList.add('sidebar-open');
        document.body.style.overflow = 'hidden'; 
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        document.body.style.overflow = '';
    }

    if (universalToggleBtn) {
        universalToggleBtn.addEventListener('click', openSidebar);
    }

    if (legacyToggleBtn) {
        legacyToggleBtn.addEventListener('click', openSidebar);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    
    // Profile Dropdown Toggle Global
    const profileToggle = document.getElementById('profileToggle');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileToggle && profileDropdown) {
        profileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!profileDropdown.contains(e.target) && !profileToggle.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });
    }

    // Resize Handle
    window.addEventListener('resize', function () {
        if (window.innerWidth > 1024) {
             document.body.classList.remove('sidebar-open');
             document.body.style.overflow = '';
             
             // Always show burger icon
             const icon = desktopToggleBtn?.querySelector('i');
             if (icon) icon.className = 'ri-menu-line';
        }
    });
});