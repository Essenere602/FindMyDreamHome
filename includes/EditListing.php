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

// Vérifier que l'annonce existe et que l'utilisateur a le droit de la modifier
try {
    $stmt = $pdo->prepare("SELECT * FROM listing WHERE id = ?");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        $_SESSION['error_message'] = "Annonce introuvable";
        header('Location: ?page=main');
        exit();
    }
    
    // Vérifier les droits de modification
    if ($_SESSION['user_role'] !== 'admin' && $listing['user_id'] != $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Vous n'avez pas l'autorisation de modifier cette annonce";
        header('Location: ?page=main');
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors du chargement de l'annonce";
    header('Location: ?page=main');
    exit();
}

$errors = [];
$success = '';
$formData = [
    'image' => $listing['image_url'],
    'titre' => $listing['title'],
    'prix' => $listing['price'],
    'ville' => $listing['city'],
    'description' => $listing['description'],
    'transaction_type' => $listing['transaction_type_id'],
    'property_type' => $listing['property_type_id']
];

// Récupérer les types depuis la base de données
try {
    $propertyTypes = $pdo->query("SELECT * FROM propertyType")->fetchAll();
    $transactionTypes = $pdo->query("SELECT * FROM transactionType")->fetchAll();
} catch (PDOException $e) {
    $errors['general'] = "Erreur lors du chargement des types";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données
    $formData['image'] = trim(filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL));
    $formData['titre'] = trim(filter_input(INPUT_POST, 'titre', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $formData['prix'] = trim(filter_input(INPUT_POST, 'prix', FILTER_SANITIZE_NUMBER_INT));
    $formData['ville'] = trim(filter_input(INPUT_POST, 'ville', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $formData['description'] = trim(filter_input(INPUT_POST, 'description', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
    $formData['transaction_type'] = filter_input(INPUT_POST, 'transaction_type', FILTER_SANITIZE_NUMBER_INT);
    $formData['property_type'] = filter_input(INPUT_POST, 'property_type', FILTER_SANITIZE_NUMBER_INT);
    
    // Validation côté serveur
    if (empty($formData['image'])) {
        $errors['image'] = 'L\'URL de l\'image est requise';
    } elseif (!filter_var($formData['image'], FILTER_VALIDATE_URL)) {
        $errors['image'] = 'Veuillez entrer une URL valide pour l\'image';
    }
    
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
    
    if (empty($errors)) {
        try {
            // Mettre à jour l'annonce
            $stmt = $pdo->prepare("
                UPDATE listing 
                SET title = ?, description = ?, price = ?, city = ?, image_url = ?, 
                    property_type_id = ?, transaction_type_id = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $formData['titre'],
                $formData['description'],
                $formData['prix'],
                $formData['ville'],
                $formData['image'],
                $formData['property_type'],
                $formData['transaction_type'],
                $listing_id
            ]);
            
            $_SESSION['success_message'] = 'Annonce modifiée avec succès !';
            header('Location: ?page=main');
            exit();
            
        } catch (PDOException $e) {
            $errors['general'] = "Erreur lors de la modification de l'annonce : " . $e->getMessage();
        }
    }
}
?>

<main class="auth-main">
    <div class="auth-container" style="max-width: 600px;">
        <div class="auth-card">
            <h1 class="auth-title">Modifier l'annonce</h1>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" id="editListingForm" novalidate>
                <div class="form-group">
                    <label for="image" class="form-label">URL de l'image</label>
                    <input type="url" id="image" name="image" class="form-input <?php echo isset($errors['image']) ? 'error' : ''; ?>" 
                           value="<?php echo htmlspecialchars($formData['image']); ?>" 
                           placeholder="https://example.com/image.jpg" required>
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
                
                <div style="display: flex; gap: 10px; justify-content: space-between;">
                    <button type="submit" class="auth-btn" style="flex: 1;">Sauvegarder les modifications</button>
                    <a href="?page=main" class="auth-btn" style="flex: 1; background: #6c757d; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</main>
<script src="../assets/js/addListing.js"></script>