<?php

namespace SftpBundle\Controller;

use phpseclib\Net\SFTP;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        /* service van maken? Niet meteen nodig, twee regels.
 * Wel om default ook te kunnen gebruiken.
*/
        $sftp = new SFTP($this->container->getParameter('ftp_host'));
        $sftp_login = $sftp->login($this->container->getParameter('ftp_user'), $this->container->getParameter('ftp_password'));
        // einde service

        if ($sftp_login) {
            $dir = $this->container->getParameter('ftp_serverpath');
            $rawlist = $sftp->rawlist($dir);
        }

        return $this->render('SftpBundle:Default:index.html.twig', [
            'dir' => $dir,
            'rawlist' => $rawlist,
        ]);
    }
}
