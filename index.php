<?php
// Configuration générale du site : modifiez uniquement cette zone pour commencer.
$message = '';

// Affichage des messages envoyés par le fichier de traitement.
if (isset($_GET['connexion']) && $_GET['connexion'] === 'ok') {
	$message = 'Connexion réussie.';
} elseif (isset($_GET['erreur'])) {
	$message = htmlspecialchars($_GET['erreur'], ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>index_mini_projet</title>
	<link rel="stylesheet" href="style.css">
    
</head>
<body>
	<main>
		<h1>Connexion</h1>

		<?php if ($message !== ''): ?>
			<p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
		<?php endif; ?>

		<form method="post" action="connexion.php">
			<p>
				<label for="identifiant">Identifiant</label><br>
				<input type="text" id="identifiant" name="identifiant" required autocomplete="username">
			</p>

			<p>
				<label for="mot_de_passe">Mot de passe</label><br>
				<input type="password" id="mot_de_passe" name="mot_de_passe" required autocomplete="current-password">
			</p>

			<button type="submit">Se connecter</button>
		</form>
	</main>
</body>
</html>
