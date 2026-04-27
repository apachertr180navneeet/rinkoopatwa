<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Mail, DB, Hash, Validator, Session, File,Exception;
use Yajra\DataTables\Facades\DataTables;

class StitchController extends Controller
{
    public function index()
    {
        return view('admin.stitch.index');
    }

    public function getAll(Request $request)
    {
        $query = User::query()->where('role','stitch')->whereNull('deleted_at');

        // Filter by name
        if ($request->name) {
            $query->where('full_name', 'like', '%' . $request->name . '%');
        }

        // Global search
        if ($request->search_value) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search_value . '%')
                  ->orWhere('email', 'like', '%' . $request->search_value . '%')
                  ->orWhere('phone', 'like', '%' . $request->search_value . '%');
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()

            // Status Toggle
            ->addColumn('status', function ($row) {
                $checked = $row->status == 'active' ? 'checked' : '';
                return '<label class="switch">
                            <input type="checkbox" class="statusToggle" data-id="'.$row->id.'" '.$checked.'>
                            <span class="slider round"></span>
                        </label>';
            })

            // Action Buttons
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-warning editBtn" data-id="'.$row->id.'">Edit</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->id.'">Delete</button>
                ';
            })

            // Prevent DT_RowIndex error
            ->orderColumn('DT_RowIndex', function ($query, $order) {
                $query->orderBy('id', $order);
            })

            ->rawColumns(['status','action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'mobile' => 'required|digits:10',
            'email' => 'required|email|unique:users,email',

            'password' => 'required|min:6',
        ]);

        User::create([
            'full_name'     => $request->name,
            'phone'    => $request->mobile,
            'email'    => $request->email,
            'city'     => $request->city,
            'password' => Hash::make($request->password),

            // ✅ DEFAULT ROLE
            'role'     => 'stitch'
        ]);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return User::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        // Validation
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email',
            'mobile' => 'nullable|digits_between:10,15',
            'password' => 'nullable|min:6'
        ]);

        // Find user
        $user = User::findOrFail($id);

        // Update data array
        $data = [
            'full_name' => $request->name,
            'phone'     => $request->mobile,
            'email'     => $request->email,
            'city'      => $request->city,
        ];

        // ✅ Password only if provided
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        // Update user
        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data'    => $user
        ]);
    }

    public function changeStatus(Request $request)
    {
        $user = User::find($request->id);
        $user->status = $user->status == 'active' ? 'inactive' : 'active';
        $user->save();

        return response()->json(['success'=>true]);
    }

    public function delete($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['success'=>true]);
    }
}
