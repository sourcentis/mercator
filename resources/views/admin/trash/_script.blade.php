@php
    // Liste d'un modèle en mode corbeille (variable partagée par TrashModeMiddleware).
    $trashList = null;
    if (isset($trashListModel)) {
        $trashRoutes = \App\Support\TrashMode::indexRoutes();
        $trashList = [
            'base' => isset($trashRoutes[$trashListModel]) ? route($trashRoutes[$trashListModel]) : null,
            'restore' => route('admin.trash.restore', [\App\Support\TrashMode::key($trashListModel), 0]),
            'canRestore' => Gate::allows(\App\Support\TrashMode::permissionPrefix($trashListModel).'_delete'),
            'label' => trans('cruds.trash.restore'),
            'confirm' => trans('cruds.trash.restore_confirm'),
        ];
    }
@endphp
<script>
(function () {
    'use strict';
    var content = document.getElementById('content-home');
    if (!content) return;

    // Pas de création ni de modification en mode corbeille.
    content.querySelectorAll('#btn-new').forEach(function (el) { el.remove(); });

    var list = @json($trashList);
    if (list) {
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        content.querySelectorAll('table tbody tr[data-entry-id]').forEach(function (row) {
            var id = row.getAttribute('data-entry-id');
            var cell = row.lastElementChild;
            if (!cell || !id) return;

            // On ne garde que le lien vers la fiche (s'il a été rendu, selon les droits).
            // (comparaison sur le chemin : APP_URL peut différer de l'hôte utilisé par le navigateur)
            var showPath = list.base ? new URL(list.base).pathname.replace(/\/$/, '') + '/' + id : null;
            Array.from(cell.children).forEach(function (child) {
                var path = child.tagName === 'A' && child.href ? new URL(child.href).pathname : null;
                if (!(path && showPath && path === showPath)) child.remove();
            });

            if (list.canRestore) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = new URL(list.restore).pathname.replace(/\/0\/restore$/, '/' + id + '/restore');
                form.style.display = 'inline-block';
                form.onsubmit = function () { return confirm(list.confirm); };
                form.innerHTML = '<input type="hidden" name="_token">'
                    + '<button type="submit" class="btn btn-xs btn-success"></button>';
                form.querySelector('input').value = token;
                form.querySelector('button').textContent = list.label;
                cell.appendChild(document.createTextNode(' '));
                cell.appendChild(form);
            }
        });
    }

    // Fiche d'un objet supprimé : pas de modification ni de suppression.
    if (document.getElementById('trash-object')) {
        content.querySelectorAll('a[href$="/edit"]').forEach(function (el) { el.remove(); });
        content.querySelectorAll('form').forEach(function (form) {
            var method = form.querySelector('input[name="_method"]');
            if (method && method.value.toUpperCase() === 'DELETE') form.remove();
        });
    }
})();
</script>
