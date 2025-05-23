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
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
    @vite([
        'resources/js/app.js',
        'resources/assets/js/pages-workflows.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Nyitott folyamatok</h4>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <table class="datatables-workflows table border-top">
                        <thead>
                            <tr style="background-color: rgba(105,108,255,.16)">
                                <th>ID</th>
                                <th>Típus</th>
                                <th>Folyamatindító</th>
                                <th>Státusz</th>
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
                    <button type="button" id="confirm_delete" data-workflow-id="" class="btn btn-primary">Sztornózás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit information modal -->
    <div class="modal fade" id="auditModal" tabindex="-1" aria-labelledby="auditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="auditModalLabel">Folyamat audit adatok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow-none">
                                <div class="card-body p-0">
                                    <!-- Creation Info -->
                                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-success">
                                                <i class="ti ti-plus"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Létrehozás</h6>
                                            <p class="mb-0 text-muted medium" id="createdByName">-</p>
                                            <p class="mb-0 text-muted medium" id="createdAtDate">-</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Last Modification Info -->
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-warning">
                                                <i class="ti ti-edit"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Utolsó módosítás</h6>
                                            <p class="mb-0 text-muted medium" id="updatedByName">-</p>
                                            <p class="mb-0 text-muted medium" id="updatedAtDate">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
                </div>
            </div>
        </div>
    </div>
@endsection