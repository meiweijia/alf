<?php

use Illuminate\Database\Seeder;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 创建 100 笔订单
        $orders = factory(\App\Models\Order::class, 100)->create();

        foreach ($orders as $order) {
            // 每笔订单随机选择 1 - 3 个场地
            $items = factory(\App\Models\OrderItem::class, random_int(1, 3))->create([
                'order_id' => $order->id,
            ]);

            // 计算总价
            $total = $items->sum(function (\App\Models\OrderItem $item) {
                return $item->fees * $item->amount;
            });

            // 更新订单总价
            $order->update([
                'total_fees' => $total,
            ]);

        }

    }
}
