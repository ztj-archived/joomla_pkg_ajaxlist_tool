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
    public function __construct($form = null) {
        parent::__construct($form);
        $this->setScript();
    }
    protected function getInput() {
        //url create
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
        $this->url = JRoute::_('index.php?'.http_build_query($urlparams),false);
        //chosenAjaxSettings
        $id = isset($this->element['id'])?$this->element['id']:null;
        $cssId = '#'.$this->getId($id,$this->element['name']);
        $this->method = isset($this->element['method'])?(int)$this->element['method']:'GET';
        $this->key = isset($this->element['key'])?(int)$this->element['key']:'keyword';
        $mintermlength = isset($this->element['mintermlength'])?(int)$this->element['mintermlength']:2;
        $defaultValue = json_encode($this->value);
        $chosenAjaxSettings = new Registry(array(
            'selector' => $cssId,
            'defaultValue' => $defaultValue,
            'type' => $this->method,
            'url' => $this->url,
            'multiple' => $this->multiple,
            'required' => $this->required,
            'dataType' => 'json',
            'jsonTermKey' => $this->key,
            'minTermLength' => $mintermlength));
        $this->setExtendAjaxChosen($chosenAjaxSettings);

        return parent::getInput();
    }
    protected function getOptions() {
        $options = parent::getOptions();
        if(!empty($this->value)) {
            foreach((array)$this->value as $value) {
                $option = new stdClass;
                $option->text = $value;
                $option->value = $value;
                $option->selected = true;
                $options[] = $option;
            }
        }
        return $options;
    }
    protected function setExtendAjaxChosen(Registry $options) {
        $selector = $options->get('selector','.tagfield');
        $defaultValue = $options->get('defaultValue','null');
        $type = $options->get('type','GET');
        $url = $options->get('url',null);
        $dataType = $options->get('dataType','json');
        $jsonTermKey = $options->get('jsonTermKey','term');
        $afterTypeDelay = $options->get('afterTypeDelay','500');
        $minTermLength = $options->get('minTermLength','2');
        $multiple = (int)$options->get('multiple',0);
        $required = (int)$options->get('required',0);

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("
            jQuery(document).ready(function($) {
                $('{$selector}').extendAjaxChosen({
                    defaultValue:{$defaultValue},
                    type: '{$type}',
                    url: '{$url}',
                    dataType: '{$dataType}',
                    jsonTermKey: '{$jsonTermKey}',
                    afterTypeDelay: '{$afterTypeDelay}',
                    minTermLength: '{$minTermLength}',
                    multiple: {$multiple},
                    required: {$required},
                },
                function(data) {
                    var results = [];
                    $.each(data,
                    function(i, val) {
                        results.push({
                            value: val.value,
                            text: val.text
                        });
                    });
            
                    return results;
                });
            });
        ");
    }
    static $isScript = false;
    protected function setScript() {
        if(self::$isScript) {
            return;
        }
  		JText::script('JGLOBAL_KEEP_TYPING');
		JText::script('JGLOBAL_LOOKING_FOR');
        JHtml::_('jquery.framework');
        JHtml::_('formbehavior.chosen','select');
        JHtml::_('script','jui/ajax-chosen.min.js',false,true,false,false);
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration('
            (function($) {
                $.fn.extendAjaxChosen = function(settings, callback, chosenOptions) {
                    //获取相关节点
                    var nodeSelect=this;
                    var nodeOption;
                    //默认值和ajax
                    var defaultValue=settings.defaultValue;
                    if(defaultValue && defaultValue != ""){
                        var options = $.extend({}, {}, $(nodeSelect).data(), settings);
                        options.url=settings.url+"&default=1&"+settings.jsonTermKey+"="+defaultValue;
                        //ajax处理
                        options.success = function(data) {
                            if (!(data != null)) {
                                return;
                            }
                            items = callback(data);
                            $.each(items, function(i, element) {
                                nodeOption=nodeSelect.find("option[value=\'" + element.value + "\']");
                                if(nodeOption.length == 1){
                                    nodeOption.text(element.text);
                                }
                            });
                            nodeSelect.trigger("liszt:updated");
                            
                        }
                        //ajax查询
                        $.ajax(options);
                    }
                    //单选处理
                    if(!settings.multiple && nodeSelect.data().chosen){
                        $("<option />").attr("value","").html("").prependTo(nodeSelect);
                        nodeSelect.data().chosen.disable_search_threshold=0;
                        nodeSelect.data().chosen.allow_custom_value=0;
                    }
                    //多选处理
                    if(settings.multiple){
                        //多选值为null的处理
                        if(nodeSelect.val() == null){
                            $("<option />").attr("value","").attr("selected","").html("").prependTo(nodeSelect);
                        }
                        //多选的改变事件
                        nodeSelect.chosen().change(function(){
                            var nodeEmptyOption=nodeSelect.children("[value=\'\']");
                            if(nodeEmptyOption.length > 0){
                                nodeEmptyOption.remove();
                                nodeSelect.trigger("liszt:updated");
                            }
                            if(nodeSelect.val() == null){
                                $("<option />").attr("value","").attr("selected","").html("").prependTo(nodeSelect);
                            }
                        });
                    }
                    //必填处理
                    if(!settings.required && nodeSelect.data().chosen){
                        nodeSelect.data().chosen.allow_single_deselect=true;
                    }
                    //执行原始对象
                    this.ajaxChosen(settings, callback, chosenOptions);
                    nodeSelect.trigger("liszt:updated");
                }
            })(jQuery);
        ');
        self::$isScript = true;
    }
}
