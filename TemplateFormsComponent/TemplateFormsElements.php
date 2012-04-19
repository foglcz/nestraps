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

    /** @var array the html parameters for latte macros */
    private $params;

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
        if(!empty($this->params)) {
            return $this->params;
        }

        $params = array();
        $params['type'] = $this->field->control->type ? $this->field->control->type : 'text';
        $params['name'] = $this->field->getHtmlName();
        $params['id'] = $this->field->getHtmlId();
        $params['value'] = $this->field->getValue();
        $params['class'] = $this->field->getOption('class', false) ? $this->field->getOption('class') : 'input-xlarge';

        // Optional parameters
        if($this->field->isRequired()) { $params['required'] = 'required'; }
        if($this->field->disabled) { $params['disabled'] = 'disabled'; }
        if($this->field->getOption('placeholder', false)) { $params['placeholder'] = $this->field->getOption('placeholder'); }
        if(!empty($this->field->control->size)) { $params['size'] = $this->field->control->size; }
        if(!empty($this->field->control->maxlength)) { $params['maxlength'] = $this->field->control->maxlength; }

        // Rules?
        $rules = $this->exportRules();
        if($rules) { $params['data-nette-rules'] = $rules; }

        // done
        return $this->params = $params;
    }

    /**
     * Copycat from Nette\Forms\Controls\BaseControl due to PROTECTED STATUS of the method.
     * @param array $rules
     * @return string
     */
    protected function exportRules()
    {
        $rules = $this->field->getRules();
        $payload = array();
        foreach ($rules as $rule) {
            if (!is_string($op = $rule->operation)) {
                $op = callback($op);
                if (!$op->isStatic()) {
                    continue;
                }
            }
            if ($rule->type === \Nette\Forms\Rule::VALIDATOR) {
                $item = array('op' => ($rule->isNegative ? '~' : '') . $op, 'msg' => $rules->formatMessage($rule, FALSE));

            } elseif ($rule->type === \Nette\Forms\Rule::CONDITION) {
                $item = array(
                    'op' => ($rule->isNegative ? '~' : '') . $op,
                    'rules' => self::exportRules($rule->subRules),
                    'control' => $rule->control->getHtmlName()
                );
                if ($rule->subRules->getToggles()) {
                    $item['toggle'] = $rule->subRules->getToggles();
                }
            }

            if (is_array($rule->arg)) {
                foreach ($rule->arg as $key => $value) {
                    $item['arg'][$key] = $value instanceof IControl ? (object) array('control' => $value->getHtmlName()) : $value;
                }
            } elseif ($rule->arg !== NULL) {
                $item['arg'] = $rule->arg instanceof IControl ? (object) array('control' => $rule->arg->getHtmlName()) : $rule->arg;
            }

            $payload[] = $item;
        }

        // Post-proccess
        $return = substr(json_encode($payload), 1, -1);
        $return = preg_replace('#"([a-z0-9]+)":#i', '$1:', $return);
        $return = preg_replace('#(?<!\\\\)"([^\\\\\',]*)"#i', "'$1'", $return);
        return $return ? $return : false;
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

/**
 * Button stack
 *
 * @author Pavel Ptacek
 */
class ButtonStack {
    /** @var Control[] */
    public $buttons = array();

    /** @var string latte file */
    public $latte;

    /**
     * Create new buttonstack for templates
     * @param array $controls
     * @param string $override
     */
    public function __construct(array $controls, $override = null) {
        $this->buttons = $controls;
        $this->latte = $override ? $override : 'controls/ButtonStack.latte';
    }
}