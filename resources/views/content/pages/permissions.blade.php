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
        'resources/assets/js/authorizations-permissions.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Jogosultságok</h4>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <table class="datatables-permissions table border-top">
                        <thead>
                            <tr style="background-color: rgba(105,108,255,.16)">
                                <th></th>
                                <th>Név</th>
                                <th>Szerepkör</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection