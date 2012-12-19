<?php
namespace licensor;
class page_index extends \Page {
    function init(){
        parent::init();

        $this->add('View')->setHTML('Current license: <b>'.$this->api->license().'</b><br/>'.
                'Domain: <b>'.$_SERVER['HTTP_HOST'].'</b><br/>'.' Site fingerprint: <b>'.
        	$this->api->license_checksum().'</b>')
                ->addClass('float-right atk-box ui-widget-content ui-corner-all');

        $this->add('H1')->set('License Management for Agile Toolkit');

        if(!function_exists('openssl_verify')){
            $this->add('P')->setHTML('<font color="red">Your PHP installation does not have openssl extension enabled. Please, enable OpenSSL support</font>');    

        }


        $this->add('P')->set('Agile Toolkit is distributed under dual license. The same code-base is
        	available either under the terms of AGPL license or a commercial license. In either case,
        	for your copy to function properly, you need to register it. If any of the data will change after you
        		acquire your license, you can always re-issue a new one.');

        $cc=$this->add('Columns');
        $p=$cc->addColumn(6);
        $p->add('H3')->set('Open-Source License');

        $p->add('P')->set('AGPL is a industry standard open-source license. When you are using Agile Toolkit
        	under AGPL license you must publish results of your work for your users to see. We recommend
        	you to publish your software through Github.com or a similar social coding site.');

        $p->add('P')->set('Once your code is available, you must fill out registration form below. This form will
        	send your data to www.agiletoolkit.org which will generate a "certificate" for your copy of Agile Toolkit.
        	With that certificate you can continue to use Agile Toolkit as long as you keep open-source repository
        	up-to-date.');

        $f=$p->add('Form');
        $f->addClass('stacked');

        $repo=$this->api->getConfig('license/atk4/repo',false);
        // Detect repository location
        if(!$repo && is_readable(getcwd().'/.git/config')){
        	$cf=file_get_contents(getcwd().'/.git/config');
        	$cf=explode("\n",$cf);
        	foreach($cf as $line){
        		$line=trim($line);
        		list($key,$value)=explode('=',$line);
        		$key=trim($key);$value=trim($value);
        		if($key=='url'){
        			$repo=$value;
        			break;
        		}
        	}
        }

        $f->addField('line','host','Domain')->set($_SERVER['HTTP_HOST']);
        $f->addField('line','repo','Code Repository')->set($repo);
        $f->addSubmit('Register');
        if($f->isSubmitted()){
            $this->js()->univ()->location($this->api->url('https://agiletoolkit.org/u/reg',array(
                't'=>'agpl',
                'd'=>$f->get('host'),
        		'r'=>$f->get('repo')
            )))->execute();
        }


        $p=$cc->addColumn(6);
        $p->add('H3')->set('Commercial License');

        $p->add('P')->set('Commercial license allows you to continue using Agile Toolkit in commercial environment. You will
        	be able to use your web software publicly without disclosing your source code, however you must pay some
        	insignificant price to developers of Agile Toolkit so that we can continue to make Agile Toolkit even
        	better and more powerful for you.');

        $p->add('P')->set('If you are holder of developer license, you can "issue" additional certificate for this
        	installation, you do not need to pay anything extra.');


        $f=$p->add('Form');
        $f->addClass('stacked');
        $f->addField('line','host','Domain')->set($_SERVER['HTTP_HOST']);
        $f->addField('dropdown','type','License Type')->setValueList(array(
        	'single'=>'Purchase license for this domain',
        	'devel'=>'Purchase multi-domain enterprise license',
        	'add'=>'Register under existing license'
        	));
        $f->addSubmit('Register');

        if($f->isSubmitted()){
        	$this->js()->univ()->location('https://agiletoolkit.org/u/reg?t='.urlencode($f->get('type'))
        		.'&d='.urlencode($f->get('host')
        		))->execute();
        }
    }
}
