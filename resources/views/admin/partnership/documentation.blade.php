@extends('adminmodule::layouts.master')

@section('title', translate('API Documentation'))

@push('css_or_js')
    <style>
        .api-doc { line-height: 1.8; }
        .api-doc h4 { color: #0c67a3; margin-top: 1.5rem; }
        .api-doc .endpoint {
            background: #f8f9fa;
            border-left: 4px solid #0c67a3;
            padding: 10px 16px;
            margin: 10px 0;
            border-radius: 4px;
            font-family: monospace;
        }
        .api-doc .method-get { color: #28a745; font-weight: bold; }
        .api-doc .method-post { color: #007bff; font-weight: bold; }
        .api-doc .method-put { color: #ffc107; font-weight: bold; }
        .api-doc .method-delete { color: #dc3545; font-weight: bold; }
        .api-doc pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
        }
        .api-doc .card { margin-bottom: 1.5rem; }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h2 class="fs-22 text-capitalize">{{ translate('API Documentation') }}</h2>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-outline-primary">
                        <i class="bi bi-printer"></i> {{ translate('Print / Export PDF') }}
                    </button>
                    <a href="{{ route('admin.partnership.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> {{ translate('Back') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-10">
                    <div class="api-doc">

                        <div class="card">
                            <div class="card-body">
                                <h4>1. {{ translate('Authentication') }}</h4>
                                <p>{{ translate('All API requests must include the following headers:') }}</p>
                                <div class="endpoint">
                                    <span class="method-post">X-API-Key:</span> your_api_key<br>
                                    <span class="method-post">X-Secret-Key:</span> your_secret_key<br>
                                    <span class="method-post">Content-Type:</span> application/json<br>
                                    <span class="method-post">Accept:</span> application/json
                                </div>
                                <p>{{ translate('You can get your API key and secret key from the partner details page.') }}</p>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>2. {{ translate('Base URL') }}</h4>
                                <div class="endpoint">{{ $baseUrl }}/api/partner</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>3. {{ translate('Create Order') }}</h4>
                                <p>{{ translate('Create a new delivery or ride order.') }}</p>
                                <div class="endpoint">
                                    <span class="method-post">POST</span> /api/partner/orders
                                </div>
                                <h6>{{ translate('Request Body:') }}</h6>
<pre>{
    "type": "parcel",
    "pickup_address": "123 Main St, Dar es Salaam",
    "pickup_lat": -6.8235,
    "pickup_lng": 39.2695,
    "destination_address": "456 Market Rd, Dar es Salaam",
    "destination_lat": -6.8150,
    "destination_lng": 39.2750,
    "receiver_name": "John Doe",
    "receiver_phone": "+255712345678",
    "parcel_weight": 2.5,
    "payment_method": "cash_on_delivery"
}</pre>
                                <h6>{{ translate('Response (201):') }}</h6>
<pre>{
    "success": true,
    "order_id": "ORD-20260814-001",
    "status": "pending",
    "estimated_fare": 15000,
    "currency": "TZS",
    "message": "Order created successfully"
}</pre>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>4. {{ translate('View Order') }}</h4>
                                <p>{{ translate('Get details of a specific order.') }}</p>
                                <div class="endpoint">
                                    <span class="method-get">GET</span> /api/partner/orders/{order_id}
                                </div>
                                <h6>{{ translate('Response (200):') }}</h6>
<pre>{
    "success": true,
    "order": {
        "id": "ORD-20260814-001",
        "type": "parcel",
        "status": "ongoing",
        "pickup_address": "123 Main St, Dar es Salaam",
        "destination_address": "456 Market Rd, Dar es Salaam",
        "driver": {
            "name": "Driver Name",
            "phone": "+255700000000",
            "vehicle": "Toyota Vitz",
            "plate": "T123 ABC"
        },
        "fare": 15000,
        "currency": "TZS",
        "created_at": "2026-08-14 10:30:00"
    }
}</pre>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>5. {{ translate('List Orders') }}</h4>
                                <p>{{ translate('Get a list of all orders for this partner.') }}</p>
                                <div class="endpoint">
                                    <span class="method-get">GET</span> /api/partner/orders?status=pending&page=1
                                </div>
                                <h6>{{ translate('Query Parameters:') }}</h6>
                                <ul>
                                    <li><code>status</code> - pending, accepted, ongoing, completed, cancelled</li>
                                    <li><code>page</code> - page number (default: 1)</li>
                                    <li><code>per_page</code> - items per page (default: 20)</li>
                                </ul>
                                <h6>{{ translate('Response (200):') }}</h6>
<pre>{
    "success": true,
    "data": [
        { "id": "ORD-001", "status": "pending", ... },
        { "id": "ORD-002", "status": "completed", ... }
    ],
    "total": 50,
    "current_page": 1,
    "last_page": 3
}</pre>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>6. {{ translate('Cancel Order') }}</h4>
                                <p>{{ translate('Cancel an existing order.') }}</p>
                                <div class="endpoint">
                                    <span class="method-delete">DELETE</span> /api/partner/orders/{order_id}
                                </div>
                                <h6>{{ translate('Response (200):') }}</h6>
<pre>{
    "success": true,
    "message": "Order cancelled successfully"
}</pre>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>7. {{ translate('Track Order') }}</h4>
                                <p>{{ translate('Get real-time tracking of an ongoing order.') }}</p>
                                <div class="endpoint">
                                    <span class="method-get">GET</span> /api/partner/orders/{order_id}/track
                                </div>
                                <h6>{{ translate('Response (200):') }}</h6>
<pre>{
    "success": true,
    "order_id": "ORD-20260814-001",
    "status": "ongoing",
    "driver_location": {
        "lat": -6.8200,
        "lng": 39.2710
    },
    "eta_minutes": 15,
    "distance_km": 3.2
}</pre>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>8. {{ translate('Wallet Balance') }}</h4>
                                <p>{{ translate('Get the partner wallet balance.') }}</p>
                                <div class="endpoint">
                                    <span class="method-get">GET</span> /api/partner/wallet
                                </div>
                                <h6>{{ translate('Response (200):') }}</h6>
<pre>{
    "success": true,
    "balance": 50000,
    "currency": "TZS",
    "pending": 5000
}</pre>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>9. {{ translate('Transaction History') }}</h4>
                                <p>{{ translate('Get transaction history for the partner wallet.') }}</p>
                                <div class="endpoint">
                                    <span class="method-get">GET</span> /api/partner/wallet/transactions?page=1
                                </div>
                                <h6>{{ translate('Response (200):') }}</h6>
<pre>{
    "success": true,
    "data": [
        {
            "id": "TXN-001",
            "type": "debit",
            "amount": 15000,
            "description": "Order ORD-001 fare",
            "created_at": "2026-08-14 10:35:00"
        }
    ],
    "total": 20,
    "current_page": 1
}</pre>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>10. {{ translate('Webhook Events') }}</h4>
                                <p>{{ translate('We send the following events to your webhook URL:') }}</p>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('Event') }}</th>
                                            <th>{{ translate('Description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>order.created</code></td>
                                            <td>{{ translate('When a new order is created') }}</td>
                                        </tr>
                                        <tr>
                                            <td><code>order.accepted</code></td>
                                            <td>{{ translate('When a driver accepts the order') }}</td>
                                        </tr>
                                        <tr>
                                            <td><code>order.ongoing</code></td>
                                            <td>{{ translate('When the driver starts the trip') }}</td>
                                        </tr>
                                        <tr>
                                            <td><code>order.completed</code></td>
                                            <td>{{ translate('When the order is completed') }}</td>
                                        </tr>
                                        <tr>
                                            <td><code>order.cancelled</code></td>
                                            <td>{{ translate('When the order is cancelled') }}</td>
                                        </tr>
                                        <tr>
                                            <td><code>wallet.updated</code></td>
                                            <td>{{ translate('When wallet balance changes') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <h6>{{ translate('Webhook Payload Example:') }}</h6>
<pre>{
    "event": "order.completed",
    "order_id": "ORD-20260814-001",
    "status": "completed",
    "fare": 15000,
    "currency": "TZS",
    "timestamp": "2026-08-14T11:00:00Z"
}</pre>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>11. {{ translate('Error Responses') }}</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('Code') }}</th>
                                            <th>{{ translate('Description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>401</td><td>{{ translate('Invalid or missing API key') }}</td></tr>
                                        <tr><td>403</td><td>{{ translate('Partner suspended or permission denied') }}</td></tr>
                                        <tr><td>404</td><td>{{ translate('Order not found') }}</td></tr>
                                        <tr><td>422</td><td>{{ translate('Validation error') }}</td></tr>
                                        <tr><td>500</td><td>{{ translate('Server error') }}</td></tr>
                                    </tbody>
                                </table>
                                <h6>{{ translate('Error Format:') }}</h6>
<pre>{
    "success": false,
    "error": "invalid_api_key",
    "message": "The API key provided is invalid or has been revoked."
}</pre>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
