<?php
include 'config.php';
redirectIfNotLogged();

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();

// Ajouter une transaction
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_transaction'])) {
    $type = $_POST['type'];
    $description = $_POST['description'];
    $montant = $_POST['montant'];
    $date_transaction = $_POST['date_transaction'];
    $membre_id = $_POST['membre_id'] ?? null;
    
    $query = "INSERT INTO transactions (type, description, montant, date_transaction, utilisateur_id, membre_id) 
              VALUES (:type, :description, :montant, :date_transaction, :utilisateur_id, :membre_id)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':montant', $montant);
    $stmt->bindParam(':date_transaction', $date_transaction);
    $stmt->bindParam(':utilisateur_id', $user_id);
    $stmt->bindParam(':membre_id', $membre_id);
    
    if ($stmt->execute()) {
        $success = "Transaction ajoutée avec succès!";
    } else {
        $error = "Erreur lors de l'ajout de la transaction";
    }
}

// Ajouter un salaire
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_salaire'])) {
    $nom_employe = $_POST['nom_employe'];
    $mois = $_POST['mois'];
    $montant = $_POST['montant_salaire'];
    $statut = $_POST['statut'];
    
    $query = "INSERT INTO salaires (nom_employe, mois, montant, statut, utilisateur_id) 
              VALUES (:nom_employe, :mois, :montant, :statut, :utilisateur_id)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nom_employe', $nom_employe);
    $stmt->bindParam(':mois', $mois);
    $stmt->bindParam(':montant', $montant);
    $stmt->bindParam(':statut', $statut);
    $stmt->bindParam(':utilisateur_id', $user_id);
    
    if ($stmt->execute()) {
        $success_salaire = "Salaire ajouté avec succès!";
    } else {
        $error_salaire = "Erreur lors de l'ajout du salaire";
    }
}

// Récupérer les transactions
$query = "SELECT t.*, m.nom, m.prenom 
          FROM transactions t 
          LEFT JOIN membres m ON t.membre_id = m.id 
          WHERE t.utilisateur_id = :user_id 
          ORDER BY t.date_transaction DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les salaires
$query = "SELECT * FROM salaires WHERE utilisateur_id = :user_id ORDER BY mois DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$salaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les membres pour les selects
$query = "SELECT id, nom, prenom FROM membres WHERE utilisateur_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$membres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINEX SYSTEM - Finance</title>
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
        .sidebar {
            background-color: #041e64ff;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }
        .user-info {
            color: white;
            text-align: center;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--secondary);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <h2 class="mb-4">Dépenses & Entrées</h2>

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#transactions">Transactions</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#salaires">Salaires</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Transactions Tab -->
                            <div class="tab-pane fade show active" id="transactions">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h5>Ajouter une transaction</h5>
                                        
                                        <?php if (isset($success)): ?>
                                            <div class="alert alert-success"><?php echo $success; ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($error)): ?>
                                            <div class="alert alert-danger"><?php echo $error; ?></div>
                                        <?php endif; ?>
                                        
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="date_transaction" class="form-label">Date</label>
                                                <input type="date" class="form-control" id="date_transaction" name="date_transaction" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="type" class="form-label">Type</label>
                                                <select class="form-select" id="type" name="type" required>
                                                    <option value="entree">Entrée</option>
                                                    <option value="depense">Dépense</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="membre_id" class="form-label">Membre (optionnel)</label>
                                                <select class="form-select" id="membre_id" name="membre_id">
                                                    <option value="">-- Sélectionner un membre --</option>
                                                    <?php foreach ($membres as $membre): ?>
                                                        <option value="<?php echo $membre['id']; ?>">
                                                            <?php echo $membre['prenom'] . ' ' . $membre['nom']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <input type="text" class="form-control" id="description" name="description" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="montant" class="form-label">Montant (G)</label>
                                                <input type="number" class="form-control" id="montant" name="montant" min="0" step="0.01" required>
                                            </div>
                                            <button type="submit" name="ajouter_transaction" class="btn btn-finex">Ajouter Transaction</button>
                                        </form>
                                    </div>
                                    <div class="col-md-8">
                                        <h5>Historique des Transactions</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Description</th>
                                                        <th>Type</th>
                                                        <th>Montant</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($transactions as $transaction): ?>
                                                    <tr>
                                                        <td><?php echo $transaction['date_transaction']; ?></td>
                                                        <td>
                                                            <?php echo $transaction['description']; ?>
                                                            <?php if ($transaction['membre_id']): ?>
                                                                <br><small class="text-muted"><?php echo $transaction['prenom'] . ' ' . $transaction['nom']; ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $transaction['type'] == 'entree' ? 'bg-success' : 'bg-danger'; ?>">
                                                                <?php echo $transaction['type']; ?>
                                                            </span>
                                                        </td>
                                                        <td class="<?php echo $transaction['type'] == 'entree' ? 'text-success' : 'text-danger'; ?>">
                                                            <?php echo $transaction['type'] == 'entree' ? '+' : '-'; ?>
                                                            <?php echo number_format($transaction['montant'], 2); ?> G
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Salaires Tab -->
                            <div class="tab-pane fade" id="salaires">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Ajouter un salaire</h5>
                                        
                                        <?php if (isset($success_salaire)): ?>
                                            <div class="alert alert-success"><?php echo $success_salaire; ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($error_salaire)): ?>
                                            <div class="alert alert-danger"><?php echo $error_salaire; ?></div>
                                        <?php endif; ?>
                                        
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="nom_employe" class="form-label">Nom de l'employé</label>
                                                <input type="text" class="form-control" id="nom_employe" name="nom_employe" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="mois" class="form-label">Mois</label>
                                                <input type="text" class="form-control" id="mois" name="mois" placeholder="ex: Novembre 2025" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="montant_salaire" class="form-label">Montant (G)</label>
                                                <input type="number" class="form-control" id="montant_salaire" name="montant_salaire" min="0" step="0.01" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="statut" class="form-label">Statut</label>
                                                <select class="form-select" id="statut" name="statut" required>
                                                    <option value="non_paye">Non Payé</option>
                                                    <option value="paye">Payé</option>
                                                </select>
                                            </div>
                                            <button type="submit" name="ajouter_salaire" class="btn btn-finex">Ajouter Salaire</button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Suivi des Salaires</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Employé</th>
                                                        <th>Mois</th>
                                                        <th>Montant</th>
                                                        <th>Statut</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($salaires as $salaire): ?>
                                                    <tr>
                                                        <td><?php echo $salaire['nom_employe']; ?></td>
                                                        <td><?php echo $salaire['mois']; ?></td>
                                                        <td><?php echo number_format($salaire['montant'], 2); ?> G</td>
                                                        <td>
                                                            <span class="badge <?php echo $salaire['statut'] == 'paye' ? 'bg-success' : 'bg-warning'; ?>">
                                                                <?php echo $salaire['statut'] == 'paye' ? 'Payé' : 'Non Payé'; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Définir la date d'aujourd'hui par défaut
        document.getElementById('date_transaction').value = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>