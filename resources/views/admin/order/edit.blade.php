@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card p-4">
            <h5 class="text-center mb-4">View Order</h5>

            <form>

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
                        @if(!empty($order->front_photo))
                            <img src="{{ $order->front_photo }}" 
                                class="img-fluid rounded border" 
                                style="height:150px; object-fit:cover;">
                        @else
                            <p class="text-muted">No Image</p>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label>Side Photo</label><br>
                        @if(!empty($order->side_photo))
                            <img src="{{ $order->side_photo }}" 
                                class="img-fluid rounded border" 
                                style="height:150px; object-fit:cover;">
                        @else
                            <p class="text-muted">No Image</p>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label>Back Photo</label><br>
                        @if(!empty($order->back_photo))
                            <img src="{{ $order->back_photo }}" 
                                class="img-fluid rounded border" 
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
                    @foreach($measurements as $key => $measurement)
                        <div class="col-md-6">
                            <label>{{ $key }}</label>
                            <input type="text" class="form-control" 
                                value="{{ $measurement }} inch" readonly>
                        </div>
                    @endforeach
                </div>

                <!-- ADDITIONAL -->
                <h6>Additional Requirement</h6>
                @php
                    $additionals = json_decode($order->additional_requirement, true);
                @endphp
                <div class="row mb-3">
                    @foreach($additionals as $additionalkey => $additional)
                        <div class="col-md-6">
                            <label>{{ ucfirst($additionalkey) }}</label>
                            <input type="text" class="form-control" value="{{ $additional }} Buttons" readonly>
                        </div>
                    @endforeach
                </div>

                @foreach ( $categories as $category )
                    <!-- CATEGORY -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Category</label>
                            <input type="hidden" name="categoryid" value="{{ $category->id }}">
                            <input type="text" class="form-control" value="{{ $category->name }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Assign Stitch Master</label>
                            <select class="form-control">
                                <option>Select Stitch Master</option>
                                @foreach($masters as $key => $master)
                                    <option value="{{ $master->id }}">{{ $master->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Status</label>
                            <select class="form-control">
                                <option value="pending">Pending</option>
                                <option value="trial-ready">Trial Ready</option>
                                <option value="complete">Complete</option>
                            </select>
                        </div>
                    </div>
                @endforeach

                <!-- FINAL STATUS -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>Order Status</label>
                        <select class="form-control">
                            <option value="pending">Pending</option>
                            <option value="complete">Complete</option>
                        </select>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="text-end">
                    <button type="button" class="btn btn-primary">Notification Send</button>

                    <button type="button" class="btn btn-primary">Send</button>
                </div>

            </form>
        </div>
    </div>
@endsection
