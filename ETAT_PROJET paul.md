# État du mini-projet

## Ce qui a été mis en place

- Formulaire de connexion dans `index.php` avec identifiant (nom ou e-mail), mot de passe et bouton pour afficher/masquer le mot de passe.
- Traitement de connexion dans `connexion.php` : recherche du compte dans MySQL, validation du mot de passe via `password_verify()`, création de session et redirection vers `tableauDeBord.php`.
- Tableau de bord protégé par session valide et affichant le nom de l'utilisateur connecté.
- Vidage des champs du formulaire à l'affichage de la page de connexion, y compris après un retour depuis l'historique du navigateur.
- Correction du dump SQL : retrait du commentaire SQL invalide et de l'`UPDATE` placeholder.

## Vérifications effectuées

- Vérification de syntaxe PHP : `php -l Mini_projet/index.php` → aucune erreur.
- Vérification du hash avec PHP : `password_verify()` confirme que `Projet2026!` correspond au mot de passe hashé du compte `Anastasya`.
- Interrogation de la base `miniProjet` : le hash du compte actif a bien été aligné avec le mot de passe de test.
- Validation du compte de test dans la base MySQL réelle : le mot de passe `Projet2026!` est bien accepté.

## Comptes de test

- Identifiant : `Anastasya`
- Mot de passe : `Projet2026!`
- Le mot de passe est stocké en hash dans `miniProjet.sql` et dans la base MySQL active.

## État actuel

Le flux de connexion côté serveur est validé : le compte de test fonctionne avec le hash présent dans la base, la session est créée et le redirectionnement vers le tableau de bord est bien le chemin attendu.

La configuration MySQL locale utilisée pour le développement a été alignée sur le compte de test afin de permettre la validation fonctionnelle.

## À faire ensuite

1. Tester le parcours complet dans le navigateur avec les identifiants ci-dessus et confirmer l'arrivée sur le tableau de bord.
2. Vérifier visuellement le rendu du tableau de bord après connexion.
3. Pour un déploiement réel, remplacer les identifiants MySQL écrits directement dans `connexion.php` par une configuration non versionnée.

Le dernier point restant est la validation manuelle du parcours HTTP complet depuis le navigateur.
