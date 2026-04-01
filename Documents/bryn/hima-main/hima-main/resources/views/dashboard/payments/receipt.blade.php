<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Receipt</title>
    <style>
        :root {
            --ink: #2a2100;
            --muted: #6b5b26;
            --line: rgba(42, 33, 0, .14);
            --paper: #fffdf4;
            --panel: #fff7d1;
            --accent: #2a2100;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 2rem 1rem;
            font-family: "DM Sans", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top, rgba(255, 229, 140, .28), transparent 38%),
                linear-gradient(180deg, #fff8d8 0%, #fff2b0 100%);
        }

        .shell {
            max-width: 860px;
            margin: 0 auto;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .toolbar-copy h1 {
            margin: 0;
            font-size: 1.7rem;
        }

        .toolbar-copy p {
            margin: .35rem 0 0;
            color: var(--muted);
        }

        .toolbar-actions {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
        }

        .btn,
        .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .72rem 1rem;
            border-radius: 999px;
            border: 1px solid var(--line);
            text-decoration: none;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .btn {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff7ce;
        }

        .btn-outline {
            background: rgba(255, 255, 255, .72);
            color: var(--ink);
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: .9rem;
            margin-bottom: 1rem;
        }

        .brand-logo {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, .78);
            object-fit: cover;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            flex: 0 0 auto;
        }

        .brand-meta h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .brand-meta p {
            margin: .2rem 0 0;
            color: var(--muted);
        }

        .receipt {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 18px 48px rgba(57, 44, 6, .12);
        }

        .receipt-head {
            padding: 1.4rem 1.5rem 1.1rem;
            background: linear-gradient(135deg, #2a2100 0%, #5a4700 100%);
            color: #fff6d0;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .receipt-head h2 {
            margin: 0;
            font-size: 1.35rem;
        }

        .receipt-head p {
            margin: .25rem 0 0;
            color: rgba(255, 246, 208, .82);
        }

        .receipt-status {
            padding: .45rem .8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            font-weight: 800;
            align-self: start;
        }

        .receipt-body {
            padding: 1.35rem 1.5rem 1.5rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .9rem;
        }

        .cell {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--panel);
            padding: .95rem 1rem;
        }

        .label {
            margin: 0 0 .35rem;
            color: var(--muted);
            font-size: .86rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .value {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .subvalue {
            margin: .25rem 0 0;
            color: var(--muted);
            font-size: .92rem;
        }

        .receipt-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed var(--line);
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .receipt-total strong {
            display: block;
            font-size: 1.7rem;
            line-height: 1;
        }

        .helper {
            margin-top: 1rem;
            color: var(--muted);
            font-size: .92rem;
        }

        @media (max-width: 680px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .toolbar,
            .helper {
                display: none !important;
            }

            .shell {
                max-width: none;
                margin: 0;
            }

            .receipt {
                border: none;
                border-radius: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    @php
        $action = request()->query('action');
    @endphp

    <div class="shell">
        <div class="toolbar">
            <div class="toolbar-copy">
                <h1>Payment Receipt</h1>
                <p>Use Print for paper copies or Save as PDF from the print dialog.</p>
            </div>
            <div class="toolbar-actions">
                <a class="btn-outline" href="{{ route('dashboard.payments.index', ['lang' => app()->getLocale()]) }}">Back</a>
                <button class="btn-outline" type="button" onclick="window.print()">Print</button>
                <a class="btn" href="{{ route('dashboard.payments.receipt', ['payment' => $payment, 'lang' => app()->getLocale(), 'action' => 'pdf']) }}">Save PDF</a>
            </div>
        </div>

        <section class="receipt" id="receipt">
            <div class="receipt-head">
                <div>
                    <h2>Receipt #{{ $payment->invoice_no }}</h2>
                    <p>Paid on {{ $payment->paid_at?->format('F j, Y') ?? 'Not paid yet' }}</p>
                </div>
                <div class="receipt-status">{{ strtoupper($payment->status) }}</div>
            </div>

            <div class="receipt-body">
                <div class="brand-row">
                    @if (!empty($appLogoUrl))
                        <img src="{{ $appLogoUrl }}" alt="{{ $appName ?? 'App' }} logo" class="brand-logo">
                    @else
                        <div class="brand-logo">{{ strtoupper(substr($appName ?? 'SP', 0, 1)) }}</div>
                    @endif
                    <div class="brand-meta">
                        <h2>{{ $appName ?? 'Student Portal' }}</h2>
                        <p>Official payment receipt</p>
                    </div>
                </div>

                <div class="grid">
                    <div class="cell">
                        <p class="label">Student</p>
                        <p class="value">{{ $payment->student?->name ?? '-' }}</p>
                        <p class="subvalue">{{ $payment->student?->email ?? '-' }}</p>
                    </div>
                    <div class="cell">
                        <p class="label">Invoice</p>
                        <p class="value">{{ $payment->invoice_no }}</p>
                        <p class="subvalue">Status: {{ ucfirst($payment->status) }}</p>
                    </div>
                    <div class="cell">
                        <p class="label">Due Date</p>
                        <p class="value">{{ $payment->due_date?->format('F j, Y') ?? '-' }}</p>
                    </div>
                    <div class="cell">
                        <p class="label">Payment Date</p>
                        <p class="value">{{ $payment->paid_at?->format('F j, Y') ?? '-' }}</p>
                    </div>
                    <div class="cell">
                        <p class="label">Total Amount</p>
                        <p class="value">{{ number_format((float) $payment->amount, 2) }}</p>
                    </div>
                    <div class="cell">
                        <p class="label">Paid Amount</p>
                        <p class="value">{{ number_format((float) $payment->paid_amount, 2) }}</p>
                    </div>
                </div>

                <div class="receipt-total">
                    <div>
                        <p class="label">Final Paid Total</p>
                        <strong>{{ number_format((float) $payment->paid_amount, 2) }}</strong>
                    </div>
                    <div>
                        <p class="label">Generated</p>
                        <p class="value">{{ now()->format('F j, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <p class="helper">Print opens the browser print dialog. Save PDF downloads a generated PDF file.</p>
    </div>

    @if ($action === 'print')
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif
</body>
</html>
