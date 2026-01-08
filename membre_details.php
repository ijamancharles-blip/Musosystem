<?php
include 'config.php';
redirectIfNotLogged();
require_once 'functions.php'; 

if (!isset($_GET['id'])) {
    header("Location: membres.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();
$membre_id = $_GET['id'];

// Récupérer les informations du membre
$query = "SELECT * FROM membres WHERE id = :membre_id AND utilisateur_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':membre_id', $membre_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$membre = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$membre) {
    header("Location: membres.php");
    exit();
}

// GET CONFIGURATION - Jwenn kantite kotizasyon obligatwa nan tab configuration
$nombre_cotisations_requis = getConfiguration('cotisations_requises', 12, $user_id);
$nombre_cotisations_requis = intval($nombre_cotisations_requis);

// Récupérer les transactions du membre
$query = "SELECT * FROM transactions WHERE membre_id = :membre_id AND utilisateur_id = :user_id ORDER BY date_transaction DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':membre_id', $membre_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les cotisations du membre
$query = "SELECT * FROM cotisations 
          WHERE membre_id = :membre_id 
          AND utilisateur_id = :user_id 
          ORDER BY numero_cotisation ASC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':membre_id', $membre_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$cotisations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kont kotizasyon pou manb sa a
$nombre_cotisations_membre = count($cotisations);

// Récupérer les informations de l'utilisateur (sol) pour l'en-tête
$query_user = "SELECT * FROM utilisateurs WHERE id = :user_id";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bindParam(':user_id', $user_id);
$stmt_user->execute();
$utilisateur = $stmt_user->fetch(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUZZO - Détails Membre</title>
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
        .cotisation-case {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 2px solid #ddd;
            border-radius: 5px;
            text-align: center;
            line-height: 36px;
            margin: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .cotisation-case.active {
            background-color: #2ecc71;
            color: white;
            border-color: #2ecc71;
        }
        
        /* Styles pour l'impression */
        @media print {
            .no-print {
                display: none !important;
            }
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
            .card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
            }
            .table {
                border: 1px solid #000 !important;
            }
            .table th, .table td {
                border: 1px solid #000 !important;
            }
        }
        
        .print-header {
            display: none;
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
                <!-- En-tête pour l'impression -->
                <div class="print-header">
                    <h2><?php echo $utilisateur['nom_sol'] ?? ''; ?></h2>
                    <p><strong>Adresse:</strong> <?php echo $utilisateur['adresse'] ?? ''; ?></p>
                    <p><strong>Téléphone:</strong> <?php echo $utilisateur['telephone'] ?? ''; ?></p>
                    <p><strong>Email:</strong> <?php echo $utilisateur['email'] ?? ''; ?></p>
                    <h3>Rapport des Cotisations - <?php echo $membre['prenom'] . ' ' . $membre['nom']; ?></h3>
                    <p><strong>Code Membre:</strong> <?php echo $membre['code_membre']; ?></p>
                    <hr>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard Membre: <?php echo $membre['prenom'] . ' ' . $membre['nom']; ?> (Code: <?php echo $membre['code_membre']; ?>)</h2>
                    <div>
                        <button class="btn btn-success me-2 no-print" onclick="imprimerCotisations()">
                            <i class="fas fa-print me-1"></i> Imprimer Cotisations
                        </button>
                        <a href="membres.php" class="btn btn-outline-secondary no-print">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-info-circle me-2"></i>Informations Personnelles
                            </div>
                            <div class="card-body">
                                <p><strong>Nom:</strong> <?php echo $membre['nom']; ?></p>
                                <p><strong>Prénom:</strong> <?php echo $membre['prenom']; ?></p>
                                <p><strong>Téléphone:</strong> <?php echo $membre['telephone'] ?: 'Non renseigné'; ?></p>
                                <p><strong>Email:</strong> <?php echo $membre['email'] ?: 'Non renseigné'; ?></p>
                                <p><strong>Adresse:</strong> <?php echo $membre['adresse'] ?: 'Non renseignée'; ?></p>
                                <p><strong>Sexe:</strong> <?php echo $membre['sexe'] == 'M' ? 'Masculin' : 'Féminin'; ?></p>
                                <p><strong>Date d'entrée:</strong> <?php echo $membre['date_entree']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-chart-pie me-2"></i>Plan & Rentabilité
                                </div>
                                
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="border p-3 text-center rounded">
                                            <h5>Plan</h5>
                                            <h4 class="text-primary"><?php echo number_format($membre['plan'], 2); ?> G</h4>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="border p-3 text-center rounded">
                                            <h5>Cotisations Actives</h5>
                                            <h4 class="text-warning"><?php echo $nombre_cotisations_membre; ?>/<?php echo $nombre_cotisations_requis; ?></h4>
                                        </div>
                                    </div>
                                </div>
                                
                                <h6>Cotisations Actives (sur <?php echo $nombre_cotisations_requis; ?> requises)</h6>
                                <div class="mb-3">
                                    <?php for ($i = 1; $i <= $nombre_cotisations_requis; $i++): 
                                        $is_active = false;
                                        foreach ($cotisations as $cotisation) {
                                            if ($cotisation['numero_cotisation'] == $i) {
                                                $is_active = true;
                                                break;
                                            }
                                        }
                                    ?>
                                        <div class="cotisation-case <?php echo $is_active ? 'active' : ''; ?>" title="Cotisation #<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nombre de Cotisations Actives:</strong> 
                                            <?php echo $nombre_cotisations_membre; ?> / <?php echo $nombre_cotisations_requis; ?> 
                                            (<?php echo $nombre_cotisations_requis > 0 ? round(($nombre_cotisations_membre / $nombre_cotisations_requis) * 100, 1) : 0; ?>%)
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Solde Total du Membre:</strong> 
                                            <?php 
                                            $solde = 0;
                                            foreach ($transactions as $transaction) {
                                                if ($transaction['type'] == 'entree') {
                                                    $solde += $transaction['montant'];
                                                } else {
                                                    $solde -= $transaction['montant'];
                                                }
                                            }
                                            echo number_format($solde, 2); ?> G
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                

                <!-- Section pour l'impression des cotisations -->
                <div class="card mt-4 no-print">
                    <div class="card-header">
                        <i class="fas fa-list me-2"></i>Détail des Cotisations
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date de Cotisation</th>
                                        <th>Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($cotisations) > 0): ?>
                                        <?php foreach ($cotisations as $cotisation): ?>
                                        <tr>
                                            <td>#<?php echo $cotisation['numero_cotisation']; ?></td>
                                            <td><?php echo $cotisation['date_cotisation']; ?></td>
                                            <td><?php echo number_format($cotisation['montant'], 2); ?> G</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Aucune cotisation trouvée</td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
function imprimerCotisations() {
    // Créer une nouvelle fenêtre pour l'impression
    let fenetreImpression = window.open('', '_blank');
    
    // Contenu HTML pour l'impression
    let contenuImpression = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Rapport des Cotisations - <?php echo $membre['prenom'] . ' ' . $membre['nom']; ?></title>
            <style>
                @page {
                    margin: 15mm;
                }
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0;
                    padding: 0;
                    font-size: 12px;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #000;
                }
                .logo-container {
                    text-align: center;
                    margin-bottom: 10px;
                }
                .logo {
                    max-height: 40px;
                    width: auto;
                }
                .sol-name {
                    font-size: 35px;
                    font-weight: bold;
                    color: #005653;
                    text-transform: uppercase;
                    margin: 10px 0 5px 0;
                }
                .contact-info {
                    font-size: 11px;
                    margin: 3px 0;
                }
                .report-title {
                    font-size: 18px;
                    font-weight: bold;
                    margin: 15px 0;
                    text-decoration: underline;
                }
                .info-membre { 
                    margin: 20px 0; 
                    padding: 15px;
                    border: 1px solid #000;
                    border-radius: 5px;
                    background-color: #f9f9f9;
                }
                .membre-info-row {
                    display: flex;
                    margin-bottom: 5px;
                }
                .membre-label {
                    font-weight: bold;
                    min-width: 150px;
                }
                
                .summary {
                    margin: 25px 0;
                    padding: 15px;
                    background-color: #f9f9f9;
                    font-size: 13px;
                    page-break-inside: avoid;
                }
                .summary-item {
                    display: flex;
                    justify-content: space-between;
                    margin: 5px 0;
                }
                .summary-label {
                    font-weight: bold;
                }
                
                /* Styles pour le tableau des 10 dernières cotisations */
                .dernieres-cotisations {
                    margin: 30px 0;
                    page-break-inside: avoid;
                }
                .dernieres-cotisations h3 {
                    text-align: center;
                    font-size: 16px;
                    margin-bottom: 15px;
                    text-decoration: underline;
                    color: #041e64ff;
                }
                .cotisations-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 0 auto;
                    font-size: 12px;
                }
                .cotisations-table th {
                    background-color: #005653;
                    color: white;
                    padding: 10px;
                    text-align: center;
                    font-weight: bold;
                    border: 1px solid #ddd;
                }
                .cotisations-table td {
                    padding: 10px;
                    border: 1px solid #ddd;
                    text-align: center;
                }
                .cotisations-table tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .cotisations-table tr:hover {
                    background-color: #f5f5f5;
                }
                .montant {
                    font-weight: bold;
                    color: #2e7d32;
                }
                .no-cotisations {
                    text-align: center;
                    font-style: italic;
                    color: #666;
                    padding: 20px;
                    border: 1px solid #ddd;
                    background-color: #f9f9f9;
                }
                
                .footer { 
                    margin-top: 30px; 
                    text-align: center; 
                    font-size: 10px;
                    color: #666;
                    border-top: 1px solid #ccc;
                    padding-top: 10px;
                    page-break-inside: avoid;
                }
                @media print {
                    .cotisations-table th {
                        background-color: #005653 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-container">
                    <img src="Assets/images/declinaison 1 muso sans fond.png" alt="Logo MUSO" class="logo">
                </div>
                <div class="sol-name"><?php echo $utilisateur['nom_sol'] ?? ''; ?></div>
                
                <div class="report-title">
                    RAPPORT DES COTISATIONS
                </div>
            </div>
            
            <div class="info-membre">
                <div class="membre-info-row">
                    <div class="membre-label">Nom & Prénom:</div>
                    <div><?php echo $membre['prenom'] . ' ' . $membre['nom']; ?></div>
                </div>
                <div class="membre-info-row">
                    <div class="membre-label">Code Membre:</div>
                    <div><?php echo $membre['code_membre']; ?></div>
                </div>
                <div class="membre-info-row">
                    <div class="membre-label">Téléphone:</div>
                    <div><?php echo $membre['telephone'] ?: 'Non renseigné'; ?></div>
                </div>
                <div class="membre-info-row">
                    <div class="membre-label">Date d'entrée:</div>
                    <div><?php echo $membre['date_entree']; ?></div>
                </div>
                <div class="membre-info-row">
                    <div class="membre-label">Objectif cotisations:</div>
                    <div><?php echo $nombre_cotisations_requis; ?></div>
                </div>
            </div>
            
            <?php 
            $totalCotisations = 0;
            $nombre_cotisations_membre = count($cotisations);
            
            // Obtenir seulement les 10 dernières cotisations
            $dernieres_cotisations = array_slice($cotisations, -10, 10, true);
            $dernieres_cotisations = array_reverse($dernieres_cotisations); // Plus récent en premier
            
            if (count($cotisations) > 0): 
                foreach ($cotisations as $cotisation): 
                    $totalCotisations += $cotisation['montant'];
                endforeach;
            endif;
            ?>
            
            <div class="dernieres-cotisations">
                <h3>10 DERNIÈRES COTISATIONS</h3>
                
                <?php if (count($dernieres_cotisations) > 0): ?>
                    <table class="cotisations-table">
                        <thead>
                            <tr>
                                <th>N° Cotisation</th>
                                <th>Date de Cotisation</th>
                                <th>Montant (GDES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($dernieres_cotisations as $cotisation): 
                            ?>
                                <tr>
                                    <!-- Montre numero_cotisation soti nan SQL -->
                                    <td><?php echo $cotisation['numero_cotisation']; ?></td>

                                    <!-- Fòma dat -->
                                    <td><?php echo date('d/m/Y', strtotime($cotisation['date_cotisation'])); ?></td>

                                    <!-- Fòma montan -->
                                    <td class="montant"><?php echo number_format($cotisation['montant'], 2); ?></td>
                                </tr>
                            <?php 
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-cotisations">
                        Aucune cotisation enregistrée pour ce membre.
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="summary">
                <div class="summary-item">
                    <span class="summary-label">Total cotisations payées:</span>
                    <span><?php echo number_format($totalCotisations, 2); ?> G</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Nombre de cotisations:</span>
                    <span><?php echo $nombre_cotisations_membre; ?> / <?php echo $nombre_cotisations_requis; ?></span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Date du rapport:</span>
                    <span><?php echo date('d/m/Y'); ?></span>
                </div>
            </div>
            
            <div class="footer">
                <p>Document généré le <?php echo date('d/m/Y à H:i'); ?> - MUSO </p>
            </div>
        </body>
        </html>
    `;
    
    // Écrire le contenu dans la nouvelle fenêtre et lancer l'impression
    fenetreImpression.document.write(contenuImpression);
    fenetreImpression.document.close();
    fenetreImpression.focus();
    
    // Attendre que le contenu soit chargé avant d'imprimer
    fenetreImpression.onload = function() {
        fenetreImpression.print();
        // fenetreImpression.close(); // Optionnel: fermer la fenêtre après impression
    };
}
</script>
</body>
</html>