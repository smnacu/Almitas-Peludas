    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <span class="logo-icon">🐾</span>
                    <span>Almitas Peludas</span>
                </div>
                <div class="footer-info">
                    <p>Estética y Salud Animal a domicilio</p>
                    <p>📍 Atendemos: Oeste (Lun) | Centro (Mié) | Norte (Vie)</p>
                </div>
                <div class="footer-social">
                    <?php 
                    $config = require __DIR__ . '/../config/app.php'; 
                    ?>
                    <a href="https://wa.me/<?= $config['whatsapp'] ?>" target="_blank" class="social-link">📱 WhatsApp</a>
                    <a href="https://instagram.com/<?= $config['instagram'] ?>" target="_blank" class="social-link">📸 Instagram</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Almitas Peludas. Cuidando a tu familia multiespecie. ❤️</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?= $baseUrl ?>/assets/js/app.js"></script>
</body>
</html>
