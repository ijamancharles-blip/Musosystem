<?php
include 'config.php';
redirectIfNotLogged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $membre_id = $_POST['membre_id'];
    $user_id = getUserId();
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Récupérer le nombre de cases actuel du membre
    $query = "SELECT nombre_cases FROM membres WHERE id = :membre_id AND utilisateur_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':membre_id', $membre_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $membre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($membre) {
        $nouveau_nombre = $membre['nombre_cases'] + 1;
        
        $query_update = "UPDATE membres SET nombre_cases = :nombre_cases WHERE id = :membre_id AND utilisateur_id = :user_id";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bindParam(':nombre_cases', $nouveau_nombre);
        $stmt_update->bindParam(':membre_id', $membre_id);
        $stmt_update->bindParam(':user_id', $user_id);
        
        if ($stmt_update->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de la case']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Membre non trouvé']);
    }
}
?>