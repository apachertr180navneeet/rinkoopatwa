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
</style>
@endsection

@section('content')

<div class="container-fluid py-4">

<div class="card p-3">

    <div class="d-flex justify-content-between mb-3">
        <h5>Stitch Management</h5>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#stitchModel">
            Add Stitch
        </button>
    </div>

    <div class="d-flex gap-2 mb-3">
        <input type="text" id="stitchName" class="form-control" placeholder="stitch Name">
        <input type="text" id="globalSearch" class="form-control" placeholder="Search">
    </div>

    <table class="table table-bordered" id="stitchTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Location</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>

</div>
</div>

<!-- MODAL -->
<!-- MODAL -->
<div class="modal fade" id="stitchModel">
    <div class="modal-dialog">
        <form id="stitchForm">
            @csrf
            <input type="hidden" id="stitch_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitle">Stitch Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="text" name="name" id="name" class="form-control mb-1" placeholder="Name">
                    <small class="text-danger error" id="error_name"></small>

                    <input type="text" name="mobile" id="mobile" class="form-control mb-1" placeholder="Mobile">
                    <small class="text-danger error" id="error_mobile"></small>

                    <input type="email" name="email" id="email" class="form-control mb-1" placeholder="Email">
                    <small class="text-danger error" id="error_email"></small>

                    <input type="text" name="city" id="city" class="form-control mb-1" placeholder="Location">
                    <small class="text-danger error" id="error_city"></small>

                    <!-- ✅ PASSWORD FIELD -->
                    <input type="password" name="password" id="password" class="form-control mb-1" placeholder="Password">
                    <small class="text-danger error" id="error_password"></small>

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
$(function () {

    let table = $('#stitchTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.stitch.getall') }}",
            data: function (d) {
                d.name = $('#stitchName').val();
                d.search_value = $('#globalSearch').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'full_name' },
            { data: 'phone' },
            { data: 'email' },
            { data: 'city' },
            { data: 'status', orderable:false },
            { data: 'action', orderable:false }
        ],
        order: [[1,'asc']]
    });

    $('#stitchName, #globalSearch').keyup(function(){
        table.draw();
    });

    // CLEAR ERRORS
    function clearErrors(){
        $('.error').text('');
        $('.form-control').removeClass('is-invalid');
    }

    // SAVE
    $('#stitchForm').submit(function(e){
        e.preventDefault();

        clearErrors();

        let id = $('#stitch_id').val();
        let url = id ? 'stitch/update/'+id : "{{ route('admin.stitch.store') }}";

        $('#saveBtn').prop('disabled', true).text('Saving...');

        $.ajax({
            url: url,
            type: "POST",
            data: $(this).serialize(),

            success: function(res){
                $('#stitchModel').modal('hide');
                table.draw();
                $('#stitchForm')[0].reset();

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'stitch saved successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
            },

            error: function(xhr){
                let errors = xhr.responseJSON.errors;

                $.each(errors, function(key, value){
                    $('#error_' + key).text(value[0]);
                    $('#' + key).addClass('is-invalid');
                });
            },

            complete: function(){
                $('#saveBtn').prop('disabled', false).text('Save');
            }
        });
    });

    // EDIT
    $(document).on('click','.editBtn',function(){
        clearErrors();

        let id = $(this).data('id');

        $.get('stitch/edit/'+id, function(data){
            $('#stitch_id').val(data.id);
            $('#name').val(data.full_name);
            $('#mobile').val(data.phone);
            $('#email').val(data.email);
            $('#city').val(data.city);

            $('#stitchModel').modal('show');
        });
    });

    // DELETE
    $(document).on('click','.deleteBtn',function(){
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this stitch",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url:'stitch/delete/'+id,
                    type:'DELETE',
                    data:{_token:'{{ csrf_token() }}'},

                    success:function(){
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

    // STATUS
    $(document).on('change','.statusToggle',function(){
        let id = $(this).data('id');
        let checkbox = $(this);

        Swal.fire({
            title: 'Change Status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {

            if(result.isConfirmed){

                $.post("{{ route('admin.stitch.status') }}", {
                    _token:'{{ csrf_token() }}',
                    id:id
                }, function(){

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