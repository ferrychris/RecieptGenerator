@php
    use App\Services\FontStacks;
    $displayFont = FontStacks::get($theme['font_display']);
    $bodyFont = FontStacks::get($theme['font_body']);
    $radius = (int) ($theme['radius'] ?? 16);
@endphp
<!DOCTYPE html>
<html lang="{{ $meta['locale'] ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice['number'] }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 48px 56px;
            font-family: {{ $bodyFont }};
            color: {{ $theme['text'] }};
            font-size: 12px;
            line-height: 1.6;
            background: #fffaf8;
        }
        .muted { color: {{ $theme['muted'] }}; }
        header { display: flex; justify-content: space-between; align-items: center; }
        .business-name {
            font-family: {{ $displayFont }};
            font-weight: 800;
            font-size: 22px;
        }
        .pill {
            display: inline-block;
            background: {{ $theme['accent'] }};
            color: {{ $theme['accent_ink'] }};
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card {
            background: #ffffff;
            border: 1px solid {{ $theme['rule'] }};
            border-radius: {{ $radius }}px;
            padding: 20px 24px;
            margin-top: 20px;
        }
        .card h4 {
            margin: 0 0 8px 0;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: {{ $theme['muted'] }};
        }
        .card p { margin: 0; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items thead { display: table-header-group; }
        table.items th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: {{ $theme['muted'] }};
            padding: 0 8px 10px 0;
        }
        table.items th.num, table.items td.num { text-align: right; }
        table.items td {
            padding: 10px 8px;
            border-top: 1px solid {{ $theme['rule'] }};
            vertical-align: top;
        }
        table.items tr { break-inside: avoid; }
        .totals-panel {
            margin-top: 16px;
            background: color-mix(in srgb, {{ $theme['accent'] }} 12%, white);
            border-radius: {{ $radius }}px;
            padding: 20px 24px;
            margin-left: auto;
            width: 280px;
        }
        .totals-panel table { width: 100%; border-collapse: collapse; }
        .totals-panel td { padding: 4px 0; }
        .totals-panel td.label { color: {{ $theme['muted'] }}; }
        .totals-panel td.value { text-align: right; }
        .totals-panel .grand-total td { font-weight: 800; font-size: 16px; padding-top: 10px; }
        footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: {{ $theme['muted'] }};
        }
    </style>
</head>
<body>
    <header>
        <div>
            @if(($theme['logo_mode'] ?? 'image') === 'image' && $business['logo_url'])
                <img src="{{ $business['logo_url'] }}" alt="{{ $business['name'] }}" style="max-height:40px; margin-bottom:6px;">
            @endif
            @if($business['show_name'] ?? true)
                <div class="business-name">{{ $business['name'] }}</div>
            @endif
            @foreach($business['address_lines'] as $line)
                <div class="muted" style="font-size:11px;">{{ $line }}</div>
            @endforeach
            @if($business['phone'])<div class="muted" style="font-size:11px;">{{ $business['phone'] }}</div>@endif
            @if($business['email'])<div class="muted" style="font-size:11px;">{{ $business['email'] }}</div>@endif
        </div>
        <span class="pill">{{ $meta['doc_type'] === 'receipt' ? 'Receipt' : 'Invoice' }}</span>
    </header>

    <div class="card" style="display:flex; justify-content:space-between;">
        <div>
            <h4>Billed To</h4>
            <p><strong>{{ $customer['name'] }}</strong></p>
            @if($customer['company'])<p class="muted">{{ $customer['company'] }}</p>@endif
            @if($customer['email'])<p class="muted">{{ $customer['email'] }}</p>@endif
        </div>
        <div style="text-align:right;">
            <h4>Details</h4>
            <p>{{ $invoice['number'] }}</p>
            <p class="muted">{{ $invoice['issue_date'] }}</p>
            <p class="muted" style="font-size:10px;">Created {{ $invoice['created_at'] }}</p>
            @if($invoice['first_payment_date'])
                <p class="muted" style="font-size:10px;">1st payment {{ $invoice['first_payment_date'] }}</p>
            @endif
        </div>
    </div>

    <div class="card">
        <table class="items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="num">Qty</th>
                    <th class="num">Unit Price</th>
                    @if($options['show_tax_column'] ?? true)
                        <th class="num">Tax</th>
                    @endif
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td class="num">{{ $item['qty'] }}</td>
                        <td class="num">{{ $item['unit_price'] }}</td>
                        @if($options['show_tax_column'] ?? true)
                            <td class="num">{{ $item['tax_rate'] ?? '—' }}</td>
                        @endif
                        <td class="num">{{ $item['line_total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="totals-panel">
        <table>
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">{{ $totals['subtotal'] }}</td>
            </tr>
            @foreach($totals['tax_breakdown'] as $tax)
                <tr>
                    <td class="label">{{ $tax['label'] }} ({{ $tax['rate'] }})</td>
                    <td class="value">{{ $tax['amount'] }}</td>
                </tr>
            @endforeach
            <tr class="grand-total">
                <td>Total</td>
                <td class="value">{{ $totals['total'] }}</td>
            </tr>
            @if($totals['amount_paid'])
                <tr>
                    <td class="label">Amount paid</td>
                    <td class="value">{{ $totals['amount_paid'] }}</td>
                </tr>
                <tr>
                    <td class="label">Balance due</td>
                    <td class="value">{{ $totals['balance_due'] }}</td>
                </tr>
            @endif
        </table>
    </div>

    @if(($options['show_notes'] ?? true) && $invoice['notes'])
        <div class="card">
            <h4>Notes</h4>
            <p>{{ $invoice['notes'] }}</p>
        </div>
    @endif

    @if($theme['footer_text'])
        <footer>{{ $theme['footer_text'] }}</footer>
    @endif
</body>
</html>
