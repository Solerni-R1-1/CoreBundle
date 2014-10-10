<?php

namespace Claroline\CoreBundle\Migrations\oci8;

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
                id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                workspace_id NUMBER(10) DEFAULT NULL, 
                nbPublicationsForum VARCHAR2(255) NOT NULL, 
                nbActivity VARCHAR2(255) NOT NULL, 
                last_action_date DATE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_ANALYTICS_USER_MOOC_STATS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_ANALYTICS_USER_MOOC_STATS ADD CONSTRAINT CLARO_ANALYTICS_USER_MOOC_STATS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_ANALYTICS_USER_MOOC_STATS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_ANALYTICS_USER_MOOC_STATS_AI_PK BEFORE INSERT ON CLARO_ANALYTICS_USER_MOOC_STATS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_ANALYTICS_USER_MOOC_STATS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_ANALYTICS_USER_MOOC_STATS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_ANALYTICS_USER_MOOC_STATS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_ANALYTICS_USER_MOOC_STATS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_315980BAA76ED395 ON claro_analytics_user_mooc_stats (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_315980BA82D40A1F ON claro_analytics_user_mooc_stats (workspace_id)
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
            DROP (nbActiveUsers)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_user_mooc_stats
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD (
                nbActiveUsers VARCHAR2(255) NOT NULL
            )
        ");
    }
}