<?php

namespace Administration\Helper\Model;

use Zend\Form\Form;

class GenericRelationFilter
{
    protected $model;
    protected $controlPanel;

    public function __construct($model, $controlPanel)
    {
        $this->model        = $model;
        $this->controlPanel = $controlPanel;
    }

    public function getForm()
    {
        $form = new Form('Filters');
        $form->setAttribute('class', 'form-inline pull-right');
        if (count($this->model->getRelations()) > 0) {
            foreach ($this->model->getRelations() as $relation) {
                if (in_array($relation->getRelationType(), array('manyToMany','manyToOne'))) {
                    unset($value_options);
                    $value_options['all'] = '---All---';
                    $activeModel = $this->controlPanel->instantiateModelForUser($relation->getRelatedModel());
                    foreach ($activeModel->getListingForSelect() as $listingItem) {
                        $optionString = '';
                        foreach ($relation->activeModel->getListingFields() as $listingField) {
                            $optionString .= $listingItem->{$listingField} . ' ';
                        }
                        $value_options[$listingItem->id] = $optionString;
                    }

                    $form->add(array(
                        'type' => 'Zend\Form\Element\Select',
                        'name' => 'relationFilters['. $relation->inputFieldName . ']',
                        'options' => array(
                            'label'         => $relation->inputFieldName,
                            'value_options' => $value_options,
                        ),
                        'attributes' => array('class' => 'form-control input-sm'),
                    ));
                }
            }
        }
        return $form;
    }
}