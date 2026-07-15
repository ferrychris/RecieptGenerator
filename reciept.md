# Template System Plan — Invoice & Receipt Generator

Handoff document for implementation. Stack: **Laravel 12 + Inertia v2 + Svelte 5**, PDF via **Spatie Browsershot** (queued jobs). Receipts/invoices are generated programmatically: `PDF = render(invoiceData, template, theme)`. No external design tools.

---

## 1. Core Concepts

Three separate layers — do not merge them:

| Layer        | What it is                                                                               | Stored as                                           |
| ------------ | ---------------------------------------------------------------------------------------- | --------------------------------------------------- |
| **Layout**   | A Blade template file defining structure (where header, parties, items table, totals go) | Code: `resources/views/pdf/layouts/{key}.blade.php` |
| **Theme**    | Visual variables applied to a layout: accent color, fonts, density, corner radius        | JSONB on `templates.theme`                          |
| **Template** | A business's saved combination: layout key + theme + content options                     | Row in `templates` table                            |

A business picks a layout, customizes a theme, saves it as a template, and assigns it per invoice (or per customer as default). Rendering the same invoice with a different template must always work — layouts are interchangeable because they all consume the same view-model.

## 2. The Render Contract (view-model)

Every layout receives exactly this structure. Layouts must never query the DB.

```php
// App\ViewModels\InvoiceDocument
[
  'business' => ['name','logo_url','address_lines'=>[], 'tax_id','email','phone'],
  'customer' => ['name','company','address_lines'=>[], 'tax_number','email'],
  'invoice'  => ['number','issue_date','due_date','currency','currency_symbol',
                 'status','payment_terms','reference','notes'],
  'items'    => [ ['description','details','qty','unit','unit_price','tax_rate','line_total'], ... ],
  'totals'   => ['subtotal','tax_breakdown'=>[['label','rate','amount']],
                 'discount','total','amount_paid','balance_due'],
  'payment'  => ['method','bank_name','account','routing','iban','swift','instructions'],
  'theme'    => Theme object (see §4),
  'meta'     => ['doc_type' => 'invoice'|'receipt', 'locale', 'page' => 'A4'|'letter'|'80mm'],
]
```

All money values arrive **pre-formatted as strings** (formatting service handles locale/currency); layouts never do math or number formatting.

## 3. Launch Layout Catalog (v1 — six layouts)

Reference HTML implementations exist in `templates/01–06*.html` (this repo/outputs). Convert each to Blade against the contract in §2.

| Key        | Name     | Persona                             | Page            | Signature traits                                                                          |
| ---------- | -------- | ----------------------------------- | --------------- | ----------------------------------------------------------------------------------------- |
| `ledger`   | Ledger   | Consultancies, law, finance         | A4/Letter       | Serif display (Libre Baskerville), double-rule header, formal totals                      |
| `bold`     | Bold     | Agencies, startups                  | A4/Letter       | Archivo Black uppercase, full-width accent band, dark grand-total bar                     |
| `swiss`    | Swiss    | Designers, architects, engineers    | A4/Letter       | Helvetica, 4-col info grid, hairline rules, single red accent, thin-space number grouping |
| `thermal`  | Thermal  | Retail, cafés, quick service        | 80mm continuous | IBM Plex Mono, dashed rules, qty×price sub-lines, barcode strip, `doc_type: receipt`      |
| `friendly` | Friendly | Freelancers, salons, local services | A4/Letter       | Nunito, rounded cards, pill badge, soft accent panel totals                               |
| `sidebar`  | Sidebar  | Studios, contractors, boutiques     | A4/Letter       | 64mm colored left column (Fraunces serif), total featured in sidebar                      |

### Layout roadmap (v1.x — add after launch)

| Key          | Name           | Persona                   | Notes                                                                                         |
| ------------ | -------------- | ------------------------- | --------------------------------------------------------------------------------------------- |
| `statement`  | Statement      | Recurring clients         | Multi-invoice account statement; groups invoices, aging buckets                               |
| `compact`    | Compact        | High item counts          | Dense rows, smaller type, fits ~40 items/page before pagination                               |
| `letterhead` | Letterhead     | Firms with existing brand | Large reserved header zone for uploaded letterhead image                                      |
| `bilingual`  | Bilingual      | Import/export businesses  | Two-language field labels side by side (label i18n from §6)                                   |
| `estimate`   | Estimate/Quote | Pre-sale                  | Same layouts re-skinned: doc title, validity date, accept-by line — driven by `meta.doc_type` |

## 4. Theme Schema (JSONB `templates.theme`)

```json
{
    "accent": "#1d2d44",
    "accent_ink": "#ffffff",
    "text": "#22201c",
    "muted": "#6f6a5f",
    "rule": "#c9c2b4",
    "font_display": "libre-baskerville",
    "font_body": "lato",
    "density": "normal", // compact | normal | relaxed → row padding scale
    "radius": 0, // px, used only by layouts that support rounding
    "logo_mode": "image", // image | initial | none
    "number_style": "comma", // comma | thin-space | period  (display only)
    "footer_text": "Thank you for your business."
}
```

Rules:

- Each layout ships a **default theme**; a business theme overrides keys, never replaces the object.
- Fonts come from a **whitelist** (self-hosted woff2 in `public/fonts/`, NOT Google Fonts at render time — Browsershot must render offline/deterministically). Launch set: Libre Baskerville, Lato, Archivo, Inter, Helvetica-stack, IBM Plex Mono, Nunito, Fraunces, Public Sans.
- Validate theme JSON against a schema (accept only whitelisted keys/values; colors as hex).
- **Auto-theme from logo:** on logo upload, extract dominant color (server-side, e.g. league/color-extractor), generate accessible accent/ink pair (contrast ≥ 4.5:1 against white), offer as suggested theme.

## 5. Database

Extend the existing `templates` table (see plan.md §5) to:

```
templates
  id, business_id, name,
  layout_key        string   // 'ledger' | 'bold' | ...
  theme             jsonb
  options           jsonb    // §6 content options
  is_default        boolean
  created_at, updated_at
```

Drop `canva_design_id` and `asset_url`, add nothing external. Add `customers.default_template_id` (nullable FK). `invoices.template_id` snapshot: when an invoice is **finalized**, copy the resolved theme+options into `invoices.render_snapshot` (jsonb) so past invoices re-render identically even if the template is later edited.

## 6. Content Options (JSONB `templates.options`)

Per-template toggles independent of visuals:

```json
{
    "show_tax_column": true,
    "show_discount_row": false,
    "show_payment_details": true,
    "show_notes": true,
    "show_qr": false, // v1.x: QR encoding hosted invoice URL / EPC payment QR
    "item_details_line": true, // secondary description line under each item
    "labels_locale": "en", // field-label language (Invoice/Rechnung/Facture…)
    "date_format": "M j, Y"
}
```

## 7. Rendering Pipeline

```
InvoiceController@download / SendInvoiceJob
  → InvoiceDocumentBuilder (Eloquent → view-model, formats money/dates)
  → TemplateResolver (invoice.render_snapshot ?? template ?? business default ?? 'ledger' defaults)
  → Blade render: pdf.layouts.{layout_key}  with view-model
  → Browsershot: HTML → PDF, queued (RenderInvoicePdf job)
      - A4/Letter: format from meta.page, margins 0 (layouts own margins)
      - 80mm: paperWidth 80mm, continuous height
      - printBackground: true, no network access, local fonts
  → Store PDF: storage/invoices/{business}/{invoice}.pdf → invoices.pdf_url
```

Implementation notes:

- One interface: `ReceiptRenderer::render(InvoiceDocument $doc): PdfFile`; Browsershot driver first, `DomPdfDriver` fallback config flag for hosts without Chrome.
- **Pagination:** long item lists must break cleanly — repeat `<thead>` per page (`display: table-header-group`), keep totals block unbreakable (`break-inside: avoid`), test each layout at 1, 3, 25, 80 items.
- **Golden-file tests:** per layout, render fixture data → compare PDF page count + text extraction snapshot (not pixel-perfect diffs) in CI.
- Rendering is **idempotent and pure**: same snapshot + same data = identical PDF.

## 8. Template Picker & Theme Editor (Svelte pages)

- **Gallery page** (`/templates`): live preview cards of every layout rendered with the business's real data (server-rendered preview HTML in sandboxed iframes — same Blade views, so preview ≡ PDF).
- **Editor** (`/templates/{id}/edit`): left panel = theme controls (accent color picker, font pair select, density, logo mode, footer text) + options toggles; right panel = live preview. Preview updates via debounced Inertia partial reload passing draft theme → server renders Blade with draft → returns HTML. **No client-side re-implementation of layouts** — one source of truth.
- "Save as new template" (duplicate), "Set as default", "Assign to customer".
- Regenerate/change template on an invoice = pick different template → re-render (only while invoice is draft; finalized invoices keep their snapshot unless explicitly re-issued).

## 9. Build Order

1. View-model builder + `ledger` layout in Blade + Browsershot job + storage → one working PDF end to end
2. Port remaining five layouts against the same contract; golden-file tests for all six
3. `templates` table + TemplateResolver + render snapshot on finalize
4. Gallery page with live previews (business's own data)
5. Theme editor with server-side live preview; theme/options validation
6. Auto-theme from logo; font self-hosting pass
7. v1.x layouts (`statement`, `compact`, `letterhead`, `estimate` doc_type, `bilingual`) + QR option

## 10. Acceptance Criteria

- Any invoice renders on all six layouts without code changes (contract compliance)
- Theme change never requires touching Blade files
- 80-item invoice paginates correctly on every A4 layout
- Finalized invoice re-renders byte-stable from its snapshot
- Render job completes < 5s p95; no external network calls during render
- Adding layout #7 = one Blade file + one catalog entry + default theme (no schema change)
