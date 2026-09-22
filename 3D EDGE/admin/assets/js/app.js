/**
 * Shared admin panel behaviour.
 * No framework — plain JS, progressively enhances server-rendered pages.
 */
document.addEventListener('DOMContentLoaded', function () {

  /* Sidebar toggle (mobile) ------------------------------------------ */
  var sidebar = document.querySelector('.admin-sidebar');
  var toggleBtns = document.querySelectorAll('[data-sidebar-toggle]');
  toggleBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
  });
  document.addEventListener('click', function (e) {
    if (!sidebar) return;
    if (sidebar.classList.contains('open') &&
        !sidebar.contains(e.target) &&
        !e.target.closest('[data-sidebar-toggle]')) {
      sidebar.classList.remove('open');
    }
  });

  /* Auto-dismiss flash alerts ----------------------------------------- */
  document.querySelectorAll('.flash-alert').forEach(function (alertEl) {
    setTimeout(function () {
      alertEl.style.transition = 'opacity 0.3s ease';
      alertEl.style.opacity = '0';
      setTimeout(function () { alertEl.remove(); }, 300);
    }, 5000);
  });

  /* Generic delete-confirmation modal ---------------------------------
     Any element with data-confirm-delete="Form CSS selector"
     and data-confirm-name="Record name" triggers a shared modal
     instead of submitting immediately. ------------------------------ */
  var confirmModalEl = document.getElementById('confirmDeleteModal');
  var confirmModal = confirmModalEl ? new bootstrap.Modal(confirmModalEl) : null;
  var pendingForm = null;

  document.querySelectorAll('[data-confirm-delete]').forEach(function (trigger) {
    trigger.addEventListener('click', function (e) {
      e.preventDefault();
      var formSelector = trigger.getAttribute('data-confirm-delete');
      var name = trigger.getAttribute('data-confirm-name') || 'this record';
      pendingForm = document.querySelector(formSelector);
      var nameEl = document.getElementById('confirmDeleteName');
      if (nameEl) nameEl.textContent = name;
      if (confirmModal) confirmModal.show();
    });
  });

  var confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', function () {
      if (pendingForm) pendingForm.submit();
    });
  }

  /* Live client-side search/filter submit on change -------------------- */
  document.querySelectorAll('[data-auto-submit]').forEach(function (el) {
    el.addEventListener('change', function () {
      el.closest('form').submit();
    });
  });

  /* Image input preview -------------------------------------------------- */
  document.querySelectorAll('[data-image-preview]').forEach(function (input) {
    input.addEventListener('change', function () {
      var targetId = input.getAttribute('data-image-preview');
      var target = document.getElementById(targetId);
      if (!target || !input.files || !input.files[0]) return;
      var reader = new FileReader();
      reader.onload = function (e) { target.src = e.target.result; target.style.display = 'block'; };
      reader.readAsDataURL(input.files[0]);
    });
  });

});
