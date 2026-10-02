<?php
$message = '';

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
	<title>Mini projet - Connexion</title>
	<link rel="stylesheet" href="styleprojet.css">
</head>
<body>
	<main>
		<h1>Connexion</h1>

		<?php if ($message !== ''): ?>
			<p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
		<?php endif; ?>

		<form method="post" action="connexion.php">
			<p>
				<label for="identifiant">Nom ou email</label><br>
				<input type="text" id="identifiant" name="identifiant" required autocomplete="username" placeholder="Ex. Anastasya ou email@example.com">
			</p>

			<p>
				<label for="mot_de_passe">Mot de passe</label><br>
				<input type="password" id="mot_de_passe" name="mot_de_passe" required autocomplete="current-password">
				<button type="button" class="toggle-password" id="toggle-password" aria-pressed="false">Afficher le mot de passe</button>
			</p>

			<button type="submit">Se connecter</button>
		</form>
	</main>
	<script>
		const motDePasse = document.getElementById('mot_de_passe');
		const boutonMotDePasse = document.getElementById('toggle-password');
		const formulaire = document.querySelector('form');

		window.addEventListener('pageshow', () => formulaire.reset());

		boutonMotDePasse.addEventListener('click', () => {
			const motDePasseVisible = motDePasse.type === 'text';
			motDePasse.type = motDePasseVisible ? 'password' : 'text';
			boutonMotDePasse.textContent = motDePasseVisible
				? 'Afficher le mot de passe'
				: 'Masquer le mot de passe';
			boutonMotDePasse.setAttribute('aria-pressed', String(!motDePasseVisible));
		});
	</script>
</body>
</html>
