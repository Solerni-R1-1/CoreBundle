<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/01 02:58:15
 */
class Version20141201145809 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_mooc_preferences (
                mooc_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                visibility INTEGER NOT NULL, 
                PRIMARY KEY(mooc_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987255EEB87 ON claro_user_mooc_preferences (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987A76ED395 ON claro_user_mooc_preferences (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences 
            ADD CONSTRAINT FK_CEF67987255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id)
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_preferences 
            ADD CONSTRAINT FK_CEF67987A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP FOREIGN KEY FK_805FCB8F16F956BA
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F16F956BA FOREIGN KEY (associated_badge) 
            REFERENCES claro_badge (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_user_mooc_preferences
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP FOREIGN KEY FK_805FCB8F16F956BA
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F16F956BA FOREIGN KEY (associated_badge) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
        ");
    }
}