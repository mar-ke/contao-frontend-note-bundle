<?php

// src/ContaoFrontendPostitBundle.php
namespace Mar-ke\ContaoFrontendPostitBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Mar-ke\ContaoFrontendPostitBundle\DependencyInjection\ContaoFrontendPostitExtension;

class ContaoFrontendPostitBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new ContaoFrontendPostitExtension();
        }

        return $this->extension;
    }
}