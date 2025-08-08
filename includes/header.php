<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find My Dream Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar">
        <div class="logo">FindMyDreamHome</div>
        <ul class="nav-links">
            <li><a href="?page=main">Accueil</a></li>
            <li><a href="?page=houses">Maisons</a></li>
            <li><a href="?page=apartments">Appartements</a></li>
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                <li><a href="?page=favorites" class="login-btn">Mes Favoris</a></li>
                <!-- Correction : Limiter l'accès au bouton "Ajouter" aux agents et admins uniquement -->
                <?php if (in_array($_SESSION['user_role'], ['agent', 'admin'])): ?>
                    <li><a href="?page=add-listing" class="login-btn">Ajouter</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="?page=admin" class="login-btn">Admin</a></li>
                <?php endif; ?>
                <li><a href="?page=logout" class="login-btn">Déconnexion (<?php echo htmlspecialchars($_SESSION['user_email']); ?>)</a></li>
            <?php else: ?>
                <li><a href="?page=register" class="login-btn">Inscription</a></li>
                <li><a href="?page=login" class="login-btn">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>