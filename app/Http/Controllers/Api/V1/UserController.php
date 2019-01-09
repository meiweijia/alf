<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\ApiController;
use App\Libraries\Wechat;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\PassportToken;
use App\Models\UserWechatProfile as WechatUser;

class UserController extends ApiController
{
    use PassportToken;

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
            return $this->error($validator->errors());
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
        $token = $this->getToken($request, $user->password);
        return $this->success($token);
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
            return $this->error($validator->errors());
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

        $token = $this->getToken($request, $password);
        return $this->success($token);
    }

    /**
     * 获取token
     *
     * @param Request $request
     * @param $password
     * @return mixed
     */
    protected function getToken($request, $password)
    {
        $client = DB::table('oauth_clients')->where('password_client', 1)->first();

        $http = new \GuzzleHttp\Client;

        $url = url('api/oauth/token');
        $response = $http->post($url, [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => $request->input('mobile_no'),
                'password' => $password,
                'scope' => null,
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
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
        return $this->success(\Route::dispatch($token));
    }

    /**
     * 用户信息
     *
     * @param Request $request
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getProfile(Request $request)
    {
        $data = User::with('profile')->find(Auth::id());
        return $this->success($data);
    }

    /**
     * 微信授权登录
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function wechatAuth(Request $request)
    {
        $this->checkPar($request, [
            'thisurl' => 'required',
        ]);

        return Wechat::authLogin($request);
    }

    public function checkBindMobile($key)
    {
        //TODO 添加微信验证中间件
        $user = Wechat::authUser();
        $info = $user->getOriginal();
        unset($info['privilege']);
        $wechatUser = WechatUser::query()->with('user')->where('openid', $user->getId())->first();
        if ($wechatUser) {
            WechatUser::query()->where('openid')->update($info);
            $bind = $wechatUser->user ? 1 : 0;
        } else {
            WechatUser::query()->create($info);
            $bind = 0;
        }
        $url = cache($key);
        return redirect($url . '?bind=' . $bind);
    }
}