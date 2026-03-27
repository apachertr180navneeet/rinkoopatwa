@extends('admin.layouts.app')

@section('style')
<style>
    /* Card Styling */
    .dashboard-card {
        border: none;
        border-radius: 12px;
        transition: 0.3s;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }

    .card-title {
        font-size: 16px;
        font-weight: 600;
    }

    .card-stats p {
        margin-bottom: 5px;
        font-size: 13px;
    }

    /* Badges */
    .badge-custom {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
    }

    .bg-active { background: #28c76f; color: #fff; }
    .bg-inactive { background: #ea5455; color: #fff; }
    .bg-pending { background: #ff9f43; color: #fff; }
    .bg-complete { background: #7367f0; color: #fff; }

    /* Table */
    .table thead {
        background: linear-gradient(45deg, #7367f0, #9e95f5);
        color: #fff;
        font-size: 13px;
    }

    .table td, .table th {
        padding: 8px;
        font-size: 13px;
    }

    /* Mobile Optimization */
    @media (max-width: 768px) {

        .card-title {
            font-size: 14px;
        }

        .card-stats p {
            font-size: 12px;
        }

        .table td, .table th {
            font-size: 12px;
            padding: 6px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        /* Stack cards better */
        .dashboard-card {
            margin-bottom: 10px;
        }
    }
</style>
@endsection  


@section('content')

<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row g-3">

        <!-- Measurement Categories -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
            <div class="card dashboard-card shadow-sm">
                <div class="card-body text-center p-3">
                    <h5 class="card-title mb-2">Measurement Categories</h5>

                    <div class="card-stats">
                        <p>Total: <strong>10</strong></p>
                        <p>
                            Active: 
                            <span class="badge badge-custom bg-active">10</span>
                        </p>
                        <p>
                            In-Active: 
                            <span class="badge badge-custom bg-inactive">0</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
            <div class="card dashboard-card shadow-sm">
                <div class="card-body text-center p-3">
                    <h5 class="card-title mb-2">Orders</h5>

                    <div class="card-stats">
                        <p>Total: <strong>15</strong></p>
                        <p>
                            Pending: 
                            <span class="badge badge-custom bg-pending">10</span>
                        </p>
                        <p>
                            Complete: 
                            <span class="badge badge-custom bg-complete">5</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-12">
            <div class="card dashboard-card shadow-sm">
                <div class="card-body p-2 p-md-3">

                    <h5 class="card-title text-center mb-3">
                        Monthly Order Report
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered text-center align-middle mb-0">

                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Total</th>
                                    <th>Trial</th>
                                    <th>Approved</th>
                                    <th>Pending</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td><strong>Feb</strong></td>
                                    <td>100</td>
                                    <td><span class="badge bg-complete">20</span></td>
                                    <td><span class="badge bg-active">20</span></td>
                                    <td><span class="badge bg-pending">60</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Jan</strong></td>
                                    <td>80</td>
                                    <td><span class="badge bg-complete">20</span></td>
                                    <td><span class="badge bg-active">30</span></td>
                                    <td><span class="badge bg-pending">30</span></td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection


@section('script')
@endsection