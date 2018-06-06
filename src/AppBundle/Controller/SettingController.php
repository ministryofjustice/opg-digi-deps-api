<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/setting")
 */
class SettingController extends RestController
{
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function getSetting(Request $request, $id)
    {
        $setting = $this->getRepository(EntityDir\Setting::class)->find($id);/* @var $setting EntityDir\Setting */

        $this->setJmsSerialiserGroups(['setting']);

        return $setting ?: [];
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function upsertSetting(Request $request, $id)
    {
        $data = $this->deserializeBodyContent($request, [
            'content' => 'notEmpty',
            'enabled' => 'mustExist',
        ]);

        $setting = $this->getRepository(EntityDir\Setting::class)->find($id); /* @var $setting EntityDir\Setting */
        if ($setting) { //update
            $setting->setContent($data['content']);
            $setting->setEnabled($data['enabled']);
        } else { //create new one
            $setting = new EntityDir\Setting($id, $data['content'], $data['enabled']);
            $this->getEntityManager()->persist($setting);
        }

        $this->getEntityManager()->flush($setting);

        return $setting->getId();
    }
}
