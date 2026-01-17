@once
	@push('scripts')
		<script>
			window.CountryCityLoader = window.CountryCityLoader || (function () {
				function setLoading(el, text) {
					if (!el) return;
					el.innerHTML = '<option>' + text + '</option>';
				}

				function populate(list, el, valueProp, labelProp) {
					if (!el) return;
					el.innerHTML = '';
					list.forEach(function (item) {
						var val = valueProp ? item[valueProp] : item;
						var lbl = labelProp ? item[labelProp] : item;
						var opt = document.createElement('option');
						opt.value = val;
						opt.textContent = lbl;
						el.appendChild(opt);
					});
				}

				async function loadCountries(countrySelect, defaults) {
					try {
						var res = await fetch('https://countriesnow.space/api/v0.1/countries/positions');
						var data = await res.json();
						var countries = (data && data.data) ? data.data : [];
						populate(countries, countrySelect, 'name', 'name');
						if (defaults && defaults.country && Array.from(countrySelect.options).some(function (o) { return o.value === defaults.country; })) {
							countrySelect.value = defaults.country;
						}
						return countrySelect.value || (countrySelect.options[0] || {}).value;
					} catch (e) {
						setLoading(countrySelect, 'Failed to load countries');
						return null;
					}
				}

				async function loadCities(citySelect, country, defaults) {
					setLoading(citySelect, 'Loading cities...');
					try {
						var res = await fetch('https://countriesnow.space/api/v0.1/countries/cities', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json' },
							body: JSON.stringify({ country: country })
						});
						var data = await res.json();
						var cities = (data && data.data) ? data.data : [];
						populate(cities, citySelect);
						if (defaults && defaults.city && Array.from(citySelect.options).some(function (o) { return o.value === defaults.city; })) {
							citySelect.value = defaults.city;
						}
					} catch (e) {
						setLoading(citySelect, 'Failed to load cities');
					}
				}

				function init(countryId, cityId, defaults) {
					var countrySelect = document.getElementById(countryId);
					var citySelect = document.getElementById(cityId);
					if (!countrySelect || !citySelect) return;

					setLoading(countrySelect, 'Loading countries...');
					setLoading(citySelect, 'Loading cities...');

					countrySelect.addEventListener('change', function () {
						loadCities(citySelect, this.value, defaults);
					});

					// kickoff
					(function () {
						loadCountries(countrySelect, defaults).then(function (selectedCountry) {
							if (selectedCountry) {
								loadCities(citySelect, selectedCountry, defaults);
							}
						});
					})();
				}

				return { init: init };
			})();
		</script>
	@endpush
@endonce
