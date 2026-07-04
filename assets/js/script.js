document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('show');
    });
  }

  // Fee entry type toggle (manual vs upload) on submit_fee.php
  const entryRadios = document.querySelectorAll('input[name="entry_type"]');
  const manualBlock = document.getElementById('manualBlock');
  const uploadBlock = document.getElementById('uploadBlock');
  if (entryRadios.length && manualBlock && uploadBlock) {
    entryRadios.forEach(r => r.addEventListener('change', function () {
      if (this.value === 'manual') {
        manualBlock.classList.remove('d-none');
        uploadBlock.classList.add('d-none');
      } else {
        uploadBlock.classList.remove('d-none');
        manualBlock.classList.add('d-none');
      }
    }));
  }
});
