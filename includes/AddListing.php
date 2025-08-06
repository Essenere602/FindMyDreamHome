<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ?page=login');
    exit();
}

$errors = [];
$success = '';
$formData = [
    'image' => '',
    'titre' => '',
    'prix' => '',
    'ville' => '',
    'description' => '',
    'transaction_type' => '',
    'property_type' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['image'] = trim($_POST['image'] ?? '');
    $formData['titre'] = trim($_POST['titre'] ?? '');
    $formData['prix'] = trim($_POST['prix'] ?? '');
    $formData['ville'] = trim($_POST['ville'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['transaction_type'] = $_POST['transaction_type'] ?? '';
    $formData['property_type'] = $_POST['property_type'] ?? '';
    
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
    
    // Si pas d'erreurs, simulation d'ajout réussi
    if (empty($errors)) {
        $success = 'Annonce ajoutée avec succès ! Votre annonce sera visible après validation.';
        
        // Simuler l'ajout dans les données (ici on pourrait l'ajouter au tableau en session)
        $newListing = [
            'image' => $formData['image'],
            'titre' => $formData['titre'],
            'prix' => $formData['prix'],
            'localisation' => $formData['ville'] . ', France',
            'description' => $formData['description'],
            'type' => $formData['transaction_type']
        ];
        
        // Ajouter à la session pour simulation
        if (!isset($_SESSION['user_listings'])) {
            $_SESSION['user_listings'] = [];
        }
        $_SESSION['user_listings'][] = $newListing;
        
        // Réinitialiser le formulaire
        $formData = [
            'image' => '',
            'titre' => '',
            'prix' => '',
            'ville' => '',
            'description' => '',
            'transaction_type' => '',
            'property_type' => ''
        ];
    }
}
?>

<main class="auth-main">
    <div class="auth-container" style="max-width: 600px;">
        <div class="auth-card">
            <h1 class="auth-title">Ajouter une nouvelle annonce</h1>
            
            <?php if ($success): ?>
                <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" id="addListingForm" novalidate>
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
                    <input type="text" id="prix" name="prix" class="form-input <?php echo isset($errors['prix']) ? 'error' : ''; ?>" 
                           value="<?php echo htmlspecialchars($formData['prix']); ?>" 
                           placeholder="Ex: 250,000€ ou 1,200€ /month" required>
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
                        <option value="Rent" <?php echo $formData['transaction_type'] === 'Rent' ? 'selected' : ''; ?>>Location (Rent)</option>
                        <option value="Sale" <?php echo $formData['transaction_type'] === 'Sale' ? 'selected' : ''; ?>>Vente (Sale)</option>
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
                        <option value="House" <?php echo $formData['property_type'] === 'House' ? 'selected' : ''; ?>>Maison (House)</option>
                        <option value="Apartment" <?php echo $formData['property_type'] === 'Apartment' ? 'selected' : ''; ?>>Appartement (Apartment)</option>
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
