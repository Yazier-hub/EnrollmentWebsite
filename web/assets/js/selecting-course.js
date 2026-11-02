(() => {
'use strict';
const { createApp, ref } = Vue;

    const app = createApp({
        setup() {
            const selectedCourse = ref(0);

            const selectCourse = (courseId) => {
                selectedCourse.value = courseId;
            };

            return {
                selectedCourse,
                selectCourse
            };
        }
    });

    app.mount('#app');

})();
