<?php

namespace AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class FixDefaultSchemaListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'postGenerateSchema',
        );
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $Args)
    {
        $Schema = $Args->getSchema();

        if (! $Schema->hasNamespace('public')) {
            $Schema->createNamespace('public');
        }
    }
}
