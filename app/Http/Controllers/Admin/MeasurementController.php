<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Measurement;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MeasurementController extends Controller
{
    public function index()
    {
        return view('admin.measurement.index');
    }

    public function getAll(Request $request)
    {
        $query = Measurement::query()->whereNull('deleted_at');

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
            'name' => 'required|string|max:255|unique:measurements,name',
            'remark' => 'nullable|string',
            'video_link' => 'nullable|url',
        ]);

        Measurement::create([
            'name' => $request->name,
            'remark' => $request->remark,
            'video_link' => $request->video_link,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return Measurement::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $measurement = Measurement::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:measurements,name,' . $measurement->id,
            'remark' => 'nullable|string',
            'video_link' => 'nullable|url',
        ]);

        $measurement->update([
            'name' => $request->name,
            'remark' => $request->remark,
            'video_link' => $request->video_link,
            'status' => $request->status ?? $measurement->status,
        ]);

        return response()->json([
            'success' => true,
            'data' => $measurement,
        ]);
    }

    public function changeStatus(Request $request)
    {
        $measurement = Measurement::findOrFail($request->id);
        $measurement->status = $measurement->status === 'active' ? 'inactive' : 'active';
        $measurement->save();

        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        Measurement::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    public function select2(Request $request)
    {
        $search = $request->get('q');

        $query = Measurement::query()
            ->where('status', 'active')
            ->whereNull('deleted_at');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $results = $query
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($measurement) {
                return [
                    'id' => $measurement->id,
                    'text' => $measurement->name,
                    'remark' => $measurement->remark,
                    'video_link' => $measurement->video_link,
                ];
            });

        return response()->json(['results' => $results]);
    }
}
