@extends('layouts/layoutMaster')

@section('title', 'Költséghelyek')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss',
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss',
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
    'node_modules/select2/dist/js/i18n/hu.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
    'resources/assets/vendor/libs/cleavejs/cleave.js',
    'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
])
@endsection

@section('page-script')
    @vite([
        'resources/assets/js/forms-selects.js',
        'resources/assets/js/pages-aux-costcenters.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Csoportok</h4>

    <div class="alert alert-danger alert-dismissible d-none" role="alert" id="errorAlert">
        <span id="errorAlertMessage"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
        </button>
      </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <table class="datatables-costcenters table border-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Költséghely</th>
                                <th>Megnevezés</th>
                                <th>Típus</th>
                                <th>Témavezető</th>
                                <th>Projektkoordinátor</th>
                                <th>Lejárat</th>
                                <th>Minimális rendelési limit</th>
                                <th>Érvényes felvételi kérelem</th>
                                <th>Aktív</th>
                                <th>Utolsó módosító</th>
                                <th>Utolsó módosítás</th>
                                <th>Létrehozó</th>
                                <th>Létrehozás</th>
                                <th>Művelet</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="new_costcenter" aria-labelledby="new_costcenter_label">
        <div class="offcanvas-header border-bottom">
            <h5 id="new_costcenter_label" class="offcanvas-title">Új költséghely</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-1">
            <form class="add-new-record pt-0 row g-2 fv-plugins-bootstrap5 fv-plugins-framework" id="form-add-new-record" onsubmit="return false" novalidate="novalidate">
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="cost_center_code">Költséghely</label>
                    <div class="input-group input-group-merge has-validation">
                        <input class="form-control" type="text" id="cost_center_code" />
                    </div>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="name">Megnevezés</label>
                    <div class="input-group input-group-merge has-validation">
                        <input type="text" id="name" class="form-control" />
                    </div>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="type_id">Típus</label>
                    <select class="form-select select2" id="type_id">
                        @foreach($costcenterTypes as $costcenterType)
                            <option value="{{ $costcenterType->id }}">{{ $costcenterType->name }}</option>
                        @endforeach
                    </select>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="lead_user_id">Témavezető</label>
                    <select class="form-select select2" id="lead_user_id">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="project_coordinator_user_id">Projektkoordinátor</label>
                    <select class="form-select select2" id="project_coordinator_user_id">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="due_date">Lejárat</label>
                    <div class="input-group input-group-merge has-validation">
                        <input type="text" id="due_date" class="form-control" placeholder="ÉÉÉÉ.HH.NN" />
                    </div>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-label" for="minimal_order_limit">Minimális rendelési limit</label>
                    <div class="input-group input-group-merge has-validation">
                        <input type="text" id="minimal_order_limit" class="form-control numeral-mask" />
                    </div>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12 fv-plugins-icon-container">
                    <label class="form-check-label" for="valid_employee_recruitment">Érvényes felvételi kérelem</label>
                    <div class="has-validation">
                        <input type="checkbox" id="valid_employee_recruitment" class="form-check-input" />
                    </div>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="col-sm-12">
                    <button type="button" class="btn btn-primary data-submit me-sm-3 me-1" data-costcenter-id="">Mentés</button>
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
                    <p>Biztosan szeretnéd törölni ezt a költséghelyet?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_delete" data-costcenter-id="" class="btn btn-primary">Törlés</button>
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
                    <p>Biztosan szeretnéd visszaállítani ezt a költséghelyet?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_restore" data-costcenter-id="" class="btn btn-primary">Visszaállítás</button>
                </div>
            </div>
        </div>
        
@endsection