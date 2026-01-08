<?php
include 'config.php';
redirectIfNotLogged();

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();

// Récupérer les statistiques des prêts
$query = "SELECT 
            COUNT(*) as total_prets,
            SUM(montant) as total_prete,
            SUM(montant_a_rembourser) as total_a_rembourser
          FROM prets 
          WHERE utilisateur_id = :user_id AND statut = 'en_cours'";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$stats_prets = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les prêts remboursés
$query = "SELECT 
            COUNT(*) as total_prets_rembourses,
            SUM(montant) as total_rembourse
          FROM prets 
          WHERE utilisateur_id = :user_id AND statut = 'rembourse'";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$stats_rembourses = $stmt->fetch(PDO::FETCH_ASSOC);

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

// Récupérer les données pour le graphique des tendances (6 derniers mois)
$tendances_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $start_date = date('Y-m-01', strtotime($month));
    $end_date = date('Y-m-t', strtotime($month));
    
    // Entrées du mois
    $query = "SELECT SUM(montant) as total FROM transactions 
              WHERE type = 'entree' AND date_transaction BETWEEN :start_date AND :end_date 
              AND utilisateur_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $entrees = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Dépenses du mois
    $query = "SELECT SUM(montant) as total FROM transactions 
              WHERE type = 'depense' AND date_transaction BETWEEN :start_date AND :end_date 
              AND utilisateur_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $depenses = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $tendances_data[] = [
        'mois' => date('M Y', strtotime($month)),
        'entrees' => $entrees,
        'depenses' => $depenses
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINEX SYSTEM - Rapports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
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
                <h2 class="mb-4">Rapports Avancés</h2>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                Synthèse des Prêts en Cours
                            </div>
                            <div class="card-body text-center">
                                <h4 class="text-primary"><?php echo number_format($stats_prets['total_prete'] ?? 0, 2); ?> GDES</h4>
                                <p>Total Prêté</p>
                                <canvas id="pretsChart" height="50"></canvas>
                                <div class="mt-3">
                                    <small class="text-muted"><?php echo $stats_prets['total_prets'] ?? 0; ?> prêts en cours</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                Synthèse des Prêts Remboursés
                            </div>
                            <div class="card-body text-center">
                                <h4 class="text-success"><?php echo number_format($stats_rembourses['total_rembourse'] ?? 0, 2); ?> GDES</h4>
                                <p>Total Remboursé</p>
                                <canvas id="remboursementsChart" height="50"></canvas>
                                <div class="mt-3">
                                    <small class="text-muted"><?php echo $stats_rembourses['total_prets_rembourses'] ?? 0; ?> prêts remboursés</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        Prêts en Cours
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Membre (Code)</th>
                                        <th>Montant (GDES)</th>
                                        <th>Date Prêt</th>
                                        <th>Échéance</th>
                                        <th>À Rembourser (GDES)</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($prets_en_cours) > 0): ?>
                                        <?php foreach ($prets_en_cours as $pret): ?>
                                        <tr>
                                            <td><?php echo $pret['prenom'] . ' ' . $pret['nom']; ?><br><small class="text-muted"><?php echo $pret['code_membre']; ?></small></td>
                                            <td><?php echo number_format($pret['montant'], 2); ?> GDES</td>
                                            <td><?php echo $pret['date_pret']; ?></td>
                                            <td><?php echo $pret['date_echeance']; ?></td>
                                            <td><?php echo number_format($pret['montant_a_rembourser'], 2); ?> GDES</td>
                                            <td>
                                                <span class="badge bg-warning">En cours</span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Aucun prêt en cours</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        Tendances Financières (6 derniers mois)
                    </div>
                    <div class="card-body">
                        <canvas id="tendancesChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique des prêts
        const ctxPrets = document.getElementById('pretsChart').getContext('2d');
        const pretsChart = new Chart(ctxPrets, {
            type: 'doughnut',
            data: {
                labels: ['Prêts Personnels', 'Prêts Business', 'Prêts Immobiliers'],
                datasets: [{
                    data: [40, 35, 25],
                    backgroundColor: ['#3498db', '#2ecc71', '#f39c12']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Graphique des remboursements
        const ctxRemboursements = document.getElementById('remboursementsChart').getContext('2d');
        const remboursementsChart = new Chart(ctxRemboursements, {
            type: 'doughnut',
            data: {
                labels: ['Complètement', 'Partiellement', 'En retard'],
                datasets: [{
                    data: [60, 30, 10],
                    backgroundColor: ['#2ecc71', '#f39c12', '#e74c3c']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Graphique des tendances
        const ctxTendances = document.getElementById('tendancesChart').getContext('2d');
        const tendancesChart = new Chart(ctxTendances, {
            type: 'bar',
            data: {
                labels: [<?php echo '"' . implode('","', array_column($tendances_data, 'mois')) . '"'; ?>],
                datasets: [{
                    label: 'Entrées',
                    data: [<?php echo implode(',', array_column($tendances_data, 'entrees')); ?>],
                    backgroundColor: '#06f43dff'
                }, {
                    label: 'Dépenses',
                    data: [<?php echo implode(',', array_column($tendances_data, 'depenses')); ?>],
                    backgroundColor: '#f94301ff'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php include 'footer.php'; ?>