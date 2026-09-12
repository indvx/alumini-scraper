/**
 * 
 * Dynamically fetches countries, states, and cities via API endpoints.
 *
 * @param {Object} options
 */
export function initLocationCascade(options = {}) {
    const {
        countryInputId = 'countryInput',
        countryIdInputId = 'countryId',
        countrySuggestionsId = 'countrySuggestions',

        stateContainerId = 'stateContainer',
        stateInputId = 'stateInput',
        stateIdInputId = 'stateId',
        stateSuggestionsId = 'stateSuggestions',
        stateRequired = true,

        cityContainerId = 'cityContainer',
        cityInputId = 'cityInput',
        cityIdInputId = 'cityId',
        citySuggestionsId = 'citySuggestions',

        hoverClass = 'hover:bg-rose-50 dark:hover:bg-slate-700/80',
        containerId = null,
    } = options;

    const countryInput = document.getElementById(countryInputId);
    const countrySuggestions = document.getElementById(countrySuggestionsId);
    const countryIdInput = countryIdInputId ? document.getElementById(countryIdInputId) : null;

    const stateContainer = stateContainerId ? document.getElementById(stateContainerId) : null;
    const stateInput = stateInputId ? document.getElementById(stateInputId) : null;
    const stateSuggestions = stateSuggestionsId ? document.getElementById(stateSuggestionsId) : null;
    const stateIdInput = stateIdInputId ? document.getElementById(stateIdInputId) : null;

    const cityContainer = cityContainerId ? document.getElementById(cityContainerId) : null;
    const cityInput = cityInputId ? document.getElementById(cityInputId) : null;
    const citySuggestions = citySuggestionsId ? document.getElementById(citySuggestionsId) : null;
    const cityIdInput = cityIdInputId ? document.getElementById(cityIdInputId) : null;

    if (!countryInput) return;

    let currentCountryId = countryIdInput?.value || null;
    let currentStateId = stateIdInput?.value || null;

    function escapeHtml(str) {
        return str ? str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;') : '';
    }

    function closeAllSuggestions() {
        if (countrySuggestions) countrySuggestions.classList.add('hidden');
        if (stateSuggestions) stateSuggestions.classList.add('hidden');
        if (citySuggestions) citySuggestions.classList.add('hidden');
    }

    // --- Country ---
    countryInput.addEventListener('focus', () => fetchCountries(countryInput.value));
    countryInput.addEventListener('input', () => {
        if (countryIdInput) countryIdInput.value = '';
        resetStateSelection();
        fetchCountries(countryInput.value);
    });

    function fetchCountries(query) {
        if (!countrySuggestions) return;
        fetch(`/locations/countries?query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    countrySuggestions.innerHTML = data.data.map(item => `
                        <div class="px-4 py-2.5 ${hoverClass} cursor-pointer text-slate-800 dark:text-slate-200 transition-colors"
                            data-loc-id="${item.id}" data-loc-name="${escapeHtml(item.name)}">
                            ${escapeHtml(item.name)}
                        </div>
                    `).join('');

                    countrySuggestions.querySelectorAll('[data-loc-id]').forEach(el => {
                        el.addEventListener('click', (e) => {
                            e.stopPropagation();
                            selectCountry(el.getAttribute('data-loc-id'), el.getAttribute('data-loc-name'));
                        });
                    });
                    countrySuggestions.classList.remove('hidden');
                } else {
                    countrySuggestions.classList.add('hidden');
                }
            })
            .catch(() => countrySuggestions.classList.add('hidden'));
    }

    function selectCountry(id, name) {
        countryInput.value = name;
        if (countryIdInput) countryIdInput.value = id;
        currentCountryId = id;
        if (countrySuggestions) countrySuggestions.classList.add('hidden');

        resetStateSelection();
        if (stateContainer) {
            stateContainer.classList.remove('hidden');
        }
        if (stateInput && stateRequired) {
            stateInput.setAttribute('required', 'required');
        }
        if (stateInput) {
            fetchStates('');
        }
    }

    function resetStateSelection() {
        currentStateId = null;
        if (stateInput) stateInput.value = '';
        if (stateIdInput) stateIdInput.value = '';
        resetCitySelection();
        if (stateContainer && !countryInput.value.trim()) {
            stateContainer.classList.add('hidden');
            if (stateInput && stateRequired) {
                stateInput.removeAttribute('required');
            }
        }
    }

    // --- State ---
    if (stateInput) {
        stateInput.addEventListener('focus', () => fetchStates(stateInput.value));
        stateInput.addEventListener('input', () => {
            if (stateIdInput) stateIdInput.value = '';
            resetCitySelection();
            fetchStates(stateInput.value);
        });
    }

    function fetchStates(query) {
        if (!stateSuggestions) return;
        const countryParam = currentCountryId ? `country_id=${currentCountryId}` :
            `country=${encodeURIComponent(countryInput.value)}`;
        fetch(`/locations/states?${countryParam}&query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    stateSuggestions.innerHTML = data.data.map(item => `
                        <div class="px-4 py-2.5 ${hoverClass} cursor-pointer text-slate-800 dark:text-slate-200 transition-colors flex justify-between items-center"
                            data-loc-id="${item.id}" data-loc-name="${escapeHtml(item.name)}">
                            <span>${escapeHtml(item.name)}</span>
                            ${item.state_code ? `<span class="text-xs font-mono text-slate-400">${escapeHtml(item.state_code)}</span>` : ''}
                        </div>
                    `).join('');

                    stateSuggestions.querySelectorAll('[data-loc-id]').forEach(el => {
                        el.addEventListener('click', (e) => {
                            e.stopPropagation();
                            selectState(el.getAttribute('data-loc-id'), el.getAttribute('data-loc-name'));
                        });
                    });
                    stateSuggestions.classList.remove('hidden');
                } else {
                    stateSuggestions.classList.add('hidden');
                }
            })
            .catch(() => stateSuggestions.classList.add('hidden'));
    }

    function selectState(id, name) {
        if (stateInput) stateInput.value = name;
        if (stateIdInput) stateIdInput.value = id;
        currentStateId = id;
        if (stateSuggestions) stateSuggestions.classList.add('hidden');

        resetCitySelection();
        if (cityContainer) {
            cityContainer.classList.remove('hidden');
        }
        if (cityInput) {
            fetchCities('');
        }
    }

    function resetCitySelection() {
        if (cityInput) cityInput.value = '';
        if (cityIdInput) cityIdInput.value = '';
        if (cityContainer && stateInput && !stateInput.value.trim()) {
            cityContainer.classList.add('hidden');
        }
    }

    // --- City ---
    if (cityInput) {
        cityInput.addEventListener('focus', () => fetchCities(cityInput.value));
        cityInput.addEventListener('input', () => {
            if (cityIdInput) cityIdInput.value = '';
            fetchCities(cityInput.value);
        });
    }

    function fetchCities(query) {
        if (!citySuggestions) return;
        const stateParam = currentStateId ? `state_id=${currentStateId}` :
            `state=${encodeURIComponent(stateInput ? stateInput.value : '')}`;
        fetch(`/locations/cities?${stateParam}&query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    citySuggestions.innerHTML = data.data.map(item => `
                        <div class="px-4 py-2.5 ${hoverClass} cursor-pointer text-slate-800 dark:text-slate-200 transition-colors"
                            data-loc-id="${item.id}" data-loc-name="${escapeHtml(item.name)}">
                            ${escapeHtml(item.name)}
                        </div>
                    `).join('');

                    citySuggestions.querySelectorAll('[data-loc-id]').forEach(el => {
                        el.addEventListener('click', (e) => {
                            e.stopPropagation();
                            selectCity(el.getAttribute('data-loc-id'), el.getAttribute('data-loc-name'));
                        });
                    });
                    citySuggestions.classList.remove('hidden');
                } else {
                    citySuggestions.classList.add('hidden');
                }
            })
            .catch(() => citySuggestions.classList.add('hidden'));
    }

    function selectCity(id, name) {
        if (cityInput) cityInput.value = name;
        if (cityIdInput) cityIdInput.value = id;
        if (citySuggestions) citySuggestions.classList.add('hidden');
    }

    // Close suggestions on outside click
    document.addEventListener('click', (e) => {
        if (containerId) {
            const containerEl = document.getElementById(containerId);
            if (containerEl && !containerEl.contains(e.target)) {
                closeAllSuggestions();
                return;
            }
        }

        if (countryInput && countrySuggestions && !countryInput.contains(e.target) && !countrySuggestions.contains(e.target)) {
            countrySuggestions.classList.add('hidden');
        }
        if (stateInput && stateSuggestions && !stateInput.contains(e.target) && !stateSuggestions.contains(e.target)) {
            stateSuggestions.classList.add('hidden');
        }
        if (cityInput && citySuggestions && !cityInput.contains(e.target) && !citySuggestions.contains(e.target)) {
            citySuggestions.classList.add('hidden');
        }
    });

    return {
        closeAllSuggestions,
        fetchCountries,
        fetchStates,
        fetchCities,
    };
}

if (typeof window !== 'undefined') {
    window.initLocationCascade = initLocationCascade;
}
