    <?php if ($isPortal): ?>
                </main>
                <footer class="portal-footer">
                    <span>&copy; 2026 CIAT Dental Clinic. Care for every smile.</span>
                    <a href="<?php echo $contactUrl; ?>">Contact the clinic</a>
                </footer>
            </div>
        </div>
    <?php else: ?>
        </main>
        <footer class="site-footer">
            <a class="portal-brand" href="<?php echo $brandUrl; ?>">
                <span class="portal-brand-mark" aria-hidden="true">✳</span>
                <span>CIAT<span class="portal-brand-subtitle">Dental Clinic</span></span>
            </a>
            <p>Thoughtful, comfortable dental care for you and your family.</p>
            <div class="site-footer-contact">
                <span>+60 3 1234 5678</span>
                <a href="mailto:dentalclinic666@gmail.com">dentalclinic666@gmail.com</a>
                <a href="<?php echo $contactUrl; ?>">Clinic details</a>
            </div>
            <small>&copy; 2026 CIAT Dental Clinic. All rights reserved.</small>
        </footer>
    <?php endif; ?>
</body>
</html>
