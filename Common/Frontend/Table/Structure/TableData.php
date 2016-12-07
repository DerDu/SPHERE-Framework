<?php
namespace SPHERE\Common\Frontend\Table\Structure;

use SPHERE\Common\Frontend\Table\Repository\Title;

/**
 * Class TableData
 *
 * @package SPHERE\Common\Frontend\Table\Structure
 */
class TableData extends Table
{

    /**
     * @param string|Object[] $DataList
     * @param Title $TableTitle
     * @param array $ColumnDefinition
     * @param bool|array $Interactive
     * @param bool $useHtmlRenderer false JS, true DOM
     */
    public function __construct(
        $DataList,
        Title $TableTitle = null,
        $ColumnDefinition = array(),
        $Interactive = true,
        $useHtmlRenderer = false
    ) {

        /**
         *
         */
        if (is_bool($DataList)) {
            $DataList = array();
        }

        /**
         * Server-Side-Processing
         */
        if (is_string($DataList) && ( $Interactive || is_array($Interactive) )) {

            $DataColumns = array_keys($ColumnDefinition);
            array_walk($DataColumns, function (&$V) {

                $V = array('data' => $V);
            });
            if (is_array($Interactive)) {
                $Interactive = array_merge_recursive($Interactive, array(
                    "processing" => true,
                    "serverSide" => true,
                    "ajax"       => array(
                        "url"  => $this->getRequest()->getUrlBase().$DataList,
                        "type" => "POST"
                    ),
                    "columns"    => $DataColumns
                ));
            } else {
                $Interactive = array(
                    "processing" => true,
                    "serverSide" => true,
                    "ajax"       => array(
                        "url"  => $this->getRequest()->getUrlBase().$DataList,
                        "type" => "POST"
                    ),
                    "columns"    => $DataColumns
                );
            }
            $DataList = array();
        }

        /**
         *
         */
        if (!is_array($DataList)) {
            $DataList = array($DataList);
        }
        if (empty( $ColumnDefinition ) && !empty( $DataList )) {
            if (is_object(current($DataList))) {
                /** @var Object[] $DataList */
                $GridHead = array_keys(current($DataList)->__toArray());
            } else {
                $GridHead = array_keys(current($DataList));
            }
        } elseif (!empty( $ColumnDefinition )) {
            // Rename by ShowCol
            $GridHead = array_values($ColumnDefinition);
        } else {
            $GridHead = array();
        }

        if (empty( $ColumnDefinition )) {
            $ColumnDefinition = array_combine(array_values($GridHead), array_values($GridHead));
        }

        if (!$useHtmlRenderer && (count($DataList) > 0 && $Interactive)) {
            // JS Table Data
            $ObjectList = array();
            array_walk($DataList, function (&$Row) use (&$ObjectList, $ColumnDefinition) {

                array_walk($Row, function (&$Column, $Index) use ($ColumnDefinition, $Row) {

                    /**
                     * With Object, use getter instead of property (if available)
                     */
                    if (is_object($Column) && method_exists($Row, 'get'.substr(trim($Index), 2))) {
                        $Column = $Row->{'get'.substr(trim($Index), 2)}();
                    }
                    /**
                     * Other values
                     */
                    if (empty( $ColumnDefinition )) {
                        $Column = (new TableColumn($Column))->getContent();
                    } elseif (in_array(preg_replace('!^[^a-z0-9_]*!is', '', $Index), array_keys($ColumnDefinition))) {
                        $Column = (new TableColumn($Column))->getContent();
                    } else {
                        $Column = false;
                    }
                });
                // Convert to Array
                if (is_object($Row)) {
                    /** @var Object $Row */
                    $Row = array_filter($Row->__toArray());
                } else {
                    $Row = array_filter($Row);
                }

                $Index = array_flip(array_keys($ColumnDefinition));
                array_walk($Index, function (&$Value) {

                    $Value = '';
                });
                /** @var array $Row */
                // Sort by ShowCol
                $Row = array_merge($Index, $Row);
            });
            $ObjectList = $DataList;

            $IndexList = array_keys($ColumnDefinition);
            array_walk($IndexList, function (&$Name) {

                $Name = array('data' => $Name);
            });

            $DataList = array();
        } else {
            // HTML Table Data
            /** @var TableRow[] $DataList */
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($DataList, function (&$Row, $Index, $Content) {

                array_walk($Row, function (&$Column, $Index, $Content) {

                    /**
                     * With Object, use getter instead of property (if available)
                     */
                    if (is_object($Column) && method_exists($Content[1], 'get'.substr(trim($Index), 2))) {
                        $Column = $Content[1]->{'get'.substr(trim($Index), 2)}();
                    }

                    /**
                     * Other values
                     */
                    if (empty( $Content[0] )) {
                        $Column = new TableColumn($Column);
                    } elseif (in_array(preg_replace('!^[^a-z0-9_]*!is', '', $Index), array_keys($Content[0]))) {
                        $Column = new TableColumn($Column);
                    } else {
                        $Column = false;
                    }
                }, array($Content, $Row));
                // Convert to Array
                if (is_object($Row)) {
                    /** @var Object $Row */
                    $Row = array_filter($Row->__toArray());
                } else {
                    $Row = array_filter($Row);
                }
                /** @var array $Row */
                // Sort by ShowCol
                $Row = array_merge(array_flip(array_keys($Content)), $Row);
                /** @noinspection PhpParamsInspection */
                $Row = new TableRow($Row);
            }, $ColumnDefinition);
        }

        array_walk($GridHead, function (&$V) {

            $V = new TableColumn($V);
        });

        if (count($DataList) > 0 || $Interactive) {

            if (isset( $ObjectList ) && isset( $IndexList )) {
                // Fix missing Data .. WHY DOES THIS WORK???
                $ObjectList = array_slice($ObjectList, 0);

                if (is_array($Interactive)) {
                    $Interactive = array_merge($Interactive, array('data' => $ObjectList, 'columns' => $IndexList));
                }
                if (!is_array($Interactive)) {
                    $Interactive = array('data' => $ObjectList, 'columns' => $IndexList);
                }
            }

            parent::__construct(
                new TableHead(new TableRow($GridHead)), new TableBody($DataList), $TableTitle,
                $Interactive, null
            );
        } else {
            if ($Interactive === null) {
                parent::__construct(
                    new TableHead(new TableRow($GridHead)), new TableBody($DataList), $TableTitle, null, null
                );
            } else {
                parent::__construct(
                    new TableHead(new TableRow($GridHead)), new TableBody($DataList), $TableTitle, false, null
                );
            }
        }
    }

}
