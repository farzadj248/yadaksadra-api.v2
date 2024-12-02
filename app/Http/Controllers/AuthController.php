<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use App\Models\AffiliateHistory;
use App\Models\News;
use App\Models\UsersAddress;
use App\Models\Discounts;
use App\Models\DepositRequests;
use App\Models\InvitationLinks;
use App\Helper\Sms;
use App\Models\User;
use App\Models\UsersOtp;
use App\Models\Orders;
use App\Models\ArticleComments;
use App\Models\NewsComments;
use App\Models\ProductComments;
use App\Models\Transactions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

use App\Http\Requests\Auth\UserLoginRequest;
use App\Http\Requests\Auth\UserRegisterRequest;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function checkLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|numeric',
            'password' => 'sometimes|required|min:6',
            'FirstName' => 'sometimes|required|string|max:255',
            'LastName' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $mobile_number = $request->mobile_number;
        $password = $request->password;
        $firstName = $request->FirstName;
        $lastName = $request->LastName;

        $user = User::where('mobile_number', $mobile_number)->first();

        // Case 1: Mobile number and password provided
        if ($mobile_number && $password) {
            if ($user) {
                // Send OTP for login if user exists
                $otp = $this->sendOtp($mobile_number);
                return $this->responseWithOtp('OTP sent for login.', $otp, false);
            }
            // New user registration if FirstName and LastName are provided
            if ($firstName && $lastName) {
                $otp = $this->sendOtp($mobile_number);
                return $this->responseWithOtp('OTP sent for registration.', $otp, true);
            }

            // User not found and insufficient data for registration
            return $this->invalidRequestResponse('User not found. Provide FirstName and LastName to register.');
        }

        // Case 2: Only mobile number provided and user exists
        if ($mobile_number && !$password && $user) {
            $otp = $this->sendOtp($mobile_number);
            return $this->responseWithOtp('OTP sent for login.', $otp, false);
        }

        // Default response for unhandled cases
        return $this->invalidRequestResponse('Invalid request.');
    }

    /**
     * Generate and send OTP for the given mobile number.
     *
     * @param string $mobile_number
     * @return string Generated OTP
     */
    private function sendOtp($mobile_number)
    {
        $otp = $this->generateOtp();
        $exp = Carbon::now()->addMinutes(2);

        $UsersOtp = UsersOtp::firstOrNew(['mobile_number' => $mobile_number]);
        $UsersOtp->fill([
            'otp' => $otp,
            'expire_date' => $exp,
            'repeat' => $UsersOtp->repeat + 1 ?? 1,
            'role' => $UsersOtp->exists ? $UsersOtp->role : 'user',
        ])->save();

        Sms::sendWithPatern($mobile_number, 'qm47qhvmhujlvjg', ['otp' => $otp]);

        return $otp;
    }

    /**
     * Build a JSON response with OTP data.
     *
     * @param string $message
     * @param string $otp
     * @return \Illuminate\Http\JsonResponse
     */
    private function responseWithOtp($message, $otp, $isNew)
    {
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => $message,
            'data' => [
                'otp' => $otp,
                'exp' => 120,
                'isNew' => $isNew
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Build a JSON response for invalid requests.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    private function invalidRequestResponse($message)
    {
        return response()->json([
            'success' => false,
            'statusCode' => 400,
            'message' => $message
        ], Response::HTTP_BAD_REQUEST);
    }


    // public function checkLogin(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'mobile_number' => 'required|numeric',
    //         'password' => 'sometimes|required|min:6',
    //         'FirstName' => 'sometimes|required|string|max:255',
    //         'LastName' => 'sometimes|required|string|max:255',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'statusCode' => 422,
    //             'message' => $validator->errors()
    //         ], Response::HTTP_OK);
    //     }

    //     $mobile_number = $request->mobile_number;
    //     $password = $request->password;
    //     $firstName = $request->FirstName;
    //     $lastName = $request->LastName;

    //     $isExist = User::where("mobile_number", $mobile_number)->first();

    //     // Case 1: Check if mobile_number and password exist in the request
    //     if ($mobile_number && $password) {
    //         if ($isExist) {
    //             // Validate the password
    //             $otp = $this->generateOtp();
    //             $exp = Carbon::now()->addMinutes(2);
    //             return response()->json([
    //                 'success' => true,
    //                 'statusCode' => 200,
    //                 'message' => 'OTP sent for Login.',
    //                 'data' => [
    //                     'otp' => $otp,
    //                     'exp' => 120
    //                 ]
    //             ], Response::HTTP_OK);
    //         } else {
    //             if ($firstName && $lastName) {
    //                 // Case 2: New user registration with mobile_number, password, FirstName, LastName
    //                 $otp = $this->generateOtp();
    //                 $exp = Carbon::now()->addMinutes(2);

    //                 UsersOtp::create([
    //                     'mobile_number' => $mobile_number,
    //                     'otp' => $otp,
    //                     'expire_date' => $exp,
    //                     'role' => 'user'
    //                 ]);

    //                 $input_data = ["otp" => $otp];
    //                 Sms::sendWithPatern($mobile_number, "qm47qhvmhujlvjg", $input_data);

    //                 return response()->json([
    //                     'success' => true,
    //                     'statusCode' => 201,
    //                     'message' => 'OTP sent for registration.',
    //                     'data' => [
    //                         'Register_OTP' => $otp,
    //                         'exp' => 120
    //                     ]
    //                 ], Response::HTTP_OK);
    //             }
    //         }
    //     }

    //     // Case 3: Only mobile_number provided and user exists
    //     if ($mobile_number && !$password && $isExist) {
    //         $otp = $this->generateOtp();
    //         $exp = Carbon::now()->addMinutes(2);

    //         $UsersOtp = UsersOtp::where("mobile_number", $mobile_number)->first();

    //         if ($UsersOtp) {
    //             $UsersOtp->update([
    //                 'otp' => $otp,
    //                 'expire_date' => $exp,
    //                 'repeat' => $UsersOtp->repeat + 1
    //             ]);
    //         } else {
    //             UsersOtp::create([
    //                 'mobile_number' => $mobile_number,
    //                 'otp' => $otp,
    //                 'expire_date' => $exp,
    //                 'role' => 'user'
    //             ]);
    //         }

    //         $input_data = ["otp" => $otp];
    //         Sms::sendWithPatern($mobile_number, "qm47qhvmhujlvjg", $input_data);

    //         return response()->json([
    //             'success' => true,
    //             'statusCode' => 201,
    //             'message' => 'OTP sent for Login.',
    //             'data' => [
    //                 'otp' => $otp,
    //                 'exp' => 120
    //             ]
    //         ], Response::HTTP_OK);
    //     }

    //     // Default response for unhandled cases
    //     return response()->json([
    //         'success' => false,
    //         'statusCode' => 400,
    //         'message' => 'Invalid request.'
    //     ], Response::HTTP_BAD_REQUEST);
    // }


    // public function checkLogin(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'mobile_number' => 'required|numeric',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'statusCode' => 422,
    //             'message' => $validator->errors()
    //         ], Response::HTTP_OK);
    //     }

    //     $isExist = User::where("mobile_number", $request->mobile_number)->exists();
    //     $otp = $this->generateOtp();
    //     $exp = Carbon::now()->addMinute(2);

    //     $UsersOtp = UsersOtp::where("mobile_number", $request->mobile_number)->first();

    //     if ($UsersOtp) {
    //         $diff = $this->getDifference($UsersOtp->expire_date);
    //         if ($diff['isValid'] == true) {
    //             return response()->json([
    //                 'success' => true,
    //                 'statusCode' => 201,
    //                 'message' => 'عملیات با موفقیت انجام شد.',
    //                 "data" => [
    //                     'exp' => $diff['exp'],
    //                     'isNew' => $isExist == true ? false : true
    //                 ]
    //             ], Response::HTTP_OK);
    //         }

    //         $UsersOtp->update([
    //             'otp' => $otp,
    //             'expire_date' => $exp,
    //             'repeat' => $UsersOtp->repeat + 1
    //         ]);
    //     } else {
    //         $UsersOtp = UsersOtp::create([
    //             'mobile_number' => $request->mobile_number,
    //             'otp' => $otp,
    //             'expire_date' => $exp,
    //             'role' => 'user'
    //         ]);
    //     }

    //     $input_data = array("otp" => $otp);

    //     Sms::sendWithPatern($request->mobile_number, "qm47qhvmhujlvjg", $input_data);


    //     return response()->json([
    //         'success' => true,
    //         'statusCode' => 201,
    //         'message' => 'عملیات با موفقیت انجام شد.',
    //         "data" => [
    //             'exp' => 120,
    //             'isNew' => $isExist == true ? false : true
    //         ]
    //     ], Response::HTTP_OK);
    // }

    // public function login(Request $request)
    // {
    //     $credentials = $request->only('mobile_number', 'password');

    //     $exp = Carbon::now()->addDays(7)->timestamp;

    //     try {
    //         if (! $token = JWTAuth::attempt($credentials, ['exp' => $exp])) {
    //             return response()->json([
    //                 'success' => false,
    //                 'statusCode' => 422,
    //                 'message' => "اطلاعات کاربری نادرست است."
    //             ], Response::HTTP_OK);
    //         }
    //     } catch (JWTException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'statusCode' => 500,
    //             'message' => "کمی بعد تلاش کنید"
    //         ], Response::HTTP_OK);
    //     }

    //     $user = User::where("mobile_number", $request->get("mobile_number"))->first();

    //     return response()->json([
    //         'success' => true,
    //         'statusCode' => 201,
    //         'message' => 'عملیات با موفقیت انجام شد.',
    //         "data" => [
    //             "user" => [
    //                 "id" => $user->id,
    //                 "personnel_code" =>  $user->personnel_code,
    //                 "uuid" =>  $user->uuid,
    //                 "first_name" =>  $user->first_name,
    //                 "last_name" =>  $user->last_name,
    //                 "user_name" =>  $user->user_name,
    //                 "ceo_name" =>  $user->ceo_name,
    //                 "avatar" => $user->avatar,
    //                 "role" => $user->role,
    //                 "upgrade" => $user->status
    //             ],
    //             "tokens" => [
    //                 "access_token" => [
    //                     "value" => $token,
    //                     "token_type" => "Bearer",
    //                     "expires_in" => $exp
    //                 ],
    //             ],
    //             'uuid' => $user->uuid
    //         ]
    //     ], Response::HTTP_OK);
    // }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required',
            'mobile_number' => 'required|string|max:11|unique:users',
            'uuid' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $last_user = User::latest()->first();
        if ($last_user) {
            $personnel_code = (int)$last_user->personnel_code + 1;
        } else {
            $personnel_code =  1000;
        }

        $uuid = $request->uuid;

        $affId = null;
        if ($request->affId) {
            $affiliateHistory = AffiliateHistory::where("uuid", $request->uuid)
                ->where("affiliate_id", $request->affId)
                ->first();
            if ($affiliateHistory) {
                $aff_user = User::where("id", $request->affId)->first();
                if ($aff_user) {
                    $aff_user->increment('invited_affiliate_confirmed', 1);

                    if ($aff_user->invited_affiliate_pending > 0) {
                        $aff_user->decrement('invited_affiliate_pending', 1);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'statusCode' => 401,
                        'message' => "کد معرف یافت نشد"
                    ], Response::HTTP_OK);
                }

                $affId = $request->affId;
            } else {
                return response()->json([
                    'success' => false,
                    'statusCode' => 401,
                    'message' => "کد معرف یافت نشد"
                ], Response::HTTP_OK);
            }
        }

        $user = User::create([
            'personnel_code' => $personnel_code,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'uuid' => $uuid,
            'affiliate_id' => $affId,
            'avatar' => 'http://dl.yadaksadra.com/web/profile_user_avatar.png'
        ]);

        $address = UsersAddress::create([
            'user_id' => $user->id,
            'default' => 1,
        ]);


        UsersOtp::where("mobile_number", $user->mobile_number)->delete();

        $exp = Carbon::now()->addDays(7)->timestamp;
        // dd('23r33332');
        $token = JWTAuth::customClaims(['exp' => $exp])->fromUser($user);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            "data" => [
                "user" => [
                    "id" => $user->id,
                    "personnel_code" =>  $user->personnel_code,
                    "uuid" =>  $user->uuid,
                    "first_name" =>  $user->first_name,
                    "last_name" =>  $user->last_name,
                    "avatar" => $user->avatar,
                    "role" => $user->role,
                    "upgrade" => 1
                ],
                "tokens" => [
                    "access_token" => [
                        "value" => $token,
                        "token_type" => "Bearer",
                        "expires_in" => $exp
                    ],
                ],
                "uuid" => $uuid
            ]
        ], Response::HTTP_OK);
    }
    public function login(Request $request)
    {

      
        $credentials = $request->only('mobile_number', 'password');

        $exp = Carbon::now()->addDays(7)->timestamp;

        try {
            if (! $token = JWTAuth::attempt($credentials, ['exp' => $exp])) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => "اطلاعات کاربری نادرست است."
                ], Response::HTTP_OK);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'statusCode' => 500,
                'message' => "کمی بعد تلاش کنید"
            ], Response::HTTP_OK);
        }

        $user = User::where("mobile_number", $request->get("mobile_number"))->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            "data" => [
                "user" => [
                    "id" => $user->id,
                    "personnel_code" =>  $user->personnel_code,
                    "uuid" =>  $user->uuid,
                    "first_name" =>  $user->first_name,
                    "last_name" =>  $user->last_name,
                    "user_name" =>  $user->user_name,
                    "ceo_name" =>  $user->ceo_name,
                    "avatar" => $user->avatar,
                    "role" => $user->role,
                    "upgrade" => $user->status
                ],
                "tokens" => [
                    "access_token" => [
                        "value" => $token,
                        "token_type" => "Bearer",
                        "expires_in" => $exp
                    ],
                ],
                'uuid' => $user->uuid
            ]
        ], Response::HTTP_OK);
    }

    public function getAuthenticatedUser()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            "data" => [
                "user" => $user
            ]
        ], Response::HTTP_OK);
    }

    public function sendActivationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|size:11',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $otp = $this->generateOtp();
        $exp = Carbon::now()->addMinute(2);

        $UsersOtp = UsersOtp::where("mobile_number", $request->mobile_number)
            ->where("role", "user")
            ->first();

        if ($UsersOtp) {
            $diff = $this->getDifference($UsersOtp->expire_date);
            if ($diff['isValid'] == true) {
                return response()->json([
                    'success' => true,
                    'statusCode' => 201,
                    'message' => 'عملیات با موفقیت انجام شد.',
                    'data' => $diff['exp']
                ], Response::HTTP_OK);
            }

            $UsersOtp->update([
                'otp' => $otp,
                'expire_date' => $exp,
                'repeat' => $UsersOtp->repeat + 1
            ]);

            $input_data = array("otp" => $otp);
            Sms::sendWithPatern($request->mobile_number, "qm47qhvmhujlvjg", $input_data);
        } else {
            $UsersOtp = UsersOtp::create([
                'mobile_number' => $request->mobile_number,
                'otp' => $otp,
                'expire_date' => $exp,
                'role' => 'user'
            ]);

            $input_data = array("otp" => $otp);
            Sms::sendWithPatern($request->mobile_number, "qm47qhvmhujlvjg", $input_data);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            "data" => 120,
        ], Response::HTTP_OK);
    }

    public function validateActivationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|size:11',
            'otp' => 'sometimes|max:5',
            'password' => 'sometimes|min:6',
            'isNew' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $UsersOtp = UsersOtp::where("mobile_number", $request->mobile_number)
            ->where("role", "user")
            ->first();

        // if (!$UsersOtp) {
        //     return response()->json([
        //         'success' => false,
        //         'statusCode' => 422,
        //         'message' => [
        //             'otp' => 'عملیات با خطا مواجه شد.'
        //         ],
        //     ], Response::HTTP_OK);
        // }

        if ($request->isNew === "true") {
            if ($UsersOtp->otp == $request->otp) {
                $diff = $this->getDifference($UsersOtp->expire_date);
                if ($diff['isValid'] == true) {
                    return response()->json([
                        'success' => true,
                        'statusCode' => 201,
                        'message' => 'عملیات با موفقیت انجام شد.',
                        'isNew' => true
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => [
                            'otp' => 'کد فعالسازی نامعتبر است.'
                        ],
                    ], Response::HTTP_OK);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'otp' => 'کد فعالسازی نامعتبر است.'
                    ],
                ], Response::HTTP_OK);
            }
        } else {
        
          
            $user = User::where("mobile_number", $request->mobile_number);
            if (!$user->exists()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'user' => 'کاربر مورد نظر یافت نشد.'
                    ],
                ], Response::HTTP_OK);
            }

            if ($UsersOtp->otp == $request->otp) {
                $diff = $this->getDifference($UsersOtp->expire_date);
                if ($diff['isValid'] == true) {
                    if ($request->status == 2) {
             
                        // return response()->json([
                        //     'success' => true,
                        //     'statusCode' => 201,
                        //     'message' => 'عملیات با موفقیت انجام شد.'
                        // ], Response::HTTP_OK);


                        $exp = \Carbon\Carbon::now()->addDays(7)->timestamp;

                        $user = $user->first();

                        if (!$token = JWTAuth::fromUser($user, [$exp])) {
                            return response()->json([
                                'success' => false,
                                'statusCode' => 422,
                                'message' => [
                                    'user' => "عملیات ناموفق بود"
                                ]
                            ], Response::HTTP_OK);
                        }

                        $UsersOtp->delete();

                        return response()->json([
                            'success' => true,
                            'statusCode' => 201,
                            'message' => 'عملیات با موفقیت انجام شد.',
                            "data" => [
                                "user" => [
                                    "id" => $user->id,
                                    "personnel_code" =>  $user->personnel_code,
                                    "uuid" =>  $user->uuid,
                                    "first_name" =>  $user->first_name,
                                    "last_name" =>  $user->last_name,
                                    "user_name" =>  $user->user_name,
                                    "ceo_name" =>  $user->ceo_name,
                                    "avatar" => $user->avatar,
                                    "role" => $user->role,
                                ],
                                "tokens" => [
                                    "access_token" => [
                                        "value" => $token,
                                        "token_type" => "Bearer",
                                        "expires_in" => $exp
                                    ],
                                ]
                            ]
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'success' => false,
                            'statusCode' => 422,
                            'message' => [
                                'otp' => 'کد فعالسازی نامعتبر است.'
                            ],
                        ], Response::HTTP_OK);
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'otp' => 'کد فعالسازی نامعتبر است.'
                    ],
                ], Response::HTTP_OK);
            }
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string|max:11',
            'password' => 'required|string|min:6|confirmed',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $user = User::where("mobile_number", $request->mobile_number);
        $UsersOtp = UsersOtp::where("mobile_number", $request->mobile_number)
            ->where("otp", $request->otp)
            ->where("role", "user")->exists();

        if (!$user->exists()) {
            return response()->json([
                'success' => false,
                'statusCode' => 202,
                'message' => [
                    'user' => 'کاربر مورد نظر یافت نشد.'
                ],
            ], Response::HTTP_OK);
        }

        if (!$UsersOtp) {
            return response()->json([
                'success' => false,
                'statusCode' => 202,
                'message' => [
                    "otp" => 'عملیات با خطا مواجه شد.'
                ],
            ], Response::HTTP_OK);
        }

        $user->first()->update([
            'password' => Hash::make($request->password),
        ]);

        $exp = \Carbon\Carbon::now()->addDays(7)->timestamp;
        $token = JWTAuth::customClaims(['exp' => $exp])->fromUser($user->first());

        $updateddUser = $user->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            "data" => [
                "user" => [
                    "id" => $updateddUser->id,
                    "personnel_code" =>  $updateddUser->personnel_code,
                    "uuid" =>  $updateddUser->uuid,
                    "first_name" =>  $updateddUser->first_name,
                    "last_name" =>  $updateddUser->last_name,
                    "user_name" =>  $updateddUser->user_name,
                    "ceo_name" =>  $updateddUser->ceo_name,
                    "avatar" => $updateddUser->avatar,
                    "role" => $updateddUser->role
                ],
                "tokens" => [
                    "access_token" => [
                        "value" => $token,
                        "token_type" => "Bearer",
                        "expires_in" => $exp
                    ],
                ]
            ]
        ], Response::HTTP_OK);
    }

    function generateOtp()
    {
        return rand(11111, 99999);
    }

    public function getDifference($exp)
    {
        $now = Carbon::now();
        $created_at = $now->toDateTimeString();

        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $exp);
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $created_at);

        if ($date1->eq($date2)) {
            return [
                'isValid' => true,
                'exp' => $date1->diffInSeconds($date2)
            ];
        } else {
            if ($date1->gt($date2)) {
                return [
                    'isValid' => true,
                    'exp' => $date1->diffInSeconds($date2)
                ];
            } else {
                return [
                    'isValid' => false,
                    'exp' => 0
                ];
            }
        }
    }

    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated, do logout
        try {
            JWTAuth::invalidate($request->token);
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->status != 'null') {
            $status = explode(',', $request->status);

            $users1 = User::select('id', 'personnel_code', 'avatar', 'first_name', 'last_name', 'mobile_number', 'role', 'status', 'created_at', 'email')
                ->whereIn('status', $status)
                ->whereRaw('concat(first_name,last_name,personnel_code) like ?', "%{$request->q}%");

            if ($request->role) {
                $users1->where('role', $request->role);
            }

            $users = $users1->paginate(10);
        } else {
            $users = User::select('id', 'personnel_code', 'first_name', 'last_name', 'role', 'email')
                ->whereRaw('concat(first_name,last_name,personnel_code) like ?', "%{$request->q}%")
                ->get();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $users
        ], Response::HTTP_OK);
    }

    public function show(User $user)
    {
        $orders = Orders::where("user_id", $user->id)->count();
        $sales = Orders::where("marketer_id", $user->id)->count();
        $articleComments = (int)ArticleComments::where("user_id", $user->id)->count();
        $newsComments = (int)NewsComments::where("user_id", $user->id)->count();
        $productComments = (int)ProductComments::where("user_id", $user->id)->count();
        $customers = User::where("affiliate_id", $user->id)->count();
        $discountCodes = Discounts::where("creator_id", $user->id)->count();
        $address = UsersAddress::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' =>  [
                "user" => $user,
                "address" => $address,
                "count" => [
                    "orders" => $orders,
                    "comments" => $articleComments + $newsComments + $productComments,
                    "sales" => $sales,
                    "customers" => $customers,
                    "discountCodes" => $discountCodes
                ]
            ]
        ], Response::HTTP_OK);
    }

    public function getUser(Request $request)
    {
        $user = User::where("id", $request->id)->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function getUserProfileSummery(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $orders_processing = Orders::where("user_id", $user->id)
            ->where("status", 2)->count();

        $orders_rejected = Orders::where("user_id", $user->id)
            ->whereIn("status", [7, 8])->count();

        $orders_confirmed = Orders::where("user_id", $user->id)
            ->where("status", 5)->count();

        $orders_cancelled = Orders::where("user_id", $user->id)
            ->where("status", 6)->count();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'user' => [
                    "id" => $user->id,
                    "first_name" => $user->first_name,
                    "last_name" => $user->last_name,
                    "user_name" => $user->user_name,
                    "uuid" => $user->uuid,
                    'invited_affiliate_confirmed' => $user->invited_affiliate_confirmed,
                    "invited_affiliate_pending" => $user->invited_affiliate_pending,
                    "clicks" => $user->clicks,
                    "mobile_number" => $user->mobile_number,
                    "role" => $user->role,
                    "status" => $user->status,
                    "documents_status" => $user->documents_status,
                    "credit_purchase_type" => $user->credit_purchase_type,
                    "wallet_balance" => $user->wallet_balance,
                    "income" => $user->income,
                    "credit_purchase_inventory" => $user->credit_purchase_inventory
                ],
                'orders' => [
                    'processing' => $orders_processing,
                    'confirmed' => $orders_confirmed,
                    'rejected' => $orders_rejected,
                    'cancelled' => $orders_cancelled
                ]

            ]
        ], Response::HTTP_OK);
    }

    public function getUserWalletBalance(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'balance' => $user ? $user->wallet_balance : 0,
            ]
        ], Response::HTTP_OK);
    }

    //web
    public function getUserProfileMarketing(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $orders = Orders::select("total", "marketer_id", "marketer_commission", "status", "created_at")
            ->where("marketer_id", $user->id)
            ->get();

        $users = User::select("first_name", "last_name", "mobile_number", "created_at")
            ->where("affiliate_id", $user->id)->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'users' => $users,
                'orders' => $orders

            ]
        ], Response::HTTP_OK);
    }

    public function getOrganizationUsers(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $users = User::select("id", "first_name", "last_name", "role")
            ->whereRaw('concat(first_name,last_name) like ?', "%{$request->q}%")
            ->where("affiliate_id", $user->id)->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $users
        ], Response::HTTP_OK);
    }

    public function getUserOrders(Request $request)
    {
        $orders = Orders::select("id", "order_code", "total", "created_at", "status")
            ->where("user_id", $request->id)
            ->whereIn("status", [1, 2, 3, 4, 5, 6, 7, 8])
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $orders
        ], Response::HTTP_OK);
    }

    public function getUserTransactions(Request $request)
    {
        $transactions = Transactions::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'transactions.user_id');
        })
            ->where('description', 'like', '%' . $request->q . '%')
            ->select('transactions.*', 'users.first_name', 'users.last_name')
            ->where("user_id", $request->id)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $transactions
        ], Response::HTTP_OK);
    }

    public function getUserDocuments(Request $request)
    {
        $documents = User::select("credit_purchase_type", "documents", "documents_status")
            ->where("id", $request->id);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $documents
        ], Response::HTTP_OK);
    }

    public function verify_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'status' => 'required|numeric',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $user = User::where("id", $request->id)->first();

        if ($request->status == 2) {
            $msg = $user->first_name . " عزیز حساب کاربری شما با موفقیت تأیید گردید.
            جهت ثبت نهایی به لینک زیر مراجعه کنید.
            https://yadaksadra.com/profile/upgrade";

            $input_data = array("user-name" => $user->first_name);
            Sms::sendWithPatern($user->mobile_number, "siyp5nbi6lfff6g", $input_data);

            switch ($user->request_change_role) {
                case 2:
                    $role = "Marketer";
                    break;

                case 3:
                    $role = "Organization";
                    break;

                case 4:
                    $role = "Saler";
                    break;

                default:
                    $role = $user->role;
                    break;
            }

            $user->update([
                'status' => 2,
                'role' => $role
            ]);
        } else {
            if ($request->description != null) {
                $msg = $request->description;
            } else {
                $msg = "کاربر محترم،اطلاعات ارسالی شما جهت ارتقاء حساب کاربری مورد تأیید نمی باشد.
                جهت ویرایش اطلاعات به لینک زیر مراجعه کنید.

                https://yadaksadra.com/profile/upgrade";
            }

            Sms::send($user->mobile_number, $msg);

            $user->update([
                'status' => 3
            ]);
        }

        Messages::create([
            "title" => $request->status == 2 ? "حساب کاربری شما با موفقیت تأیید گردید." : "اطلاعات ارسالی شما جهت ارتقاء حساب کاربری مورد تأیید نمی باشد.",
            "body" => $msg,
            "user_id" => $request->id,
            "type" => 2
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    //admin
    public function getUserCredits(Request $request)
    {
        $users = User::select(
            'id',
            'personnel_code',
            'avatar',
            'first_name',
            'last_name',
            'mobile_number',
            'role',
            'status',
            'email',
            'documents_status',
            'credit_purchase_type',
            'updated_at'
        )
            ->whereIn('documents_status', [1, 2, 3])
            ->whereRaw('concat(first_name,last_name,personnel_code) like ?', "%{$request->q}%")
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $users
        ], Response::HTTP_OK);
    }

    public function getUserMarketing(Request $request)
    {
        $orders = Orders::select('marketer_id', 'total', 'created_at')
            ->orderBy('created_at')
            ->where("status", 5)
            ->where("marketer_id", $request->id);

        $res3 = $orders->whereYear('created_at', $request->year)
            ->where("status", 5)
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $orders = [];
        $ordersCount = [];

        foreach ($res3 as $key => $value) {
            $orders[(int)$key] = count($value);
        }

        for ($i = 1; $i <= 12; $i++) {
            if (!empty($orders[$i])) {
                $ordersCount[$i] = $orders[$i];
            } else {
                $ordersCount[$i] = 0;
            }
        }

        $years = [];
        for ($i = 2023; $i <= (int)Carbon::now()->format('Y'); $i++) {
            array_push($years, $i);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "graph" => array_values($ordersCount),
                "years" => $years,
                'orders' => $orders
            ]
        ], Response::HTTP_OK);
    }

    public function getUserSalerMarketing(Request $request)
    {
        $orders = Orders::select('marketer_id', 'total', 'created_at')
            ->orderBy('created_at')
            ->where("status", 5)
            ->where("marketer_id", $request->id);

        $customers = InvitationLinks::where("user_id", $request->id);
        $pending_customers = $customers->where("status", 0)->count();
        $confirmed_customers = $customers->where("status", 1)->count();

        $res1 = $orders->get();
        $res2 = $orders->count();
        $res3 = $orders->whereYear('created_at', $request->year)
            ->where("status", 5)
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $total = 0;
        $orders = [];
        $ordersCount = [];

        foreach ($res3 as $key => $value) {
            $orders[(int)$key] = count($value);
        }

        foreach ($res1 as $key => $value) {
            $total += (int)$value->total;
        }

        for ($i = 1; $i <= 12; $i++) {
            if (!empty($orders[$i])) {
                $ordersCount[$i] = $orders[$i];
            } else {
                $ordersCount[$i] = 0;
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "graph" => array_values($ordersCount),
                "ordersCount" => $res2,
                "customer" => [
                    "pending_customers" => $pending_customers,
                    "confirmed_customers" => $confirmed_customers
                ],
                "income" => $total
            ]
        ], Response::HTTP_OK);
    }

    public function getUserDiscountCodes(Request $request)
    {
        $discountCodes = Discounts::where("creator_id", $request->id)->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $discountCodes
        ], Response::HTTP_OK);
    }

    public function getUserComments(Request $request)
    {
        switch ($request->t) {
            case "1":
                $comments = ProductComments::where("user_id", $request->id)->paginate(10);
                break;

            case "2":
                $comments = ArticleComments::where("user_id", $request->id)->paginate(10);
                break;

            case "3":
                $comments = NewsComments::where("user_id", $request->id)->paginate(10);
                break;
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $comments
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_user(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'father_name' => 'nullable|string',
            'phone_number' => 'nullable|numeric',
            'work_phone_number' => 'nullable|numeric',
            'national_code' => 'nullable|numeric|digits:10',
            'birth_date' => 'nullable|string',
            'email' => 'nullable|email',
            'agency' => 'nullable|numeric|digits:1',
            'shaba_bank' => 'nullable|numeric|digits:24',
            'field_of_activity' => 'nullable|string',
            'province_of_activity_id' => 'nullable|numeric',
            'province_of_activity' => 'nullable|string',
            'city_of_activity' => 'nullable|string',
            'city_of_activity_id' => 'nullable|numeric',
            'job_position' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if ($request->national_code) {
            if ($user->national_code != $request->national_code) {
                if (User::where("national_code", $request->national_code)->exists()) {
                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => [
                            'title' => "کد ملی قبلا ثبت شده است."
                        ],
                        "data" => $user
                    ], Response::HTTP_OK);
                }
            }
        }

        $user = $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'ceo_name' => $request->ceo_name,
            'father_name' => $request->father_name,
            'phone_number' => $request->phone_number,
            'work_phone_number' => $request->work_phone_number,
            'national_code' => $request->national_code,
            'birth_date' => $request->birth_date,
            'email' => $request->email,
            'agency' => $request->agency,
            'shaba_bank' => $request->shaba_bank,
            'field_of_activity' => $request->field_of_activity,
            'province_of_activity_id' => $request->province_of_activity_id,
            'province_of_activity' => $request->province_of_activity,
            'city_of_activity' => $request->city_of_activity,
            'city_of_activity_id' => $request->city_of_activity_id,
            'job_position' => $request->job_position
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            "data" => $user
        ], Response::HTTP_OK);
    }

    public function storeInitialProfileInfo(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'father_name' => 'nullable|string',
            'national_code' => 'nullable|numeric|digits:10',
            'birth_date' => 'nullable|string',
            'email' => 'nullable|email',
            'agency' => 'numeric|digits:1',
            'shaba_bank' => 'nullable|numeric|digits:24',
            'biography' => "nullable|string"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if ($user->national_code != $request->national_code) {
            if (User::where("national_code", $request->national_code)->exists()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'title' => "کد ملی قبلا ثبت شده است."
                    ],
                    "data" => $user
                ], Response::HTTP_OK);
            }
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'father_name' => $request->father_name,
            'national_code' => $request->national_code,
            'birth_date' => $request->birth_date,
            'email' => $request->email,
            'agency' => $request->agency,
            'shaba_bank' => $request->shaba_bank,
            'biography' => $request->biography
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function changeUserRoleFromAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'role' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if (
            $request->role == "Normal" or $request->role == "Marketer" or
            $request->role == "Organization" or $request->role == "Saler"
        ) {

            $user = User::where("id", $request->id)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => 'کاربری با این شناسه یافت نشد!',
                ], Response::HTTP_OK);
            }

            $user->update([
                'role' => $request->role
            ]);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => 'عملیات ناموفق بود!',
        ], Response::HTTP_OK);
    }

    public function changeAccountRole(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'role' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        switch ($request->role) {
            case "Marketer":
                $role = 2;
                break;

            case "Organization":
                $role = 3;
                break;

            case "Saler":
                $role = 4;
                break;
        }

        $user->update([
            'request_change_role' => $role,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function saveFinalRequestChangeRole(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'user_name' => 'nullable|string',
            'phone_number' => 'nullable|numeric',
            'work_phone_number' => 'nullable|numeric',
            'field_of_activity' => 'nullable|string',
            'province_of_activity_id' => 'nullable|numeric',
            'province_of_activity' => 'nullable|string',
            'city_of_activity' => 'nullable|string',
            'city_of_activity_id' => 'nullable|numeric',
            'job_position' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $user->update([
            'user_name' => $request->user_name,
            'phone_number' => $request->phone_number,
            'work_phone_number' => $request->work_phone_number,
            'field_of_activity' => $request->field_of_activity,
            'province_of_activity_id' => $request->province_of_activity_id,
            'province_of_activity' => $request->province_of_activity,
            'city_of_activity' => $request->city_of_activity,
            'city_of_activity_id' => $request->city_of_activity_id,
            'job_position' => $request->job_position,
            'status' => 1
        ]);

        $user = User::where("id", $user->id)->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function getRoleChangingStatus(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'verify' => $user->status == 2 ? true : false,
                "role" => $user->role
            ]
        ], Response::HTTP_OK);
    }

    public function request_change_role(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'user_name' => 'string',
            'father_name' => 'nullable|string',
            'phone_number' => 'nullable|numeric',
            'work_phone_number' => 'nullable|numeric',
            'national_code' => 'nullable|numeric|digits:10',
            'birth_date' => 'nullable|string',
            'email' => 'nullable|email',
            'agency' => 'numeric|digits:1',
            'shaba_bank' => 'nullable|numeric|digits:24',
            'field_of_activity' => 'nullable|string',
            'province_of_activity_id' => 'numeric',
            'province_of_activity' => 'string',
            'city_of_activity' => 'string',
            'city_of_activity_id' => 'numeric',
            'job_position' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if ($request->national_code) {
            if ($user->national_code != $request->national_code) {
                if (User::where("national_code", $request->national_code)->exists()) {
                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => [
                            'title' => "کد ملی قبلا ثبت شده است."
                        ],
                        "data" => $user
                    ], Response::HTTP_OK);
                }
            }
        }

        $user = $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'ceo_name' => $request->ceo_name,
            'father_name' => $request->father_name,
            'phone_number' => $request->phone_number,
            'work_phone_number' => $request->work_phone_number,
            'national_code' => $request->national_code,
            'birth_date' => $request->birth_date,
            'email' => $request->email,
            'agency' => $request->agency,
            'shaba_bank' => $request->shaba_bank,
            'field_of_activity' => $request->field_of_activity,
            'province_of_activity_id' => $request->province_of_activity_id,
            'province_of_activity' => $request->province_of_activity,
            'city_of_activity' => $request->city_of_activity,
            'city_of_activity_id' => $request->city_of_activity_id,
            'job_position' => $request->job_position
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            "data" => $user
        ], Response::HTTP_OK);
    }
}
