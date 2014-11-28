<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/24 11:06:27
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
            ADD country NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD city NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD birthdate DATE
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD website NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD facebook NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD twitter NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD linkedIn NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD googlePlus NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD displayOptionalInformation BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD displayBaseInformation BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN gender
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN country
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN city
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN birthdate
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN website
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN facebook
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN twitter
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN linkedIn
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN googlePlus
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            DROP COLUMN displayOptionalInformation
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            DROP COLUMN displayBaseInformation
        ");
    }
}