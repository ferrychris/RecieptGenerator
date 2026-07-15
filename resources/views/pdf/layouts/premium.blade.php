@php
    use App\Services\FontStacks;
    $displayFont = FontStacks::get($theme['font_display']);
    $bodyFont = FontStacks::get($theme['font_body']);
    $pageBg = $theme['page_bg'] ?? '#f6ecd9';
    $gold = $theme['rule'];
    $initial = mb_strtoupper(mb_substr($business['name'], 0, 1));
@endphp
<!DOCTYPE html>
<html lang="{{ $meta['locale'] ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice['number'] }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            position: relative;
            overflow: hidden;
            background: {{ $pageBg }};
            font-family: {{ $bodyFont }};
            color: {{ $theme['text'] }};
            font-size: 11px;
            line-height: 1.6;
        }
        .corner {
            position: fixed;
            width: 560px;
            height: 560px;
            border-radius: 50%;
            background: {{ $theme['accent'] }};
            border: 7px solid {{ $gold }};
            z-index: 0;
        }
        .corner-top { top: -270px; right: -210px; }
        .corner-bottom { bottom: -270px; left: -210px; }

        .page { position: relative; z-index: 1; padding: 56px 64px; min-height: 297mm; }

        .logo-outer {
            width: 68px;
            height: 68px;
            background: {{ $gold }};
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-inner {
            width: 58px;
            height: 58px;
            background: {{ $pageBg }};
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .logo-inner img { width: 100%; height: 100%; object-fit: cover; }
        .logo-inner .initial {
            font-family: {{ $displayFont }};
            font-size: 26px;
            color: {{ $theme['accent'] }};
        }

        .doc-title {
            font-family: {{ $displayFont }};
            text-transform: uppercase;
            font-size: 34px;
            color: {{ $theme['text'] }};
            margin: 24px 0 20px 0;
            letter-spacing: 0.5px;
        }

        .meta-row { display: flex; gap: 56px; }
        .meta-label { font-size: 10px; color: {{ $theme['muted'] }}; }
        .meta-value { font-weight: 700; margin-top: 2px; }

        .gold-rule { border-top: 2px solid {{ $gold }}; margin: 24px 0; }

        .two-col { display: flex; justify-content: space-between; gap: 40px; }
        .two-col h3 {
            font-size: 13px;
            margin: 0 0 10px 0;
        }
        table.kv { border-collapse: collapse; }
        table.kv td { padding: 2px 0; vertical-align: top; }
        table.kv td.k { color: {{ $theme['text'] }}; font-weight: 700; padding-right: 8px; white-space: nowrap; }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 32px;
            border: 1.5px solid {{ $gold }};
        }
        table.items-table th {
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            padding: 10px 16px;
            border-bottom: 1.5px solid {{ $gold }};
        }
        table.items-table th.num, table.items-table td.num { text-align: right; }
        table.items-table td {
            padding: 10px 16px;
            border-top: 1px solid rgba(0,0,0,0.06);
        }
        table.items-table tr { break-inside: avoid; }

        .footer-cols { margin-top: 32px; }
        .footer-cols p { margin: 0; }
        .footer-cols .strong { font-weight: 700; }

        .thankyou-row { margin-top: 36px; align-items: flex-end; }
        .thankyou {
            font-family: {{ $displayFont }};
            font-size: 16px;
            line-height: 1.3;
            max-width: 220px;
        }
        .signature-block { text-align: right; }
        .sig-line { border-top: 1.5px solid {{ $gold }}; width: 220px; margin-left: auto; margin-bottom: 6px; }
        .muted { color: {{ $theme['muted'] }}; }
        .small { font-size: 10px; }

        .contact-bar {
            margin-top: 48px;
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
            font-size: 10px;
            color: {{ $theme['text'] }};
        }
        .contact-bar .item { display: flex; align-items: center; gap: 6px; }
        .contact-bar .dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: {{ $theme['accent'] }};
            flex-shrink: 0;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="corner corner-top"></div>
    <div class="corner corner-bottom"></div>

    <div class="page">
        <div class="logo-outer">
            <div class="logo-inner">
                @if(($theme['logo_mode'] ?? 'image') === 'image' && $business['logo_url'])
                    <img src="{{ $business['logo_url'] }}" alt="{{ $business['name'] }}">
                @else
                    <span class="initial">{{ $initial }}</span>
                @endif
            </div>
        </div>

        <h1 class="doc-title">{{ $meta['doc_type'] === 'receipt' ? 'Payment Receipt' : 'Invoice' }}</h1>

        <div class="meta-row">
            <div>
                <div class="meta-label">Receipt No:</div>
                <div class="meta-value">{{ $invoice['number'] }}</div>
            </div>
            <div>
                <div class="meta-label">Date:</div>
                <div class="meta-value">{{ $invoice['issue_date'] }}</div>
            </div>
            <div>
                <div class="meta-label">Created:</div>
                <div class="meta-value">{{ $invoice['created_at'] }}</div>
            </div>
            @if($invoice['first_payment_date'])
                <div>
                    <div class="meta-label">1st Payment:</div>
                    <div class="meta-value">{{ $invoice['first_payment_date'] }}</div>
                </div>
            @endif
        </div>

        <div class="gold-rule"></div>

        <div class="two-col">
            <div>
                <h3>Billed To:</h3>
                <table class="kv">
                    <tr><td class="k">Name:</td><td>{{ $customer['name'] }}</td></tr>
                    @if($customer['phone'])
                        <tr><td class="k">Contact:</td><td>{{ $customer['phone'] }}</td></tr>
                    @endif
                    @if($customer['email'])
                        <tr><td class="k">Email:</td><td>{{ $customer['email'] }}</td></tr>
                    @endif
                    @if(count($customer['address_lines']))
                        <tr><td class="k">Address:</td><td>{{ implode(', ', $customer['address_lines']) }}</td></tr>
                    @endif
                </table>
            </div>
            <div>
                <h3>Payment Summary:</h3>
                <table class="kv">
                    <tr><td class="k">Total:</td><td>{{ $totals['total'] }}</td></tr>
                    @if($totals['amount_paid'])
                        <tr><td class="k">Amount Paid:</td><td>{{ $totals['amount_paid'] }}</td></tr>
                        <tr><td class="k">Balance Due:</td><td>{{ $totals['balance_due'] }}</td></tr>
                    @endif
                    <tr><td class="k">Payment Method:</td><td>{{ $payment['method'] ?? '—' }}</td></tr>
                    <tr><td class="k">Transaction ID:</td><td>{{ $invoice['reference'] ?? $invoice['number'] }}</td></tr>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td class="num">{{ $item['line_total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="two-col footer-cols">
            <div>
                <h3>Payment Terms:</h3>
                <p>
                    For inquiries, contact<br>
                    {{ $business['email'] }}@if($business['phone']) or {{ $business['phone'] }}@endif
                </p>
            </div>
            <div>
                <h3>Authorized By:</h3>
                <p class="strong">{{ $business['owner_name'] ?? $business['name'] }}</p>
                <p class="muted">Accounts Manager</p>
            </div>
        </div>

        <div class="two-col thankyou-row">
            <div class="thankyou">{{ $theme['footer_text'] }}</div>
            <div class="signature-block">
                <div class="sig-line"></div>
                <div class="muted small">Signature</div>
            </div>
        </div>

        <div class="contact-bar">
            @if($business['phone'])
                <span class="item"><span class="dot"></span>{{ $business['phone'] }}</span>
            @endif
            @if($business['email'])
                <span class="item"><span class="dot"></span>{{ $business['email'] }}</span>
            @endif
            @if(count($business['address_lines']))
                <span class="item"><span class="dot"></span>{{ implode(', ', $business['address_lines']) }}</span>
            @endif
        </div>
    </div>
</body>
</html>
