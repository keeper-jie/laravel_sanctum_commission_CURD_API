<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users',
            // 'email' => 'required|email|unique:users',
            'email' => 'nullable|email|unique:users',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), '数据校验错误');
        }

        $user = new User();
        $user->name = $request->name;
        if (isset($request->email)) {
            $user->email = $request->email;
        } else {
            $user->email = '';
        }
        $user->password = bcrypt($request->password);
        //save the model to database
        $user->save();

        $user = User::where('name', $request->name)->first();
        $token = $user->createToken('authToken')->plainTextToken;
        // return response()->json([
        //     'status_code' => 200,
        //     'token' => $token
        // ]);
        return $this->sendResponse(['token' => $token], '用户注册成功');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), '数据校验错误');
        }

        if (Auth::attempt(['name' => $request->name, 'password' => $request->password])) {
            $user = User::where('name', $request->name)->first();
            $token = $user->createToken('authToken')->plainTextToken;
            return $this->sendResponse(['token' => $token], '用户登录成功');
        } else {
            //not valid password and name
            return $this->sendError('未注册', '没有注册，请注册');
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse('', '注销成功');
    }
}
