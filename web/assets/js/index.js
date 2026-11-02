(() => {
'use strict';
const { createApp, ref, onMounted } = Vue;

  const app = createApp({
    setup() {
      const programs = ref([
        { title: 'BS Information Technology', icon: 'fas fa-laptop-code', desc: 'Build software, networks, and digital systems with a future-ready, industry-aligned curriculum.', tags: ['Software Development', 'Networking', 'Web & Mobile'] },
        { title: 'BS Hospitality Management', icon: 'fas fa-utensils', desc: 'Master hotel, restaurant, and culinary operations through hands-on training.', tags: ['Culinary Arts', 'Hotel Operations', 'Events'] },
        { title: 'BS Office Administration', icon: 'fas fa-building', desc: 'Develop office management, communication, and administrative expertise.', tags: ['Office Management', 'Records', 'Communication'] },
        { title: 'BS Business Administration', icon: 'fas fa-chart-line', desc: 'Develop leadership, management, and entrepreneurial skills for modern business.', tags: ['Marketing', 'Financial Management', 'Entrepreneurship'] },
        { title: 'BS Criminology', icon: 'fas fa-balance-scale', desc: 'Prepare for a career in law enforcement, forensics, and public safety.', tags: ['Forensic Science', 'Criminal Law', 'Crime Detection'] },
        { title: 'Bachelor of Elementary Education', icon: 'fas fa-chalkboard-teacher', desc: 'Train to become a competent and compassionate elementary educator.', tags: ['Pedagogy', 'Child Development', 'Practice Teaching'] }
      ]);

      const whyItems = ref([
        { icon: 'fas fa-graduation-cap', title: 'Quality Education', desc: 'Industry-relevant curriculum delivered by experienced educators.' },
        { icon: 'fas fa-microscope', title: 'Modern Facilities', desc: 'Up-to-date laboratories, studios, and digital classrooms.' },
        { icon: 'fas fa-briefcase', title: 'Career-Ready Skills', desc: 'Hands-on training and internships that prepare you for the workplace.' },
        { icon: 'fas fa-users', title: 'Vibrant Community', desc: 'A diverse student body with active clubs and organizations.' },
        { icon: 'fas fa-hand-holding-heart', title: 'Supportive Environment', desc: 'Guidance, counseling, and student services every step of the way.' },
        { icon: 'fas fa-hand-holding-usd', title: 'Accessible Opportunities', desc: 'Scholarships and financial aid for deserving students.' }
      ]);

      const spiritItems = ref([
        { icon: 'fas fa-rocket', title: 'Driven by Innovation', desc: 'A symbol of forward-thinking learning and modern, tech-ready education.' },
        { icon: 'fas fa-shield-alt', title: 'Guardian of Excellence', desc: 'Embodying our promise to train every student to be the best.' },
        { icon: 'fas fa-users', title: 'Spirit of the Community', desc: 'Rallying our students, faculty, and alumni around shared success.' }
      ]);

      onMounted(() => {
        if (!window.gsap || !window.ScrollTrigger || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        gsap.registerPlugin(ScrollTrigger);

        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        tl.from('.hero-image-bg', { opacity: 0, scale: 0.95, duration: 1.2, delay: 0.3 })
          .from('.hero-image-content', { opacity: 0, y: 40, duration: 0.8 }, '-=0.6')
          .from('h1', { opacity: 0, y: 50, duration: 0.8 }, '-=0.4')
          .from('.inline-block', { opacity: 0, y: 20, duration: 0.6, stagger: 0.1 }, '-=0.3');

        gsap.from('.stat-number', {
          scrollTrigger: { trigger: '.stat-number', start: 'top 90%' },
          opacity: 0,
          scale: 0.8,
          duration: 0.8,
          stagger: 0.15,
          ease: 'power3.out'
        });

        gsap.utils.toArray('.program-card').forEach((card, i) => {
          gsap.from(card, {
            scrollTrigger: { trigger: card, start: 'top 85%' },
            opacity: 0,
            y: 60,
            duration: 0.8,
            delay: i * 0.1,
            ease: 'power3.out'
          });
        });

        gsap.utils.toArray('.spirit-card').forEach((card, i) => {
          gsap.from(card, {
            scrollTrigger: { trigger: card, start: 'top 85%' },
            opacity: 0,
            y: 50,
            duration: 0.7,
            delay: i * 0.15,
            ease: 'power3.out'
          });
        });

        gsap.utils.toArray('.why-card').forEach((card, i) => {
          gsap.from(card, {
            scrollTrigger: { trigger: card, start: 'top 88%' },
            opacity: 0,
            y: 50,
            duration: 0.7,
            delay: i * 0.08,
            ease: 'power3.out'
          });
        });
      });

      return { programs, whyItems, spiritItems };
    }
  });

  app.mount('#app');

})();
