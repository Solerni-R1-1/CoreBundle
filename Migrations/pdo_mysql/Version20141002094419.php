<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 09:44:20
 */
class Version20141002094419 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_last_preparation (
                id INT AUTO_INCREMENT NOT NULL, 
                classname VARCHAR(255) NOT NULL, 
                datetime DATETIME NOT NULL, 
                UNIQUE INDEX UNIQ_64C6F61093F3C6CA (datetime), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_last_preparation
        ");
    }
}