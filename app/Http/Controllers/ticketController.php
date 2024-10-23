<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\TicketCategories;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
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
        $tickets=Ticket::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'tickets.user_id');
        })
            ->select('tickets.*','users.first_name','users.last_name')
            ->where('tickets.subject', 'like', '%' . $request->q . '%')
            ->where("tickets.reply_id",0)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $tickets
        ], Response::HTTP_OK);
    }

    public function getUserTicket(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $tickets=Ticket::where('user_id',$user->id)
            ->where("tickets.reply_id",0);

        $res = $tickets->where("status",$request->status)->paginate(10);


        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $res
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
            'user_id' => 'required|numeric',
            'sender' => 'required|string',
            'receiver' => 'required|string',
            'subject' => 'required|string',
            'body' => 'required|string',
            'category_id' => 'required|numeric',
            'category_title' => 'required|string',
            'status' => 'required|numeric',
            'priority' => 'required|numeric',
            'senderType' => 'numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if(!TicketCategories::where("id",$request->category_id)->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => [
                    'title'=> 'بخش یافت نشد.'
                ]
            ], Response::HTTP_OK);
        }

        $attaches=array();

        foreach($request->allFiles() as $image){
            $attaches[] = $this->upload_files($image);
        }

        $last_ticket = Ticket::latest()->first();
        if($last_ticket){
            $ticket_code = (int)$last_ticket->ticket_code + 1;
        }else{
           $ticket_code =  1000;
        }

        $ticket = Ticket::create([
            'ticket_code' => $ticket_code,
            'reply_id' => $request->reply_id,
            'user_id' => $request->user_id,
            'sender' => $request->sender,
            'receiver' => $request->receiver,
            'subject' => $request->subject,
            'body' => $request->body,
            'category_id' => $request->category_id,
            'category_title' => $request->category_title,
            'status' => $request->status,
            'priority' => $request->priority,
            'attaches' => json_encode($attaches),
            'senderType' => $request->senderType
        ]);

        if($request->reply_id!=0){
            $mainTicket=Ticket::where("id",$request->reply_id)->first();
            $mainTicket->update(["status"=>$request->status]);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
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
