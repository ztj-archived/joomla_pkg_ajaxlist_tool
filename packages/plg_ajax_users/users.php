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
        $db = JFactory::getDbo();

        $keyword = trim(JFactory::getApplication()->input->get('keyword',null,'string'));
        if(empty($keyword)) {
            return json_encode(array());
        }
        $keyword = $db->quote('%'.$db->escape($keyword,true).'%',false);

        $query = $db->getQuery(true);
        $query->select('id as value,username as text');
        $query->from($db->quoteName('#__users'));

        if(substr($keyword,2,8) != 'default-') {
            $query->where('username like '.$keyword);
        } else {
            $ids = explode('-',substr($keyword,10,-2));
            JArrayHelper::toInteger($ids);
            $query->where('id in ('.implode(',',$ids).')');
        }

        $query->order($db->quoteName('id').' DESC');
        $db->setQuery($query,0,10);
        return json_encode($db->loadObjectList());
    }
}
