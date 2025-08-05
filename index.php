<?php
// Inclure les données
require_once 'data/data.php';

// Inclure le header
include 'includes/header.php';

// Vérifier si un paramètre "page" est présent dans l'URL
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'register':
        include 'includes/register.php';
        break;

    case 'login':
        include 'includes/login.php';
        break;

    case 'home':
    default:
        include 'includes/main.php';
        break;
}

// Inclure le footer
include 'includes/footer.php';
?>