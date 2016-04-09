# joomla_lib_field_ajaxlist
&nbsp;&nbsp;&nbsp;&nbsp; 这是一个功能强大的动态ajax列表field，其强大的功能大家自己体验吧：http://www.phpsj.com/ajaxlistfield.html。

##使用示例：
```xml
<?xml version="1.0" encoding="utf-8"?>
<form> 
  <fieldset name="default" label="AjaxList Field Demo"> 
    <field name="articles" type="ajaxlist" label="Articles" plugin="articles" multiple="true"/>  
    <field name="categories" type="ajaxlist" label="Categories" plugin="categories" multiple="true"/>  
    <field name="tags" type="ajaxlist" label="Tags" plugin="tags" multiple="true"/> 
  </fieldset> 
</form>
```

##field参数说明：
* option：组件名，默认：com_ajax
* plugin：当 option=com_ajax 时的插件名称，默认：null
* task：task，默认：null
* format：数据格式化，默认：null
* oter：其他url参数，","号分割，默认null
* login：url是否需要登陆，默认：null。本参数测试中

##版本更新：
&nbsp;&nbsp;&nbsp;&nbsp;2016-04-10 <br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;发布版本V1.0 <br>