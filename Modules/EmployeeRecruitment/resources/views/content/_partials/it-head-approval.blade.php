<div class="d-flex">
    <label class="form-label col-6 col-md-3">Név</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->name }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Csoport 1</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->workgroup1 ? $recruitment->workgroup1->workgroup_number . ' - ' . $recruitment->workgroup1->name : '' }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Csoport 2</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->workgroup2 ? $recruitment->workgroup2->workgroup_number . ' - ' . $recruitment->workgroup2->name : '-' }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Munkakör típusa</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->position ? $recruitment->position->type : '-' }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Munkakör</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->position ? $recruitment->position->name : '-' }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Jogviszony típusa</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->employment_type }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Jogviszony kezdete</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->employment_start_date }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Jogviszony vége</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->employment_end_date }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Heti munkaóraszám</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->weekly_working_hours }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Javasolt email cím</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->email }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Dolgozószoba</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->employee_room ? $recruitment->employee_room : '-' }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Telefon mellék</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->phone_extension ? $recruitment->phone_extension : '-' }}</span>
</div>
<div class="d-flex">
    <label class="form-label col-6 col-md-3">Kezdeményező megjegyzése</label>
    <span class="fw-bold ms-1 text-break">{{ $recruitment->initiator_comment ?: 'Nincs megjegyzés' }}</span>
</div>