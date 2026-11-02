(() => {
'use strict';
const { createApp, onMounted } = Vue;

  const app = createApp({
    setup() {
      onMounted(() => {
        if (!window.gsap || !window.ScrollTrigger || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        gsap.registerPlugin(ScrollTrigger);

        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        tl.from('h1', { opacity: 0, y: 40, duration: 0.8 })
          .from('.inline-block', { opacity: 0, y: 20, duration: 0.6 }, '-=0.4')
          .from('.text-lg', { opacity: 0, y: 20, duration: 0.6 }, '-=0.3');

        gsap.from('.service-card', {
          scrollTrigger: { trigger: '.service-card', start: 'top 85%' },
          opacity: 0,
          y: 30,
          duration: 0.6,
          stagger: 0.08,
          ease: 'power2.out'
        });

        gsap.from('.stat-number', {
          scrollTrigger: { trigger: '.stat-number', start: 'top 90%' },
          opacity: 0,
          scale: 0.9,
          duration: 0.6,
          stagger: 0.1,
          ease: 'power2.out'
        });

        gsap.from('.cta-section', {
          scrollTrigger: { trigger: '.cta-section', start: 'top 85%' },
          opacity: 0,
          y: 30,
          duration: 0.8,
          ease: 'power2.out'
        });
      });

      return {};
    }
  });

  app.mount('#app');

})();
