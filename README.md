nestraps
========

The Bootstrap style nette forms

How to use:

```php
$form->setRenderer(new foglcz\Nestrap);
```

How to use ideally: (from presenter)

```php
$form->setRenderer(new foglcz\Nestrap(null, $this->context->cacheStorage));
```

What is it
==========
Nestraps is a replacement for conventional renderer in Nette Framework. Basically,
all it does is that it takes given .latte file & sends the form there.

The .latte file can be written in any way - how you want it.

Twitter Bootstrap style
=======================
Due to bootstraps nature, we have following options available:

```php
$field->setOption('help', 'help text shown next to control');
$field->setOption('status', 'warning|errors|success'); --> the css style of the field
$field->setOption('prepend', 'text'); --> the prepend field part
$field->setOption('append', 'text');  --> the append field part
$field->setOption('placeholder', 'text'); --> the placeholder="" parameter
```

The neat inline checkboxes are generated this way:

```php
$form->addCheckbox('chk1', 'caption 1')->setOption('inline', true)->setOption('label', 'checkbox group label');
$form->addCheckbox('chk2', 'caption 2')->setOption('inline', true);
$form->addCheckbox('chk3', 'caption 3')->setOption('inline', true);
```

Buttons have some special classes as well:
`$form->addSubmit()->setOption('button-class', 'btn-success')`

Prepend/append can be used with conjunction of:

```php
$field->setOption('prepend-button', 'id');
$field->setOption('append-button', 'id');
```

...where the "id" means HTML id of button, so that you can use it within javascripts

Prepend/append buttons can be used also with:
```php
$field->setOption('prepend-button-class', 'btn-success|btn-warning|btn-danger|btn-primary|...');
$field->setOption('append-button-class', 'btn-success|btn-warning|btn-danger|btn-primary|...');
```

where the value is appended as class to the button

If you are working with submits or buttons & want to edit class, use the "Standart Nette Way ^TM":

`$field->controlPrototype->class = 'btn btn-success'; // (this generates green button)`

OVERLOADING:
------------
Don't really worry about manually editing this file -- altough it's possible, much cleaner approach is to create
your own template with following markup:

```
  {layout 'path/to/bootstrap.latte'}
  {define #TextInput}<insert text input markup here>{/define}
```

If you want to overload some field but not overload the input globally, use following:

`$field->setOption('latte', 'path/to/your/overloaded.latte');`

... or:

`$field->setOption('blockname', 'nameOfYourDefineBlockWithoutHash');`

NOTE:
-----
In the template, the <hidden> fields are rendered as last. The easiest way to extend any field is:

`$form->addHidden(...)->setOption('latte', 'path/to/your/field.latte')`

License
-------
LGPL.

--------------------------------------------------------------------------------

discussion: http://forum.nette.org/cs/10274-convetionalrenderer-latte-pomocnik-pro-rucni-renderovani-formulare-tw-bootstrap