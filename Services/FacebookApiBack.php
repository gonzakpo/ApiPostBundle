<?php
namespace Tecspro\Bundle\ApiPostBundle\Services;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;

class FacebookApiBack
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
            'appId'  => "252525252",
            'secret' => "fsdf3425fe",
            // 'req_perms' => 'user_friends, email, publish_actions, manage_pages',
            // 'redirect_uri' => '',
            'client_id' => $facebookId,
        );
// GET /oauth/access_token?
// client_id={app-id}
// &client_secret={app-secret}
// &grant_type=client_credentials
        // $this->facebook = new \Facebook($config);
        FacebookSession::setDefaultApplication($this->config['appId'], $this->config['secret']);
        // // If you already have a valid access token:
        // $this->session = new FacebookSession('access-token');
        // If you're making app-level requests:
        // $this->session = FacebookSession::newAppSession();

        
        // If you already have a valid access token:
        // echo $user->getFacebookAccessToken().'<br>';
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
        // If you're making app-level requests:
        // $this->session = FacebookSession::newAppSession();

        // $request = new FacebookRequest(
        //     $this->session,
        //     'GET',
        //     '/oauth/client_code',
        //     $config
        // );
        // $request->execute();
        // $data = $request->getResponse();
        // ladybug_dump_die($data);
        // ladybug_dump_die($request->execute());

        // $response = (new FacebookRequest(
        //   $this->session, 'GET', '/oauth/access_token', array(
        //     'client_id' => $config['appId'],
        //     'client_secret' => $config['secret'],
        //     'grant_type' => 'user_friends, email, publish_actions, manage_pages',
        //   )
        // ))->execute()->getGraphObject();
        // $this->session = new FacebookSession($this->session->getAccessToken());
        ladybug_dump($this->session);
        // ladybug_dump_die($response);
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
        // if (count($noticia->getImagenes())) {
        //     $picture = 'http://...ar' . $noticia->getImagenes()->first()->getWebPath();
        // } else {
        //     $picture = "http://...ar/bundles/tecsprfront/web/img/.png"; 
        // }
        if($this->session) {
            // Make a new request and execute it.
            // try {
            //   $response = (new FacebookRequest($this->session, 'GET', '/me/accounts'))->execute();
            //   $object = $response->getGraphObject();
            //   // ladybug_dump_die($object->asArray()['data'][0]);
            //   // foreach ($object->asArray() as $key => $value) {
            //   //     ladybug_dump($value);
            //   //     echo "--<br>";
            //   // }
            //   ladybug_dump(get_object_vars($object->asArray()['data'][0]));
            // } catch (FacebookRequestException $ex) {
            //   echo $ex->getMessage();
            // } catch (\Exception $ex) {
            //   echo $ex->getMessage();
            // }
            try {
                /* make the API call */
            //     $request = new FacebookRequest(
            //       $this->session,
            //       'GET',
            //       '/me/accounts'
            //     );
            //     $response = $request->execute();
            //     $graphObject = $response->getGraphObject();
            // ladybug_dump($this->session->getSessionInfo());
                // $appId = $this->session->getSessionInfo()->getAppId();
                // echo 'appId: '.$appId.'<br>';
                // echo 'client id: '.$this->config['client_id'].'<br>';
                // $get = (
                //     new FacebookRequest(
                //         $this->session, 'GET', '/'.$this->config['client_id'].'/accounts'
                //     )
                // )->execute()->getGraphObject();
                // )->execute()->getResponse();
                // )->getParameters();
                // ladybug_dump($get);
                $response = (
                    new FacebookRequest(
                        $this->session, 'POST', '/2453225/feed', array(
                            'link' => 'www.chaco.dev',
                            'message' => 'Chaco'
                        )
                    )
                )->execute()->getGraphObject();

                echo "Posted with id: " . $response->getProperty('id');
            } catch(FacebookRequestException $e) {
                echo "Exception occured, code: " . $e->getCode();
                echo " with message: " . $e->getMessage();
            }
        }
        ladybug_dump_die($this->session);
        // $pageInfo = $this->facebook->api("/$facebookId?fields=access_token");
        // echo var_dump($page_info);die;
        // $oid = $facebook->api('/' . $facebookId . '/feed', 'POST', array(
        //     "access_token" => $pageInfo['access_token'],
        //     "link"         => $this->getCompleteUrl($post->getId()),
        //     "message"      => $noticia->getDescripcion(),
        //     "caption"      => "ipap.chaco.gov.ar",
        //     "name"         => $noticia->getTitulo(),
        //     "picture"      => $picture,
        //     )
        // );
    }

    private function getUser()
    {
        return $this->context->getToken()->getUser();
    }

}
