<?php
header('Content-Type: application/json');
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erreur' => 'Méthode non autorisée']);
    exit;
}

$donnees = json_decode(file_get_contents('php://input'), true);
$uid_badge = $donnees['uid_badge'] ?? null;
$id_casier = $donnees['id_casier'] ?? null;

if (empty($uid_badge) || empty($id_casier)) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Données incomplètes (uid_badge ou id_casier manquant)']);
    exit;
}

try {
    // Vérifie si le badge est actif ET s'il appartient à l'utilisateur assigné au casier ciblé
    $sql = "SELECT b.id_badge, c.id_casier 
            FROM Badge b
            LEFT JOIN Casier c ON c.id_user_attribue = b.id_user AND c.id_casier = :casier
            WHERE b.uid_badge = :uid AND b.actif = 1";
            
    $requete = $pdo->prepare($sql);
    $requete->execute(['uid' => $uid_badge, 'casier' => $id_casier]);
    $resultat = $requete->fetch(PDO::FETCH_ASSOC);

    if ($resultat && $resultat['id_casier'] !== null) {
        echo json_encode(["access_granted" => true, "id_badge" => $resultat['id_badge']]);
    } else {
        http_response_code(403);
        // Le badge existe peut-être, mais il n'est pas assigné à ce casier ou est inactif
        echo json_encode(["access_granted" => false, "message" => "Accès refusé"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur serveur"]);
}
?>
