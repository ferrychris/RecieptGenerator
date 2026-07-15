@php
    use App\Services\FontStacks;
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
            padding: 6mm 4mm;
            font-family: {{ $bodyFont }};
            color: {{ $theme['text'] }};
            font-size: 10px;
            line-height: 1.45;
        }
        .center { text-align: center; }
        .muted { color: {{ $theme['muted'] }}; }
        .business-name {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .dashed {
            border-top: 1px dashed {{ $theme['rule'] }};
            margin: 8px 0;
        }
        .meta-row { display: flex; justify-content: space-between; }
        .item { margin-bottom: 6px; break-inside: avoid; }
        .item .desc-row { display: flex; justify-content: space-between; font-weight: 700; }
        .item .sub-row { display: flex; justify-content: space-between; color: {{ $theme['muted'] }}; font-size: 9px; }
        .totals-row { display: flex; justify-content: space-between; margin: 2px 0; }
        .grand-total { font-weight: 700; font-size: 13px; margin-top: 4px; }
        .barcode {
            height: 34px;
            margin: 10px 0 4px 0;
            background: repeating-linear-gradient(
                90deg,
                {{ $theme['text'] }} 0px,
                {{ $theme['text'] }} 2px,
                transparent 2px,
                transparent 3px,
                {{ $theme['text'] }} 3px,
                {{ $theme['text'] }} 4px,
                transparent 4px,
                transparent 6px
            );
        }
        footer { margin-top: 10px; font-size: 9px; }
    </style>
</head>
<body>
    <div class="center">
        @if(($theme['logo_mode'] ?? 'image') === 'image' && $business['logo_url'])
            <img src="{{ $business['logo_url'] }}" alt="{{ $business['name'] }}" style="max-height:32px; margin-bottom:6px;">
        @endif
        @if($business['show_name'] ?? true)
            <div class="business-name">{{ $business['name'] }}</div>
        @endif
        @foreach($business['address_lines'] as $line)
            <div class="muted">{{ $line }}</div>
        @endforeach
        @if($business['phone'])<div class="muted">{{ $business['phone'] }}</div>@endif
        @if($business['email'])<div class="muted">{{ $business['email'] }}</div>@endif
    </div>

    <div class="dashed"></div>

    <div class="meta-row"><span>No.</span><span>{{ $invoice['number'] }}</span></div>
    <div class="meta-row"><span>Date</span><span>{{ $invoice['issue_date'] }}</span></div>
    <div class="meta-row"><span>Created</span><span>{{ $invoice['created_at'] }}</span></div>
    <div class="meta-row"><span>Customer</span><span>{{ $customer['name'] }}</span></div>
    @if($invoice['first_payment_date'])
        <div class="meta-row"><span>1st Payment</span><span>{{ $invoice['first_payment_date'] }}</span></div>
    @endif

    <div class="dashed"></div>

    @foreach($items as $item)
        <div class="item">
            <div class="desc-row">
                <span>{{ $item['description'] }}</span>
                <span>{{ $item['line_total'] }}</span>
            </div>
            <div class="sub-row">
                <span>{{ $item['qty'] }} &times; {{ $item['unit_price'] }}</span>
                @if($item['tax_rate'])
                    <span>tax {{ $item['tax_rate'] }}</span>
                @endif
            </div>
        </div>
    @endforeach

    <div class="dashed"></div>

    <div class="totals-row"><span class="muted">Subtotal</span><span>{{ $totals['subtotal'] }}</span></div>
    @foreach($totals['tax_breakdown'] as $tax)
        <div class="totals-row"><span class="muted">{{ $tax['label'] }} ({{ $tax['rate'] }})</span><span>{{ $tax['amount'] }}</span></div>
    @endforeach
    <div class="totals-row grand-total"><span>Total</span><span>{{ $totals['total'] }}</span></div>
    @if($totals['amount_paid'])
        <div class="totals-row"><span class="muted">Paid</span><span>{{ $totals['amount_paid'] }}</span></div>
        <div class="totals-row"><span class="muted">Balance due</span><span>{{ $totals['balance_due'] }}</span></div>
    @endif

    @if(($options['show_notes'] ?? true) && $invoice['notes'])
        <div class="dashed"></div>
        <div class="center muted">{{ $invoice['notes'] }}</div>
    @endif

    @if($options['show_qr'] ?? false)
        <div class="barcode"></div>
    @endif

    @if($theme['footer_text'])
        <footer class="center">{{ $theme['footer_text'] }}</footer>
    @endif
</body>
</html>
