<?php
// config.php
$host = 'localhost';
$dbname = 'miniProjet';
$user = 'tamanui';
$pass = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Activation des exceptions pour la gestion des erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    // En production, on n'affiche pas l'erreur exacte pour des raisons de sécurité
    echo json_encode(["erreur" => "Erreur de connexion à la base de données"]);
    exit();
}
?>
