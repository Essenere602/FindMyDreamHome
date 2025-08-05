document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Réinitialiser les messages d'erreur
    document.getElementById('emailError').textContent = '';
    document.getElementById('passwordError').textContent = '';
    
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    
    let hasError = false;
    
    // Validation de l'email
    if (!email) {
        document.getElementById('emailError').textContent = 'L\'email est requis';
        hasError = true;
    } else if (!isValidEmail(email)) {
        document.getElementById('emailError').textContent = 'Veuillez entrer un email valide';
        hasError = true;
    }
    
    // Validation du mot de passe
    if (!password) {
        document.getElementById('passwordError').textContent = 'Le mot de passe est requis';
        hasError = true;
    }
    
    if (!hasError) {
        // Ici, vous pourrez ajouter la logique de connexion plus tard
        alert('Connexion réussie ! (simulation)');
    }
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}