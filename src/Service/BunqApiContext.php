<?php


namespace App\Service;


use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use bunq\Util\BunqEnumApiEnvironmentType;

class BunqApiContext
{
    /**
     * @var BunqApiContext $_instance
     */
    private static $_instance = null;

    /**
     * BunqApiContext constructor.
     */
    private function __construct()
    {
        if (file_exists(ApiContext::FILENAME_CONFIG_DEFAULT)) {
            $apiContext = ApiContext::restore();
            BunqContext::loadApiContext($apiContext);
            self::$_instance = $apiContext;
        }

        $environmentType = BunqEnumApiEnvironmentType::PRODUCTION();
        $apiKey = getenv('BUNQ_API_KEY');
        $deviceDescription = 'SiriBunqCommands';

        $apiContext = ApiContext::create(
            $environmentType,
            $apiKey,
            $deviceDescription
        );

        BunqContext::loadApiContext($apiContext);
        $apiContext->save();
        self::$_instance = $apiContext;
    }

    /**
     * @return BunqApiContext
     */
    public static function getInstance()
    {
        if(is_null(self::$_instance)) {
            self::$_instance = new BunqApiContext();
        }

        return self::$_instance;
    }
}