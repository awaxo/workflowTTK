@extends('layouts/layoutMaster')

@section('title', 'Ügyintézés')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
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
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
])
@endsection

@section('page-script')
    @vite([
        'resources/js/app.js',
        'Modules/EmployeeRecruitment/resources/assets/js/recruitment-opened.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Nyitott felvételi kérelmek</h4>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    @php
                        $isSecretary = Auth::user()->roles->filter(function($role) {
                            return Str::startsWith($role->name, 'titkar');
                        })->isNotEmpty();
                    @endphp
                    <script>
                        window.isSecretary = @json($isSecretary);
                    </script>

                    <table class="datatables-recruitments table border-top">
                        <thead>
                            <tr style="background-color: rgba(105,108,255,.16)">
                                <th>ID</th>
                                <th>Név</th>
                                <th>Csoport 1</th>
                                <th>Csoport 2</th>
                                <th>Költséghely 1</th>
                                <th>Munkakör típusa</th>
                                <th>Munkakör</th>
                                <th>Jogviszony típusa</th>
                                <th>Jogviszony kezdete</th>
                                <th>Létrehozás</th>
                                <th>Státusz</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel confirmation modal -->
    <div class="modal fade" id="deleteConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Biztosan szeretnéd sztornózni ezt az ügyet?</p>
                    <div class="col-sm-12">
                        <label for="cancel_reason" class="form-label">Indoklás</label>
                        <textarea class="form-control" id="cancel_reason" rows="3"></textarea>                              
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_cancel" data-workflow-id="" class="btn btn-primary">Sztornózás</button>
                </div>
            </div>
        </div>
    </div>
@endsection