@extends('layouts/layoutMaster')

@section('title', 'Ügyintézés')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
        'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/dropzone/dropzone.scss',
        'resources/css/app.css'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/select2/i18n/hu.js',
        'resources/assets/vendor/libs/cleavejs/cleave.js',
        'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
        'resources/assets/vendor/libs/dropzone/dropzone.min.js',
        'resources/assets/vendor/libs/dropzone/dropzone.js',
    ])
@endsection

@section('page-script')
    @vite([
        'resources/js/app.js',
        'Modules/EmployeeRecruitment/resources/assets/js/approve-recruitment.js'
    ])
@endsection

@section('content')
<h4 class="py-3 mb-2">Folyamat megtekintés / <span class="dynamic-part">{{ $recruitment->name }}</span></h4>

<!-- Back Button -->
<div class="mb-4">
    <button onclick="window.location.href='/hr/felveteli-kerelem'" class="btn btn-secondary">Vissza</button>
</div>

<div class="mb-2" style="font-size: larger;">
    <div class="">ID: <b>{{ $recruitment->pseudo_id }}/{{ \Carbon\Carbon::parse($recruitment->created_at)->format('Y') }}</b></div>
</div>

<!-- Form with Tabs -->
<div class="row">
    <div class="col">
        <div class="nav-align-top mb-3">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab_decision" role="tab" aria-selected="true">Részletek</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_status_history" role="tab" aria-selected="false">Státusztörténet</button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade active show" id="tab_decision" role="tabpanel">
                    <!-- Recruitment details -->
                    <div class="accordion" id="accordion_process_details">
                        @if ($isITHead && !$hasNonITHeadPermission)
                            @include('EmployeeRecruitment::content._partials.it-head-approval', ['recruitment' => $recruitment])
                        @else
                            @include('EmployeeRecruitment::content._partials.all-approval', ['recruitment' => $recruitment])
                        @endif
                    </div>
                </div>
                <div class="tab-pane fade" id="tab_status_history" role="tabpanel">
                    <div id="status_history">
                        <table class="table">
                            <thead>
                                <tr style="background-color: rgba(105,108,255,.16)">
                                    <th>Döntés</th>
                                    <th>Dátum</th>
                                    <th>Felhasználó</th>
                                    <th>Státusz</th>
                                    <th>Üzenet</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($history as $history_entry)
                                <tr>
                                    <td>
                                        <span class="badge bg-label-{{ 
                                            $history_entry['decision'] == 'approve' ? 'success' : 
                                            ($history_entry['decision'] == 'reject' ? 'danger' : 
                                            ($history_entry['decision'] == 'suspend' ? 'warning' : 
                                            ($history_entry['decision'] == 'start' ? 'success' : 
                                            ($history_entry['decision'] == 'restart' ? 'success' : 
                                            ($history_entry['decision'] == 'delete' ? 'danger' : 
                                            ($history_entry['decision'] == 'cancel' ? 'danger' : 'info')))))) }} me-1">
                                            {{ 
                                                $history_entry['decision'] == 'approve' ? 'Jóváhagyás' : 
                                                ($history_entry['decision'] == 'reject' ? 'Elutasítás' : 
                                                ($history_entry['decision'] == 'suspend' ? 'Felfüggesztés' : 
                                                ($history_entry['decision'] == 'start' ? 'Indítás' : 
                                                ($history_entry['decision'] == 'restart' ? 'Újraindítás' : 
                                                ($history_entry['decision'] == 'delete' ? 'Törlés' : 
                                                ($history_entry['decision'] == 'cancel' ? 'Sztornózás' : 'Visszaállítás')))))) }}
                                        </span>
                                    </td>
                                    <td>{{ $history_entry['datetime'] }}</td>
                                    <td>{{ $history_entry['user_name'] }}</td>
                                    <td>{{ $history_entry['decision'] == 'start' ? 'Új kérelem' : ($history_entry['decision'] == 'suspend' ? 'Felfüggesztve' : ($history_entry['decision'] == 'restore' ? 'Visszaállítva' : ($history_entry['decision'] == 'reject' || $history_entry['decision'] == 'restart' ? 'Kérelem újraellenőrzésére vár' : ($history_entry['decision'] == 'cancel' ? 'Elutasítva' : __('states.' . $history_entry['status']))))) }}</td>
                                    <td>{{ $history_entry['message'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="fst-italic">Aktuális státusz: <b>{{ __('states.' . $recruitment->state) }}</b></div>
                <div class="fst-italic">Szükséges jóváhagyók (a lista a jóváhagyókat és az esetleges helyetteseiket is tartalmazza): <b>{{ $usersToApprove ? $usersToApprove : '' }}</b></div>

                @if ($isHRHead && $recruitment->state != 'request_review' && $recruitment->state != 'completed' && $recruitment->state != 'cancelled')
                    <div class="mb-3 mt-4">
                        <label class="form-label" for="message">Üzenet</label>
                        <textarea id="message" class="form-control" placeholder="Üzenet..."></textarea>
                    </div>

                    <div class="d-grid mt-4 d-md-block decision-controls">
                        <button type="button" id="reject" class="btn btn-label-danger me-2">Elutasítás</button>                        
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Decision message missing modal -->
<div class="modal fade" id="messageMissing" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p>Amennyiben elutasítod a kérelem jóváhagyását, kérlek írj indoklást az üzenet mezőbe!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Rendben</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject confirmation modal -->
<div class="modal fade" id="rejectConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Biztosan szeretnéd elutasítani ezt az ügyet és visszaküldeni az indítóhoz?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" id="confirm_reject" data-recruitment-id="{{ $recruitment->id }}" class="btn btn-primary">Elutasítás</button>
            </div>
        </div>
    </div>
</div>
@endsection
