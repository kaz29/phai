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
    static private $_telemetryClientInstance = null;

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
    public static function initialize(Telemetry_Client $client, string $instrumentationKey = null, Application $app = null, User $user = null, Callable $customShutdownFunc = null)
    {
        if (is_null(self::$_telemetryClientInstance)) {
            $context = $client->getContext();
            $context->setInstrumentationKey($instrumentationKey);

            if (!is_null($app)) {
                $context->setApplicationContext($app);
            }

            if (!is_null($user)) {
                $context->setUserContext($user);
            }

            self::$_telemetryClientInstance = $client;
            self::$_telemetryClientInstance->trackMessage('initialized', Message_Severity_Level::VERBOSE);

            if ($customShutdownFunc === null) {
                register_shutdown_function(function () {
                    self::shutdown();
                });
            } else {
                register_shutdown_function(function () use($customShutdownFunc, $client) {
                    call_user_func($customShutdownFunc, $client);                   
                });
            }
        }

        return self::$_telemetryClientInstance;
    }

    public static function shutdown()
    {
        if (is_null(self::$_telemetryClientInstance)) {
            return;
        }

        self::$_telemetryClientInstance->trackMessage('register_shutdown_function', Message_Severity_Level::VERBOSE);
        self::$_telemetryClientInstance->flush([], false);
        self::$_telemetryClientInstance = null;
    }

    public static function getClient()
    {
        return self::$_telemetryClientInstance;
    }
}
