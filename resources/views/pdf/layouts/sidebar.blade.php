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
            line-height: 1.55;
        }
        .page { display: flex; min-height: 297mm; }
        .sidebar {
            width: 64mm;
            flex-shrink: 0;
            background: {{ $theme['accent'] }};
            color: {{ $theme['accent_ink'] }};
            padding: 40px 28px;
            display: flex;
            flex-direction: column;
        }
        .sidebar .business-name {
            font-family: {{ $displayFont }};
            font-size: 22px;
            line-height: 1.25;
        }
        .sidebar .address { margin-top: 12px; opacity: 0.85; font-size: 11px; }
        .sidebar .doc-title {
            margin-top: 32px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 11px;
            opacity: 0.8;
        }
        .sidebar .meta { margin-top: 6px; font-size: 12px; }
        .sidebar .featured-total {
            margin-top: auto;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }
        .sidebar .featured-total .label {
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 1px;
            opacity: 0.8;
        }
        .sidebar .featured-total .amount {
            font-family: {{ $displayFont }};
            font-size: 28px;
            margin-top: 6px;
        }
        .content { flex: 1; padding: 40px 48px; }
        .muted { color: {{ $theme['muted'] }}; }
        .content h3 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: {{ $theme['muted'] }};
            margin: 0 0 8px 0;
        }
        table.items { width: 100%; border-collapse: collapse; margin-top: 28px; }
        table.items thead { display: table-header-group; }
        table.items th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: {{ $theme['muted'] }};
            padding: 0 8px 8px 0;
            border-bottom: 2px solid {{ $theme['text'] }};
        }
        table.items th.num, table.items td.num { text-align: right; }
        table.items td {
            padding: 10px 8px;
            border-bottom: 1px solid {{ $theme['rule'] }};
            vertical-align: top;
        }
        table.items tr { break-inside: avoid; }
        .totals { margin-top: 16px; margin-left: auto; width: 260px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 4px 0; }
        .totals td.label { color: {{ $theme['muted'] }}; }
        .totals td.value { text-align: right; }
        .notes { margin-top: 28px; font-size: 11px; color: {{ $theme['muted'] }}; }
    </style>
</head>
<body>
    <div class="page">
        <div class="sidebar">
            @if(($theme['logo_mode'] ?? 'image') === 'image' && $business['logo_url'])
                <img src="{{ $business['logo_url'] }}" alt="{{ $business['name'] }}" style="max-height:40px; margin-bottom:8px;">
            @endif
            @if($business['show_name'] ?? true)
                <div class="business-name">{{ $business['name'] }}</div>
            @endif
            @if(count($business['address_lines']) || $business['phone'] || $business['email'])
                <div class="address">
                    @foreach($business['address_lines'] as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                    @if($business['phone'])<div>{{ $business['phone'] }}</div>@endif
                    @if($business['email'])<div>{{ $business['email'] }}</div>@endif
                </div>
            @endif

            <div class="doc-title">{{ $meta['doc_type'] === 'receipt' ? 'Receipt' : 'Invoice' }}</div>
            <div class="meta">{{ $invoice['number'] }}</div>
            <div class="meta">{{ $invoice['issue_date'] }}</div>
            <div class="meta" style="opacity:0.8; font-size:10px;">Created {{ $invoice['created_at'] }}</div>
            @if($invoice['first_payment_date'])
                <div class="meta" style="opacity:0.8; font-size:10px;">1st payment {{ $invoice['first_payment_date'] }}</div>
            @endif

            <div class="featured-total">
                <div class="label">Total</div>
                <div class="amount">{{ $totals['total'] }}</div>
            </div>
        </div>

        <div class="content">
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
        </div>
    </div>
</body>
</html>
