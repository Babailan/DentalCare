<?php
session_start();
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['patient'])) {
    header('Location: ../login.php');
    exit;
}
include __DIR__ . '/../header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <h1>Welcome to CIAT Dental Clinic</h1>
    <p>Your smile, our commitment.</p>
</div>

<!-- Horizontal Card -->
<div class="horizontal-card">
    <div class="left-content">
        <h3>Your dental care, in one place</h3>
        <p>Request a visit, chat with the doctor, and keep track of your appointment history.</p>
        <button onclick="window.location.href='appointment_slots.php';">Request Appointment</button>
    </div>
    <div class="right-content">
        <img src="../images/dental_family.jpg" alt="Family with good dental care">
    </div>
</div>

<!-- Services Section -->
<div class="services-section">
    <h3>Our Services</h3>
    <div class="services-grid">
        <div class="service-card">
            <h4>Dental Bonding</h4>
            <p>A procedure to repair decayed, chipped, fractured, or discolored teeth. It’s a cost-effective way to improve the aesthetics of your smile.</p>
        </div>
        <div class="service-card">
            <h4>Teeth Cleaning</h4>
            <p>Regular teeth cleaning helps in removing plaque and tartar buildup, preventing gum disease and keeping your mouth healthy.</p>
        </div>
        <div class="service-card">
            <h4>Teeth Whitening</h4>
            <p>Brighten your smile with our professional teeth whitening services, designed to remove stains and discoloration for a radiant look.</p>
        </div>
        <div class="service-card">
            <h4>Tooth Extraction</h4>
            <p>Whether it's a troublesome wisdom tooth or a tooth damaged beyond repair, our extraction services are performed with utmost care to ensure your comfort and health.</p>
        </div>
    </div>
</div>

<!-- Team Members Slider -->
<div class="team-slider">
    <h3>Our Professionals</h3>
    <button class="prev" onclick="moveSlide(-1)">&#10094;</button>
    <div class="slides">
        <div class="slide">
            <img src="../images/team1.jpg" alt="Team Member 1">
            <h4>Dr. Wong Xue En</h4>
            <p>Dentist</p>
        </div>
        <div class="slide">
            <img src="../images/team2.jpg" alt="Team Member 2">
            <h4>Dr. Koh Wen Le</h4>
            <p>Orthodontist</p>
        </div>
        <div class="slide">
            <img src="../images/team3.jpg" alt="Team Member 3">
            <h4>Dr. Lee Chang Jing</h4>
            <p>Periodontist</p>
        </div>
        <div class="slide">
            <img src="../images/team4.jpg" alt="Team Member 4">
            <h4>Dr. Ng Jing Wei</h4>
            <p>Prosthodontist</p>
        </div>
    </div>
    <button class="next" onclick="moveSlide(1)">&#10095;</button>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

<script>
let currentSlide = 0;

function moveSlide(n) {
    const slides = document.querySelector('.slides');
    const totalSlides = document.querySelectorAll('.slide').length;
    currentSlide = (currentSlide + n + totalSlides) % totalSlides;
    slides.style.transform = `translateX(${-currentSlide * 100}%)`;
}
</script>
