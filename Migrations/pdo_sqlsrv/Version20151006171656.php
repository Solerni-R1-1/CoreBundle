<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/10/06 05:17:10
 */
class Version20151006171656 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD lockedLogin BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD lockedPassword BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences ALTER COLUMN visibility BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN lockedLogin
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN lockedPassword
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences ALTER COLUMN visibility INT NOT NULL
        ");
    }
}