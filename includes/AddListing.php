<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ?page=login');
    exit();
}

// Vérifier si l'utilisateur a le droit d'ajouter des annonces (agent ou admin uniquement)
if (!in_array($_SESSION['user_role'], ['agent', 'admin'])) {
    $_SESSION['error_message'] = "Vous n'avez pas l'autorisation d'ajouter des annonces. Seuls les agents et administrateurs peuvent publier.";
    header('Location: ?page=main');
    exit();
}

$errors = [];
$success = '';
$formData = [
    'titre' => '',
    'prix' => '',
    'ville' => '',
    'description' => '',
    'transaction_type' => '',
    'property_type' => ''
];

// Récupérer les types depuis la base de données
try {
    $propertyTypes = $pdo->query("SELECT * FROM propertyType")->fetchAll();
    $transactionTypes = $pdo->query("SELECT * FROM transactionType")->fetchAll();
} catch (PDOException $e) {
    $errors['general'] = "Erreur lors du chargement des types";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données avec des filtres modernes
    $formData['titre'] = trim(filter_input(INPUT_POST, 'titre', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $formData['prix'] = trim(filter_input(INPUT_POST, 'prix', FILTER_SANITIZE_NUMBER_INT));
    $formData['ville'] = trim(filter_input(INPUT_POST, 'ville', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $formData['description'] = trim(filter_input(INPUT_POST, 'description', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $formData['transaction_type'] = filter_input(INPUT_POST, 'transaction_type', FILTER_SANITIZE_NUMBER_INT);
    $formData['property_type'] = filter_input(INPUT_POST, 'property_type', FILTER_SANITIZE_NUMBER_INT);
    
    // Validation côté serveur
    if (empty($formData['titre'])) {
        $errors['titre'] = 'Le titre est requis';
    } elseif (strlen($formData['titre']) < 3) {
        $errors['titre'] = 'Le titre doit contenir au moins 3 caractères';
    }
    
    if (empty($formData['prix'])) {
        $errors['prix'] = 'Le prix est requis';
    } elseif (!is_numeric($formData['prix']) || $formData['prix'] <= 0) {
        $errors['prix'] = 'Le prix doit être un nombre valide supérieur à 0';
    }
    
    if (empty($formData['ville'])) {
        $errors['ville'] = 'La ville est requise';
    }
    
    if (empty($formData['description'])) {
        $errors['description'] = 'La description est requise';
    } elseif (strlen($formData['description']) < 10) {
        $errors['description'] = 'La description doit contenir au moins 10 caractères';
    }
    
    if (empty($formData['transaction_type'])) {
        $errors['transaction_type'] = 'Le type de transaction est requis';
    }
    
    if (empty($formData['property_type'])) {
        $errors['property_type'] = 'Le type de bien est requis';
    }
    
    // Validation de l'image uploadée
    if (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors['image'] = 'L\'image est requise';
    } elseif ($_FILES['image']['error'] != UPLOAD_ERR_OK) {
        $errors['image'] = 'Erreur lors du téléchargement de l\'image';
    } else {
        $imageInfo = getimagesize($_FILES['image']['tmp_name']);
        if (!$imageInfo) {
            $errors['image'] = 'Le fichier doit être une image valide';
        } elseif (!in_array($imageInfo['mime'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            $errors['image'] = 'Format d\'image non supporté. Utilisez JPG, PNG, GIF ou WebP';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB max
            $errors['image'] = 'L\'image ne doit pas dépasser 5MB';
        }
    }
    
    if (empty($errors)) {
        try {
            // Commencer une transaction
            $pdo->beginTransaction();
            
            // Insérer la nouvelle annonce sans l'image d'abord
            $stmt = $pdo->prepare("
                INSERT INTO listing 
                (title, description, price, city, property_type_id, transaction_type_id, user_id, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $formData['titre'],
                $formData['description'],
                $formData['prix'],
                $formData['ville'],
                $formData['property_type'],
                $formData['transaction_type'],
                $_SESSION['user_id']
            ]);
            
            // Récupérer l'ID de l'annonce créée
            $listing_id = $pdo->lastInsertId();
            
            // Traitement de l'image
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Créer le nom du fichier : titre_annonce_ID.extension
            $imageExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $safeTitre = preg_replace('/[^a-zA-Z0-9_-]/', '_', $formData['titre']);
            $imageName = $safeTitre . '_' . $listing_id . '.' . strtolower($imageExtension);
            $imagePath = $uploadDir . $imageName;
            
            // Déplacer l'image uploadée
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                // Mettre à jour l'annonce avec le chemin de l'image
                $stmt = $pdo->prepare("UPDATE listing SET image_url = ? WHERE id = ?");
                $stmt->execute([$imagePath, $listing_id]);
                
                // Valider la transaction
                $pdo->commit();
                
                $success = 'Annonce ajoutée avec succès !';
                
                // Réinitialiser le formulaire
                $formData = [
                    'titre' => '',
                    'prix' => '',
                    'ville' => '',
                    'description' => '',
                    'transaction_type' => '',
                    'property_type' => ''
                ];
            } else {
                throw new Exception("Erreur lors du déplacement de l'image");
            }
            
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $pdo->rollBack();
            
            // Supprimer l'image si elle a été déplacée
            if (isset($imagePath) && file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            $errors['general'] = "Erreur lors de l'ajout de l'annonce : " . $e->getMessage();
        }
    }
}
?>

<main class="auth-main">
    <div class="auth-container" style="max-width: 600px;">
        <div class="auth-card">
            <h1 class="auth-title">Ajouter une nouvelle annonce</h1>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" id="addListingForm" enctype="multipart/form-data" novalidate>
                <div class="form-group">
                    <label for="image" class="form-label">Image de l'annonce</label>
                    <input type="file" id="image" name="image" class="form-input <?php echo isset($errors['image']) ? 'error' : ''; ?>" 
                           accept="image/jpeg,image/png,image/gif,image/webp" required>
                    <small style="color: #666; font-size: 0.85em;">Formats acceptés : JPG, PNG, GIF, WebP (max 5MB)</small>
                    <?php if (isset($errors['image'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['image']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="imageError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="titre" class="form-label">Titre de l'annonce</label>
                    <input type="text" id="titre" name="titre" class="form-input <?php echo isset($errors['titre']) ? 'error' : ''; ?>" 
                           value="<?php echo htmlspecialchars($formData['titre']); ?>" 
                           placeholder="Ex: Magnifique villa avec piscine" required>
                    <?php if (isset($errors['titre'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['titre']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="titreError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="prix" class="form-label">Prix</label>
                    <input type="number" id="prix" name="prix" class="form-input <?php echo isset($errors['prix']) ? 'error' : ''; ?>" 
                           value="<?php echo htmlspecialchars($formData['prix']); ?>" 
                           placeholder="Ex: 250000" min="1" step="1" required>
                    <?php if (isset($errors['prix'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['prix']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="prixError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="ville" class="form-label">Ville</label>
                    <input type="text" id="ville" name="ville" class="form-input <?php echo isset($errors['ville']) ? 'error' : ''; ?>" 
                           value="<?php echo htmlspecialchars($formData['ville']); ?>" 
                           placeholder="Ex: Paris, Lyon, Marseille..." required>
                    <?php if (isset($errors['ville'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['ville']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="villeError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description courte</label>
                    <textarea id="description" name="description" class="form-input <?php echo isset($errors['description']) ? 'error' : ''; ?>" 
                              rows="4" placeholder="Décrivez votre bien en quelques lignes..." required><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['description']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="descriptionError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="transaction_type" class="form-label">Type de transaction</label>
                    <select id="transaction_type" name="transaction_type" class="form-input <?php echo isset($errors['transaction_type']) ? 'error' : ''; ?>" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach ($transactionTypes as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= $formData['transaction_type'] == $type['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['transaction_type'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['transaction_type']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="transactionTypeError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="property_type" class="form-label">Type de bien</label>
                    <select id="property_type" name="property_type" class="form-input <?php echo isset($errors['property_type']) ? 'error' : ''; ?>" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach ($propertyTypes as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= $formData['property_type'] == $type['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['property_type'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['property_type']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="propertyTypeError"></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="auth-btn">Enregistrer l'annonce</button>
            </form>
            
            <div class="auth-link">
                <p><a href="?page=main">← Retour à l'accueil</a></p>
            </div>
        </div>
    </div>
</main>
<script src="../assets/js/addListing.js"></script>