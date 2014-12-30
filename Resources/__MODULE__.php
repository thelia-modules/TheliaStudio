<?php
{include "./includes/header.php"}

namespace {$moduleCode};

use Thelia\Module\BaseModule;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;


/**
 * Class {$moduleCode}
 * @package {$moduleCode}
 */
class {$moduleCode} extends BaseModule
{
    const MESSAGE_DOMAIN = "{$moduleCode|lower}";

    public function postActivation(ConnectionInterface $con = null)
    {
        $database = new Database($con);

        $database->insertSql(null, [__DIR__ . "/Config/create.sql", __DIR__ . "/Config/insert.sql"]);
    }
}
