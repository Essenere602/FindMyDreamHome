<?php
$errors = [];
$success = '';
$email = '';
$password = '';
$confirmPassword = '';
$role = 'user'; // Rôle par défaut

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    
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
    
    // Validation du rôle
    if (!in_array($role, ['user', 'agent'])) {
        $role = 'user'; // Sécurité : forcer le rôle par défaut si invalide
    }
    
    // Dans la partie d'inscription
    if (empty($errors)) {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors['email'] = 'Cet email est déjà utilisé';
            } else {
                // Hacher le mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer le nouvel utilisateur
                $stmt = $pdo->prepare("INSERT INTO user (email, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$email, $hashedPassword, $role]);
                
                // Connecter l'utilisateur directement
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_role'] = $role;
                
                header('Location: ?page=main');
                exit();
            }
        } catch (PDOException $e) {
            $errors['general'] = "Erreur lors de l'inscription";
        }
    }
}
?>

<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Créer un compte sur FindMyDreamHome</h1>
            
            <?php if (isset($errors['general'])): ?>
                <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>
            
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
                    <input type="password" id="password" name="password" class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>" required>
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="passwordError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" class="form-input <?php echo isset($errors['confirmPassword']) ? 'error' : ''; ?>" required>
                    <?php if (isset($errors['confirmPassword'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['confirmPassword']); ?></span>
                    <?php else: ?>
                        <span class="error-message" id="confirmPasswordError"></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="role" class="form-label">Type de compte</label>
                    <select id="role" name="role" class="form-input" required>
                        <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>Utilisateur (consultation uniquement)</option>
                        <option value="agent" <?= $role === 'agent' ? 'selected' : '' ?>>Agent immobilier (publier des annonces)</option>
                    </select>
                    <span class="error-message" id="roleError"></span>
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