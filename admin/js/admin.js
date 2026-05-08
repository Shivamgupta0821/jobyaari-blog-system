// Sidebar toggle for mobile
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
  document.querySelector('.admin-sidebar')?.classList.toggle('open');
});

// Close sidebar on outside click (mobile)
document.addEventListener('click', function(e) {
  const sidebar = document.querySelector('.admin-sidebar');
  const toggle  = document.getElementById('sidebarToggle');
  if (sidebar && toggle && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
    sidebar.classList.remove('open');
  }
});
