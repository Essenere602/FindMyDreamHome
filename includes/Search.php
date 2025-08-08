<?php
// Configuration de la pagination
$listings_per_page = 12;
$current_page = max(1, (int)($_GET['p'] ?? 1)); 

// Récupération des paramètres de recherche
$search_city = trim($_GET['city'] ?? '');
$search_max_price = !empty($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$search_property_type = !empty($_GET['property_type']) ? (int)$_GET['property_type'] : null;
$search_transaction_type = !empty($_GET['transaction_type']) ? (int)$_GET['transaction_type'] : null;

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
    
    // Construire l'URL de redirection avec les paramètres de recherche
    $redirect_params = [];
    if ($search_city) $redirect_params[] = 'city=' . urlencode($search_city);
    if ($search_max_price) $redirect_params[] = 'max_price=' . $search_max_price;
    if ($search_property_type) $redirect_params[] = 'property_type=' . $search_property_type;
    if ($search_transaction_type) $redirect_params[] = 'transaction_type=' . $search_transaction_type;
    $redirect_params[] = 'p=' . $current_page;
    
    $redirect_url = '?page=search&' . implode('&', $redirect_params);
    header('Location: ' . $redirect_url);
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

// Récupérer les types pour les filtres
$propertyTypes = [];
$transactionTypes = [];
try {
    $stmt = $pdo->query("SELECT * FROM propertyType ORDER BY name");
    $propertyTypes = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM transactionType ORDER BY name");
    $transactionTypes = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors du chargement des types";
}

// Construction de la requête avec filtres
$where_conditions = [];
$params = [];

if (!empty($search_city)) {
    $where_conditions[] = "l.city LIKE ?";
    $params[] = '%' . $search_city . '%';
}

if ($search_max_price !== null && $search_max_price > 0) {
    $where_conditions[] = "l.price <= ?";
    $params[] = $search_max_price;
}

if ($search_property_type !== null && $search_property_type > 0) {
    $where_conditions[] = "l.property_type_id = ?";
    $params[] = $search_property_type;
}

if ($search_transaction_type !== null && $search_transaction_type > 0) {
    $where_conditions[] = "l.transaction_type_id = ?";
    $params[] = $search_transaction_type;
}

// Clause WHERE
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Compter le nombre total d'annonces avec filtres
$total_listings = 0;
try {
    $count_sql = "
        SELECT COUNT(*) as total
        FROM listing l
        JOIN propertyType pt ON l.property_type_id = pt.id
        JOIN transactionType tt ON l.transaction_type_id = tt.id
        JOIN user u ON l.user_id = u.id
        $where_clause
    ";
    
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    $total_listings = (int)$result['total'];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors du comptage des annonces: " . $e->getMessage();
}

// Calculer le nombre total de pages
$total_pages = max(1, ceil($total_listings / $listings_per_page));

// Vérifier si la page demandée est valide
if ($current_page > $total_pages && $total_listings > 0) {
    $current_page = 1;
}

// Calculer l'offset
$offset = ($current_page - 1) * $listings_per_page;

// Récupérer les annonces avec filtres
$listings = [];
try {
    $listings_sql = "
        SELECT l.*, pt.name AS property_type, tt.name AS transaction_type, u.email as owner_email
        FROM listing l
        JOIN propertyType pt ON l.property_type_id = pt.id
        JOIN transactionType tt ON l.transaction_type_id = tt.id
        JOIN user u ON l.user_id = u.id
        $where_clause
        ORDER BY l.created_at DESC
        LIMIT " . (int)$listings_per_page . " OFFSET " . (int)$offset . "
    ";
    
    $stmt = $pdo->prepare($listings_sql);
    $stmt->execute($params);
    $listings = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors du chargement des annonces: " . $e->getMessage();
}
?>

<main class="main-content">
    <section class="section">
        <h2 class="section-title">Rechercher un bien immobilier</h2>
        
        <!-- Formulaire de recherche -->
        <div class="search-form-container">
            <form method="GET" class="search-form" id="searchForm">
                <input type="hidden" name="page" value="search">
                
                <div class="search-row">
                    <div class="search-field">
                        <label for="city" class="search-label">Ville</label>
                        <input type="text" 
                               id="city" 
                               name="city" 
                               class="search-input" 
                               placeholder="Ex: Paris, Lyon..."
                               value="<?php echo htmlspecialchars($search_city); ?>">
                    </div>
                    
                    <div class="search-field">
                        <label for="max_price" class="search-label">Prix maximum</label>
                        <input type="number" 
                               id="max_price" 
                               name="max_price" 
                               class="search-input" 
                               placeholder="Ex: 500000"
                               min="1"
                               step="1000"
                               value="<?php echo $search_max_price ? $search_max_price : ''; ?>">
                    </div>
                    
                    <div class="search-field">
                        <label for="property_type" class="search-label">Type de bien</label>
                        <select id="property_type" name="property_type" class="search-input">
                            <option value="">Tous les types</option>
                            <?php foreach ($propertyTypes as $type): ?>
                                <option value="<?= $type['id'] ?>" <?= $search_property_type == $type['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="search-field">
                        <label for="transaction_type" class="search-label">Type de transaction</label>
                        <select id="transaction_type" name="transaction_type" class="search-input">
                            <option value="">Tous les types</option>
                            <?php foreach ($transactionTypes as $type): ?>
                                <option value="<?= $type['id'] ?>" <?= $search_transaction_type == $type['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="search-actions">
                    <button type="submit" class="search-btn">🔍 Rechercher</button>
                    <a href="?page=search" class="reset-btn">🗑️ Effacer les filtres</a>
                </div>
            </form>
        </div>
        
        <!-- Messages de succès/erreur -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Résultats de la recherche -->
        <div class="search-results-header">
            <h3 class="results-title">
                <?php if ($search_city || $search_max_price || $search_property_type || $search_transaction_type): ?>
                    Résultats de recherche 
                <?php else: ?>
                    Toutes les annonces 
                <?php endif ?>
                (<?php echo $total_listings; ?> annonce<?php echo $total_listings > 1 ? 's' : ''; ?>)
                <?php if ($total_pages > 1): ?>
                    - Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?>
                <?php endif; ?>
            </h3>
        </div>
        
        <?php if (empty($listings) && ($search_city || $search_max_price || $search_property_type || $search_transaction_type)): ?>
            <div class="no-results">
                <h3>Aucune annonce trouvée</h3>
                <p>Aucune annonce ne correspond à vos critères de recherche.</p>
                <p>Essayez de modifier vos filtres pour élargir la recherche.</p>
            </div>
        <?php elseif (empty($listings)): ?>
            <div class="no-results">
                <h3>Aucune annonce disponible</h3>
                <p>Il n'y a actuellement aucune annonce à afficher.</p>
            </div>
        <?php else: ?>
            <div class="annonces-grid">
                <?php foreach ($listings as $listing): 
                    $is_favorite = in_array($listing['id'], $user_favorites);
                    $can_edit = isset($_SESSION['user_logged_in']) && ($_SESSION['user_role'] === 'admin' || $listing['user_id'] == $_SESSION['user_id']);
                ?>
                    <div class="annonce-card">
                        <img src="<?php echo htmlspecialchars($listing['image_url']); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>" class="annonce-image">
                        <div class="type-badge <?php echo strtolower($listing['transaction_type']) === 'rent' ? 'type-rent' : 'type-sale'; ?>">
                            <?php echo htmlspecialchars($listing['transaction_type']); ?>
                        </div>
                        
                        <!-- Actions en haut à GAUCHE -->
                        <?php if (isset($_SESSION['user_logged_in'])): ?>
                            <div class="annonce-actions">
                                <!-- Bouton favori -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_favorite">
                                    <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                    <button type="submit" class="action-btn favorite-btn" title="<?php echo $is_favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                        <?php echo $is_favorite ? '❤️' : '🤍'; ?>
                                    </button>
                                </form>
                                
                                <!-- Boutons d'édition/suppression pour les propriétaires et admins -->
                                <?php if ($can_edit): ?>
                                    <a href="?page=edit-listing&id=<?php echo $listing['id']; ?>" 
                                       class="action-btn edit-btn" title="Modifier">
                                        ✏️
                                    </a>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?');">
                                        <input type="hidden" name="action" value="delete_listing">
                                        <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                        <button type="submit" class="action-btn delete-btn" title="Supprimer">
                                            🗑️
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="annonce-content">
                            <div class="annonce-header">
                                <h3 class="annonce-titre"><?php echo htmlspecialchars($listing['title']); ?></h3>
                                <div class="annonce-prix"><?php echo number_format($listing['price'], 0, ',', ' '); ?>€</div>
                            </div>
                            <div class="annonce-localisation"><?php echo htmlspecialchars($listing['city']); ?></div>
                            <p class="annonce-description"><?php echo htmlspecialchars($listing['description']); ?></p>
                            <div class="annonce-meta">
                                Type: <?php echo htmlspecialchars($listing['property_type']); ?> | 
                                Publié par: <?php echo htmlspecialchars($listing['owner_email']); ?>
                            </div>
                            <button class="contact-btn">Contact</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    // Paramètres de recherche pour la pagination
                    $search_params = [];
                    if ($search_city) $search_params[] = 'city=' . urlencode($search_city);
                    if ($search_max_price) $search_params[] = 'max_price=' . $search_max_price;
                    if ($search_property_type) $search_params[] = 'property_type=' . $search_property_type;
                    if ($search_transaction_type) $search_params[] = 'transaction_type=' . $search_transaction_type;
                    $base_params = 'page=search&' . implode('&', $search_params);
                    ?>
                    
                    <!-- Bouton Précédent -->
                    <?php if ($current_page > 1): ?>
                        <a href="?<?php echo $base_params; ?>&p=<?php echo $current_page - 1; ?>" 
                           class="pagination-btn pagination-nav">
                            « Précédent
                        </a>
                    <?php endif; ?>
                    
                    <!-- Numéros de pages -->
                    <?php 
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($end_page - $start_page < 4) {
                        if ($start_page == 1) {
                            $end_page = min($total_pages, $start_page + 4);
                        } else {
                            $start_page = max(1, $end_page - 4);
                        }
                    }
                    
                    if ($start_page > 1): ?>
                        <a href="?<?php echo $base_params; ?>&p=1" class="pagination-btn">1</a>
                        <?php if ($start_page > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="pagination-btn active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo $base_params; ?>&p=<?php echo $i; ?>" class="pagination-btn"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="?<?php echo $base_params; ?>&p=<?php echo $total_pages; ?>" class="pagination-btn"><?php echo $total_pages; ?></a>
                    <?php endif; ?>
                    
                    <!-- Bouton Suivant -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?<?php echo $base_params; ?>&p=<?php echo $current_page + 1; ?>" 
                           class="pagination-btn pagination-nav">
                            Suivant »
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>