<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class AppliedOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appliedOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理已经支付的订单，如果订单下所有的预定场地都已经过期，则把订单设为已完成状态，每分钟执行';

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
        app(OrderService::class)->handleAppliedOrder();
    }
}
