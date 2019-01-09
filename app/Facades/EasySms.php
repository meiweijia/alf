<?php

namespace App\Facades;


use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static array  send($to, $message, array $gateways = [])
 * @method static \Overtrue\EasySms\Contracts\GatewayInterface  gateway($name = null)
 * @method static \Overtrue\EasySms\Contracts\StrategyInterface  strategy($strategy = null)
 * @method static $this  extend($name, Closure $callback)
 * @method static \Overtrue\EasySms\Support\Config  getConfig()
 * @method static string  getDefaultGateway()
 * @method static $this  setDefaultGateway($name)
 * @method static \Overtrue\EasySms\Messenger  getMessenger()
 */
class EasySms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'easySms';
    }
}