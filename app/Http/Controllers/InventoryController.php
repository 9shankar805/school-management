<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryReportExport;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage inventory');
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index()
    {
        $totalAssets      = Asset::count();
        $availableAssets  = Asset::where('status', 'available')->count();
        $assignedAssets   = Asset::where('status', 'assigned')->count();
        $maintenanceAssets = Asset::where('status', 'maintenance')->count();

        $totalItems       = InventoryItem::count();
        $lowStockItems    = InventoryItem::active()->lowStock()->count();
        $outOfStockItems  = InventoryItem::where('quantity_in_stock', 0)->count();

        $totalSuppliers   = Supplier::count();
        $pendingPOs       = PurchaseOrder::whereIn('status', ['pending', 'approved', 'ordered'])->count();

        // Asset category breakdown
        $assetsByCategory = AssetCategory::withCount('assets')
            ->where('type', 'asset')->orderByDesc('assets_count')->get();

        // Low stock list
        $lowStockList = InventoryItem::active()->lowStock()
            ->with('category')
            ->orderBy('quantity_in_stock')
            ->take(10)->get();

        // Recent stock transactions
        $recentTransactions = StockTransaction::with(['inventoryItem', 'processedByUser'])
            ->latest('transacted_at')->take(10)->get();

        // Recent POs
        $recentPOs = PurchaseOrder::with('supplier')
            ->latest('order_date')->take(5)->get();

        // Warranty expiry alerts
        $warrantyAlerts = Asset::whereNotNull('warranty_expiry')
            ->where('warranty_expiry', '<=', now()->addDays(30))
            ->where('status', '!=', 'disposed')
            ->with('category')->take(8)->get();

        return view('inventory.dashboard', compact(
            'totalAssets', 'availableAssets', 'assignedAssets', 'maintenanceAssets',
            'totalItems', 'lowStockItems', 'outOfStockItems',
            'totalSuppliers', 'pendingPOs',
            'assetsByCategory', 'lowStockList', 'recentTransactions',
            'recentPOs', 'warrantyAlerts'
        ));
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function reportForm()
    {
        $categories = AssetCategory::orderBy('name')->get();
        $suppliers  = Supplier::active()->orderBy('name')->get();
        return view('inventory.reports', compact('categories', 'suppliers'));
    }

    public function reportExport(Request $request)
    {
        $data = $request->validate([
            'report_type' => 'required|in:assets,stock,suppliers,low_stock,transactions,purchase_orders',
            'format'      => 'required|in:pdf,excel',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'category_id' => 'nullable|integer',
        ]);

        $type     = $data['report_type'];
        $format   = $data['format'];
        $dateFrom = $data['date_from'] ?? null;
        $dateTo   = $data['date_to']   ?? null;
        $catId    = $data['category_id'] ?? null;

        $records = match ($type) {
            'assets' => Asset::with(['category','supplier','assignedUser'])
                ->when($catId, fn($q) => $q->where('category_id', $catId))
                ->orderBy('name')->get(),

            'stock' => InventoryItem::with(['category','supplier'])
                ->when($catId, fn($q) => $q->where('category_id', $catId))
                ->orderBy('name')->get(),

            'suppliers' => Supplier::withCount(['assets','inventoryItems','purchaseOrders'])
                ->orderBy('name')->get(),

            'low_stock' => InventoryItem::active()->lowStock()
                ->with(['category','supplier'])->orderBy('quantity_in_stock')->get(),

            'transactions' => StockTransaction::with(['inventoryItem','processedByUser'])
                ->when($dateFrom, fn($q) => $q->where('transacted_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('transacted_at', '<=', $dateTo . ' 23:59:59'))
                ->latest('transacted_at')->get(),

            'purchase_orders' => PurchaseOrder::with(['supplier','raisedBy'])
                ->when($dateFrom, fn($q) => $q->where('order_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('order_date', '<=', $dateTo))
                ->latest('order_date')->get(),
        };

        $title = ucwords(str_replace('_', ' ', $type)) . ' Report';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('inventory.reports-pdf', compact('records', 'type', 'title', 'dateFrom', 'dateTo'))
                      ->setPaper('a4', 'landscape');
            return $pdf->download("inventory-{$type}-report.pdf");
        }

        return Excel::download(
            new InventoryReportExport($records, $type),
            "inventory-{$type}-report.xlsx"
        );
    }
}
