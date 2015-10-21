<?php
namespace Tecspro\Bundle\ApiPostBundle\Services;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;

class FacebookApi
{
    private $container;
    private $em;
    private $context;
    private $config;
    private $session;

    public function __construct($container, $em, $context)
    {
        $this->container = $container;
        $this->em = $em;
        $this->context = $context;
    }

    /**
     * Configure facebook
     *
     * page appId
     * page secret
     *
     * @return void;
     */
    public function configure()
    {
        $user = $this->getUser();
        $facebookId = $user->getFaceBookId();

        $this->config = array(
            'appId'  => "5345454543",
            'secret' => "gdfgdf454534",
            'client_id' => $facebookId,
        );
        FacebookSession::setDefaultApplication($this->config['appId'], $this->config['secret']);
        // If you already have a valid access token:
        $this->session = new FacebookSession($user->getFacebookAccessToken());
        // To validate the session:
        try {
          $this->session->validate();
        } catch (FacebookRequestException $ex) {
          // Session not valid, Graph API returned an exception with the reason.
          echo $ex->getMessage();
        } catch (\Exception $ex) {
          // Graph API returned info, but it may mismatch the current app or have expired.
          echo $ex->getMessage();
        }
    }

    /**
     * Post facebook
     *
     * @param array $post
     *
     * @return void;
     */
    public function postInPage($post)
    {
        $idPostFace = null;
        //obtengo fanpage segun provincia
        switch ($post['provincia']) {
          case 'chaco':
            $dataPage = 0;
            $dataIdPage = '3543534534';
            break;
          case 'corrientes':
            $dataPage = 1;
            $dataIdPage = '54353454353';
            break;
          default:
            $dataPage = null;
            $dataIdPage = null;
            break;
        }

        if($this->session) {
            try {
                //obtengo el access_token de la page
                $response = (new FacebookRequest($this->session, 'GET', '/me/accounts'))->execute();
                $object = $response->getGraphObject();
                $page = get_object_vars($object->asArray()['data'][$dataPage]);
                if ($page['id'] == $dataIdPage) {
                    $this->session = new FacebookSession($page['access_token']);
                    $this->session->validate();
                }
                //fin obtengo el access_token de la page
                $url = '/'.$dataIdPage.'/feed';
                $response = (
                    new FacebookRequest(
                        $this->session, 'POST', $url, array(
                            'link'    => $post['link'],
                            'message' => $post['message'],
                        )
                    )
                )->execute()->getGraphObject();

                $idPostFace = $response->getProperty('id');
            } catch(FacebookRequestException $e) {
                // echo "Exception occured, code: " . $e->getCode();
                // echo " with message: " . $e->getMessage();
                $idPostFace = false;
            }
        }

        return $idPostFace;
    }

    private function getUser()
    {
        return $this->context->getToken()->getUser();
    }

}