<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

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
            ADD COLUMN h0 VARCHAR(255) NOT NULL 
            ADD COLUMN h1 VARCHAR(255) NOT NULL 
            ADD COLUMN h2 VARCHAR(255) NOT NULL 
            ADD COLUMN h3 VARCHAR(255) NOT NULL 
            ADD COLUMN h4 VARCHAR(255) NOT NULL 
            ADD COLUMN h5 VARCHAR(255) NOT NULL 
            ADD COLUMN h6 VARCHAR(255) NOT NULL 
            ADD COLUMN h7 VARCHAR(255) NOT NULL 
            ADD COLUMN h8 VARCHAR(255) NOT NULL 
            ADD COLUMN h9 VARCHAR(255) NOT NULL 
            ADD COLUMN h10 VARCHAR(255) NOT NULL 
            ADD COLUMN h11 VARCHAR(255) NOT NULL 
            ADD COLUMN h12 VARCHAR(255) NOT NULL 
            ADD COLUMN h13 VARCHAR(255) NOT NULL 
            ADD COLUMN h14 VARCHAR(255) NOT NULL 
            ADD COLUMN h15 VARCHAR(255) NOT NULL 
            ADD COLUMN h16 VARCHAR(255) NOT NULL 
            ADD COLUMN h17 VARCHAR(255) NOT NULL 
            ADD COLUMN h18 VARCHAR(255) NOT NULL 
            ADD COLUMN h19 VARCHAR(255) NOT NULL 
            ADD COLUMN h20 VARCHAR(255) NOT NULL 
            ADD COLUMN h21 VARCHAR(255) NOT NULL 
            ADD COLUMN h22 VARCHAR(255) NOT NULL 
            ADD COLUMN h23 VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h0 
            DROP COLUMN h1 
            DROP COLUMN h2 
            DROP COLUMN h3 
            DROP COLUMN h4 
            DROP COLUMN h5 
            DROP COLUMN h6 
            DROP COLUMN h7 
            DROP COLUMN h8 
            DROP COLUMN h9 
            DROP COLUMN h10 
            DROP COLUMN h11 
            DROP COLUMN h12 
            DROP COLUMN h13 
            DROP COLUMN h14 
            DROP COLUMN h15 
            DROP COLUMN h16 
            DROP COLUMN h17 
            DROP COLUMN h18 
            DROP COLUMN h19 
            DROP COLUMN h20 
            DROP COLUMN h21 
            DROP COLUMN h22 
            DROP COLUMN h23
        ");
    }
}