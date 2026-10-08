<?php

namespace App\Http\Controllers;

use App\Support\HeaderNotificationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeaderNotificationController extends Controller
{
    public function __invoke(Request $request, HeaderNotificationResolver $resolver): JsonResponse
    {
        $payload = $resolver->resolve($request->user());

        return response()->json([
            'notification_total' => (int) ($payload['notificationTotal'] ?? 0),
            'menu_html' => view('layouts.partials.notification-menu', $payload)->render(),
        ]);
    }
}
