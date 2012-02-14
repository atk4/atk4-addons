This addon allows you to sort records.

1. Create field "ord" in your database table
2. $grid->add('gridorder/Controller_GridOrder');

if you need sorting in grud, then

if($crud->grid)$crud->grid->add('gridorder/Controller_GridOrder');
