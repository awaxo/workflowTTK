<div class="card">
    <div class="card-body">
        <div class="content-header mb-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">A munkakör (munkahely) főbb egészségkárosító kockázatai</h5>
                <small>33/1998 (VI. 24.) NM rendelet szerint</small>
            </div>
            <i class="fas fa-question-circle fa-2x help-icon" data-bs-toggle="modal" data-bs-target="#helpModal" title="Segítség"></i>
        </div>
        <div class="row g-3">
            <div class="row g-3 align-items-center mt-3">
                <div class="col-auto">
                    <span>Kézi anyagmozgatás</span>
                </div>
                <div class="col-auto">
                    <input type="radio" id="whole_work_time" name="manual_handling_time" value="whole" class="form-check-input">
                    <label class="form-check-label" for="whole_work_time">A munkaidő egészében</label>
                </div>
                <div class="col-auto">
                    <input type="radio" id="part_of_work_time" name="manual_handling_time" value="part" class="form-check-input">
                    <label class="form-check-label" for="part_of_work_time">A munkidő egy részében</label>
                </div>
            </div>
            
            <div class="row g-3 align-items-center mt-3">
                <div class="col-auto">
                    <span>5 kg – 20 kg</span>
                </div>
                <div class="col-auto">
                    <input type="radio" id="whole_work_time_5_20" name="manual_handling_weight_5_20" value="whole" class="form-check-input">
                    <label class="form-check-label" for="whole_work_time_5_20">A munkaidő egészében</label>
                </div>
                <div class="col-auto">
                    <input type="radio" id="part_of_work_time_5_20" name="manual_handling_weight_5_20" value="part" class="form-check-input">
                    <label class="form-check-label" for="part_of_work_time_5_20">A munkidő egy részében</label>
                </div>
            </div>
            
            <div class="row g-3 align-items-center mt-3">
                <div class="col-auto">
                    <span>20 kg – 50 kg</span>
                </div>
                <div class="col-auto">
                    <input type="radio" id="whole_work_time_20_50" name="manual_handling_weight_20_50" value="whole" class="form-check-input">
                    <label class="form-check-label" for="whole_work_time_20_50">A munkaidő egészében</label>
                </div>
                <div class="col-auto">
                    <input type="radio" id="part_of_work_time_20_50" name="manual_handling_weight_20_50" value="part" class="form-check-input">
                    <label class="form-check-label" for="part_of_work_time_20_50">A munkidő egy részében</label>
                </div>
            </div>
            
            <div class="row g-3 align-items-center mt-3">
                <div class="col-auto">
                    <span>> 50kg</span>
                </div>
                <div class="col-auto">
                    <input type="radio" id="whole_work_time_over_50" name="manual_handling_weight_over_50" value="whole" class="form-check-input">
                    <label class="form-check-label" for="whole_work_time_over_50">A munkaidő egészében</label>
                </div>
                <div class="col-auto">
                    <input type="radio" id="part_of_work_time_over_50" name="manual_handling_weight_over_50" value="part" class="form-check-input">
                    <label class="form-check-label" for="part_of_work_time_over_50">A munkidő egy részében</label>
                </div>
            </div>
        </div>

        <!-- Help Modal -->
        <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Leírás</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Specifikus segítség a kitöltéshez</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bezárás</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>