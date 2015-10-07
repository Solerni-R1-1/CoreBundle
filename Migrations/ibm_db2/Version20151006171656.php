<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

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
            ADD COLUMN lockedLogin SMALLINT NOT NULL 
            ADD COLUMN lockedPassword SMALLINT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences ALTER visibility visibility SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN lockedLogin 
            DROP COLUMN lockedPassword
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences ALTER visibility visibility INTEGER NOT NULL
        ");
    }
}