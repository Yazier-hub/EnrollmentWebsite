(() => {
'use strict';
const { createApp, ref } = Vue;

    const app = createApp({
        setup() {
            const selectBranch = (branch) => {
                window.location.href = 'selecting-course.php?branch=' + branch;
            };

            return {
                selectBranch
            };
        }
    });

    app.mount('#app');

})();
