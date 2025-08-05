<?php
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validation serveur
    if (empty($email)) {
        $errors['email'] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Veuillez entrer un email valide";
    }
    
    if (empty($password)) {
        $errors['password'] = "Le mot de passe est requis";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    
    // Simulation connexion réussie
    if (empty($errors)) {
        $success = "Connexion réussie ! (simulation)";
        $email = $password = '';
    }
}

?>

<?php if ($success): ?>
    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Connexion à FindMyDreamHome</h1>
            
    <form class="auth-form" method="POST" id="loginForm" novalidate>
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            <?php if (isset($errors['email'])): ?>
                <span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span>
            <?php else: ?>
                <span class="error-message" id="emailError"></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" id="password" name="password" class="form-input" required>
            <?php if (isset($errors['password'])): ?>
                <span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span>
            <?php else: ?>
                <span class="error-message" id="passwordError"></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="auth-btn">Se connecter</button>
    </form>
            
            <div class="auth-link">
                <p>Pas encore de compte ? <a href="?page=register">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>
</main>
<script src="../assets/js/login.js"></script>