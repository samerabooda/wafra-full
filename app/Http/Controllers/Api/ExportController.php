<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, Branch};
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    // ── GET /api/cards/export/excel ────────────────────────────
    public function excel(Request $request): Response
    {
        $cards = $this->getCards($request);

        // UTF-8 BOM for proper Arabic in Excel
        $bom  = "\xEF\xBB\xBF";
        $rows = [];

        // Headers - bilingual
        $rows[] = implode(',', [
            '"م"',
            '"رقم الحساب / Account No."',
            '"الفرع / Branch"',
            '"الشهر / Month"',
            '"البروكر / Broker"',
            '"ع. بروكر / Broker Comm."',
            '"المسوّق / Marketer"',
            '"ع. مسوّق / Mkt. Comm."',
            '"مسوّق خارجي / Ext. Mktr"',
            '"ع. خارجي / Ext. Comm."',
            '"Referral"',
            '"ع. Referral"',
            '"Rebate"',
            '"الإجمالي / Total"',
            '"إيداع أولي / Init. Deposit"',
            '"نوع / Type"',
            '"الحالة / Status"',
        ]);

        foreach ($cards as $i => $c) {
            $rows[] = implode(',', [
                $i + 1,
                '"#' . $c->account_number . '"',
                '"' . addslashes($c->branch?->name_ar ?? '') . '"',
                '"' . $c->month . '"',
                '"' . addslashes($c->broker?->name ?? '—') . '"',
                $c->broker_commission,
                '"' . addslashes($c->marketer?->name ?? '—') . '"',
                $c->marketer_commission,
                '"' . addslashes($c->extMarketer1?->name ?? '—') . '"',
                $c->ext_commission1,
                '"' . addslashes($c->referral_account ?? '—') . '"',
                $c->referral_commission,
                $c->rebate_amount,
                round($c->broker_commission + $c->marketer_commission + $c->ext_commission1
                    + $c->ext_commission2 + $c->referral_commission + $c->rebate_amount, 2),
                $c->initial_deposit,
                '"' . addslashes($c->accountType?->name_en ?? '') . '"',
                '"' . $c->status . '"',
            ]);
        }

        $csv = $bom . implode("\n", $rows);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="wafra-cards-' . date('Y-m-d') . '.csv"',
        ]);
    }

    // ── GET /api/cards/export/pdf ──────────────────────────────
    public function pdf(Request $request): Response
    {
        $cards = $this->getCards($request);
        $user  = Auth::user();

        // Arabic-safe HTML PDF (browser print / wkhtmltopdf compatible)
        $html = $this->buildPdfHtml($cards, $user);

        return response($html, 200, [
            'Content-Type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="wafra-cards-' . date('Y-m-d') . '.html"',
        ]);
    }

    // ── Build PDF HTML ─────────────────────────────────────────
    private function buildPdfHtml($cards, $user): string
    {
        $rows = '';
        foreach ($cards as $i => $c) {
            $total = round(
                $c->broker_commission + $c->marketer_commission
                + $c->ext_commission1 + $c->ext_commission2
                + $c->referral_commission + $c->rebate_amount, 2
            );
            $ccBadge = $c->cc_branch_id
                ? '<span style="background:#e8f5f0;color:#0a7a5a;font-size:9px;padding:1px 5px;border-radius:3px;border:1px solid #b2d8cc">CC</span> '
                : '';
            $rebTag  = $c->has_rebate
                ? ' <span style="background:#ede9fb;color:#534AB7;font-size:9px;padding:1px 4px;border-radius:3px">R</span>'
                : '';

            $rows .= "
            <tr style='background:" . ($i%2===0 ? '#fff' : '#f9fafb') . "'>
                <td style='text-align:center;color:#888;font-size:10px'>" . ($i+1) . "</td>
                <td style='font-family:monospace;font-weight:600;font-size:11px'>{$ccBadge}#{$c->account_number}{$rebTag}</td>
                <td>" . htmlspecialchars($c->branch?->name_ar ?? '—') . "</td>
                <td>" . htmlspecialchars($c->month) . "</td>
                <td>" . htmlspecialchars($c->broker?->name ?? '—') . "</td>
                <td style='text-align:center;font-weight:600;color:#1a7a4a'>\${$c->broker_commission}</td>
                <td>" . htmlspecialchars($c->marketer?->name ?? '—') . "</td>
                <td style='text-align:center;color:#1a7a4a'>\${$c->marketer_commission}</td>
                <td style='text-align:center;font-weight:700;color:" . ($total > 8 ? '#c0392b' : '#1a7a4a') . "'>\${$total}</td>
                <td style='text-align:center;color:#2563eb'>\${$c->initial_deposit}</td>
                <td style='text-align:center;font-size:10px'>" . $c->status . "</td>
            </tr>";
        }

        $title    = 'كروت العمولات / Commission Cards';
        $date     = now()->format('d/m/Y H:i');
        $total_r  = count($cards);
        $totalDep = number_format($cards->sum('initial_deposit'), 0);

        return <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title}</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700&display=swap');
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Tajawal',Arial,sans-serif;background:#fff;color:#1a1a1a;font-size:12px;direction:rtl}
  .header{background:linear-gradient(135deg,#0a2a20,#0F6E56);color:white;padding:16px 24px;
    display:flex;align-items:center;justify-content:space-between}
  .h-title{font-size:18px;font-weight:700}
  .h-sub{font-size:11px;opacity:.75;margin-top:2px}
  .h-meta{font-size:11px;text-align:left;opacity:.8}
  .stats{display:flex;gap:0;border-bottom:2px solid #e5e7eb}
  .stat{flex:1;padding:10px 16px;border-left:1px solid #e5e7eb;text-align:center}
  .stat:last-child{border-left:none}
  .sn{font-size:18px;font-weight:700;color:#0F6E56}
  .sl{font-size:10px;color:#888}
  table{width:100%;border-collapse:collapse}
  th{background:#1e2535;color:white;padding:7px 8px;font-size:10px;font-weight:600;
    border:1px solid #374151;white-space:nowrap;text-align:right}
  td{padding:6px 8px;border:1px solid #e5e7eb;font-size:11px;white-space:nowrap}
  .footer{padding:10px 24px;font-size:10px;color:#888;display:flex;justify-content:space-between;
    border-top:1px solid #e5e7eb;margin-top:8px}
  @media print{
    body{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .no-print{display:none}
    @page{margin:10mm;size:A4 landscape}
  }
</style>
</head>
<body>
<div class="header">
  <div>
    <div class="h-title">وفرة الخليجية — كروت العمولات</div>
    <div class="h-sub">Wafra Gulf — Commission Cards Report</div>
  </div>
  <div class="h-meta">
    التاريخ: {$date}<br>
    أُعدَّ بواسطة: {$user?->name}
  </div>
</div>
<div class="stats">
  <div class="stat"><div class="sn">{$total_r}</div><div class="sl">إجمالي الكروت</div></div>
  <div class="stat"><div class="sn">\${$totalDep}</div><div class="sl">إجمالي الإيداعات</div></div>
</div>
<div class="no-print" style="padding:8px 16px;background:#f0f9ff;font-size:11px;color:#0369a1">
  💡 اضغط Ctrl+P أو ⌘+P للطباعة أو الحفظ كـ PDF
</div>
<table>
  <thead>
    <tr>
      <th>م</th><th>رقم الحساب</th><th>الفرع</th><th>الشهر</th>
      <th>البروكر</th><th>ع. بروكر</th><th>المسوّق</th><th>ع. مسوّق</th>
      <th>الإجمالي</th><th>إيداع أولي</th><th>الحالة</th>
    </tr>
  </thead>
  <tbody>{$rows}</tbody>
</table>
<div class="footer">
  <span>وفرة الخليجية للخدمات المالية · {$date}</span>
  <span>إجمالي: {$total_r} كارت</span>
</div>
</body>
</html>
HTML;
    }

    // ── Query cards ───────────────────────────────────────────
    private function getCards(Request $request)
    {
        $user  = Auth::user();
        $query = CommissionCard::with([
            'branch','broker','marketer','extMarketer1','extMarketer2',
            'accountType','ccBranch',
        ])->whereNull('deleted_at');

        if ($user->isBranchManager()) {
            $query->where(function($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhere('cc_branch_id', $user->branch_id);
            });
        }

        if ($b = $request->branch_id) $query->where('branch_id', $b);
        if ($m = $request->month)     $query->where('month', $m);
        if ($br= $request->broker_id) $query->where('broker_id', $br);

        return $query->orderBy('month_date', 'desc')->get();
    }
}
