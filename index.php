Paul <?php
// Configuration générale du site : modifiez uniquement cette zone pour commencer.
$site = [
	'name' => 'Mon projet',
	'tagline' => 'Une phrase courte pour présenter votre activité.',
	'description' => 'Présentez ici votre projet, votre entreprise ou votre portfolio avec quelques mots simples.',
	'email' => 'bonjour@exemple.fr',
];

// Liens affichés dans le menu principal.
$navigation = [
	['label' => 'Accueil', 'href' => '#accueil'],
	['label' => 'À propos', 'href' => '#a-propos'],
	['label' => 'Services', 'href' => '#services'],
	['label' => 'Contact', 'href' => '#contact'],
];

// Cartes présentées dans la section « Services ».
$services = [
	['title' => 'Service principal', 'text' => 'Décrivez ici votre première offre ou fonctionnalité.'],
	['title' => 'Accompagnement', 'text' => 'Expliquez comment vous aidez vos visiteurs ou clients.'],
	['title' => 'Solution sur mesure', 'text' => 'Ajoutez une troisième proposition importante.'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?= htmlspecialchars($site['description'], ENT_QUOTES, 'UTF-8') ?>">
	<title><?= htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8') ?></title>
	<style>
		/* Variables principales : couleurs et largeur maximale du contenu. */
		:root {
			--couleur-principale: #1f5c55;
			--couleur-accent: #e6a34b;
			--couleur-fond: #f6f4ef;
			--couleur-texte: #1d2b2a;
			--largeur-contenu: 1120px;
		}

		* { box-sizing: border-box; }
		html { scroll-behavior: smooth; }
		body {
			margin: 0;
			color: var(--couleur-texte);
			background: var(--couleur-fond);
			font-family: Georgia, "Times New Roman", serif;
			line-height: 1.6;
		}

		a { color: inherit; }
		.conteneur { width: min(100% - 2rem, var(--largeur-contenu)); margin: 0 auto; }
		/* En-tête et navigation */
		.entete {
			border-bottom: 1px solid rgba(29, 43, 42, .12);
			background: rgba(246, 244, 239, .94);
		}
		.barre-navigation {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 2rem;
			min-height: 76px;
		}
		.logo { color: var(--couleur-principale); font-size: 1.4rem; font-weight: bold; text-decoration: none; }
		.navigation { display: flex; flex-wrap: wrap; gap: 1.25rem; }
		.navigation a { text-decoration: none; }
		.navigation a:hover { color: var(--couleur-principale); }

		/* Section d’accueil */
		.hero { padding: 7rem 0 6rem; background: linear-gradient(120deg, #e4eee8, var(--couleur-fond) 58%); }
		.hero-contenu { max-width: 720px; }
		.surtitre { color: var(--couleur-principale); font-weight: bold; letter-spacing: .08em; text-transform: uppercase; }
		h1, h2, h3 { line-height: 1.15; margin-top: 0; }
		h1 { max-width: 780px; margin-bottom: 1.25rem; font-size: clamp(2.8rem, 7vw, 5.5rem); }
		h2 { margin-bottom: 1rem; font-size: clamp(2rem, 4vw, 3.25rem); }
		h3 { margin-bottom: .75rem; font-size: 1.35rem; }
		.hero p { max-width: 620px; font-size: 1.2rem; }
		.actions { display: flex; flex-wrap: wrap; gap: .85rem; margin-top: 2rem; }
		.bouton { display: inline-block; padding: .8rem 1.25rem; border: 2px solid var(--couleur-principale); border-radius: 3px; text-decoration: none; }
		.bouton-principal { color: #fff; background: var(--couleur-principale); }
		.bouton-secondaire { color: var(--couleur-principale); }

		/* Sections et cartes de contenu */
		.section { padding: 5rem 0; }
		.section-alt { background: #fff; }
		.intro-section { max-width: 650px; margin-bottom: 2.5rem; }
		.grille-services { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
		.carte { padding: 1.5rem; border-top: 4px solid var(--couleur-accent); background: var(--couleur-fond); }
		.contact { color: #fff; background: var(--couleur-principale); }
		.contact .bouton { border-color: #fff; }
		.pied-de-page { padding: 2rem 0; font-size: .95rem; }
		.pied-de-page .conteneur { display: flex; justify-content: space-between; gap: 1rem; }

		/* Adaptation de la mise en page sur les petits écrans */
		@media (max-width: 700px) {
			.barre-navigation, .pied-de-page .conteneur { align-items: flex-start; flex-direction: column; gap: .75rem; padding: 1rem 0; }
			.hero { padding: 5rem 0 4rem; }
			.section { padding: 3.5rem 0; }
			.grille-services { grid-template-columns: 1fr; }
		}
	</style>
</head>
<body>
	<!-- En-tête : nom du site et navigation principale. -->
	<header class="entete">
		<div class="conteneur barre-navigation">
			<a class="logo" href="#accueil"><?= htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8') ?></a>
			<nav class="navigation" aria-label="Navigation principale">
				<?php foreach ($navigation as $element): ?>
					<a href="<?= htmlspecialchars($element['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($element['label'], ENT_QUOTES, 'UTF-8') ?></a>
				<?php endforeach; ?>
			</nav>
		</div>
	</header>

	<main>
		<!-- Présentation principale du projet. -->
		<section id="accueil" class="hero">
			<div class="conteneur hero-contenu">
				<p class="surtitre"><?= htmlspecialchars($site['tagline'], ENT_QUOTES, 'UTF-8') ?></p>
				<h1>Donnez une présence claire à votre projet.</h1>
				<p><?= htmlspecialchars($site['description'], ENT_QUOTES, 'UTF-8') ?></p>
				<div class="actions">
					<a class="bouton bouton-principal" href="#contact">Nous contacter</a>
					<a class="bouton bouton-secondaire" href="#a-propos">Découvrir le projet</a>
				</div>
			</div>
		</section>

		<!-- Présentation de l’activité ou de l’équipe. -->
		<section id="a-propos" class="section">
			<div class="conteneur intro-section">
				<p class="surtitre">À propos</p>
				<h2>Un espace simple à faire évoluer.</h2>
				<p>Remplacez ce texte par votre histoire, vos valeurs ou les informations essentielles que vos visiteurs doivent connaître.</p>
			</div>
		</section>

		<!-- Les cartes sont générées automatiquement depuis $services. -->
		<section id="services" class="section section-alt">
			<div class="conteneur">
				<div class="intro-section">
					<p class="surtitre">Services</p>
					<h2>Ce que vous proposez</h2>
				</div>
				<div class="grille-services">
					<?php foreach ($services as $service): ?>
						<article class="carte">
							<h3><?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?></h3>
							<p><?= htmlspecialchars($service['text'], ENT_QUOTES, 'UTF-8') ?></p>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<!-- Point de contact principal du site. -->
		<section id="contact" class="section contact">
			<div class="conteneur intro-section">
				<p class="surtitre">Contact</p>
				<h2>Parlons de votre projet.</h2>
				<p>Remplacez l’adresse ci-dessous par votre adresse professionnelle.</p>
				<a class="bouton" href="mailto:<?= htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8') ?></a>
			</div>
		</section>
	</main>

	<!-- Pied de page avec l’année actuelle générée par PHP. -->
	<footer class="pied-de-page">
		<div class="conteneur">
			<span>&copy; <?= date('Y') ?> <?= htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8') ?></span>
			<span>Site réalisé en PHP et HTML</span>
		</div>
	</footer>
</body>
</html>
