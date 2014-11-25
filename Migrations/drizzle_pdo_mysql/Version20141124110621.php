<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
            ADD gender INT NOT NULL, 
            ADD country VARCHAR(255) DEFAULT NULL, 
            ADD city VARCHAR(255) DEFAULT NULL, 
            ADD birthdate DATE DEFAULT NULL, 
            ADD website VARCHAR(255) DEFAULT NULL, 
            ADD facebook VARCHAR(255) DEFAULT NULL, 
            ADD twitter VARCHAR(255) DEFAULT NULL, 
            ADD linkedIn VARCHAR(255) DEFAULT NULL, 
            ADD googlePlus VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD displayOptionalInformation BOOLEAN NOT NULL, 
            ADD displayBaseInformation BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP gender, 
            DROP country, 
            DROP city, 
            DROP birthdate, 
            DROP website, 
            DROP facebook, 
            DROP twitter, 
            DROP linkedIn, 
            DROP googlePlus
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            DROP displayOptionalInformation, 
            DROP displayBaseInformation
        ");
    }
}