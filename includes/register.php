<?php
// Traitement du formulaire d'inscription
$errors = [];
$success = '';
$email = '';
$password = '';
$confirmPassword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');
    
    // Validation côté serveur
    if (empty($email)) {
        $errors['email'] = 'L\'email est requis';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Veuillez entrer un email valide';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est requis';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères';
    }
    
    if (empty($confirmPassword)) {
        $errors['confirmPassword'] = 'La confirmation du mot de passe est requise';
    } elseif ($password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Les mots de passe ne correspondent pas';
    }
    
    // Si pas d'erreurs, simulation d'inscription réussie
    if (empty($errors)) {
        // Simuler un utilisateur inscrit et connecté automatiquement
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_id'] = 2; // ID simulé différent pour l'inscription
        
        // Redirection immédiate vers la page principale
        header('Location: ?page=main');
        exit();
    }
}
?>

<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Créer un compte sur FindMyDreamHome</h1>
            
            <form class="auth-form" method="POST" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="emailError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($password); ?>" required>
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="passwordError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" class="form-input <?php echo isset($errors['confirmPassword']) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($confirmPassword); ?>" required>
                    <?php if (isset($errors['confirmPassword'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['confirmPassword']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="confirmPasswordError"></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="auth-btn">S'inscrire</button>
            </form>
            
            <div class="auth-link">
                <p>Déjà inscrit ? <a href="?page=login">Connectez-vous</a></p>
            </div>
        </div>
    </div>
</main>
<script src="../assets/js/register.js"></script>
