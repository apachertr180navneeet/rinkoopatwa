<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Detail</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            color: #333;
        }

        .container {
            border: 1px solid #ccc;
            padding: 15px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            color: #fff;
            font-size: 10px;
        }

        .yellow { background: #f4b400; }
        .green { background: #4caf50; }

        .section {
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 10px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 5px;
        }

        .info-table td {
            padding: 4px;
        }

        .measure-table {
            width: 100%;
            border-collapse: collapse;
        }

        .measure-table td {
            padding: 6px;
            border-bottom: 1px solid #eee;
        }

        .value-box {
            border: 1px solid #ccc;
            padding: 4px;
            text-align: center;
            width: 80px;
        }

        .image-table {
            width: 100%;
            margin-top: 10px;
        }

        .image-table td {
            text-align: center;
        }

        img {
            width: 90px;
            height: 90px;
            border: 1px solid #ccc;
        }

    </style>
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td>
                <strong>{{ $order->user_name }}</strong><br>
                {{ $order->mobile }}<br>
                {{ $order->email }}
            </td>
            <td align="right">
                {{ date('d M Y | h:i A', strtotime($order->created_at)) }}<br><br>
                @php
                    $statusClass = $order->status == 'pending' ? 'yellow' : 'green';
                @endphp

                <span class="badge {{ $statusClass }}">
                    {{ ucfirst($order->status) }}
                </span>
            </td>
        </tr>
    </table>

    <!-- CATEGORY -->
    <div>
        @foreach(explode(',', $order->category_id) as $cat)
            <span class="badge yellow">Item {{ $cat }}</span>
        @endforeach
    </div>

    <!-- STITCH FOR -->
    <div class="section">
        <div class="section-title">Stitch Details</div>

        <table class="info-table">
            <tr>
                <td><strong>Name:</strong> {{ $order->stitch_for_name }}</td>
                <td><strong>Phone:</strong> {{ $order->phone_no }}</td>
            </tr>
            <tr>
                <td><strong>Height:</strong> {{ $order->height }}</td>
                <td><strong>Weight:</strong> {{ $order->body_weight }} KG</td>
            </tr>
            <tr>
                <td><strong>Shoe Size:</strong> {{ $order->shoes_size }}</td>
                <td></td>
            </tr>
        </table>

        <!-- IMAGES -->
        <table class="image-table">
            <tr>
                <td>
                    <div><strong>Front</strong></div>
                    @if($order->front_photo)
                        <img src="{{ $order->front_photo }}">
                    @endif
                </td>
                <td>
                    <div><strong>Side</strong></div>
                    @if($order->side_photo)
                        <img src="{{ $order->side_photo }}">
                    @endif
                </td>
                <td>
                    <div><strong>Back</strong></div>
                    @if($order->back_photo)
                        <img src="{{ $order->back_photo }}">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- MEASUREMENTS -->
    <div class="section">
        <div class="section-title">Measurements</div>

        <table class="measure-table">
            @foreach($order->measurements as $key => $value)
                <tr>
                    <td>{{ $key }}</td>
                    <td align="right">
                        <div class="value-box">{{ $value }} inch</div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <!-- ADDITIONAL -->
    <div class="section">
        <div class="section-title">Additional Requirements</div>

        <table class="measure-table">
            @foreach($order->additional as $key => $value)
                <tr>
                    <td>{{ ucfirst($key) }}</td>
                    <td align="right">
                        <div class="value-box">{{ $value }}</div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

</div>

</body>
</html>