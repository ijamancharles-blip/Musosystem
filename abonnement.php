<?php

session_start();
$user_id   = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_nom'] ?? '';

if (!$user_id) {
    // Redirije nan paj koneksyon
    header("Location: login.php");
    exit; // Sispann tout ekzekisyon
}



// ==================== KONEKSYON BAZ DONE ====================

require_once("config.php");
$database = new Database();
$conn = $database->getConnection();

// ==================== SESYON ====================
// Tcheke si session user egziste anvan ou li li


// ==================== TRETE SOUMSYON FÒM ====================
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $plan_type = $_POST['plan_type'] ?? '';
    $transaction_number = trim($_POST['transaction_number'] ?? '');
    
    if (empty($plan_type) || empty($transaction_number)) {
        $message = 'Tanpri chwazi yon plan epi antre nimewo transaksyon an.';
        $message_type = 'error';
    } else {
        try {
            // Anrejistre nan baz done
            $stmt = $conn->prepare("
                INSERT INTO subscriptions (user_id, plan_type, transaction_number, created_at) 
                VALUES (:user_id, :plan_type, :transaction_number, NOW())
            ");
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':plan_type', $plan_type, PDO::PARAM_STR);
            $stmt->bindParam(':transaction_number', $transaction_number, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $message = '✅ Abònman anrejistre avèk siksè! Admin pral verifye transaksyon an.';
                $message_type = 'success';
                
                // Reyajiste fòm lan
                echo '<script>
                    setTimeout(function() {
                        document.getElementById("subscription-form").reset();
                        document.getElementById("submit-btn").disabled = true;
                        document.querySelectorAll(".plan-card").forEach(card => {
                            card.classList.remove("selected");
                        });
                        document.getElementById("selected-plan-display").classList.remove("show");
                    }, 1000);
                </script>';
            } else {
                $message = '❌ Erè pandan anrejistreman.';
                $message_type = 'error';
            }
            
        } catch (PDOException $e) {
            $message = '❌ Erè baz done: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// ==================== PLAN YO DISPONIB ====================
$plans = [
    'basic_mois' => [
        'name' => 'Plan Basique Mois',
        'price' => '1,500 Gdes',
        'period' => '/mwa',
        'icon' => 'fa-star',
        'color' => '#3498db',
        'features' => ['Gestion cotisation', 'Dashboard cotisation', 'Gestion membre', 'Profil']
    ],
    'premium_mois' => [
        'name' => 'Plan Premium Mois',
        'price' => '3,000 Gdes',
        'period' => '/mois',
        'icon' => 'fa-crown',
        'color' => '#f39c12',
        'features' => ['Dashboard', 'Gestion membre', 'Gestion cotisation', 'Gestion finance', 'Rapports avancée', 'Gestion des prets','Gestion de profil' ,'Et autre']
    ],
    'basic_ans' => [
        'name' => 'Plan Basique Ans',
        'price' => '15,000 Gdes',
        'period' => '/ans',
        'icon' => 'fa-star',
        'color' => '#3498db',
        'features' => ['Gestion cotisation', 'Dashboard cotisation', 'Gestion membre', 'Profil']
    ],
    'premium_ans' => [
        'name' => 'Plan Premium Ans',
        'price' => '35,000 Gdes',
        'period' => '/ans',
        'icon' => 'fa-crown',
        'color' => '#f39c12',
        'features' => ['Tout nan Basik', 'Ajoute 100 membre', 'Sipò 24/7', 'Rapò avanse', 'Priyorite']
    ],
];
?>

<!DOCTYPE html>
<html lang="ht">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSO Abònman</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: linear-gradient(to right, #015807ff, #4a6491);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-content h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        
        }
        
        .header-content h4 {
            opacity: 0.9;
            font-size: 1.1rem;
            font-style: italic;
            
        }
        
        .user-info {
            background: rgba(255,255,255,0.15);
            padding: 15px 20px;
            border-radius: 10px;
            text-align: right;
        }
        
        .user-info strong {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        /* Message */
        .message {
            padding: 18px 25px;
            margin-bottom: 30px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.1rem;
            animation: slideIn 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .message.success {
            background: linear-gradient(to right, #27ae60, #2ecc71);
            color: white;
            border-left: 6px solid #229954;
        }
        
        .message.error {
            background: linear-gradient(to right, #e74c3c, #ff6b6b);
            color: white;
            border-left: 6px solid #c0392b;
        }
        
        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 992px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }
        
        /* Plans Section */
        .plans-section h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3498db;
        }
        
        .plans-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .plan-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid #e0e0e0;
            position: relative;
            overflow: hidden;
        }
        
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            border-color: #3498db;
        }
        
        .plan-card.selected {
            border-color: #27ae60;
            background: #f8fff9;
            box-shadow: 0 10px 25px rgba(39, 174, 96, 0.2);
        }
        
        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .plan-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .plan-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .plan-name {
            font-size: 1.6rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .plan-price {
            text-align: right;
        }
        
        .price {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .period {
            font-size: 1rem;
            color: #7f8c8d;
            display: block;
        }
        
        .features {
            list-style: none;
            margin-bottom: 20px;
        }
        
        .features li {
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .features li:last-child {
            border-bottom: none;
        }
        
        .features li i {
            color: #27ae60;
        }
        
        .select-indicator {
            text-align: center;
            color: #27ae60;
            font-weight: bold;
            margin-top: 10px;
            display: none;
        }
        
        .plan-card.selected .select-indicator {
            display: block;
        }
        
        /* Form Section */
        .form-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-section h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #9b59b6;
        }
        
        .selected-plan-display {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #3498db;
            display: none;
        }
        
        .selected-plan-display.show {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .selected-plan-display p {
            margin: 8px 0;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .help-text {
            display: block;
            margin-top: 8px;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, #27ae60, #2ecc71);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .submit-btn:hover:not(:disabled) {
            background: linear-gradient(to right, #229954, #27ae60);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }
        
        .submit-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* History Section */

        /* Animations */
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status-suspended {
            background: #ffeaa7;
            color: #d35400;
        }
        
        .status-active {
            background: #a3e4d7;
            color: #27ae60;
        }

            .bg {
            margin-top: 15px;
            text-align: center;
        }

        .bg .btn-sm {
            background: linear-gradient(135deg, #003666ff, #00004cff);
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }


        .bg .btn-sm:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .user-info {
                text-align: center;
                width: 100%;
            }
            
            .plan-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .plan-price {
                text-align: center;
            }
            
            .history-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1>Page Abonnement</h1>
                <h4>Numero Moncash: 34941969</h4>
                <h4>Nom: Camilove Landie Dieudonné</h>
            </div>
            <div class="user-info">
                <strong><i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name ?? ''); ?></strong>
                
                <div class="bg">
                    <form action="logout.php" method="post">
                        <button type="submit" class="btn-sm">
                            Retour
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Message -->
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
            
        <?php endif; ?>
        
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Left: Plans -->
            <div class="plans-section">
                <h2><i class="fas fa-crown"></i> Plan Abònman Disponib</h2>
                <div class="plans-container">
                    <?php foreach ($plans as $key => $plan): ?>
                    <div class="plan-card" data-plan="<?php echo $key; ?>">
                        <div class="plan-header">
                            <div class="plan-title">
                                <div class="plan-icon" style="background: <?php echo $plan['color']; ?>;">
                                    <i class="fas <?php echo $plan['icon']; ?>"></i>
                                </div>
                                <div class="plan-name"><?php echo $plan['name']; ?></div>
                            </div>
                            <div class="plan-price">
                                <div class="price"><?php echo $plan['price']; ?></div>
                                <span class="period"><?php echo $plan['period']; ?></span>
                            </div>
                        </div>
                        <ul class="features">
                            <?php foreach ($plan['features'] as $feature): ?>
                            <li><i class="fas fa-check"></i> <?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="select-indicator">
                            <i class="fas fa-check-circle"></i> Plan Choisi
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Right: Form -->
            <div class="form-section">
                <h2><i class="fas fa-credit-card"></i> Fè Peman Ou</h2>
                
                <div id="selected-plan-display" class="selected-plan-display">
                    <p><strong><i class="fas fa-check-circle"></i> Plan choisi:</strong> <span id="display-plan-name"></span></p>
                    <p><strong><i class="fas fa-money-bill-wave"></i> Pri:</strong> <span id="display-plan-price"></span></p>
                    <p><strong><i class="fas fa-money-bill-wave"></i> Numero Moncash: 34941969 </strong> <span id="display-plan-price"></span></p>
                    <p><strong><i class="fas fa-money-bill-wave"></i> Nom: Camilove Landie Dieudonné</strong> <span id="display-plan-price"></span></p>
                </div>
                
                <form method="POST" id="subscription-form">
                    <input type="hidden" name="plan_type" id="hidden-plan-type" value="">
                    
                    <div class="form-group">
                        <label for="transaction_number">
                            <i class="fas fa-receipt"></i> Nimewo Transaksyon
                        </label>
                        <input type="text" 
                               id="transaction_number" 
                               name="transaction_number" 
                               value="<?php echo isset($_POST['transaction_number']) ? htmlspecialchars($_POST['transaction_number']) : ''; ?>"
                               placeholder="Egzanp: TXN: 0026599399304" 
                               required
                               maxlength="50">
                        <small class="help-text">
                            Antre nimewo transaksyon ki sou resi MonCash ou
                        </small>
                    </div>
                    
                    <button type="submit" 
                            name="soumettre" 
                            class="submit-btn" 
                            id="submit-btn" 
                            >
                        <i class="fas fa-paper-plane"></i> Soumèt Peman
                    </button>
                </form>
            </div>
        </div>
        
        <!-- History Section -->
        
    </div>
    
    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const planCards = document.querySelectorAll('.plan-card');
            const hiddenPlanType = document.getElementById('hidden-plan-type');
            const selectedPlanDisplay = document.getElementById('selected-plan-display');
            const displayPlanName = document.getElementById('display-plan-name');
            const displayPlanPrice = document.getElementById('display-plan-price');
            const submitBtn = document.getElementById('submit-btn');
            const transactionInput = document.getElementById('transaction_number');
            
            const plans = {
                'basic_mois': {
                    name: 'Plan Basique Mois',
                    price: '1,500 Gdes/mwa'
                },
                'premium_mois': {
                    name: 'Plan Premium Mois',
                    price: '3,000 Gdes/mois'
                },
                'basic_ans': {
                    name: 'Plan Basique Ans',
                    price: '15,000 Gdes/ans'
                },
                'premium_ans': {
                    name: 'Plan Premium Ans',
                    price: '35,000 Gdes/ans'
                },
                
            };
            
            let selectedPlan = null;
            
            // Klike sou plan yo
            planCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Retire seleksyon ansyen
                    planCards.forEach(c => c.classList.remove('selected'));
                    
                    // Ajoute seleksyon nouvo
                    this.classList.add('selected');
                    
                    // Mete ajou plan chwazi a
                    selectedPlan = this.getAttribute('data-plan');
                    hiddenPlanType.value = selectedPlan;
                    
                    // Afiche enfòmasyon plan chwazi a
                    selectedPlanDisplay.classList.add('show');
                    displayPlanName.textContent = plans[selectedPlan].name;
                    displayPlanPrice.textContent = plans[selectedPlan].price;
                    
                    // Pèmèt bouton soumèt si gen transaksyon
                    validateForm();
                    
                    // Desann nan fòm lan (pou mobil)
                    if (window.innerWidth < 768) {
                        selectedPlanDisplay.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
            
            // Validasyon nimewo transaksyon
            transactionInput.addEventListener('input', function() {
                // Konvèti an majiskil epi retire karaktè ilegal
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
                
                // Mete ajou koulè fwontyè
                if (this.value.trim().length >= 5) {
                    this.style.borderColor = '#27ae60';
                } else {
                    this.style.borderColor = '#ddd';
                }
                
                validateForm();
            });
            
            // Fonksyon validasyon
            function validateForm() {
                const hasPlan = selectedPlan !== null;
                const hasTransaction = transactionInput.value.trim().length >= 5;
                
                if (hasPlan && hasTransaction) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            }
            
            // Validasyon sou soumisyon
            document.getElementById('subscription-form').addEventListener('submit', function(e) {
                if (!selectedPlan) {
                    e.preventDefault();
                    alert('Tanpri chwazi yon plan abònman.');
                    return;
                }
                
                if (transactionInput.value.trim().length < 12) {
                    e.preventDefault();
                    alert('Nimewo transaksyon an dwe gen omwen 12 karaktè.');
                    return;
                }
                
                // Konfimasyon
                if (!confirm(`Èske w sèten w vle anrejistre abònman ${plans[selectedPlan].name}?\nNimewo transaksyon: ${transactionInput.value}`)) {
                    e.preventDefault();
                    return;
                }
                
                // Chanje tèks bouton pandan soumisyon
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ap anrejistre...';
                submitBtn.disabled = true;
            });

            
            // Ajoute animasyon
            planCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>