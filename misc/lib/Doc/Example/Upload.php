<?php
class Doc_Example_Upload extends Doc_Example {
	function init(){
		parent::init();

		$this->add('H2')->set('File Uploading');
		$this->add('P')->set('File management in PHP is often messy, involves temporary directories or is even stuck in
				MySQL. Agile Toolkit implements a powerful yet very simple "Filestore". This is a class which helps to keep
				your files organized. From the moment your file upload have finished and it have been saved to a upload
				directory - Filestore creates a new record for this file using MVC concept. 
				');

		$this->add('P')->set('What is really important is the flexibility and simplicity of this approach. You can now add
				upload field to your form and when form data is saved - you will receive filestore_file_id in upload field.
				The record in the database will automatically contain the file meta-information such as size and original
				name.
				');

		$this->add('P')->set('In this example we are going beyond that. We are slightly modifying File model/controller by
				defining new field "sample_file" and saying that it must always be true in our case. By using that, you can
				only see or upload files which conform to this condition, therefore you will not be able to see any other
				files in the filestore.
				');


		$this->setCode(<<<'EOD'

$c=$p->add('Controller_Filestore_File');
$c->addField('sample_file')->datatype('boolean');
$c->setMasterField('sample_file',true);

$f=$p->add('Form');
$f->addField('upload','upload_file')->setController($c);

$p->add('H4')->set('Previously Uploaded Files');

$c->setActualFields(array('original_filename','filesize'));
$g=$p->add('MVCGrid')
 ->setController($c);
$g->addColumnPlain('delete','delete');

$f->addButton('Refresh')->js('click',$p->js()->reload());

EOD
);


	}
}
