<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
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
                return '
                    <a href="' . route('admin.orders.edit', $row->id) . '"  class="btn btn-sm btn-warning">
                        Edit
                    </a>
                ';
            })
            ->editColumn('status', function ($row) {
                return ucfirst($row->status);
            })
            ->rawColumns(['action'])
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
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'user_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'order_no' => 'required|string|max:100|unique:orders,order_no,' . $order->id,
            'stitch_for_name' => 'nullable|string|max:255',
            'phone_no' => 'nullable|string|max:20',
            'height' => 'nullable|string|max:50',
            'body_weight' => 'nullable|string|max:50',
            'shoes_size' => 'nullable|string|max:50',
            'front_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'side_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'back_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'neck' => 'nullable|string|max:50',
            'chest' => 'nullable|string|max:50',
            'shoulder' => 'nullable|string|max:50',
            'sleeve_length' => 'nullable|string|max:50',
            'waist' => 'nullable|string|max:50',
            'additional_requirement' => 'nullable|string',
            'status' => 'nullable|in:pending,complete',

            'category_ids' => 'nullable|array',
            'category_ids.*' => 'nullable|exists:categories,id',
            'stitch_master_ids' => 'nullable|array',
            'stitch_master_ids.*' => 'nullable|exists:users,id',
            'stitch_statuses' => 'nullable|array',
            'stitch_statuses.*' => 'nullable|in:trial_ready,pending,complete',
        ]);

        $categoryIds = $request->input('category_ids', []);
        $stitchMasterIds = $request->input('stitch_master_ids', []);
        $stitchStatuses = $request->input('stitch_statuses', []);

        $selectedCategoryIds = array_values(array_filter($categoryIds, function ($v) {
            return !empty($v);
        }));

        $defaultStitchByCategory = [];
        if (!empty($selectedCategoryIds)) {
            $categoriesWithStitches = Category::query()
                ->whereIn('id', $selectedCategoryIds)
                ->with(['stitches' => function ($q) {
                    $q->where('role', 'stitch')->select('users.id', 'users.full_name');
                }])
                ->get(['id']);

            foreach ($categoriesWithStitches as $cat) {
                $defaultStitchByCategory[$cat->id] = $cat->stitches->first()?->id;
            }
        }

        $assignments = [];
        foreach ($categoryIds as $i => $categoryId) {
            if (empty($categoryId)) {
                continue;
            }

            $stitchMasterId = $stitchMasterIds[$i] ?? null;
            if (empty($stitchMasterId)) {
                $stitchMasterId = $defaultStitchByCategory[$categoryId] ?? null;
            }

            if (empty($stitchMasterId)) {
                continue;
            }

            $assignments[] = [
                'category_id' => (int) $categoryId,
                'stitch_master_id' => (int) $stitchMasterId,
                'stitch_status' => $stitchStatuses[$i] ?? 'pending',
            ];
        }

        DB::beginTransaction();
        try {
            $this->handlePhotos($request, $validated, $order);

            if (!empty($assignments)) {
                $validated['category_id'] = $assignments[0]['category_id'];
                $validated['stitch_master_id'] = $assignments[0]['stitch_master_id'];
                $validated['stitch_status'] = $assignments[0]['stitch_status'];
            }

            $order->update($validated);

            $order->categoryStitchItems()->delete();

            foreach ($assignments as $item) {
                OrderCategoryStitch::create([
                    'order_id' => $order->id,
                    'category_id' => $item['category_id'],
                    'stitch_master_id' => $item['stitch_master_id'],
                    'stitch_status' => $item['stitch_status'],
                ]);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
