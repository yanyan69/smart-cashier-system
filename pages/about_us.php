<?php 
session_start(); 
include '../includes/header.php'; 
?>
<div class="container-with-sidebar">
    <?php include '../includes/sidebar.php'; ?>

    <div class="container">
        <header>
            <h1>About Us - Techlaro Company</h1>
        </header>

        <section class="content">
            <div class="company-overview">
                <h2>Our Company</h2>
                <p>Techlaro Company is a dynamic and innovative technology firm dedicated to providing cutting-edge solutions to meet the evolving needs of our clients. Founded on the principles of collaboration, expertise, and a passion for technology, we strive to deliver high-quality services and products that empower businesses and individuals alike. Our team of skilled professionals brings together a wealth of experience in various aspects of software development and database management.</p>

                <h2>Our Mission</h2>
                <p>To empower our clients with robust and scalable technology solutions through collaboration, innovation, and unwavering commitment to excellence.</p>

                <h2>Our Vision</h2>
                <p>To be a leading technology company recognized for its expertise, customer-centric approach, and contribution to technological advancement.</p>

                <h2>Our Core Values</h2>
                <ul>
                    <li><strong>Collaboration:</strong> We believe in the power of teamwork and open communication.</li>
                    <li><strong>Innovation:</strong> We are committed to exploring and implementing the latest technologies.</li>
                    <li><strong>Excellence:</strong> We strive for the highest standards in everything we do.</li>
                    <li><strong>Integrity:</strong> We conduct our business with honesty and transparency.</li>
                    <li><strong>Customer Focus:</strong> Our clients' success is our top priority.</li>
                </ul>
            </div>

            <h2>Our Team</h2>
            <div class="team-members">
                <a href="https://yanyan69.github.io/yanyan.github.io/" class="team-member">
                    <img src="assets/images/christian.jpg" alt="Christian L. Narvaez">
                    <h3>Christian L. Narvaez</h3>
                    <p>Full-Stack Developer</p>
                </a>
                <a href="https://example.com/johnpaul-armenta" class="team-member">
                    <img src="assets/images/johnpaul.jpg" alt="John Paul F. Armenta">
                    <h3>John Paul F. Armenta</h3>
                    <p>Back-end Developer</p>
                </a>
                <a href="https://example.com/jerald-preclaro" class="team-member">
                    <img src="assets/images/jerald.jpg" alt="Jerald James D. Preclaro">
                    <h3>Jerald James D. Preclaro</h3>
                    <p>Front-end Developer</p>
                </a>
                <a href="https://example.com/marielle-maming" class="team-member">
                    <img src="assets/images/marielle.jpg" alt="Marielle B. Maming">
                    <h3>Marielle B. Maming</h3>
                    <p>Database Administrator</p>
                </a>
            </div>
        </section>

        <footer>
            <p>&copy; 2025 Techlaro Company</p>
        </footer>
    </div>
</div>
<script src="assets/js/scripts.js"></script>
<script>
    window.onload = function() {
        const container = document.querySelector('.container');
        if (container) {
            container.scrollIntoView({ behavior: 'smooth' });
        }
    };
</script>
</body>
</html>