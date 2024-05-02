@extends('layouts/layoutMaster')

@section('title', 'Profil')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
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
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/cleavejs/cleave.js',
    'resources/assets/vendor/libs/cleavejs/cleave-phone.js'
])
@endsection

@section('page-script')
    @vite([
        'resources/assets/js/pages-settings.js'
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
                        <div class="col-4">
                            <label for="suspension_thresold" class="form-label">Törlési küszöb felfüggesztett státuszban</label>
                            <div class="d-flex align-items-center">
                                <input class="form-control numeral-mask" type="text" id="suspension_thresold" value="96" />
                                <span class="ms-2">Óra</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="col-4">
                            <label for="director_approval_thresold" class="form-label">Igazgatói jóváhagyás alsó határa</label>
                            <div class="d-flex align-items-center">
                                <input class="form-control numeral-mask" type="text" id="director_approval_thresold" value="5000000" />
                                <span class="ms-2">Ft</span>
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

                <div class="row g-3">
                    <div class="col-sm-4">
                        <label for="workgroup_id_1" class="form-label">Helyettesített funkció</label>
                        <select class="form-select select2" id="workgroup_id_1">
                            <option selected>Válassz funkciót</option>
                            @foreach($delegations as $delegation)
                                <option value="{{ $delegation['type'] }}">{{ $delegation['readable_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection