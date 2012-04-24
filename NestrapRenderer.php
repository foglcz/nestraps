<?php
/**
 * This file has been created by @hosiplan and @foglcz
 * ("we simply copycat from each other" -foglcz)
 *
 * @license LGPL
 */

namespace foglcz; // to make sense out of everything ;)

/**
 * Template renderer for forms
 *
 * This renderer heavily depends on the .latte file. It comes bundled with "default nette template" which is exactly
 * the same as the old markup, and with new Twitter Bootstrap template.
 *
 * Usage:
 * $form = new Nette\Forms\Form;
 * $form->setRenderer(new foglcz\NestrapRenderer('path_to.latte'))
 *
 * You can also send your very own template:
 * $form = new Nette\Forms\Form;
 * $form->setRenderer(new foglcz\NestrapRenderer(new Nette\Templating\FileTemplate()));
 *
 * The final options which are available depends on the latte file which you use. If you use the bootstrap.latte file,
 * which is a Twitter Bootstrap template, consult there for new options.
 *
 * @author Pavel Ptáček
 * @author Filip Procházka
 * @see bootstrap.latte
 * @see nette.latte
 */
class NestrapRenderer implements \Nette\Forms\IFormRenderer {
    /**  @var \Nette\Templating\Template */
    private $template;

    /** @var \Nette\Forms\Form */
    private $form;

    /** @var bool set to false, if you want to display the field errors also as form errors */
    public $errorsAtInputs = true;

    /**
     * Element stack generator helper
     * @var bool
     * @internal
     */
    private $stackOpen = false;

    /**
     * @param \Nette\Templating\Template|string|null $template full path to file and/or template instance and/or null
     * @param \Nette\Caching\IStorage|null $storage optional cache storage for template
     */
    public function __construct($template = null, \Nette\Caching\IStorage $storage = null) {
        if($template === null) {
            $template = __DIR__ . '/bootstrap.latte';
        }

        if($template instanceof \Nette\Templating\Template) {
            $this->template = $template;
        }
        elseif(is_string($template) && is_readable($template)) {
            $this->template = new \Nette\Templating\FileTemplate($template);
            $this->template->registerFilter(new \Nette\Latte\Engine());
        }
        elseif(is_string($template) && !is_readable($template)) {
            throw new \Nette\FileNotFoundException('File "' . $template . '" does not exists or is not readable by php.');
        }
        else {
            throw new \Nette\InvalidArgumentException('$template can be instance of Nette\Templating\Template, string path to file or null, ' . gettype($template) . ' given.');
        }

        if($storage) {
            $this->template->setCacheStorage($storage);
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
}