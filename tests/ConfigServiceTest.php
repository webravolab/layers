<?php
use Webravo\Infrastructure\Library\Configuration;

class ConfigServiceTest extends TestCase
{
    public function testConfigService()
    {
        $service = (new Webravo\Infrastructure\Library\Configuration())::instance();

        self::assertEquals('value001', $service->getKey('TEST_KEY_001','app'));
        self::assertEquals('UTC', $service->getKey('timezone', 'app'));
        self::assertNull($service->getKey('INVALID'));
        self::assertNull($service->getKey('missing','invalid'));
        self::assertEquals('DEFAULT', $service->getKey('missing','invalid','DEFAULT'));

        self::assertEquals('UTC', Configuration::get('timezone', 'app'));
        self::assertNull(Configuration::get('INVALID'));

        $a_conf = Configuration::getClass('google');
        self::assertTrue(is_array($a_conf));

        // Check settings config override
        self::assertNull(Configuration::get('TEST_OVERRIDE_BAD'));


        // To let this test pass ... set SETTINGS_DB_CONNECTION environment variable
        $db_connection = $service->getKey('SETTINGS_DB_CONNECTION');
        if (!empty($db_connection)) {

            // Create a key in settings table
            $service->setKey('TEST_OVERRIDE_GOOD','9999');

            /*
            $results = DB::connection($db_connection)->insert("insert into settings ('key','value') values('TEST_OVERRIDE_GOOD','9999')");
            // Check for existence
            $results = DB::connection($db_connection)->select("select * from settings");
            self::assertArrayHasKey(0, $results);
            */
            self::assertEquals('9999', Configuration::get('TEST_OVERRIDE_GOOD'));


            $results = DB::connection($db_connection)->select("select * from settings");

            // Replace a key in settings table
            $service->setKey('TEST_OVERRIDE_GOOD','8888');

            self::assertEquals('8888', Configuration::get('TEST_OVERRIDE_GOOD'));

            $results = DB::connection($db_connection)->select("select * from settings");

            // Delete a key
            $service->deleteKey('TEST_OVERRIDE_GOOD');

            self::assertEquals('1234', Configuration::get('TEST_OVERRIDE_GOOD', null,'1234'));

            $results = DB::connection($db_connection)->select("select * from settings");

            // Set a class

            $service->setKey('MY-CLASS.ONE','111');
            $service->setKey('MY-CLASS-TWO','222');

            $a_class = $service->getClass('MY-CLASS');



        }
    }
}
