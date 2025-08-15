const toggleButton = document.querySelector('.toggle-button');
const sidebar = document.querySelector('.sidebar');
const content = document.querySelector('.content');

toggleButton.addEventListener('click', () => {
    sidebar.classList.toggle('open'); // For smaller screens
    toggleButton.classList.toggle('active');
    // For larger screens, you might toggle 'collapsed' on sidebar and 'sidebar-collapsed' on content
    if (window.innerWidth > 768) {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('sidebar-collapsed');
    }
});

// You might also want to handle resizing to ensure the correct state
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        sidebar.classList.remove('open');
        toggleButton.classList.remove('active');
        // Ensure expanded state on larger screens
        sidebar.classList.remove('collapsed');
        content.classList.remove('sidebar-collapsed');
    } else {
        // Ensure collapsed/off-screen state on smaller screens initially
        if (!sidebar.classList.contains('open')) {
            // Optionally set initial state for small screens
        }
    }
});