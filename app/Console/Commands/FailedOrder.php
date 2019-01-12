<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class FailedOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'failedOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理未支付的订单，把订单中的场地设为可选状态';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        app(OrderService::class)->handleFailedOrder();
    }
}
