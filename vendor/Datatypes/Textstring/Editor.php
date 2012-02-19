<?php
namespace Datatypes\Textstring;

use Application\Model\Datatype,
    Zend\Form\Element;

class Editor extends AbstractDatatpye\Editor
{
    public function save()
    {
        $value = $this->getRequest()->getPost($this->getName());
        $this->setValue($value);
    }

    public function load()
    {
        $parameters = $this->getConfiguration();
        $property = $this->getProperty();
        $textstring = new Element\Text($this->getName());
        $textstring->setLabel($property->getName());
        $textstring->setValue($this->getValue());
        if(!empty($parameters['length']))
        {
            $textstring->setAttrib('maxlength', $parameters['length']);
        }

        return $textstring;
    }
}

