<?php
// db.php
$serveur = 'localhost';
$baseDeDonnees = 'miniProjet';
$utilisateur = 'admin';
$motDePasseBase = '14052007pm'; 

try {
    $pdo = new PDO(
        "mysql:host=$serveur;dbname=$baseDeDonnees;charset=utf8mb4",
        $utilisateur,
        $motDePasseBase,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // ⚠️ AFFICHE LA VRAIE ERREUR AU LIEU DE REDIRIGER
    die("ERREUR EXACTE DE DB : " . $e->getMessage());
}