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
])
@endsection

@section('page-script')
    @vite([
        'resources/js/app.js',
        'resources/assets/js/forms-selects.js',
        'resources/assets/js/form-basic-inputs.js',
        'resources/assets/js/pages-users.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Felhasználók</h4>

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

                    <table class="datatables-users table border-top" data-api-endpoint="{{ $apiEndpoint }}">
                        <thead>
                            <tr style="background-color: rgba(105,108,255,.16)">
                                <th></th>
                                <th>Név</th>
                                <th>Email</th>
                                <th>Csoport</th>
                                <th>Szerepkörök</th>
                                <th>Külsős jogok</th>
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

    <div class="offcanvas offcanvas-end" tabindex="-1" id="new_user" aria-labelledby="new_user_label">
        <div class="offcanvas-header border-bottom">
            <h5 id="new_user_label" class="offcanvas-title">Új felhasználó</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-1">
            <form class="add-new-record pt-0 row g-2 fv-plugins-bootstrap5 fv-plugins-framework" id="form-add-new-record" onsubmit="return false" novalidate="novalidate">
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="name">Név</label>
                    <div class="input-group input-group-merge has-validation">
                        <input type="text" id="name" class="form-control" name="name" />
                    </div>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-group input-group-merge has-validation">
                        <input type="text" id="email" class="form-control" name="email" />
                    </div>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="workgroup_id">Csoport</label>
                    <select class="form-select select2" id="workgroup_id">
                        @foreach($workgroups as $workgroup)
                            <option value="{{ $workgroup->id }}">{{ $workgroup->workgroup_number }} - {{ $workgroup->name }}</option>
                        @endforeach
                    </select>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="roles">Szerepkörök</label>
                    <select class="form-select select2" id="roles" multiple>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name_readable }}</option>
                        @endforeach
                    </select>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="external_privileges">Külsős jogok</label>
                    <select class="form-select select2" id="external_privileges" name="external_privileges" multiple>
                        @foreach($externalPrivileges as $privilege)
                            <option value="{{ $privilege->id }}">{{ $privilege->name }}</option>
                        @endforeach
                    </select>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>

                <div class="col-sm-12">
                    <button type="button" class="btn btn-primary data-submit me-sm-3 me-1" data-user-id="">Mentés</button>
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
                    <p>Biztosan szeretnéd törölni ezt a felhasználót?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_delete" data-user-id="" class="btn btn-primary">Törlés</button>
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
                    <p>Biztosan szeretnéd visszaállítani ezt a felhasználót?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_restore" data-user-id="" class="btn btn-primary">Visszaállítás</button>
                </div>
            </div>
        </div>
    </div>

@endsection