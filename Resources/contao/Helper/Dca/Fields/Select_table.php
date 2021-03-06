<?php
/**
 * Select - dca field definition
 *
 * @category	pearls
 * @copyright	HOME
 * @author		Dirk Holstein <dh@holsteinmedia.com>
 *
 */

namespace Home\PearlsBundle\Resources\contao\Helper\Dca\Fields;

class Select_table extends Base
{
    /**
     * select field settings
     * @var array
     */
    const SETTINGS = array (
        'exclude'                 	=> true,
        'inputType'              	=> 'select',
        'sql'                    	=> "varchar(255) NOT NULL default ''",
        'options'                   => array(
            'eval'       => array(
                'mandatory'=>true
            ),
        )
    );

    public static function getSettings()
    {
        return array_replace_recursive(parent::getSettings(), self::SETTINGS);
    }
}
