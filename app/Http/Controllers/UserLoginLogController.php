<?php

namespace App\Http\Controllers;

use App\Models\UserLoginLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserLoginLogController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        if ($request->ajax()) {
            $query = UserLoginLog::query()->with('user');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user', fn (UserLoginLog $log) => optional($log->user)->name ?? 'Unknown')
                ->addColumn('email', fn (UserLoginLog $log) => optional($log->user)->email ?? 'N/A')
                ->editColumn('action', fn (UserLoginLog $log) => ucfirst($log->action))
                ->editColumn('ip_address', fn (UserLoginLog $log) => $log->ip_address ?? 'N/A')
                ->editColumn('location', fn (UserLoginLog $log) => $log->location ?? 'N/A')
                ->editColumn('user_agent', fn (UserLoginLog $log) => $log->user_agent ?? 'N/A')
                ->editColumn('logged_at', fn (UserLoginLog $log) => optional($log->logged_at)->format('d-M-Y H:i') ?? 'N/A')
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('email', function ($query, $keyword) {
                    $query->whereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('email', 'like', "%{$keyword}%");
                    });
                })
                ->make(true);
        }

        return view('login_logs.index');
    }
}
