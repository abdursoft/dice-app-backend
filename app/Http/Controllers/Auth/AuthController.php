<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Essentials\JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name"     => "required|string",
            "email"    => "required|email|unique:users,email",
            "password" => "required|string|min:6",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => "INVALID_DATA",
                'message' => 'User registration failed',
                'errors'  => $validator->errors(),
            ], 400);
        }

        try {
            DB::beginTransaction();
            if (! empty($request->role) && $request->role == 'admin') {
                $count = User::where('role', 'admin')->count();
                if ($count >= 1) {
                    return response()->json([
                        'code'    => 'ADMIN_EXISTS',
                        'message' => 'Admin registration over',
                    ], 400);
                }
            }
            $user = User::create(
                [
                    "name"     => $request->input('name'),
                    "role"     => $request->role ?? 'user',
                    "email"    => $request->email,
                    'avatar'   => $request->avatar ?? '1',
                    "password" => Hash::make($request->input('password')),
                ]
            );

            DB::commit();

            $token  = JWTAuth::createToken($user->role, 12, $user->id, $user->email);
            $expire = now()->addSeconds(3600);

            return response()->json([
                'code'       => 'REGISTRATION_SUCCESSFUL',
                'message'    => 'Your account has been successfully created',
                'token'      => $token,
                'expires_in' => $expire,
                'token_type' => 'Bearer',
                'user'       => [
                    'id'            => $user->id,
                    'role'          => $user->role,
                    'name'          => $user->name,
                    'token'         => $user->toke,
                    'email'         => $user->email,
                    'avatar'        => $user->avatar,
                    'token'         => $user->token,
                    'highest_score' => $user->highest_score,
                ],
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code'    => 'INTERNAL_SERVER_ERROR',
                'message' => 'User registration failed',
            ], 400);
        }
    }

    /**
     * Profile image upload
     */
    public function profileImage(Request $request)
    {
        if ($request->hasFile('image')) {
            try {
                $user   = User::find($request->header('id'));
                $upload = Storage::disk('public')->put("uploads/profile", $request->file('image')) ?? $user->image;
                User::where('id', $request->header('id'))->update([
                    'image' => $request->root() . "/storage/" . $upload,
                    'path'  => $upload,
                ]);
                if (! empty($user->image)) {
                    Storage::disk('public')->delete($user->path);
                }
                return response()->json([
                    'code'    => 'PROFILE_IMAGE_UPDATED',
                    'message' => 'Profile image successfully updated',
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'code'    => 'INTERNAL_SERVER_ERROR',
                    'message' => 'Profile image couldn\'t update',
                    'errors'  => $th->getMessage(),
                ], 400);
            }
        } else {
            return response()->json([
                'code'    => 'IMAGE_REQUIRED',
                'message' => 'Please provide an image with a post request',
            ], 400);
        }
    }

    /**
     * Update user profile data
     */
    public function profileData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => "required|string|unique:users,name," . $request->user->id . ",id",
            'email' => "required|string|unique:users,email," . $request->user->id . ",id",
            'bio'   => "required|max:25",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 'INVALID_DATA',
                'message' => 'Profile data couldn\'t update',
                'errors'  => $validator->errors(),
            ], 400);
        }

        try {
            User::where('id', $request->user->id)->update($validator->validate());
            return response()->json([
                'code'    => 'PROFILE_DATA_UPDATED',
                'message' => 'Profile data successfully updated',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code'    => 'INTERNAL_ERROR',
                'message' => 'Internal server error',
            ], 400);
        }
    }

    /**
     * Login the user with jwt token
     */
    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 'INVALID_DATA',
                'message' => 'Authentication failed',
                'errors'  => $validator->errors(),
            ], 401);
        }

        // Determine if the user input is an email or email number
        $user = User::where('email', $request->email)->select('id', 'name', 'avatar', 'email', 'role', 'password')->first();

        if ($user) {
            if (Hash::check($request->input('password'), $user->password)) {
                $token = JWTAuth::createToken($user->role, 12, $user->id, $user->email);

                $expire = now()->addSeconds(3600);

                return response()->json([
                    'code'       => 'LOGIN_SUCCESS',
                    'message'    => 'Login successful',
                    'token'      => $token,
                    'expires_in' => $expire,
                    'token_type' => 'Bearer',
                    'user'       => [
                        'id'            => $user->id,
                        'role'          => $user->role,
                        'name'          => $user->name,
                        'token'         => $user->toke,
                        'email'         => $user->email,
                        'avatar'        => $user->avatar,
                        'token'         => $user->token,
                        'highest_score' => $user->highest_score,
                    ],
                ], 200);
            } else {
                return response()->json([
                    'code'    => 'INCORRECT_PASSWORD',
                    'message' => 'Incorrect Password',
                ], 400);
            }
        } else {
            return response()->json([
                'code'    => 'LOGIN_FAILED',
                'message' => 'User not found',
            ], 404);
        }
    }

    // user signout action
    public function signout(Request $request)
    {
        try {
            $user = $request->attributes->get('auth_user');

            if ($user) {
                $user->api_token        = null;
                $user->token_expired_at = null;
                $user->save();
            }

            return response()->json([
                'code'    => 'SIGNOUT_SUCCESSFUL',
                'message' => 'Signout action successful',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code'    => 'SIGNOUT_FAILED',
                'message' => 'Signout action failed!',
            ], 400);
        }
    }

    // check authenticated user
    public function checkAuthUser(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        if ($user) {
            return response()->json([
                'code'    => 'USER_AUTHENTICATED',
                'message' => 'User is authenticated',
                'user'    => [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'role'          => $user->role,
                    'avatar'        => $user->avatar,
                    'token'         => $user->token,
                    'highest_score' => $user->highest_score,
                ],
            ], 200);
        } else {
            return response()->json([
                'code'    => 'UNAUTHORIZED',
                'message' => 'User is not authenticated',
            ], 401);
        }
    }

    // check authenticated user
    public function checkPusherAuthUser(Request $request)
    {
        $user = $request->user(); // comes from your PusherMiddleware
        if (!$user) {
            return response()->json([], 403);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }


    /**
     * Refresh JWT token.
     */
    public function refresh(Request $request)
    {
        $user = $request->header('refreshToken');
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $decodedToken = JWTAuth::decodeToken($user, false);
        if (! $decodedToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        $user  = User::find($decodedToken->id);
        $token = JWTAuth::createToken($user->role, 1, $user->id, $user->email);

        $user->api_token        = $token;
        $user->token_expired_at = now()->addSeconds(3600);
        $user->save();

        return response()->json([
            'token'      => $token,
            'expires_in' => now()->addSeconds(3600),
        ]);
    }

    /**
     * Check user friends
     */
    public function searchFriends(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        if ($user) {
            $friends = User::where('id', '!=', $user->id)
                ->where('name', 'LIKE', '%' . ($request->input('name') ?? '') . '%')
                ->where('role', 'user')
                ->select('id', 'name', 'avatar', 'token', 'highest_score')
                ->orderBy('highest_score', 'desc')
                ->get();

            return response()->json([
                'code'    => 'FRIENDS_LIST',
                'message' => 'Friends list retrieved successfully',
                'friends' => $friends,
            ], 200);
        } else {
            return response()->json([
                'code'    => 'UNAUTHORIZED',
                'message' => 'User is not authenticated',
            ], 401);
        }
    }

    /**
     * Get friend details
     */
    public function friendDetails(Request $request, $token){
        $user = $request->attributes->get('auth_user');

        if ($user) {
            $friend = User::where('token', $token)
                ->where('role', 'user')
                ->select('id', 'name', 'avatar', 'token', 'highest_score')
                ->first();

            if($friend){
                return response()->json([
                    'code'    => 'FRIEND_DETAILS',
                    'message' => 'Friend details retrieved successfully',
                    'friend' => $friend,
                ], 200);
            }else{
                return response()->json([
                    'code'    => 'NOT_FOUND',
                    'message' => 'Friend not found',
                ], 404);
            }

        } else {
            return response()->json([
                'code'    => 'UNAUTHORIZED',
                'message' => 'User is not authenticated',
            ], 401);
        }
    }
}
