<div class="modal fade" id="brand_modal" tabindex="-1">
    <form id="brand_form">
    @csrf
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header header-custom">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Add Brand</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="brand">Brand Name*</label>
                            <span id="brand_msg" class="text-danger text-right pull-right"></span>
                            <input type="text" class="form-control" id="brand" name="brand">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <span id="description_msg" class="text-danger text-right pull-right"></span>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                <button type="button" id="add_brand" class="btn btn-primary add_brand">Save</button>
            </div>
        </div>
    </div>
    </form>
</div>
