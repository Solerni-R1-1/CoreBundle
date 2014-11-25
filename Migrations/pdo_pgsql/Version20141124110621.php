<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/24 11:06:26
 */
class Version20141124110621 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD gender INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD country VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD city VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD birthdate DATE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD website VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD facebook VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD twitter VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD linkedIn VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD googlePlus VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD displayOptionalInformation BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD displayBaseInformation BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP gender
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP country
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP city
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP birthdate
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP website
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP facebook
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP twitter
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP linkedIn
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP googlePlus
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            DROP displayOptionalInformation
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            DROP displayBaseInformation
        ");
    }
}