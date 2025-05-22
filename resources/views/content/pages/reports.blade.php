@extends('layouts/layoutMaster')

@section('title', 'Lekérdezések')

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
        'resources/assets/js/pages-reports.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Lekérdezések</h4>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Éves összesítő riportok</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-4">
                    <label for="report_year" class="form-label">Évszám</label>
                    <select class="form-select select2" id="report_year" name="report_year">
                        <option value="" selected>Válassz évet</option>
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <label for="report_type" class="form-label">Riport típusa</label>
                    <select class="form-select select2" id="report_type" name="report_type">
                        <option value="" selected>Válassz riport típust</option>
                        @foreach($reportTypes as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 d-flex align-items-end">
                    <button class="btn btn-primary me-2 btn-generate-report">Lekérdezés</button>
                    <button class="btn btn-success btn-export-report" disabled>Excel export</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="card mt-4" id="results-card" style="display: none;">
        <div class="card-header">
            <h5 class="card-title mb-0" id="results-title">Lekérdezés eredménye</h5>
        </div>
        <div class="card-body">
            <div id="results-content">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Job Advertisement Statistics Template -->
    <template id="job-ad-stats-template">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Mutató</th>
                        <th>Érték</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Összes lezárt ügy száma</td>
                        <td class="total-completed"></td>
                    </tr>
                    <tr>
                        <td>Álláshirdetéssel történt felvétel</td>
                        <td class="with-job-ad"></td>
                    </tr>
                    <tr>
                        <td>Álláshirdetésre jelentkező nők száma</td>
                        <td class="female-applicants"></td>
                    </tr>
                    <tr>
                        <td>Álláshirdetésre jelentkező férfiak száma</td>
                        <td class="male-applicants"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </template>

    <!-- Chemical Workers Template -->
    <template id="chemical-workers-template">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="chemical-workers-table">
                <thead class="table-light">
                    <tr>
                        <th>Név</th>
                        <th>Kitettség szintje</th>
                        <th>Kémiai kóroki tényezők</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamic rows will be added here -->
                </tbody>
            </table>
        </div>
    </template>

    <!-- Carcinogenic Workers Template -->
    <template id="carcinogenic-workers-template">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="carcinogenic-workers-table">
                <thead class="table-light">
                    <tr>
                        <th>Név</th>
                        <th>Kitettség szintje</th>
                        <th>Rákkeltő anyagok</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamic rows will be added here -->
                </tbody>
            </table>
        </div>
    </template>
@endsection