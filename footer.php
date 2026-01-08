<?php
// footer.php - Pied de page commun
?>
    <!-- Footer -->
    <footer style="background-color: #000000ff; color: white; margin-top: 3rem;">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 text-center py-3">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> MUSO. Tous droits réservés.</p>
                    <small>Gestion Mutuelle moderne</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personnalisés -->
    <script>
        // Tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Popovers Bootstrap
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });
        
        // Confirmation avant suppression
        function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
            return confirm(message);
        }
        
        // Formatage automatique des montants
        document.addEventListener('DOMContentLoaded', function() {
            const montantInputs = document.querySelectorAll('input[type="number"]');
            montantInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value) {
                        this.value = parseFloat(this.value).toFixed(2);
                    }
                });
            });
        });
        
        // Auto-soumission des formulaires avec loading
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = showLoading(submitBtn);
                        
                        // Réactiver le bouton après 10s maximum
                        setTimeout(() => {
                            hideLoading(submitBtn, originalText);
                        }, 10000);
                    }
                });
            });
        });
    </script>
</body>
</html>