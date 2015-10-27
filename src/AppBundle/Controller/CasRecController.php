<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
use AppBundle\Entity as EntityDir;


/**
 * @Route("/casrec")
 */
class CasRecController extends RestController
{
    /**
     * Bulk insert
     * Max 10k otherwise failing (memory reach 128M)
     * 
     * @Route("/bulk-add/{truncate}")
     * @Method({"POST"})
     */
    public function addBulk(Request $request, $truncate)
    {
        $maxRecords = 50000;
        $persistEvery = 5000;
        
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);
        
        ini_set('memory_limit','1024M');
        set_time_limit(600);
        
        $data = json_decode(gzuncompress(base64_decode($request->getContent())), true);
        $count = count($data);
        
        if (!$count) {
            throw new \RuntimeException("No record received from the API");
        }
        if ($count > $maxRecords) {
            throw new \RuntimeException("Max $maxRecords records allowed in a single bulk insert");
        }
        
        $this->get('logger')->info('Received ' . count($data) . ' records');
        
        $em = $this->getEntityManager();
        $validator = $this->get('validator');
        
        try {
            $em->beginTransaction();
            if ($truncate) {
                $em->getConnection()->query('TRUNCATE TABLE casrec');
            }

            $index = 1;
            foreach ($data as  $row) {
                $casRec = new EntityDir\CasRec(
                    $row['Case'], 
                    $row['Surname'], 
                    $row['Deputy No'], 
                    $row['Dep Surname'], 
                    $row['Dep Postcode']
                );
                
                $errors = $validator->validate($casRec);
                if (count($errors) > 0) {
                    $this->get('logger')->warning($errors);
                    unset($casRec);
                }  else {
                    $em->persist($casRec);
                    if (($index++ % $persistEvery) === 0) {
                       $em->flush();
                       $em->clear();
                       $this->get('logger')->info("saved $index / $count records. ".(memory_get_peak_usage() / 1024 / 1024)." MB of memory used");
                    }
                }
            }

            $em->flush();
            $em->commit();
            $em->clear();
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $em->rollback();
            
            throw new \RuntimeException($e->getMessage());
        }
        
        return $index;
    }

     /**
     * @Route("/count")
     * @Method({"GET"})
     */
    public function userCount()
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);
        
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('count(c.id)');
        $qb->from('AppBundle\Entity\CasRec','c');

        $count = $qb->getQuery()->getSingleScalarResult();
        
        return $count;
    }
    
}
