<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us · Bestlink College of the Philippines</title>
  <link rel="stylesheet" href="../assets/css/utilities.css" />
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css" />
  
    <link rel="stylesheet" href="../assets/css/contact.css" />
    <link rel="stylesheet" href="../assets/css/site.css" />
</head>
<body data-page="contact">

<!-- ===== TOP NAV ===== -->
<header class="site-header glass-nav fixed top-0 left-0 right-0 z-50 text-white py-3">
  <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <a href="index.html">
        <img src="../assets/bcp-logo.png" alt="BCP Logo" class="h-11 w-auto object-contain" />
      </a>
      <a href="index.html" class="text-sm font-light hidden sm:inline hover:text-bcp-sky transition-colors">
        <span class="font-semibold">Bestlink</span> College of the Philippines
      </a>
    </div>
    <button type="button" class="site-menu-toggle" aria-controls="primary-navigation" aria-expanded="false" aria-label="Open navigation menu"><i class="fas fa-bars" aria-hidden="true"></i> Menu</button>
            <nav id="primary-navigation" aria-label="Main navigation" class="flex flex-wrap items-center gap-3 md:gap-6 text-sm font-medium">
      <a href="index.html" class="nav-link px-1 py-1 text-gray-300">Home</a>
      <a href="about.html" class="nav-link px-1 py-1 text-gray-300">About</a>
      <a href="programs.html" class="nav-link px-1 py-1 text-gray-300">Programs</a>
      <a href="admissions.html" class="nav-link px-1 py-1 text-gray-300">Admissions</a>
      <a href="student-services.html" class="nav-link px-1 py-1 text-gray-300">Student Services</a>
      <a href="events.php" class="nav-link px-1 py-1 text-gray-300">Events</a>
      <a href="contact.php" class="nav-link px-1 py-1 text-bcp-sky">Contact</a>
      <a href="online-admission.html" class="btn-sky px-6 py-2 rounded-full flex items-center gap-2 text-sm">
        <i class="fas fa-user-graduate"></i> Enroll Now!
      </a>
    </nav>
  </div>
</header>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>

  <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
    <div class="hero-badge">Get in Touch</div>
    <h1>Contact Us</h1>
    <p>
      Have a question about programs, admission, or student services?
      We'd love to hear from you — our team is ready to help.
    </p>
  </div>
</section>

<!-- ===== BODY ===== -->
<section class="contact-body">
  <div class="max-w-7xl mx-auto px-4">

    <div class="contact-layout">

      <!-- LEFT: Info Cards + Map -->
      <div>
        <div class="info-card">
          <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div class="info-content">
            <div class="info-label">Address</div>
            <div class="info-value">
              #1071 Brgy. Kaligayahan, Quirino Highway,
              Novaliches Quezon City, Philippines 1123
            </div>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon"><i class="fas fa-envelope"></i></div>
          <div class="info-content">
            <div class="info-label">Email</div>
            <div class="info-value">
              <a href="mailto:bcp-inquiry@bcp.edu.ph">bcp-inquiry@bcp.edu.ph</a>
            </div>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon"><i class="fas fa-phone"></i></div>
          <div class="info-content">
            <div class="info-label">Phone</div>
            <div class="info-value">7000-5317</div>
          </div>
        </div>

        <div class="map-wrapper">
          <iframe
            src="https://www.openstreetmap.org/export/embed.html?bbox=120.98,14.68,121.05,14.75&layer=mapnik&marker=14.715,121.015"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="BCP Location">
          </iframe>
        </div>
      </div>

      <!-- RIGHT: Contact Form -->
      <div class="form-card">

        <h2>Send us a message</h2>
        <p class="form-subtitle">Fill out the form and our team will get back to you shortly.</p>

        <?php if ($formSuccess): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
              <strong>Message sent successfully!</strong><br>
              Thank you for reaching out. We'll get back to you as soon as possible.
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($formError)): ?>
          <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div><?php echo $e($formError); ?></div>
          </div>
        <?php endif; ?>

        <form method="POST" action="contact.php" id="contactForm">
          <input type="hidden" name="action" value="send_message">

          <div class="form-row">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input
                type="text"
                id="name"
                name="name"
                placeholder="Juan dela Cruz"
                value="<?php echo $e($oldInput['name']); ?>"
                required
              >
            </div>
            <div class="form-group">
              <label for="email">Email Address</label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="you@example.com"
                value="<?php echo $e($oldInput['email']); ?>"
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label for="subject">Subject</label>
            <input
              type="text"
              id="subject"
              name="subject"
              placeholder="Admission inquiry"
              value="<?php echo $e($oldInput['subject']); ?>"
              required
            >
          </div>

          <div class="form-group">
            <label for="message">Message</label>
            <textarea
              id="message"
              name="message"
              placeholder="How can we help you?"
              required
            ><?php echo $e($oldInput['message']); ?></textarea>
          </div>

          <button type="submit" class="btn-submit">
            Send Message
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      </div>

    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="bg-[#0a1e3d] text-white py-12">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <div>
        <div class="flex items-center gap-3 mb-4">
          <a href="index.html">
            <img src="../assets/bcp-logo.png" alt="Bestlink College of the Philippines logo" class="h-12 w-auto" />
          </a>
          <a href="index.html" class="font-bold text-lg hover:text-bcp-sky transition-colors">BCP</a>
        </div>
        <p class="text-gray-400 text-sm leading-relaxed">
          At Bestlink College of the Philippines, We provide and promote quality education with modern and unique techniques to able to enhance the skill and the knowledge of our dear students to make them globally competitive and productive citizens.
        </p>
      </div>
      <div>
        <h4 class="font-bold text-white mb-4">EXPLORE</h4>
        <ul class="space-y-2 text-gray-400 text-sm">
          <li><a href="index.html" class="footer-link">Home</a></li>
          <li><a href="about.html" class="footer-link">About</a></li>
          <li><a href="programs.html" class="footer-link">Programs</a></li>
          <li><a href="admissions.html" class="footer-link">Admissions</a></li>
          <li><a href="student-services.html" class="footer-link">Student Services</a></li>
          <li><a href="events.php" class="footer-link">Events</a></li>
          <li><a href="contact.php" class="footer-link">Contact</a></li>
          <li><a href="#" class="footer-link">Gallery</a></li>
          <li><a href="#" class="footer-link">Careers</a></li>
          <li><a href="#" class="footer-link">Scholarships</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-bold text-white mb-4">USEFUL LINKS</h4>
        <ul class="space-y-2 text-gray-400 text-sm">
          <li><a href="index.html" class="footer-link">Home</a></li>
          <li><a href="#" class="footer-link">BCP College LMS</a></li>
          <li><a href="#" class="footer-link">BCP SHS LMS</a></li>
          <li><a href="#" class="footer-link">BCP SMS</a></li>
          <li><a href="#" class="footer-link">BCP Student E-Mail</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-bold text-white mb-4">CONTACT</h4>
        <ul class="space-y-3 text-gray-400 text-sm">
          <li class="flex gap-3"><i class="fas fa-map-marker-alt text-bcp-sky mt-1"></i> #1071 Brgy. Kaligayahan, Quirino Highway, Novaliches Quezon City, Philippines 1123</li>
          <li class="flex gap-3"><i class="fas fa-envelope text-bcp-sky mt-1"></i> bcp-inquiry@bcp.edu.ph</li>
          <li class="flex gap-3"><i class="fas fa-phone text-bcp-sky mt-1"></i> 7000-5317 · 8442-8601</li>
        </ul>
        <div class="flex gap-4 mt-4">
          <a href="#" class="text-gray-400 hover:text-bcp-sky transition-colors"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="text-gray-400 hover:text-bcp-sky transition-colors"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-gray-400 hover:text-bcp-sky transition-colors"><i class="fab fa-instagram"></i></a>
          <a href="#" class="text-gray-400 hover:text-bcp-sky transition-colors"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
    </div>
    <div class="border-t border-white/10 mt-8 pt-6 flex flex-wrap justify-between items-center text-sm text-gray-400">
      <span>© Copyright BCP. All Rights Reserved</span>
      <span>Developed by BCP MIS Department</span>
    </div>
  </div>
</footer>

<script id="page-data" type="application/json"><?= PageView::json($clientData ?? []) ?></script>
<script src="../assets/js/site.js"></script>
</body>
</html>
