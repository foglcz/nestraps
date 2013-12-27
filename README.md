Bootstrap / Zurb Foundation forms for Nette Framework
=====================================================

Installation
------------
```
$ composer require foglcz/nestraps
```

How to use in nette
-------------------

Update your `config.neon`:
```
    factories:
        form:
            parameters: [type]
            class: \Nette\Application\UI\Form
            setup:
                - setRenderer(\foglcz\Nestraps(%type%, @cacheStorage))
```

And then in your presenter:
```php
public function createComponentYourFormName($name) {
    $form = $this->getContext()->createForm(\foglcz\Nestraps::BOOTSTRAP);
    $form = $this->getContext()->createForm(\foglcz\Nestraps::FOUNDATION);
    // ...
}
```

What is it
==========
Nestraps is a replacement for conventional renderer in Nette Framework. Basically,
all it does is that it takes given .latte file & sends the form there.

The .latte file can be written in any way - how you want it.

Twitter Bootstrap style
=======================
Detailed documentation is pending; for now proceed to HomepagePresenter in showcase: https://github.com/foglcz/nestraps/blob/master/showcase/nette-2.0/app/presenters/HomepagePresenter.php

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
In the template, the hidden fields are rendered as last. The easiest way to extend any field is:

`$form->addHidden(...)->setOption('latte', 'path/to/your/field.latte')`

License
-------
LGPL.