<?php
include 'config.php';
redirectIfNotLogged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $membre_id = $_POST['membre_id'];
    $montant = $_POST['montant'];
    $user_id = getUserId();
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "UPDATE membres SET montant_par_case = :montant WHERE id = :membre_id AND utilisateur_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':montant', $montant);
    $stmt->bindParam(':membre_id', $membre_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du changement du montant']);
    }
}
?>