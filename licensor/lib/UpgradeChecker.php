<?php
/***********************************************************
  Upgrade Checker class. Will check agiletoolkit.org for new
  Agile Toolkit releases

  Reference:
  http://agiletoolkit.org/doc/ref

 **ATK4*****************************************************
 This file is part of Agile Toolkit 4 
 http://agiletoolkit.org

 (c) 2008-2011 Agile Technologies Ireland Limited
 Distributed under Affero General Public License v3

 If you are using this file in YOUR web software, you
 must make your make source code for YOUR web software
 public.

 See LICENSE.txt for more information

 You can obtain non-public copy of Agile Toolkit 4 at
 http://agiletoolkit.org/commercial

 *****************************************************ATK4**/
namespace licensor;
class UpgradeChecker extends \View {
    function init(){
        parent::init();

        $this->api->routePages('licensor');

        if(!$this->api->template->is_set('js_include'))return;  // no support in templtae
        $v=$this->api->getVersion().' '.$this->api->license();
        if($this->api->license()=='unlicensed'){
            $v='<a href="'.$this->api->url('licensor').'">'.$v.'</a>';
        }
        $this->setHTML($v);
        if($v[0]!=4)return;     // probably not ATK version

        if(isset($_COOKIE[$x=str_replace('/','_',$this->name).'_'.str_replace('.','_',
        $this->api->getVersion())]))return;

        $this->api->template->appendHTML('js_include',
            '<script async="true" onload="try{ atk4_version_check(\''.str_replace('/','_',$this->name).
                '\'); } catch(e){ }" type="text/javascript" src="http://agiletoolkit.org/upgrade_check/'.
        $this->api->getVersion().'.js?key='.$this->api->license_checksum().'"></script>'."\n");
    }
}
