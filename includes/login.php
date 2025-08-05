<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Connexion à FindMyDreamHome</h1>
            
            <form class="auth-form" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                    <span class="error-message" id="emailError"></span>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                    <span class="error-message" id="passwordError"></span>
                </div>
                
                <button type="submit" class="auth-btn">Login</button>
            </form>
            
            <div class="auth-link">
                <p>Pas encore de compte ? <a href="?page=register">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>
</main>