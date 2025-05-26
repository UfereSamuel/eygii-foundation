    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
            
            // Mobile sidebar toggle
            $('.navbar-toggler').on('click', function() {
                $('#sidebarMenu').toggleClass('show');
            });
            
            // Auto-hide alerts
            $('.alert').each(function() {
                if ($(this).hasClass('alert-success') || $(this).hasClass('alert-info')) {
                    setTimeout(() => {
                        $(this).fadeOut();
                    }, 5000);
                }
            });
            
            // Confirm delete actions
            $('.btn-delete').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
            
            // Form validation
            $('form').on('submit', function() {
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...').prop('disabled', true);
                
                // Re-enable after 10 seconds as fallback
                setTimeout(() => {
                    submitBtn.html(originalText).prop('disabled', false);
                }, 10000);
            });
        });
        
        // Utility functions
        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('.main-content').prepend(alertHtml);
            
            // Auto-hide success/info alerts
            if (type === 'success' || type === 'info') {
                setTimeout(() => {
                    $('.alert').first().fadeOut();
                }, 5000);
            }
        }
        
        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }
        
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-NG', {
                style: 'currency',
                currency: 'NGN'
            }).format(amount);
        }
    </script>
</body>
</html> 