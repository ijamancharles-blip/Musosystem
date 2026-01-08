<!-- Modal Ajouter Utilisateur -->
<div class="modal fade" id="ajouterUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Nouvel Utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="admin_actions.php" id="formAjouterUser">
                <div class="modal-body">
                    <input type="hidden" name="action" value="ajouter_utilisateur">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nom" name="nom" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="prenom" name="prenom" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" id="telephone" name="telephone" placeholder="+509 ...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea class="form-control" id="adresse" name="adresse" rows="2" placeholder="Adresse complète..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sexe" class="form-label">Sexe</label>
                                <select class="form-select" id="sexe" name="sexe">
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                <div class="form-text">Minimum 6 caractères</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1">
                                    <label class="form-check-label fw-bold" for="is_admin">
                                        <i class="fas fa-crown me-1 text-warning"></i>Accès Administrateur
                                    </label>
                                </div>
                                <div class="form-text">Peut accéder au panel d'administration</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label fw-bold" for="is_active">
                                        <i class="fas fa-user-check me-1 text-success"></i>Compte Actif
                                    </label>
                                </div>
                                <div class="form-text">Peut se connecter au système</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-finex">
                        <i class="fas fa-save me-1"></i>Créer l'utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier Utilisateur -->
<div class="modal fade" id="modifierUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Modifier l'Utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="admin_actions.php" id="formModifierUser">
                <div class="modal-body">
                    <input type="hidden" name="action" value="modifier_utilisateur">
                    <input type="hidden" id="modifier_user_id" name="user_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modifier_nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modifier_nom" name="nom" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modifier_prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modifier_prenom" name="prenom" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modifier_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="modifier_email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modifier_telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" id="modifier_telephone" name="telephone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modifier_adresse" class="form-label">Adresse</label>
                        <textarea class="form-control" id="modifier_adresse" name="adresse" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modifier_sexe" class="form-label">Sexe</label>
                                <select class="form-select" id="modifier_sexe" name="sexe">
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modifier_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="modifier_password" name="password" minlength="6">
                                <div class="form-text">Laissez vide pour ne pas modifier</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modifier_is_admin" name="is_admin" value="1">
                                    <label class="form-check-label fw-bold" for="modifier_is_admin">
                                        <i class="fas fa-crown me-1 text-warning"></i>Accès Administrateur
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="modifier_is_active" name="is_active" value="1">
                                    <label class="form-check-label fw-bold" for="modifier_is_active">
                                        <i class="fas fa-user-check me-1 text-success"></i>Compte Actif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-finex">
                        <i class="fas fa-save me-1"></i>Modifier l'utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Supprimer Utilisateur -->
<div class="modal fade" id="supprimerUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Confirmation de Suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="admin_actions.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="supprimer_utilisateur">
                    <input type="hidden" id="supprimer_user_id" name="user_id">
                    
                    <div class="text-center mb-4">
                        <i class="fas fa-trash fa-3x text-danger mb-3"></i>
                        <h5>Êtes-vous sûr de vouloir supprimer cet utilisateur ?</h5>
                        <p class="mb-0">Utilisateur: <strong id="supprimer_user_nom" class="text-danger"></strong></p>
                    </div>
                    
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Attention! Cette action est irréversible.</strong><br>
                        Toutes les données associées seront supprimées:
                        <ul class="mb-0 mt-2">
                            <li>Membres et leurs plans</li>
                            <li>Transactions et historique</li>
                            <li>Prêts et cotisations</li>
                            <li>Toutes les données financières</li>
                        </ul>
                    </div>
                    
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="confirm_suppression" required>
                        <label class="form-check-label fw-bold" for="confirm_suppression">
                            Je confirme la suppression définitive
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmSuppression" disabled>
                        <i class="fas fa-trash me-1"></i>Supprimer Définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Gestion des modals
document.addEventListener('DOMContentLoaded', function() {
    // Modal de modification
    const modifierModal = document.getElementById('modifierUserModal');
    if (modifierModal) {
        modifierModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userNom = button.getAttribute('data-user-nom');
            const userPrenom = button.getAttribute('data-user-prenom');
            const userEmail = button.getAttribute('data-user-email');
            const userTelephone = button.getAttribute('data-user-telephone');
            const userAdresse = button.getAttribute('data-user-adresse');
            const userSexe = button.getAttribute('data-user-sexe');
            const userAdmin = button.getAttribute('data-user-admin') === '1';
            const userActive = button.getAttribute('data-user-active') === '1';
            
            document.getElementById('modifier_user_id').value = userId;
            document.getElementById('modifier_nom').value = userNom;
            document.getElementById('modifier_prenom').value = userPrenom;
            document.getElementById('modifier_email').value = userEmail;
            document.getElementById('modifier_telephone').value = userTelephone || '';
            document.getElementById('modifier_adresse').value = userAdresse || '';
            document.getElementById('modifier_sexe').value = userSexe || 'M';
            document.getElementById('modifier_is_admin').checked = userAdmin;
            document.getElementById('modifier_is_active').checked = userActive;
        });
    }
    
    // Modal de suppression
    const supprimerModal = document.getElementById('supprimerUserModal');
    if (supprimerModal) {
        supprimerModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userNom = button.getAttribute('data-user-nom');
            
            document.getElementById('supprimer_user_id').value = userId;
            document.getElementById('supprimer_user_nom').textContent = userNom;
        });
        
        // Activer/désactiver le bouton de suppression
        const confirmCheckbox = document.getElementById('confirm_suppression');
        const btnConfirm = document.getElementById('btnConfirmSuppression');
        
        if (confirmCheckbox && btnConfirm) {
            confirmCheckbox.addEventListener('change', function() {
                btnConfirm.disabled = !this.checked;
            });
        }
    }

    // Validation des formulaires
    const formAjouter = document.getElementById('formAjouterUser');
    if (formAjouter) {
        formAjouter.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            if (password.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                return false;
            }
        });
    }
});
</script>