<?php
/**
 * Alias - dca field definition
 *
 * @category	pearls
 * @copyright	HOME
 * @author		Dirk Holstein <dh@holsteinmedia.com>
 */

namespace Home\PearlsBundle\Resources\contao\Helper\Dca\Fields;

class Alias extends Base
{
    /**
     * alias field settings
     * @var array
     */
    const SETTINGS = array (
        'inputType'		=> 'text',
        'exclude'       => true,
        'search'        => true,
        'eval'          => [
            'rgxp' => 'alias',
            'unique' => true,
            'maxlength' => 128,
            //'tl_class' => 'w50',
            'style' => '
                background-color: #F9F9F9;
                color: #bbb;
                border: 1px solid #c8c8c8;',
        ],
        'save_callback' => array(
            array('Home\PearlsBundle\Resources\contao\Helper\Dca\Fields\Alias','getAliasFromRefField')
        ),
        'refField'      => 'title',
        'sql'           => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
    );

    public static function getSettings()
    {
        return array_merge(parent::getSettings(), self::SETTINGS);
    }

    /**
     * Generate alias
     *
     * @param $varValue
     * @param \Contao\DataContainer $dc
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getAliasFromRefField($varValue,  $dc)
    {
        /**
         * when using an array as refField add in first place the separator like: 'refField' => array('_', 'field_1', 'field_2')
         */
        if (is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['refField'])) {
            $fields = $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['refField'];
            $separator = array_shift($fields);

            $refValue = "";
            forEach($fields as $field) {
                $refValue .= $refValue =="" ? $dc->activeRecord->$field : $separator.$dc->activeRecord->$field;
            }
        } else {
            $field = $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['refField'];
            $refValue = $dc->activeRecord->$field;
        }

        return self::createAlias($varValue, $refValue, $dc->table, $dc->id);
    }

    /**
     * creates an alias
     *
     * @param $alias
     * @param $string
     * @param $dbTable
     * @param int $rowId
     * @return string
     * @throws \Exception
     */
    public static function createAlias($alias, $string, $dbTable, $rowId=0)
    {
        if ($alias == "") {
            if (!$string) {
                throw new \Exception('Could not create an alias from empty strings. [Pearls\Form\Fields\Alias]');
            }
            $alias = self::umlauteumwandeln($string);
            $alias = iconv('UTF-8', 'ASCII//TRANSLIT', $alias);
            $alias = \StringUtil::decodeEntities($alias);
            $alias = \StringUtil::restoreBasicEntities($alias);
            $alias = \StringUtil::standardize($alias);
        }

        $i = $rowId;
        $originalAlias = $alias;

        while (!self::aliasExists($alias, $dbTable, $i)) {
            $i++;
            $alias = $originalAlias.'-'.$i;
        }

        return $alias;
    }

    /**
     * proof if an alias exists only once in an db table
     *
     * @param $alias
     * @param $dbTable
     * @param int $rowId
     * @return bool
     * @throws \Exception
     */
    public static function aliasExists($alias, $dbTable, $rowId=0)
    {
        if ($alias == "" || $dbTable == "" ) {
            throw new \Exception('Parameter for proofAlias is missing. [Pearls\Form\Fields\Alias]');
        }
        $db   = \Database::getInstance();
        $strSelect = 'SELECT id FROM '.$dbTable.' WHERE alias=?';
        $strSelect .= ($rowId > 0 || (is_string($rowId) && strlen($rowId) > 0)) ? ' AND id != "'.$rowId.'"' : '';

        $objAlias = $db->prepare($strSelect)
            ->execute($alias);

        return ($objAlias->numRows < 1) ? true : false;
    }

    private static function umlauteumwandeln($str){
        $replaceArr = Array(
            "Ä" => "AE",
            "Ö" => "OE",
            "Ü" => "UE",
            "ä" => "ae",
            "ö" => "oe",
            "ü" => "ue",
            "ß" => "ss"
        );
        return strtr($str, $replaceArr);
    }
}
