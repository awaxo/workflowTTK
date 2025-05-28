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
        'resources/assets/js/pages-workflows-closed.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Lezárt ügyek</h4>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card hidden-scroll">
                <div class="card-datatable table-responsive horizontal-scroll pt-0">
                    <table class="datatables-workflows table border-top">
                        <thead>
                            <tr style="background-color: rgba(105,108,255,.16)">
                                <th>ID</th>
                                <th>Típus</th>
                                <th>Státusz</th>
                                <th>Folyamatindító</th>
                                <th>Utolsó módosító</th>
                                <th>Utolsó módosítás</th>
                                <th>Létrehozó</th>
                                <th>Létrehozás</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection