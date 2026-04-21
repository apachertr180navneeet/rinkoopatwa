<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Models\CategoryStitch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->with([
                'stitches' => function ($q) {
                    $q->where('role', 'stitch')
                        ->select('users.id', 'users.full_name');
                }
            ])
            ->get(['id', 'name']);

        // categoryId => [{ id, full_name }, ...]
        $categoryStitchMap = [];
        foreach ($categories as $category) {
            $categoryStitchMap[$category->id] = $category->stitches
                ->map(fn ($stitchMaster) => [
                    'id' => $stitchMaster->id,
                    'full_name' => $stitchMaster->full_name,
                ])
                ->values()
                ->all();
        }

        return view('admin.order.index', compact('categories', 'categoryStitchMap'));
    }

    public function getAll(Request $request)
    {
        $query = Order::query()->whereNull('deleted_at');

        if ($request->order_no) {
            $query->where('order_no', 'like', '%' . $request->order_no . '%');
        }

        if ($request->name) {
            $query->where('user_name', 'like', '%' . $request->name . '%');
        }

        if ($request->search_value) {
            $search = $request->search_value;
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', '%' . $search . '%')
                    ->orWhere('user_name', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        return DataTables::of($query)
        ->addColumn('action', function ($row) {
            return '<a href="' . route('admin.orders.edit', $row->id) . '" class="btn btn-sm btn-warning">Edit</a>';
        })
        ->editColumn('status', function ($row) {
            if ($row->status === 'pending') {
                return '<span class="badge bg-warning text-dark">Pending</span>';
            } elseif ($row->status === 'complete') {
                return '<span class="badge bg-success">Complete</span>';
            }
            return '<span class="badge bg-secondary">' . ucfirst($row->status) . '</span>';
        })
        ->rawColumns(['status', 'action'])   // ✅ allow HTML
        ->escapeColumns([])                  // ✅ FORCE disable escaping
        ->make(true);
    }

    public function edit($id)
    {
        $order = Order::findOrFail($id);

        $masters = User::query()->where('role','stitch')->whereNull('deleted_at')->get();

        $categoryIds = explode(',', $order->category_id);

        $categories = Category::whereIn('id', $categoryIds)->get();

        return view('admin.order.edit', compact('order', 'masters','categories'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,complete',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::findOrFail($id);

            // ✅ Update status
            if ($request->has('status')) {
                $order->status = $request->status;
            }

            $order->save();

            DB::commit();

            // ✅ Redirect to index route
            return redirect()->route('admin.orders.index')
                ->with('success', 'Order status updated successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update order')
                ->withInput();
        }
    }

    public function assignMaster(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'category_id' => 'required',
            'master_id' => 'required',
        ]);

        CategoryStitch::updateOrCreate(
            [
                'order_id' => $request->order_id,
                'category_id' => $request->category_id,
            ],
            [
                'stitch_id' => $request->master_id,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Master Assigned Successfully'
        ]);
    }
}
