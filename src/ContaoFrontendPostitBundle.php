<?php

namespace Mar-ke\ContaoFrontendPostitBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Mar-ke\ContaoFrontendPostitBundle\DependencyInjection\ContaoFrontendPostitExtension;

class ContaoFrontendPostitBundle extends AbstractBundle
{
    
    public function getContainerExtension() {

        return new ContaoFrontendPostitExtension();

    }
    
}