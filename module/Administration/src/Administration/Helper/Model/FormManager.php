<?php
namespace Administration\Helper\Model;

use Zend\Db\TableGateway\Exception;
use Zend\Form\Form;

class FormManager
{
    private   $tabs = array();
    private   $entity;
    private   $controlPanel;
    protected $form;

    public function __construct(GenericModelTableGateway $entity, $controlPanel)
    {
        $this->entity       = $entity;
        $this->controlPanel = $controlPanel;
        foreach ($this->entity->getModel()->getForm() as $tabName => $fields) {
            $this->addTab($tabName, $fields);
        }
    }

    protected function addTab($name, array $containingFields = array())
    {
        if (empty($name)) {
            throw new Exception\InvalidArgumentException('Tab must have a name!');
        } else {
            $this->tabs[] = array('name' => $name , 'fields' => $containingFields);
        }
    }

    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * Instantiates the Zend Form Object.
     */
    public function getFormObject()
    {
        if (!$this->form) {
            $this->form = new Form($this->entity->getModel()->getTableName());
            $this->form->add(array(
                'type' => 'hidden',
                'name' => 'id',
            ));

            if ($this->entity->getModel()->getMaximumTreeDepth() > 0) {
                $value_options = array();
                $value_options[0] = '---Root Item---';

                foreach ($this->entity->getListingForSelect() as $listingItem) {
                    $optionString = '';
                    foreach ($this->entity->getModel()->getListingFields() as $listingField) {
                        $optionString .= $listingItem->{$listingField} . ' ';
                    }
                    $value_options[$listingItem->id] = $optionString;
                }

                $this->form->add(array(
                    'type' => 'Zend\Form\Element\Select',
                    'name' => 'parent_' . $this->entity->getModel()->getPrefix() . 'id',
                    'options' => array(
                        'label' => 'parent_' . $this->entity->getModel()->getPrefix() . 'id',
                        'value_options' => $value_options,
                    ),
                    'attributes' => array('class' => 'form-control', 'multiple' => 'multiple'),
                ));
            }

            foreach ($this->entity->getModel()->getAllFields() as $field) {

                $type          = 'Zend\Form\Element\Text';
                $attributes    = array();
                $value_options = array();
                $name          = '';
                $label         = '';

                if (in_array($field, array_merge($this->entity->getModel()->getIntegers(), $this->entity->getModel()->getVarchars(), $this->entity->getModel()->getMultilingualVarchars(), $this->entity->getModel()->getImageCaptions(), $this->entity->getModel()->getFileCaptions(), $this->entity->getModel()->getMultilingualFilesCaptions())) ) {

                    $type       = 'Zend\Form\Element\Text';
                    $attributes = array('class' => 'form-control');
                    $name       = $field;
                    $label      = $this->entity->getModel()->getPrefix() . $field;

                } elseif (in_array($field, array_merge($this->entity->getModel()->getTexts(), $this->entity->getModel()->getMultilingualTexts()))) {

                    $type       = 'Zend\Form\Element\Textarea';
                    $attributes = array('class' => 'form-control');
                    $name       = $field;
                    $label      = $this->entity->getModel()->getPrefix() . $field;

                } elseif (in_array($field, array_merge($this->entity->getModel()->getLongTexts(), $this->entity->getModel()->getMultilingualLongTexts()))) {

                    $type       = 'Zend\Form\Element\Textarea';
                    $attributes = array('class' => 'tinyMce');
                    $name       = $field;
                    $label      = $this->entity->getModel()->getPrefix() . $field;

                } elseif (in_array($field, $this->entity->getModel()->getEnums())) {

                    $type       = 'Zend\Form\Element\Radio';
                    $attributes = array('class' => 'switch','value' => '0');
                    $value_options = array('0' => 'No', '1' => 'Yes');
                    $name       = $field;
                    $label      = $this->entity->getModel()->getPrefix() . $field;

                } elseif (in_array($field, array_merge($this->entity->getModel()->getImages(), $this->entity->getModel()->getFiles(), $this->entity->getModel()->getMultilingualFiles()))) {

                    $type       = 'Zend\Form\Element\Text';
                    $attributes = array('class' => 'form-control', 'id' => $field);
                    if (in_array($field, $this->entity->getModel()->getImages())) {
                        $attributes = array_merge($attributes,array('data-type' => 'image'));
                    } else {
                        $attributes = array_merge($attributes,array('data-type' => 'file'));
                    }
                    $name       = $field;
                    $label      = $this->entity->getModel()->getPrefix() . $field;

                } elseif (in_array($field , $this->entity->getModel()->getRelations())) {
                    $type       = 'Zend\Form\Element\Select';
                    $attributes = array('class' => 'form-control');

                    $name       = $field->inputFieldName;
                    $label      = $field->inputFieldName;

                    if (in_array($field->getRelationType(), array('oneToMany', 'manyToMany'))) {
                        $attributes['multiple'] = 'multiple';
                    } else {
                        $value_options[0] = 'Please Choose';
                    }

                    $activeModel = $this->controlPanel->instantiateModelForUser($field->getRelatedModel());
                    foreach ($activeModel->getListingForSelect() as $listingItem) {
                        $value_options[$listingItem->id] = $listingItem->{$field->getRelatedSelectDisplayFields()};
                    }
                } elseif (in_array($field , $this->entity->getModel()->getCustomSelections())) {
                    $type       = 'Zend\Form\Element\Select';
                    $attributes = array('class' => 'form-control');

                    if ($field->isMultiple()) {
                        $attributes['multiple'] = 'multiple';
                        $value_options = $field->getSelectOptions();
                    } else {
                        $value_options = array_merge(array('0' => 'Please Choose'), $field->getSelectOptions());
                    }

                    $name       = $field->getFieldName();
                    $label      = $this->entity->getModel()->getPrefix() . $field->getFieldName();
                }

                if (in_array($field, $this->entity->getModel()->getAllMultilingualFields())) {
                    foreach ($this->controlPanel->getSiteLanguages() as $languageId => $language) {
                        if (array_key_exists('id', $attributes)) {
                            $attributes['id'] = $attributes['id'] . '-' . $languageId;
                        }
                        $this->form->add(array(
                            'type' => $type,
                            'name' => $name . '[' . $languageId . ']',
                            'options' => array(
                                'label' => $label,
                                'value_options' => $value_options,
                            ),
                            'attributes' => array_merge($attributes,array('placeholder' => $name)),
                        ));
                    }
                } else {
                    $this->form->add(array(
                        'type' => $type,
                        'name' => $name,
                        'options' => array(
                            'label' => $label,
                            'value_options' => $value_options,
                        ),
                        'attributes' => array_merge($attributes,array('placeholder' => $name)),
                    ));
                }
            }
        }
        return $this->form;
    }
}