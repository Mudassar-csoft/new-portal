<?php

namespace App\Http\Controllers;

use App\Models\UserLoginLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserLoginLogController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

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
                ->filter(function (Builder $query) use ($request): void {
                    $keyword = trim((string) data_get($request->input('search', []), 'value', ''));

                    if ($keyword === '') {
                        return;
                    }

                    $like = $this->toSqlLikePattern($keyword);

                    $query->where(function (Builder $searchQuery) use ($like): void {
                        $searchQuery
                            ->where('action', 'like', $like)
                            ->orWhere('ip_address', 'like', $like)
                            ->orWhere('location', 'like', $like)
                            ->orWhere('user_agent', 'like', $like)
                            ->orWhereHas('user', function (Builder $userQuery) use ($like): void {
                                $userQuery
                                    ->where('name', 'like', $like)
                                    ->orWhere('email', 'like', $like);
                            });
                    });
                })
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

    private function toSqlLikePattern(string $value): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value) . '%';
    }
}
