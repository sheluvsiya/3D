    </div><!-- /.admin-content -->
  </div><!-- /.admin-main -->
</div><!-- /.admin-shell -->

<!-- Shared delete-confirmation modal, reused by every management page -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body pt-4 px-4">
        <h5 class="mb-2">Delete this record?</h5>
        <p class="text-muted mb-0">
          This will permanently remove <strong id="confirmDeleteName">this record</strong>. This can't be undone.
        </p>
      </div>
      <div class="modal-footer border-0 px-4 pb-4">
        <button type="button" class="btn btn-3de-outline btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn" data-bs-dismiss="modal">Delete</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/admin/assets/js/app.js"></script>
</body>
</html>
