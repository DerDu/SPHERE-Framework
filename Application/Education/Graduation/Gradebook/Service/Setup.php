<?php
namespace SPHERE\Application\Education\Graduation\Gradebook\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();

        $tblGradeType = $this->setTableGradeType($Schema);
        $tblGradeText = $this->setTableGradeText($Schema);
        $this->setTableGrade($Schema, $tblGradeType, $tblGradeText);

        $this->setTableProposalBehaviorGrade($Schema, $tblGradeType);

        $tblScoreRule = $this->setTableScoreRule($Schema);
        $tblScoreCondition = $this->setTableScoreCondition($Schema);
        $tblScoreGroup = $this->setTableScoreGroup($Schema);
        $tblScoreType = $this->setTableScoreType($Schema);
        $this->setTableScoreRuleConditionList($Schema, $tblScoreRule, $tblScoreCondition);
        $this->setTableScoreConditionGradeTypeList($Schema, $tblGradeType, $tblScoreCondition);
        $this->setTableScoreConditionGroupList($Schema, $tblScoreCondition, $tblScoreGroup);
        $this->setTableScoreConditionGroupRequirement($Schema, $tblScoreCondition, $tblScoreGroup);
        $this->setTableScoreGroupGradeTypeList($Schema, $tblGradeType, $tblScoreGroup);
        $this->setTableScoreRuleDivisionSubject($Schema, $tblScoreRule, $tblScoreType);
        $this->setTableScoreRuleSubjectGroup($Schema, $tblScoreRule);
        $this->setTableMinimumGradeCount($Schema, $tblGradeType);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
        return $this->getConnection()->getProtocol($Simulate);
    }


    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableGradeType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGradeType');
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Code')) {
            $Table->addColumn('Code', 'string');
        }
        $this->getConnection()->removeIndex($Table, array('Code'));
        if (!$this->getConnection()->hasIndex($Table, array('Code', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Code', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'IsHighlighted')) {
            $Table->addColumn('IsHighlighted', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'serviceTblTestType')) {
            $Table->addColumn('serviceTblTestType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'IsActive')) {
            $Table->addColumn('IsActive', 'boolean', array('default' => true));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     * @param Table $tblGradeText
     *
     * @return Table
     */
    private function setTableGrade(Schema &$Schema, Table $tblGradeType, Table $tblGradeText)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGrade');
        if (!$this->getConnection()->hasColumn('tblGrade', 'Grade')) {
            $Table->addColumn('Grade', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'Comment')) {
            $Table->addColumn('Comment', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'Trend')) {
            $Table->addColumn('Trend', 'smallint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblSubjectGroup')) {
            $Table->addColumn('serviceTblSubjectGroup', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblPeriod')) {
            $Table->addColumn('serviceTblPeriod', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblTest')) {
            $Table->addColumn('serviceTblTest', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblTestType')) {
            $Table->addColumn('serviceTblTestType', 'bigint', array('notnull' => false));
        }
        if (!$Table->hasColumn('Date')) {
            $Table->addColumn('Date', 'datetime', array('notnull' => false));
        }
        $this->createColumn($Table, 'serviceTblPersonTeacher', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'PublicComment', self::FIELD_TYPE_STRING);

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);
        $this->createForeignKey($Table, $tblGradeText, true);

//        $this->createIndex($Table, array('serviceTblPerson', 'serviceTblTest'), false);
        $this->createIndex($Table, array('serviceTblPerson', 'serviceTblTest'), true);

//        // alten nicht unique index entfernen
//        if (($indexList = $Table->getIndexes())) {
//            foreach ($indexList as $index) {
//                if (!$index->isUnique()) {
//                    $hasPersonColumn = false;
//                    $hasTestColumn = false;
//                    if (($columns = $index->getColumns())) {
//                        foreach ($columns as $column) {
//                            if ($column == 'serviceTblPerson') {
//                                $hasPersonColumn = true;
//                            }
//                            if ($column == 'serviceTblTest') {
//                                $hasTestColumn = true;
//                            }
//                        }
//
//                        if ($hasPersonColumn && $hasTestColumn) {
//                            $Table->dropIndex($index->getName());
//                        }
//                    }
//                }
//            }
//        }
//        $Table->addUniqueIndex(array('serviceTblPerson', 'serviceTblTest'), 'UNIQ_TblGradeServiceTblPersonServiceTblTest');


        $this->createIndex($Table, array('serviceTblDivision', 'serviceTblSubject'), false);
        $this->createIndex($Table, array(TblGrade::ATTR_SERVICE_TBL_TEST, TblGrade::ENTITY_REMOVE), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableScoreRule(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreRule');
        if (!$this->getConnection()->hasColumn('tblScoreRule', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreRule', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreRule', 'IsActive')) {
            $Table->addColumn('IsActive', 'boolean', array('default' => true));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableScoreCondition(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreCondition');
        if (!$this->getConnection()->hasColumn('tblScoreCondition', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreCondition', 'Round')) {
            $Table->addColumn('Round', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreCondition', 'Priority')) {
            $Table->addColumn('Priority', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblScoreCondition', 'IsActive')) {
            $Table->addColumn('IsActive', 'boolean', array('default' => true));
        }
        $this->createColumn($Table, 'Period', self::FIELD_TYPE_INTEGER, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableScoreGroup(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreGroup');
        if (!$this->getConnection()->hasColumn('tblScoreGroup', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreGroup', 'Round')) {
            $Table->addColumn('Round', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreGroup', 'Multiplier')) {
            $Table->addColumn('Multiplier', 'string');
        }
        if (!$Table->hasColumn('IsEveryGradeASingleGroup')) {
            $Table->addColumn('IsEveryGradeASingleGroup', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblScoreGroup', 'IsActive')) {
            $Table->addColumn('IsActive', 'boolean', array('default' => true));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableScoreType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreType');
        if (!$this->getConnection()->hasColumn('tblScoreType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreType', 'Pattern')) {
            $Table->addColumn('Pattern', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblScoreRule
     * @param Table $tblScoreCondition
     *
     * @return Table
     */
    private function setTableScoreRuleConditionList(Schema &$Schema, Table $tblScoreRule, Table $tblScoreCondition)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreRuleConditionList');

        $this->getConnection()->addForeignKey($Table, $tblScoreRule, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreCondition, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     * @param Table $tblScoreCondition
     *
     * @return Table
     */
    private function setTableScoreConditionGradeTypeList(Schema &$Schema, Table $tblGradeType, Table $tblScoreCondition)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreConditionGradeTypeList');

        $this->createColumn($Table, 'Count', self::FIELD_TYPE_INTEGER, false, 1);

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreCondition, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblScoreCondition
     * @param Table $tblScoreGroup
     *
     * @return Table
     */
    private function setTableScoreConditionGroupList(Schema &$Schema, Table $tblScoreCondition, Table $tblScoreGroup)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreConditionGroupList');

        $this->getConnection()->addForeignKey($Table, $tblScoreCondition, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreGroup, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblScoreCondition
     * @param Table $tblScoreGroup
     *
     * @return Table
     */
    private function setTableScoreConditionGroupRequirement(Schema &$Schema, Table $tblScoreCondition, Table $tblScoreGroup)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreConditionGroupRequirement');
        $this->createColumn($Table, 'Count', self::FIELD_TYPE_INTEGER, false, 1);

        $this->getConnection()->addForeignKey($Table, $tblScoreCondition, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreGroup, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     * @param Table $tblScoreGroup
     *
     * @return Table
     */
    private function setTableScoreGroupGradeTypeList(Schema &$Schema, Table $tblGradeType, Table $tblScoreGroup)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreGroupGradeTypeList');
        if (!$this->getConnection()->hasColumn('tblScoreGroupGradeTypeList', 'Multiplier')) {
            $Table->addColumn('Multiplier', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreGroup, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblScoreRule
     * @param Table $tblScoreType
     *
     * @return Table
     */
    private function setTableScoreRuleDivisionSubject(Schema &$Schema, Table $tblScoreRule, Table $tblScoreType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreRuleDivisionSubject');

        if (!$this->getConnection()->hasColumn('tblScoreRuleDivisionSubject', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblScoreRuleDivisionSubject', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblScoreRule, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreType, true);

        $this->createIndex($Table, array('serviceTblDivision', 'serviceTblSubject'), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblScoreRule
     *
     * @return Table
     */
    private function setTableScoreRuleSubjectGroup(Schema &$Schema, Table $tblScoreRule)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreRuleSubjectGroup');

        if (!$this->getConnection()->hasColumn('tblScoreRuleSubjectGroup', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblScoreRuleSubjectGroup', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblScoreRuleSubjectGroup', 'serviceTblSubjectGroup')) {
            $Table->addColumn('serviceTblSubjectGroup', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblScoreRule, true);

        $this->createIndex($Table, array('serviceTblDivision', 'serviceTblSubject', 'serviceTblSubjectGroup'), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     *
     * @return Table
     */
    private function setTableMinimumGradeCount(Schema &$Schema, Table $tblGradeType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblMinimumGradeCount');
        if (!$Table->hasColumn('serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$Table->hasColumn('serviceTblLevel')) {
            $Table->addColumn('serviceTblLevel', 'bigint', array('notnull' => false));
        }
        if (!$Table->hasColumn('Count')) {
            $Table->addColumn('Count', 'integer');
        }
        $this->createColumn($Table, 'Period', self::FIELD_TYPE_INTEGER, false, SelectBoxItem::PERIOD_FULL_YEAR);
        $this->createColumn($Table, 'Highlighted', self::FIELD_TYPE_INTEGER, false, 1);
        $this->createColumn($Table, 'Course', self::FIELD_TYPE_INTEGER, false, SelectBoxItem::COURSE_NONE);

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableGradeText(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblGradeText');

        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Identifier', self::FIELD_TYPE_STRING);

        $this->createIndex($Table, array('Identifier'));

        return $Table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblGradeType
     *
     * @return Table
     */
    private function setTableProposalBehaviorGrade(Schema &$schema, Table $tblGradeType)
    {

        $Table = $this->getConnection()->createTable($schema, 'tblProposalBehaviorGrade');

        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, false);
        $this->createColumn($Table, 'serviceTblTask', self::FIELD_TYPE_BIGINT, false);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, false);
        $this->createColumn($Table, 'serviceTblPersonTeacher', self::FIELD_TYPE_BIGINT, false);
        $this->createColumn($Table, 'Grade', self::FIELD_TYPE_STRING, false);
        $this->createColumn($Table, 'Trend', 'smallint', false);
        $this->createColumn($Table, 'Comment', self::FIELD_TYPE_STRING, false);

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);

        $this->createIndex($Table, array('serviceTblDivision', 'serviceTblTask', 'serviceTblPerson', 'tblGradeType'), true);

        return $Table;
    }
}
