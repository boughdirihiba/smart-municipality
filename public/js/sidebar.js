document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.querySelector('.sidebar');
  const toggle = document.getElementById('sidebarToggle');
  const toggleIcon = document.getElementById('sidebarToggleIcon');
  if (!sidebar || !toggle) return;

  // initialize from localStorage
  const collapsed = localStorage.getItem('sidebarCollapsed') === '1';
  if (collapsed) {
    sidebar.classList.add('collapsed');
    if (toggleIcon) toggleIcon.textContent = '❯';
  }

  toggle.addEventListener('click', function (e) {
    e.preventDefault();
    const isCollapsed = sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed ? '1' : '0');
    if (toggleIcon) toggleIcon.textContent = isCollapsed ? '❯' : '❮';
  });

  // Defensive: ensure sidebar links navigate even if another layer intercepts clicks
  sidebar.addEventListener('click', function (e) {
    const a = e.target.closest('a.sidebar-link');
    if (!a) return;
    // If the anchor has a valid href, perform navigation explicitly
    const href = a.getAttribute('href');
    if (href && href !== '#') {
      // allow default for ctrl/cmd clicks
      if (e.ctrlKey || e.metaKey || e.shiftKey) return;
      e.preventDefault();
      window.location.href = href;
    }
  });
});
