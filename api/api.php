<?php
// 1. On force la réponse en JSON pour le Raspberry Pi
header('Content-Type: application/json');

// 2. On lit la requête envoyée par ton script Python
$json_recu = file_get_contents('php://input');
$donnees = json_decode($json_recu, true);

// 3. On extrait l'action et l'UID du badge
$action    = $donnees['action'] ?? null;
$uid_badge = $donnees['uid_badge'] ?? null;

$acces_autorise = false;

if ($action === 'verify' && !empty($uid_badge)) {
    try {
        // CONNEXION À MARIADB
        $pdo = new PDO('mysql:host=localhost;dbname=miniProjet;charset=utf8', 'tamanui', '1234');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // REQUÊTE SQL (avec vérification que le badge est actif)
        $sql = "SELECT id_badge FROM Badge WHERE uid_badge = :uid AND actif = 1 LIMIT 1";
        $requete = $pdo->prepare($sql);
        $requete->execute(['uid' => $uid_badge]);
        
        // Si on trouve une ligne, le badge est valide !
        if ($requete->fetch()) {
            $acces_autorise = true;
        }
        
    } catch (PDOException $e) {
        // En cas d'erreur BDD, l'accès reste bloqué par sécurité
    }
}

// 4. On renvoie le vrai ou faux au format JSON
echo json_encode(["access_granted" => $acces_autorise]);
?>
