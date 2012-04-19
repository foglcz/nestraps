<?php
/**
 * This file has been created within Animal Group
 *
 * @copyright Animal Group
 */

namespace AG\Forms;

/**
 * The base template renderer for forms.
 *
 * Usage:
 * $form->addRenderer(new TemplateFormsRenderer);
 *
 * If you want to override entire
 *
 * You can customize the directory, where all the templates resides.
 * Created with twitter bootstrap in mind. If you want to append options, use:
 *
 * ok $field->setOption('help', 'help text');
 * ok $field->setOption('status', 'warning|error|success'); // the style of the field
 * ok $field->setOption('class', 'someclasses');
 * ok $field->setOption('prepend', 'prepend text within input');
 * ok $field->setOption('append', 'append text within input');
 * ok $field->setOption('prepend-button', 'buttonid');
 * ok $field->setOption('append-button', 'buttonid');
 * ok $field->setOption('placeholder', 'this is some placeholder text');
 *
 * @author Pavel Ptacek
 * @version pre-alpha
 */
class TemplateRenderer extends \Nette\Object implements \Nette\Forms\IFormRenderer {

    /** @var string master file */
    private $master;

    /** @var bool true, if you want to display the field errors also as form errors */
    private $showFieldErorrsGlobally = false;

    /** @var \AG\Forms\Template\Control[] the current control stack */
    private $controlStack = array();

    /** @var array the current button stack */
    private $buttonStack = array();

    /**
     * @param string $master full path to the master file
     */
    public function __construct($master = null) {
        if($master === null) {
            $master = __DIR__ . '/master.latte';
        }
        if(!is_readable($master)) {
            throw new \Nette\InvalidArgumentException('Master file "' . $master . '" is not readable.');
        }

        // Save
        $this->master = $master;
    }

    /**
     * true, if you want to display the field errors also as form errors
     * @param bool $show
     * @return self
     */
    public function setShowFieldErrorsGlobally($show = true) {
        $this->showFieldErorrsGlobally = $show;
        return $this;
    }

    /**
     * Render the templates
     *
     * @param \Nette\Forms\Form $form
     * @return void
     */
    public function render(\Nette\Forms\Form $form) {
        $translator = $form->getTranslator();
        foreach($form->getControls() as $control) {
            $control->setOption('rendered', false);
        }

        // Create master template
        $master = $this->createTemplate($this->master, $form);

        /**
         * Preproccess the form fields into given structure, which template requires (for simple working with it there)
         */
        $master->hiddens = array();
        $master->method = $form->getMethod();
        $master->action = $form->getAction();
        if (strcasecmp($form->getMethod(), 'get') === 0) {
            $el = clone $form->getElementPrototype();
            $url = explode('?', (string) $el->action, 2);
            $master->action = $url[0];
            if (isset($url[1])) {
                foreach (preg_split('#[;&]#', $url[1]) as $param) {
                    $parts = explode('=', $param, 2);
                    $name = urldecode($parts[0]);
                    if (!isset($form[$name])) {
                        $master->hiddens[$name] = urldecode($parts[1]);
                    }
                }
            }
        }

        /**
         * Step 2: prepare form-related errors
         */
        $master->errors = array();
        if(count($form->getErrors()) > 0) {
            $master->errors = $form->getErrors();

            // Remove the field errors from form errors?
            if(!$this->showFieldErorrsGlobally) {
                $fieldErrors = array();
                foreach($form->getControls() as $control) {
                    if(!$control->hasErrors()) {
                        continue;
                    }

                    $master->errors = array_diff($master->errors, $control->getErrors());
                }
            }

            // If we have translator, translate!
            if($translator) {
                foreach($master->errors as $key => $val) {
                    $master->errors[$key] = $translator->translate($val);
                }
            }
        }

        /**
         * Step 3: prepare groups
         */
        $master->groups = array();
        foreach($form->getGroups() as $group) {
            /** @var $group \Nette\Forms\ControlGroup */
            if(!$group->getControls() || !$group->getOption('visual')) {
                continue;
            }

            // Define group
            $one = new Template\Group;
            $one->label = $group->getOption('label');
            $one->description = $group->getOption('description');
            $one->controls = array();
            if(!empty($translator)) {
                $one->label = $translator->translate($one->label);
                $one->description = $translator->translate($one->description);
            }

            // Render controls
            foreach($group->getControls() as $control) {
                $this->addControl($control, $form);
            }
            $this->appendButtonStack();
            $one->controls = $this->getControlStack();

            // Append to template
            $master->groups[] = $one;
        }

        /**
         * Step 4: render rest of the controls
         */
        $master->controls = array();
        foreach($form->getControls() as $control) {
            $this->addControl($control, $form);
        }
        $this->appendButtonStack();
        $master->controls = $this->getControlStack();

        /**
         * And render master template!
         */
        echo $master;
    }

    /**
     * Prepares one control and adds it to the control stack
     * @param \Nette\Forms\Controls\BaseControl $control
     * @param \Nette\Forms\Form $form
     * @return void
     */
    protected function addControl(\Nette\Forms\Controls\BaseControl $control, \Nette\Forms\Form $form) {
        // skip?
        if ($control->getOption('rendered') || $control->getForm(FALSE) !== $form) {
            return;
        }

        // Create instance
        $one = new Template\Control($control);

        // Button?
        if($control instanceof \Nette\Forms\Controls\Button) {
            $this->buttonStack[] = $one;
            return;
        }
        $this->appendButtonStack();
        $this->controlStack[] = $one;
        $control->setOption('rendered', true);
    }

    /**
     * Append button stack into the $this->controlStack and clear the button stack
     * @return void
     */
    protected function appendButtonStack() {
        if(empty($this->buttonStack)) {
            return;
        }

        $override = null;
        foreach($this->buttonStack as $button) {
            $button->field->setOption('rendered', true);
            $override = $button->field->getOption('latteForStack', false) ? $button->field->getOption('latteForStack') : null;
        }
        $this->controlStack[] = new Template\ButtonStack($this->buttonStack, $override);
        $this->buttonStack = array();
    }

    /**
     * Get current control stack & clear it
     * @return array
     */
    protected function getControlStack() {
        $stack = $this->controlStack;
        $this->controlStack = array();
        return $stack;
    }

    /**
     * Create template for given file
     * @param string $template
     * @param \Nette\Forms\Form $form
     * @return \Nette\Templating\FileTemplate
     */
    protected function createTemplate($template, \Nette\Forms\Form $form) {
        $template = new \Nette\Templating\FileTemplate($template);
        $template->registerFilter(new \Nette\Latte\Engine());
        if($form->getTranslator()) {
            $template->setTranslator($form->getTranslator());
        }
        $template->form = $form;
        return $template;
    }
}