<?php
$errors = [];
$success = '';
$email = '';
$password = '';

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
    }
    
    // Dans la partie de vérification des identifiants
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role']; // Ajout du rôle en session
                
                header('Location: ?page=main');
                exit();
            } else {
                $errors['general'] = "Identifiants incorrects";
            }
        } catch (PDOException $e) {
            $errors['general'] = "Erreur lors de la connexion";
        }
    }     
}
?>

<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Connexion à FindMyDreamHome</h1>
            
            <?php if (isset($errors['general'])): ?>
                <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" id="loginForm" novalidate>
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

                <button type="submit" class="auth-btn">Se connecter</button>
            </form>
            
            <div class="auth-link">
                <p>Pas encore de compte ? <a href="?page=register">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>
</main>
<script src="../assets/js/login.js"></script>