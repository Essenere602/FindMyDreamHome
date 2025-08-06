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
            <li><a href="?page=main#houses">Maisons</a></li>
            <li><a href="?page=main#apartments">Appartements</a></li>
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                <li><a href="?page=add-listing" class="login-btn">Ajouter</a></li>
                <li><a href="?page=logout" class="login-btn">Déconnexion (<?php echo htmlspecialchars($_SESSION['user_email']); ?>)</a></li>
            <?php else: ?>
                <li><a href="?page=register" class="login-btn">Inscription</a></li>
                <li><a href="?page=login" class="login-btn">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>