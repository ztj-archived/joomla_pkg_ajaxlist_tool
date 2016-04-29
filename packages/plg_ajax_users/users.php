<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Ajax
 *
 * @copyright   Phpsj.com All rights reserved.
 * @license     GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

class plgAjaxUsers extends JPlugin {
    function onAjaxUsers() {
        $input = JFactory::getApplication()->input;
        $default = $input->getInt('default',0);
        $keyword = trim($input->get('keyword',null,'string'));
        if(empty($keyword)) {
            return json_encode(array());
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id as value,username as text');
        $query->from($db->quoteName('#__users'));

        if($default) {
            $keyword=explode(',',$keyword);
            JArrayHelper::toInteger($keyword);
            $query->where('id in ('.implode(',',$keyword).')');
        } else {
            $keyword = $db->quote('%'.$db->escape($keyword,true).'%',false);
            $query->where('username like '.$keyword);
        }

        $query->order($db->quoteName('id').' DESC');
        $db->setQuery($query,0,10);
        $rows = $db->loadObjectList();
        if(empty($rows)) {
            return json_encode(array());
        }
        return json_encode($rows);
    }
}
