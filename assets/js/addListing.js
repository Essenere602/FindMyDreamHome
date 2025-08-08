document.getElementById('addListingForm').addEventListener('submit', function(e) {
    // Réinitialiser les messages d'erreur
    document.getElementById('imageError').textContent = '';
    document.getElementById('titreError').textContent = '';
    document.getElementById('prixError').textContent = '';
    document.getElementById('villeError').textContent = '';
    document.getElementById('descriptionError').textContent = '';
    document.getElementById('transactionTypeError').textContent = '';
    document.getElementById('propertyTypeError').textContent = '';
    
    const imageInput = document.getElementById('image');
    const titre = document.getElementById('titre').value.trim();
    const prix = document.getElementById('prix').value.trim();
    const ville = document.getElementById('ville').value.trim();
    const description = document.getElementById('description').value.trim();
    const transactionType = document.getElementById('transaction_type').value;
    const propertyType = document.getElementById('property_type').value;
    
    let hasError = false;
    
    // Validation de l'image
    if (!imageInput.files || imageInput.files.length === 0) {
        document.getElementById('imageError').textContent = 'L\'image est requise';
        hasError = true;
    } else {
        const file = imageInput.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!allowedTypes.includes(file.type)) {
            document.getElementById('imageError').textContent = 'Format d\'image non supporté. Utilisez JPG, PNG, GIF ou WebP';
            hasError = true;
        } else if (file.size > maxSize) {
            document.getElementById('imageError').textContent = 'L\'image ne doit pas dépasser 5MB';
            hasError = true;
        }
    }
    
    // Validation du titre
    if (!titre) {
        document.getElementById('titreError').textContent = 'Le titre est requis';
        hasError = true;
    } else if (titre.length < 3) {
        document.getElementById('titreError').textContent = 'Le titre doit contenir au moins 3 caractères';
        hasError = true;
    }
    
    // Validation du prix
    if (!prix) {
        document.getElementById('prixError').textContent = 'Le prix est requis';
        hasError = true;
    }
    
    // Validation de la ville
    if (!ville) {
        document.getElementById('villeError').textContent = 'La ville est requise';
        hasError = true;
    }
    
    // Validation de la description
    if (!description) {
        document.getElementById('descriptionError').textContent = 'La description est requise';
        hasError = true;
    } else if (description.length < 10) {
        document.getElementById('descriptionError').textContent = 'La description doit contenir au moins 10 caractères';
        hasError = true;
    }
    
    // Validation du type de transaction
    if (!transactionType) {
        document.getElementById('transactionTypeError').textContent = 'Le type de transaction est requis';
        hasError = true;
    }
    
    // Validation du type de bien
    if (!propertyType) {
        document.getElementById('propertyTypeError').textContent = 'Le type de bien est requis';
        hasError = true;
    }
    
    if (hasError) {
        e.preventDefault();
    }
});

// Prévisualisation de l'image 
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // pour prévoir prévisualisation
        };
        reader.readAsDataURL(file);
    }
});