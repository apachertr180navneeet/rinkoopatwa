@extends('admin.layouts.app')

@section('style')
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 45px;
            height: 22px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            background-color: #d9dee3;
            border-radius: 30px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: .3s;
        }

        input:checked+.slider {
            background-color: #696cff;
        }

        input:checked+.slider:before {
            transform: translateX(22px);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">

        <div class="card p-3">

            <div class="d-flex justify-content-between mb-3">
                <h5>Measurement Management</h5>

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#measurementModal">
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
                <input type="text" id="globalSearch" class="form-control" placeholder="Search here">
            </div>

            <table class="table table-bordered" id="measurementTable">
                <thead>
                    <tr>
                        <th>SR No.</th>
                        <th>Name</th>
                        <th>Remark</th>
                        <th>Video Link</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>

    <!-- Add / Edit Modal -->
    <div class="modal fade" id="measurementModal">
        <div class="modal-dialog modal-lg">
            <form id="measurementForm">
                @csrf
                <input type="hidden" id="measurement_id">

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
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Remark <small class="text-muted">(e.g. how to measure)</small></label>
                                <textarea name="remark" id="remark" class="form-control" rows="2"></textarea>
                                <small class="text-danger error" id="error_remark"></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Video Link <small class="text-muted">(YouTube tutorial URL)</small></label>
                                <input type="url" name="video_link" id="video_link" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                                <small class="text-danger error" id="error_video_link"></small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function() {

            let table = $('#measurementTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthChange: false,
                ajax: {
                    url: "{{ route('admin.measurements.getall') }}",
                    data: function(d) {
                        d.search_value = $('#globalSearch').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name' },
                    { data: 'remark', orderable: false },
                    { data: 'video_link', orderable: false },
                    { data: 'status', orderable: false },
                    { data: 'action', orderable: false }
                ],
                order: [[1, 'asc']]
            });

            $('#perPage').on('change', function() {
                let val = parseInt($(this).val(), 10) || 10;
                table.page.len(val).draw();
            });

            $('#globalSearch').keyup(function() {
                table.draw();
            });

            function clearErrors() {
                $('.error').text('');
                $('.form-control').removeClass('is-invalid');
            }

            $('#measurementForm').submit(function(e) {
                e.preventDefault();
                clearErrors();
                let formData = new FormData(this);
                let id = $('#measurement_id').val();
                let url = id ? 'measurements/update/' + id : "{{ route('admin.measurements.store') }}";

                $('#saveBtn').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function() {
                        $('#measurementModal').modal('hide');
                        table.draw();
                        $('#measurementForm')[0].reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Measurement saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors || {};
                        $.each(errors, function(key, value) {
                            $('#error_' + key).text(value[0]);
                            $('#' + key).addClass('is-invalid');
                        });
                    },
                    complete: function() {
                        $('#saveBtn').prop('disabled', false).text('Save');
                    }
                });
            });

            $(document).on('click', '.editBtn', function() {
                clearErrors();
                let id = $(this).data('id');

                $.get('measurements/edit/' + id, function(data) {
                    $('#measurement_id').val(data.id);
                    $('#name').val(data.name);
                    $('#remark').val(data.remark || '');
                    $('#video_link').val(data.video_link || '');
                    $('#measurementModal').modal('show');
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this measurement",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'measurements/delete/' + id,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function() {
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

            $(document).on('change', '.statusToggle', function() {
                let id = $(this).data('id');
                let checkbox = $(this);

                Swal.fire({
                    title: 'Change Status?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ route('admin.measurements.status') }}", {
                            _token: '{{ csrf_token() }}',
                            id: id
                        }, function() {
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

            $('#measurementModal').on('hidden.bs.modal', function () {
                clearErrors();
                $('#measurementForm')[0].reset();
                $('#measurement_id').val('');
                $('#saveBtn').prop('disabled', false).text('Save');
            });

        });
    </script>
@endsection
