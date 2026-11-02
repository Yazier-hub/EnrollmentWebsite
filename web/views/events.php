<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Events · Bestlink College of the Philippines</title>
  <link rel="stylesheet" href="../assets/css/utilities.css" />
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css" />
  
    <link rel="stylesheet" href="../assets/css/events.css" />
    <link rel="stylesheet" href="../assets/css/site.css" />
</head>
<body data-page="events">

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
      <a href="events.php" class="nav-link px-1 py-1 text-bcp-sky">Events</a>
      <a href="contact.php" class="nav-link px-1 py-1 text-gray-300">Contact</a>
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

  <div class="max-w-4xl mx-auto px-4 relative z-10">
    <div class="hero-badge">What's Happening</div>
    <h1>Events <span>Calendar</span></h1>
    <p>
      Festivals, research expos, sports, and academic activities
      happening across Bestlink College of the Philippines.
    </p>

    <div class="hero-stats">
      <div>
        <div class="hero-stat-value"><?php echo (int) $totalEvents; ?></div>
        <div class="hero-stat-label">Total Events</div>
      </div>
      <div>
        <div class="hero-stat-value"><?php echo (int) $upcomingCount; ?></div>
        <div class="hero-stat-label">Upcoming</div>
      </div>
      <div>
        <div class="hero-stat-value"><?php echo (int) $ongoingCount; ?></div>
        <div class="hero-stat-label">Ongoing</div>
      </div>
      <div>
        <div class="hero-stat-value"><?php echo (int) $completedCount; ?></div>
        <div class="hero-stat-label">Completed</div>
      </div>
    </div>
  </div>
</section>

<!-- ===== EVENTS BODY ===== -->
<section class="events-body">
  <div class="max-w-7xl mx-auto px-4">

    <?php if (isset($fatalMessage)): ?>
      <div class="empty-state">
        <i class="fas fa-exclamation-triangle"></i>
        <h3 class="empty-title">System Temporarily Unavailable</h3>
        <p class="empty-description"><?php echo $e($fatalMessage); ?></p>
      </div>
    <?php elseif (empty($eventsByMonth)): ?>
      <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h3 class="empty-title">No events scheduled</h3>
        <p class="empty-description">Check back soon for upcoming activities.</p>
      </div>
    <?php else: ?>
      <?php foreach ($eventsByMonth as $monthName => $monthEvents): ?>
        <div class="event-month">
          <div class="month-header">
            <h2><?php echo $e($monthName); ?></h2>
            <span class="event-count">
              <?php echo count($monthEvents); ?> event<?php echo count($monthEvents) !== 1 ? 's' : ''; ?>
            </span>
          </div>

          <div class="event-grid">
            <?php foreach ($monthEvents as $event): ?>
              <div class="event-card">
                <div class="event-badges">
                  <span class="event-tag <?php echo $e($event->tagClass()); ?>">
                    <i class="fas <?php echo $e($event->icon()); ?>"></i>
                    <?php echo $e($event->eventType); ?>
                  </span>
                  <span class="event-date-badge">
                    <i class="far fa-calendar-alt"></i>
                    <?php echo $e($event->formattedDate()); ?>
                  </span>
                  <span class="status-pill <?php echo $e($event->statusClass()); ?>">
                    <?php echo $e($event->statusLabel()); ?>
                  </span>
                </div>

                <h3><?php echo $e($event->eventTitle); ?></h3>

                <?php if ($event->description !== ''): ?>
                  <p><?php echo $e($event->description); ?></p>
                <?php endif; ?>

                <?php if ($event->timeRange() !== ''): ?>
                  <div class="event-meta">
                    <i class="far fa-clock"></i>
                    <?php echo $e($event->timeRange()); ?>
                  </div>
                <?php endif; ?>

                <?php if ($event->location !== ''): ?>
                  <div class="event-meta">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo $e($event->location); ?>
                  </div>
                <?php endif; ?>

                <?php if ($event->targetAudience !== ''): ?>
                  <div class="event-meta">
                    <i class="fas fa-users"></i>
                    <?php echo $e($event->targetAudience); ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>

<!-- ===== STATS SECTION ===== -->
<div class="event-stats">
  <div class="stats-glow-top"></div>
  <div class="stats-glow-bottom"></div>

  <div class="stats-content">
    <div>
      <div class="stats-value">45,000+</div>
      <div class="stats-label">Students</div>
      <div class="stats-caption">Across all programs and campuses</div>
    </div>
    <div>
      <div class="stats-value">2002</div>
      <div class="stats-label">Established</div>
      <div class="stats-caption">Two decades of quality education</div>
    </div>
    <div>
      <div class="stats-value">3</div>
      <div class="stats-label">Campuses</div>
      <div class="stats-caption">Millionaire's Village, Main, Bulacan</div>
    </div>
    <div class="stats-brand">
      <img class="stats-logo" src="../assets/bcp-logo.png" alt="BCP" />
      <span class="stats-brand-name">BCP</span>
      <span class="stats-divider">|</span>
      <span class="stats-country">PHILIPPINES</span>
    </div>
  </div>
</div>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
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
