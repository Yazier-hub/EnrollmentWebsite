(() => {
'use strict';
const { createApp, ref } = Vue;

    const app = createApp({
        setup() {
            const selectedType = ref('college');

            const proceedToBranch = () => {
                if (selectedType.value) {
                    window.location.href = 'selecting-branch.html';
                }
            };

            return {
                selectedType,
                proceedToBranch
            };
        }
    });

    app.mount('#app');

})();
