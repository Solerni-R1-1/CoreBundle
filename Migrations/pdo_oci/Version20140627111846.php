<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/27 11:18:48
 */
class Version20140627111846 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_session MODIFY (
                start_date TIMESTAMP(0) DEFAULT NULL, 
                end_date TIMESTAMP(0) DEFAULT NULL, 
                start_inscription_date TIMESTAMP(0) DEFAULT NULL, 
                end_inscription_date TIMESTAMP(0) DEFAULT NULL, 
                title VARCHAR2(255) DEFAULT NULL, 
                max_users NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP (illustration_name)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD (
                illustration_name VARCHAR2(255) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session MODIFY (
                start_date TIMESTAMP(0) NOT NULL, 
                end_date TIMESTAMP(0) NOT NULL, 
                start_inscription_date TIMESTAMP(0) NOT NULL, 
                end_inscription_date TIMESTAMP(0) NOT NULL, 
                title VARCHAR2(255) NOT NULL, 
                max_users NUMBER(10) NOT NULL
            )
        ");
    }
}