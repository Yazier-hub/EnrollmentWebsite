<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Select Course · Bestlink College of the Philippines</title>
    <link rel="stylesheet" href="../assets/css/utilities.css" />
    <script src="../assets/vendor/vue.global.prod.js"></script>
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css" />
    
    <link rel="stylesheet" href="../assets/css/selecting-course.css" />
    <link rel="stylesheet" href="../assets/css/site.css" />
</head>
<body data-page="selecting-course">

<div id="app">
    <!-- ===== TOP NAV ===== -->
    <header class="site-header fixed top-0 left-0 right-0 z-50 glass-dark text-white py-3 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-between">
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
                <a href="contact.php" class="nav-link px-1 py-1 text-gray-300">Contact</a>
                <a href="online-admission.html" class="btn-sky px-6 py-2 rounded-full font-bold flex items-center gap-2 text-sm">
                    <i class="fas fa-user-graduate"></i> Enroll Now!
                </a>
            </nav>
        </div>
    </header>

    <!-- ===== HERO ===== -->
    <section class="course-hero">
        <div class="float-shape w-96 h-96 bg-[#4fc3f7] top-20 -left-20"></div>
        <div class="float-shape w-64 h-64 bg-[#29b6f6] bottom-20 -right-20"></div>

        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="text-center text-white">
                <span class="inline-block bg-white/10 backdrop-blur-sm text-bcp-sky text-xs font-semibold px-5 py-2 rounded-full mb-6 border border-white/10 hero-text">
                    <i class="fas fa-graduation-cap mr-2"></i> Bestlink College of the Philippines
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold leading-tight hero-text delay-1">
                    Enrollment Management <span class="text-bcp-sky">System</span>
                </h1>
                <p class="text-lg text-[#b0d4e8] mt-4 max-w-2xl mx-auto leading-relaxed hero-text delay-2">
                    Application Process — Select your course.
                </p>
            </div>
        </div>
    </section>

    <!-- ===== COURSE SELECTION ===== -->
    <section class="py-16 bg-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[#4fc3f7]/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#29b6f6]/5 rounded-full blur-3xl"></div>

        <div class="max-w-6xl mx-auto px-4 relative z-10">
            <?php if ($message): ?>
                <div class="<?php echo $messageType === 'success' ? 'message-success' : 'message-error'; ?>">
                    <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo $e($message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($courses)): ?>
                <!-- No Courses Available -->
                <div class="no-courses">
                    <div class="icon"><i class="fas fa-book-open"></i></div>
                    <h3>No Courses Available</h3>
                    <p>There are currently no courses available. Please contact the admissions office for more information.</p>
                </div>
            <?php else: ?>
                <!-- Note -->
                <div class="note-text mb-6">
                    <i class="fas fa-info-circle text-bcp-sky mr-2"></i>
                    Select your desired course below. You will be asked to provide your personal information in the next step.
                    <span class="block text-xs text-gray-500 mt-1">Total courses available: <?php echo count($courses); ?></span>
                </div>

                <form method="POST" action="" id="courseForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card"
                                 tabindex="0" role="radio"
                                 :aria-checked="selectedCourse === <?php echo (int) $course->id; ?>"
                                 @keydown.enter.prevent="selectCourse(<?php echo (int) $course->id; ?>)"
                                 @keydown.space.prevent="selectCourse(<?php echo (int) $course->id; ?>)"
                                 @click="selectCourse(<?php echo (int) $course->id; ?>)"
                                 :class="{ selected: selectedCourse === <?php echo (int) $course->id; ?> }">
                                <div class="course-code">
                                    <?php echo $e($course->code); ?>
                                    <span class="badge"><?php echo $e(ucfirst($branch)); ?></span>
                                </div>
                                <div class="course-name"><?php echo $e($course->name); ?></div>
                                <div class="course-years"><?php echo (int) $course->years; ?> Year Program</div>
                                <input type="radio"
                                       name="course_id"
                                       value="<?php echo (int) $course->id; ?>"
                                       hidden
                                       :checked="selectedCourse === <?php echo (int) $course->id; ?>" />
                                <button type="submit" class="proceed-btn" @click.stop="selectCourse(<?php echo (int) $course->id; ?>)">
                                    <i class="fas fa-arrow-right mr-2"></i> Proceed
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Hidden submit for form compatibility -->
                    <input class="selecting-course-detail-1" type="submit" id="hiddenSubmit" />
                </form>
            <?php endif; ?>

            <p class="text-center text-sm text-[#1a3a6b] mt-8">
                BCP Online Admission © <?php echo date('Y'); ?>
            </p>
        </div>
    </section>

    <!-- ===== STATS SECTION ===== -->
    <div class="bg-bcp-deep text-white py-16 my-6 rounded-[4rem] rounded-b-3xl mx-4 md:mx-8 shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-80 h-80 bg-[#4fc3f7]/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-[#29b6f6]/10 rounded-full blur-2xl"></div>

        <div class="max-w-7xl mx-auto px-4 relative z-10 flex flex-wrap justify-around items-center gap-8 text-center">
            <div>
                <div class="stat-number text-5xl">45,000+</div>
                <div class="font-light opacity-80 mt-1">Students</div>
                <div class="text-xs opacity-50">A thriving learning community across all programs and campuses.</div>
            </div>
            <div>
                <div class="stat-number text-5xl">2002</div>
                <div class="font-light opacity-80 mt-1">Established</div>
                <div class="text-xs opacity-50">Founded in June 2002 — over two decades of quality education.</div>
            </div>
            <div>
                <div class="stat-number text-5xl">3</div>
                <div class="font-light opacity-80 mt-1">Campuses</div>
                <div class="text-xs opacity-50">Millionaire's Village, Main, and Bulacan campuses serving learners.</div>
            </div>
            <div class="glass px-8 py-4 rounded-full flex items-center gap-4 border border-white/10">
                <img src="../assets/bcp-logo.png" alt="BCP" class="h-10 w-auto" />
                <span class="font-bold tracking-wider text-lg">BCP</span>
                <span class="opacity-30">|</span>
                <span class="font-light">PHILIPPINES</span>
            </div>
        </div>
    </div>

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
                <span>BCP Online Admission © <?php echo date('Y'); ?></span>
                <span>Developed by BCP MIS Department</span>
            </div>
        </div>
    </footer>
</div>

<!-- ===== VUE APP ===== -->


<script id="page-data" type="application/json"><?= PageView::json($clientData ?? []) ?></script>
<script src="../assets/js/selecting-course.js"></script>
<script src="../assets/js/site.js"></script>
</body>
</html>
