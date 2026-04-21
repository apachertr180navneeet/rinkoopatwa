@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card p-4">
            <h5 class="text-center mb-4">View Order</h5>

            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                @csrf
                <!-- USER DETAILS -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>User Name</label>
                        <input type="text" class="form-control" value="{{ $order->user_name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Mobile Number</label>
                        <input type="text" class="form-control" value="{{ $order->mobile }}" readonly>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Email</label>
                        <input type="email" class="form-control" value="{{ $order->email }}" readonly>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Order No.</label>
                        <input type="text" class="form-control" value="{{ $order->order_no }}" readonly>
                    </div>
                </div>

                <!-- STITCH FOR -->
                <h6 class="mt-4">Stitch For</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Name</label>
                        <input type="text" class="form-control" value="{{ $order->user_name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Phone</label>
                        <input type="text" class="form-control" value="{{ $order->mobile }}" readonly>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Height</label>
                        <input type="text" class="form-control" value="{{ $order->height }}" readonly>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label>Weight</label>
                        <input type="text" class="form-control" value="{{ $order->body_weight }}" readonly>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Shoes Size</label>
                        <input type="text" class="form-control" value="{{ $order->shoes_size }}" readonly>
                    </div>
                </div>

                <!-- IMAGES -->
                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <label>Front Photo</label><br>
                        @if (!empty($order->front_photo))
                            <img src="{{ $order->front_photo }}" class="img-fluid rounded border"
                                style="height:150px; object-fit:cover;">
                        @else
                            <p class="text-muted">No Image</p>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label>Side Photo</label><br>
                        @if (!empty($order->side_photo))
                            <img src="{{ $order->side_photo }}" class="img-fluid rounded border"
                                style="height:150px; object-fit:cover;">
                        @else
                            <p class="text-muted">No Image</p>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label>Back Photo</label><br>
                        @if (!empty($order->back_photo))
                            <img src="{{ $order->back_photo }}" class="img-fluid rounded border"
                                style="height:150px; object-fit:cover;">
                        @else
                            <p class="text-muted">No Image</p>
                        @endif
                    </div>
                </div>

                <!-- MEASUREMENTS -->
                <h6>Measurement</h6>

                @php
                    $measurements = json_decode($order->mesurment_json, true);
                @endphp

                <div class="row mb-3">
                    @foreach ($measurements as $key => $measurement)
                        <div class="col-md-6">
                            <label>{{ $key }}</label>
                            <input type="text" class="form-control" value="{{ $measurement }} inch" readonly>
                        </div>
                    @endforeach
                </div>

                <!-- ADDITIONAL -->
                <h6>Additional Requirement</h6>
                @php
                    $additionals = json_decode($order->additional_requirement, true);
                @endphp
                <div class="row mb-3">
                    @foreach ($additionals as $additionalkey => $additional)
                        <div class="col-md-6">
                            <label>{{ ucfirst($additionalkey) }}</label>
                            <input type="text" class="form-control" value="{{ $additional }} Buttons" readonly>
                        </div>
                    @endforeach
                </div>

                @foreach ($categories as $category)
                    @php
                        $categorystitch = DB::table('category_stitch')
                            ->where('order_id', $order->id)
                            ->where('category_id', $category->id)
                            ->first();
                    @endphp

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Category</label>
                            <input type="hidden" name="orderid" value="{{ $order->id }}">
                            <input type="hidden" name="categoryid" value="{{ $category->id }}">
                            <input type="text" class="form-control" value="{{ $category->name }}" readonly>
                        </div>

                        <!-- Stitch Master -->
                        <div class="col-md-4">
                            <label>Assign Stitch Master</label>
                            <select class="form-control assign-master">
                                <option value="">Select Stitch Master</option>
                                @foreach ($masters as $master)
                                    <option value="{{ $master->id }}"
                                        {{ isset($categorystitch) && $categorystitch->stitch_id == $master->id ? 'selected' : '' }}>
                                        {{ $master->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <label>Status</label>
                            <select class="form-control status-dropdown" disabled>
                                <option value="pending"
                                    {{ isset($categorystitch) && $categorystitch->status == 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>

                                <option value="trial-ready"
                                    {{ isset($categorystitch) && $categorystitch->status == 'trial-ready' ? 'selected' : '' }}>
                                    Trial Ready
                                </option>

                                <option value="complete"
                                    {{ isset($categorystitch) && $categorystitch->status == 'complete' ? 'selected' : '' }}>
                                    Complete
                                </option>
                            </select>
                        </div>
                    </div>
                @endforeach

                <!-- FINAL STATUS -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>Order Status</label>
                        <select class="form-control" name="status">
                            <option value="pending">Pending</option>
                            <option value="complete">Complete</option>
                        </select>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="text-end">

                    <button type="submit" id="sendBtn" class="btn btn-primary">Send</button>
                </div>

            </form>
        </div>
    </div>
@endsection


@section('script')
    <script>
        $(document).on('change', '.assign-master', function() {

            let row = $(this).closest('.row');

            let order_id = row.find('input[name="orderid"]').val();
            let category_id = row.find('input[name="categoryid"]').val();
            let master_id = $(this).val();

            if (master_id == '') return;

            $.ajax({
                url: "{{ route('assign.master') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order_id: order_id,
                    category_id: category_id,
                    master_id: master_id
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: response.message || 'Master Assigned Successfully',
                        timer: 1200,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {

                    let msg = 'Something went wrong';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    toastr.error(msg);
                }
            });

        });


        // ✅ CHECK ONLY ON PAGE LOAD
        $(document).ready(function() {

            let total = $('.status-dropdown').length;
            let completeCount = 0;

            $('.status-dropdown').each(function() {
                if ($(this).val() === 'complete') {
                    completeCount++;
                }
            });

            // ✅ ENABLE only when all complete
            if (total > 0 && total === completeCount) {
                $('#sendBtn').prop('disabled', false);
            } else {
                $('#sendBtn').prop('disabled', true);
            }

        });
    </script>
@endsection
