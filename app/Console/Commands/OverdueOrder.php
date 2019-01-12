<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class OverdueOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'overdueOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理场地信息，整点运行，若订单已经完成，把场地设为可预定状态';

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
        app(OrderService::class)->handleOverdueFieldProfile();
    }
}
