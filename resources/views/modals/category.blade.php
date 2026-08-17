<div class="modal fade" id="category_modal" tabindex="-1">
    <form id="category_form">
    @csrf
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header header-custom">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Add Category</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="category">Category Name*</label>
                            <span id="category_msg" class="text-danger text-right pull-right"></span>
                            <input type="text" class="form-control" id="category" name="category">
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
                <button type="button" id="add_category" class="btn btn-primary add_category">Save</button>
            </div>
        </div>
    </div>
    </form>
</div>
