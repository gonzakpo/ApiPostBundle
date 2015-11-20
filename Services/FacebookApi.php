<?php
namespace Tecspro\Bundle\ApiPostBundle\Services;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;

class FacebookApi {

    private $container;
    private $em;
    private $context;
    private $config;
    private $session;

    public function __construct($container, $em, $context) {
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
    public function configure($config) {
        $this->config = $config;

        FacebookSession::setDefaultApplication($this->config['appId'], $this->config['secret']);
        // If you already have a valid access token:
        $this->session = new FacebookSession($this->config['token']);
        // If you're making app-level requests:
        $this->session = FacebookSession::newAppSession();
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

        if($this->session) {
            try {
                //obtengo el access_token de la page
                $url = "/".$this->config['idPage']."?fields=access_token";
                $response = (new FacebookRequest($this->session, 'GET', $url))
                    ->execute()->getGraphObject();
                $access_token_page = $response->getProperty('access_token');
                if ($access_token_page) {
                    $this->session = new FacebookSession($access_token_page);
                    $this->session->validate();
                }
                //fin obtengo el access_token de la page
                $url = "/".$this->config['appId']."/feed";
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
                echo "Exception occured, code: " . $e->getCode();
                echo " with message: " . $e->getMessage();
                $idPostFace = false;
            }
        }

        return $idPostFace;
    }
}
