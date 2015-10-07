<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

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
            ADD lockedLogin BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD lockedPassword BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences ALTER visibility TYPE BOOLEAN
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP lockedLogin
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP lockedPassword
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences ALTER visibility TYPE INT
        ");
    }
}