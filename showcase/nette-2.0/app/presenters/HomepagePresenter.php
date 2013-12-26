<?php

/**
 * Homepage presenter
 *
 * @author Pavel Ptacek
 */
class HomepagePresenter extends BasePresenter {

    /**
     * Components for all the forms, to keep the API same
     */
    protected function appendControls(\Nette\Application\UI\Form $form) {
        // General form errors
        $form->addError('This is a generic form error.');

        // Showcase
        $form->addText('simple', 'Simple input');
        $form->addText('placeholder', 'Input with placeholder')->setOption('placeholder', 'This is a placeholder value');
        $form->addText('help', 'Simple input with help')->setOption('help', 'This is some help text');
        $form->addText('help_block', 'Help with block style')->setOption('help', 'This is some help text')->setOption('help-style', 'block');
        $form->addText('status_warning', 'Simple input with warning')->setOption('status', 'warning');
        $form->addText('status_error', 'Simple input with error')->setOption('status', 'error');
        $form->addText('status_success', 'Simple input with success')->setOption('status', 'success');
        $form->addText('prepend_append', 'Prepend and append')->setOption('prepend', '$')->setOption('append', '.00');
        $form->addTextArea('textarea', 'Some textarea');

        // Prepend & append with buttons
        $form->addText('prepend_append_buttons', 'Prepend and append BUTTONS')
            ->setOption('prepend', 'butt prepend')            ->setOption('append', 'butt append')
            ->setOption('prepend-button', 'html-id-prepend')  ->setOption('append-button', 'html-id-append')
            ->setOption('prepend-button-class', 'btn-primary')->setOption('append-button-class', 'btn-success');


        // Checkboxes - standard
        $form->addCheckbox('chks1', 'Standard checkbox 1');
        $form->addCheckbox('chks2', 'Standard checkbox 2');

        // Checkboxes - inline
        $form->addCheckbox('chki1', 'first')->setOption('inline', true)->setOption('label', 'Inline checkboxes');
        $form->addCheckbox('chki2', 'second')->setOption('inline', true);
        $form->addCheckbox('chki3', 'third')->setOption('inline', true);

        // Radio buttons
        $form->addRadioList('radiolist', 'Radio lists', array(
            'first' => 'This is first radio option',
            'second' => 'This is second radio option',
        ));

        // Selects
        $form->addSelect('select', 'Simple select', array(
            'one' => 'First option',
            'two' => 'Second option',
            'three' => 'Third option',
            'four' => 'Fourth option',
        ), 1);
        $form->addSelect('multiselect', 'Multiselect', array(
            'one' => 'First option',
            'two' => 'Second option',
            'three' => 'Third option',
            'four' => 'Fourth option',
            'fifth' => 'Fifth option',
        ), 2)->setOption('help', 'Sorry about the fact that Bootstrap does not have size element available; you need to style this one yourself...')->setOption('help-style', 'block');

        // Styles on individual controls
        $form->addText('input_with_class', 'Input with class')
            ->getControlPrototype()->readonly = 'readonly';

        // Disabled input
        $form->addText('disabled', 'Disabled input')->setDisabled(true);

        // Required input
        $form->addText('required', 'Required input')->setRequired('This input is required');

        // Input with error on it via Nette
        $form->addText('error', 'Nette error showcase')->addError('Field cannot be empty');
    }

    /**
     * Helper for appending submits
     *
     * @param \Nette\Application\UI\Form $form
     */
    protected function appendSubmits(\Nette\Application\UI\Form $form) {
        $form->addSubmit('btn', 'Default');
        $form->addSubmit('btnprimary', 'Primary')->setOption('class', 'btn-primary');
        $form->addSubmit('btninfo', 'Info')->setOption('class', 'btn-info');
        $form->addSubmit('btnsucc', 'Success')->setOption('class', 'btn-success');
        $form->addSubmit('btnwarn', 'Warning')->setOption('class', 'btn-warning');
        $form->addSubmit('btndang', 'Danger')->setOption('class', 'btn-danger');
        $form->addSubmit('btninve', 'Inverse')->setOption('class', 'btn-inverse');
        $form->addSubmit('btnlink', 'Link')->setOption('class', 'btn-link');
        $form->addSubmit('btnprimarylarge', 'Primary & large & block')->setOption('class', 'btn-primary btn-large btn-block');
    }


    /**
     * Bootstrap 2 showcase
     *
     * Using http://getbootstrap.com/2.3.2/base-css.html#forms
     */
    public function createComponentBootstrap2($name) {
        $form = $this->getContext()->createForm(\foglcz\Nestraps::BOOTSTRAP_2);

        // Type of form? The form-horizontal is default.
        //$form->elementPrototype->attrs['class'] = 'form-inline';
        //$form->elementPrototype->attrs['class'] = 'form-search';

        // Append elements
        $form->addGroup('Default styles');
        $this->appendControls($form);

        // Style the input with class
        $form['input_with_class']->setOption('placeholder', '.input-xlarge & .uneditable-input')
            ->setOption('class', 'input-xlarge uneditable-input');

        // Bootstrap 2 specifics
        $form->addGroup('Bootstrap 2 specific features.');

        // Multiappend / prepend buttons
        $form->addText('append_multibutton', 'Multibutton append')
            ->setOption('append-html', '<button class="btn" type="button">Search</button><button class="btn" type="button">Options</button>')
            ->setOption('help', 'Prepend is the same - you just use "prepend-html" option');

        // Multiappend / prepend dropdowns
        $form->addText('prepend_dropdown', 'Multidropdown append&prepend')
            ->setOption('prepend-html', ' <div class="btn-group">
                <button class="btn dropdown-toggle" data-toggle="dropdown">
                  Action
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li class="divider"></li>
                  <li><a href="#">Separated link</a></li>
                </ul>
              </div>')
            ->setOption('append-html', ' <div class="btn-group">
                <button class="btn dropdown-toggle" data-toggle="dropdown">
                  Action
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li class="divider"></li>
                  <li><a href="#">Separated link</a></li>
                </ul>
              </div>');

        // Prepend / append : segmented
        $form->addText('prepapp_segmented', 'Append&prepend: segmented')
            ->setOption('prepend-html', ' <div class="btn-group">
                <button class="btn" tabindex="-1">Action</button>
                <button class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li class="divider"></li>
                  <li><a href="#">Separated link</a></li>
                </ul>
              </div>')
            ->setOption('prepend-segmented', true)
            ->setOption('help', 'The segmented prepend/append works only when there\'s only prepend OR append, not both. See botstrap docs for details.')
            ->setOption('help-style', 'block');

        // Append submits
        $this->appendSubmits($form);

        return $form;
    }

    /**
     * Bootstrap 3 showcase
     *
     * using http://getbootstrap.com/css/#forms
     */
    public function createComponentBootstrap3($name) {
        $form = $this->getContext()->createForm(\foglcz\Nestraps::BOOTSTRAP_3);

        // Type of form? form-horizontal style is default.
        //$form->elementPrototype->attrs['class'] = 'form-inline';
        //$form->elementPrototype->attrs['class'] = 'form-search';
        //$form->elementPrototype->attrs['class'] = 'form-default';

        // Append elements
        $form->addGroup('Default styles');
        $this->appendControls($form);

        // Style the input with class
        $form['input_with_class']->setOption('placeholder', '.input-lg & readonly')
            ->setOption('class', 'input-lg form-control');

        // Bootstrap v3 specifics
        $form->addGroup('Specifics for bootstrap v3');

        // Prepend radio / checkbox
        $form->addText('prepend_checkbox', 'Prepend checkbox')
            ->setOption('prepend-html', '<span class="input-group-addon"><input type="checkbox"></span>');
        $form->addText('prepend_radio', 'Prepend radio')
            ->setOption('prepend-html', '<span class="input-group-addon"><input type="radio"></span>');

        // Multiappend / prepend buttons
        $form->addText('append_multibutton', 'Multibutton append')
            ->setOption('append-html', '<div class="input-group-btn"><button class="btn" type="button">Search</button><button class="btn" type="button">Options</button></div>')
            ->setOption('help', 'Prepend is the same - you just use "prepend-html" option');

        // Multiappend / prepend dropdowns
        $form->addText('prepend_dropdown', 'Multidropdown append&prepend')
            ->setOption('prepend-html', '<div class="input-group-btn">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
                <ul class="dropdown-menu">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li class="divider"></li>
                  <li><a href="#">Separated link</a></li>
                </ul>
              </div><!-- /btn-group -->')
            ->setOption('append-html', ' <div class="input-group-btn">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
                <ul class="dropdown-menu pull-right">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li class="divider"></li>
                  <li><a href="#">Separated link</a></li>
                </ul>
              </div><!-- /btn-group -->
            </div><!-- /input-group -->');

        // Prepend / append : segmented
        $form->addText('prepapp_segmented', 'Append&prepend: segmented')
            ->setOption('prepend-html', '<div class="input-group-btn">
                                          <button type="button" class="btn btn-default" tabindex="-1">Action</button>
                                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                          </button>
                                          <ul class="dropdown-menu" role="menu">
                                            <li><a href="#">Action</a></li>
                                            <li><a href="#">Another action</a></li>
                                            <li><a href="#">Something else here</a></li>
                                            <li class="divider"></li>
                                            <li><a href="#">Separated link</a></li>
                                          </ul>
                                        </div>')
            ->setOption('help', 'The segmented prepend/append works just based on markup - there\'s no need to use setOption(prepend-segmented) with bootstrap v3.')
            ->setOption('help-style', 'block');

        // Append submits
        $form->addGroup('Submit line');
        $this->appendSubmits($form);

        return $form;
    }
}
