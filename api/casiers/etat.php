<?php
header('Content-Type: application/json');
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    exit;
}

$id_casier = $_GET['id'] ?? null;
$donnees = json_decode(file_get_contents('php://input'), true);
$etat_porte = $donnees['etat_porte'] ?? null;

if (!$id_casier || !in_array($etat_porte, ['ouverte', 'fermee'])) {
    http_response_code(400);
    echo json_encode(['erreur' => 'ID casier manquant ou état de porte invalide']);
    exit;
}

try {
    $sql = "UPDATE Casier SET etat_porte = :etat WHERE id_casier = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['etat' => $etat_porte, 'id' => $id_casier]);
    
    echo json_encode(["message" => "État de la porte mis à jour"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erreur" => "Erreur BDD"]);
}
?>
