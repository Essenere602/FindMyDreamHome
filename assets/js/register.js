document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Réinitialiser les messages d'erreur
    document.getElementById('emailError').textContent = '';
    document.getElementById('passwordError').textContent = '';
    document.getElementById('confirmPasswordError').textContent = '';
    
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();
    
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
    } else if (password.length < 6) {
        document.getElementById('passwordError').textContent = 'Le mot de passe doit contenir au moins 6 caractères';
        hasError = true;
    }
    
    // Validation de la confirmation du mot de passe
    if (!confirmPassword) {
        document.getElementById('confirmPasswordError').textContent = 'La confirmation du mot de passe est requise';
        hasError = true;
    } else if (password !== confirmPassword) {
        document.getElementById('confirmPasswordError').textContent = 'Les mots de passe ne correspondent pas';
        hasError = true;
    }
    
    if (!hasError) {
        // Ici, vous pourrez ajouter la logique d'inscription plus tard
        alert('Inscription réussie ! (simulation)');
    }
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}