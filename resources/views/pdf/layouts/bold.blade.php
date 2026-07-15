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
            font-family: {{ $bodyFont }};
            color: {{ $theme['text'] }};
            font-size: 12px;
            line-height: 1.5;
        }
        .band {
            background: {{ $theme['accent'] }};
            color: {{ $theme['accent_ink'] }};
            padding: 40px 64px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .band .business-name {
            font-family: {{ $displayFont }};
            text-transform: uppercase;
            font-size: 24px;
            letter-spacing: 0.5px;
        }
        .band .doc-title {
            font-family: {{ $displayFont }};
            text-transform: uppercase;
            font-size: 32px;
            text-align: right;
            letter-spacing: 1px;
        }
        .band .meta-table { margin-top: 10px; text-align: right; opacity: 0.85; font-size: 11px; }
        .band .meta-table td { padding: 1px 0 1px 16px; }
        main { padding: 40px 64px; }
        .muted { color: {{ $theme['muted'] }}; }
        .parties { margin-bottom: 32px; }
        .parties h3 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: {{ $theme['muted'] }};
            margin: 0 0 8px 0;
        }
        .parties p { margin: 0; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items thead { display: table-header-group; }
        table.items th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 0 8px 10px 0;
            border-bottom: 3px solid {{ $theme['text'] }};
        }
        table.items th.num, table.items td.num { text-align: right; }
        table.items td {
            padding: 12px 8px 12px 0;
            border-bottom: 1px solid {{ $theme['rule'] }};
            vertical-align: top;
        }
        table.items tr { break-inside: avoid; }
        .totals { margin-top: 20px; margin-left: auto; width: 320px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 5px 0; }
        .totals td.label { color: {{ $theme['muted'] }}; }
        .totals td.value { text-align: right; }
        .grand-total-bar {
            margin-top: 12px;
            background: {{ $theme['accent'] }};
            color: {{ $theme['accent_ink'] }};
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: {{ $displayFont }};
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 16px;
        }
        footer {
            margin-top: 48px;
            padding-top: 16px;
            border-top: 1px solid {{ $theme['rule'] }};
            font-size: 10px;
            color: {{ $theme['muted'] }};
            text-align: center;
        }
        .notes { margin-top: 32px; font-size: 11px; color: {{ $theme['muted'] }}; }
    </style>
</head>
<body>
    <div class="band">
        <div>
            @if(($theme['logo_mode'] ?? 'image') === 'image' && $business['logo_url'])
                <img src="{{ $business['logo_url'] }}" alt="{{ $business['name'] }}" style="max-height:40px; margin-bottom:8px;">
            @endif
            @if($business['show_name'] ?? true)
                <div class="business-name">{{ $business['name'] }}</div>
            @endif
            @if(count($business['address_lines']) || $business['phone'] || $business['email'])
                <div class="meta-table" style="text-align:left; margin-top:6px; opacity:0.8; font-size:11px;">
                    @foreach($business['address_lines'] as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                    @if($business['phone'])<div>{{ $business['phone'] }}</div>@endif
                    @if($business['email'])<div>{{ $business['email'] }}</div>@endif
                </div>
            @endif
        </div>
        <div>
            <div class="doc-title">{{ $meta['doc_type'] === 'receipt' ? 'Receipt' : 'Invoice' }}</div>
            <table class="meta-table">
                <tr><td>No.</td><td>{{ $invoice['number'] }}</td></tr>
                <tr><td>Date</td><td>{{ $invoice['issue_date'] }}</td></tr>
                <tr><td>Created</td><td>{{ $invoice['created_at'] }}</td></tr>
                @if($invoice['first_payment_date'])
                    <tr><td>1st Payment</td><td>{{ $invoice['first_payment_date'] }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <main>
        <div class="parties">
            <h3>Billed To</h3>
            <p><strong>{{ $customer['name'] }}</strong></p>
            @if($customer['company'])
                <p>{{ $customer['company'] }}</p>
            @endif
            @foreach($customer['address_lines'] as $line)
                <p>{{ $line }}</p>
            @endforeach
            @if($customer['email'])
                <p class="muted">{{ $customer['email'] }}</p>
            @endif
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
            </table>
            <div class="grand-total-bar">
                <span>Total</span>
                <span>{{ $totals['total'] }}</span>
            </div>
            @if($totals['amount_paid'])
                <table style="margin-top:8px;">
                    <tr>
                        <td class="label">Amount paid</td>
                        <td class="value">{{ $totals['amount_paid'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Balance due</td>
                        <td class="value">{{ $totals['balance_due'] }}</td>
                    </tr>
                </table>
            @endif
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
    </main>
</body>
</html>
