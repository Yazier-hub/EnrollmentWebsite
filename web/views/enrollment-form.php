<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Enrollment Form · Bestlink College of the Philippines</title>
    <link rel="stylesheet" href="../assets/css/utilities.css" />
    <script src="../assets/vendor/vue.global.prod.js"></script>
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css" />
    <!-- Leaflet -->
    <link rel="stylesheet" href="../assets/vendor/leaflet/leaflet.css" />
    <script src="../assets/vendor/leaflet/leaflet.js"></script>
    
    <link rel="stylesheet" href="../assets/css/enrollment-form.css" />
    <link rel="stylesheet" href="../assets/css/site.css" />
</head>
<body data-page="enrollment-form">

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
    <section class="form-hero">
        <div class="float-shape w-96 h-96 bg-[#4fc3f7] top-20 -left-20"></div>
        <div class="float-shape w-64 h-64 bg-[#29b6f6] bottom-20 -right-20"></div>

        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="text-center text-white">
                <span class="inline-block bg-white/10 backdrop-blur-sm text-bcp-sky text-xs font-semibold px-5 py-2 rounded-full mb-6 border border-white/10 hero-text">
                    <i class="fas fa-file-alt mr-2"></i> Application Form
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold leading-tight hero-text delay-1">
                    Enrollment Management <span class="text-bcp-sky">System</span>
                </h1>
                <p class="text-lg text-[#b0d4e8] mt-4 max-w-2xl mx-auto leading-relaxed hero-text delay-2">
                    <?php if ($course_info): ?>
                        Applying for <strong class="text-white"><?php echo $e($course_info['code']); ?></strong> - <?php echo $e($course_info['name']); ?>
                    <?php else: ?>
                        Complete your application form.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </section>

    <!-- ===== FORM ===== -->
    <section class="py-16 bg-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[#4fc3f7]/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#29b6f6]/5 rounded-full blur-3xl"></div>

        <div class="max-w-4xl mx-auto px-4 relative z-10">
            <div class="form-container">
                <?php if ($message): ?>
                    <div class="<?php
                        echo $messageType === 'success' ? 'success-message' :
                            ($messageType === 'warning' ? 'warning-message' : 'error-message');
                    ?>">
                        <?php if ($messageType === 'success'): ?>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                            <h3>Application Submitted Successfully!</h3>
                            <p><?php echo $e($message); ?></p>
                            <p class="text-sm mt-2">You will be redirected to the enrollment page.</p>
                        <?php else: ?>
                            <i class="fas <?php echo $messageType === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                            <?php echo $message; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$message || $messageType !== 'success'): ?>
                <div>
                    <!-- Step Indicator -->
                    <div class="step-indicator" aria-label="Application steps">
                        <button type="button" class="step" :class="{ active: currentStep === 0, completed: currentStep > 0 }" :aria-current="currentStep === 0 ? 'step' : undefined" @click="goToStep(0)">
                            <span class="num">1</span>
                            <span>Basic Info</span>
                        </button>
                        <button type="button" class="step" :class="{ active: currentStep === 1, completed: currentStep > 1 }" :aria-current="currentStep === 1 ? 'step' : undefined" @click="goToStep(1)">
                            <span class="num">2</span>
                            <span>Parent/Guardian</span>
                        </button>
                        <button type="button" class="step" :class="{ active: currentStep === 2, completed: currentStep > 2 }" :aria-current="currentStep === 2 ? 'step' : undefined" @click="goToStep(2)">
                            <span class="num">3</span>
                            <span>Education</span>
                        </button>
                        <button type="button" class="step" :class="{ active: currentStep === 3 }" :aria-current="currentStep === 3 ? 'step' : undefined" @click="goToStep(3)">
                            <span class="num">4</span>
                            <span>Summary</span>
                        </button>
                    </div>

                    <form method="POST" action="" id="enrollmentForm" enctype="multipart/form-data" @submit="validateForm" novalidate>
                        <input type="hidden" name="action" value="submit" />
                        <input type="hidden" name="course_id" value="<?php echo $e($course_id); ?>" />

                        <?php if ($course_info): ?>
                            <div class="bg-[#f8faff] rounded-xl p-4 mb-6 border border-[#e2ebf6] text-center">
                                <span class="text-sm text-[#1a3a6b]">Selected Course:</span>
                                <span class="course-badge"><?php echo $e($course_info['code']); ?> - <?php echo $e($course_info['name']); ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- ===== SECTION 1: BASIC INFORMATION ===== -->
                        <div class="form-section" :class="{ active: currentStep === 0 }">
                            <h2 class="text-2xl font-bold text-[#0a1e3d] mb-6">Basic Information</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="form-label" for="admission_type">Admission Type <span class="text-red-500">*</span></label>
                                    <select name="admission_type" id="admission_type" class="form-input" required v-model="admissionType">
                                        <option value="">Select Admission Type</option>
                                        <option value="freshmen">Freshmen</option>
                                        <option value="transferee">Transferee</option>
                                        <option value="returnee">Returnee</option>
                                        <option value="senior_high">Senior High School</option>
                                    </select>
                                </div>

                                <div>
                                    <p class="form-label" id="working-student-label">Are you a Working Student?</p>
                                    <div class="radio-group mt-1" role="group" aria-labelledby="working-student-label">
                                        <label>
                                            <input type="radio" name="working_student" value="Yes" <?php echo $formData['working_student'] === 'Yes' ? 'checked' : ''; ?> /> Yes
                                        </label>
                                        <label>
                                            <input type="radio" name="working_student" value="No" <?php echo $formData['working_student'] === 'No' ? 'checked' : ''; ?> /> No
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label" for="surname">Last Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="surname" class="form-input" placeholder="Enter last name" value="<?php echo $e($formData['surname']); ?>" id="surname" required />
                                </div>

                                <div>
                                    <label class="form-label" for="first_name">First Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="first_name" class="form-input" placeholder="Enter first name" value="<?php echo $e($formData['first_name']); ?>" id="first_name" required />
                                </div>

                                <div>
                                    <label class="form-label" for="middle_name">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-input" placeholder="Enter middle name" value="<?php echo $e($formData['middle_name']); ?>" id="middle_name" />
                                </div>

                                <div>
                                    <label class="form-label" for="suffix">Suffix</label>
                                    <input type="text" name="suffix" class="form-input" placeholder="Jr., Sr., III" value="<?php echo $e($formData['suffix']); ?>" id="suffix" />
                                </div>

                                <div>
                                    <label class="form-label" for="sex">Sex <span class="text-red-500">*</span></label>
                                    <select name="sex" class="form-input" required id="sex">
                                        <option value="">Select Sex</option>
                                        <option value="Male" <?php echo $formData['sex'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $formData['sex'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $formData['sex'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label" for="civil_status">Civil Status <span class="text-red-500">*</span></label>
                                    <select name="civil_status" class="form-input" required id="civil_status">
                                        <option value="">Select Civil Status</option>
                                        <option value="Single" <?php echo $formData['civil_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                                        <option value="Married" <?php echo $formData['civil_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                                        <option value="Divorced" <?php echo $formData['civil_status'] === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                        <option value="Widowed" <?php echo $formData['civil_status'] === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label" for="religion">Religion</label>
                                    <input type="text" name="religion" class="form-input" placeholder="Enter religion" value="<?php echo $e($formData['religion']); ?>" id="religion" />
                                </div>

                                <div>
                                    <label class="form-label" for="date_of_birth">Date of Birth <span class="text-red-500">*</span></label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" class="form-input" value="<?php echo $e($formData['date_of_birth']); ?>" required @change="calculateAge" />
                                </div>

                                <div>
                                    <label class="form-label" for="age">Age <span class="text-red-500">*</span></label>
                                    <input type="number" name="age" id="age" class="form-input" placeholder="Age" value="<?php echo $e($formData['age']); ?>" readonly required />
                                </div>

                                <div>
                                    <label class="form-label" for="place_of_birth">Place of Birth <span class="text-red-500">*</span></label>
                                    <input type="text" name="place_of_birth" class="form-input" placeholder="City, Province" value="<?php echo $e($formData['place_of_birth']); ?>" id="place_of_birth" required />
                                </div>

                                <div>
                                    <label class="form-label" for="email">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" class="form-input" placeholder="you@example.com" value="<?php echo $e($formData['email']); ?>" id="email" required />
                                </div>

                                <div>
                                    <label class="form-label" for="contact_number">Contact Number <span class="text-red-500">*</span></label>
                                    <input type="tel" name="contact_number" class="form-input" placeholder="0912 345 6789" value="<?php echo $e($formData['contact_number']); ?>" id="contact_number" required />
                                </div>

                                <div>
                                    <label class="form-label" for="facebook">Facebook Name</label>
                                    <input type="text" name="facebook" class="form-input" placeholder="Facebook profile name" value="<?php echo $e($formData['facebook']); ?>" id="facebook" />
                                </div>
                                <!-- MESSENGER NAME REMOVED -->
                            </div>

                            <!-- ====== MAP-BASED ADDRESS ====== -->
                            <div class="mt-6">
                                <label class="form-label" for="addressSearch">
                                    <i class="fas fa-map-marked-alt text-bcp-sky mr-1"></i>
                                    Search Your Location (optional)
                                </label>

                                <div class="autocomplete-wrapper">
                                    <input
                                        type="text"
                                        id="addressSearch"
                                        class="form-input"
                                        placeholder="Search barangay, city, or province (e.g. Novaliches, Quezon City)…"
                                        autocomplete="off"
                                        v-model="searchQuery"
                                        @input="onSearchInput"
                                        @keydown.down.prevent="moveHighlight(1)"
                                        @keydown.up.prevent="moveHighlight(-1)"
                                        @keydown.enter.prevent="selectHighlighted"
                                        @blur="closeSuggestionsSoon"
                                        @focus="reopenSuggestions"
                                    />
                                    <div v-if="suggestions.length && showSuggestions" class="autocomplete-list">
                                        <div
                                            v-for="(s, i) in suggestions"
                                            :key="s.place_id"
                                            class="autocomplete-item"
                                            :class="{ active: i === highlightedIndex }"
                                            @mousedown.prevent="selectSuggestion(s)"
                                        >
                                            <div class="ac-main">[[ s.display_name.split(',')[0] ]]</div>
                                            <div class="ac-sub">[[ s.display_name ]]</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="map-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Search or click the map to pin your location, or enter your address below.
                                </div>

                                <div id="map" role="region" aria-label="Choose your address on the map"></div>

                                <!-- ===== Typed House/Block/Lot (user input) ===== -->
                                <div class="mt-4">
                                    <label class="form-label" for="address_street">House No. / Block / Lot <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="address_street"
                                        id="address_street"
                                        class="form-input"
                                        placeholder="e.g. Blk 3 Lot 74, Purok 2"
                                        v-model="streetLine"
                                        @input="onStreetInput"
                                        required
                                    />
                                </div>

                                <!-- Editable address components also work without the map service. -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                    <div><label class="form-label" for="address_barangay">Barangay *</label><input id="address_barangay" name="address_barangay" class="form-input" v-model="address.barangay" required /></div>
                                    <div><label class="form-label" for="address_city">City / Municipality *</label><input id="address_city" name="address_city" class="form-input" v-model="address.city" required /></div>
                                    <div><label class="form-label" for="address_province">Province / Metro Manila *</label><input id="address_province" name="address_province" class="form-input" v-model="address.province" required /></div>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">You can enter or correct your address here if the map cannot find it.</p>
                                <!-- Combined address and optional map coordinates -->
                                <input type="hidden" name="address_region"   :value="address.region" />
                                <input type="hidden" name="address_complete" :value="fullAddress" />
                                <input type="hidden" name="address_lat"      :value="address.lat" />
                                <input type="hidden" name="address_lng"      :value="address.lng" />

                                <div v-if="address.city || address.province || address.barangay" class="address-summary">
                                    <div class="row">
                                        <span>Full address:</span>
                                        <strong>[[ fullAddress || '—' ]]</strong>
                                    </div>
                                    <div class="row"><span>Barangay:</span><strong>[[ address.barangay || '—' ]]</strong></div>
                                    <div class="row"><span>City/Mun.:</span><strong>[[ address.city || '—' ]]</strong></div>
                                    <div class="row"><span>Province:</span><strong>[[ address.province || '—' ]]</strong></div>
                                    <div class="row"><span>Region:</span><strong>[[ address.region || '—' ]]</strong></div>
                                    <div class="row"><span>Coordinates:</span><strong>[[ address.lat || '—' ]], [[ address.lng || '—' ]]</strong></div>
                                </div>

                                <p v-if="addressError" class="text-sm text-red-500 mt-2">
                                    <i class="fas fa-exclamation-circle mr-1"></i> [[ addressError ]]
                                </p>
                            </div>

                            <div class="flex justify-end mt-6">
                                <button type="button" class="btn-sky px-8 py-3 rounded-full font-bold" @click="validateAndProceed(1)">
                                    Next <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ===== SECTION 2: PARENT/GUARDIAN ===== -->
                        <div class="form-section" :class="{ active: currentStep === 1 }">
                            <h2 class="text-2xl font-bold text-[#0a1e3d] mb-6">Parent's/Guardian's Information</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="form-label" for="parent_full_name">Parent/Guardian Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="parent_full_name" class="form-input" placeholder="Full name" value="<?php echo $e($formData['parent_full_name']); ?>" id="parent_full_name" required />
                                </div>
                                <div>
                                    <label class="form-label" for="parent_contact">Parent/Guardian Contact</label>
                                    <input type="tel" name="parent_contact" class="form-input" placeholder="0912 345 6789" value="<?php echo $e($formData['parent_contact']); ?>" id="parent_contact" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="form-label" for="parent_address">Parent/Guardian Address</label>
                                <input type="text" name="parent_address" class="form-input" placeholder="Complete address" value="<?php echo $e($formData['parent_address']); ?>" id="parent_address" />
                            </div>

                            <div class="flex justify-between mt-6">
                                <button type="button" class="btn-outline-sky px-8 py-3 rounded-full font-bold" @click="currentStep = 0">
                                    <i class="fas fa-arrow-left mr-2"></i> Back
                                </button>
                                <button type="button" class="btn-sky px-8 py-3 rounded-full font-bold" @click="currentStep = 2">
                                    Next <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ===== SECTION 3: EDUCATIONAL BACKGROUND ===== -->
                        <div class="form-section" :class="{ active: currentStep === 2 }">
                            <h2 class="text-2xl font-bold text-[#0a1e3d] mb-6">Educational Background</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="form-label" for="school_last_attended">Last School Attended <span class="text-red-500">*</span></label>
                                    <input type="text" name="school_last_attended" class="form-input" placeholder="School name" value="<?php echo $e($formData['school_last_attended']); ?>" id="school_last_attended" required />
                                </div>
                                <div>
                                    <label class="form-label" for="year_graduated">Year Graduated <span class="text-red-500">*</span></label>
                                    <input type="text" name="year_graduated" class="form-input" placeholder="e.g. 2024-2025" value="<?php echo $e($formData['year_graduated']); ?>" id="year_graduated" required />
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="form-label" for="how_hear">How did you hear about our school?</label>
                                <select name="how_hear" class="form-input" id="how_hear">
                                    <option value="">Select option</option>
                                    <option value="social_media" <?php echo $formData['how_hear'] === 'social_media' ? 'selected' : ''; ?>>Social Media</option>
                                    <option value="friend" <?php echo $formData['how_hear'] === 'friend' ? 'selected' : ''; ?>>Friend/Relative</option>
                                    <option value="school_website" <?php echo $formData['how_hear'] === 'school_website' ? 'selected' : ''; ?>>School Website</option>
                                    <option value="advertisement" <?php echo $formData['how_hear'] === 'advertisement' ? 'selected' : ''; ?>>Advertisement</option>
                                    <option value="others" <?php echo $formData['how_hear'] === 'others' ? 'selected' : ''; ?>>Others</option>
                                </select>
                            </div>

                            <div class="flex justify-between mt-6">
                                <button type="button" class="btn-outline-sky px-8 py-3 rounded-full font-bold" @click="currentStep = 1">
                                    <i class="fas fa-arrow-left mr-2"></i> Back
                                </button>
                                <button type="button" class="btn-sky px-8 py-3 rounded-full font-bold" @click="currentStep = 3">
                                    Next <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ===== SECTION 4: SUMMARY & REQUIREMENTS ===== -->
                        <div class="form-section" :class="{ active: currentStep === 3 }">
                            <h2 class="text-2xl font-bold text-[#0a1e3d] mb-6">Summary</h2>

                            <div class="bg-[#f8faff] rounded-xl p-6 mb-6 border border-[#e2ebf6]">
                                <p class="text-sm text-[#1a3a6b]">Please review your information before submitting.</p>
                                <?php if ($course_info): ?>
                                    <p class="text-sm text-[#1a3a6b] mt-2">
                                        <strong>Course:</strong> <?php echo $e($course_info['code']); ?> - <?php echo $e($course_info['name']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="text-sm text-[#1a3a6b] mt-2" v-if="fullAddress">
                                    <strong>Address:</strong> [[ fullAddress ]]
                                </p>
                            </div>

                            <!-- Dynamic Requirements Section -->
                            <div class="requirements-section" v-if="admissionType">
                                <div class="req-title">
                                    <i class="fas fa-file-upload"></i> Upload Enrollment Documents
                                </div>
                                <p class="text-sm text-[#1a3a6b] mb-2">Attach a clear copy of each required document. Files will be saved when you submit your application.</p>
                                <p id="document-upload-help" class="text-sm text-[#1a3a6b] mb-2">Accepted formats: JPG, PNG, WEBP, GIF, or PDF. Maximum 5 MB per file.</p>
                                <p class="text-sm text-[#1a3a6b] mb-3">Please also submit the original documents to your respective branch. Previously uploaded documents can be kept or replaced.</p>
                                <div class="space-y-3">
                                    <div
                                        v-for="requirement in (requiredDocsByType[admissionType] || [])"
                                        :key="requirement.id"
                                        class="rounded-lg border border-[#dce8f5] bg-white p-4"
                                    >
                                        <label :for="'requirement-' + requirement.id" class="form-label">
                                            <i class="fas fa-file-alt text-bcp-sky mr-2"></i>
                                            <strong class="text-[#0a1e3d]">[[ requirement.name ]]</strong>
                                            <span v-if="!existingRequirements[requirement.id]" class="text-red-500">*</span>
                                        </label>
                                        <p v-if="existingRequirements[requirement.id]" class="text-sm text-green-700 mb-2">
                                            Uploaded: [[ existingRequirements[requirement.id].file_name ]]. Choose a file below only to replace it.
                                        </p>
                                        <input
                                            class="form-input"
                                            type="file"
                                            :id="'requirement-' + requirement.id"
                                            :name="'requirements[' + requirement.id + ']'"
                                            accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,image/jpeg,image/png,image/webp,image/gif,application/pdf"
                                            :required="!existingRequirements[requirement.id]"
                                            aria-describedby="document-upload-help"
                                            @change="onDocumentChange($event, requirement.id)"
                                        />
                                        <p v-if="uploadErrors[requirement.id]" class="text-sm text-red-600 mt-2" role="alert">[[ uploadErrors[requirement.id] ]]</p>
                                        <p v-else-if="selectedDocuments[requirement.id]" class="text-sm text-green-700 mt-2" aria-live="polite">Ready to upload: [[ selectedDocuments[requirement.id] ]]</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                            <div>
                                                <label :for="'requirement-name-' + requirement.id" class="form-label">Document name (optional)</label>
                                                <input type="text" class="form-input" maxlength="255"
                                                    :id="'requirement-name-' + requirement.id"
                                                    :name="'requirement_names[' + requirement.id + ']'"
                                                    :value="existingRequirements[requirement.id]?.custom_name || ''"
                                                    :disabled="!selectedDocuments[requirement.id]"
                                                    placeholder="Defaults to the file name" />
                                            </div>
                                            <div>
                                                <label :for="'requirement-notes-' + requirement.id" class="form-label">Notes (optional)</label>
                                                <textarea class="form-input" rows="2" maxlength="2000"
                                                    :id="'requirement-notes-' + requirement.id"
                                                    :name="'requirement_notes[' + requirement.id + ']'"
                                                    :value="existingRequirements[requirement.id]?.notes || ''"
                                                    :disabled="!selectedDocuments[requirement.id]"
                                                    placeholder="Additional details about this upload"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="warning-message">
                                <i class="fas fa-info-circle mr-2"></i> Please select an Admission Type in Step 1 to view the requirements.
                            </div>

                            <div class="flex justify-between mt-6">
                                <button type="button" class="btn-outline-sky px-8 py-3 rounded-full font-bold" @click="currentStep = 2">
                                    <i class="fas fa-arrow-left mr-2"></i> Back
                                </button>
                                <button type="submit" class="btn-sky px-10 py-3 rounded-full font-bold">
                                    <i class="fas fa-paper-plane mr-2"></i> Submit Application
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <p class="text-center text-sm text-[#1a3a6b] mt-6">
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

<!-- ===== VUE APP + LEAFLET ===== -->


<script id="page-data" type="application/json"><?= PageView::json($clientData ?? []) ?></script>
<script src="../assets/js/enrollment-form.js"></script>
<script src="../assets/js/site.js"></script>
</body>
</html>
