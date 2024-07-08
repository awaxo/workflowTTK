@extends('layouts/layoutMaster')

@section('title', 'Folyamatok / ' . $recruitment->name)

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/dropzone/dropzone.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/cleavejs/cleave.js',
        'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
        'resources/assets/vendor/libs/dropzone/dropzone.min.js',
        'resources/assets/vendor/libs/dropzone/dropzone.js',
    ])
@endsection

@section('page-script')
    @vite([
        'Modules/EmployeeRecruitment/resources/assets/js/approve-recruitment.js'
    ])
@endsection

@section('content')
<h4 class="py-3 mb-4">Folyamat megtekintés / <span class="dynamic-part">{{ $recruitment->name }}</span></h4>

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
                    @if ($isITHead)
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
                                <td><span class="badge bg-label-{{ $history_entry['decision'] == 'approve' ? 'success' : ($history_entry['decision'] == 'reject' ? 'danger' : ($history_entry['decision'] == 'suspend' ? 'warning' : ($history_entry['decision'] == 'start' ? 'success' : 'info'))) }} me-1">
                                    {{ $history_entry['decision'] == 'approve' ? 'Jóváhagyás' : ($history_entry['decision'] == 'reject' ? 'Elutasítás' : ($history_entry['decision'] == 'suspend' ? 'Felfüggesztés' : ($history_entry['decision'] == 'start' ? 'Indítás' : 'Visszaállítás'))) }}</span></td>
                                <td>{{ $history_entry['datetime'] }}</td>
                                <td>{{ $history_entry['user_name'] }}</td>
                                <td>{{ __('states.' . $history_entry['status']) }}</td>
                                <td>{{ $history_entry['message'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="fst-italic">Szükséges jóváhagyók (a lista a jóváhagyókat és az esetleges helyetteseiket is tartalmazza): <b>{{ $usersToApprove ? $usersToApprove : '' }}</b></div>
        </div>
    </div>
</div>
@endsection
