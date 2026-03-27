<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.category.index');
    }

    public function getAll(Request $request)
    {
        $query = Category::query()->whereNull('deleted_at');

        if ($request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->search_value) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search_value . '%');
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                $checked = $row->status === 'active' ? 'checked' : '';
                return '<label class="switch">
                            <input type="checkbox" class="statusToggle" data-id="' . $row->id . '" ' . $checked . '>
                            <span class="slider round"></span>
                        </label>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-warning editBtn" data-id="' . $row->id . '">Edit</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="' . $row->id . '">Delete</button>
                ';
            })
            ->orderColumn('DT_RowIndex', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'measurements' => 'nullable|string',
            'youtube_url' => 'nullable|url',
        ]);

        Category::create([
            'name' => $request->name,
            'status' => $request->status ?? 'active',
            'measurements' => $request->measurements,
            'youtube_url' => $request->youtube_url,
        ]);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return Category::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'measurements' => 'nullable|string',
            'youtube_url' => 'nullable|url',
        ]);

        $category->update([
            'name' => $request->name,
            'status' => $request->status ?? $category->status,
            'measurements' => $request->measurements,
            'youtube_url' => $request->youtube_url,
        ]);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function changeStatus(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->status = $category->status === 'active' ? 'inactive' : 'active';
        $category->save();

        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        Category::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Select2 data source for categories (active only).
     */
    public function select2(Request $request)
    {
        $search = $request->get('q');

        $query = Category::query()
            ->where('status', 'active')
            ->whereNull('deleted_at');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $results = $query
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'text' => $category->name,
                ];
            });

        return response()->json(['results' => $results]);
    }
}

