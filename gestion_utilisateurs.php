<?php
session_start();

// 1. Sécurité : Vérifier que l'utilisateur est connecté ET qu'il a le rôle admin
if (empty($_SESSION['connecte']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// 2. Connexion à la base de données
require_once 'db.php';

// Récupération des messages flash
$messageSucces = $_SESSION['message_succes'] ?? '';
$messageErreur = $_SESSION['message_erreur'] ?? '';
unset($_SESSION['message_succes'], $_SESSION['message_erreur']);

// 3A. Traitement : Création d'un nouvel utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_utilisateur'])) {
    $nouveauNom = trim($_POST['nom'] ?? '');
    $nouveauEmail = trim($_POST['email'] ?? '');
    $nouveauRole = $_POST['role'] ?? 'eleve';
    $nouveauMdp = $_POST['mot_de_passe'] ?? '';
    $uidBadge = trim($_POST['uid_badge'] ?? '');

    // On transforme l'email vide en NULL pour la base de données
    $emailSql = $nouveauEmail === '' ? null : $nouveauEmail;

    if ($nouveauNom !== '' && $nouveauMdp !== '') {
        $hashMdp = password_hash($nouveauMdp, PASSWORD_DEFAULT);
        
        try {
            $pdo->beginTransaction();

            $requeteInsertUser = $pdo->prepare('INSERT INTO Utilisateur (nom, email, mot_de_passe, role) VALUES (:nom, :email, :mot_de_passe, :role)');
            $requeteInsertUser->execute([
                'nom' => $nouveauNom,
                'email' => $emailSql,
                'mot_de_passe' => $hashMdp,
                'role' => $nouveauRole
            ]);
            
            $nouvelIdUser = $pdo->lastInsertId();

            if ($uidBadge !== '') {
                $requeteInsertBadge = $pdo->prepare('INSERT INTO Badge (uid_badge, id_user) VALUES (:uid_badge, :id_user)');
                $requeteInsertBadge->execute([
                    'uid_badge' => $uidBadge,
                    'id_user' => $nouvelIdUser
                ]);
            }

            $pdo->commit();
            $_SESSION['message_succes'] = "L'utilisateur $nouveauNom a été ajouté avec succès.";

        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $_SESSION['message_erreur'] = "Erreur : Ce numéro de badge existe déjà dans la base de données.";
            } else {
                $_SESSION['message_erreur'] = "Erreur lors de la création : " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['message_erreur'] = "Veuillez remplir le nom et le mot de passe.";
    }
    
    header('Location: gestion_utilisateurs.php');
    exit;
}

// 3B. Traitement : Modification d'un utilisateur existant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_utilisateur'])) {
    $idModif = (int)$_POST['id_user_modifie'];
    $nomModif = trim($_POST['nom'] ?? '');
    $emailModif = trim($_POST['email'] ?? '');
    $roleModif = $_POST['role'] ?? 'eleve';
    $mdpModif = $_POST['mot_de_passe'] ?? '';

    $emailSql = $emailModif === '' ? null : $emailModif;

    if ($idModif > 0 && $nomModif !== '') {
        try {
            // Si l'administrateur a rempli le champ mot de passe, on le met à jour
            if ($mdpModif !== '') {
                $hashMdp = password_hash($mdpModif, PASSWORD_DEFAULT);
                $requeteUpdate = $pdo->prepare('UPDATE Utilisateur SET nom = :nom, email = :email, role = :role, mot_de_passe = :mdp WHERE id_user = :id');
                $requeteUpdate->execute(['nom' => $nomModif, 'email' => $emailSql, 'role' => $roleModif, 'mdp' => $hashMdp, 'id' => $idModif]);
            } else {
                // Sinon, on met à jour uniquement les autres informations
                $requeteUpdate = $pdo->prepare('UPDATE Utilisateur SET nom = :nom, email = :email, role = :role WHERE id_user = :id');
                $requeteUpdate->execute(['nom' => $nomModif, 'email' => $emailSql, 'role' => $roleModif, 'id' => $idModif]);
            }
            
            // Si on modifie son PROPRE compte, on met à jour la variable de session pour éviter les bugs
            if ($idModif === (int)$_SESSION['id_user']) {
                $_SESSION['nom'] = $nomModif;
                $_SESSION['role'] = $roleModif;
            }

            $_SESSION['message_succes'] = "L'utilisateur $nomModif a été modifié avec succès.";
        } catch (PDOException $e) {
            $_SESSION['message_erreur'] = "Erreur lors de la modification : " . $e->getMessage();
        }
    } else {
        $_SESSION['message_erreur'] = "Le nom ne peut pas être vide.";
    }
    
    header('Location: gestion_utilisateurs.php');
    exit;
}

// 3C. Traitement : Attribution d'un badge à un utilisateur existant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attribuer_badge'])) {
    $idUserExistant = (int)($_POST['id_user_existant'] ?? 0);
    $nouvelUidBadge = trim($_POST['nouvel_uid_badge'] ?? '');

    if ($idUserExistant > 0 && $nouvelUidBadge !== '') {
        try {
            $requeteInsertBadge = $pdo->prepare('INSERT INTO Badge (uid_badge, id_user) VALUES (:uid_badge, :id_user)');
            $requeteInsertBadge->execute([
                'uid_badge' => $nouvelUidBadge,
                'id_user' => $idUserExistant
            ]);
            $_SESSION['message_succes'] = "Le badge $nouvelUidBadge a été attribué avec succès.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['message_erreur'] = "Erreur : Ce numéro de badge existe déjà et ne peut pas être réattribué.";
            } else {
                $_SESSION['message_erreur'] = "Erreur lors de l'attribution : " . $e->getMessage();
            }
        }
    }
    header('Location: gestion_utilisateurs.php');
    exit;
}

// 3D. Traitement : Suppression d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_utilisateur'])) {
    $idUserASupprimer = (int)($_POST['id_user_a_supprimer'] ?? 0);

    if ($idUserASupprimer === (int)$_SESSION['id_user']) {
        $_SESSION['message_erreur'] = "Action refusée : Vous ne pouvez pas supprimer votre propre compte.";
    } elseif ($idUserASupprimer > 0) {
        try {
            $requeteDelete = $pdo->prepare('DELETE FROM Utilisateur WHERE id_user = :id_user');
            $requeteDelete->execute(['id_user' => $idUserASupprimer]);
            $_SESSION['message_succes'] = "L'utilisateur a été supprimé avec succès.";
        } catch (PDOException $e) {
            $_SESSION['message_erreur'] = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
    header('Location: gestion_utilisateurs.php');
    exit;
}

// 4. Mode Édition : Vérifier si on veut modifier un utilisateur en particulier
$idUtilisateurAEditer = (int)($_GET['edit'] ?? 0);
$utilisateurAEditer = null;

if ($idUtilisateurAEditer > 0) {
    $reqEdit = $pdo->prepare('SELECT id_user, nom, email, role FROM Utilisateur WHERE id_user = :id');
    $reqEdit->execute(['id' => $idUtilisateurAEditer]);
    $utilisateurAEditer = $reqEdit->fetch();
}

// 5. Récupération de la liste des utilisateurs avec leurs badges ET leur dernier passage
try {
    // Jointure complexe : Utilisateur -> Badge -> Acces pour récupérer la date la plus récente (MAX)
    $requeteSelect = $pdo->query('
        SELECT 
            u.id_user, u.nom, u.email, u.role, 
            GROUP_CONCAT(DISTINCT b.uid_badge SEPARATOR ", ") as badges,
            MAX(a.date_heure) as dernier_passage
        FROM Utilisateur u 
        LEFT JOIN Badge b ON u.id_user = b.id_user 
        LEFT JOIN Acces a ON b.id_badge = a.id_badge 
        GROUP BY u.id_user 
        ORDER BY u.id_user DESC
    ');
    $utilisateurs = $requeteSelect->fetchAll();
} catch (PDOException $e) {
    die("Erreur de récupération : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="styleprojet.css">
    <style>
        .admin-main { max-width: 1000px; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.95rem; }
        th, td { border: 1px solid #b8c0cc; padding: 0.5rem; text-align: left; }
        th { background: #1f5f99; color: white; }
        
        .message-succes { background: #e6f5e9; padding: 10px; border: 1px solid #2e7d32; color: #2e7d32; border-radius: 4px; margin-bottom: 1rem; }
        .message-erreur { background: #ffebee; padding: 10px; border: 1px solid #c62828; color: #c62828; border-radius: 4px; margin-bottom: 1rem; }
        
        select { width: 100%; padding: 0.7rem; border: 1px solid #b8c0cc; border-radius: 4px; font: inherit; }
        .info-bulle { font-size: 0.85rem; color: #555; }
        hr.separateur { border: 0; border-top: 1px solid #d9dee5; margin: 2rem 0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        
        .btn-action { display: inline-block; padding: 0.4rem 0.6rem; font-size: 0.9rem; text-decoration: none; border-radius: 4px; border: 1px solid transparent; cursor: pointer; }
        .btn-editer { background: #f57c00; color: white; margin-bottom: 4px; }
        .btn-editer:hover { background: #e65100; color: white; }
        .btn-supprimer { background: #c62828; color: white; border: none; width: 100%; }
        .btn-supprimer:hover { background: #b71c1c; }

        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; gap: 0; } }
    </style>
</head>
<body>
    <main class="admin-main">
        <h1>Administration</h1>
        <p><a href="tableauDeBord.php" style="color: #1f5f99; text-decoration: none;">← Retour au tableau de bord</a></p>

        <?php if ($messageSucces !== ''): ?>
            <div class="message-succes"><?= htmlspecialchars($messageSucces, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($messageErreur !== ''): ?>
            <div class="message-erreur"><?= htmlspecialchars($messageErreur, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($utilisateurAEditer): ?>
            <!-- VUE ÉDITION -->
            <div style="background: #eef6ff; padding: 1.5rem; border: 1px solid #1f5f99; border-radius: 4px;">
                <h2>Modifier l'utilisateur : <?= htmlspecialchars($utilisateurAEditer['nom']) ?></h2>
                <form method="post" action="gestion_utilisateurs.php">
                    <input type="hidden" name="id_user_modifie" value="<?= (int)$utilisateurAEditer['id_user'] ?>">
                    <div class="form-grid">
                        <div>
                            <p>
                                <label for="nom_modif">Nom *</label><br>
                                <input type="text" id="nom_modif" name="nom" value="<?= htmlspecialchars($utilisateurAEditer['nom'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </p>
                            <p>
                                <label for="email_modif">Email</label><br>
                                <input type="email" id="email_modif" name="email" value="<?= htmlspecialchars((string)$utilisateurAEditer['email'], ENT_QUOTES, 'UTF-8') ?>">
                            </p>
                        </div>
                        <div>
                            <p>
                                <label for="role_modif">Rôle *</label><br>
                                <select id="role_modif" name="role" required>
                                    <option value="eleve" <?= $utilisateurAEditer['role'] === 'eleve' ? 'selected' : '' ?>>Élève</option>
                                    <option value="admin" <?= $utilisateurAEditer['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                </select>
                            </p>
                            <p>
                                <label for="mdp_modif">Nouveau mot de passe</label><br>
                                <input type="password" id="mdp_modif" name="mot_de_passe" placeholder="Laisser vide pour ne pas le changer">
                            </p>
                        </div>
                    </div>
                    <button type="submit" name="modifier_utilisateur" style="width: auto; margin-top: 1rem; padding: 0.8rem 1.5rem;">Enregistrer les modifications</button>
                    <a href="gestion_utilisateurs.php" style="display:inline-block; margin-left: 1rem; color:#1f5f99;">Annuler</a>
                </form>
            </div>
            <hr class="separateur">
        <?php else: ?>
            <!-- VUE NORMALE (Création / Ajout Badge) -->
            <div class="form-grid">
                <div>
                    <h2>Créer un utilisateur</h2>
                    <form method="post" action="gestion_utilisateurs.php">
                        <p>
                            <label for="nom">Nom *</label><br>
                            <input type="text" id="nom" name="nom" required>
                        </p>
                        <p>
                            <label for="email">Email</label><br>
                            <input type="email" id="email" name="email">
                        </p>
                        <p>
                            <label for="mot_de_passe">Mot de passe *</label><br>
                            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                        </p>
                        <p>
                            <label for="role">Rôle *</label><br>
                            <select id="role" name="role" required>
                                <option value="eleve">Élève</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </p>
                        <p>
                            <label for="uid_badge">UID du badge (Optionnel)</label><br>
                            <input type="text" id="uid_badge" name="uid_badge" placeholder="Ex: 2A:8E:BF:24">
                        </p>
                        <button type="submit" name="ajouter_utilisateur">Créer l'utilisateur</button>
                    </form>
                </div>

                <div>
                    <h2>Attribuer un badge</h2>
                    <form method="post" action="gestion_utilisateurs.php">
                        <p>
                            <label for="id_user_existant">Utilisateur existant *</label><br>
                            <select id="id_user_existant" name="id_user_existant" required>
                                <option value="">-- Sélectionner --</option>
                                <?php foreach ($utilisateurs as $user): ?>
                                    <option value="<?= (int)$user['id_user'] ?>">
                                        <?= htmlspecialchars($user['nom'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        <p>
                            <label for="nouvel_uid_badge">UID du badge *</label><br>
                            <input type="text" id="nouvel_uid_badge" name="nouvel_uid_badge" required placeholder="Ex: 99:9F:62:C2">
                        </p>
                        <button type="submit" name="attribuer_badge" style="margin-top: 10px;">Attribuer le badge</button>
                    </form>
                </div>
            </div>
            <hr class="separateur">
        <?php endif; ?>

        <h2>Liste des utilisateurs actuels</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Badge(s) UID</th>
                    <th>Dernier passage</th>
                    <th style="width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $user): ?>
                    <tr>
                        <td><?= (int)$user['id_user'] ?></td>
                        <td><?= htmlspecialchars($user['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($user['email'] ?? 'Non défini', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($user['badges'] ?? 'Aucun', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php 
                            if ($user['dernier_passage']) {
                                // Formate la date au format français (Jour/Mois/Année Heure:Minute)
                                echo htmlspecialchars(date('d/m/Y H:i', strtotime($user['dernier_passage'])));
                            } else {
                                echo '<em>Jamais</em>';
                            }
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="gestion_utilisateurs.php?edit=<?= (int)$user['id_user'] ?>" class="btn-action btn-editer" style="display: block; width: 100%; box-sizing: border-box;">Éditer</a>
                            
                            <form method="post" action="gestion_utilisateurs.php" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer l\'utilisateur <?= htmlspecialchars(addslashes($user['nom'])) ?> ?');">
                                <input type="hidden" name="id_user_a_supprimer" value="<?= (int)$user['id_user'] ?>">
                                <button type="submit" name="supprimer_utilisateur" class="btn-action btn-supprimer" <?= ((int)$user['id_user'] === (int)$_SESSION['id_user']) ? 'disabled style="opacity: 0.5; cursor: not-allowed;" title="Impossible"' : '' ?>>
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>