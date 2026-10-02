<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../../../config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $donnees = json_decode(file_get_contents('php://input'), true);
    $id_casier = $donnees['id_casier'] ?? null;
    $id_badge = $donnees['id_badge'] ?? null; // Peut être null si un badge inconnu est scanné
    $statut = $donnees['statut'] ?? null;

    if (!$id_casier || !in_array($statut, ['autorise', 'refuse'])) {
        http_response_code(400);
        echo json_encode(['erreur' => 'Données invalides ou statut incorrect']);
        exit;
    }

    try {
        $sql = "INSERT INTO Acces (id_casier, id_badge, statut) VALUES (:casier, :badge, :statut)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'casier' => $id_casier,
            'badge' => $id_badge,
            'statut' => $statut
        ]);
        http_response_code(201);
        echo json_encode(["message" => "Accès enregistré"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["erreur" => "Erreur BDD"]);
    }
} elseif ($method === 'GET') {
    try {
        $sql = "SELECT a.id_acces, a.date_heure, a.statut, c.numero AS numero_casier, u.nom, u.prenom 
                FROM Acces a
                LEFT JOIN Casier c ON a.id_casier = c.id_casier
                LEFT JOIN Badge b ON a.id_badge = b.id_badge
                LEFT JOIN Utilisateur u ON b.id_user = u.id_user
                ORDER BY a.date_heure DESC";
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["erreur" => "Erreur BDD"]);
    }
}
?>
