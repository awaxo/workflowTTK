@extends('layouts/layoutMaster')

@section('title', 'Profil')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss',
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
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/select2/i18n/hu.js',
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
    'resources/assets/vendor/libs/cleavejs/cleave.js',
    'resources/assets/vendor/libs/cleavejs/cleave-phone.js'
])
@endsection

@section('page-script')
    @vite([
        'resources/assets/js/forms-selects.js',
        'resources/assets/js/pages-profile.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Profil beállítások</h4>

    <div class="alert alert-danger alert-dismissible d-none" role="alert" id="errorAlert">
        <span id="errorAlertMessage"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="nav-align-top">
        <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-noticitation-settings" aria-controls="navs-pills-generic-settings" aria-selected="true">Értesítési beállítások</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-delegations" aria-controls="navs-pills-delegations" aria-selected="false">Helyettesítések</button>
            </li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane fade show active" id="navs-pills-noticitation-settings" role="tabpanel">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="col-3">
                            <label for="approval_notification" class="form-label">Értesítő email jóváhagyóként</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="approval_notification" {{ $approval_notification == 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="approval_notification">Kérek értesítő emailt jóváhagyóként</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success btn-submit">Mentés</button>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-delegations" role="tabpanel">
                <div class="content-header mb-3">
                    <h5 class="mb-0">Helyettesek beállítása</h5>
                    <small>Add meg a helyettesíteni kívánt funkciót és a helyettest</small>
                </div>

                <div class="row">
                    <div class="col-sm-4">
                        <div class="row">
                            <div class="col-sm-12 mb-3">
                                <label for="delegation_type" class="form-label">Helyettesített funkció</label>
                                <select class="form-select select2" id="delegation_type" name="delegation_type">
                                    <option value="" selected>Válassz funkciót</option>
                                    @foreach($delegations as $delegation)
                                        @if(isset($delegation['type']))
                                            <option value="{{ $delegation['type'] }}">{{ $delegation['readable_name'] }}</option>
                                        @elseif(isset($delegation[0]['type']))
                                            <option value="{{ $delegation[0]['type'] }}">{{ $delegation[0]['readable_name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12 mb-3">
                                <label for="delegated_user" class="form-label">Helyettesítő</label>
                                <select class="form-select select2" id="delegated_user" name="delegated_user">
                                    <option value="" selected>Válassz helyettesítőt</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="delegation_start_date" class="form-label">Helyettesítés kezdete</label>
                                <input type="text" id="delegation_start_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="delegation_start_date" />
                            </div>
                            <div class="col-sm-4">
                                <label for="delegation_end_date" class="form-label">Helyettesítés vége</label>
                                <input type="text" id="delegation_end_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="delegation_end_date" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-1 d-flex align-items-center justify-content-center">
                        <a href="javascript:" id="save_delegation"><i class="fas fa-angles-right text-success mt-4 fa-3x"></i></a>
                    </div>
                    <div class="col-sm-7 d-flex align-items-center">
                        <div class="card-datatable table-responsive pt-0">
                            <table class="datatables-delegates table border-top">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Helyettesítő</th>
                                        <th>Funkció</th>
                                        <th>Kezdete</th>
                                        <th>Vége</th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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
                    <p>Biztosan szeretnéd törölni ezt a helyettesítést?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_delete" data-delegation-id="" class="btn btn-primary">Törlés</button>
                </div>
            </div>
        </div>
    </div>
@endsection