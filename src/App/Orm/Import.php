<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2019  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\App\Orm;

use Dvelum\Orm;
use Dvelum\Orm\Record\Builder;
use Dvelum\Db\Adapter;
use \Ext_Factory;

class Import
{
    /**
     * Convert orm field into ext object
     * 
     * @param string $name            
     * @param array $fieldConfig - field info from Db_Object_Config
     * @return array| null
     */
    static public function convertOrmFieldToExtField(string $name , array $fieldConfig) : ?array
    {
        //$designerConfig  = \Dvelum\Config::storage()->get('designer.php');
        $type = $fieldConfig['db_type'];
        $newField = false;
        
        /*
         * Adapter
         */
        if(isset($fieldConfig['type']) && $fieldConfig['type'] === 'link')
        {
            if($fieldConfig['link_config']['link_type'] == 'dictionary'){
                $newField = Ext_Factory::object('Component_Field_System_Dictionary');
                if($fieldConfig['required']){
                  $newField->forceSelection = true;
                }else{
                  $newField->forceSelection = false;
                }
                $newField->dictionary = $fieldConfig['link_config']['object'];
            }elseif($fieldConfig['link_config']['link_type'] == Orm\Record\Config::LINK_OBJECT && $fieldConfig['link_config']['object'] == 'medialib'){
                $newField = Ext_Factory::object('Ext_Component_Field_System_Medialibitem');
            }elseif($fieldConfig['link_config']['link_type'] == Orm\Record\Config::LINK_OBJECT){
                $newField = Ext_Factory::object('Ext_Component_Field_System_Objectlink');
                $newField->objectName = $fieldConfig['link_config']['object'];
            }elseif($fieldConfig['link_config']['link_type'] == Orm\Record\Config::LINK_OBJECT_LIST){
                $newField = Ext_Factory::object('Ext_Component_Field_System_Objectslist');
                $newField->objectName = $fieldConfig['link_config']['object'];
            }else{
                $newField = Ext_Factory::object('Form_Field_Text');
            }
        }
        /*
         * Boolean
         */
        elseif($type === 'boolean')
        {
            $newField = Ext_Factory::object('Form_Field_Checkbox');
            $newField->inputValue = 1;
            $newField->uncheckedValue = 0;
        }
        /*
         * Integer
         */
        elseif(in_array($type , Builder::$intTypes , true))
        {
            $newField = Ext_Factory::object('Form_Field_Number');
            $newField->allowDecimals = false;
        }
        /*
         * Float
         */
        elseif(in_array($type , Builder::$floatTypes , true))
        {
            $newField = Ext_Factory::object('Form_Field_Number');
            $newField->allowDecimals = true;
            $newField->decimalSeparator = ',';
            
            if(isset($fieldConfig['db_precision']))
              $newField->decimalPrecision = $fieldConfig['db_precision'];
            else
              $newField->decimalPrecision = 2;
        }
        /*
         * String
         */
        elseif(in_array($type , Builder::$charTypes , true))
        {
            $newField = Ext_Factory::object('Form_Field_Text');
        }
        /*
         * Text
         */
        elseif(in_array($type , Builder::$textTypes , true))
        {
            if($designerConfig->get('html_editor') && isset($fieldConfig['allow_html']) && $fieldConfig['allow_html']){
                $newField = Ext_Factory::object('Component_Field_System_Medialibhtml');
                $newField->editorName = $name;
                $newField->title = $fieldConfig['title'];
                $newField->frame = false;
            }else{
                $newField = Ext_Factory::object('Form_Field_Textarea');
            }
        }
        /*
         * Date time
         */
        elseif(in_array($type , Builder::$dateTypes , true))
        {
            switch($type){
                case 'date':
                    $newField = Ext_Factory::object('Form_Field_Date');
                    $newField->format = 'Y-m-d';
                    $newField->submitFormat = 'Y-m-d';
                    $newField->altFormats = 'Y-m-d';
                    break;
                case 'datetime':
                case 'timestamp':
                    $newField = Ext_Factory::object('Form_Field_Date');
                    $newField->format = 'Y-m-d H:i:s';
                    $newField->submitFormat = 'Y-m-d H:i:s';
                    $newField->altFormats = 'Y-m-d H:i:s';
                    break;
                case 'time':
                    $newField = Ext_Factory::object('Form_Field_Time');
                    $newField->format = 'H:i:s';
                    $newField->submitFormat = 'H:i:s';
                    $newField->altFormats = 'H:i:s';
                    break;
            }
        }
        /*
         * Undefined type
         */
        else
        {
            $newField = Ext_Factory::object('Form_Field_Text');
        }
        
        $newFieldConfig = $newField->getConfig();
        
        if($newFieldConfig->isValidProperty('name'))
           $newField->name = $name;
              
        if(isset($fieldConfig['db_default']) && $fieldConfig['db_default']!==false && $newFieldConfig->isValidProperty('value'))
          $newField->value  = $fieldConfig['db_default'];
         
        if(in_array($type, Builder::$numTypes , true) && isset($fieldConfig['db_unsigned']) && $fieldConfig['db_unsigned'] && $newFieldConfig->isValidProperty('minValue'))
          $newField->minValue = 0;
                           
        if($newField->getClass() != 'Component_Field_System_Medialibhtml' && $newFieldConfig->isValidProperty('fieldLabel'))
          $newField->fieldLabel = $fieldConfig['title'];
        
        if($newField->getClass() === 'Component_Field_System_Objectslist')
          $newField->title = $fieldConfig['title'];
        
        if(isset($fieldConfig['required']) && $fieldConfig['required'] && $newFieldConfig->isValidProperty('allowBlank'))
          $newField->allowBlank = false;
        
        return $newField;
    }
}