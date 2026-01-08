<?php
// Fonctions utilitaires pour le système FINEX

// Vérifier si formatMoney existe avant de le déclarer
if (!function_exists('formatMoney')) {
    function formatMoney($amount) {
        return number_format($amount, 2) . ' G';
    }
}

// Vérifier si logAction existe avant de le déclarer
if (!function_exists('logAction')) {
    function logAction($user_id, $action, $conn) {
        try {
            $query = "INSERT INTO logs (utilisateur_id, action, date_action) VALUES (:user_id, :action, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':action', $action);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de l'enregistrement du log : " . $e->getMessage());
        }
    }
}

// Vérifier si getMonthName existe avant de le déclarer
if (!function_exists('getMonthName')) {
    function getMonthName($monthNumber) {
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        return $months[$monthNumber] ?? 'Mois inconnu';
    }
}

// Vérifier si generateMemberCode existe avant de le déclarer
if (!function_exists('generateMemberCode')) {
    function generateMemberCode($prenom, $nom) {
        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        $timestamp = time();
        return $initials . $timestamp;
    }
}

// Vérifier si calculateLoanInterest existe avant de le déclarer
if (!function_exists('calculateLoanInterest')) {
    function calculateLoanInterest($montant, $taux, $duree) {
        $interet = $montant * ($taux / 100);
        return $montant + $interet;
    }
}

// Vérifier si hasPaidCotisation existe avant de le déclarer
if (!function_exists('hasPaidCotisation')) {
    function hasPaidCotisation($membre_id, $mois, $annee, $user_id) {
        global $conn;
        $query = "SELECT id FROM cotisations 
                  WHERE membre_id = :membre_id 
                  AND mois = :mois 
                  AND annee = :annee 
                  AND utilisateur_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':membre_id', $membre_id);
        $stmt->bindParam(':mois', $mois);
        $stmt->bindParam(':annee', $annee);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}

// Vérifier si getMemberBalance existe avant de le déclarer
if (!function_exists('getMemberBalance')) {
    function getMemberBalance($membre_id, $user_id) {
        global $conn;
        $query = "SELECT 
                    SUM(CASE WHEN type = 'entree' THEN montant ELSE -montant END) as solde
                  FROM transactions 
                  WHERE membre_id = :membre_id 
                  AND utilisateur_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':membre_id', $membre_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['solde'] ?? 0;
    }
}


// Vérifier si getUserFinancialStats existe avant de le déclarer
if (!function_exists('getUserFinancialStats')) {
    function getUserFinancialStats($user_id) {
        global $conn;
        $stats = [];

        // Total entrées
        $query = "SELECT SUM(montant) as total FROM transactions 
                  WHERE type = 'entree' AND utilisateur_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $stats['total_entrees'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Total dépenses
        $query = "SELECT SUM(montant) as total FROM transactions 
                  WHERE type = 'depense' AND utilisateur_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $stats['total_depenses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Solde actuel
        $stats['solde'] = $stats['total_entrees'] - $stats['total_depenses'];
        return $stats;
    }
}


if (!function_exists('getConfiguration')) {
    function getConfiguration($cle, $default = null, $user_id = null) {
        global $conn;
        
        if ($user_id === null) {
            $user_id = getUserId();
        }
        
        try {
            $query = "SELECT valeur FROM configuration 
                      WHERE utilisateur_id = :user_id AND cle = :cle";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':cle', $cle, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['valeur'] !== null) {
                return $result['valeur'];
            }
        } catch (PDOException $e) {
            // Log erreur si nécessaire
        }
        
        return $default;
    }
}

if (!function_exists('setConfiguration')) {
    function setConfiguration($cle, $valeur, $user_id = null) {
        global $conn;
        
        if ($user_id === null) {
            $user_id = getUserId();
        }
        
        try {
            $query_check = "SELECT id FROM configuration 
                            WHERE utilisateur_id = :user_id AND cle = :cle";
            $stmt_check = $conn->prepare($query_check);
            $stmt_check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_check->bindParam(':cle', $cle, PDO::PARAM_STR);
            $stmt_check->execute();
            
            if ($stmt_check->rowCount() > 0) {
                $query = "UPDATE configuration 
                          SET valeur = :valeur 
                          WHERE utilisateur_id = :user_id AND cle = :cle";
            } else {
                $query = "INSERT INTO configuration (utilisateur_id, cle, valeur)
                          VALUES (:user_id, :cle, :valeur)";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':cle', $cle, PDO::PARAM_STR);
            $stmt->bindParam(':valeur', $valeur);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}

?>


