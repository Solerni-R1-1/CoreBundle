<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

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
            ADD lockedLogin TINYINT(1) NOT NULL, 
            ADD lockedPassword TINYINT(1) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences CHANGE visibility visibility TINYINT(1) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP lockedLogin, 
            DROP lockedPassword
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences CHANGE visibility visibility INT NOT NULL
        ");
    }
}