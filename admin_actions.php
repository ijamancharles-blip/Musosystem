<?php
include 'config.php';
redirectIfNotLogged();
redirectIfNotAdmin();

$db = new Database();
$conn = $db->getConnection();

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'ajouter_utilisateur':
        ajouterUtilisateur($conn);
        break;
        
    case 'modifier_utilisateur':
        modifierUtilisateur($conn);
        break;
        
    case 'supprimer_utilisateur':
        supprimerUtilisateur($conn);
        break;
        
    case 'bloquer_utilisateur':
        bloquerUtilisateur($conn);
        break;
        
    case 'debloquer_utilisateur':
        debloquerUtilisateur($conn);
        break;
        
    default:
        header("Location: admin_dashboard.php?error=action_invalide");
        exit();
}

function ajouterUtilisateur($conn) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    $sexe = $_POST['sexe'] ?? 'M';
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        header("Location: admin_dashboard.php?error=champs_manquants");
        exit();
    }
    
    if (strlen($password) < 6) {
        header("Location: admin_dashboard.php?error=password_trop_court");
        exit();
    }
    
    // Vérifier si l'email existe déjà
    $query = "SELECT id FROM utilisateurs WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        header("Location: admin_dashboard.php?error=email_existe");
        exit();
    }
    
    // Hasher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insérer l'utilisateur
    $query = "INSERT INTO utilisateurs (nom, prenom, email, telephone, adresse, sexe, mot_de_passe, is_admin, is_active) 
              VALUES (:nom, :prenom, :email, :telephone, :adresse, :sexe, :mot_de_passe, :is_admin, :is_active)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telephone', $telephone);
    $stmt->bindParam(':adresse', $adresse);
    $stmt->bindParam(':sexe', $sexe);
    $stmt->bindParam(':mot_de_passe', $hashed_password);
    $stmt->bindParam(':is_admin', $is_admin);
    $stmt->bindParam(':is_active', $is_active);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=utilisateur_ajoute");
    } else {
        header("Location: admin_dashboard.php?error=erreur_ajout");
    }
    exit();
}

function modifierUtilisateur($conn) {
    $user_id = $_POST['user_id'];
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    $sexe = $_POST['sexe'] ?? 'M';
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($nom) || empty($prenom) || empty($email)) {
        header("Location: admin_dashboard.php?error=champs_manquants");
        exit();
    }
    
    // Vérifier si l'email existe déjà pour un autre utilisateur
    $query = "SELECT id FROM utilisateurs WHERE email = :email AND id != :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        header("Location: admin_dashboard.php?error=email_existe");
        exit();
    }
    
    // Préparer la requête de mise à jour
    if (!empty($password)) {
        if (strlen($password) < 6) {
            header("Location: admin_dashboard.php?error=password_trop_court");
            exit();
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE utilisateurs SET 
                    nom = :nom, 
                    prenom = :prenom, 
                    email = :email, 
                    telephone = :telephone,
                    adresse = :adresse,
                    sexe = :sexe,
                    mot_de_passe = :mot_de_passe,
                    is_admin = :is_admin,
                    is_active = :is_active 
                  WHERE id = :user_id";
    } else {
        $query = "UPDATE utilisateurs SET 
                    nom = :nom, 
                    prenom = :prenom, 
                    email = :email, 
                    telephone = :telephone,
                    adresse = :adresse,
                    sexe = :sexe,
                    is_admin = :is_admin,
                    is_active = :is_active 
                  WHERE id = :user_id";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telephone', $telephone);
    $stmt->bindParam(':adresse', $adresse);
    $stmt->bindParam(':sexe', $sexe);
    $stmt->bindParam(':is_admin', $is_admin);
    $stmt->bindParam(':is_active', $is_active);
    $stmt->bindParam(':user_id', $user_id);
    
    if (!empty($password)) {
        $stmt->bindParam(':mot_de_passe', $hashed_password);
    }
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=utilisateur_modifie");
    } else {
        header("Location: admin_dashboard.php?error=erreur_modification");
    }
    exit();
}

function supprimerUtilisateur($conn) {
    $user_id = $_POST['user_id'];
    $current_user_id = getUserId();
    
    // Empêcher l'auto-suppression
    if ($user_id == $current_user_id) {
        header("Location: admin_dashboard.php?error=auto_suppression");
        exit();
    }
    
    try {
        // Commencer une transaction
        $conn->beginTransaction();
        
        // Supprimer les transactions de l'utilisateur
        $query = "DELETE FROM transactions WHERE utilisateur_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Supprimer les cotisations de l'utilisateur
        $query = "DELETE FROM cotisations WHERE utilisateur_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Supprimer les prêts de l'utilisateur
        $query = "DELETE FROM prets WHERE utilisateur_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Supprimer les membres de l'utilisateur
        $query = "DELETE FROM membres WHERE utilisateur_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Supprimer l'utilisateur
        $query = "DELETE FROM utilisateurs WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Valider la transaction
        $conn->commit();
        
        header("Location: admin_dashboard.php?success=utilisateur_supprime");
        
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollBack();
        header("Location: admin_dashboard.php?error=erreur_suppression");
    }
    exit();
}

function bloquerUtilisateur($conn) {
    $user_id = $_POST['user_id'];
    $current_user_id = getUserId();
    
    // Empêcher l'auto-blocage
    if ($user_id == $current_user_id) {
        header("Location: admin_dashboard.php?error=auto_blocage");
        exit();
    }
    
    $query = "UPDATE utilisateurs SET is_active = 0 WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=utilisateur_bloque");
    } else {
        header("Location: admin_dashboard.php?error=erreur_blocage");
    }
    exit();
}

function debloquerUtilisateur($conn) {
    $user_id = $_POST['user_id'];
    
    $query = "UPDATE utilisateurs SET is_active = 1 WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=utilisateur_debloque");
    } else {
        header("Location: admin_dashboard.php?error=erreur_deblocage");
    }
    exit();
}
?>