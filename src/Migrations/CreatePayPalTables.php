<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 07.12.16
 * Time: 11:56
 */

namespace PayPal\Migrations;

use PayPal\Models\Database\Account;
use PayPal\Models\Database\Settings;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

class CreatePayPalTables
{
    public function run(Migrate $migrate)
    {
        /**
         * Create the settings table
         */
        try
        {
            $migrate->deleteTable(Settings::class);
        }
        catch (\Exception $e)
        {
            //Table does not exist
        }
        $migrate->createTable(Settings::class);

        /**
         * Create the account table
         */
        try
        {
            $migrate->deleteTable(Account::class);
        }
        catch (\Exception $e)
        {
            //Table does not exist
        }
        $migrate->createTable(Account::class);
    }
}