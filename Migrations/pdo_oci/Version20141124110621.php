<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

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
            ADD (
                gender NUMBER(10) NOT NULL, 
                country VARCHAR2(255) DEFAULT NULL, 
                city VARCHAR2(255) DEFAULT NULL, 
                birthdate DATE DEFAULT NULL, 
                website VARCHAR2(255) DEFAULT NULL, 
                facebook VARCHAR2(255) DEFAULT NULL, 
                twitter VARCHAR2(255) DEFAULT NULL, 
                linkedIn VARCHAR2(255) DEFAULT NULL, 
                googlePlus VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD (
                displayOptionalInformation NUMBER(1) NOT NULL, 
                displayBaseInformation NUMBER(1) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP (
                gender, country, city, birthdate, website, 
                facebook, twitter, linkedIn, googlePlus
            )
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            DROP (
                displayOptionalInformation, displayBaseInformation
            )
        ");
    }
}