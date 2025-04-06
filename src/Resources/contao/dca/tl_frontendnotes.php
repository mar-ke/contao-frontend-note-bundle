<?php

declare(strict_types=1);

// contao/dca/tl_frontendnotes.php
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Backend;

\Contao\System::loadLanguageFile('tl_page');

class tl_frontendnotes 
{
    public static function listFrontendNotes(array $arrRow): string
    {
        // Load the page model
        $objPage = \Contao\PageModel::findByPk($arrRow['page']);

        // Return the title of the page if it exists, otherwise return the page ID
        return $arrRow['title']; 
    }

    public static function groupByPage($group, $mode, $field, $row, DataContainer $dc): string
    {
        // Load the page model
        $objPage = \Contao\PageModel::findByPk($row[$field]);
        
        if ( isset ( $objPage->id ) ) {
            
            $titleWithLink = "<a href='contao/preview?page=".$objPage->id."' target='blank'>".$objPage->title ."</a>";
            
        }
        
        // Return the title of the page if it exists, otherwise return the page ID
        return $objPage ? $titleWithLink : $group;
    }
}

$GLOBALS['TL_DCA']['tl_frontendnotes'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'switchToEdit' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'tstamp' => 'index',
            ],
        ],
    ],
    
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['page'],
            'flag' => 11,
            'panelLayout' => 'search,limit'
            
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
            'label_callback' => ['tl_frontendnotes', 'listFrontendNotes'],
            'group_callback' => ['tl_frontendnotes', 'groupByPage']
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg'
            ],
        ],
    ],    

    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'mandatory' => true],
            'sql' => "TEXT NOT NULL default ''",
        ],
        'yCoordinate' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'xCoordinate' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'clr w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'pArticle' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'bgColor' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'page' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'user' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'userinfo' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'mandatory' => true],
            'sql' => "TEXT NOT NULL default ''",
        ],
    ],

    'palettes' => [
        'default' => '{vendor_legend},title;{position_legend},yCoordinate,xCoordinate,pArticle,bgColor,page,user,userinfo',
    ],
];