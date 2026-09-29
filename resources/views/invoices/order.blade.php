<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $order->id }} - Belanjaja</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background-color: #ffffff;
            padding: 30px;
            line-height: 1.5;
        }

        .header-table, .info-table, .items-table, .total-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .brand-title {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        .invoice-title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .invoice-number {
            text-align: right;
            font-size: 11px;
            color: #4b5563;
            margin-top: 3px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 6px;
        }

        .status-paid {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .status-shipped {
            background-color: #e0e7ff;
            color: #4338ca;
            border: 1px solid #a5b4fc;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fcd34d;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .divider {
            height: 2px;
            background-color: #2563eb;
            margin: 20px 0;
        }

        .info-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 20px;
        }

        .info-title {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .info-content {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.6;
        }

        .info-content strong {
            color: #111827;
        }

        .items-table {
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #2563eb;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 9px 10px;
            text-align: left;
        }

        .items-table th.text-right, .items-table td.text-right {
            text-align: right;
        }

        .items-table th.text-center, .items-table td.text-center {
            text-align: center;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
            color: #374151;
            vertical-align: middle;
        }

        .items-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .product-name {
            font-weight: bold;
            color: #111827;
            font-size: 11px;
        }

        .product-meta {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        .total-section {
            width: 100%;
            margin-top: 10px;
        }

        .total-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .total-table td {
            padding: 6px 10px;
            font-size: 11px;
        }

        .total-table .label {
            color: #4b5563;
            text-align: right;
        }

        .total-table .value {
            text-align: right;
            font-weight: 600;
            color: #111827;
        }

        .grand-total-row td {
            border-top: 2px solid #2563eb;
            padding-top: 8px;
            font-size: 13px !important;
            font-weight: bold;
        }

        .grand-total-row .label {
            color: #2563eb;
        }

        .grand-total-row .value {
            color: #2563eb;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }

        .footer p {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="brand-title">Belanjaja</div>
                <div class="brand-subtitle">Platform E-Commerce Terpercaya & Terlengkap</div>
                <div style="margin-top: 8px; font-size: 10px; color: #6b7280;">
                    Situs: https://belanjaja.com &bull; Bantuan: support@belanjaja.com
                </div>
            </td>
            <td style="width: 45%; text-align: right;">
                <div class="invoice-title">INVOICE RESMI</div>
                <div class="invoice-number">INV/{{ $order->created_at->format('Ymd') }}/{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div style="margin-top: 4px; font-size: 11px; color: #4b5563;">
                    Tanggal Pesanan: <strong>{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</strong>
                </div>
                <div>
                    @php
                        $statusLabels = [
                            'paid'      => 'Lunas',
                            'completed' => 'Selesai',
                            'shipped'   => 'Sedang Dikirim',
                            'pending'   => 'Menunggu Pembayaran',
                            'cancelled' => 'Dibatalkan',
                        ];
                        $statusClass = 'status-' . ($order->status ?? 'pending');
                        $statusText = $statusLabels[$order->status] ?? ucfirst($order->status);
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Info Section (Buyer & Shipping) -->
    <table class="info-table" style="margin-bottom: 15px;">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <div class="info-box">
                    <div class="info-title">Informasi Pembeli</div>
                    <div class="info-content">
                        <strong>{{ $order->user->name ?? 'Pembeli' }}</strong><br>
                        Email: {{ $order->user->email ?? '-' }}<br>
                        Telepon: {{ $order->shipping_address['phone'] ?? '-' }}<br>
                        Metode Pembayaran: <strong>{{ strtoupper($order->payment_type ?? 'Midtrans') }}</strong>
                    </div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top;">
                <div class="info-box">
                    <div class="info-title">Tujuan Pengiriman</div>
                    <div class="info-content">
                        <strong>{{ $order->shipping_address['name'] ?? $order->user->name ?? '-' }}</strong><br>
                        {{ $order->shipping_address['detail'] ?? '-' }}<br>
                        {{ $order->shipping_address['subdistrict'] ?? '' }}{{ isset($order->shipping_address['city']) ? ', ' . $order->shipping_address['city'] : '' }}<br>
                        {{ $order->shipping_address['province'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}<br>
                        @if(!empty($order->tracking_number))
                            No. Resi Pengiriman: <strong style="font-family: monospace;">{{ $order->tracking_number }}</strong>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Products Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 6%;">No</th>
                <th style="width: 44%;">Nama Produk</th>
                <th class="text-right" style="width: 18%;">Harga Satuan</th>
                <th class="text-center" style="width: 12%;">Jumlah</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="product-name">{{ $item->product->name ?? 'Produk' }}</div>
                        <div class="product-meta">
                            @if(!empty($item->product->category->name))
                                Kategori: {{ $item->product->category->name }}
                            @endif
                            @if(!empty($item->product->location))
                                &bull; Dikirim dari: {{ $item->product->location }}
                            @endif
                        </div>
                    </td>
                    <td class="text-right">Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right" style="font-weight: 600;">
                        Rp {{ number_format($item->subtotal ?: ($item->price_snapshot * $item->quantity), 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="total-section">
        <table class="total-table">
            @php
                $shippingCost = (float) ($order->shipping_cost ?? 0);
                $itemsSubtotal = (float) $order->total_price - $shippingCost;
                if ($itemsSubtotal <= 0) {
                    $itemsSubtotal = (float) $order->total_price;
                }
            @endphp
            <tr>
                <td class="label">Subtotal Produk:</td>
                <td class="value">Rp {{ number_format($itemsSubtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Biaya Pengiriman ({{ ucfirst($order->shipping_service ?? 'Reguler') }}):</td>
                <td class="value" style="{{ $shippingCost > 0 ? '' : 'color: #16a34a;' }}">
                    {{ $shippingCost > 0 ? 'Rp ' . number_format($shippingCost, 0, ',', '.') : 'Gratis Ongkir' }}
                </td>
            </tr>
            <tr class="grand-total-row">
                <td class="label">Total Pembayaran:</td>
                <td class="value">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Footer Section -->
    <div class="footer">
        <p><strong>Terima kasih telah berbelanja di Belanjaja!</strong></p>
        <p>Invoice ini sah dan diterbitkan secara resmi oleh sistem komputer Belanjaja.</p>
        <p>Dicetak otomatis pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

</body>
</html>
