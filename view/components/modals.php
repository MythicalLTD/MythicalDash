<!-- START OF #createticket -->
<div class="modal fade" id="createticket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content p-3 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2">Create a new ticket</h3>
                    <p class="text-muted">In order to be able to create a ticket, we need some information!</p>
                </div>
                <form method="GET" action="/help-center/tickets/new" class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" placeholder="Help" required/>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="priority">Priority</label>
                        <select id="priority" name="priority" class="select2 form-select" placeholder="Select priority" required data-allow-clear="true">
                            <option value="">Select</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="description">Description</label>
                        <textarea required class="form-control" name="description" id="description" rows="3"
                            placeholder="Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="attachment">Attachment</label>
                        <input type="text" id="attachment" name="attachment" class="form-control" placeholder="https://i.imgur.com/yed5Zfk.gif" required/>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END OF #createticket -->