<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $data = Notification::where('seen', false)
        ->orderBy('id', 'desc')
        ->get()
        ->groupBy('action');
        
        return response()->json([
            'success'=> true,
            'data'=> $data
        ],200);
    }

    public function seenMessage($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update([
            'seen' => true
        ]);
        return response()->json([
            'success' => true,
        ], 200);
    }
}
