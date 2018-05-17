<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 16-5-18
 * Time: 16:23
 */

namespace SftpBundle\Controller;


use phpseclib\Net\SFTP;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Ftp controller.
 *
 * @Route("ftp")
 */
class FtpController extends Controller
{
    /**
     * @Route("/bulk")
     */
    public function bulkAction()
    {
        /* service van maken? Niet meteen nodig, twee regels.
         * Wel om default ook te kunnen gebruiken.
        */
        $sftp = new SFTP($this->container->getParameter('ftp_host'));
        $sftp_login = $sftp->login($this->container->getParameter('ftp_user'), $this->container->getParameter('ftp_password'));
        // einde service

        if ($sftp_login) {

            $dir = $sftp->exec('cd '.$this->container->getParameter('ftp_serverpath').'; ls -la');

            $cdir = $sftp->chdir($this->container->getParameter('ftp_serverpath'));

            $nlist = $sftp->nlist();

            $counts[] = null;

            if ($cdir == 1) {

                foreach ($nlist as $file) {
                    $content = $sftp->get($file);
                    $counts[$file] = substr_count($content, "\n");

                    if ($counts[$file] > 0) {
                        if (!$sftp->is_dir('backup')) {
                            $sftp->mkdir('backup');
                        }

                        $sftp->put('backup/' . $file, $content);
                        $sftp->delete($file);
                    }
                }

                return $this->render('SftpBundle:Ftp:index.html.twig', array(
                    'path' => $sftp->exec('pwd'),
                    'dir' => $dir,
                    'cdir' => $cdir,
                    'cd' => $this->container->getParameter('ftp_serverpath'),
                    'nlist' => $nlist,
                    'counts' => $counts,
                ));
            }
            return $this->render('SftpBundle:Default:index.html.twig');

        } else throw new \Exception('Cannot login into your server !');

    }

    /**
     * @Route("/reversebulk")
     */
    public function reverseAction()
    {
        /* service van maken? Niet meteen nodig, twee regels.
         * Wel om default ook te kunnen gebruiken.
        */
        $sftp = new SFTP($this->container->getParameter('ftp_host'));
        $sftp_login = $sftp->login($this->container->getParameter('ftp_user'), $this->container->getParameter('ftp_password'));
        // einde service

        if ($sftp_login) {

            $dir = $sftp->exec('cd '.$this->container->getParameter('ftp_serverpath').'; ls -la');

            $cdir = $sftp->chdir($this->container->getParameter('ftp_serverpath').'/backup');

            $nlist = $sftp->nlist();

            $counts[] = null;

            if ($cdir == 1) {

                foreach ($nlist as $file) {
                    $content = $sftp->get($file);
                    $counts[$file] = substr_count($content, "\n");

                    if ($counts[$file] > 0) {

                        $sftp->put('../' . $file, $content);
                        $sftp->delete($file);
                    }
                }
                $sftp->delete('../backup');

                return $this->render('SftpBundle:Ftp:index.html.twig', array(
                    'path' => $sftp->exec('pwd'),
                    'dir' => $dir,
                    'cdir' => $cdir,
                    'cd' => $this->container->getParameter('ftp_serverpath').'/backup',
                    'nlist' => $nlist,
                    'counts' => $counts,
                ));
            }
            return $this->render('SftpBundle:Default:index.html.twig');

        } else throw new \Exception('Cannot login into your server !');

    }
}