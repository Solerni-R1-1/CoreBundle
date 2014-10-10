<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 04:50:05
 */
class Version20141002165004 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD (
                h0 VARCHAR2(255) NOT NULL, 
                h1 VARCHAR2(255) NOT NULL, 
                h2 VARCHAR2(255) NOT NULL, 
                h3 VARCHAR2(255) NOT NULL, 
                h4 VARCHAR2(255) NOT NULL, 
                h5 VARCHAR2(255) NOT NULL, 
                h6 VARCHAR2(255) NOT NULL, 
                h7 VARCHAR2(255) NOT NULL, 
                h8 VARCHAR2(255) NOT NULL, 
                h9 VARCHAR2(255) NOT NULL, 
                h10 VARCHAR2(255) NOT NULL, 
                h11 VARCHAR2(255) NOT NULL, 
                h12 VARCHAR2(255) NOT NULL, 
                h13 VARCHAR2(255) NOT NULL, 
                h14 VARCHAR2(255) NOT NULL, 
                h15 VARCHAR2(255) NOT NULL, 
                h16 VARCHAR2(255) NOT NULL, 
                h17 VARCHAR2(255) NOT NULL, 
                h18 VARCHAR2(255) NOT NULL, 
                h19 VARCHAR2(255) NOT NULL, 
                h20 VARCHAR2(255) NOT NULL, 
                h21 VARCHAR2(255) NOT NULL, 
                h22 VARCHAR2(255) NOT NULL, 
                h23 VARCHAR2(255) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP (
                h0, h1, h2, h3, h4, h5, h6, h7, h8, h9, h10, 
                h11, h12, h13, h14, h15, h16, h17, h18, 
                h19, h20, h21, h22, h23
            )
        ");
    }
}