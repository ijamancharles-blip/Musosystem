<?php
include 'config.php';
redirectIfNotLogged();
redirectIfNotAdmin();

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();

// Récupérer les statistiques globales
$stats = [];

$query = "
    SELECT t.id, t.user_id, t.plan_type, t.transaction_number, t.created_at,
           u.nom, u.prenom
    FROM subscriptions t
    JOIN utilisateurs u ON t.user_id = u.id
";
$stmt = $conn->prepare($query);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Total des utilisateurs
$query = "SELECT COUNT(*) as total FROM utilisateurs";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['total_utilisateurs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Utilisateurs actifs
$query = "SELECT COUNT(*) as total FROM utilisateurs WHERE is_active = 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['utilisateurs_actifs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];




// Récupérer tous les utilisateurs avec leurs statistiques
$query = "SELECT u.*, 
                 COUNT(DISTINCT m.id) as nb_membres,
                 COUNT(DISTINCT t.id) as nb_transactions,
                 COUNT(DISTINCT p.id) as nb_prets,
                 COALESCE(SUM(m.plan), 0) as total_plan
          FROM utilisateurs u 
          LEFT JOIN membres m ON u.id = m.utilisateur_id 
          LEFT JOIN transactions t ON u.id = t.utilisateur_id 
          LEFT JOIN prets p ON u.id = p.utilisateur_id 
          GROUP BY u.id 
          ORDER BY u.date_creation DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "FINEX SYSTEM - Admin Dashboard";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #041e64ff;
            --secondary: #3498db;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-admin {
            background: linear-gradient(135deg, #041e64ff 0%, #000000ff 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
            font-weight: 500;
        }
        
        .main-content {
            padding: 30px;
            margin-left: 0;
        }
        
        .page-title {
            color: #041e64ff;
            font-weight: 500;
            margin-bottom: 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #ffffffff 0%, #f6fafdff 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 25px;
            border: none;
        }
        
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: black;
        }
        
        .table th {
            background-color: #000105ff;
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f3f4;
        }
        
        .avatar-sm {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            color: white;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, #313131ff 0%, #000000ff 100%);
            border: none;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(4, 30, 100, 0.3);
            color: white;
        }
        
        .alert-finex {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .user-actions .btn {
            border-radius: 6px;
            margin: 2px;
        }
        
        .badge-admin {
            font-size: 0.75rem;
            padding: 6px 10px;
            border-radius: 20px;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-admin navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-crown me-2"></i>FINEX ADMIN
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="avatar-sm bg-warning me-2">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span>Administrateur</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="admin_profil.php">
                            <i class="fas fa-user me-2"></i>Mon Profil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title">
                <i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord Administrateur
            </h2>
            <div class="badge bg-warning text-dark fs-6 px-3 py-2">
                <i class="fas fa-shield-alt me-1"></i>Super Administrateur
            </div>
        </div>

        <!-- Messages de succès/erreur -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-finex">
                <i class="fas fa-check-circle me-2"></i>
                <?php
                switch ($_GET['success']) {
                    case 'utilisateur_ajoute': echo "Utilisateur créé avec succès!"; break;
                    case 'utilisateur_modifie': echo "Utilisateur modifié avec succès!"; break;
                    case 'utilisateur_supprime': echo "Utilisateur supprimé avec succès!"; break;
                    case 'utilisateur_bloque': echo "Utilisateur bloqué avec succès!"; break;
                    case 'utilisateur_debloque': echo "Utilisateur débloqué avec succès!"; break;
                    default: echo "Action réussie!";
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-finex">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php
                switch ($_GET['error']) {
                    case 'email_existe': echo "Cet email est déjà utilisé!"; break;
                    case 'champs_manquants': echo "Veuillez remplir tous les champs obligatoires!"; break;
                    case 'password_trop_court': echo "Le mot de passe doit contenir au moins 6 caractères!"; break;
                    case 'auto_suppression': echo "Vous ne pouvez pas supprimer votre propre compte!"; break;
                    case 'auto_blocage': echo "Vous ne pouvez pas bloquer votre propre compte!"; break;
                    default: echo "Une erreur est survenue!";
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Statistiques Globales -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="stat-card bg-primary text-white">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total_utilisateurs']; ?></div>
                        <div class="stat-label">Utilisateurs Total</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="stat-card bg-success text-white">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['utilisateurs_actifs']; ?></div>
                        <div class="stat-label">Utilisateurs Actifs</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-table">
                        <table class="table table-hover">
                            <th>id</th>
                            <th>id Utilisateurs</th>
                            <th>Plan</th>
                            <th>numero transaction</th>
                            <th>date</th>
                            <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?= htmlspecialchars($transaction['id']) ?></td>
                                <td><?= htmlspecialchars($transaction['user_id']) ?></td>
                                <td><?= htmlspecialchars($transaction['plan_type']) ?></td>
                                <td><?= htmlspecialchars($transaction['transaction_number']) ?></td>
                                <td><?= htmlspecialchars((new DateTime($transaction['created_at']))->format('d/m/Y') ) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

        <div class="row">
            <!-- Gestion des Utilisateurs -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-users-cog me-2"></i>Gestion des Utilisateurs
                        </h5>
                        <div>
                            <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#ajouterUserModal">
                                <i class="fas fa-plus me-1"></i>Nouvel Utilisateur
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($utilisateurs)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">Aucun utilisateur trouvé</h4>
                                <p class="text-muted">Commencez par créer le premier utilisateur.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Utilisateur</th>
                                            <th>Contact</th>
                                            <th>Date Création</th>
                                            <th>Statistiques</th>
                                            <th>Statut</th>
                                            <th width="150">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($utilisateurs as $utilisateur): ?>
                                        
                                        <tr>
                                            <td>
                                            <?= htmlspecialchars($utilisateur['id']) ?>
                                        </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary me-3">
                                                        <?php echo strtoupper(substr($utilisateur['prenom'], 0, 1) . substr($utilisateur['nom'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></strong>
                                                        <?php if ($utilisateur['id'] == $user_id): ?>
                                                            <br><span class="badge bg-info badge-admin">Vous</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div><i class="fas fa-envelope me-1 text-muted"></i> <?php echo htmlspecialchars($utilisateur['email']); ?></div>
                                                <?php if ($utilisateur['telephone']): ?>
                                                    <div><i class="fas fa-phone me-1 text-muted"></i> <?php echo htmlspecialchars($utilisateur['telephone']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($utilisateur['date_creation'])); ?>
                                                <br><small class="text-muted"><?php echo date('H:i', strtotime($utilisateur['date_creation'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <span class="badge bg-primary badge-admin" title="Membres">
                                                        <i class="fas fa-users me-1"></i><?php echo $utilisateur['nb_membres']; ?>
                                                    </span>
                                                    <span class="badge bg-info badge-admin" title="Transactions">
                                                        <i class="fas fa-exchange-alt me-1"></i><?php echo $utilisateur['nb_transactions']; ?>
                                                    </span>
                                                    <span class="badge bg-warning text-dark badge-admin" title="Prêts">
                                                        <i class="fas fa-hand-holding-usd me-1"></i><?php echo $utilisateur['nb_prets']; ?>
                                                    </span>
                                                    <span class="badge bg-success badge-admin" title="Capital">
                                                        <?php echo number_format($utilisateur['total_plan'], 0); ?> G
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($utilisateur['is_admin']): ?>
                                                    <span class="badge bg-warning text-dark badge-admin">
                                                        <i class="fas fa-crown me-1"></i>Admin
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary badge-admin">
                                                        <i class="fas fa-user me-1"></i>Utilisateur
                                                    </span>
                                                <?php endif; ?>
                                                <br>
                                                <?php if ($utilisateur['is_active'] == 1): ?>
                                                    <span class="badge bg-success badge-admin mt-1">
                                                        <i class="fas fa-check me-1"></i>Actif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger badge-admin mt-1">
                                                        <i class="fas fa-ban me-1"></i>Bloqué
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="user-actions">
                                                    <!-- Modifier -->
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modifierUserModal"
                                                            data-user-id="<?php echo $utilisateur['id']; ?>"
                                                            data-user-nom="<?php echo htmlspecialchars($utilisateur['nom']); ?>"
                                                            data-user-prenom="<?php echo htmlspecialchars($utilisateur['prenom']); ?>"
                                                            data-user-email="<?php echo htmlspecialchars($utilisateur['email']); ?>"
                                                            data-user-telephone="<?php echo htmlspecialchars($utilisateur['telephone'] ?? ''); ?>"
                                                            data-user-adresse="<?php echo htmlspecialchars($utilisateur['adresse'] ?? ''); ?>"
                                                            data-user-sexe="<?php echo $utilisateur['sexe']; ?>"
                                                            data-user-admin="<?php echo $utilisateur['is_admin']; ?>"
                                                            data-user-active="<?php echo $utilisateur['is_active']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <?php if ($utilisateur['id'] != $user_id): ?>
                                                        <!-- Bloquer/Débloquer -->
                                                        <?php if ($utilisateur['is_active'] == 1): ?>
                                                            <form method="POST" action="admin_actions.php" class="d-inline">
                                                                <input type="hidden" name="action" value="bloquer_utilisateur">
                                                                <input type="hidden" name="user_id" value="<?php echo $utilisateur['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                                        onclick="return confirm('Bloquer cet utilisateur ? Il ne pourra plus se connecter.')">
                                                                    <i class="fas fa-lock"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" action="admin_actions.php" class="d-inline">
                                                                <input type="hidden" name="action" value="debloquer_utilisateur">
                                                                <input type="hidden" name="user_id" value="<?php echo $utilisateur['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                        onclick="return confirm('Débloquer cet utilisateur ?')">
                                                                    <i class="fas fa-unlock"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Supprimer -->
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#supprimerUserModal"
                                                                data-user-id="<?php echo $utilisateur['id']; ?>"
                                                                data-user-nom="<?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include 'admin_modals.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function exportData() {
        if (confirm('Exporter toutes les données du système ?')) {
            window.location.href = 'admin_export.php';
        }
    }

  
    // Tooltips pour les boutons
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
    </script>
</body>
</html>