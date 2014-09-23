<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/21 10:19:02
 */
class Version20140721101901 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN duration INT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN weekly_time INT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN duration NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN weekly_time NVARCHAR(255)
        ");
    }
}