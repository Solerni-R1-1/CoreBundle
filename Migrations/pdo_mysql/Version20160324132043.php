<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/03/24 01:20:58
 */
class Version20160324132043 extends AbstractMigration
{
    public function up(Schema $schema)
    {

        $this->addSql(" UPDATE claro_user SET notifarticle=1; ");
        $this->addSql(" UPDATE claro_user SET notifsujettheme=1; ");
        $this->addSql(" UPDATE claro_user SET notifciter=1;");
        $this->addSql(" UPDATE claro_user SET notiflike=1; ");


    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc CHANGE notifarticlemooc notifarticlemooc TINYINT(1) DEFAULT '0' NOT NULL, 
            CHANGE notifsujetthememooc notifsujetthememooc TINYINT(1) DEFAULT '0' NOT NULL, 
            CHANGE notifcitermooc notifcitermooc TINYINT(1) DEFAULT '0' NOT NULL, 
            CHANGE notiflikemooc notiflikemooc TINYINT(1) DEFAULT '0' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user CHANGE notifarticle notifarticle TINYINT(1) DEFAULT '1' NOT NULL, 
            CHANGE notifsujettheme notifsujettheme TINYINT(1) DEFAULT '1' NOT NULL, 
            CHANGE notifciter notifciter TINYINT(1) DEFAULT '1' NOT NULL, 
            CHANGE notiflike notiflike TINYINT(1) DEFAULT '1' NOT NULL
        ");
    }
}