<?php
// Traitement des actions (ajout/suppression favoris, suppression annonce)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_logged_in'])) {
    $action = $_POST['action'] ?? '';
    $listing_id = (int)($_POST['listing_id'] ?? 0);
    
    if ($action === 'toggle_favorite' && $listing_id > 0) {
        try {
            // Vérifier si l'annonce est déjà en favori
            $stmt = $pdo->prepare("SELECT id FROM favorite WHERE user_id = ? AND listing_id = ?");
            $stmt->execute([$_SESSION['user_id'], $listing_id]);
            
            if ($stmt->fetch()) {
                // Retirer des favoris
                $stmt = $pdo->prepare("DELETE FROM favorite WHERE user_id = ? AND listing_id = ?");
                $stmt->execute([$_SESSION['user_id'], $listing_id]);
                $_SESSION['success_message'] = "Annonce retirée des favoris";
            } else {
                // Ajouter aux favoris
                $stmt = $pdo->prepare("INSERT INTO favorite (user_id, listing_id) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $listing_id]);
                $_SESSION['success_message'] = "Annonce ajoutée aux favoris";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de la gestion des favoris";
        }
    }
    
    if ($action === 'delete_listing' && $listing_id > 0) {
        try {
            // Vérifier les droits de suppression
            $stmt = $pdo->prepare("SELECT user_id FROM listing WHERE id = ?");
            $stmt->execute([$listing_id]);
            $listing = $stmt->fetch();
            
            if ($listing && ($_SESSION['user_role'] === 'admin' || $listing['user_id'] == $_SESSION['user_id'])) {
                // Commencer une transaction pour supprimer l'annonce et ses favoris
                $pdo->beginTransaction();
                
                // Supprimer d'abord les favoris associés
                $stmt = $pdo->prepare("DELETE FROM favorite WHERE listing_id = ?");
                $stmt->execute([$listing_id]);
                
                // Puis supprimer l'annonce
                $stmt = $pdo->prepare("DELETE FROM listing WHERE id = ?");
                $stmt->execute([$listing_id]);
                
                $pdo->commit();
                $_SESSION['success_message'] = "Annonce supprimée avec succès";
            } else {
                $_SESSION['error_message'] = "Vous n'avez pas l'autorisation de supprimer cette annonce";
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error_message'] = "Erreur lors de la suppression";
        }
    }
    
    // Redirection pour éviter la resoumission du formulaire
    header('Location: ?page=main');
    exit();
}

// Récupérer les favoris de l'utilisateur connecté
$user_favorites = [];
if (isset($_SESSION['user_logged_in'])) {
    try {
        $stmt = $pdo->prepare("SELECT listing_id FROM favorite WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_favorites = array_column($stmt->fetchAll(), 'listing_id');
    } catch (PDOException $e) {
        // Ignorer l'erreur silencieusement
    }
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
    <!-- Section Maisons -->
    <section class="section" id="houses">
        <h2 class="section-title">Nos annonces de maisons</h2>
        <div class="annonces-grid">
            <?php 
            try {
                $stmt = $pdo->prepare("
                    SELECT l.*, pt.name AS property_type, tt.name AS transaction_type, u.email as owner_email
                    FROM listing l
                    JOIN propertyType pt ON l.property_type_id = pt.id
                    JOIN transactionType tt ON l.transaction_type_id = tt.id
                    JOIN user u ON l.user_id = u.id
                    WHERE pt.name = 'House'
                    ORDER BY l.created_at DESC
                    LIMIT 6
                ");
                $stmt->execute();
                $maisons = $stmt->fetchAll();
                
                foreach ($maisons as $maison): 
                    $is_favorite = in_array($maison['id'], $user_favorites);
                    $can_edit = isset($_SESSION['user_logged_in']) && ($_SESSION['user_role'] === 'admin' || $maison['user_id'] == $_SESSION['user_id']);
                ?>
                    <div class="annonce-card">
                        <img src="<?php echo $maison['image_url']; ?>" alt="<?php echo $maison['title']; ?>" class="annonce-image">
                        <div class="type-badge <?php echo strtolower($maison['transaction_type']) === 'rent' ? 'type-rent' : 'type-sale'; ?>">
                            <?php echo $maison['transaction_type']; ?>
                        </div>
                        
                        <!-- Correction : Actions en haut à GAUCHE -->
                        <?php if (isset($_SESSION['user_logged_in'])): ?>
                            <div style="position: absolute; top: 10px; left: 10px; display: flex; gap: 5px;">
                                <!-- Bouton favori -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_favorite">
                                    <input type="hidden" name="listing_id" value="<?php echo $maison['id']; ?>">
                                    <button type="submit" style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 5px;" title="<?php echo $is_favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                        <?php echo $is_favorite ? '❤️' : '🤍'; ?>
                                    </button>
                                </form>
                                
                                <!-- Boutons d'édition/suppression pour les propriétaires et admins -->
                                <?php if ($can_edit): ?>
                                    <a href="?page=edit-listing&id=<?php echo $maison['id']; ?>" 
                                       style="background: #007bff; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 12px;" title="Modifier">
                                        ✏️
                                    </a>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?');">
                                        <input type="hidden" name="action" value="delete_listing">
                                        <input type="hidden" name="listing_id" value="<?php echo $maison['id']; ?>">
                                        <button type="submit" 
                                                style="background: #dc3545; color: white; border: none; padding: 5px 8px; cursor: pointer; border-radius: 3px; font-size: 12px;" title="Supprimer">
                                            🗑️
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="annonce-content">
                            <div class="annonce-header">
                                <h3 class="annonce-titre"><?php echo htmlspecialchars($maison['title']); ?></h3>
                                <div class="annonce-prix"><?php echo number_format($maison['price'], 0, ',', ' '); ?>€</div>
                            </div>
                            <div class="annonce-localisation"><?php echo htmlspecialchars($maison['city']); ?></div>
                            <p class="annonce-description"><?php echo htmlspecialchars($maison['description']); ?></p>
                            <div style="font-size: 0.8em; color: #666; margin-top: 10px;">
                                Publié par: <?php echo htmlspecialchars($maison['owner_email']); ?>
                            </div>
                            <button class="contact-btn">Contact</button>
                        </div>
                    </div>
                <?php endforeach;
            } catch (PDOException $e) {
                echo "<p>Erreur lors du chargement des annonces de maisons</p>";
            }
            ?>
        </div>
    </section>

    <!-- Section Appartements -->
    <section class="section" id="apartments">
        <h2 class="section-title">Nos annonces d'appartements</h2>
        <div class="annonces-grid">
            <?php 
            try {
                $stmt = $pdo->prepare("
                    SELECT l.*, pt.name AS property_type, tt.name AS transaction_type, u.email as owner_email
                    FROM listing l
                    JOIN propertyType pt ON l.property_type_id = pt.id
                    JOIN transactionType tt ON l.transaction_type_id = tt.id
                    JOIN user u ON l.user_id = u.id
                    WHERE pt.name = 'Apartment'
                    ORDER BY l.created_at DESC
                    LIMIT 6
                ");
                $stmt->execute();
                $appartements = $stmt->fetchAll();
                
                foreach ($appartements as $appartement): 
                    $is_favorite = in_array($appartement['id'], $user_favorites);
                    $can_edit = isset($_SESSION['user_logged_in']) && ($_SESSION['user_role'] === 'admin' || $appartement['user_id'] == $_SESSION['user_id']);
                ?>
                    <div class="annonce-card">
                        <img src="<?php echo $appartement['image_url']; ?>" alt="<?php echo $appartement['title']; ?>" class="annonce-image">
                        <div class="type-badge <?php echo strtolower($appartement['transaction_type']) === 'rent' ? 'type-rent' : 'type-sale'; ?>">
                            <?php echo $appartement['transaction_type']; ?>
                        </div>
                        
                        <!-- Correction : Actions en haut à GAUCHE -->
                        <?php if (isset($_SESSION['user_logged_in'])): ?>
                            <div style="position: absolute; top: 10px; left: 10px; display: flex; gap: 5px;">
                                <!-- Bouton favori -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_favorite">
                                    <input type="hidden" name="listing_id" value="<?php echo $appartement['id']; ?>">
                                    <button type="submit" style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 5px;" title="<?php echo $is_favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                        <?php echo $is_favorite ? '❤️' : '🤍'; ?>
                                    </button>
                                </form>
                                
                                <!-- Boutons d'édition/suppression pour les propriétaires et admins -->
                                <?php if ($can_edit): ?>
                                    <a href="?page=edit-listing&id=<?php echo $appartement['id']; ?>" 
                                       style="background: #007bff; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 12px;" title="Modifier">
                                        ✏️
                                    </a>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?');">
                                        <input type="hidden" name="action" value="delete_listing">
                                        <input type="hidden" name="listing_id" value="<?php echo $appartement['id']; ?>">
                                        <button type="submit" 
                                                style="background: #dc3545; color: white; border: none; padding: 5px 8px; cursor: pointer; border-radius: 3px; font-size: 12px;" title="Supprimer">
                                            🗑️
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="annonce-content">
                            <div class="annonce-header">
                                <h3 class="annonce-titre"><?php echo htmlspecialchars($appartement['title']); ?></h3>
                                <div class="annonce-prix"><?php echo number_format($appartement['price'], 0, ',', ' '); ?>€</div>
                            </div>
                            <div class="annonce-localisation"><?php echo htmlspecialchars($appartement['city']); ?></div>
                            <p class="annonce-description"><?php echo htmlspecialchars($appartement['description']); ?></p>
                            <div style="font-size: 0.8em; color: #666; margin-top: 10px;">
                                Publié par: <?php echo htmlspecialchars($appartement['owner_email']); ?>
                            </div>
                            <button class="contact-btn">Contact</button>
                        </div>
                    </div>
                <?php endforeach;
            } catch (PDOException $e) {
                echo "<p>Erreur lors du chargement des annonces d'appartements</p>";
            }
            ?>
        </div>
    </section>
</main>