<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ?page=login');
    exit();
}

// Récupérer l'ID de l'annonce
$listing_id = (int)($_GET['id'] ?? 0);

if ($listing_id <= 0) {
    $_SESSION['error_message'] = "Annonce introuvable";
    header('Location: ?page=main');
    exit();
}

// Vérifier que l'annonce existe et que l'utilisateur a le droit de la supprimer
try {
    $stmt = $pdo->prepare("SELECT * FROM listing WHERE id = ?");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        $_SESSION['error_message'] = "Annonce introuvable";
        header('Location: ?page=main');
        exit();
    }
    
    // Vérifier les droits de suppression
    if ($_SESSION['user_role'] !== 'admin' && $listing['user_id'] != $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Vous n'avez pas l'autorisation de supprimer cette annonce";
        header('Location: ?page=main');
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors du chargement de l'annonce";
    header('Location: ?page=main');
    exit();
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Commencer une transaction
        $pdo->beginTransaction();
        
        // Supprimer d'abord les favoris associés
        $stmt = $pdo->prepare("DELETE FROM favorite WHERE listing_id = ?");
        $stmt->execute([$listing_id]);
        
        // Puis supprimer l'annonce
        $stmt = $pdo->prepare("DELETE FROM listing WHERE id = ?");
        $stmt->execute([$listing_id]);
        
        // Valider la transaction
        $pdo->commit();
        
        $_SESSION['success_message'] = "Annonce supprimée avec succès";
        header('Location: ?page=main');
        exit();
        
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        $_SESSION['error_message'] = "Erreur lors de la suppression de l'annonce";
    }
}
?>

<main class="auth-main">
    <div class="auth-container" style="max-width: 600px;">
        <div class="auth-card">
            <h1 class="auth-title">Supprimer l'annonce</h1>
            
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>⚠️ Attention !</strong> Cette action est irréversible.
            </div>
            
            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">Annonce à supprimer :</h3>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <img src="<?php echo htmlspecialchars($listing['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($listing['title']); ?>" 
                         style="width: 100px; height: 80px; object-fit: cover; border-radius: 5px;">
                    <div>
                        <h4 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($listing['title']); ?></h4>
                        <p style="margin: 0; color: #666; font-size: 0.9em;">
                            <?php echo htmlspecialchars($listing['city']); ?> - 
                            <?php echo number_format($listing['price'], 0, ',', ' '); ?>€
                        </p>
                        <p style="margin: 5px 0 0 0; font-size: 0.85em; color: #888;">
                            Créée le <?php echo date('d/m/Y', strtotime($listing['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <form method="POST" style="display: flex; gap: 10px; justify-content: space-between;">
                <input type="hidden" name="confirm_delete" value="1">
                <button type="submit" 
                        class="auth-btn" 
                        style="flex: 1; background-color: #dc3545;"
                        onclick="return confirm('Êtes-vous vraiment sûr de vouloir supprimer cette annonce ? Cette action ne peut pas être annulée.')">
                    🗑️ Confirmer la suppression
                </button>
                <a href="?page=main" 
                   class="auth-btn" 
                   style="flex: 1; background: #6c757d; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                    Annuler
                </a>
            </form>
        </div>
    </div>
</main>