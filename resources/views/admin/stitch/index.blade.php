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

/* PASSWORD ICON */
.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #888;
}
.toggle-password:hover {
    color: #333;
}
</style>
@endsection

@section('content')

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="container-fluid py-4">

<div class="card p-3">

    <div class="d-flex justify-content-between mb-3">
        <h5>Stitches Management</h5>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
            Add Stitches
        </button>
    </div>

    <div class="d-flex gap-2 mb-3">
        <input type="text" id="userName" class="form-control" placeholder="User Name">
        <input type="text" id="globalSearch" class="form-control" placeholder="Search">
    </div>

    <table class="table table-bordered" id="userTable">
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
<div class="modal fade" id="userModal">
    <div class="modal-dialog">
        <form id="userForm">
            @csrf
            <input type="hidden" id="user_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5>Stitches Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="text" name="name" id="name" class="form-control mb-2" placeholder="Name">
                    <small class="text-danger error" id="error_name"></small>

                    <input type="text" name="mobile" id="mobile" class="form-control mb-2" placeholder="Mobile">
                    <small class="text-danger error" id="error_mobile"></small>

                    <input type="email" name="email" id="email" class="form-control mb-2" placeholder="Email">
                    <small class="text-danger error" id="error_email"></small>

                    <!-- PASSWORD FIELD -->
                    <div class="position-relative mb-2">
                        <input type="password" name="password" 
                               class="form-control password-field" 
                               placeholder="Password">

                        <span class="toggle-password">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>
                    <small class="text-danger error" id="error_password"></small>

                    <input type="text" name="city" id="city" class="form-control mb-2" placeholder="Location">
                    <small class="text-danger error" id="error_city"></small>

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

    let table = $('#userTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.users.getall') }}",
            data: function (d) {
                d.name = $('#userName').val();
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

    $('#userName, #globalSearch').keyup(function(){
        table.draw();
    });

    function clearErrors(){
        $('.error').text('');
        $('.form-control').removeClass('is-invalid');
    }

    // SHOW / HIDE PASSWORD
    $(document).on('click', '.toggle-password', function () {

        let input = $(this).siblings('.password-field');
        let icon = $(this).find('i');

        if (input.attr("type") === "password") {
            input.attr("type", "text");
            icon.removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            input.attr("type", "password");
            icon.removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });

    // RESET PASSWORD WHEN MODAL OPEN
    $('#userModal').on('shown.bs.modal', function () {
        $('.password-field').val('').attr('type','password');
        $('.toggle-password i').removeClass('fa-eye-slash').addClass('fa-eye');
    });

    // SAVE
    $('#userForm').submit(function(e){
        e.preventDefault();

        clearErrors();

        let id = $('#user_id').val();
        let url = id ? 'users/update/'+id : "{{ route('admin.users.store') }}";

        $('#saveBtn').prop('disabled', true).text('Saving...');

        $.ajax({
            url: url,
            type: "POST",
            data: $(this).serialize(),

            success: function(res){
                $('#userModal').modal('hide');
                table.draw();
                $('#userForm')[0].reset();

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'User saved successfully',
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

        $.get('users/edit/'+id, function(data){
            $('#user_id').val(data.id);
            $('#name').val(data.full_name);
            $('#mobile').val(data.phone);
            $('#email').val(data.email);
            $('#city').val(data.city);

            $('.password-field').val('');

            $('#userModal').modal('show');
        });
    });

    // DELETE
    $(document).on('click','.deleteBtn',function(){
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this user",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url:'users/delete/'+id,
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

                $.post("{{ route('admin.users.status') }}", {
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