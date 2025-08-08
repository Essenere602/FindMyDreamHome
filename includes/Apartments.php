<?php
// Configuration de la pagination
$listings_per_page = 12;
$current_page = max(1, (int)($_GET['p'] ?? 1)); 

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
    header('Location: ?page=apartments&p=' . $current_page);
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

// Compter le nombre total d'appartements
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM listing l
        JOIN propertyType pt ON l.property_type_id = pt.id
        WHERE pt.name = 'Apartment'
    ");
    $stmt->execute();
    $total_listings = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $total_listings = 0;
}

// Calculer le nombre total de pages
$total_pages = max(1, ceil($total_listings / $listings_per_page));

// Vérifier si la page demandée est valide
if ($current_page > $total_pages) {
    header('Location: ?page=apartments&p=1');
    exit();
}

// Calculer l'offset
$offset = ($current_page - 1) * $listings_per_page;

// Récupérer les appartements pour la page actuelle
try {
    $stmt = $pdo->prepare("
        SELECT l.*, pt.name AS property_type, tt.name AS transaction_type, u.email as owner_email
        FROM listing l
        JOIN propertyType pt ON l.property_type_id = pt.id
        JOIN transactionType tt ON l.transaction_type_id = tt.id
        JOIN user u ON l.user_id = u.id
        WHERE pt.name = 'Apartment'
        ORDER BY l.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $listings_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $apartments = $stmt->fetchAll();
} catch (PDOException $e) {
    $apartments = [];
    $_SESSION['error_message'] = "Erreur lors du chargement des annonces";
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
        <h2 class="section-title">
            Nos annonces d'appartements 
            (<?php echo $total_listings; ?> annonces - Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?>)
        </h2>
        
        <?php if (empty($apartments)): ?>
            <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 10px; margin: 20px;">
                <h3 style="color: #6c757d;">Aucun appartement disponible</h3>
                <p style="color: #6c757d; margin-bottom: 20px;">Il n'y a actuellement aucune annonce d'appartement à afficher.</p>
                <a href="?page=main" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    Retour à l'accueil
                </a>
            </div>
        <?php else: ?>
            <div class="annonces-grid">
                <?php foreach ($apartments as $apartment): 
                    $is_favorite = in_array($apartment['id'], $user_favorites);
                    $can_edit = isset($_SESSION['user_logged_in']) && ($_SESSION['user_role'] === 'admin' || $apartment['user_id'] == $_SESSION['user_id']);
                ?>
                    <div class="annonce-card">
                        <img src="<?php echo $apartment['image_url']; ?>" alt="<?php echo $apartment['title']; ?>" class="annonce-image">
                        <div class="type-badge <?php echo strtolower($apartment['transaction_type']) === 'rent' ? 'type-rent' : 'type-sale'; ?>">
                            <?php echo $apartment['transaction_type']; ?>
                        </div>
                        
                        <!-- Actions en haut à GAUCHE -->
                        <?php if (isset($_SESSION['user_logged_in'])): ?>
                            <div style="position: absolute; top: 10px; left: 10px; display: flex; gap: 5px;">
                                <!-- Bouton favori -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_favorite">
                                    <input type="hidden" name="listing_id" value="<?php echo $apartment['id']; ?>">
                                    <button type="submit" style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 5px;" title="<?php echo $is_favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                        <?php echo $is_favorite ? '❤️' : '🤍'; ?>
                                    </button>
                                </form>
                                
                                <!-- Boutons d'édition/suppression pour les propriétaires et admins -->
                                <?php if ($can_edit): ?>
                                    <a href="?page=edit-listing&id=<?php echo $apartment['id']; ?>" 
                                       style="background: #007bff; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 12px;" title="Modifier">
                                        ✏️
                                    </a>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?');">
                                        <input type="hidden" name="action" value="delete_listing">
                                        <input type="hidden" name="listing_id" value="<?php echo $apartment['id']; ?>">
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
                                <h3 class="annonce-titre"><?php echo htmlspecialchars($apartment['title']); ?></h3>
                                <div class="annonce-prix"><?php echo number_format($apartment['price'], 0, ',', ' '); ?>€</div>
                            </div>
                            <div class="annonce-localisation"><?php echo htmlspecialchars($apartment['city']); ?></div>
                            <p class="annonce-description"><?php echo htmlspecialchars($apartment['description']); ?></p>
                            <div style="font-size: 0.8em; color: #666; margin-top: 10px;">
                                Publié par: <?php echo htmlspecialchars($apartment['owner_email']); ?>
                            </div>
                            <button class="contact-btn">Contact</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination" style="margin-top: 3rem; display: flex; justify-content: center; align-items: center; gap: 10px;">
                    <!-- Bouton Précédent -->
                    <?php if ($current_page > 1): ?>
                        <a href="?page=apartments&p=<?php echo $current_page - 1; ?>" 
                           class="pagination-btn" 
                           style="padding: 10px 15px; background: #4285f4; color: white; text-decoration: none; border-radius: 5px; font-weight: 600;">
                            « Précédent
                        </a>
                    <?php endif; ?>
                    
                    <!-- Numéros de pages -->
                    <?php 
                    // Afficher jusqu'à 5 numéros de page
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    // Ajuster si on est proche du début ou de la fin
                    if ($end_page - $start_page < 4) {
                        if ($start_page == 1) {
                            $end_page = min($total_pages, $start_page + 4);
                        } else {
                            $start_page = max(1, $end_page - 4);
                        }
                    }
                    
                    // Afficher la première page si nécessaire
                    if ($start_page > 1): ?>
                        <a href="?page=apartments&p=1" 
                           class="pagination-btn" 
                           style="padding: 10px 15px; background: #f8f9fa; color: #333; text-decoration: none; border-radius: 5px; border: 1px solid #ddd;">
                            1
                        </a>
                        <?php if ($start_page > 2): ?>
                            <span style="padding: 10px 5px; color: #666;">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Pages du milieu -->
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="pagination-btn active" 
                                  style="padding: 10px 15px; background: #4285f4; color: white; border-radius: 5px; font-weight: bold;">
                                <?php echo $i; ?>
                            </span>
                        <?php else: ?>
                            <a href="?page=apartments&p=<?php echo $i; ?>" 
                               class="pagination-btn" 
                               style="padding: 10px 15px; background: #f8f9fa; color: #333; text-decoration: none; border-radius: 5px; border: 1px solid #ddd;">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <!-- Afficher la dernière page si nécessaire -->
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span style="padding: 10px 5px; color: #666;">...</span>
                        <?php endif; ?>
                        <a href="?page=apartments&p=<?php echo $total_pages; ?>" 
                           class="pagination-btn" 
                           style="padding: 10px 15px; background: #f8f9fa; color: #333; text-decoration: none; border-radius: 5px; border: 1px solid #ddd;">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Bouton Suivant -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=apartments&p=<?php echo $current_page + 1; ?>" 
                           class="pagination-btn" 
                           style="padding: 10px 15px; background: #4285f4; color: white; text-decoration: none; border-radius: 5px; font-weight: 600;">
                            Suivant »
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>