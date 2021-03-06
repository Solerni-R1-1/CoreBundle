<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/20 06:30:53
 */
class Version20141020183052 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_D6FE8DD8F624B39D
        ");
        $this->addSql("
            DROP INDEX IDX_D6FE8DD8727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_message AS 
            SELECT id, 
            sender_id, 
            object, 
            content, 
            date, 
            is_removed, 
            root, 
            sender_username, 
            receiver_string 
            FROM claro_message
        ");
        $this->addSql("
            DROP TABLE claro_message
        ");
        $this->addSql("
            CREATE TABLE claro_message (
                id INTEGER NOT NULL, 
                sender_id INTEGER DEFAULT NULL, 
                root INTEGER DEFAULT NULL, 
                object VARCHAR(255) NOT NULL, 
                content CLOB NOT NULL, 
                date DATETIME NOT NULL, 
                is_removed BOOLEAN NOT NULL, 
                sender_username VARCHAR(255) NOT NULL, 
                receiver_string VARCHAR(1023) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D6FE8DD8F624B39D FOREIGN KEY (sender_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D6FE8DD816F4F95B FOREIGN KEY (root) 
                REFERENCES claro_message (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_message (
                id, sender_id, object, content, date, 
                is_removed, root, sender_username, 
                receiver_string
            ) 
            SELECT id, 
            sender_id, 
            object, 
            content, 
            date, 
            is_removed, 
            root, 
            sender_username, 
            receiver_string 
            FROM __temp__claro_message
        ");
        $this->addSql("
            DROP TABLE __temp__claro_message
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD8F624B39D ON claro_message (sender_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD816F4F95B ON claro_message (root)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_D6FE8DD8F624B39D
        ");
        $this->addSql("
            DROP INDEX IDX_D6FE8DD816F4F95B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_message AS 
            SELECT id, 
            sender_id, 
            root, 
            object, 
            content, 
            date, 
            is_removed, 
            sender_username, 
            receiver_string 
            FROM claro_message
        ");
        $this->addSql("
            DROP TABLE claro_message
        ");
        $this->addSql("
            CREATE TABLE claro_message (
                id INTEGER NOT NULL, 
                sender_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                root INTEGER DEFAULT NULL, 
                object VARCHAR(255) NOT NULL, 
                content CLOB NOT NULL, 
                date DATETIME NOT NULL, 
                is_removed BOOLEAN NOT NULL, 
                sender_username VARCHAR(255) NOT NULL, 
                receiver_string VARCHAR(1023) NOT NULL, 
                lft INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D6FE8DD8F624B39D FOREIGN KEY (sender_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D6FE8DD8727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_message (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_message (
                id, sender_id, root, object, content, 
                date, is_removed, sender_username, 
                receiver_string
            ) 
            SELECT id, 
            sender_id, 
            root, 
            object, 
            content, 
            date, 
            is_removed, 
            sender_username, 
            receiver_string 
            FROM __temp__claro_message
        ");
        $this->addSql("
            DROP TABLE __temp__claro_message
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD8F624B39D ON claro_message (sender_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD8727ACA70 ON claro_message (parent_id)
        ");
    }
}