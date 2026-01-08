<?php
include 'config.php';
redirectIfNotLogged();
require_once 'functions.php';

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();

// GESTION CONFIGURATION - Kantite kotizasyon obligatwa
// Pran valè nan tab configuration nan baz done
try {
    $query = "SELECT valeur FROM configuration WHERE utilisateur_id = :user_id AND cle = 'cotisations_requises'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['valeur'] !== null) {
        $nombre_cotisations_requis = intval($result['valeur']);
    } else {
        $nombre_cotisations_requis = 12; // Valeur default si pa genyen
    }
} catch (PDOException $e) {
    $nombre_cotisations_requis = 12; // Valeur default si gen erè
}

// Si gen paramèt nan URL pou chanje configuration
if (isset($_GET['cotisations_requises'])) {
    $nouveau_nombre = intval($_GET['cotisations_requises']);
    if ($nouveau_nombre >= 1 && $nouveau_nombre <= 750) {
        try {
            // Verifye si konfigirasyon an deja egziste
            $query_check = "SELECT id FROM configuration WHERE utilisateur_id = :user_id AND cle = 'cotisations_requises'";
            $stmt_check = $conn->prepare($query_check);
            $stmt_check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_check->execute();
            
            if ($stmt_check->rowCount() > 0) {
                // Mete ajou konfigirasyon ki egziste deja
                $query = "UPDATE configuration SET valeur = :valeur WHERE utilisateur_id = :user_id AND cle = 'cotisations_requises'";
            } else {
                // Kreye nouvo konfigirasyon
                $query = "INSERT INTO configuration (utilisateur_id, cle, valeur) VALUES (:user_id, 'cotisations_requises', :valeur)";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':valeur', $nouveau_nombre);
            
            if ($stmt->execute()) {
                $nombre_cotisations_requis = $nouveau_nombre;
                $success = "Configuration mise à jour: $nouveau_nombre cotisations requises";
            } else {
                $error = "Erreur lors de la sauvegarde de la configuration";
            }
        } catch (PDOException $e) {
            $error = "Erreur base de données: " . $e->getMessage();
        }
    }
}

// Ajouter une cotisation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_cotisation'])) {
    $membre_id = $_POST['membre_id'] ?? null;
    $montant = $_POST['montant'] ?? 0;
    $date_cotisation = $_POST['date_cotisation'] ?? date('Y-m-d');
    
    // Valide done yo
    if (!$membre_id || $montant <= 0) {
        $error = "Membre ak montan obligatwa!";
    } else {
        try {
            // Jwenn dènye nimewo cotisation pou menm manb sa a
            $query_numero = "SELECT MAX(numero_cotisation) as dernier_numero 
                            FROM cotisations 
                            WHERE membre_id = :membre_id";
            $stmt_numero = $conn->prepare($query_numero);
            $stmt_numero->bindParam(':membre_id', $membre_id, PDO::PARAM_INT);
            $stmt_numero->execute();
            $result = $stmt_numero->fetch(PDO::FETCH_ASSOC);
            
            $nouveau_numero = 1;
            if ($result && $result['dernier_numero'] !== null) {
                $nouveau_numero = (int)$result['dernier_numero'] + 1;
            }
            
            // Ajoute nouvel cotisation
            $query = "INSERT INTO cotisations 
                     (numero_cotisation, membre_id, montant, date_cotisation, utilisateur_id) 
                     VALUES (:numero, :membre_id, :montant, :date_cotisation, :user_id)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':numero', $nouveau_numero, PDO::PARAM_INT);
            $stmt->bindParam(':membre_id', $membre_id, PDO::PARAM_INT);
            $stmt->bindParam(':montant', $montant);
            $stmt->bindParam(':date_cotisation', $date_cotisation);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Enregistre transaksyon otomatikman
                $query_membre = "SELECT nom, prenom FROM membres WHERE id = :membre_id";
                $stmt_membre = $conn->prepare($query_membre);
                $stmt_membre->bindParam(':membre_id', $membre_id, PDO::PARAM_INT);
                $stmt_membre->execute();
                $membre = $stmt_membre->fetch(PDO::FETCH_ASSOC);
                
                $description_transaction = "Cotisation #$nouveau_numero - " . 
                                         ($membre['prenom'] ?? '') . " " . ($membre['nom'] ?? '');
                
                $query_transaction = "INSERT INTO transactions 
                                    (type, description, montant, date_transaction, utilisateur_id, membre_id) 
                                    VALUES ('entree', :description, :montant, :date_cotisation, :user_id, :membre_id)";
                
                $stmt_transaction = $conn->prepare($query_transaction);
                $stmt_transaction->bindParam(':description', $description_transaction);
                $stmt_transaction->bindParam(':montant', $montant);
                $stmt_transaction->bindParam(':date_cotisation', $date_cotisation);
                $stmt_transaction->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt_transaction->bindParam(':membre_id', $membre_id, PDO::PARAM_INT);
                $stmt_transaction->execute();
                
                $success = "Cotisation #$nouveau_numero ajoutée avec succès!";
                if (function_exists('logAction')) {
                    logAction($user_id, "Nouvelle cotisation #$nouveau_numero: $montant G", $conn);
                }
            } else {
                $error = "Erreur lors de l'ajout de la cotisation";
            }
        } catch (PDOException $e) {
            $error = "Erreur base de données: " . $e->getMessage();
        }
    }
}

// Récupérer les membres
try {
    $query = "SELECT id, nom, prenom, code_membre FROM membres WHERE utilisateur_id = :user_id ORDER BY nom, prenom";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $membres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $membres = [];
}

// Récupérer les cotisations
try {
    $query = "SELECT c.*, m.nom, m.prenom, m.code_membre 
              FROM cotisations c 
              JOIN membres m ON c.membre_id = m.id 
              WHERE c.utilisateur_id = :user_id 
              ORDER BY c.date_cotisation DESC, c.numero_cotisation DESC
              LIMIT 100";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cotisations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cotisations = [];
}

// Préparer les données pour l'état des cotisations
$etat_cotisations = [];
foreach ($membres as $membre) {
    try {
        $query_count = "SELECT COUNT(*) as total FROM cotisations 
                       WHERE membre_id = :membre_id";
        $stmt_count = $conn->prepare($query_count);
        $stmt_count->bindParam(':membre_id', $membre['id'], PDO::PARAM_INT);
        $stmt_count->execute();
        $count = $stmt_count->fetch(PDO::FETCH_ASSOC);
        
        $etat_cotisations[$membre['id']] = [
            'total' => (int)($count['total'] ?? 0),
            'pourcentage' => $nombre_cotisations_requis > 0 ? 
                            round((($count['total'] ?? 0) / $nombre_cotisations_requis) * 100, 1) : 0
        ];
    } catch (PDOException $e) {
        $etat_cotisations[$membre['id']] = ['total' => 0, 'pourcentage' => 0];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINEX SYSTEM - Gestion des Cotisations</title>
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
            background-color: var(--primary);
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
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

        .user-info {
            color: white;
            text-align: center;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
        .btn-finex:hover {
            background-color: #2980b9;
            color: white;
        }
        .cotisation-case {
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 2px solid #ddd;
            border-radius: 5px;
            text-align: center;
            line-height: 26px;
            margin: 1px;
            font-weight: bold;
            font-size: 0.7em;
        }
        .cotisation-case.paye {
            background-color: #2ecc71;
            color: white;
            border-color: #2ecc71;
        }
        .cotisation-case.non-paye {
            background-color: #e74c3c;
            color: white;
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
                <h2 class="mb-4">Gestion des Cotisations</h2>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-plus-circle me-2"></i>Nouvelle Cotisation
                            </div>
                            <div class="card-body">
                                <form method="POST" id="form-cotisation">
                                    <div class="mb-3">
                                        <label for="membre_id" class="form-label">Membre *</label>
                                        <select class="form-select" id="membre_id" name="membre_id" required>
                                            <option value="">-- Sélectionner un membre --</option>
                                            <?php foreach ($membres as $membre): 
                                                $nb_cotisations = $etat_cotisations[$membre['id']]['total'] ?? 0;
                                            ?>
                                                <option value="<?php echo htmlspecialchars($membre['id']); ?>">
                                                    <?php echo htmlspecialchars($membre['prenom'] . ' ' . $membre['nom'] . ' (' . $membre['code_membre'] . ')'); ?>
                                                    - <?php echo $nb_cotisations; ?> cotisations
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="montant" class="form-label">Montant (G) *</label>
                                        <input type="number" class="form-control" id="montant" name="montant" 
                                               min="0.01" step="0.01" required placeholder="0.00">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="date_cotisation" class="form-label">Date de cotisation *</label>
                                        <input type="date" class="form-control" id="date_cotisation" name="date_cotisation" required>
                                    </div>
                                    
                                    <button type="submit" name="ajouter_cotisation" class="btn btn-finex w-100">
                                        <i class="fas fa-save me-2"></i>Enregistrer Cotisation
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <i class="fas fa-cog me-2"></i>Configuration
                            </div>
                            <div class="card-body">
                                <form method="GET" action="" id="form-config">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre de cotisations requises par membre</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="cotisations_requises" 
                                                   name="cotisations_requises" 
                                                   min="1" max="750" 
                                                   value="<?php echo $nombre_cotisations_requis; ?>"
                                                   required>
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-save me-1"></i> Appliquer
                                            </button>
                                        </div>
                                        <small class="text-muted">Définissez le nombre total de cotisations attendues pour chaque membre</small>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-list me-2"></i>Historique des Cotisations
                                <span class="badge bg-primary float-end"><?php echo count($cotisations); ?></span>
                            </div>
                            <div class="card-body">
                                <?php if (count($cotisations) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Membre</th>
                                                    <th>Montant</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($cotisations as $cotisation): ?>
                                                <tr>
                                                    <td><span class="badge bg-secondary">#<?php echo htmlspecialchars($cotisation['numero_cotisation']); ?></span></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($cotisation['prenom'] . ' ' . $cotisation['nom']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($cotisation['code_membre']); ?></small>
                                                    </td>
                                                    <td class="text-success fw-bold">+ <?php echo number_format($cotisation['montant'], 2); ?> G</td>
                                                    <td>
                                                        <?php 
                                                        $dateFormatted = '';
                                                        if (!empty($cotisation['date_cotisation'])) {
                                                            try {
                                                                $dateFormatted = date('d/m/Y', strtotime($cotisation['date_cotisation']));
                                                            } catch (Exception $e) {
                                                                $dateFormatted = $cotisation['date_cotisation'];
                                                            }
                                                        }
                                                        echo htmlspecialchars($dateFormatted);
                                                        ?>
                                                    </td>
                                                    
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                        <h5>Aucune cotisation enregistrée</h5>
                                        <p class="mb-0">Commencez par ajouter une cotisation</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-2"></i>État des Cotisations
                                <span class="badge bg-info float-end"><?php echo count($membres); ?> membres</span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Objectif: <strong><?php echo $nombre_cotisations_requis; ?> cotisations</strong> par membre
                                        <?php if ($nombre_cotisations_requis != 12): ?>
                                            <span class="badge bg-warning ms-2">Configuré</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if (count($membres) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr class="table-light">
                                                    <th>Membre</th>
                                                    <th>Cotisations</th>
                                                    <th>Progression</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($membres as $membre): 
                                                    $nb_cotisations = $etat_cotisations[$membre['id']]['total'] ?? 0;
                                                    $pourcentage = $etat_cotisations[$membre['id']]['pourcentage'] ?? 0;
                                                    $pourcentage_affichage = min($pourcentage, 100);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($membre['code_membre']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap mb-1">
                                                            <?php for ($i = 1; $i <= $nombre_cotisations_requis; $i++): 
                                                                $is_paye = $i <= $nb_cotisations;
                                                            ?>
                                                                <div class="cotisation-case <?php echo $is_paye ? 'paye' : 'non-paye'; ?>" 
                                                                     title="Cotisation #<?php echo $i; ?>">
                                                                    <?php echo $i; ?>
                                                                </div>
                                                            <?php endfor; ?>
                                                        </div>
                                                        
                                                        <?php if ($nb_cotisations > $nombre_cotisations_requis): ?>
                                                            <div>
                                                                <small class="text-success">
                                                                    <i class="fas fa-plus-circle"></i> +<?php echo ($nb_cotisations - $nombre_cotisations_requis); ?> supplémentaire(s)
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar 
                                                                <?php echo $pourcentage >= 100 ? 'bg-success' : ($pourcentage >= 75 ? 'bg-info' : ($pourcentage >= 50 ? 'bg-warning' : 'bg-danger')); ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo $pourcentage_affichage; ?>%;"
                                                                 aria-valuenow="<?php echo $pourcentage_affichage; ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                                <?php echo number_format($pourcentage, 1); ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $pourcentage >= 100 ? 'bg-success' : ($pourcentage >= 75 ? 'bg-info' : ($pourcentage >= 50 ? 'bg-warning' : 'bg-danger')); ?>">
                                                            <?php echo $nb_cotisations; ?>/<?php echo $nombre_cotisations_requis; ?>
                                                            <?php if ($pourcentage >= 100): ?>
                                                                <i class="fas fa-check ms-1"></i>
                                                            <?php endif; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-3x mb-3"></i><br>
                                        <h5>Aucun membre enregistré</h5>
                                        <p class="mb-0">Ajoutez d'abord des membres pour gérer leurs cotisations</p>
                                    </div>
                                <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.getElementById('date_cotisation');
            if (dateInput && !dateInput.value) {
                dateInput.value = today;
            }
            
            // Valide fòm kotizasyon an
            const formCotisation = document.getElementById('form-cotisation');
            if (formCotisation) {
                formCotisation.addEventListener('submit', function(e) {
                    const montant = document.getElementById('montant');
                    const membre = document.getElementById('membre_id');
                    
                    if (membre && membre.value === "") {
                        e.preventDefault();
                        alert('Veuillez sélectionner un membre');
                        membre.focus();
                        return;
                    }
                    
                    if (montant && (parseFloat(montant.value) <= 0 || isNaN(parseFloat(montant.value)))) {
                        e.preventDefault();
                        alert('Montant doit être supérieur à 0');
                        montant.focus();
                    }
                });
            }
            
            // Valide fòm konfigirasyon an
            const formConfig = document.getElementById('form-config');
            if (formConfig) {
                formConfig.addEventListener('submit', function(e) {
                    const input = document.getElementById('cotisations_requises');
                    if (input) {
                        const valeur = parseInt(input.value);
                        if (valeur < 1 || valeur > 750 || isNaN(valeur)) {
                            e.preventDefault();
                            alert('Veuillez entrer un nombre valide entre 1 et 750');
                            input.focus();
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php 
if (file_exists('footer.php')) {
    include 'footer.php'; 
}
?>