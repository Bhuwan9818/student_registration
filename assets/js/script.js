document.addEventListener('DOMContentLoaded', function () {
  // ---- Sidebar drawer (mobile/tablet) ----
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebarBackdrop');

  function closeSidebar() {
    sidebar?.classList.remove('show');
    backdrop?.classList.remove('show');
  }

  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('show');
      backdrop?.classList.toggle('show');
    });
  }
  backdrop?.addEventListener('click', closeSidebar);

  // ---- Fee entry type toggle (manual vs upload) on submit_fee.php ----
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

  // ---- Bulk select checkboxes on admin_students.php ----
  const selectAll = document.getElementById('selectAllRows');
  const rowChecks = document.querySelectorAll('.row-check');
  const bulkBar = document.getElementById('bulkActionBar');
  const bulkCount = document.getElementById('bulkCount');

  function refreshBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked').length;
    if (bulkBar) {
      bulkBar.classList.toggle('d-none', checked === 0);
      if (bulkCount) bulkCount.textContent = checked;
    }
  }

  if (selectAll) {
    selectAll.addEventListener('change', function () {
      rowChecks.forEach(cb => cb.checked = selectAll.checked);
      refreshBulkBar();
    });
  }
  rowChecks.forEach(cb => cb.addEventListener('change', refreshBulkBar));
});
