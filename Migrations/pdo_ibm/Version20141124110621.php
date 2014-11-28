<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

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
            ADD COLUMN gender INTEGER NOT NULL 
            ADD COLUMN country VARCHAR(255) DEFAULT NULL 
            ADD COLUMN city VARCHAR(255) DEFAULT NULL 
            ADD COLUMN birthdate DATE DEFAULT NULL 
            ADD COLUMN website VARCHAR(255) DEFAULT NULL 
            ADD COLUMN facebook VARCHAR(255) DEFAULT NULL 
            ADD COLUMN twitter VARCHAR(255) DEFAULT NULL 
            ADD COLUMN linkedIn VARCHAR(255) DEFAULT NULL 
            ADD COLUMN googlePlus VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD COLUMN displayOptionalInformation SMALLINT NOT NULL 
            ADD COLUMN displayBaseInformation SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN gender 
            DROP COLUMN country 
            DROP COLUMN city 
            DROP COLUMN birthdate 
            DROP COLUMN website 
            DROP COLUMN facebook 
            DROP COLUMN twitter 
            DROP COLUMN linkedIn 
            DROP COLUMN googlePlus
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            DROP COLUMN displayOptionalInformation 
            DROP COLUMN displayBaseInformation
        ");
    }
}