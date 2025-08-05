<?php
// Inclure les données
require_once 'data/data.php';

// Inclure le header
include 'includes/header.php';
?>

    <!-- Contenu principal -->
    <main class="main-content">
        <!-- Section Maisons -->
        <section class="section" id="houses">
            <h2 class="section-title">Nos annonces de maisons</h2>
            <div class="annonces-grid">
                <?php foreach ($maisons as $maison): ?>
                    <div class="annonce-card">
                        <img src="<?php echo $maison['image']; ?>" alt="<?php echo $maison['titre']; ?>" class="annonce-image">
                        <div class="type-badge <?php echo strtolower($maison['type']) === 'rent' ? 'type-rent' : 'type-sale'; ?>">
                            <?php echo $maison['type']; ?>
                        </div>
                        <div class="annonce-content">
                            <div class="annonce-header">
                                <h3 class="annonce-titre"><?php echo $maison['titre']; ?></h3>
                                <div class="annonce-prix"><?php echo $maison['prix']; ?></div>
                            </div>
                            <div class="annonce-localisation"><?php echo $maison['localisation']; ?></div>
                            <p class="annonce-description"><?php echo $maison['description']; ?></p>
                            <button class="contact-btn">Contact</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Section Appartements -->
        <section class="section" id="apartments">
            <h2 class="section-title">Nos annonces d'appartements</h2>
            <div class="annonces-grid">
                <?php foreach ($appartements as $appartement): ?>
                    <div class="annonce-card">
                        <img src="<?php echo $appartement['image']; ?>" alt="<?php echo $appartement['titre']; ?>" class="annonce-image">
                        <div class="type-badge <?php echo strtolower($appartement['type']) === 'rent' ? 'type-rent' : 'type-sale'; ?>">
                            <?php echo $appartement['type']; ?>
                        </div>
                        <div class="annonce-content">
                            <div class="annonce-header">
                                <h3 class="annonce-titre"><?php echo $appartement['titre']; ?></h3>
                                <div class="annonce-prix"><?php echo $appartement['prix']; ?></div>
                            </div>
                            <div class="annonce-localisation"><?php echo $appartement['localisation']; ?></div>
                            <p class="annonce-description"><?php echo $appartement['description']; ?></p>
                            <button class="contact-btn">Contact</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
<?php
// Inclure le footer
include 'includes/footer.php';
?>