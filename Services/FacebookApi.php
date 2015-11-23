<?php
namespace Tecspro\Bundle\ApiPostBundle\Services;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;

class FacebookApi
{
    private $container;
    private $em;
    private $context;

    private $config;
    private $session;

    public function __construct($container, $em, $context) {
        $this->container = $container;
        $this->em        = $em;
        $this->context   = $context;
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
        $this->session = new FacebookSession($this->config['accessToken']);
        // If you're making app-level requests:
        //$this->session = FacebookSession::newAppSession();
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

    private function getGraphObjectUrl($method, $url)
    {
        $request     = new FacebookRequest($this->session, $method, $url);
        $response    = $request->execute();
        $graphObject = $response->getGraphObject();

        return $graphObject;
    }

    public function connectFace($config, $user) {
        $this->config = $config;
        $params = array(
            'scope' => 'email,publish_actions,manage_pages,status_update',
        );
        $loginUrl = null;

        FacebookSession::setDefaultApplication($this->config['appId'], $this->config['secret']);
        // login helper with redirect_uri
        $helper = new FacebookRedirectLoginHelper($this->config['url']);
        
        try {
          $this->session = $helper->getSessionFromRedirect();
        } catch(FacebookRequestException $ex) {
          // When Facebook returns an error
        } catch(Exception $ex) {
          // When validation fails or other local issues
        }
        // see if we have a session
        if ($this->session) {
            // User logged in, get the AccessToken entity.
            $accessToken          = $this->session->getAccessToken();
            // Exchange the short-lived token for a long-lived token.
            $longLivedAccessToken = $accessToken->extend();
            // graph api request for user data
            $graphObject = $this->getGraphObjectUrl('GET', '/me');
            //var_dump($graphObject);
            $fbid = $graphObject->getProperty('id');              // To Get Facebook ID
            //$fbfullname = $graphObject->getProperty('name'); // To Get Facebook full name
            //$femail = $graphObject->getProperty('email');    // To Get Facebook email ID/
            $userManager = $this->container->get('fos_user.user_manager');
            //$user = $userManager->findUserBy(array('id'=>$idUser));
            $user->setFacebookId($fbid);
            $user->setFacebookAccessToken($longLivedAccessToken);
            $userManager->updateUser($user);
            if (
                !$this->controlPermissions('email') or
                !$this->controlPermissions('manage_pages') or
                !$this->controlPermissions('publish_actions') or
                !$this->controlPermissions('status_update')
            ) {
                $loginUrl = $helper->getLoginUrl($params);
            }
        } else {
            $loginUrl = $helper->getLoginUrl($params);
        }

        return $loginUrl;
    }

    private function controlPermissions($permission)
    {
        $graphObject = $this->getGraphObjectUrl('GET', '/me/permissions');
        //si no existe permiso entra
        if (in_array($permission, $graphObject)) {
            $return = true;
        } else {
            $return = false;
        }

        return $return;
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
                $url = '/'.$this->config["idPage"].'?fields=access_token';
                echo $url."<br>";
                $response = (new FacebookRequest($this->session, 'GET', $url))
                    ->execute()->getGraphObject();
                $access_token_page = $response->getProperty('access_token');
                echo $access_token_page."<br>";
                if ($access_token_page) {
                    $this->session = new FacebookSession($access_token_page);
                    $this->session->validate();
                }
                //fin obtengo el access_token de la page
                $url = '/'.$this->config["appId"].'/feed';
                echo $url."<br>";
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