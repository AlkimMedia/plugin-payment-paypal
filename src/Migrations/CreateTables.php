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

class CreateTables
{
    public function run(Migrate $migrate)
    {
        /**
         * Create the settings table
         */
        $migrate->createTable(Settings::class);

        /**
         * Create the account table
         */
        $migrate->createTable(Account::class);
    }
}