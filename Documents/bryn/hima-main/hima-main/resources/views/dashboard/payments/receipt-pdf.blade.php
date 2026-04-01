<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #2a2100;
            font-size: 12px;
            margin: 24px;
        }

        .header {
            border-bottom: 2px solid #2a2100;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .brand {
            width: 100%;
        }

        .brand td {
            vertical-align: middle;
        }

        .logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
        }

        .brand-name {
            font-size: 20px;
            font-weight: 700;
        }

        .brand-sub {
            color: #6b5b26;
            margin-top: 4px;
        }

        .status {
            text-align: right;
            font-weight: 700;
            font-size: 13px;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .muted {
            color: #6b5b26;
        }

        table.meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        table.meta td {
            width: 50%;
            border: 1px solid #d8cfaa;
            padding: 12px;
            vertical-align: top;
            background: #fff8df;
        }

        .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b5b26;
            margin-bottom: 6px;
        }

        .value {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px dashed #d8cfaa;
            padding-top: 14px;
        }

        .total {
            font-size: 24px;
            font-weight: 700;
            margin: 4px 0 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="brand">
            <tr>
                <td>
                    @if (!empty($appLogoUrl))
                        <img src="{{ $appLogoUrl }}" alt="Logo" class="logo">
                    @endif
                </td>
                <td>
                    <div class="brand-name">{{ $appName ?? 'Student Portal' }}</div>
                    <div class="brand-sub">Official payment receipt</div>
                </td>
                <td class="status">
                    {{ strtoupper($payment->status) }}
                </td>
            </tr>
        </table>
    </div>

    <h1 class="title">Receipt #{{ $payment->invoice_no }}</h1>
    <div class="muted">Paid on {{ $payment->paid_at?->format('F j, Y') ?? 'Not paid yet' }}</div>

    <table class="meta">
        <tr>
            <td>
                <div class="label">Student</div>
                <p class="value">{{ $payment->student?->name ?? '-' }}</p>
                <div class="muted">{{ $payment->student?->email ?? '-' }}</div>
            </td>
            <td>
                <div class="label">Invoice</div>
                <p class="value">{{ $payment->invoice_no }}</p>
                <div class="muted">Status: {{ ucfirst($payment->status) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Due Date</div>
                <p class="value">{{ $payment->due_date?->format('F j, Y') ?? '-' }}</p>
            </td>
            <td>
                <div class="label">Payment Date</div>
                <p class="value">{{ $payment->paid_at?->format('F j, Y') ?? '-' }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Total Amount</div>
                <p class="value">{{ number_format((float) $payment->amount, 2) }}</p>
            </td>
            <td>
                <div class="label">Paid Amount</div>
                <p class="value">{{ number_format((float) $payment->paid_amount, 2) }}</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        <div class="label">Final Paid Total</div>
        <div class="total">{{ number_format((float) $payment->paid_amount, 2) }}</div>
        <div class="muted" style="margin-top:10px;">Generated on {{ now()->format('F j, Y g:i A') }}</div>
    </div>
</body>
</html>
