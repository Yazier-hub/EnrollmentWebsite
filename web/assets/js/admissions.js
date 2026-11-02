(() => {
'use strict';
const { createApp, ref, onMounted } = Vue;

  const app = createApp({
    setup() {
      const activeFaq = ref(null);

      const faqs = ref([
        {
          question: 'Who can apply for admission?',
          answer: 'Senior High School graduates, transferees, returnees, and ALS/PEPT passers are eligible to apply for admission to Bestlink College of the Philippines.'
        },
        {
          question: 'What documents are required for enrollment?',
          answer: 'Required documents include Form 138 (Report Card), Form 137, Certificate of Good Moral, PSA Birth Certificate, ID pictures, and Barangay Clearance. Additional requirements may apply for transferees and SHS applicants.'
        },
        {
          question: 'Is there an entrance examination?',
          answer: 'Yes, BCP conducts an entrance examination for all applicants. The exam assesses academic readiness and helps determine the appropriate program placement.'
        },
        {
          question: 'Are scholarships and financial aid available?',
          answer: 'Yes, BCP offers various scholarship programs and financial aid to deserving students, including those from marginalized families. Contact the Admissions Office for more information.'
        },
        {
          question: 'Can I enroll online?',
          answer: 'Yes, BCP offers an online application process that is quick, easy, and secure. You can start your application, save your work, and complete it later at your convenience.'
        },
        {
          question: 'Does BCP charge tuition fees?',
          answer: 'BCP offers quality education with NO TUITION FEE. Students only pay minimal miscellaneous fees, making education accessible to all.'
        }
      ]);

      const toggleFaq = (index) => {
        activeFaq.value = activeFaq.value === index ? null : index;
      };

      onMounted(() => {
        if (!window.gsap || !window.ScrollTrigger || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        gsap.registerPlugin(ScrollTrigger);

        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        tl.from('h1', { opacity: 0, y: 40, duration: 0.8 })
          .from('.inline-block', { opacity: 0, y: 20, duration: 0.6 }, '-=0.4')
          .from('.text-lg', { opacity: 0, y: 20, duration: 0.6 }, '-=0.3')
          .from('.enrollment-banner-image', { opacity: 0, scale: 0.95, duration: 0.8 }, '-=0.2');

        gsap.from('.info-card', {
          scrollTrigger: { trigger: '.info-card', start: 'top 85%' },
          opacity: 0,
          y: 30,
          duration: 0.6,
          stagger: 0.1,
          ease: 'power2.out'
        });

        gsap.from('.req-card', {
          scrollTrigger: { trigger: '.req-card', start: 'top 85%' },
          opacity: 0,
          y: 30,
          duration: 0.6,
          stagger: 0.1,
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

        gsap.from('.faq-item', {
          scrollTrigger: { trigger: '.faq-item', start: 'top 90%' },
          opacity: 0,
          y: 20,
          duration: 0.5,
          stagger: 0.08,
          ease: 'power2.out'
        });
      });

      return {
        activeFaq,
        faqs,
        toggleFaq
      };
    }
  });

  app.mount('#app');

})();
