<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/03 02:34:08
 */
class Version20141003143407 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_user_mooc_stats (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                nbPublicationsForum VARCHAR(255) NOT NULL, 
                nbActivity VARCHAR(255) NOT NULL, 
                last_action_date DATE NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_315980BAA76ED395 (user_id), 
                INDEX IDX_315980BA82D40A1F (workspace_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_user_mooc_stats 
            ADD CONSTRAINT FK_315980BAA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_user_mooc_stats 
            ADD CONSTRAINT FK_315980BA82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP nbActiveUsers
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD nbActiveUsers VARCHAR(255) NOT NULL
        ");
    }
}