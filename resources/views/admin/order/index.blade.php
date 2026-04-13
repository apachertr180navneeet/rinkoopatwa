@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card p-3 mb-4">
        <div class="d-flex justify-content-between mb-3">
            <h5 class="mb-0">Orders Management</h5>
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
@endsection

@section('script')
<script>
    $(function () {

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
    });
</script>
@endsection
