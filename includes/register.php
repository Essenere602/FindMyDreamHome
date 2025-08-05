<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Inscription</h1>
            
            <form class="auth-form" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                    <span class="error-message" id="emailError"></span>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                    <span class="error-message" id="passwordError"></span>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" required>
                    <span class="error-message" id="confirmPasswordError"></span>
                </div>
                
                <button type="submit" class="auth-btn">S'inscrire</button>
            </form>
            
            <div class="auth-link">
                <p>Déjà inscrit ? <a href="login.php">Connectez-vous</a></p>
            </div>
        </div>
    </div>
</main>
<script src="../assets/js/register.js"></script>