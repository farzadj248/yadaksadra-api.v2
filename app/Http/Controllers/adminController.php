<?php

    namespace App\Http\Controllers;

    use App\Helper\EventLogs;
    use Illuminate\Support\Facades\Date;
    use App\Helper\Sms;
use App\Http\Resources\CommonResources;
use App\Models\Admin;
    use App\Models\UsersOtp;
    use Carbon\Carbon;
    use App\Models\AdminRoles;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use Config;
    use Tymon\JWTAuth\Exceptions\JWTException;
    use Tymon\JWTAuth\Facades\JWTAuth;
    use Symfony\Component\HttpFoundation\Response;

    class adminController extends Controller
    {
        public function login(Request $request)
        {
            $credentials = $request->only('user_name', 'password');

            $exp = Carbon::now()->addDays(7)->timestamp;

            try {
                if (! $token = auth()->guard('admin')->attempt($credentials, ['exp' => $exp])) {
                    return response()->json(['message' => 'نام کاربری یا کلمه عبور نامعتبر است.'], 400);
                }
            } catch (JWTException $e) {
                return response()->json(['message' => 'خطا سرور!'], 500);
            }

            $admin=Admin::select('id','personnel_code','user_name','first_name','last_name','avatar','email','avatar','status','roles')
            ->where("user_name",$request->user_name)->first();

            if($admin->status==0){
                return response()->json([
                    'message' => 'حساب کاربری شما غیرفعال است.',
                ], 401);
            }

            $claims=$this->explodeRoles($admin->roles);

            EventLogs::addToLog([
                'subject' => "حساب کاربری",
        	    'body' => "ورود به حساب کاربری",
        	    'user_id' => $admin->id,
        	    'user_name'=> $admin->first_name." ".$admin->last_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'عملیات با موفقیت انجام شد.',
                "data" => [
                    "admin" =>[
                        "information" => $admin,
                        "claims" => $claims
                    ],
                    "token"=> $token
                ]
            ], Response::HTTP_OK);
        }

        public function register(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'user_name' => 'required|string|max:255|unique:admins',
                'email' => 'required|string|max:255|unique:admins',
                'mobile_number' => 'required|string|max:11|unique:admins',
                'password' => 'required|string|min:6|confirmed',
                'roles' => 'required|string'
            ]);

            $claims=$this->explodeRoles($request->roles);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => $validator->errors()->toJson()
                ], Response::HTTP_OK);
            }

            $last_admin = Admin::latest()->first();
            if($last_admin){
                $personnel_code = (int)$last_admin->personnel_code + 1;
            }else{
               $personnel_code =  1000;
            }

            $admin = Admin::create([
                'personnel_code' => $personnel_code,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_name' => $request->user_name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'password' => Hash::make($request->password),
                'roles' => $request->roles
            ]);

            $exp = Carbon::now()->addDays(7)->timestamp;
            $token = JWTAuth::customClaims(['exp' => $exp])->fromUser($admin);

            // $input_data=array("welcome" => $admin);
            // $res=Sms::sendWithPatern($request->mobile_number,$input_data,"gjnvwe45ui");

            EventLogs::addToLog([
                'subject' => "ایجاد پرسنل جدید",
        	    'body' => $admin,
        	    'user_id' => $admin->id,
        	    'user_name'=> $admin->first_name." ".$admin->last_name,
            ]);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                "data" => [
                    "admin" =>[
                        "information" => [
                            "personnel_code" => $admin->personnel_code,
                            "first_name" => $admin->first_name,
                            "last_name" => $admin->last_name,
                            "user_name" => $admin->user_name,
                            "email" => $admin->email,
                            "avatar" => $admin->avatar
                        ],
                        "claims" => $claims
                    ],
                    "tokens"=> [
                        "access_token"=> [
                            "value"=> $token,
                            "token_type"=> "Bearer",
                            "expires_in"=> $exp
                        ],
                    ]
                ]
            ], Response::HTTP_OK);
        }

        public function getAuthenticatedUser()
        {
            try {

                if (! $admin = JWTAuth::parseToken()->authenticate()) {
                    return response()->json(['admin_not_found'], 404);
                }

            } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

                return response()->json(['token_expired'], $e->getStatusCode());

            } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

                return response()->json(['token_invalid'], $e->getStatusCode());

            } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

                return response()->json(['token_absent'], $e->getStatusCode());

            }

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                "data" => [
                    "user" => $admin
                ]
            ], Response::HTTP_OK);
        }

        public function sendActivationCode(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'mobile_number' => 'required|size:11',
            ]);

            if($validator->fails()){
                return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
            }

            $admin = Admin::where("mobile_number",$request->mobile_number)->exists();
            if(!$admin) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 202,
                    'message' => 'کاربر مورد نظر یافت نشد.',
                ], Response::HTTP_OK);
            }

            $otp = $this->generateOtp();
            $exp = Carbon::now()->addMinute(2);
            $UsersOtp = UsersOtp::where("mobile_number",$request->mobile_number)
            ->where("role","admin")
            ->first();

            if($UsersOtp){
                $diff = $this->getDifference($UsersOtp->expire_date);
                if($diff['isValid'] == true){
                    return response()->json([
                        'success' => true,
                        'statusCode' => 201,
                        'message' => 'عملیات با موفقیت انجام شد.',
                        "data" => [
                            "exp" => $diff['exp'],
                        ]
                    ], Response::HTTP_OK);
                }

                $UsersOtp->update([
                    'otp' => $otp,
                    'expire_date' => $exp
                ]);
            }else{
                UsersOtp::create([
                    'mobile_number' => $request->mobile_number,
                    'otp' => $otp,
                    'expire_date' => $exp,
                    'role' => 'admin'
                ]);
            }

            $input_data=array("otp" => $otp);
            $res=Sms::sendWithPatern($request->mobile_number,"qm47qhvmhujlvjg",$input_data);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                "data" => [
                    "exp" => 120,
                ]
            ], Response::HTTP_OK);
        }

        public function validateActivationCode(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'mobile_number' => 'required|size:11',
                'otp' => 'required',
            ]);

            if($validator->fails()){
                return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
            }

            $admin = Admin::where("mobile_number",$request->mobile_number)->exists();
            if(!$admin) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 202,
                    'message' => 'کاربر مورد نظر یافت نشد.',
                ], Response::HTTP_OK);
            }

            $otp = $this->generateOtp();
            $exp = Carbon::now()->addMinute(2);
            $UsersOtp = UsersOtp::where("mobile_number",$request->mobile_number)
            ->where("role","admin")
            ->first();

            if(!$UsersOtp){
                return response()->json([
                    'success' => false,
                    'statusCode' => 202,
                    'message' => 'عملیات با خطا مواجه شد.',
                ], Response::HTTP_OK);
            }

            if($UsersOtp->otp == $request->otp){
                $diff = $this->getDifference($UsersOtp->expire_date);
                if($diff['isValid'] == true){
                    return response()->json([
                        'success' => true,
                        'statusCode' => 201,
                        'message' => 'عملیات با موفقیت انجام شد.',
                        'date' => [
                            "exp" => $diff['exp']
                        ]
                    ], Response::HTTP_OK);
                }else{
                    return response()->json([
                        'success' => false,
                        'statusCode' => 202,
                        'message' => 'کد فعالسازی نامعتبر است.',
                    ], Response::HTTP_OK);
                }
            }else{
                return response()->json([
                    'success' => false,
                    'statusCode' => 202,
                    'message' => 'کد فعالسازی صحیح نیست.',
                ], Response::HTTP_OK);
            }
        }

        public function changePassword(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'mobile_number' => 'required|string|max:11',
                'password' => 'required|string|min:6|confirmed',
                'otp' => 'required',
            ]);

            if($validator->fails()){
                return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
            }

            $admin = Admin::where("mobile_number",$request->mobile_number);
            $UsersOtp = UsersOtp::where("mobile_number",$request->mobile_number)
            ->where("otp",$request->otp)
            ->where("role","admin")->exists();

            if(!$admin->exists()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 202,
                    'message' => 'کاربر مورد نظر یافت نشد.',
                ], Response::HTTP_OK);
            }

            if(!$UsersOtp) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 202,
                    'message' => 'عملیات با خطا مواجه شد.',
                ], Response::HTTP_OK);
            }

            $admin->first()->update([
                'password' => Hash::make($request->password),
            ]);

            $exp = \Carbon\Carbon::now()->addDays(7)->timestamp;
            $token = JWTAuth::customClaims(['exp' => $exp])->fromUser($admin->first());

            $updatedAdmin = $admin->first();
            $claims=$this->explodeRoles($updatedAdmin->roles);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                "data" => [
                    "admin" =>[
                        "information" => $updatedAdmin,
                        "claims" => $claims
                    ],
                    "tokens"=> [
                        "access_token"=> [
                            "value"=> $token,
                            "token_type"=> "Bearer",
                            "expires_in"=> $exp
                        ],
                    ],
                ]
            ], Response::HTTP_OK);
        }

        public function recoveryPassword(Request $request)
        {
            $credentials = $request->only('mobile_number', 'password');

            $exp = \Carbon\Carbon::now()->addDays(7)->timestamp;

            try {
                if (! $token = JWTAuth::attempt($credentials, ['exp' => $exp])) {
                    return response()->json(['error' => 'invalid_credentials'], 400);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }

            $admin=Admin::select('personnel_code','user_name','first_name','last_name','avatar','email','avatar','status','roles')
            ->where("user_name",$request->get("user_name"))->first();

            $claims=$this->explodeRoles($admin->roles);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                "data" => [
                    "admin" =>[
                        "information" => $admin,
                        "claims" => $claims
                    ],
                    "tokens"=> [
                        "access_token"=> [
                            "value"=> $token,
                            "token_type"=> "Bearer",
                            "expires_in"=> $exp
                        ],
                    ],
                ]
            ], Response::HTTP_OK);
        }

        public function updatePasswd(Request $request)
        {
            $admin = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'password' => 'required|confirmed|min:6',
                'old_pass' => 'required|min:6',
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => $validator->errors()
                ], Response::HTTP_OK);
            }

            if(!$admin || !Hash::check($request->old_pass, $admin->password)){
        		return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'title'=> "کلمه عبور فعلی نادرست است."
                    ],
                    'data'=> $admin
                ], Response::HTTP_OK);
        	}

            $admin->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.'
            ], Response::HTTP_OK);
        }

        public function logout(Request $request)
        {
            $validator = Validator::make($request->only('token'), [
                'token' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => "توکن الزامی است."], 422);
            }

            try {
                JWTAuth::invalidate($request->token);
                return response()->json([
                    'success'=> true,
                    'message' => 'عملیات با موفقیت انجام شد.'
                ],200);
            } catch (JWTException $exception) {
                return response()->json([
                    'message' => 'خطا!'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        public function explodeRoles($admin_roles)
        {
            $claims=[];

            $roles=json_decode($admin_roles, true);

            foreach ($roles as $k => $v) {
                $claim=AdminRoles::select("access_leveles")->where("en_title",$v["value"])->first();
                if($claim){
                    $c1 = json_decode($claim->access_leveles, true);
                    foreach ($c1 as $k2 => $v2) {
                        array_push($claims,$v2["value"]);
                    }
                }
            }

            return array_unique($claims);
        }

        function generateOtp(){
            return rand(11111,99999);
        }

        public function getDifference($exp) {
            $now = Carbon::now();
            $created_at=$now->toDateTimeString();

            $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $exp);
            $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $created_at);

            if($date1->eq($date2)){
                return [
                    'isValid' => true,
                    'exp' => $date1->diffInSeconds($date2)
                ];
            }else{
                if($date1->gt($date2)){
                    return [
                        'isValid' => true,
                        'exp' => $date1->diffInSeconds($date2)
                    ];
                }else{
                    return [
                        'isValid' => false,
                        'exp' => 0
                    ];
                }
            }
        }

        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function index(Request $request)
        {
            $data=Admin::where("id", '!=',1)
            ->whereRaw('concat(first_name,personnel_code,last_name) like ?', "%{$request->q}%")
            ->where('id','!=',auth()->guard('admin')->user()->id)
            ->paginate(10);
            return CommonResources::collection($data);
        }

        /**
         * Store a newly created resource in storage.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            $admin = auth()->guard('admin')->user();

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'user_name' => 'required|string|max:255|unique:admins',
                'email' => 'required|email|unique:admins',
                'mobile_number' => 'required|string|max:11|unique:admins',
                'password' => 'required|string|min:6|confirmed',
                'national_code'=> 'required|string|min:8|unique:admins',
                'avatar' => 'required|string',
                'birth_date' => 'required||date_format:Y-m-d',
                'gender' => 'required|in:Male,FeMale',
                'role_id' => 'required|exists:admin_roles,id',
                'status' => 'required|string',
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ], 422);
            }

            $personel_code = $this->personel_code();

            $new_admin = Admin::create([
                'personnel_code' => $personel_code,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_name' => $request->user_name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'national_code' => $request->national_code,
                'role_id' => $request->role_id,
                'avatar' => $request->avatar,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'status' => $request->status,
                'password' => Hash::make($request->password)
            ]);

            EventLogs::addToLog([
                'subject' => "ایجاد پرسنل جدید:". $request->first_name.' '. $request->last_name." توسط ". $admin->first_name.' '. $admin->last_name,
        	    'body' => $new_admin,
        	    'user_id' => $admin->id,
        	    'user_name'=> $admin->first_name." ".$admin->last_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'عملیات با موفقیت انجام شد.',
            ], Response::HTTP_OK);
        }

        function personel_code(){
            return rand(111111,999999);
        }

        /**
         * Display the specified resource.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function show(Admin $admin)
        {
            return response()->json([
                'success' => true,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $admin
            ], Response::HTTP_OK);
        }

        public function profile()
        {
            $admin = auth()->guard()->user();
            return response()->json([
                'success' => true,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $admin
            ], Response::HTTP_OK);
        }

        public function updateProfile(Request $request)
        {
            $admin = auth()->guard('admin')->user();

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'user_name' => 'required|string|max:255|unique:admins,user_name,' . $admin->id,
                'mobile_number' => 'required|string|max:11|unique:admins,mobile_number,' . $admin->id,
                'password' => 'nullable|string|min:6|confirmed',
                'national_code' => 'required|string|min:8|unique:admins,national_code,' . $admin->id,
                'avatar' => 'required|string',
                'birth_date' => 'required||date_format:Y-m-d',
                'gender' => 'required|in:Male,FeMale'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ], 422);
            }

            $admin->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_name' => $request->user_name,
                'mobile_number' => $request->mobile_number,
                'national_code' => $request->national_code,
                'avatar' => $request->avatar,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'password' => !empty($request->password) ? Hash::make($request->password) : $admin->password
            ]);

            return response()->json([
                'success' => true,
                'message' => 'عملیات با موفقیت انجام شد.',
            ], Response::HTTP_OK);
        }

        /**
         * Update the specified resource in storage.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function update(Request $request, Admin $admin)
        {
            $current_admin = auth()->guard('admin')->user();

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'user_name' => 'required|string|max:255|unique:admins,user_name,' . $admin->id,
                'mobile_number' => 'required|string|max:11|unique:admins,mobile_number,' . $admin->id,
                'password' => 'nullable|string|min:6|confirmed',
                'national_code' => 'required|string|min:8|unique:admins,national_code,' . $admin->id,
                'avatar' => 'required|string',
                'birth_date' => 'required||date_format:Y-m-d',
                'gender' => 'required|in:Male,FeMale',
                'role_id' => 'required|exists:admin_roles,id',
                'status' => 'required|string',
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ], 422);
            }

            $admin->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_name' => $request->user_name,
                'mobile_number' => $request->mobile_number,
                'national_code' => $request->national_code,
                'role_id' => $request->role_id,
                'avatar' => $request->avatar,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'status' => $request->status,
                'password' => !Empty($request->password) ? Hash::make($request->password) : $admin->password
            ]);

            EventLogs::addToLog([
                'subject' => "ویرایش پرسنل: " . $admin->first_name . ' ' . $admin->last_name . " توسط " . $current_admin->first_name . ' ' . $current_admin->last_name,
                'body' => $admin,
                'user_id' => $current_admin->id,
                'user_name' => $current_admin->first_name . " " . $current_admin->last_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $admin
            ], Response::HTTP_OK);
        }

        /**
         * Remove the specified resource from storage.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function destroy(Admin $admin)
        {
            $current_admin = auth()->guard('admin')->user();

            if($admin->id==1){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => 'امکان حذف این پرسنل وجود ندارد.',
                    'data' => $admin
                ], Response::HTTP_OK);
            }

            $admin->delete();

            EventLogs::addToLog([
                'subject' => " حذف پرسنل ". $admin->first_name.' '. $admin->last_name,
        	    'body' => $admin,
        	    'user_id' => $current_admin->id,
        	    'user_name'=> $current_admin->first_name." ".$current_admin->last_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'عملیات با موفقیت انجام شد.'
            ], Response::HTTP_OK);
        }
    }
