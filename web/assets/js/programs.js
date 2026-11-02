(() => {
'use strict';
const { createApp, ref, onMounted } = Vue;

  const app = createApp({
    setup() {
      const activeTab = ref('college');

      const tabs = ref([
        { key: 'college', label: 'College Programs' },
        { key: 'senior', label: 'Senior High' },
        { key: 'tesda', label: 'TESDA' }
      ]);

      const collegePrograms = ref([
        { code: 'BSIT', title: 'BS Information Technology', icon: 'fas fa-laptop-code', duration: 4, desc: 'Build software, networks, and digital systems with a future-ready, industry-aligned curriculum.', tags: ['Software Development', 'Networking', 'Web & Mobile'] },
        { code: 'BSHM', title: 'BS Hospitality Management', icon: 'fas fa-utensils', duration: 4, desc: 'Master hotel, restaurant, and culinary operations through hands-on training.', tags: ['Culinary Arts', 'Hotel Operations', 'Events'] },
        { code: 'BSOA', title: 'BS Office Administration', icon: 'fas fa-building', duration: 4, desc: 'Develop office management, communication, and administrative expertise.', tags: ['Office Management', 'Records', 'Communication'] },
        { code: 'BSBA', title: 'BS Business Administration', icon: 'fas fa-chart-line', duration: 4, desc: 'Develop leadership, management, and entrepreneurial skills for modern business.', tags: ['Marketing', 'Financial Management', 'Entrepreneurship'] },
        { code: 'BSCrim', title: 'BS Criminology', icon: 'fas fa-balance-scale', duration: 4, desc: 'Prepare for a career in law enforcement, forensics, and public safety.', tags: ['Forensic Science', 'Criminal Law', 'Crime Detection'] },
        { code: 'BEED', title: 'Bachelor of Elementary Education', icon: 'fas fa-chalkboard-teacher', duration: 4, desc: 'Train to become a competent and compassionate elementary educator.', tags: ['Pedagogy', 'Child Development', 'Practice Teaching'] },
        { code: 'BSED', title: 'Bachelor of Secondary Education', icon: 'fas fa-chalkboard', duration: 4, desc: 'Specialize in teaching and shape the next generation of high school learners.', tags: ['Major Specialization', 'Curriculum', 'Practice Teaching'] },
        { code: 'BSCpE', title: 'BS Computer Engineering', icon: 'fas fa-microchip', duration: 4, desc: 'Engineer hardware and software systems, from embedded devices to intelligent machines.', tags: ['Embedded Systems', 'Microprocessors', 'Robotics'] },
        { code: 'BSTM', title: 'BS Tourism Management', icon: 'fas fa-plane', duration: 4, desc: 'Explore travel, tourism, and destination management on a global scale.', tags: ['Travel Operations', 'Eco-Tourism', 'Destinations'] },
        { code: 'BSEnt', title: 'BS Entrepreneurship', icon: 'fas fa-lightbulb', duration: 4, desc: 'Learn to build, launch, and scale your own business ventures.', tags: ['Business Planning', 'Innovation', 'Venture Growth'] },
        { code: 'BSAIS', title: 'BS Accounting Information System', icon: 'fas fa-calculator', duration: 4, desc: 'Blend accounting principles with information systems and data-driven finance.', tags: ['Accounting', 'Information Systems', 'Auditing'] },
        { code: 'BSPsych', title: 'BS Psychology', icon: 'fas fa-brain', duration: 4, desc: 'Understand human behavior and prepare for careers in counseling and research.', tags: ['Clinical Psychology', 'Research', 'Counseling'] },
        { code: 'BLIS', title: 'BL Information Science', icon: 'fas fa-book-open', duration: 4, desc: 'Manage information resources, libraries, and knowledge systems.', tags: ['Information Management', 'Cataloguing', 'Digital Archives'] }
      ]);

      const seniorHighPrograms = ref([
        { code: 'STEM', title: 'Science, Technology, Engineering, and Mathematics', icon: 'fas fa-flask', duration: 2, desc: 'Prepare for college programs in science, technology, engineering, and mathematics fields.', tags: ['Research', 'Advanced Math', 'Science'] },
        { code: 'ABM', title: 'Accountancy, Business, and Management', icon: 'fas fa-chart-pie', duration: 2, desc: 'Develop business acumen and management skills for careers in entrepreneurship and corporate sectors.', tags: ['Accounting', 'Marketing', 'Management'] },
        { code: 'HUMSS', title: 'Humanities and Social Sciences', icon: 'fas fa-users-cog', duration: 2, desc: 'Explore social sciences, literature, and communication for careers in public service and media.', tags: ['Literature', 'Social Studies', 'Communication'] },
        { code: 'TVL', title: 'Technical-Vocational-Livelihood', icon: 'fas fa-tools', duration: 2, desc: 'Hands-on training in specialized technical skills for immediate employment or entrepreneurship.', tags: ['Skills Training', 'Certification', 'Hands-on'] }
      ]);

      const tesdaPrograms = ref([
        { code: 'TESDA', title: 'Animation', icon: 'fas fa-film', duration: '6 months', desc: '2D and 3D animation production skills for the creative and media industry.', tags: ['2D Animation', '3D Animation', 'Creative'] },
        { code: 'TESDA', title: 'Visual Graphics Design', icon: 'fas fa-paint-brush', duration: '6 months', desc: 'Digital design, layout, and visual communication for print and web.', tags: ['Digital Design', 'Layout', 'Web Design'] },
        { code: 'TESDA', title: 'Housekeeping', icon: 'fas fa-bed', duration: '6 months', desc: 'Professional housekeeping operations for hotels and accommodation services.', tags: ['Hotel Operations', 'Housekeeping', 'Hospitality'] },
        { code: 'TESDA', title: 'Bartending', icon: 'fas fa-glass-martini-alt', duration: '3 months', desc: 'Beverage preparation, mixology, and bar service standards.', tags: ['Mixology', 'Beverage', 'Bar Service'] },
        { code: 'TESDA', title: 'Baking and Pastry Production', icon: 'fas fa-cookie', duration: '6 months', desc: 'Baking, pastry, and dessert production techniques for the food industry.', tags: ['Baking', 'Pastry', 'Dessert'] },
        { code: 'TESDA', title: 'Food and Beverage Services', icon: 'fas fa-concierge-bell', duration: '3 months', desc: 'Dining service operations and customer service for restaurants and hotels.', tags: ['Food Service', 'F&B', 'Customer Service'] },
        { code: 'TESDA', title: 'Cookery', icon: 'fas fa-utensil-spoon', duration: '6 months', desc: 'Commercial cooking and kitchen operations for the food service industry.', tags: ['Cooking', 'Kitchen Ops', 'Food Industry'] },
        { code: 'TESDA', title: 'Tour Guiding Services', icon: 'fas fa-map-signs', duration: '3 months', desc: 'Professional tour guiding, itinerary handling, and destination management.', tags: ['Tour Guiding', 'Itinerary', 'Destinations'] },
        { code: 'TESDA', title: 'Front Office Services', icon: 'fas fa-hotel', duration: '3 months', desc: 'Hotel front desk, reservations, and guest services operations.', tags: ['Front Desk', 'Reservations', 'Guest Services'] }
      ]);

      onMounted(() => {
        if (!window.gsap || !window.ScrollTrigger || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        gsap.registerPlugin(ScrollTrigger);

        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        tl.from('h1', { opacity: 0, y: 40, duration: 0.8 })
          .from('.inline-block', { opacity: 0, y: 20, duration: 0.6 }, '-=0.4')
          .from('.text-lg', { opacity: 0, y: 20, duration: 0.6 }, '-=0.3');

        gsap.from('.stat-number', {
          scrollTrigger: { trigger: '.stat-number', start: 'top 90%' },
          opacity: 0,
          scale: 0.9,
          duration: 0.6,
          stagger: 0.1,
          ease: 'power2.out'
        });
      });

      return {
        activeTab,
        tabs,
        collegePrograms,
        seniorHighPrograms,
        tesdaPrograms
      };
    }
  });

  app.mount('#app');

})();
