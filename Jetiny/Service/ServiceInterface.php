<?php
namespace Jetiny\Service;

interface ServiceInterface
{
    public function setup($context);
    public function teardown();
}