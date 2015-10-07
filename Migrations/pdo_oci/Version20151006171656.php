<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

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
            ADD (
                lockedLogin NUMBER(1) NOT NULL, 
                lockedPassword NUMBER(1) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences MODIFY (
                visibility NUMBER(1) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP (lockedLogin, lockedPassword)
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences MODIFY (
                visibility NUMBER(10) DEFAULT NULL
            )
        ");
    }
}