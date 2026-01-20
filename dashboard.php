<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();

// Récupérer les informations de l'utilisateur (nom_sol, etc.)
$query_user = "SELECT nom_sol, email, is_admin FROM utilisateurs WHERE id = :user_id";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bindParam(':user_id', $user_id);
$stmt_user->execute();
$user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

$is_admin = $user_info['is_admin'] ?? false;
$nom_sol = $user_info['nom_sol'] ?? '';
$user_email = $user_info['email'] ?? '';

// Stocker dans la session pour usage futur
$_SESSION['nom_sol'] = $nom_sol;
$_SESSION['user_email'] = $user_email;

// Récupérer les statistiques
$stats = [];

// Nombre de membres
$query = "SELECT COUNT(*) as total FROM membres WHERE utilisateur_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$stats['membres'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Capital brut (somme des plans des membres)
// 1. Total transactions type 'entre'
// Total entrées
$query = "SELECT COALESCE(SUM(montant), 0) AS total_entree
          FROM transactions
          WHERE utilisateur_id = :user_id
          AND type = 'entree'";

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$total_entree = $stmt->fetch(PDO::FETCH_ASSOC)['total_entree'];


// Total dépenses
$query = "SELECT COALESCE(SUM(montant), 0) AS total_depense
          FROM transactions
          WHERE utilisateur_id = :user_id
          AND type = 'depense'";

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$total_depense = $stmt->fetch(PDO::FETCH_ASSOC)['total_depense'];


// Capital brut FINAL
$stats['capital'] = $total_entree - $total_depense;

// Dépenses (si tu l’affiches séparément)
$stats['depenses'] = $total_depense;


// Entrées du jour
$today = date('Y-m-d');
$query = "SELECT SUM(montant) as total FROM transactions 
          WHERE type = 'entree' AND date_transaction = :today AND utilisateur_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':today', $today);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$stats['entrees_jour'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Données pour le graphique des entrées (7 derniers jours)
$entrees_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = "SELECT SUM(montant) as total FROM transactions 
              WHERE type = 'entree' AND date_transaction = :date AND utilisateur_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $entrees_data[] = [
        'date' => $date,
        'total' => $total,
        'label' => date('D', strtotime($date))
    ];
}

// Dernières transactions
$query = "SELECT t.*, m.nom, m.prenom 
          FROM transactions t 
          LEFT JOIN membres m ON t.membre_id = m.id 
          WHERE t.utilisateur_id = :user_id 
          ORDER BY t.date_transaction DESC, t.id DESC 
          LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$dernieres_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSO - Dashboard</title>
    <link rel="icon" href="./Assets/images/mus" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #005653;
            --secondary: #3498db;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #17a2b8;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        .container-fluid {
            padding: 0;
        }
        
        /* Hamburger Menu Button */
        .hamburger {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            cursor: pointer;
            width: 30px;
            height: 24px;
            flex-direction: column;
            justify-content: space-between;
            background: transparent;
            border: none;
            padding: 0;
        }
        
        .hamburger span {
            display: block;
            height: 4px;
            width: 100%;
            background-color: var(--primary);
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        
        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(8px, -8px);
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary) 100%);
            color: white;
            min-height: 100vh;
            height: 100%;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            z-index: 1000;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .user-info {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .logo-container {
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .logo-img {
            max-width: 80px;
            height: auto;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        
        .logo-img:hover {
            transform: scale(1.05);
            border-color: var(--success);
        }
        
        .user-info p {
            font-size: 1.1rem;
            margin-bottom: 5px;
            font-weight: 600;
            color: white;
        }
        
        .user-info small {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
        }
        
        .sidebar .nav {
            padding: 15px 0;
        }
        
        .sidebar .nav-item {
            margin-bottom: 5px;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 12px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid var(--secondary);
            color: white;
        }
        
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--success);
            color: white;
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            width: 25px;
            margin-right: 12px;
            font-size: 1.1rem;
            text-align: center;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            width: calc(100% - 250px);
            transition: margin-left 0.3s ease;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            color: white;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .stat-card.membres {
            background: linear-gradient(135deg, #120697ff, #2980b9);
        }
        
        .stat-card.capital {
            background: linear-gradient(135deg, #2ecc71, #04622bff);
        }
        
        .stat-card.depenses {
            background: linear-gradient(135deg, #f35c4bff, #c0392b);
        }
        
        .stat-card.entrees {
            background: linear-gradient(135deg, #12bbf3ff, #3297eaff);
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 15px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .stat-card .icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .page-title {
            color: var(--primary);
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2rem;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 10px;
            display: inline-block;
        }
        
        .card-header {
            background: white !important;
            border-bottom: 2px solid #f1f1f1;
            padding: 20px 25px;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .card-title {
            color: var(--primary);
            font-weight: 600;
            margin: 0;
        }
        
        .list-group-item {
            border: none;
            border-bottom: 1px solid #f1f1f1;
            padding: 15px 20px;
            transition: background-color 0.3s ease;
        }
        
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
        
        .list-group-item:last-child {
            border-bottom: none;
            border-radius: 0 0 15px 15px;
        }
        
        .badge {
            font-size: 0.85rem;
            padding: 8px 12px;
            border-radius: 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .main-content {
                padding: 25px;
            }
            
            .page-title {
                font-size: 1.8rem;
                margin-top: 20px;
            }
        }
        
        @media (max-width: 768px) {
            body{
                padding: 0 15px;
                box-sizing: border-box
            }
            .hamburger {
                display: flex;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px 0;
               
            }
            footer {
        margin-left: calc(-15px);   /* konpanse body padding */
        margin-right: calc(-15px);
        width: calc(100% + 30px);   /* 100% + padding body sou toulede bò */
    }
            
            .stat-card .number {
                font-size: 2rem;
            }
            
            .stat-card .icon {
                font-size: 2.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
                margin-top: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .stat-card {
                padding: 20px 15px;
            }
            
            .stat-card .number {
                font-size: 1.8rem;
            }
            
            .card-header {
                padding: 15px 20px;
            }
            
            .hamburger {
                top: 15px;
                right: 15px;
                width: 28px;
                height: 22px;
            }
            
            .hamburger span {
                height: 3px;
            }
        }
        
        /* Animation pour les cartes de statistiques */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Animation pour sidebar */
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
            }
            to {
                transform: translateX(0);
            }
        }
        
        .sidebar.active {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>
<body>
    <!-- Hamburger Menu Button -->
    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <!-- Overlay for mobile menu -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <div class="user-info">
                    <!-- Logo Image -->
                    <div class="logo-container">
                        <img src="Assets/images/declinaison 2 muso.png" alt="Logo MUSO" class="logo-img">
                    </div>
                    <p class="mb-0"><?php echo htmlspecialchars($nom_sol); ?></p>
                    <small><?php echo htmlspecialchars($user_email); ?></small>
                </div>
                <ul class="nav flex-column mt-3">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="membres.php">
                            <i class="fas fa-users"></i> Membres
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cotisations.php">
                            <i class="fas fa-hand-holding-usd"></i> Cotisations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="finance.php">
                            <i class="fas fa-money-bill-wave"></i> Finance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rapports.php">
                            <i class="fas fa-chart-bar"></i> Rapports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">
                            <i class="fas fa-user"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prets.php">
                            <i class="fas fa-exchange-alt"></i> Gestion des prêts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link logout" 
                           href="logout.php" 
                           onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <h2 class="page-title">Tableau de Bord - <?php echo htmlspecialchars($nom_sol); ?></h2>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card membres">
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="number"><?php echo $stats['membres']; ?></div>
                            <div>Membres Actifs</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card capital">
                            <div class="icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="number"><?php echo number_format($stats['capital'], 2); ?> G</div>
                            <div>Capital Brut</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card depenses">
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="number"><?php echo number_format($stats['depenses'], 2); ?> G</div>
                            <div>Total Dépenses</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card entrees">
                            <div class="icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="number"><?php echo number_format($stats['entrees_jour'], 2); ?> G</div>
                            <div>Entrées du Jour</div>
                        </div>
                    </div>
                </div>
                
                <!-- Chart and Recent Transactions -->
                <div class="row mt-4">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Statistiques des Rentrées Journalières</h5>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary active">Semaine</button>
                                    <button class="btn btn-outline-primary">Mois</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="entreesChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Dernières Transactions</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php if (empty($dernieres_transactions)): ?>
                                        <div class="list-group-item text-center text-muted py-4">
                                            <i class="fas fa-receipt fa-2x mb-2"></i>
                                            <p>Aucune transaction</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($dernieres_transactions as $transaction): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold small">
                                                        <?php 
                                                        if ($transaction['membre_id']) {
                                                            echo $transaction['prenom'] . ' ' . $transaction['nom'];
                                                        } else {
                                                            echo $transaction['description'] ?? 'Transaction';
                                                        }
                                                        ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y', strtotime($transaction['date_transaction'])); ?>
                                                    </small>
                                                </div>
                                                <span class="badge <?php echo $transaction['type'] == 'entree' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $transaction['type'] == 'entree' ? '+' : '-'; ?>
                                                    <?php echo number_format($transaction['montant'], 2); ?> G
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.getElementById('hamburger');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('mainContent');
            
            function toggleSidebar() {
                hamburger.classList.toggle('active');
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }
            
            function closeSidebar() {
                hamburger.classList.remove('active');
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Toggle sidebar on hamburger click
            hamburger.addEventListener('click', toggleSidebar);
            
            // Close sidebar on overlay click
            overlay.addEventListener('click', closeSidebar);
            
            // Close sidebar when clicking on a menu link (for mobile)
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        closeSidebar();
                    }
                });
            });
            
            // Close sidebar when window is resized to desktop
            function handleResize() {
                if (window.innerWidth > 768) {
                    closeSidebar();
                }
            }
            
            window.addEventListener('resize', handleResize);
            
            // Graphique des entrées
            const ctxEntrees = document.getElementById('entreesChart');
            if (ctxEntrees) {
                const entreesChart = new Chart(ctxEntrees, {
                    type: 'line',
                    data: {
                        labels: [<?php echo '"' . implode('","', array_column($entrees_data, 'label')) . '"'; ?>],
                        datasets: [{
                            label: 'Entrées (G)',
                            data: [<?php echo implode(',', array_column($entrees_data, 'total')); ?>],
                            borderColor: '#005653',
                            backgroundColor: 'rgba(40, 197, 71, 0.34)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#005653',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: '#3498db',
                                borderWidth: 1
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    color: '#6c757d'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6c757d'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php include 'footer.php'; ?>