<!-- Contenu principal -->
<main class="main-content">
    <!-- Section Maisons -->
    <section class="section" id="houses">
        <h2 class="section-title">Nos annonces de maisons</h2>
        <div class="annonces-grid">
            <?php 
            try {
                $stmt = $pdo->prepare("
                    SELECT l.*, pt.name AS property_type, tt.name AS transaction_type 
                    FROM listing l
                    JOIN propertyType pt ON l.property_type_id = pt.id
                    JOIN transactionType tt ON l.transaction_type_id = tt.id
                    WHERE pt.name = 'House'
                ");
                $stmt->execute();
                $maisons = $stmt->fetchAll();
                
                foreach ($maisons as $maison): ?>
                    <div class="annonce-card">
                        <img src="<?php echo $maison['image_url']; ?>" alt="<?php echo $maison['title']; ?>" class="annonce-image">
                        <div class="type-badge <?php echo strtolower($maison['transaction_type']) === 'rent' ? 'type-rent' : 'type-sale'; ?>">
                            <?php echo $maison['transaction_type']; ?>
                        </div>
                        <div class="annonce-content">
                            <div class="annonce-header">
                                <h3 class="annonce-titre"><?php echo $maison['title']; ?></h3>
                                <div class="annonce-prix"><?php echo $maison['price']; ?>€</div>
                            </div>
                            <div class="annonce-localisation"><?php echo $maison['city']; ?></div>
                            <p class="annonce-description"><?php echo $maison['description']; ?></p>
                            <button class="contact-btn">Contact</button>
                        </div>
                    </div>
                <?php endforeach;
            } catch (PDOException $e) {
                echo "<p>Erreur lors du chargement des annonces de maisons</p>";
            }
            ?>
        </div>
    </section>

    <!-- Section Appartements -->
    <section class="section" id="apartments">
        <h2 class="section-title">Nos annonces d'appartements</h2>
        <div class="annonces-grid">
            <?php 
            try {
                $stmt = $pdo->prepare("
                    SELECT l.*, pt.name AS property_type, tt.name AS transaction_type 
                    FROM listing l
                    JOIN propertyType pt ON l.property_type_id = pt.id
                    JOIN transactionType tt ON l.transaction_type_id = tt.id
                    WHERE pt.name = 'Apartment'
                ");
                $stmt->execute();
                $appartements = $stmt->fetchAll();
                
                foreach ($appartements as $appartement): ?>
                    <div class="annonce-card">
                        <img src="<?php echo $appartement['image_url']; ?>" alt="<?php echo $appartement['title']; ?>" class="annonce-image">
                        <div class="type-badge <?php echo strtolower($appartement['transaction_type']) === 'rent' ? 'type-rent' : 'type-sale'; ?>">
                            <?php echo $appartement['transaction_type']; ?>
                        </div>
                        <div class="annonce-content">
                            <div class="annonce-header">
                                <h3 class="annonce-titre"><?php echo $appartement['title']; ?></h3>
                                <div class="annonce-prix"><?php echo $appartement['price']; ?>€</div>
                            </div>
                            <div class="annonce-localisation"><?php echo $appartement['city']; ?></div>
                            <p class="annonce-description"><?php echo $appartement['description']; ?></p>
                            <button class="contact-btn">Contact</button>
                        </div>
                    </div>
                <?php endforeach;
            } catch (PDOException $e) {
                echo "<p>Erreur lors du chargement des annonces d'appartements</p>";
            }
            ?>
        </div>
    </section>
</main>