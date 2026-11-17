{{--
    Filtre le bâtiment parent (#building_id) et les bâtiments enfant
    (select[name="buildings[]"]) pour ne proposer que les bâtiments
    du site sélectionné (#site_id).
    Paramètre :
      - $buildingSiteMap : [building_id => site_id]
--}}
<script @nonce>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const buildingSiteMap = @json($buildingSiteMap);

        const $site = $('#site_id');
        const $parent = $('#building_id');
        const $children = $('select[name="buildings[]"]');

        if (!$site.length || (!$parent.length && !$children.length)) {
            return;
        }

        // Copie des options d'origine (option vide comprise pour le parent)
        function snapshot($sel) {
            return $sel.find('option').map(function () {
                return { value: this.value, text: this.text };
            }).get();
        }
        const allParentOptions = $parent.length ? snapshot($parent) : [];
        const allChildrenOptions = $children.length ? snapshot($children) : [];

        // Reconstruit les options d'un select en ne gardant que celles du site,
        // en conservant la ou les valeurs actuellement sélectionnées si possible
        function fill($sel, options, site) {
            const current = $sel.val();
            const keep = Array.isArray(current)
                ? current.map(String)
                : (current !== null && current !== undefined && current !== '' ? [String(current)] : []);

            $sel.empty();
            options.forEach(function (opt) {
                const matches = opt.value === ''
                    || site === ''
                    || String(buildingSiteMap[opt.value]) === site;
                if (matches) {
                    $sel.append(new Option(opt.text, opt.value, false, keep.includes(opt.value)));
                }
            });
            $sel.trigger('change.select2');
        }

        function applyFilter() {
            const site = String($site.val() ?? '');
            if ($parent.length) {
                fill($parent, allParentOptions, site);
            }
            if ($children.length) {
                fill($children, allChildrenOptions, site);
            }
        }

        $site.on('change', function () {
            applyFilter();
        });

        // Sélectionne automatiquement le site du bâtiment parent choisi
        // si aucun site n'est déjà sélectionné.
        if ($parent.length) {
            $parent.on('change', function () {
                if ($site.val()) {
                    return;
                }
                const parentId = $parent.val();
                const siteId = parentId ? buildingSiteMap[parentId] : undefined;
                if (siteId !== undefined && siteId !== null) {
                    $site.val(String(siteId)).trigger('change');
                }
            });
        }

        applyFilter();
    });
</script>
