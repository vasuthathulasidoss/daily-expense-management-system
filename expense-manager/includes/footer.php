      </main> <!-- /.page-container -->

      <!-- Footer Info -->
      <footer class="app-footer">
        <div class="footer-inner">
          <p class="copyright">&copy; <?= date('Y') ?> Daily Expense Management System. All rights reserved.</p>
          <div class="footer-links">
            <span>Runs on PHP 8+ & MySQL PDO</span>
            <span class="sep">&middot;</span>
            <span>XAMPP / WAMP Ready</span>
          </div>
        </div>
      </footer>
    </div> <!-- /.app-main -->
  </div> <!-- /.app-layout -->

  <!-- Global Confirmation Modal for Deletes -->
  <div id="confirm-modal" class="modal-backdrop" aria-hidden="true" role="dialog">
    <div class="modal-dialog">
      <div class="modal-header">
        <h3 id="modal-title" class="modal-title">Confirm Deletion</h3>
        <button type="button" class="modal-close" onclick="closeConfirmModal()">&times;</button>
      </div>
      <div class="modal-body">
        <p id="modal-message">Are you sure you want to delete this record? This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
        <form id="modal-confirm-form" method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
          <input type="hidden" id="modal-item-id" name="id" value="">
          <button type="submit" class="btn btn-danger" id="modal-confirm-btn">Yes, Delete</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Application Scripts -->
  <script src="<?= base_url('assets/js/app.js') ?>"></script>
  <script src="<?= base_url('assets/js/charts.js') ?>"></script>
</body>
</html>
