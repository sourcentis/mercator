{{-- Paramètres attendus :
    $part    : partie CPE à filtrer
        (
        "a" : application
        "o" : Operating System
        "h" : Harware
        )
    $vendor  : valeur courante du vendor
    $product : valeur courante du product
    $version : valeur courante de la version
--}}

{{-- ===================== HTML ===================== --}}
<div class="card-header">
    Common Platform Enumeration (CPE)
</div>
<div class="card-body">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="vendor-selector">{{ trans('cruds.application.fields.vendor') }}</label>
                <select id="vendor-selector" class="form-control" name="vendor">
                    <option>{{ old('vendor', $vendor ?? '') }}</option>
                </select>
                <span class="help-block">{{ trans('cruds.application.fields.vendor_helper') }}</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="product-selector">{{ trans('cruds.application.fields.product') }}</label>
                <select id="product-selector" class="form-control" name="product">
                    <option>{{ old('product', $product ?? '') }}</option>
                </select>
                <span class="help-block">{{ trans('cruds.application.fields.product_helper') }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="version-selector">{{ trans('cruds.application.fields.version') }}</label>
                <select id="version-selector" class="form-control" name="version">
                    <option>{{ old('version', $version ?? '') }}</option>
                </select>
                <span class="help-block">{{ trans('cruds.application.fields.version_helper') }}</span>
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group">
                <br>
                <button type="button" class="btn btn-info" id="guess"
                        title="Guess vendor and product based on name">
                    Guess
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Modal Bootstrap ===================== --}}
<div class="modal fade" id="cpeGuesserModal" tabindex="-1"
     style="pointer-events: none;">
    <div class="modal-dialog modal-lg" style="pointer-events: all;">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cpeGuesserModalLabel">CPE Matching</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cpeGuesserBody">
                <div class="text-center text-muted py-3">
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    {{ trans('global.loading') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ trans('global.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Script ===================== --}}
<script @nonce>
document.addEventListener("DOMContentLoaded", function () {

    const cpePart = @json($part ?? 'a');

    // ------------------------------------------------
    // Vendor
    // ------------------------------------------------
    $('#vendor-selector').select2({
        placeholder: 'Start typing to search',
        tags: true,
        allowClear: true,
        ajax: {
            url: '/admin/cpe/search/vendors',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { part: cpePart, search: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (vendor) {
                        return { id: vendor.name, text: vendor.name };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });

    // ------------------------------------------------
    // Product
    // ------------------------------------------------
    $('#product-selector').select2({
        placeholder: 'Start typing to search',
        tags: true,
        allowClear: true,
        ajax: {
            url: '/admin/cpe/search/products',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { part: cpePart, vendor: $("#vendor-selector").val(), search: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (product) {
                        return { id: product.name, text: product.name };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    // ------------------------------------------------
    // Version
    // ------------------------------------------------
    $('#version-selector').select2({
        placeholder: 'Start typing to search',
        tags: true,
        allowClear: true,
        ajax: {
            url: '/admin/cpe/search/versions',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    part: cpePart,
                    vendor: $("#vendor-selector").val(),
                    product: $("#product-selector").val(),
                    search: params.term || ''
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (version) {
                        return { id: version.name, text: version.name };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    // ------------------------------------------------
    // CPE Guesser
    // ------------------------------------------------

    function generateCPEList(data) {
        if (!data || data.length === 0) {
            return '<p class="text-muted text-center">{{ trans('global.no_result') }}</p>';
        }
        let ret = '<div style="max-height: 350px; overflow-y: auto;">';
        ret += '<table class="table table-sm table-hover">';
        ret += '<thead><tr><th>Vendor</th><th>Product</th><th></th></tr></thead><tbody>';
        data.forEach(function (element) {
            ret += '<tr style="cursor:pointer" data-vendor="' + element.vendor_name + '" data-product="' + element.product_name + '">';
            ret += '<td>' + element.vendor_name + '</td>';
            ret += '<td>' + element.product_name + '</td>';
            ret += '</tr>';
        });
        ret += '</tbody></table></div>';
        return ret;
    }

$('#guess').on('click', function () {
    let name = $("#name").val();
    if (!name) return;

    const cpeModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('cpeGuesserModal'));

    // Positionner le modal près du bouton
    const btn    = document.getElementById('guess');
    const rect   = btn.getBoundingClientRect();
    const dialog = document.querySelector('#cpeGuesserModal .modal-dialog');

    // dialog.style.marginTop = (rect.bottom + window.scrollY + 8 - 200) + 'px';
    dialog.style.marginTop = (rect.bottom + 8 - 200) + 'px';

    $('#cpeGuesserBody').find('tbody tr').on('click', function () {
        let vendor = $(this).data('vendor');
        let product = $(this).data('product');

        $("#vendor-selector").append('<option>' + vendor + '</option>');
        $("#vendor-selector").val(vendor).trigger('change');
        $("#product-selector").append('<option>' + product + '</option>');
        $("#product-selector").val(product).trigger('change');
        $("#version-selector").val(null).trigger('change');
        cpeModal.hide();
    });
    cpeModal.show();

$.get("/admin/cpe/search/guess?search=" + encodeURIComponent(name)+ (cpePart ? "&part=" + cpePart : ""))
    .then(function (result) {
        $('#cpeGuesserBody').html(generateCPEList(result));

        $('#cpeGuesserBody').find('tbody tr').on('click', function () {
            let vendor = $(this).data('vendor');
            let product = $(this).data('product');

            $("#vendor-selector").append('<option>' + vendor + '</option>');
            $("#vendor-selector").val(vendor).trigger('change');
            $("#product-selector").append('<option>' + product + '</option>');
            $("#product-selector").val(product).trigger('change');
            $("#version-selector").val(null).trigger('change');
            cpeModal.hide();
        });
    })
    .fail(function () {
        $('#cpeGuesserBody').html('<p class="text-danger">An error occurred.</p>');
    });
});

});
</script>
