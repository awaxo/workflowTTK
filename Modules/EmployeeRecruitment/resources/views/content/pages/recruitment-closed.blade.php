@extends('layouts/layoutMaster')

@section('title', 'Csoportok')

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
        'Modules/EmployeeRecruitment/resources/assets/js/recruitment-closed.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Lezárt felvételi kérelmek</h4>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <table class="datatables-recruitments table border-top">
                        <thead>
                            <tr style="background-color: rgba(105,108,255,.16)">
                                <th>ID</th>
                                <th>Név</th>
                                <th>Státusz</th>
                                <th>Csoport 1</th>
                                <th>Csoport 2</th>
                                <th>Költséghely 1</th>
                                <th>Munkakör típusa</th>
                                <th>Munkakör</th>
                                <th>Jogviszony típusa</th>
                                <th>Jogviszony kezdete</th>
                                <th>Létrehozás</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>        
@endsection