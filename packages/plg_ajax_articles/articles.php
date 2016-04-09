<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Ajax
 *
 * @copyright   Phpsj.com All rights reserved.
 * @license     GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

class plgAjaxArticles extends JPlugin {
    function onAjaxArticles() {
        $db = JFactory::getDbo();

        $keyword = trim(JFactory::getApplication()->input->get('keyword'));
        if(empty($keyword)) {
            return json_encode(array());
        }
        $keyword = $db->quote('%'.$db->escape($keyword,true).'%',false);

        $query = $db->getQuery(true);
        $query->select('id as value,title as text');
        $query->from($db->quoteName('#__content'));

        if(substr($keyword,2,8) != 'default-') {
            $query->where('title like '.$keyword);
        } else {
            $ids = explode('-',substr($keyword,10,-2));
            JArrayHelper::toInteger($ids);
            $query->where('id in ('.implode(',',$ids).')');
        }

        $query->where('state = 1');
        $query->order($db->quoteName('id').' DESC');
        $db->setQuery($query,0,10);
        return json_encode($db->loadObjectList());
    }
}
