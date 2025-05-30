@extends('layouts/layoutMaster')

@section('title', 'Ügyintézés')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/css/app.css'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/select2/i18n/hu.js',
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/@form-validation/trigger.js',
    'resources/assets/vendor/libs/cleavejs/cleave.js',
    'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
])
@endsection

@section('page-script')
    @vite([
        'resources/js/app.js',
        'resources/assets/js/forms-selects.js',
        'resources/assets/js/pages-aux-external-access.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Hozzáférési jogok</h4>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card hidden-scroll">
                <div class="card-datatable table-responsive horizontal-scroll pt-0">
                    @php
                        $workgroup915 = \App\Models\Workgroup::where('workgroup_number', 915)->first();
                        $isLeaderOfWg915 = true;
                    @endphp
                    <script>
                        window.isLeaderOfWg915 = @json($isLeaderOfWg915);
                    </script>

                    <table class="datatables-external-access table border-top">
                        <thead>
                            <tr style="background-color: rgba(105,108,255,.16)">
                                <th></th>
                                <th>Külső rendszer</th>
                                <th>Admin csoport</th>
                                <th>Aktív</th>
                                <th>Utolsó módosító</th>
                                <th>Utolsó módosítás</th>
                                <th>Létrehozó</th>
                                <th>Létrehozás</th>
                                <th>Műveletek</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="new_external_access" aria-labelledby="new_external_access_label">
        <div class="offcanvas-header border-bottom">
            <h5 id="new_external_access_label" class="offcanvas-title">Új hozzáférési jogosultság</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-1">
            <form class="add-new-record pt-0 row g-2 fv-plugins-bootstrap5 fv-plugins-framework" id="form-add-new-record" onsubmit="return false" novalidate="novalidate">
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="external_system">Külső rendszer</label>
                    <div class="input-group input-group-merge has-validation">
                        <input class="form-control numeral-mask" type="text" id="external_system" name="external_system" />
                    </div>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="admin_group_number">Admin csoport</label>
                    <select class="form-select select2" id="admin_group_number" name="admin_group_number">
                        <option value=""></option>
                        @foreach($workgroups as $workgroup)
                            <option value="{{ $workgroup->id }}">{{ $workgroup->workgroup_number }} - {{ $workgroup->name }}</option>
                        @endforeach
                    </select>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12">
                    <button type="button" class="btn btn-primary data-submit me-sm-3 me-1" data-workgroup-id="">Mentés</button>
                    <button type="reset" class="btn btn-outline-secondary cancel" data-bs-dismiss="offcanvas">Mégse</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div class="modal fade" id="deleteConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Biztosan szeretnéd törölni ezt a hozzáférési jogosultságot?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_delete" data-external-access-id="" class="btn btn-primary">Törlés</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore confirmation modal -->
    <div class="modal fade" id="restoreConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Biztosan szeretnéd visszaállítani ezt a hozzáférési jogosultságot?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_restore" data-external-access-id="" class="btn btn-primary">Visszaállítás</button>
                </div>
            </div>
        </div>
    </div>
        
@endsection