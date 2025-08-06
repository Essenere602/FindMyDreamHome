<?php
// Démarrer la session
session_start();

// Inclure les données
require_once 'data/data.php';

// Inclure le header
include 'includes/header.php';

// Vérifier si un paramètre "page" est présent dans l'URL
$page = isset($_GET['page']) ? $_GET['page'] : 'main';

switch ($page) {
    case 'register':
        include 'includes/register.php';
        break;

    case 'login':
        include 'includes/login.php';
        break;

    case 'add-listing':
        include 'includes/AddListing.php';
        break;

    case 'logout':
        // Déconnexion de l'utilisateur
        session_destroy();
        header('Location: ?page=main');
        exit();
        break;

    case 'main':
    default:
        include 'includes/main.php';
        break;
}

// Inclure le footer
include 'includes/footer.php';
?>