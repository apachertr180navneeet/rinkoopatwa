@extends('admin.layouts.app') 
@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <h5 class="py-2 mb-2">
        <span class="text-primary fw-light">Change Password</span>
    </h5>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.update.password') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="mb-3">
                                <label for="oldPasswordInput" class="form-label">Old Password</label>
                                <input name="old_password" type="password" class="form-control @error('old_password') is-invalid @enderror" id="oldPasswordInput" placeholder="Old Password" required>
                                @error('old_password')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="newPasswordInput" class="form-label">New Password</label>
                                <input name="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" id="newPasswordInput" placeholder="New Password" required>
                                @error('new_password')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="confirmNewPasswordInput" class="form-label">Confirm New Password</label>
                                <input name="new_password_confirmation" type="password" class="form-control" id="confirmNewPasswordInput" placeholder="Confirm New Password" required>
                            </div>
                        </div>
                        <div class="pt-4">
                            <div class="col-md-12 submit-btn">
                                <button type="submit" class="btn btn-primary">Save</button> 
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        
</div>
@endsection