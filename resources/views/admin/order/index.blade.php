@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card p-3 mb-4">
        <div class="d-flex justify-content-between mb-3">
            <h5 class="mb-0">Orders Management</h5>
            <button class="btn btn-primary" id="addOrderBtn">Add Order</button>
        </div>

        <div class="d-flex gap-2 mb-3">
            <div class="w-25">
                <select class="form-select" id="perPage">
                    <option value="10">Show: 10</option>
                    <option value="25">Show: 25</option>
                    <option value="50">Show: 50</option>
                </select>
            </div>
            <input type="text" id="filterName" class="form-control" placeholder="Name">
            <input type="text" id="filterOrderNo" class="form-control" placeholder="Order No.">
            <input type="text" id="globalSearch" class="form-control" placeholder="Search here">
        </div>

        <table class="table table-bordered" id="orderTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Order No.</th>
                    <th>User Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="orderModal">
    <div class="modal-dialog modal-xl">
        <form id="orderForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="order_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add / Edit Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">User Name</label>
                            <input type="text" name="user_name" id="user_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile" id="mobile" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="text" name="email" id="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Order No.</label>
                            <input type="text" name="order_no" id="order_no" class="form-control" placeholder="Auto generated if blank">
                        </div>

                        <div class="col-12 mt-2"><h6 class="mb-0">Stitch For</h6></div>
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="stitch_for_name" id="stitch_for_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone No.</label>
                            <input type="text" name="phone_no" id="phone_no" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Height</label>
                            <input type="text" name="height" id="height" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Body Weight</label>
                            <input type="text" name="body_weight" id="body_weight" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Shoes Size</label>
                            <input type="text" name="shoes_size" id="shoes_size" class="form-control">
                        </div>

                        <div class="col-12 mt-2"><h6 class="mb-0">Photos</h6></div>
                        <div class="col-md-4">
                            <label class="form-label">Front Photo</label>
                            <input type="file" name="front_photo" id="front_photo" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Side Photo</label>
                            <input type="file" name="side_photo" id="side_photo" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Back Photo</label>
                            <input type="file" name="back_photo" id="back_photo" class="form-control">
                        </div>

                        <div class="col-12 mt-2"><h6 class="mb-0">Measurement</h6></div>
                        <div class="col-md-4">
                            <label class="form-label">Neck</label>
                            <input type="text" name="neck" id="neck" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Chest</label>
                            <input type="text" name="chest" id="chest" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Shoulder</label>
                            <input type="text" name="shoulder" id="shoulder" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sleeve Length</label>
                            <input type="text" name="sleeve_length" id="sleeve_length" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Waist</label>
                            <input type="text" name="waist" id="waist" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Additional Requirement</label>
                            <textarea name="additional_requirement" id="additional_requirement" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-12">
                            <h6 class="mb-2">Category & Stitch Master</h6>
                            <div id="assignmentsContainer"></div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addAssignmentBtn">
                                Add Category
                            </button>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Admin Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="complete">Complete</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="saveBtn">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        const categoryStitchMap = @json($categoryStitchMap ?? []);
        const categoriesList = @json($categories->map(function ($c) {
            return ['id' => $c->id, 'name' => $c->name];
        })->values()->all());

        let categoryOptionsHtml = '<option value="">Select Category</option>';
        categoriesList.forEach(c => {
            categoryOptionsHtml += `<option value="${c.id}">${c.name}</option>`;
        });

        function populateStitchMasters($stitchSelect, categoryId, selectedStitchId) {
            $stitchSelect.empty();
            $stitchSelect.append('<option value="">Select Stitch Master</option>');
            const list = categoryStitchMap[categoryId] || [];
            let foundSelected = false;
            list.forEach(s => {
                const opt = $('<option/>').val(s.id).text(s.full_name);
                if (selectedStitchId && String(selectedStitchId) === String(s.id)) {
                    opt.prop('selected', true);
                    foundSelected = true;
                }
                $stitchSelect.append(opt);
            });

            // If the selected stitch master isn't in the mapping (e.g. deactivated),
            // still show it so the edit modal displays the current value.
            if (selectedStitchId && !foundSelected) {
                $stitchSelect.append(
                    $('<option/>').val(selectedStitchId).text('Stitch Master #' + selectedStitchId).prop('selected', true)
                );
            }

            // If user picked a category and only one stitch master exists, auto-select it.
            if (!selectedStitchId && list.length === 1) {
                $stitchSelect.val(list[0].id);
            }
        }

        function syncRemoveButtons() {
            const $rows = $('#assignmentsContainer .assignment-row');
            $rows.each(function (idx) {
                const showRemove = idx !== 0 || $rows.length > 1;
                $(this).find('.removeAssignmentBtn').toggle(showRemove);
            });
        }

        function createAssignmentRow(data = {}) {
            const $row = $(`
                <div class="assignment-row row g-2 align-items-end mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category_ids[]" class="form-select category_select">
                            ${categoryOptionsHtml}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Assign Stitch Master</label>
                        <select name="stitch_master_ids[]" class="form-select stitch_select">
                            <option value="">Select Stitch Master</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stitch Master Status</label>
                        <select name="stitch_statuses[]" class="form-select stitch_status_select">
                            <option value="pending">Pending</option>
                            <option value="trial_ready">Trial Ready</option>
                            <option value="complete">Complete</option>
                        </select>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger removeAssignmentBtn" title="Remove">x</button>
                    </div>
                </div>
            `);

            const categoryId = data.category_id || '';
            const stitchMasterId = data.stitch_master_id || '';
            const stitchStatus = data.stitch_status || 'pending';

            $row.find('.category_select').val(categoryId);
            $row.find('.stitch_status_select').val(stitchStatus);
            if (categoryId) {
                populateStitchMasters($row.find('.stitch_select'), categoryId, stitchMasterId);
            }

            return $row;
        }

        function resetAssignments() {
            $('#assignmentsContainer').empty();
            $('#assignmentsContainer').append(createAssignmentRow());
            syncRemoveButtons();
        }

        let table = $('#orderTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthChange: false,
            ajax: {
                url: "{{ route('admin.orders.getall') }}",
                data: function (d) {
                    d.name = $('#filterName').val();
                    d.order_no = $('#filterOrderNo').val();
                    d.search_value = $('#globalSearch').val();
                }
            },
            columns: [
                { data: 'created_at', render: function (data) { return data ? new Date(data).toLocaleDateString() : ''; } },
                { data: 'order_no' },
                { data: 'user_name' },
                { data: 'mobile' },
                { data: 'email' },
                { data: 'status' },
                { data: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });

        $('#perPage').on('change', function () {
            table.page.len(parseInt($(this).val(), 10) || 10).draw();
        });

        $('#filterName, #filterOrderNo, #globalSearch').on('keyup', function () {
            table.draw();
        });

        function resetForm() {
            $('#orderForm')[0].reset();
            $('#order_id').val('');
            resetAssignments();
            $('#status').val('pending');
        }

        $('#addOrderBtn').on('click', function () {
            resetForm();
            $('#orderModal').modal('show');
        });

        $('#orderForm').submit(function (e) {
            e.preventDefault();

            // Remove empty category rows before building FormData.
            $('#assignmentsContainer .assignment-row').each(function () {
                const catId = $(this).find('.category_select').val();
                if (!catId) {
                    $(this).remove();
                }
            });

            let id = $('#order_id').val();
            let url = id ? 'orders/update/' + id : "{{ route('admin.orders.store') }}";
            let formData = new FormData(this);

            $('#saveBtn').prop('disabled', true).text('Saving...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function () {
                    $('#orderModal').modal('hide');
                    table.draw();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Order saved successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    let message = xhr.responseJSON?.message || 'Failed to save order';
                    Swal.fire({ icon: 'error', title: 'Error', text: message });
                },
                complete: function () {
                    $('#saveBtn').prop('disabled', false).text('Update');
                }
            });
        });

        $(document).on('click', '.editBtn', function () {
            let id = $(this).data('id');
            $.get('orders/edit/' + id, function (data) {
                $('#order_id').val(data.id);
                $('#user_name').val(data.user_name);
                $('#mobile').val(data.mobile);
                $('#email').val(data.email);
                $('#order_no').val(data.order_no);
                $('#stitch_for_name').val(data.stitch_for_name);
                $('#phone_no').val(data.phone_no);
                $('#height').val(data.height);
                $('#body_weight').val(data.body_weight);
                $('#shoes_size').val(data.shoes_size);
                $('#neck').val(data.neck);
                $('#chest').val(data.chest);
                $('#shoulder').val(data.shoulder);
                $('#sleeve_length').val(data.sleeve_length);
                $('#waist').val(data.waist);
                $('#additional_requirement').val(data.additional_requirement);

                $('#assignmentsContainer').empty();
                const items = data.categoryStitchItems || [];
                if (items.length === 0) {
                    $('#assignmentsContainer').append(createAssignmentRow());
                } else {
                    items.forEach((item, idx) => {
                        $('#assignmentsContainer').append(createAssignmentRow({
                            category_id: item.category_id,
                            stitch_master_id: item.stitch_master_id,
                            stitch_status: item.stitch_status || 'pending'
                        }));
                    });
                }
                syncRemoveButtons();

                $('#status').val(data.status || 'pending');
                $('#orderModal').modal('show');
            });
        });

        $(document).on('click', '.deleteBtn', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this order',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'orders/delete/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function () {
                            table.draw();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
        });

        // Category selection inside assignment rows.
        $(document).on('change', '.assignment-row .category_select', function () {
            const $row = $(this).closest('.assignment-row');
            const categoryId = $(this).val();
            populateStitchMasters($row.find('.stitch_select'), categoryId, null);
        });

        $('#addAssignmentBtn').on('click', function () {
            $('#assignmentsContainer').append(createAssignmentRow());
            syncRemoveButtons();
        });

        $(document).on('click', '.removeAssignmentBtn', function () {
            const $row = $(this).closest('.assignment-row');
            $row.remove();
            syncRemoveButtons();
        });

        resetAssignments();
    });
</script>
@endsection
