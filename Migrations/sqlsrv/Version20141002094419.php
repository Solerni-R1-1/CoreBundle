<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 09:44:21
 */
class Version20141002094419 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_last_preparation (
                id INT IDENTITY NOT NULL, 
                classname NVARCHAR(255) NOT NULL, 
                datetime DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_64C6F61093F3C6CA ON claro_analytics_last_preparation (datetime) 
            WHERE datetime IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_last_preparation
        ");
    }
}