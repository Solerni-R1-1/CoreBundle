<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 05:37:17
 */
class Version20141002173716 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_hourly_mooc_stats (
                id NUMBER(10) NOT NULL, 
                workspace_id NUMBER(10) DEFAULT NULL, 
                action VARCHAR2(255) NOT NULL, 
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
                h23 VARCHAR2(255) NOT NULL, 
                \"date\" DATE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_ANALYTICS_HOURLY_MOOC_STATS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_ANALYTICS_HOURLY_MOOC_STATS ADD CONSTRAINT CLARO_ANALYTICS_HOURLY_MOOC_STATS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_ANALYTICS_HOURLY_MOOC_STATS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_ANALYTICS_HOURLY_MOOC_STATS_AI_PK BEFORE INSERT ON CLARO_ANALYTICS_HOURLY_MOOC_STATS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_ANALYTICS_HOURLY_MOOC_STATS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_ANALYTICS_HOURLY_MOOC_STATS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_ANALYTICS_HOURLY_MOOC_STATS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_ANALYTICS_HOURLY_MOOC_STATS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_C560845B82D40A1F ON claro_analytics_hourly_mooc_stats (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_hourly_mooc_stats 
            ADD CONSTRAINT FK_C560845B82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP (
                h0, h1, h2, h3, h4, h5, h6, h7, h8, h9, h10, 
                h11, h12, h13, h14, h15, h16, h17, h18, 
                h19, h20, h21, h22, h23
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_hourly_mooc_stats
        ");
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
}