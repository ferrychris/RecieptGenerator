@php
    $fontStacks = [
        'libre-baskerville' => "'Libre Baskerville', Georgia, 'Times New Roman', serif",
        'lato' => "'Lato', Helvetica, Arial, sans-serif",
    ];
    $displayFont = $fontStacks[$theme['font_display']] ?? $fontStacks['libre-baskerville'];
    $bodyFont = $fontStacks[$theme['font_body']] ?? $fontStacks['lato'];
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
            padding: 56px 64px;
            font-family: {{ $bodyFont }};
            color: {{ $theme['text'] }};
            font-size: 12px;
            line-height: 1.5;
        }
        .rule { border-top: 1px solid {{ $theme['rule'] }}; }
        .rule-thick { border-top: 2px solid {{ $theme['text'] }}; }
        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
        }
        .business-name {
            font-family: {{ $displayFont }};
            font-size: 22px;
            font-weight: 700;
            color: {{ $theme['text'] }};
        }
        .muted { color: {{ $theme['muted'] }}; }
        .doc-title {
            font-family: {{ $displayFont }};
            font-size: 28px;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .meta-table { margin-top: 8px; text-align: right; }
        .meta-table td { padding: 2px 0 2px 16px; }
        .parties {
            display: flex;
            justify-content: space-between;
            margin-top: 32px;
            padding-top: 16px;
        }
        .party h3 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: {{ $theme['muted'] }};
            margin: 0 0 6px 0;
        }
        .party p { margin: 0; }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 32px;
        }
        table.items thead {
            display: table-header-group;
        }
        table.items th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: {{ $theme['muted'] }};
            padding: 0 8px 8px 0;
            border-bottom: 2px solid {{ $theme['text'] }};
        }
        table.items th.num, table.items td.num { text-align: right; }
        table.items td {
            padding: 10px 8px 10px 0;
            border-bottom: 1px solid {{ $theme['rule'] }};
            vertical-align: top;
        }
        table.items tr { break-inside: avoid; }
        .totals {
            margin-top: 16px;
            margin-left: auto;
            width: 280px;
        }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 4px 0; }
        .totals td.label { color: {{ $theme['muted'] }}; }
        .totals td.value { text-align: right; }
        .totals .grand-total {
            border-top: 2px solid {{ $theme['text'] }};
            font-weight: 700;
            font-size: 15px;
        }
        .totals .grand-total td { padding-top: 10px; }
        footer {
            margin-top: 48px;
            padding-top: 16px;
            font-size: 10px;
            color: {{ $theme['muted'] }};
            text-align: center;
        }
        .notes {
            margin-top: 32px;
            font-size: 11px;
            color: {{ $theme['muted'] }};
        }
    </style>
</head>
<body>
    <header class="rule-thick">
        <div>
            @if(($theme['logo_mode'] ?? 'image') === 'image' && $business['logo_url'])
                <img src="{{ $business['logo_url'] }}" alt="{{ $business['name'] }}" style="max-height:48px; margin-bottom:8px;">
            @endif
            @if($business['show_name'] ?? true)
                <div class="business-name">{{ $business['name'] }}</div>
            @endif
            @foreach($business['address_lines'] as $line)
                <div class="muted">{{ $line }}</div>
            @endforeach
            @if($business['phone'])
                <div class="muted">{{ $business['phone'] }}</div>
            @endif
            @if($business['email'])
                <div class="muted">{{ $business['email'] }}</div>
            @endif
            @if($business['tax_id'])
                <div class="muted">Tax ID: {{ $business['tax_id'] }}</div>
            @endif
        </div>
        <div>
            <div class="doc-title">{{ $meta['doc_type'] === 'receipt' ? 'Receipt' : 'Invoice' }}</div>
            <table class="meta-table">
                <tr><td class="muted">Number</td><td>{{ $invoice['number'] }}</td></tr>
                <tr><td class="muted">Date</td><td>{{ $invoice['issue_date'] }}</td></tr>
                <tr><td class="muted">Created</td><td>{{ $invoice['created_at'] }}</td></tr>
                @if($invoice['status'])
                    <tr><td class="muted">Status</td><td>{{ ucfirst(str_replace('_', ' ', $invoice['status'])) }}</td></tr>
                @endif
                @if($invoice['first_payment_date'])
                    <tr><td class="muted">1st Payment</td><td>{{ $invoice['first_payment_date'] }}</td></tr>
                @endif
            </table>
        </div>
    </header>

    <div class="parties rule">
        <div class="party">
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
        <div class="notes rule" style="padding-top:12px;">
            <strong class="muted">Notes</strong>
            <p>{{ $invoice['notes'] }}</p>
        </div>
    @endif

    @if($theme['footer_text'])
        <footer class="rule">{{ $theme['footer_text'] }}</footer>
    @endif
</body>
</html>
