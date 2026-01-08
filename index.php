<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSO SYSTEM - Gestion Financière Intelligente</title>
    <link rel="icon" href="./Assets/images/mus" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Variables et styles généraux */
        :root {
            --primary: #005653;
            --secondary: #01dc82;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #95a5a6;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            color: var(--dark);
            line-height: 1.6;
            background-color: #f9f9f9;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--secondary);
            color: white;
            border-radius: 30px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;

        }

        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-accent {
            background-color: var(--accent);
        }

        .btn-accent:hover {
            background-color: #c0392b;
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }

        .btn-outline:hover {
            background-color: var(--secondary);
            color: white;
        }

        section {
            padding: 80px 0;
        }

        h1, h2, h3 {
            margin-bottom: 20px;
            color: var(--primary);
        }

        h1 {
            font-size: 2.8rem;
            line-height: 1.2;
        }

        h2 {
            font-size: 2.2rem;
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        h2:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background-color: var(--secondary);
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        p {
            margin-bottom: 20px;
            color: #555;
        }

        /* Header et navigation */
        header {
            background-color: white;
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            z-index: 1000;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            display: flex;
        
        }
        .logo img{
            width: 130px;
            height: auto;
        }

        .nav-links {
    display: flex;
    align-items: center;   /* alinyman vètikal */
    gap: 25px;             /* espas ant items */
    list-style: none;
    margin: 0;
    padding: 0;
}

        .nav-links li {
            margin-left: 30px;
        }

        .nav-links a {
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--secondary);
        }

        .mobile-menu-btn {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--primary);
        }

        /* Hero section */
        .hero {
            background:
                linear-gradient(135deg, rgba(0, 0, 0, 0.89), rgba(4, 47, 25, 0.84)),
                url("./Assets/images/9.jpg");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            padding: 150px 0 100px;
            text-align: center;
        }


        .hero h1 {
            color: white;
            margin-bottom: 20px;
            font-size: 3rem;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 40px;
            color: rgba(255, 255, 255, 0.9);
        }

        .hero-btns {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        .btn-login {
            display: inline-block;
            padding: 5px 25px;
            background-color: var(--primary); /* oswa #007bff */
            color: #fff;
            text-decoration: none;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        /* Fonctionnalités */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .feature-card {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            margin-bottom: 15px;
        }

        /* Avantages */
        .advantages {
            background-color: #f0f5f9;
        }

        .advantage-list {
            display: flex;
            flex-direction: column;
            max-width: 800px;
            margin: 0 auto;
        }

        .advantage-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .advantage-icon {
            font-size: 2rem;
            color: var(--secondary);
            margin-right: 20px;
            min-width: 60px;
        }

        /* Contact */
        .contact-container {
            display: flex;
            flex-wrap: wrap;
            gap: 50px;
            justify-content: center;
        }

        .contact-info {
            flex: 1;
            min-width: 300px;
        }

        .contact-form {
            flex: 1;
            min-width: 300px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        /* Footer */
        footer {
            background-color: var(--primary);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column {
            flex: 1;
            min-width: 250px;
        }


        .footer-column p{
            color: white;
        }

        .footer-column h3 {
            color: white;
            margin-bottom: 25px;
            font-size: 1.3rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--secondary);
            padding-left: 5px;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: var(--transition);
        }

        .social-icons a:hover {
            background-color: var(--secondary);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            h1 {
                font-size: 2.5rem;
            }
            
            h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background-color: white;
                flex-direction: column;
                padding: 20px 0;
                box-shadow: var(--shadow);
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links li {
                margin: 10px 0;
                text-align: center;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero-btns {
                flex-direction: column;
                align-items: center;
            }

            .hero-btns .btn {
                width: 100%;
                max-width: 300px;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header et Navigation -->
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="./Assets/images/declinaison 1 muso sans fond.png" alt="">
                </div>
                
                <ul class="nav-links">
                    <li><a href="#accueil">Accueil</a></li>
                    <li><a href="#fonctionnalites">Fonctionnalités</a></li>
                    <li><a href="#avantages">Nos Avantages</a></li>
                    <li><a href="login.php" class="btn-login">Se Connecter</a></li>
                </ul>
                
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Section Hero -->
    <section id="accueil" class="hero">
        <div class="container">
            <h1>Transformez Votre Gestion Financière avec <br> MUSO SYSTEM</h1>
            <p>La plateforme intuitive pour un contrôle total de vos membres, cotisations, prêts et finances. Simplifiez, optimisez, prospérez.</p>
            
            <div class="hero-btns">
                <a href="login.php" class="btn">Se Connecter <i class="fas fa-arrow-right"></i></a>
                <a href="signup.php" class="btn btn-outline">Creer un compte</a>
            </div>
        </div>
    </section>

    <!-- Section Fonctionnalités -->
    <section id="fonctionnalites">
        <div class="container">
            <h2>Fonctionnalités Clés Conçues Pour Vous</h2>
            <p class="section-subtitle">MUSO SYSTEM vous offre une suite d'outils puissants pour une gestion transparente et efficace de votre organisation.</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Gestion des Membres</h3>
                    <p>Gérez facilement les informations de vos membres, leurs adhésions et leurs historiques en temps réel avec une interface intuitive.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h3>Gestion des Cotisations</h3>
                    <p>Automatisez le suivi des cotisations, envoyez des rappels et générez des rapports détaillés pour une trésorerie maîtrisée.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h3>Gestion des Prêts</h3>
                    <p>Simplifiez l'octroi, le suivi et le remboursement des prêts avec des calendriers personnalisés et des alertes automatiques.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>Tableaux de Bord Complets</h3>
                    <p>Visualisez toutes vos données financières avec des tableaux de bord interactifs et des graphiques en temps réel.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <h3>Rapports Automatiques</h3>
                    <p>Générez automatiquement des rapports détaillés pour les assemblées générales, les audits et les autorités de régulation.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Sécurité Maximale</h3>
                    <p>Vos données sont protégées par des protocoles de sécurité avancés avec cryptage et sauvegardes automatiques.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Avantages -->
    <section id="avantages" class="advantages">
        <div class="container">
            <h2>Nos Avantages</h2>
            <p class="section-subtitle">Découvrez pourquoi des centaines d'organisations font confiance à MUSO SYSTEM pour leur gestion financière.</p>
            
            <div class="advantage-list">
                <div class="advantage-item">
                    <div class="advantage-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div>
                        <h3>Gain de Temps Exceptionnel</h3>
                        <p>Automatisez jusqu'à 80% de vos tâches administratives et concentrez-vous sur le développement de votre organisation.</p>
                    </div>
                </div>
                
                <div class="advantage-item">
                    <div class="advantage-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h3>Prise de Décision Éclairée</h3>
                        <p>Accédez à des données financières précises et à des analyses détaillées pour prendre les meilleures décisions.</p>
                    </div>
                </div>
                
                <div class="advantage-item">
                    <div class="advantage-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h3>Transparence Totale</h3>
                        <p>Offrez une visibilité complète sur la gestion financière à vos membres et renforcez leur confiance.</p>
                    </div>
                </div>
                
                <div class="advantage-item">
                    <div class="advantage-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div>
                        <h3>Support Personnalisé</h3>
                        <p>Bénéficiez d'un accompagnement sur mesure avec notre équipe d'experts disponible pour vous aider.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Contact -->

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>MUSO SYSTEM</h3>
                    <p>La plateforme intuitive pour un contrôle total de vos membres, cotisations, prêts et finances.</p>
                    <p>Simplifiez, optimisez, prospérez.</p>
                </div>
                
                <div class="footer-column">
                    <h3>Liens Rapides</h3>
                    <ul class="footer-links">
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#fonctionnalites">Fonctionnalités</a></li>
                        <li><a href="#avantages">Nos Avantages</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Fonctionnalités</h3>
                    <ul class="footer-links">
                        <li><a href="#">Gestion des Membres</a></li>
                        <li><a href="#">Gestion des Cotisations</a></li>
                        <li><a href="#">Gestion des Prêts</a></li>
                        <li><a href="#">Tableaux de Bord</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Newsletter</h3>
                    <p>Abonnez-vous pour recevoir nos dernières actualités.</p>
                    <div style="margin-top: 15px;">
                        <input type="email" placeholder="Votre email" style="padding: 10px; width: 70%; border-radius: 4px; border: none;">
                        <button class="btn" style="margin-top: 10px; padding: 10px 20px;">S'abonner</button>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                &copy; 2023 MUSO SYSTEM. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script>
        // Menu mobile
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
        
        // Fermer le menu mobile en cliquant sur un lien
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('.nav-links').classList.remove('active');
            });
        });
        
        // Gestion du formulaire de contact
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Merci pour votre message ! Nous vous contacterons bientôt.');
            this.reset();
        });
        
        // Animation au défilement
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
            } else {
                header.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
            }
        });
    </script>
</body>
</html>