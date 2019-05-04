<?php
namespace kaz29\Phai;

use ApplicationInsights\Channel\Contracts\Application;
use ApplicationInsights\Channel\Contracts\User;
use ApplicationInsights\Telemetry_Client;
use ApplicationInsights\Channel\Contracts\Message_Severity_Level;

class Phai
{
    /**
     * @var Telemetry_Client
     */
    static private $_telemetryClienInstance = null;

    public static function createClient() : Telemetry_Client
    {
        return new Telemetry_Client();
    }

    /**
     * Initialize phai.
     *
     * @param string $instrumentationKey
     * @param Application|null $application
     * @param User|null $user
     * @return null|Phai
     */
    public static function initialize(Telemetry_Client $client, string $instrumentationKey = null, Application $app = null, User $user = null )
    {
        if (is_null(self::$_telemetryClienInstance)) {
            $context = $client->getContext();
            $context->setInstrumentationKey($instrumentationKey);

            if (!is_null($app)) {
                $context->setApplicationContext($app);
            }

            if (!is_null($user)) {
                $context->setUserContext($user);
            }

            self::$_telemetryClienInstance = $client;
            self::$_telemetryClienInstance->trackMessage('initialized', Message_Severity_Level::VERBOSE);

            register_shutdown_function(function () {
                self::shutdown();
            });
        }

        return self::$_telemetryClienInstance;
    }

    public static function shutdown()
    {
        if (is_null(self::$_telemetryClienInstance)) {
            return;
        }

        self::$_telemetryClienInstance->trackMessage('register_shutdown_function', Message_Severity_Level::VERBOSE);
        self::$_telemetryClienInstance->flush();
        self::$_telemetryClienInstance = null;
    }

    public static function getClient()
    {
        return self::$_telemetryClienInstance;
    }
}
