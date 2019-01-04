<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Libraries\Paginator;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OrderController extends ApiController
{

    /**
     * 获取充值记录
     *
     * @param Request $request
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBalanceLogs(Request $request)
    {
        $prePage = $request->input('per_page') ?? 10;
        $data = $this->getOrder(Order::ORDER_TYPE_RECHARGE, $prePage);
        return $this->success($data);
    }

    /**
     * 获取订场记录
     *
     * @param Request $request
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getReserveLogs(Request $request)
    {
        $prePage = $request->input('per_page') ?? 10;
        $data = $this->getOrder(Order::ORDER_TYPE_RESERVE, $prePage);
        return $this->success($data);
    }

    private function getOrder($type, $perPage = 10)
    {
        $user_id = Auth::id();
        $data = Order::query()->where('user_id', $user_id)
            ->where('type', $type)
            ->paginate($perPage);
        return $this->paginate($data);
    }
}
