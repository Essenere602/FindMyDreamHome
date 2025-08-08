<?php
// Démarrer la session
session_start();

// Inclure connexion à la base données
require_once 'config/database.php';

// Inclure le header
include 'includes/header.php';

// Router
$page = $_GET['page'] ?? 'main';

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
    case 'edit-listing':
        include 'includes/EditListing.php';
        break;
    case 'delete-listing':
        include 'includes/DeleteListing.php';
        break;
    case 'favorites':
        include 'includes/Favorites.php';
        break;
    case 'toggle-favorite':
        include 'includes/ToggleFavorite.php';
        break;
    case 'houses':
        include 'includes/Houses.php';
        break;
    case 'apartments':
        include 'includes/Apartments.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: ?page=main');
        exit();
    default:
        include 'includes/main.php';
}

include 'includes/footer.php';
?>