<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ?page=login');
    exit();
}

// Traitement des actions (suppression favoris, suppression annonce)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_logged_in'])) {
    $action = $_POST['action'] ?? '';
    $listing_id = (int)($_POST['listing_id'] ?? 0);
    
    if ($action === 'remove_favorite' && $listing_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM favorite WHERE user_id = ? AND listing_id = ?");
            $stmt->execute([$_SESSION['user_id'], $listing_id]);
            $_SESSION['success_message'] = "Annonce retirée des favoris";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de la suppression du favori";
        }
    }
    
    if ($action === 'delete_listing' && $listing_id > 0) {
        try {
            // Vérifier les droits de suppression
            $stmt = $pdo->prepare("SELECT user_id FROM listing WHERE id = ?");
            $stmt->execute([$listing_id]);
            $listing = $stmt->fetch();
            
            if ($listing && ($_SESSION['user_role'] === 'admin' || $listing['user_id'] == $_SESSION['user_id'])) {
                // Supprimer l'annonce
                $stmt = $pdo->prepare("DELETE FROM listing WHERE id = ?");
                $stmt->execute([$listing_id]);
                $_SESSION['success_message'] = "Annonce supprimée avec succès";
            } else {
                $_SESSION['error_message'] = "Vous n'avez pas l'autorisation de supprimer cette annonce";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de la suppression";
        }
    }
    
    // Redirection pour éviter la resoumission du formulaire
    header('Location: ?page=favorites');
    exit();
}

// Récupérer les favoris de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT l.*, pt.name AS property_type, tt.name AS transaction_type, u.email as owner_email
        FROM listing l
        JOIN propertyType pt ON l.property_type_id = pt.id
        JOIN transactionType tt ON l.transaction_type_id = tt.id
        JOIN user u ON l.user_id = u.id
        JOIN favorite f ON l.id = f.listing_id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $favorites = $stmt->fetchAll();
} catch (PDOException $e) {
    $favorites = [];
    $_SESSION['error_message'] = "Erreur lors du chargement des favoris";
}
?>

<!-- Messages de succès/erreur -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px; text-align: center;">
        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px; text-align: center;">
        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<main class="main-content">
    <section class="section">
        <h2 class="section-title">Mes Favoris (<?php echo count($favorites); ?> annonces)</h2>
        
        <?php if (empty($favorites)): ?>
            <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 10px; margin: 20px;">
                <h3 style="color: #6c757d;">Aucun favori pour le moment</h3>
                <p style="color: #6c757d; margin-bottom: 20px;">Parcourez les annonces et ajoutez celles qui vous intéressent en cliquant sur le cœur !</p>
                <a href="?page=main" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    Voir les annonces
                </a>
            </div>
        <?php else: ?>
            <div class="annonces-grid">
                <?php foreach ($favorites as $favorite): 
                    $can_edit = $_SESSION['user_role'] === 'admin' || $favorite['user_id'] == $_SESSION['user_id'];
                ?>
                    <div class="annonce-card">
                        <img src="<?php echo $favorite['image_url']; ?>" alt="<?php echo $favorite['title']; ?>" class="annonce-image">
                        <div class="type-badge <?php echo strtolower($favorite['transaction_type']) === 'rent' ? 'type-rent' : 'type-sale'; ?>">
                            <?php echo $favorite['transaction_type']; ?>
                        </div>
                        
                        <!-- Correction : Actions en haut à GAUCHE -->
                        <div style="position: absolute; top: 10px; left: 10px; display: flex; gap: 5px;">
                            <!-- Bouton retirer des favoris -->
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Retirer cette annonce de vos favoris ?');">
                                <input type="hidden" name="action" value="remove_favorite">
                                <input type="hidden" name="listing_id" value="<?php echo $favorite['id']; ?>">
                                <button type="submit" style="background: #dc3545; color: white; border: none; font-size: 16px; cursor: pointer; padding: 5px 8px; border-radius: 3px;" title="Retirer des favoris">
                                    ❤️
                                </button>
                            </form>
                            
                            <!-- Boutons d'édition/suppression pour les propriétaires et admins -->
                            <?php if ($can_edit): ?>
                                <a href="?page=edit-listing&id=<?php echo $favorite['id']; ?>" 
                                   style="background: #007bff; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 12px;" title="Modifier">
                                    ✏️
                                </a>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?');">
                                    <input type="hidden" name="action" value="delete_listing">
                                    <input type="hidden" name="listing_id" value="<?php echo $favorite['id']; ?>">
                                    <button type="submit" 
                                            style="background: #dc3545; color: white; border: none; padding: 5px 8px; cursor: pointer; border-radius: 3px; font-size: 12px;" title="Supprimer">
                                        🗑️
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="annonce-content">
                            <div class="annonce-header">
                                <h3 class="annonce-titre"><?php echo htmlspecialchars($favorite['title']); ?></h3>
                                <div class="annonce-prix"><?php echo number_format($favorite['price'], 0, ',', ' '); ?>€</div>
                            </div>
                            <div class="annonce-localisation"><?php echo htmlspecialchars($favorite['city']); ?></div>
                            <p class="annonce-description"><?php echo htmlspecialchars($favorite['description']); ?></p>
                            <div style="font-size: 0.8em; color: #666; margin-top: 10px;">
                                Type: <?php echo htmlspecialchars($favorite['property_type']); ?> | 
                                Publié par: <?php echo htmlspecialchars($favorite['owner_email']); ?>
                            </div>
                            <button class="contact-btn">Contact</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>