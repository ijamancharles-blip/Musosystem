<?php
include 'config.php';
redirectIfNotLogged();
include 'functions.php';

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();

// Ajouter un prêt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_pret'])) {
    $membre_id = $_POST['membre_id'];
    $montant = $_POST['montant'];
    $taux_interet = $_POST['taux_interet'];
    $duree_mois = $_POST['duree_mois'];
    $date_pret = $_POST['date_pret'];
    
    // Calculer la date d'échéance et le montant à rembourser
    $date_echeance = date('Y-m-d', strtotime($date_pret . " + $duree_mois months"));
    $montant_a_rembourser = $montant + ($montant * $taux_interet / 100);
    
    $query = "INSERT INTO prets (membre_id, montant, taux_interet, duree_mois, date_pret, date_echeance, montant_a_rembourser, utilisateur_id) 
              VALUES (:membre_id, :montant, :taux_interet, :duree_mois, :date_pret, :date_echeance, :montant_a_rembourser, :utilisateur_id)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':membre_id', $membre_id);
    $stmt->bindParam(':montant', $montant);
    $stmt->bindParam(':taux_interet', $taux_interet);
    $stmt->bindParam(':duree_mois', $duree_mois);
    $stmt->bindParam(':date_pret', $date_pret);
    $stmt->bindParam(':date_echeance', $date_echeance);
    $stmt->bindParam(':montant_a_rembourser', $montant_a_rembourser);
    $stmt->bindParam(':utilisateur_id', $user_id);
    
    if ($stmt->execute()) {
        $success = "Prêt ajouté avec succès!";
        logAction($user_id, "Nouveau prêt de $montant G", $conn);
    } else {
        $error = "Erreur lors de l'ajout du prêt";
    }
}

// Rembourser un prêt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rembourser_pret'])) {
    $pret_id = $_POST['pret_id'];
    $montant_rembourse = $_POST['montant_rembourse'];
    
    $query = "UPDATE prets SET statut = 'rembourse' WHERE id = :pret_id AND utilisateur_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':pret_id', $pret_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        // Enregistrer la transaction de remboursement
        $query_transaction = "INSERT INTO transactions (type, description, montant, date_transaction, utilisateur_id) 
                             VALUES ('entree', 'Remboursement de prêt', :montant, CURDATE(), :user_id)";
        $stmt_transaction = $conn->prepare($query_transaction);
        $stmt_transaction->bindParam(':montant', $montant_rembourse);
        $stmt_transaction->bindParam(':user_id', $user_id);
        $stmt_transaction->execute();
        
        $success = "Prêt marqué comme remboursé!";
        logAction($user_id, "Remboursement prêt: $montant_rembourse G", $conn);
    } else {
        $error = "Erreur lors du remboursement du prêt";
    }
}

// Récupérer les membres pour le select
$query = "SELECT id, nom, prenom, code_membre FROM membres WHERE utilisateur_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$membres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les prêts en cours
$query = "SELECT p.*, m.nom, m.prenom, m.code_membre 
          FROM prets p 
          JOIN membres m ON p.membre_id = m.id 
          WHERE p.utilisateur_id = :user_id AND p.statut = 'en_cours'
          ORDER BY p.date_pret DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$prets_en_cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les prêts remboursés
$query = "SELECT p.*, m.nom, m.prenom, m.code_membre 
          FROM prets p 
          JOIN membres m ON p.membre_id = m.id 
          WHERE p.utilisateur_id = :user_id AND p.statut = 'rembourse'
          ORDER BY p.date_pret DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$prets_rembourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques des prêts
$query_stats = "SELECT 
    COUNT(*) as total_prets,
    SUM(montant) as total_prete,
    SUM(montant_a_rembourser) as total_a_rembourser
    FROM prets 
    WHERE utilisateur_id = :user_id";
$stmt_stats = $conn->prepare($query_stats);
$stmt_stats->bindParam(':user_id', $user_id);
$stmt_stats->execute();
$stats_prets = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINEX SYSTEM - Gestion des Prêts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .btn-finex {
            background-color: #3498db;
            color: white;
            border: none;
        }
        .stat-card {
            padding: 15px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }
        .stat-card.primary { background: linear-gradient(45deg, #3498db, #2980b9); }
        .stat-card.success { background: linear-gradient(45deg, #2ecc71, #27ae60); }
        .stat-card.warning { background: linear-gradient(45deg, #f39c12, #e67e22); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <h2 class="mb-4">Gestion des Prêts</h2>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card primary">
                            <h4><?php echo $stats_prets['total_prets'] ?? 0; ?></h4>
                            <p>Total Prêts</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card success">
                            <h4><?php echo number_format($stats_prets['total_prete'] ?? 0, 2); ?> G</h4>
                            <p>Total Prêté</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card warning">
                            <h4><?php echo number_format($stats_prets['total_a_rembourser'] ?? 0, 2); ?> G</h4>
                            <p>À Rembourser</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(45deg, #9b59b6, #8e44ad);">
                            <h4><?php echo count($prets_rembourses); ?></h4>
                            <p>Prêts Remboursés</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#nouveau-pret">Nouveau Prêt</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#prets-en-cours">Prêts en Cours</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#prets-rembourses">Prêts Remboursés</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Nouveau Prêt -->
                            <div class="tab-pane fade show active" id="nouveau-pret">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Nouveau Prêt</h5>
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="membre_id" class="form-label">Membre</label>
                                                <select class="form-select" id="membre_id" name="membre_id" required>
                                                    <option value="">-- Sélectionner un membre --</option>
                                                    <?php foreach ($membres as $membre): ?>
                                                        <option value="<?php echo $membre['id']; ?>">
                                                            <?php echo $membre['prenom'] . ' ' . $membre['nom'] . ' (' . $membre['code_membre'] . ')'; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="montant" class="form-label">Montant (G)</label>
                                                <input type="number" class="form-control" id="montant" name="montant" min="0" step="0.01" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="taux_interet" class="form-label">Taux d'intérêt (%)</label>
                                                <input type="number" class="form-control" id="taux_interet" name="taux_interet" min="0" max="100" step="0.1" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="duree_mois" class="form-label">Durée (mois)</label>
                                                <input type="number" class="form-control" id="duree_mois" name="duree_mois" min="1" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="date_pret" class="form-label">Date du prêt</label>
                                                <input type="date" class="form-control" id="date_pret" name="date_pret" required>
                                            </div>
                                            <button type="submit" name="ajouter_pret" class="btn btn-finex">Enregistrer Prêt</button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Calcul du Remboursement</h5>
                                        <div class="border p-3 rounded">
                                            <div id="calcul-remboursement">
                                                <p class="text-muted">Remplissez le formulaire pour voir le calcul</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Prêts en Cours -->
                            <div class="tab-pane fade" id="prets-en-cours">
                                <h5>Prêts en Cours</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Membre</th>
                                                <th>Montant</th>
                                                <th>Taux</th>
                                                <th>Date Prêt</th>
                                                <th>Échéance</th>
                                                <th>À Rembourser</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($prets_en_cours) > 0): ?>
                                                <?php foreach ($prets_en_cours as $pret): ?>
                                                <tr>
                                                    <td>
                                                        <?php echo $pret['prenom'] . ' ' . $pret['nom']; ?>
                                                        <br><small class="text-muted"><?php echo $pret['code_membre']; ?></small>
                                                    </td>
                                                    <td><?php echo number_format($pret['montant'], 2); ?> G</td>
                                                    <td><?php echo $pret['taux_interet']; ?>%</td>
                                                    <td><?php echo $pret['date_pret']; ?></td>
                                                    <td><?php echo $pret['date_echeance']; ?></td>
                                                    <td class="text-success"><?php echo number_format($pret['montant_a_rembourser'], 2); ?> G</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#rembourserModal<?php echo $pret['id']; ?>">
                                                            Rembourser
                                                        </button>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Modal de remboursement -->
                                                <div class="modal fade" id="rembourserModal<?php echo $pret['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Rembourser le prêt</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <p>Membre: <strong><?php echo $pret['prenom'] . ' ' . $pret['nom']; ?></strong></p>
                                                                    <p>Montant à rembourser: <strong><?php echo number_format($pret['montant_a_rembourser'], 2); ?> G</strong></p>
                                                                    <input type="hidden" name="pret_id" value="<?php echo $pret['id']; ?>">
                                                                    <input type="hidden" name="montant_rembourse" value="<?php echo $pret['montant_a_rembourser']; ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Confirmer le remboursement</label>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" required>
                                                                            <label class="form-check-label">Je confirme le remboursement complet</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                    <button type="submit" name="rembourser_pret" class="btn btn-success">Confirmer Remboursement</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">Aucun prêt en cours</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Prêts Remboursés -->
                            <div class="tab-pane fade" id="prets-rembourses">
                                <h5>Prêts Remboursés</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Membre</th>
                                                <th>Montant</th>
                                                <th>Taux</th>
                                                <th>Date Prêt</th>
                                                <th>Date Échéance</th>
                                                <th>Montant Remboursé</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($prets_rembourses) > 0): ?>
                                                <?php foreach ($prets_rembourses as $pret): ?>
                                                <tr>
                                                    <td>
                                                        <?php echo $pret['prenom'] . ' ' . $pret['nom']; ?>
                                                        <br><small class="text-muted"><?php echo $pret['code_membre']; ?></small>
                                                    </td>
                                                    <td><?php echo number_format($pret['montant'], 2); ?> G</td>
                                                    <td><?php echo $pret['taux_interet']; ?>%</td>
                                                    <td><?php echo $pret['date_pret']; ?></td>
                                                    <td><?php echo $pret['date_echeance']; ?></td>
                                                    <td class="text-success"><?php echo number_format($pret['montant_a_rembourser'], 2); ?> G</td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">Aucun prêt remboursé</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Définir la date d'aujourd'hui par défaut
        document.getElementById('date_pret').value = new Date().toISOString().split('T')[0];
        
        // Calcul du remboursement en temps réel
        function calculerRemboursement() {
            const montant = parseFloat(document.getElementById('montant').value) || 0;
            const taux = parseFloat(document.getElementById('taux_interet').value) || 0;
            const duree = parseInt(document.getElementById('duree_mois').value) || 0;
            
            if (montant > 0 && taux > 0 && duree > 0) {
                const interet = montant * (taux / 100);
                const totalARembourser = montant + interet;
                const mensualite = totalARembourser / duree;
                
                document.getElementById('calcul-remboursement').innerHTML = `
                    <p><strong>Montant du prêt:</strong> ${montant.toFixed(2)} G</p>
                    <p><strong>Intérêt total:</strong> ${interet.toFixed(2)} G</p>
                    <p><strong>Total à rembourser:</strong> ${totalARembourser.toFixed(2)} G</p>
                    <p><strong>Mensualité estimée:</strong> ${mensualite.toFixed(2)} G/mois</p>
                `;
            }
        }
        
        // Écouter les changements dans les champs de calcul
        document.getElementById('montant').addEventListener('input', calculerRemboursement);
        document.getElementById('taux_interet').addEventListener('input', calculerRemboursement);
        document.getElementById('duree_mois').addEventListener('input', calculerRemboursement);
    </script>
</body>
</html>