<?php
/**
 * This file is part of foglcz/nestraps
 *
 * Copyright Pavel Ptacek (c) 2013
 * Copyright Filip Prochazka (c) 2012
 *
 * @license LGPL
 */

namespace foglcz; // to make sense out of everything ;)
use Nette\Application\UI\Form;
use Nette\FileNotFoundException;
use Nette\InvalidArgumentException;
use Nette\Latte\Engine;
use Nette\Templating\FileTemplate;
use Nette\Templating\ITemplate;
use Nette\Templating\Template;
use Nette\Utils\Strings;

/**
 * Template renderer for forms
 *
 * This class provides nessesary helpers for the template.latte to be capable of properly generating output html
 * for any given form.
 *
 * @author Pavel Ptáček (ptacek.pavel@gmail.com)
 * @author Filip Procházka (filip@prochazka.su)
 * @see bootstrap.latte
 * @see foundation.latte
 */
class Nestraps implements \Nette\Forms\IFormRenderer {

    /** @var string Default bootstrap (v 3.0) */
    const BOOTSTRAP = 'bootstrap-v2.latte';

    /** @var string Default foundation (v 5.0) */
    const FOUNDATION = 'foundation-v5.latte';

    /** @var string Older bootstrap (v 2.3.2) */
    const BOOTSTRAP_2 = 'bootstrap-v2.latte';

    /** @var string Older bootstrap (v 2.3.2) */
    const BOOTSTRAP_3 = 'bootstrap-v3.latte';

    /** @var string Even older foundation (v 3.2.5) */
    const FOUNDATION_3 = 'foundation-v3.latte';

    /** @var string Older foundation (v 4.3.2) */
    const FOUNDATION_4 = 'foundation-v4.latte';

    /** @var string Older foundation (v 4.3.2) */
    const FOUNDATION_5 = 'foundation-v5.latte';

    /**  @var \Nette\Templating\Template */
    private $template;

    /** @var \Nette\Forms\Form */
    private $form;

    /** @var bool errors at inputs or on top of the form? */
    private $errorsAtInputs = true;

    /** @var bool Element stack generator helper */
    private $stackOpen = false;

    /**
     * @param string|Template $template Set either to constant, your instance of ITemplate and/or your path to your template file.
     * @param \Nette\Caching\IStorage $storage storage if available
     * @throws InvalidArgumentException Thrown when parameter combination does not match
     * @throws FileNotFoundException Thrown when path to the file is nonexistent or the file is not readable
     */
    public function __construct($template, \Nette\Caching\IStorage $storage = null) {
        $this->template = null;

        // If we're passed an ITemplate instance, use that
        if($template instanceof ITemplate) {
            $this->template = $template;
        }
        elseif(is_string($template) && !file_exists($template) && file_exists(__DIR__ . '/' . $template)) {
            $template = __DIR__ . '/' . $template;
        }

        // CHeck & init
        if($this->template === null && !file_exists($template)) {
            throw new InvalidArgumentException('$template has to be either path to template or instance of \Nette\Templating\ITemplate');
        }
        if($this->template === null) {
            $this->template = new FileTemplate($template);
            $this->template->registerFilter(new Engine());

            if($storage) {
                $this->template->setCacheStorage($storage);
            }
        }
    }

    /**
     * Render the templates
     *
     * @param \Nette\Forms\Form $form
     * @return void
     */
    public function render(\Nette\Forms\Form $form) {
        if($form === $this->form) {
            // don't forget for second run, @hosiplan! ;)
            foreach($this->form->getControls() as $control) {
                $control->setOption('rendered', false);
            }

            echo $this->template;
            return;
        }

        // Store the current form instance
        $this->form = $form;

        // Translator available?
        if($translator = $form->getTranslator()) { // intentional =
            $this->template->setTranslator($translator);
        }

        // Pre-proccess form
        $errors = $form->getErrors();
        foreach($form->getControls() as $control) {
            $control->setOption('rendered', false);

            if(!$control->getOption('blockname', false)) {
                $ex = explode('\\', get_class($control));
                $control->setOption('blockname', end($ex));
            }

            if($this->errorsAtInputs) {
                $errors = array_diff($errors, $control->errors);
            }
        }

        // Assign to template
        $this->template->form = $form;
        $this->template->errors = $errors;
        $this->template->renderer = $this;
        $this->template->errorsAtInputs = $this->errorsAtInputs;

        // And echo the output
        echo $this->template;
    }

    /**
     * Find specific controls within the current form
     *
     * @param string $fieldtype
     * @param bool $skipRendered false if you want to get even the rendered ones
     * @return array
     *
     * @internal
     */
    public function findFields($fieldtype, $skipRendered = true) {
        $stack = array();
        $fieldtype = $fieldtype;
        foreach($this->form->getControls() as $control) {
            if($skipRendered && $control->getOption('rendered', false)) {
                continue;
            }

            if($control->getOption('blockname') === $fieldtype) {
                $stack[] = $control;
            }
        }

        return $stack;
    }

    /**
     * @param \Nette\Forms\Controls\BaseControl $control
     * @return string
     *
     * @internal
     * @author Filip Procházka
     */
    public static function getControlName(\Nette\Forms\Controls\BaseControl $control) {
        return $control->lookupPath('Nette\Forms\Form');
    }

    /**
     * Opens button stack and returns true if it was closed previously
     *
     * @return bool
     *
     * @author Pavel Ptáček
     * @internal
     */
    public function openStack() {
        if($this->stackOpen) {
            return false;
        }

        return $this->stackOpen = true;
    }

    /**
     * Closes open stack if next values is not instance of the same control as the current one
     *
     * @param $iterator
     * @param \Nette\Forms\Controls\BaseControl $control
     * @return bool
     *
     * @author Pavel Ptáček
     * @internal
     */
    public function closeStack($iterator, \Nette\Forms\Controls\BaseControl $control) {
        if(!$this->stackOpen) {
            return false;
        }

        // Buttons inherit, yet are grouped.
        if($control instanceof \Nette\Forms\Controls\Button && $iterator->nextValue instanceof \Nette\Forms\Controls\Button) {
            return false;
        }
        if($iterator->nextValue instanceof $control) {
            return false;
        }

        return !($this->stackOpen = false);
    }

    /**
     * Set class on form's prototype, if not one of $excludes are set
     *
     * @param Form $form
     * @param string $class
     * @param array $exclude
     * @internal
     */
    public function setFormClass(Form $form, $class, array $exclude = array()) {
        if(!isset($form->getElementPrototype()->attrs['class'])) {
            $form->getElementPrototype()->attrs['class'] = $class;
            return;
        }

        $defined = Strings::lower($form->getElementPrototype()->attrs['class']);
        foreach($exclude as $one) {
            $one = Strings::lower($one);
            if(strpos($defined, $one) !== false) {
                return;
            }
        }

        $form->getElementPrototype()->attrs['class'] = $form->getElementPrototype()->attrs['class'] . ' ' . $class;
    }

    /**
     * Sets whether the errors should be at inputs or fields.
     * Wrapped as function to be able to use this within config.neon without hassle
     *
     * @param bool $value
     */
    public function setErrorsOnFields($value) {
        $this->errorsAtInputs = $value;
    }
}