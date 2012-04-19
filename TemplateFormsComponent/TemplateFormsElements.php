<?php
/**
 * This file has been created within Animal Group
 *
 * @copyright Animal Group
 */

namespace AG\Forms\Template;

/**
 * The individual control for templates
 *
 * @author pavel ptacek
 */
class Control {
    /** @var \Nette\Forms\Controls\BaseControl The base form control */
    public $field;

    /** @var string latte file */
    public $latte;

    /**
     * Create new instance
     *
     * @param \Nette\Forms\Controls\BaseControl $control
     */
    public function __construct(\Nette\Forms\Controls\BaseControl $control) {
        $this->field = $control;
        if($control->getOption('latte', false)) {
            $this->latte = $control->getOption('latte');
        }
        else {
            $classname = get_class($control);
            $ex = explode('\\', $classname);
            $this->latte = 'controls/' . array_pop($ex) . '.latte';
        }
    }

    /**
     * Get variables for text input
     * @return array
     */
    public function getTextInputDefinition() {
        // @todo ;)
    }
}

/**
 * One group in templates
 *
 * @author Pavel Ptacek
 */
class Group {
    /** @var string group label */
    public $label;

    /** @var string description of the group */
    public $description = false;

    /** @var array the controls within group */
    public $controls = array();
}