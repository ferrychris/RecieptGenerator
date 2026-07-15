@php
    use App\Services\FontStacks;
    $displayFont = FontStacks::get($theme['font_display']);
    $bodyFont = FontStacks::get($theme['font_body']);
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
            font-size: 11px;
            line-height: 1.5;
        }
        .muted { color: {{ $theme['muted'] }}; }
        .accent { color: {{ $theme['accent'] }}; }
        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 3px solid {{ $theme['accent'] }};
            padding-bottom: 14px;
        }
        .business-name {
            font-family: {{ $displayFont }};
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .doc-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: {{ $theme['accent'] }};
            font-weight: 700;
        }
        .info-grid {
            display: flex;
            margin-top: 20px;
            border-bottom: 1px solid {{ $theme['rule'] }};
            padding-bottom: 20px;
        }
        .info-grid .col {
            flex: 1;
            padding-right: 16px;
            border-left: 1px solid {{ $theme['rule'] }};
            padding-left: 16px;
        }
        .info-grid .col:first-child { border-left: none; padding-left: 0; }
        .info-grid h4 {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: {{ $theme['muted'] }};
            margin: 0 0 6px 0;
            font-weight: 400;
        }
        .info-grid p { margin: 0; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 28px; }
        table.items thead { display: table-header-group; }
        table.items th {
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: {{ $theme['muted'] }};
            padding: 0 8px 8px 0;
            border-bottom: 1px solid {{ $theme['text'] }};
        }
        table.items th.num, table.items td.num { text-align: right; }
        table.items td {
            padding: 8px 8px 8px 0;
            border-bottom: 1px solid {{ $theme['rule'] }};
            vertical-align: top;
        }
        table.items tr { break-inside: avoid; }
        .totals { margin-top: 16px; margin-left: auto; width: 260px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 4px 0; border-bottom: 1px solid {{ $theme['rule'] }}; }
        .totals td.label { color: {{ $theme['muted'] }}; }
        .totals td.value { text-align: right; }
        .totals .grand-total td { border-bottom: none; border-top: 1px solid {{ $theme['text'] }}; padding-top: 8px; font-weight: 700; font-size: 14px; }
        footer {
            margin-top: 48px;
            padding-top: 12px;
            border-top: 1px solid {{ $theme['rule'] }};
            font-size: 9px;
            color: {{ $theme['muted'] }};
        }
        .notes { margin-top: 24px; font-size: 10px; color: {{ $theme['muted'] }}; }
    </style>
</head>
<body>
    <header>
        <div>
            @if(($theme['logo_mode'] ?? 'image') === 'image' && $business['logo_url'])
                <img src="{{ $business['logo_url'] }}" alt="{{ $business['name'] }}" style="max-height:36px; margin-bottom:6px;">
            @endif
            @if($business['show_name'] ?? true)
                <div class="business-name">{{ $business['name'] }}</div>
            @endif
            @foreach($business['address_lines'] as $line)
                <div class="muted" style="font-size:10px;">{{ $line }}</div>
            @endforeach
            @if($business['phone'])<div class="muted" style="font-size:10px;">{{ $business['phone'] }}</div>@endif
            @if($business['email'])<div class="muted" style="font-size:10px;">{{ $business['email'] }}</div>@endif
        </div>
        <div class="doc-title">{{ $meta['doc_type'] === 'receipt' ? 'Receipt' : 'Invoice' }} {{ $invoice['number'] }}</div>
    </header>

    <div class="info-grid">
        <div class="col">
            <h4>Date</h4>
            <p>{{ $invoice['issue_date'] }}</p>
            <p class="muted" style="font-size:9px;">Created {{ $invoice['created_at'] }}</p>
        </div>
        <div class="col">
            <h4>Status</h4>
            <p>{{ ucfirst(str_replace('_', ' ', $invoice['status'])) }}</p>
            @if($invoice['first_payment_date'])
                <p class="muted" style="font-size:9px;">1st payment {{ $invoice['first_payment_date'] }}</p>
            @endif
        </div>
        <div class="col">
            <h4>Billed To</h4>
            <p>{{ $customer['name'] }}</p>
            @if($customer['company'])<p class="muted">{{ $customer['company'] }}</p>@endif
        </div>
        <div class="col">
            <h4>Contact</h4>
            @if($customer['email'])<p>{{ $customer['email'] }}</p>@endif
            @if($business['tax_id'])<p class="muted">Tax ID: {{ $business['tax_id'] }}</p>@endif
        </div>
    </div>

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

    <div class="totals">
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
                <td class="value accent">{{ $totals['total'] }}</td>
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
        <div class="notes">
            <strong class="muted">Notes</strong>
            <p>{{ $invoice['notes'] }}</p>
        </div>
    @endif

    @if($theme['footer_text'])
        <footer>{{ $theme['footer_text'] }}</footer>
    @endif
</body>
</html>
