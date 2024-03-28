@extends('layouts/layoutMaster')

@section('title', 'Jogosultságok')

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
        'resources/assets/js/authorizations-permissions.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Jogosultságok</h4>
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.0.1/min/dropzone.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.js"></script>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Upload Multiple Images using dropzone.js and Laravel</h1>
                <!-- FILEPATH: /c:/Munka/TTK/workflowTTK/resources/views/content/pages/permissions.blade.php -->
                <!-- BEGIN: ed8c6549bwf9 -->
                <form action="/test" method="POST" enctype="multipart/form-data" class="dropzone" id="image-upload">
                    <div>
                        <h3>Upload Multiple Image By Click On Box</h3>
                    </div>
                </form>
                <!-- END: ed8c6549bwf9 -->
            </div>
        </div>
    </div><script type="text/javascript">
            Dropzone.options.imageUpload = {
                maxFilesize         :       1,
                acceptedFiles: ".jpeg,.jpg,.png,.gif"
            };
    </script> --}}

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <table class="datatables-permissions table border-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Név</th>
                                <th>Tag</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection