<?php
session_start();

// 1. Vérifie si l'utilisateur est connecté
if (empty($_SESSION['connecte'])) {
	header('Location: index.php?erreur=' . urlencode('Veuillez vous connecter.'));
	exit;
}

// 2. Vérifie si l'utilisateur a le rôle admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?erreur=' . urlencode('Accès refusé. Espace réservé aux administrateurs.'));
    exit;
}

$nomUtilisateur = htmlspecialchars((string) ($_SESSION['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tableau de bord</title>
	<link rel="stylesheet" href="styleprojet.css">
</head>
<body>
	<main>
		<h1>Tableau de bord</h1>
		<p>Bienvenue, <?= $nomUtilisateur ?>.</p>
		<p>Vous êtes connecté en tant qu'administrateur.</p>

        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #d9dee5;">
            <h2>Espace Administrateur</h2>
            <a href="gestion_utilisateurs.php" style="display: inline-block; padding: 0.7rem 1rem; background: #1f5f99; color: #fff; text-decoration: none; border-radius: 4px;">Gérer les utilisateurs</a>
        </div>
	</main>
</body>
</html>