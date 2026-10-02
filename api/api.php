<?php
// On force le format de réponse en JSON pour que le script Python le comprenne
header('Content-Type: application/json');

// 1. Connexion à votre base de données via votre fichier existant
require_once 'db.php';

// 2. Sécurité : Seules les requêtes POST (envoyées par la passerelle) sont acceptées
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erreur' => 'Méthode non autorisée. Utilisez POST.']);
    exit;
}

// 3. Récupération des données envoyées par la Raspberry Pi
$json_recu = file_get_contents('php://input');
$donnees = json_decode($json_recu, true);

$action = $donnees['action'] ?? null;
$uid_badge = $donnees['uid_badge'] ?? null;
$id_casier = $donnees['id_casier'] ?? null;

// Vérification que les données minimales sont bien présentes
if ($action !== 'verify' || empty($uid_badge) || empty($id_casier)) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Données incomplètes (action, uid_badge ou id_casier manquants)']);
    exit;
}

try {
    // 4. Vérification des droits d'accès
    // On cherche si le badge existe (actif) ET s'il est lié à l'utilisateur de ce casier précis
    $sql_verif = "SELECT b.id_badge, c.id_casier 
                  FROM Badge b
                  LEFT JOIN Casier c ON c.id_user_attribue = b.id_user AND c.id_casier = :casier
                  WHERE b.uid_badge = :uid AND b.actif = 1";
            
    $requete = $pdo->prepare($sql_verif);
    $requete->execute(['uid' => $uid_badge, 'casier' => $id_casier]);
    $resultat = $requete->fetch(PDO::FETCH_ASSOC);

    $acces_autorise = false;
    $id_badge_connu = null;
    $statut_passage = 'refuse';

    // Si on trouve un résultat, cela signifie que le badge existe et est actif
    if ($resultat) {
        $id_badge_connu = $resultat['id_badge'];
        
        // Si la jointure avec le casier a fonctionné, alors c'est le bon utilisateur
        if ($resultat['id_casier'] !== null) {
            $acces_autorise = true;
            $statut_passage = 'autorise';
        }
    }

    // 5. Enregistrement dans l'historique (Table Acces)
    // Même si le badge est inconnu ($id_badge_connu = null), on enregistre la tentative refusée
    $sql_log = "INSERT INTO Acces (id_casier, id_badge, statut) VALUES (:casier, :badge, :statut)";
    $log_stmt = $pdo->prepare($sql_log);
    $log_stmt->execute([
        'casier' => $id_casier,
        'badge' => $id_badge_connu,
        'statut' => $statut_passage
    ]);

    // 6. Réponse à la Raspberry Pi
    if ($acces_autorise) {
        // La porte va s'ouvrir
        echo json_encode([
            "access_granted" => true, 
            "id_badge" => $id_badge_connu
        ]);
    } else {
        // La porte reste fermée
        http_response_code(403);
        echo json_encode([
            "access_granted" => false, 
            "message" => "Accès refusé"
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    // En cas d'erreur de base de données, on renvoie une erreur 500 pour que le matériel sache qu'il y a un problème serveur
    echo json_encode(["erreur" => "Erreur serveur : " . $e->getMessage()]);
}
?>
