<?php

class Application_Form_TagRevert extends Zend_Form{
    
    public function init(){

        $captcha = new Zend_Form_Element_Captcha('captcha', array(
            'label' => "Enter symbols:",
            'captcha' => array(
                'captcha'   => 'Image', 
                'wordLen'   => 4,
                'width'     => 200,
                'timeout'   => 120,
                'expiration'=> 300,
               'font'       => My_Advanced::getBasePath(). 'fonts/arial.ttf',
                'imgDir'    => My_Advanced::getBasePath(). 'images/captcha/',
                'imgUrl'    => '/images/captcha/',
                'gcFreq'    => 5
            ),
        ));
        $captcha->setDecorators(array('ViewHelper', 'Errors',
                                array('HtmlTag', array('tag' => 'div', 'class' => 'my-captcha')),
                                array('Label', array('tag' => 'div', 'class' => 'label-my-captcha')),
        ));
        $captcha->removeDecorator("ViewHelper");


        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('OK');
        $submit->setDecorators(array('ViewHelper', 'Errors',
                               array('HtmlTag', array('tag' => 'div', 'class' => 'my-submit'))     
        ));
        
        $this->setDecorators(array('FormElements',
                     array('HtmlTag', array('tag' => 'div', 'id' => 'form-revert-tag')),
                           'Form', ));
        
        $this->addElements(array($captcha, $submit));
        
    }
    
}?>
