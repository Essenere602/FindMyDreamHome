<!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Find My Dream Home – Tous droits réservés.</p>
        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
            <p style="margin-top: 10px; font-size: 0.9rem; opacity: 0.8;">
                Connecté en tant que : <?php echo htmlspecialchars($_SESSION['user_email']); ?> 
                | <a href="?page=logout" style="color: #fff; text-decoration: underline;">Se déconnecter</a>
            </p>
        <?php endif; ?>
    </footer>
</body>
</html>