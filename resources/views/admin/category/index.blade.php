@extends('admin.layouts.app')

@section('style')
<style>
    .switch { position: relative; display: inline-block; width: 45px; height: 22px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute; cursor: pointer;
        background-color: #d9dee3;
        border-radius: 30px;
        top: 0; left: 0; right: 0; bottom: 0;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 16px; width: 16px;
        left: 3px; bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: .3s;
    }
    input:checked + .slider { background-color: #696cff; }
    input:checked + .slider:before { transform: translateX(22px); }

    .measurement-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        margin: 3px;
        border-radius: 16px;
        background-color: #e0e0e0;
        font-size: 12px;
    }
    .measurement-pill button {
        border: none;
        background: transparent;
        margin-left: 6px;
        font-size: 12px;
        cursor: pointer;
    }

    /* Ensure Select2 dropdown appears above Bootstrap modal */
    .select2-container {
        width: 100% !important;
    }
    .select2-container--open .select2-dropdown {
        z-index: 9999 !important;
    }
</style>
@endsection

@section('content')

<div class="container-fluid py-4">

    <div class="card p-3">

        <div class="d-flex justify-content-between mb-3">
            <h5>Measurement Management</h5>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                Add
            </button>
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
            <input type="text" id="globalSearch" class="form-control" placeholder="Search here">
        </div>

        <table class="table table-bordered" id="categoryTable">
            <thead>
                <tr>
                    <th>SR No.</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>

    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="categoryModal">
    <div class="modal-dialog modal-lg">
        <form id="categoryForm">
            @csrf
            <input type="hidden" id="category_id">
            <input type="hidden" name="measurements" id="measurements_hidden">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitle">Measurement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control">
                            <small class="text-danger error" id="error_name"></small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">YouTube URL (Unlisted)</label>
                            <input type="text" name="youtube_url" id="youtube_url" class="form-control">
                            <small class="text-danger error" id="error_youtube_url"></small>
                        </div>
                    </div>

                    <h6 class="mb-1">Measurement</h6>
                    <div class="mb-2">
                        <select id="measurement_select" class="form-select" multiple>
                            <option value="Neck">Neck</option>
                            <option value="Chest">Chest</option>
                            <option value="Waist">Waist</option>
                            <option value="Shoulder">Shoulder</option>
                            <option value="Sleeve Length">Sleeve Length</option>
                        </select>
                    </div>
                    <small class="text-danger error d-block mb-2" id="error_measurements"></small>

                    <div id="measurementPills"></div>

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

        let measurements = [];

        function renderMeasurements() {
            const container = $('#measurementPills');
            container.empty();

            measurements.forEach((m, index) => {
                const pill = $('<span class="measurement-pill"></span>').text(m);
                const removeBtn = $('<button type="button">x</button>');
                removeBtn.on('click', function () {
                    measurements.splice(index, 1);
                    syncMeasurementsField();
                    renderMeasurements();
                });
                pill.append(removeBtn);
                container.append(pill);
            });
        }

        function syncMeasurementsField() {
            // Only update hidden input (comma separated); do NOT trigger change here
            $('#measurements_hidden').val(measurements.join(','));
        }

        // Initialize Select2 with static data
        $('#measurement_select').select2({
            placeholder: 'Measurement',
            width: '100%'
        });

        $('#measurement_select').on('change', function () {
            const selected = $(this).val() || [];
            measurements = selected;
            syncMeasurementsField();
            renderMeasurements();
        });

        let table = $('#categoryTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthChange: false,
            ajax: {
                url: "{{ route('admin.categories.getall') }}",
                data: function (d) {
                    d.name = $('#filterName').val();
                    d.search_value = $('#globalSearch').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name' },
                { data: 'status', orderable: false },
                { data: 'action', orderable: false }
            ],
            order: [[1, 'asc']]
        });

        $('#perPage').on('change', function () {
            let val = parseInt($(this).val(), 10) || 10;
            table.page.len(val).draw();
        });

        $('#filterName, #globalSearch').keyup(function () {
            table.draw();
        });

        function clearErrors() {
            $('.error').text('');
            $('.form-control').removeClass('is-invalid');
        }

        $('#categoryForm').submit(function (e) {
            e.preventDefault();
            clearErrors();

            let id = $('#category_id').val();
            let url = id ? 'categories/update/' + id : "{{ route('admin.categories.store') }}";

            $('#saveBtn').prop('disabled', true).text('Saving...');

            $.ajax({
                url: url,
                type: "POST",
                data: $(this).serialize(),
                success: function () {
                    $('#categoryModal').modal('hide');
                    table.draw();
                    $('#categoryForm')[0].reset();
                    measurements = [];
                    syncMeasurementsField();
                    renderMeasurements();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Category saved successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    let errors = xhr.responseJSON.errors || {};

                    $.each(errors, function (key, value) {
                        $('#error_' + key).text(value[0]);
                        $('#' + key).addClass('is-invalid');
                    });
                },
                complete: function () {
                    $('#saveBtn').prop('disabled', false).text('Update');
                }
            });
        });

        $(document).on('click', '.editBtn', function () {
            clearErrors();
            let id = $(this).data('id');

            $.get('categories/edit/' + id, function (data) {
                $('#category_id').val(data.id);
                $('#name').val(data.name);
                $('#youtube_url').val(data.youtube_url || '');

                measurements = [];
                if (data.measurements) {
                    measurements = data.measurements.split(',').map(function (item) {
                        return item.trim();
                    }).filter(function (item) {
                        return item.length > 0;
                    });
                }
                // Set Select2 value based on existing measurements
                $('#measurement_select').val(measurements).trigger('change');

                $('#categoryModal').modal('show');
            });
        });

        $(document).on('click', '.deleteBtn', function () {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this category",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'categories/delete/' + id,
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

        $(document).on('change', '.statusToggle', function () {
            let id = $(this).data('id');
            let checkbox = $(this);

            Swal.fire({
                title: 'Change Status?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("{{ route('admin.categories.status') }}", {
                        _token: '{{ csrf_token() }}',
                        id: id
                    }, function () {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            timer: 1200,
                            showConfirmButton: false
                        });
                        table.draw();
                    });
                } else {
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
            });
        });

    });
</script>
@endsection

