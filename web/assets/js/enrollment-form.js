(() => {
'use strict';
const config = JSON.parse(document.getElementById('page-data').textContent);
const { createApp, ref, reactive, computed, onMounted, onBeforeUnmount, nextTick, watch } = Vue;
const requiredDocsByType = config.requiredDocsByType;
const existingRequirements = config.existingRequirements;

const app = createApp({
    delimiters: ['[[', ']]'],
    setup() {
        const currentStep = ref(0);
        const admissionType = ref((config.formData.admission_type ?? ''));
        const selectedDocuments = reactive({});
        const uploadErrors = reactive({});
        watch(admissionType, (type) => {
            const activeIds = (requiredDocsByType[type] || []).map(item => String(item.id));
            Object.keys(selectedDocuments).forEach(id => {
                if (!activeIds.includes(id)) {
                    delete selectedDocuments[id];
                    delete uploadErrors[id];
                }
            });
        });
        const onDocumentChange = (event, id) => {
            const input = event.target;
            const file = input.files[0];
            selectedDocuments[id] = '';
            uploadErrors[id] = '';
            input.setCustomValidity('');
            if (!file) return;
            if (file.size === 0 || file.size > 5 * 1024 * 1024) {
                uploadErrors[id] = 'Choose a non-empty file no larger than 5 MB.';
            } else if (!/\.(jpe?g|png|webp|gif|pdf)$/i.test(file.name)) {
                uploadErrors[id] = 'Choose a JPG, PNG, WEBP, GIF, or PDF file.';
            }
            input.setCustomValidity(uploadErrors[id]);
            if (!uploadErrors[id]) {
                selectedDocuments[id] = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
            }
        };

        /* ---------- Address state (bound to hidden inputs) ---------- */
        const address = reactive({
            region:    (config.formData.address_region ?? ''),
            province:  (config.formData.address_province ?? ''),
            city:      (config.formData.address_city ?? ''),
            barangay:  (config.formData.address_barangay ?? ''),
            lat:       (config.formData.address_lat ?? ''),
            lng:       (config.formData.address_lng ?? ''),
        });
        const streetLine = ref((config.formData.address_street ?? ''));
        const addressError = ref('');

        /* ---------- Full address = typed street + map components ---------- */
        const fullAddress = computed(() => {
            const parts = [];
            if (streetLine.value.trim()) parts.push(streetLine.value.trim());
            if (address.barangay)        parts.push('Brgy. ' + address.barangay);
            if (address.city)            parts.push(address.city);
            if (address.province)        parts.push(address.province);
            if (address.region)          parts.push(address.region);
            return parts.join(', ');
        });

        const onStreetInput = () => {
            // computed updates automatically; just clear error if any
            if (streetLine.value.trim()) addressError.value = '';
        };

        /* ---------- Autocomplete state ---------- */
        const searchQuery      = ref('');
        const suggestions      = ref([]);
        const showSuggestions  = ref(false);
        const highlightedIndex = ref(-1);
        let searchTimer = null;
        let lastRequestId = 0;

        /* ---------- Map refs ---------- */
        let map = null;
        let marker = null;

        /* ---------- Helpers ---------- */
        const debounceSearch = (fn, delay = 450) => (...args) => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => fn(...args), delay);
        };

        const parseAddress = (addr) => {
            const a = addr.address || {};
            const barangay = a.village || a.suburb || a.neighbourhood || a.quarter || a.hamlet || a.city_district || '';
            const city     = a.city || a.town || a.municipality || a.city_district || '';
            const province = a.state || a.province || a.region || '';
            const region   = a.region || a.state_district || a.county || '';
            return { barangay, city, province, region };
        };

        const reverseGeocode = async (lat, lng) => {
            addressError.value = '';
            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=en`,
                    { headers: { 'Accept': 'application/json' } }
                );
                if (!res.ok) throw new Error('Reverse geocoding failed');
                const data = await res.json();
                if (data.error) throw new Error(data.error);

                const parsed = parseAddress(data);
                address.lat      = Number(lat).toFixed(6);
                address.lng      = Number(lng).toFixed(6);
                address.barangay = parsed.barangay;
                address.city     = parsed.city;
                address.province = parsed.province;
                address.region   = parsed.region;
                return true;
            } catch (err) {
                console.warn('Reverse geocode error:', err);
                addressError.value = 'Could not detect address from that point. Please try a different spot or search instead.';
                return false;
            }
        };

        const placeMarker = async (lat, lng, zoom = 16, reverse = true) => {
            if (!map) return;
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', async (ev) => {
                    const p = ev.target.getLatLng();
                    await reverseGeocode(p.lat, p.lng);
                });
            }
            map.setView([lat, lng], zoom);
            if (reverse) await reverseGeocode(lat, lng);
        };

        const selectSuggestion = async (s) => {
            showSuggestions.value = false;
            suggestions.value = [];
            highlightedIndex.value = -1;
            searchQuery.value = s.display_name;
            const lat = parseFloat(s.lat);
            const lng = parseFloat(s.lon);
            if (!isNaN(lat) && !isNaN(lng)) {
                await placeMarker(lat, lng, 17, true);
            }
        };

        const onSearchInput = debounceSearch(async () => {
            const q = searchQuery.value.trim();
            if (q.length < 3) {
                suggestions.value = [];
                return;
            }
            const reqId = ++lastRequestId;
            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=jsonv2&countrycodes=ph&addressdetails=1&limit=6&accept-language=en&q=${encodeURIComponent(q)}`,
                    { headers: { 'Accept': 'application/json' } }
                );
                const data = await res.json();
                if (reqId !== lastRequestId) return;
                suggestions.value = Array.isArray(data) ? data : [];
                showSuggestions.value = suggestions.value.length > 0;
                highlightedIndex.value = -1;
            } catch (err) {
                console.warn('Autocomplete error:', err);
                suggestions.value = [];
            }
        });

        const moveHighlight = (dir) => {
            if (!suggestions.value.length) return;
            highlightedIndex.value = (highlightedIndex.value + dir + suggestions.value.length) % suggestions.value.length;
        };

        const selectHighlighted = () => {
            if (highlightedIndex.value >= 0 && suggestions.value[highlightedIndex.value]) {
                selectSuggestion(suggestions.value[highlightedIndex.value]);
            }
        };

        const closeSuggestionsSoon = () => {
            setTimeout(() => { showSuggestions.value = false; }, 180);
        };

        const reopenSuggestions = () => {
            if (suggestions.value.length) showSuggestions.value = true;
        };

        /* ---------- Age ---------- */
        const calculateAge = () => {
            const dobInput = document.getElementById('date_of_birth');
            const ageInput = document.getElementById('age');
            if (dobInput && dobInput.value) {
                const birthDate = new Date(dobInput.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) age--;
                if (ageInput) ageInput.value = age > 0 ? age : '';
            }
        };

        /* ---------- Validation ---------- */
        const validateSections = (sections) => {
            let firstError = null;
            sections.forEach(section => {
                section.querySelectorAll('input, select, textarea').forEach(input => {
                    if (input.type === 'hidden' || input.disabled) return;
                    const invalid = !input.checkValidity() || (input.required && !input.value.trim());
                    input.classList.toggle('is-invalid', invalid);
                    input.setAttribute('aria-invalid', String(invalid));
                    if (invalid && !firstError) firstError = input;
                });
            });
            if (firstError) {
                const allSections = Array.from(document.querySelectorAll('#enrollmentForm .form-section'));
                currentStep.value = allSections.indexOf(firstError.closest('.form-section'));
                nextTick(() => {
                    firstError.focus();
                    firstError.reportValidity();
                });
            }
            return !firstError;
        };

        const goToStep = (step) => {
            const form = document.getElementById('enrollmentForm');
            if (!form) return;
            const sections = Array.from(form.querySelectorAll('.form-section'));
            if (step > currentStep.value && !validateSections(sections.slice(0, step))) return;
            currentStep.value = step;
            nextTick(() => {
                const heading = sections[step].querySelector('h2');
                heading.setAttribute('tabindex', '-1');
                heading.focus({ preventScroll: true });
                heading.scrollIntoView({ block: 'start', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
            });
        };

        const validateAndProceed = goToStep;

        const validateForm = (event) => {
            const sections = Array.from(event.target.querySelectorAll('.form-section'));
            if (!validateSections(sections)) event.preventDefault();
        };

        watch(currentStep, step => {
            if (step === 0) nextTick(() => map?.invalidateSize());
        });

        onBeforeUnmount(() => {
            clearTimeout(searchTimer);
            map?.remove();
        });

        /* ---------- Init ---------- */
        onMounted(() => {
            calculateAge();
            if (!document.getElementById('map')) return;
            if (!window.L) {
                addressError.value = 'The map is unavailable. Please enter your address in the fields below.';
                return;
            }
            map = L.map('map', { zoomControl: true }).setView([14.6760, 121.0437], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            map.on('click', async (e) => {
                await placeMarker(e.latlng.lat, e.latlng.lng, map.getZoom(), true);
            });

            if (address.lat && address.lng) {
                const lat = parseFloat(address.lat);
                const lng = parseFloat(address.lng);
                if (!isNaN(lat) && !isNaN(lng)) {
                    placeMarker(lat, lng, 16, false);
                }
            }

            const dobInput = document.getElementById('date_of_birth');
            if (dobInput && dobInput.value) calculateAge();

            nextTick(() => setTimeout(() => map && map.invalidateSize(), 300));
        });

        return {
            currentStep, admissionType, requiredDocsByType, existingRequirements,
            selectedDocuments, uploadErrors, onDocumentChange,
            address, addressError, streetLine, fullAddress, onStreetInput,
            searchQuery, suggestions, showSuggestions, highlightedIndex,
            onSearchInput, selectSuggestion, moveHighlight, selectHighlighted,
            closeSuggestionsSoon, reopenSuggestions,
            calculateAge, goToStep, validateAndProceed, validateForm,
        };
    }
});

app.mount('#app');

})();
