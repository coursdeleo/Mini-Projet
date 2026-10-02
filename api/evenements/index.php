<?php
header('Content-Type: application/json');
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$donnees = json_decode(file_get_contents('php://input'), true);
$id_casier = $donnees['id_casier'] ?? null;
$type_evenement = $donnees['type_evenement'] ?? null;
$description = $donnees['description'] ?? null;

$types_valides = ['porte_ouverte_longtemps', 'tentatives_refusees_multiples', 'autre'];

if (!$id_casier || !in_array($type_evenement, $types_valides)) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Données invalides']);
    exit;
}

try {
    $sql = "INSERT INTO Evenement (id_casier, type_evenement, description) VALUES (:casier, :type, :desc)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'casier' => $id_casier,
        'type' => $type_evenement,
        'desc' => $description
    ]);
    http_response_code(201);
    echo json_encode(["message" => "Alerte enregistrée"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur BDD"]);
}
?>
