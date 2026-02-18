<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Campus;
use App\Models\HrAnnouncement;
use App\Models\HrDepartment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_announcement.view']);

        $announcements = HrAnnouncement::query()
            ->with(['campus:id,code,name', 'department:id,name', 'creator:id,name'])
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('hrm.announcements.index', [
            'announcements' => $announcements,
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'departments' => HrDepartment::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_announcement.create']);

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'department_id' => ['nullable', 'exists:hr_departments,id'],
            'title' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string'],
            'audience_scope' => ['nullable', Rule::in(['all', 'campus', 'department', 'role'])],
            'publish_at' => ['nullable', 'date'],
            'expire_at' => ['nullable', 'date', 'after_or_equal:publish_at'],
            'channel_email' => ['nullable', 'boolean'],
            'channel_sms' => ['nullable', 'boolean'],
            'channel_whatsapp' => ['nullable', 'boolean'],
            'channel_in_app' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
        ]);

        HrAnnouncement::query()->create([
            'campus_id' => $validated['campus_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'audience_scope' => $validated['audience_scope'] ?? 'all',
            'publish_at' => $validated['publish_at'] ?? null,
            'expire_at' => $validated['expire_at'] ?? null,
            'channel_email' => (bool) ($validated['channel_email'] ?? false),
            'channel_sms' => (bool) ($validated['channel_sms'] ?? false),
            'channel_whatsapp' => (bool) ($validated['channel_whatsapp'] ?? false),
            'channel_in_app' => (bool) ($validated['channel_in_app'] ?? true),
            'status' => $validated['status'] ?? 'draft',
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Announcement saved.');
    }

    public function publish(Request $request, HrAnnouncement $announcement): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_announcement.publish']);

        $announcement->update([
            'status' => 'published',
            'publish_at' => $announcement->publish_at ?? now(),
        ]);

        return back()->with('status', 'Announcement published.');
    }
}

