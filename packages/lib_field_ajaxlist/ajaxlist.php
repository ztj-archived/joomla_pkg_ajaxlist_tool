<?php

/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Phpsj.com All rights reserved.
 * @license     GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;
defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
JFormHelper::loadFieldClass('list');

class JFormFieldAjaxList extends JFormFieldList {
    public $type = 'AjaxList';
    public $isNested = null;
    protected function getInput() {
        $oter = isset($this->element['oter'])?$this->element['oter']:'';
        $oters = explode(',',$oter);
        foreach($oters as $oter) {
            $urlparams[$oter] = isset($this->element[$oter])?(string )$this->element[$oter]:null;
        }
        $urlparams['option'] = isset($this->element['option'])?(string )$this->element['option']:'com_ajax';
        $urlparams['task'] = isset($this->element['task'])?(string )$this->element['task']:null;
        $urlparams['plugin'] = isset($this->element['plugin'])?(string )$this->element['plugin']:null;
        $urlparams['format'] = isset($this->element['format'])?(int)$this->element['format']:null;
        if($urlparams['option'] == 'com_ajax' && empty($urlparams['format'])) {
            $urlparams['format'] = 'text';
        }
        $urlparams = array_filter($urlparams);
        $this->url = JUri::root().'index.php?'.http_build_query($urlparams);

        $id = isset($this->element['id'])?$this->element['id']:null;
        $cssId = '#'.$this->getId($id,$this->element['name']);
        $this->method = isset($this->element['method'])?(int)$this->element['method']:'GET';
        $this->key = isset($this->element['key'])?(int)$this->element['key']:'keyword';
        $mintermlength = isset($this->element['mintermlength'])?(int)$this->element['mintermlength']:3;
        $chosenAjaxSettings = new Registry(array(
            'selector' => $cssId,
            'type' => $this->method,
            'url' => $this->url,
            'dataType' => 'json',
            'jsonTermKey' => $this->key,
            'minTermLength' => $mintermlength));

        JHtml::_('jquery.framework');
        JHtml::_('script','jui/ajax-chosen.min.js',false,true,false,false);
        JHtml::_('formbehavior.ajaxchosen',$chosenAjaxSettings);

        $this->multiple = true;
        return parent::getInput();
    }
    protected function getOptions() {
        $options = parent::getOptions();
        if(!empty($this->value)) {
            $url = $this->url.'&'.$this->key.'=default-'.implode('-',$this->value);
            $json = $this->curl_file_get_contents($url);
            $defaultOptions = json_decode($json);
            foreach($defaultOptions as $option) {
                $option->selected = true;
                $options[] = $option;
            }
        }
        return $options;
    }
    protected function curl_file_get_contents($url) {
        $login = isset($this->element['login'])?$this->element['login']:null;

        $ch = curl_init();
        switch(strtoupper($this->method)) {
            case 'GET':
                curl_setopt($ch,CURLOPT_HTTPGET,true);
                break;
            case 'POST':
                curl_setopt($ch,CURLOPT_POST,true);
                break;
            case 'PUT':
            default:
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,strtoupper($method));
                break;
        }
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if($login) {
            curl_setopt($ch,CURLOPT_COOKIE,apache_request_headers()['Cookie']);
        }
        $rs = curl_exec($ch);
        curl_close($ch);
        return $rs;
    }
}
