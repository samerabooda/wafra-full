<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManagerNotification;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    // GET /api/notifications — recent list for the current user (marks them delivered)
    public function index(Request $request): JsonResponse
    {
        $uid = $request->user()->id;

        // Mark first delivery (tracker accuracy: "appeared to the manager")
        ManagerNotification::where('to_user_id', $uid)->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        $rows = ManagerNotification::with(['fromBranch:id,name_ar,name_en'])
            ->where('to_user_id', $uid)
            ->orderByDesc('created_at')->limit(30)
            ->get(['id','type','card_id','account_number','month','from_branch_id','title','body','read_at','acted_at','created_at']);

        $unread = ManagerNotification::where('to_user_id', $uid)->whereNull('read_at')->count();

        return response()->json(['success' => true, 'data' => $rows, 'unread' => $unread]);
    }

    // GET /api/notifications/unread-count — tiny payload for polling the bell
    public function unreadCount(Request $request): JsonResponse
    {
        $unread = ManagerNotification::where('to_user_id', $request->user()->id)
            ->whereNull('read_at')->count();
        return response()->json(['success' => true, 'unread' => $unread]);
    }

    // GET /api/notifications/inbox — mailbox: incoming + outgoing CC notifications
    //   • Finance Admin : sees ALL
    //   • Branch manager: incoming addressed to them / their branch
    //   • CC staff      : outgoing they triggered
    public function inbox(Request $request): JsonResponse
    {
        $user = $request->user();
        $uid  = $user->id;
        $isFA = $user->isFinanceAdmin();

        $q = DB::table('manager_notifications as n')
            ->leftJoin('users as fu',    'n.from_user_id',   '=', 'fu.id')
            ->leftJoin('users as tu',    'n.to_user_id',     '=', 'tu.id')
            ->leftJoin('branches as fb', 'n.from_branch_id', '=', 'fb.id')
            ->leftJoin('branches as tb', 'n.to_branch_id',   '=', 'tb.id');

        if (!$isFA) {
            $q->where(function ($w) use ($uid, $user) {
                $w->where('n.to_user_id', $uid)->orWhere('n.from_user_id', $uid);
                if ($user->branch_id) $w->orWhere('n.to_branch_id', $user->branch_id);
            });
        }

        $rows = $q->orderByDesc('n.created_at')->limit(200)->get([
            'n.id','n.account_number','n.month','n.title','n.body',
            'n.from_user_id','n.to_user_id','n.to_branch_id',
            'fu.name as from_user','tu.name as to_user',
            'fb.name_ar as from_branch','tb.name_ar as to_branch',
            'n.created_at','n.delivered_at','n.read_at','n.acted_at',
        ]);

        $data = $rows->map(function ($r) use ($uid, $isFA) {
            $arr = (array) $r;
            $arr['direction'] = $isFA ? 'all' : ((int)$r->from_user_id === (int)$uid ? 'out' : 'in');
            return $arr;
        });

        return response()->json(['success' => true, 'is_fa' => $isFA, 'data' => $data]);
    }

    // POST /api/notifications/{id}/read
    public function markRead(Request $request, int $id): JsonResponse
    {
        $n = ManagerNotification::where('to_user_id', $request->user()->id)->findOrFail($id);
        if (!$n->read_at) $n->update(['read_at' => now(), 'delivered_at' => $n->delivered_at ?? now()]);
        return response()->json(['success' => true]);
    }

    // POST /api/notifications/read-all
    public function readAll(Request $request): JsonResponse
    {
        ManagerNotification::where('to_user_id', $request->user()->id)->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    // POST /api/notifications/{id}/act — manager clicked through to the card
    public function act(Request $request, int $id): JsonResponse
    {
        $n = ManagerNotification::where('to_user_id', $request->user()->id)->findOrFail($id);
        $n->update([
            'acted_at' => $n->acted_at ?? now(),
            'read_at'  => $n->read_at ?? now(),
            'action'   => $request->action ?? 'viewed_card',
        ]);
        return response()->json(['success' => true, 'card_id' => $n->card_id]);
    }

    // GET /api/notifications/tracker — Finance Admin accuracy dashboard
    public function tracker(Request $request): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin())
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);

        $base = DB::table('manager_notifications as n');
        if ($from = $request->date_from) $base->whereDate('n.created_at', '>=', $from);
        if ($to   = $request->date_to)   $base->whereDate('n.created_at', '<=', $to);

        // Overall KPIs
        $tot = (clone $base)->selectRaw('
            COUNT(*) total,
            SUM(CASE WHEN delivered_at IS NOT NULL THEN 1 ELSE 0 END) delivered,
            SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) read_c,
            SUM(CASE WHEN acted_at IS NOT NULL THEN 1 ELSE 0 END) acted,
            SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) unread,
            ROUND(AVG(CASE WHEN read_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, read_at) END),1) avg_read_min,
            ROUND(AVG(CASE WHEN acted_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, acted_at) END),1) avg_act_min
        ')->first();

        // Per-branch breakdown
        $byBranch = (clone $base)->leftJoin('branches as b', 'n.to_branch_id', '=', 'b.id')
            ->selectRaw("
                COALESCE(b.name_ar,'غير محدد') nar, COALESCE(b.name_en,'Unassigned') nen,
                COUNT(*) total,
                SUM(CASE WHEN n.read_at IS NOT NULL THEN 1 ELSE 0 END) read_c,
                SUM(CASE WHEN n.acted_at IS NOT NULL THEN 1 ELSE 0 END) acted,
                SUM(CASE WHEN n.read_at IS NULL THEN 1 ELSE 0 END) unread,
                ROUND(AVG(CASE WHEN n.read_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, n.created_at, n.read_at) END),1) avg_read_min
            ")
            ->groupBy('b.name_ar', 'b.name_en')->orderByDesc('total')->get();

        // Recent rows (detailed timeline)
        $recent = DB::table('manager_notifications as n')
            ->leftJoin('users as u', 'n.to_user_id', '=', 'u.id')
            ->leftJoin('branches as b', 'n.to_branch_id', '=', 'b.id')
            ->when($from, fn($q) => $q->whereDate('n.created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('n.created_at', '<=', $to))
            ->orderByDesc('n.created_at')->limit(100)
            ->get([
                'n.id','n.account_number','n.month','u.name as manager','b.name_ar as branch',
                'n.created_at','n.delivered_at','n.read_at','n.acted_at',
            ]);

        return response()->json([
            'success' => true,
            'totals'  => [
                'total'        => (int)($tot->total ?? 0),
                'delivered'    => (int)($tot->delivered ?? 0),
                'read'         => (int)($tot->read_c ?? 0),
                'acted'        => (int)($tot->acted ?? 0),
                'unread'       => (int)($tot->unread ?? 0),
                'avg_read_min' => $tot->avg_read_min !== null ? (float)$tot->avg_read_min : null,
                'avg_act_min'  => $tot->avg_act_min !== null ? (float)$tot->avg_act_min : null,
            ],
            'by_branch' => $byBranch,
            'recent'    => $recent,
        ]);
    }
}
