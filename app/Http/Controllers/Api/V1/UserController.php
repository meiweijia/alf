<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\PassportToken;

class UserController extends Controller
{
    use PassportToken;

    public function user(Request $request)
    {
        return $request->user()->token();
    }

    /**
     * 注册账号
     *
     * @param $request
     * @return mixed
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required',
            'password' => 'required',
            'c_password' => 'required',
            'code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if (false) {
            return '验证码错误';//todo test code
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        //新增用户 初始化信息表
        $profile = new UserProfile(['level' => 1, 'balance' => 0.00]);
        $user->profile()->save($profile);
        return $this->getToken($request, $user->password);
    }

    /**
     * 登录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed|string
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $input = $request->all();
        $user = User::query()->where('mobile_no', $input['mobile_no'])->first();
        if (!$user) {
            return '手机号未注册';//todo test code
        }

        if ($input['type'] == User::LOGIN_TYPE_CODE) {//短信登录
            if (false) {
                return '验证码错误';//todo test code
            }
            $password = $user->password;
        } else {
            $password = $input['password'];
        }

        return $this->getToken($request, $password);
    }

    /**
     * 获取token
     *
     * @param $request
     * @param $password
     * @return mixed
     */
    protected function getToken($request, $password)
    {
        $client = DB::table('oauth_clients')->where('password_client', 1)->first();

        $request->request->add([
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $request->input('mobile_no'),
            'password' => $password,
            'scope' => null,
        ]);

        // Fire off the internal request.
        $token = Request::create(
            'api/oauth/token',
            'POST'
        );
        return \Route::dispatch($token);
    }

    /**
     * 刷新token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $client = DB::table('oauth_clients')->where('password_client', 1)->first();

        $request->request->add([
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->input('refresh_token'),
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => null,
        ]);

        // Fire off the internal request.
        $token = Request::create(
            'api/oauth/token',
            'POST'
        );
        return \Route::dispatch($token);
    }

    /**
     * 用户信息
     *
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function profile()
    {
        $user = User::with('profile')->get();
        return $user;
    }
}