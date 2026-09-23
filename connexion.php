<?php
// Paramètres de connexion à la base de données.
$serveur = 'localhost';
$baseDeDonnees = 'mini_projet';
$utilisateur = 'root';
$motDePasseBase = '';

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
	$pdo = new PDO(
		'mysql:host=' . $serveur . ';dbname=' . $baseDeDonnees . ';charset=utf8mb4',
		$utilisateur,
		$motDePasseBase,
		[
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]
	);

	$requete = $pdo->prepare(
		'SELECT id, identifiant, mot_de_passe FROM utilisateurs WHERE identifiant = :identifiant LIMIT 1'
	);
	$requete->execute(['identifiant' => $identifiant]);
	$utilisateurTrouve = $requete->fetch();

	if ($utilisateurTrouve && password_verify($motDePasse, $utilisateurTrouve['mot_de_passe'])) {
		session_start();
		$_SESSION['utilisateur_id'] = $utilisateurTrouve['id'];
		$_SESSION['identifiant'] = $utilisateurTrouve['identifiant'];
		header('Location: index.php?connexion=ok');
		exit;
	}

	header('Location: index.php?erreur=' . urlencode('Identifiant ou mot de passe incorrect.'));
} catch (PDOException $erreur) {
	// Ne pas afficher les détails de la base de données à l’utilisateur.
	header('Location: index.php?erreur=' . urlencode('La connexion à la base de données a échoué.'));
}