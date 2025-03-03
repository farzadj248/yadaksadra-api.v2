<?php

namespace App\Http\Controllers;

use App\Events\RealTimeMessageEvent;
use App\Http\Resources\CommonResources;
use App\Models\Admin;
use App\Models\Media;
use App\Models\Notification;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\TicketCategories;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ticketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data=Ticket::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'tickets.user_id');
        })
            ->select('tickets.*','users.first_name','users.last_name')
            ->where('tickets.subject', 'like', '%' . $request->q . '%')
            ->where("tickets.reply_id",0)
            ->with('category')
            ->paginate(10);

        return CommonResources::collection($data); 
    }

    public function getUserTicket(Request $request)
    {
        $user = auth()->user();

        $tickets=Ticket::where('user_id',$user->id)
            ->where("tickets.reply_id",0)
        ->where("status",$request->status)->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $tickets
        ], Response::HTTP_OK);
    }

    public function userSendTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reply_id' => 'required|numeric',
            'subject' => 'required|string',
            'body' => 'required|string',
            'category_id' => 'required|exists:ticket_categories,id',
            'status' => 'required|numeric',
            'priority' => 'required|numeric',
            'attaches.*' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user =  auth()->user();
        $admin = Admin::find(1);
        $category = TicketCategories::find($request->category_id);

        $last_ticket = Ticket::latest()->first();
        if ($last_ticket) {
            $ticket_code = (int)$last_ticket->ticket_code + 1;
        } else {
            $ticket_code =  1000;
        }

        $ticket = Ticket::create([
            'ticket_code' => $ticket_code,
            'reply_id' => $request->reply_id ?? '',
            'user_id' => $user->id,
            'sender' => ["id"=> $user->id,"fullName"=> $user->first_name.' '.$user->last_name,"email"=> $user->email],
            'receiver' => ["id" => $admin->id, "fullName" => $admin->first_name . ' ' . $admin->last_name, "email" => $admin->email],
            'subject' => $request->subject,
            'body' => $request->body,
            'category_id' => $category->id,
            'status' => $request->status,
            'priority' => $request->priority,
            'senderType'=> 2,
            'attaches' => $request->attaches
        ]);

        if ($request->reply_id != 0) {
            Ticket::where("id", $request->reply_id)
            ->update(["status" => $request->status]);
        }

        $notification = Notification::create([
            'action' => 'message',
            'message' => [
                'action' => 'ticket',
                'id' => $ticket_code,
                'userName' => $user->first_name . ' ' . $user->last_name,
                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                'message' => 'تیکت جدید دریافت شد.'
            ]
        ]);
        event(new RealTimeMessageEvent('messages', $notification));
        event(new RealTimeMessageEvent('tickets', $notification));

        return response()->json([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ticket
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reply_id' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string',
            'body' => 'required|string',
            'category_id' => 'required|exists:ticket_categories,id',
            'status' => 'required|numeric',
            'priority' => 'required|numeric',
            'attaches.*' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        $admin =  auth()->guard('admin')->user();
        $category = TicketCategories::find($request->category_id);

        $last_ticket = Ticket::latest()->first();
        if ($last_ticket) {
            $ticket_code = (int)$last_ticket->ticket_code + 1;
        } else {
            $ticket_code =  1000;
        }

        $ticket = Ticket::create([
            'ticket_code' => $ticket_code,
            'reply_id' => $request->reply_id ?? '',
            'user_id' => $user->id,
            'sender' => ["id" => $user->id, "fullName" => $user->first_name . ' ' . $user->last_name, "email" => $user->email],
            'receiver' => ["id" => $admin->id, "fullName" => $admin->first_name . ' ' . $admin->last_name, "email" => $admin->email],
            'subject' => $request->subject,
            'body' => $request->body,
            'category_id' => $category->id,
            'status' => $request->status,
            'priority' => $request->priority,
            'senderType' =>1,
            'attaches' => $request->attaches
        ]);

        if ($request->reply_id != 0) {
            Ticket::where("id", $request->reply_id)
                ->update(["status" => $request->status]);
        }

        return response()->json([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ticket
        ], Response::HTTP_OK);
    }

    public function upload_files($file)
    {
        $filenamewithextension = $file->getClientOriginalName();
        $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filenametostore = uniqid().'.'.$extension;

        $date = date('Y-m-d', time());

        if(!Storage::disk('ftp')->exists($date)){
            Storage::disk('ftp')->makeDirectory($date);
        }

        $isOk = Storage::disk('ftp')->put($date.'/'.$filenametostore, fopen($file, 'r+'));

        if($isOk){
            return [
                "name"=> $filenametostore,
                "size"=> 0,
                "type"=> $file->getMimeType(),
                "url"=> 'https://dl.yadaksadra.com/storage/'.$date.'/'.$filenametostore
            ];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        $children = Ticket::where("reply_id",$ticket->id)
        ->orWhere("id",$ticket->id)
        ->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'single' => $ticket,
                'childs' => $children
            ]
        ], Response::HTTP_OK);
    }
}
