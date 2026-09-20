<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommissionCard;
use Illuminate\Http\{Request, Response};
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * GET /api/export/excel
     * Streams a CSV (Excel-compatible, UTF-8 with BOM).
     */
    public function excel(Request $request): StreamedResponse
    {
        $user = $request->user();
        $q    = CommissionCard::query()->with(['broker','marketer','branch','extMarketer1','extMarketer2']);

        if (method_exists(CommissionCard::class, 'applyBranchScope')) {
            $q = CommissionCard::applyBranchScope($q, $user);
        }

        if ($request->filled('branch_id')) $q->where('branch_id', $request->branch_id);
        if ($request->filled('from'))      $q->where('created_at', '>=', $request->from);
        if ($request->filled('to'))        $q->where('created_at', '<=', $request->to);

        $filename = 'wafra-cards-' . now()->format('Y-m-d-Hi') . '.csv';

        $callback = function () use ($q) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Arabic in Excel

            fputcsv($out, [
                'Account No', 'Month', 'Branch',
                'Broker', 'Broker Comm.',
                'Marketer', 'Marketer Comm.',
                'Ext. Marketer 1', 'Ext. Marketer 2',
                'Rebate', 'Rebate Amount',
                'Referral Account', 'Referral Comm.',
                'Initial Deposit', 'Monthly Deposit',
                'Total Commission', 'Status', 'Created At',
            ]);

            $q->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->account_no ?? '',
                        $r->month ?? '',
                        $r->branch->name_en ?? '',
                        $r->broker->name ?? '',
                        $r->broker_commission ?? 0,
                        $r->marketer->name ?? '',
                        $r->marketer_commission ?? 0,
                        $r->extMarketer1->name ?? '',
                        $r->extMarketer2->name ?? '',
                        $r->has_rebate ? 'Yes' : 'No',
                        $r->rebate_amount ?? 0,
                        $r->referral_account ?? '',
                        $r->referral_commission ?? 0,
                        $r->initial_deposit ?? 0,
                        $r->monthly_deposit ?? 0,
                        $r->total_commission ?? 0,
                        $r->status ?? '',
                        $r->created_at?->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * GET /api/export/pdf
     * PDF via barryvdh/laravel-dompdf, falls back to HTML if not installed.
     */
    public function pdf(Request $request): Response
    {
        $user = $request->user();
        $q    = CommissionCard::query()->with(['broker','marketer','branch']);

        if (method_exists(CommissionCard::class, 'applyBranchScope')) {
            $q = CommissionCard::applyBranchScope($q, $user);
        }

        if ($request->filled('branch_id')) $q->where('branch_id', $request->branch_id);
        if ($request->filled('from'))      $q->where('created_at', '>=', $request->from);
        if ($request->filled('to'))        $q->where('created_at', '<=', $request->to);

        $cards = $q->orderByDesc('created_at')->limit(1000)->get();

        $html = $this->buildHtml($cards);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape');
            return $pdf->download('wafra-cards-' . now()->format('Y-m-d-Hi') . '.pdf');
        }

        return response($html, 200, [
            'Content-Type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="wafra-cards-' . now()->format('Y-m-d-Hi') . '.html"',
        ]);
    }

    private function buildHtml($cards): string
    {
        $rows = '';
        foreach ($cards as $c) {
            $rows .= '<tr>'
                . '<td>' . e($c->account_no) . '</td>'
                . '<td>' . e($c->month) . '</td>'
                . '<td>' . e($c->branch->name_en ?? '') . '</td>'
                . '<td>' . e($c->broker->name ?? '') . '</td>'
                . '<td>' . e($c->broker_commission) . '</td>'
                . '<td>' . e($c->marketer->name ?? '') . '</td>'
                . '<td>' . e($c->marketer_commission) . '</td>'
                . '<td>' . e($c->initial_deposit) . '</td>'
                . '<td>' . e($c->total_commission) . '</td>'
                . '</tr>';
        }

        return "<!doctype html><html><head><meta charset='utf-8'>
            <style>
              body { font-family: 'Tajawal', DejaVu Sans, sans-serif; font-size: 10px; }
              h1 { color: #14C8A0; font-size: 16px; }
              table { width: 100%; border-collapse: collapse; margin-top: 10px; }
              th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
              th { background: #f0f0f0; font-weight: 700; }
            </style></head><body>
            <h1>Wafra Gulf — Commission Cards Export</h1>
            <p>Generated: " . now()->format('Y-m-d H:i') . "</p>
            <table>
              <thead><tr>
                <th>Account No</th><th>Month</th><th>Branch</th>
                <th>Broker</th><th>Broker Comm.</th>
                <th>Marketer</th><th>Marketer Comm.</th>
                <th>Initial Deposit</th><th>Total Comm.</th>
              </tr></thead>
              <tbody>{$rows}</tbody>
            </table>
          </body></html>";
    }
}
