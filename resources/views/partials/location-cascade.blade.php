{{--
    Sélection hiérarchique Site ↔ Bâtiment ↔ Baie (bidirectionnelle).
    Prérequis : selects #site_id, #building_id et éventuellement #bay_id (Select2).
    Paramètres :
      - $buildingSiteMap : [building_id => site_id]
      - $bayBuildingMap  : [bay_id => building_id] (optionnel)
    Règles :
      - site choisi        → bâtiments et baies filtrés sur ce site
      - bâtiment choisi    → site auto-sélectionné, baies filtrées sur ce bâtiment
      - baie choisie       → bâtiment et site auto-sélectionnés
--}}
<script @nonce>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const buildingSiteMap = @json($buildingSiteMap);
        const bayBuildingMap = @json($bayBuildingMap ?? null);

        const $site = $('#site_id');
        const $building = $('#building_id');
        const $bay = $('#bay_id');

        if (!$site.length || !$building.length) {
            return;
        }
        const hasBay = bayBuildingMap !== null && $bay.length > 0;

        // Site d'une baie, résolu transitivement : baie → bâtiment → site
        function siteOfBay(bayId) {
            const b = bayBuildingMap[bayId];
            return b === undefined ? undefined : buildingSiteMap[b];
        }

        // Copie des options d'origine de chaque select (option vide comprise)
        function snapshot($sel) {
            return $sel.find('option').map(function () {
                return { value: this.value, text: this.text };
            }).get();
        }
        const allBuildings = snapshot($building);
        const allBays = hasBay ? snapshot($bay) : [];

        let updating = false;

        // Reconstruit les options d'un select selon un prédicat, restaure la valeur
        function fill($sel, options, keep, predicate) {
            $sel.empty();
            options.forEach(function (opt) {
                if (opt.value === '' || predicate(opt.value)) {
                    $sel.append(new Option(opt.text, opt.value));
                }
            });
            $sel.val(keep !== '' && $sel.find('option[value="' + keep + '"]').length
                ? keep : '');
            $sel.trigger('change.select2');
        }

        /**
         * Applique l'état (site, building, bay) aux trois selects.
         * L'état est supposé cohérent (résolu par les handlers).
         */
        function render(state) {
            updating = true;
            try {
                $site.val(state.site).trigger('change.select2');

                fill($building, allBuildings, state.building, function (id) {
                    return state.site === ''
                        || String(buildingSiteMap[id]) === state.site;
                });

                if (hasBay) {
                    fill($bay, allBays, state.bay, function (id) {
                        if (state.building !== '') {
                            return String(bayBuildingMap[id]) === state.building;
                        }
                        if (state.site !== '') {
                            return String(siteOfBay(id)) === state.site;
                        }
                        return true; // rien de sélectionné : liste complète
                    });
                }
            } finally {
                updating = false;
            }
        }

        function val($sel) {
            return String($sel.val() ?? '');
        }

        // --- Handlers : chacun résout un état cohérent puis rend ---

        $site.on('change', function () {
            if (updating) return;
            const site = val($site);
            let building = val($building);
            let bay = hasBay ? val($bay) : '';
            // Invalide les descendants incompatibles avec le nouveau site
            if (building !== '' && String(buildingSiteMap[building]) !== site) {
                building = '';
            }
            if (bay !== '' && (building === ''
                    ? String(siteOfBay(bay)) !== site
                    : String(bayBuildingMap[bay]) !== building)) {
                bay = '';
            }
            render({ site: site, building: building, bay: bay });
        });

        $building.on('change', function () {
            if (updating) return;
            const building = val($building);
            let site = val($site);
            let bay = hasBay ? val($bay) : '';
            if (building !== '') {
                // Règle 2 : auto-sélection du site du bâtiment
                site = String(buildingSiteMap[building]);
                if (bay !== '' && String(bayBuildingMap[bay]) !== building) {
                    bay = '';
                }
            } else if (bay !== '') {
                bay = '';
            }
            render({ site: site, building: building, bay: bay });
        });

        if (hasBay) {
            $bay.on('change', function () {
                if (updating) return;
                const bay = val($bay);
                let site = val($site);
                let building = val($building);
                if (bay !== '') {
                    // Règle 3 : auto-sélection du bâtiment puis du site
                    building = String(bayBuildingMap[bay]);
                    site = String(buildingSiteMap[building]);
                }
                render({ site: site, building: building, bay: bay });
            });
        }

        // État initial : édition ou old() après erreur de validation.
        // On part des valeurs présentes et on les complète vers le haut
        // (une baie renseignée implique bâtiment et site).
        (function initialRender() {
            let site = val($site);
            let building = val($building);
            let bay = hasBay ? val($bay) : '';
            if (bay !== '' && building === '') {
                building = String(bayBuildingMap[bay] ?? '');
            }
            if (building !== '' && site === '') {
                site = String(buildingSiteMap[building] ?? '');
            }
            render({ site: site, building: building, bay: bay });
        })();
    });
</script>
