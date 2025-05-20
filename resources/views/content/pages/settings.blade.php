@extends('layouts/layoutMaster')

@section('title', 'Ügyintézés')

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
        'resources/js/app.js',
        'resources/assets/js/pages-settings.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Beállítások</h4>

    <div class="nav-align-top">
        <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-generic-settings" aria-controls="navs-pills-generic-settings" aria-selected="true">Általános paraméterek</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-workflow-deadlines" aria-controls="navs-pills-workflow-deadlines" aria-selected="false">Felvételi kérelem</button>
            </li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane fade show active" id="navs-pills-generic-settings" role="tabpanel">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="col-6">
                            <label for="notification_api_url" class="form-label">Értesítési API URL</label>
                            <div class="d-flex align-items-center">
                                <input class="form-control" type="text" id="notification_api_url" value="{{ $options['notification_api_url'] ?? '' }}" name="notification_api_url" placeholder="https://example.com/api/notification" />
                            </div>
                            <small class="text-muted">A segédtáblák módosításakor (csoport, felhasználó, költséghely, költséghely típus) erre az URL-re küldünk értesítést.</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="col-3">
                            <label for="employer_contribution" class="form-label">Szociális hozzájárulási adó</label>
                            <div class="d-flex align-items-center">
                                <input class="form-control numeral-mask" type="text" id="employer_contribution" value="{{ $options['employer_contribution'] }}" name="employer_contribution" />
                                <span class="ms-2">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success btn-submit-generic">Mentés</button>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-workflow-deadlines" role="tabpanel">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="col-3">
                            <label for="recruitment_auto_suspend_threshold" class="form-label">Elutasítási küszöb felfüggesztett státuszban</label>
                            <div class="d-flex align-items-center">
                                <input class="form-control numeral-mask" type="text" id="recruitment_auto_suspend_threshold" value="{{ $options['recruitment_auto_suspend_threshold'] }}" name="recruitment_auto_suspend_threshold" />
                                <span class="ms-2">Óra</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="col-3">
                            <label for="recruitment_director_approve_salary_threshold" class="form-label">Igazgatói jóváhagyás alsó határa</label>
                            <div class="d-flex align-items-center">
                                <input class="form-control numeral-mask" type="text" id="recruitment_director_approve_salary_threshold" value="{{ $options['recruitment_director_approve_salary_threshold'] }}" name="recruitment_director_approve_salary_threshold" />
                                <span class="ms-2">Ft</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="workflows" class="form-label">Folyamat</label>
                        <select class="form-select select2" id="workflows" name="workflows">
                            <option value="0" selected>Válassz folyamatot</option>
                            @foreach($workflows as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="workflow_states" class="form-label">Státusz</label>
                        <select class="form-select select2" id="workflow_states" name="workflow_states">
                            <option value="0" selected>Válassz státuszt</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="workflow_state_deadline" class="form-label">Határidő</label>
                        <div class="d-flex align-items-center">
                            <input class="form-control numeral-mask" type="text" id="workflow_state_deadline" name="workflow_state_deadline" />
                            <span class="ms-2">Óra</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success btn-submit-deadline">Mentés</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection