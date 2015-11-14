<?php

namespace Tecspro\Bundle\ApiPostBundle\Services;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;
use Facebook\GraphUser;

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

        $this->config = array(
            'appId' => $config->getConfigFace()->getAppId(),
            'secret' => $config->getConfigFace()->getSecret(),
            'client_id'
        );
        FacebookSession::setDefaultApplication($this->config['appId'], $this->config['secret']);


        // If you already have a valid access token:
        $this->session = new FacebookSession($config->getToken());
        // To validate the session:
        try {
            $this->session->validate();

            // ladybug_dump_die($this->session);
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
    public function postInPage($post) {
        if ($this->session) {

            try {

                $response = (new FacebookRequest(
                        $session, 'POST', '/me/feed', array(
                    'link' => 'www.example.com',
                    'message' => 'User provided message'
                        )
                        ))->execute()->getGraphObject();

                echo "Posted with id: " . $response->getProperty('id');
            } catch (FacebookRequestException $e) {

                echo "Exception occured, code: " . $e->getCode();
                echo " with message: " . $e->getMessage();
            }
        }
    }

    private function getUser() {
        return $this->context->getToken()->getUser();
    }

}
