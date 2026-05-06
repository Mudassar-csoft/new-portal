@once
	@push('scripts')
		<script>
			window.CountryCityLoader = window.CountryCityLoader || (function () {
				var countriesUrl = @json(asset('data/countries-cities.json'));
				var countriesCache = null;
				var countriesPromise = null;
				var defaultCountry = 'Pakistan';
				var defaultCity = 'Faisalabad';

				function normalizeText(value) {
					return String(value || '').trim();
				}

				function normalizeKey(value) {
					return normalizeText(value).toLowerCase();
				}

				function matchesValue(left, right) {
					return normalizeKey(left) === normalizeKey(right);
				}

				function uniqueSorted(list) {
					return Array.from(new Set(
						(list || [])
							.map(function (item) {
								return normalizeText(item);
							})
							.filter(Boolean)
					)).sort(function (left, right) {
						return left.localeCompare(right);
					});
				}

				function setMessage(el, text, disabled) {
					if (!el) return;

					el.innerHTML = '';

					var option = document.createElement('option');
					option.value = '';
					option.textContent = text;
					el.appendChild(option);
					el.disabled = !!disabled;
					refreshEnhancedSelect(el);
				}

				function addOption(el, value, label, meta) {
					var option = document.createElement('option');
					option.value = value;
					option.textContent = label;

					Object.keys(meta || {}).forEach(function (key) {
						if (meta[key] !== undefined && meta[key] !== null) {
							option.dataset[key] = meta[key];
						}
					});

					el.appendChild(option);
				}

				function refreshEnhancedSelect(el) {
					if (!el || !window.jQuery) {
						return;
					}

					var $el = window.jQuery(el);

					if (window.jQuery.fn.selectpicker && $el.data('selectpicker')) {
						$el.selectpicker('refresh');
					}

					if (window.jQuery.fn.select2 && $el.hasClass('select2-hidden-accessible')) {
						$el.trigger('change.select2');
					}
				}

				function bindCountryChange(countrySelect, handler) {
					var scheduled = null;
					var lastValue = countrySelect.value;

					function scheduleHandler() {
						if (scheduled) {
							clearTimeout(scheduled);
						}

						scheduled = setTimeout(function () {
							scheduled = null;

							if (countrySelect.value === lastValue) {
								return;
							}

							lastValue = countrySelect.value;
							handler();
						}, 0);
					}

					countrySelect.addEventListener('change', scheduleHandler);

					if (window.jQuery) {
						window.jQuery(countrySelect)
							.off('change.countryCityLoader')
							.on('change.countryCityLoader', scheduleHandler);
					}

					return function rememberCurrentValue() {
						lastValue = countrySelect.value;
					};
				}

				function normalizeCities(cities) {
					return uniqueSorted((cities || []).map(function (item) {
						if (typeof item === 'string') {
							return item;
						}

						return item && (
							item.name
							|| item.city
							|| item.city_name
							|| item.label
						);
					}));
				}

				function normalizeCountries(payload) {
					var list = Array.isArray(payload)
						? payload
						: (payload && Array.isArray(payload.data) ? payload.data : []);

					return list.map(function (item) {
						if (typeof item === 'string') {
							return { name: normalizeText(item), code: '', cities: [] };
						}

						var name = normalizeText(item && (item.name || item.country || item.country_name));
						var code = normalizeText(item && (item.iso2 || item.cca2 || item.code || item.country_code)).toUpperCase();

						if (!name) {
							return null;
						}

						return {
							name: name,
							code: code,
							cities: normalizeCities(item.cities || item.states || item.locations || [])
						};
					}).filter(Boolean).sort(function (left, right) {
						return left.name.localeCompare(right.name);
					});
				}

				function getCountries() {
					if (countriesCache) {
						return Promise.resolve(countriesCache);
					}

					if (countriesPromise) {
						return countriesPromise;
					}

					countriesPromise = fetch(countriesUrl, { cache: 'force-cache' })
						.then(function (response) {
							if (!response.ok) {
								throw new Error('Countries file failed: ' + response.status);
							}

							return response.json();
						})
						.then(function (payload) {
							countriesCache = normalizeCountries(payload);
							if (!countriesCache.length) {
								throw new Error('Countries file is empty');
							}

							return countriesCache;
						})
						.catch(function (error) {
							countriesPromise = null;
							throw error;
						});

					return countriesPromise;
				}

				function findCountry(countries, requestedCountry) {
					var requested = normalizeText(requestedCountry);
					if (!requested) {
						return null;
					}

					return countries.find(function (country) {
						return matchesValue(country.name, requested)
							|| matchesValue(country.code, requested);
					}) || null;
				}

				function resolveInitialCountry(countries, defaults) {
					return findCountry(countries, defaults && defaults.country)
						|| findCountry(countries, defaultCountry)
						|| countries[0]
						|| null;
				}

				function resolveCity(cities, requestedCity, countryName) {
					var requested = normalizeText(requestedCity);
					var matchedCity = requested
						? cities.find(function (city) {
							return matchesValue(city, requested);
						})
						: null;

					if (matchedCity) {
						return matchedCity;
					}

					if (matchesValue(countryName, defaultCountry)) {
						matchedCity = cities.find(function (city) {
							return matchesValue(city, defaultCity);
						});

						if (matchedCity) {
							return matchedCity;
						}
					}

					return cities[0] || '';
				}

				function populateCountries(countrySelect, countries) {
					countrySelect.innerHTML = '';
					countrySelect.disabled = false;

					countries.forEach(function (country) {
						addOption(countrySelect, country.name, country.name, {
							countryCode: country.code
						});
					});

					refreshEnhancedSelect(countrySelect);
				}

				function populateCities(citySelect, cities) {
					citySelect.innerHTML = '';
					citySelect.disabled = false;

					cities.forEach(function (city) {
						addOption(citySelect, city, city);
					});

					refreshEnhancedSelect(citySelect);
				}

				function loadCountries(countrySelect, defaults) {
					setMessage(countrySelect, 'Loading countries...', true);

					return getCountries()
						.then(function (countries) {
							populateCountries(countrySelect, countries);

							var selectedCountry = resolveInitialCountry(countries, defaults);
							if (selectedCountry) {
								countrySelect.value = selectedCountry.name;
								refreshEnhancedSelect(countrySelect);
							}

							return selectedCountry;
						})
						.catch(function () {
							setMessage(countrySelect, 'Failed to load countries', false);
							return null;
						});
				}

				function loadCities(citySelect, countryName, countryCode, defaults) {
					setMessage(citySelect, 'Loading cities...', true);

					return getCountries()
						.then(function (countries) {
							var selectedCountry = findCountry(countries, countryName)
								|| findCountry(countries, countryCode);

							if (!selectedCountry) {
								setMessage(citySelect, 'No cities found', false);
								return;
							}

							if (!selectedCountry.cities.length) {
								setMessage(citySelect, 'No cities found', false);
								return;
							}

							populateCities(citySelect, selectedCountry.cities);
							citySelect.value = resolveCity(
								selectedCountry.cities,
								defaults && defaults.city,
								selectedCountry.name
							);
							refreshEnhancedSelect(citySelect);
						})
						.catch(function () {
							setMessage(citySelect, 'Failed to load cities', false);
						});
				}

				function init(countryId, cityId, defaults) {
					var countrySelect = document.getElementById(countryId);
					var citySelect = document.getElementById(cityId);

					if (!countrySelect || !citySelect) {
						return;
					}

					var rememberCurrentCountry = bindCountryChange(countrySelect, function () {
						var selectedOption = countrySelect.options[countrySelect.selectedIndex];
						loadCities(
							citySelect,
							countrySelect.value,
							selectedOption ? selectedOption.dataset.countryCode : '',
							{ city: '' }
						);
					});

					loadCountries(countrySelect, defaults).then(function (selectedCountry) {
						if (!selectedCountry) {
							setMessage(citySelect, 'No cities found', false);
							return;
						}

						rememberCurrentCountry();
						loadCities(citySelect, selectedCountry.name, selectedCountry.code, defaults);
					});
				}

				return { init: init };
			})();
		</script>
	@endpush
@endonce
