<?php
session_start();

// Inclusion du fichier de connexion (s'il manque, le script s'arrête)
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$identifiant = trim($_POST['identifiant'] ?? '');
$motDePasse = $_POST['mot_de_passe'] ?? '';

if ($identifiant === '' || $motDePasse === '') {
    header('Location: index.php?erreur=' . urlencode('Veuillez remplir tous les champs.'));
    exit;
}

try {
    // La requête sélectionne désormais la colonne "role" en plus du reste
    $requete = $pdo->prepare(
        'SELECT id_user, nom, email, mot_de_passe, role FROM Utilisateur WHERE email = :identifiant OR nom = :identifiant LIMIT 1'
    );
    $requete->execute(['identifiant' => $identifiant]);
    $utilisateurTrouve = $requete->fetch();

    if (!$utilisateurTrouve || !password_verify($motDePasse, $utilisateurTrouve['mot_de_passe'])) {
        header('Location: index.php?erreur=' . urlencode('Identifiant ou mot de passe incorrect.'));
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['id_user'] = (int) $utilisateurTrouve['id_user'];
    $_SESSION['nom'] = $utilisateurTrouve['nom'];
    $_SESSION['role'] = $utilisateurTrouve['role']; // Enregistrement du rôle
    $_SESSION['connecte'] = true;

    header('Location: tableauDeBord.php');
    exit;

} catch (PDOException $e) {
    header('Location: index.php?erreur=' . urlencode('Erreur technique lors de la connexion.'));
    exit;
}