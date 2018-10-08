<?php

namespace App\Libraries;

use GuzzleHttp\Client;

class ConvertKit{

    private $url;
    private $apiKey;
    private $apiSecret;

    public function __construct() {
        $this->url = 'https://api.convertkit.com/v3';
        $this->apiKey = env('CONVERT_KIT_API_KEY');
        $this->apiSecret = env('CONVERT_KIT_API_SECRET');
    }

    /**
     * Get form details for particular form
     * @param $formId
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getFormDetails($formId) {

        $client = new Client();
        $response = $client->get(
            $this->url.'/forms/'.$formId.'?api_key='.$this->apiKey
        );

        return $response;
    }

    /**
     * Get all Forms
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getForms() {

        $client = new Client();
        $response = $client->get(
            $this->url.'/forms/?api_key='.$this->apiKey
        );

        return $response;
    }

    /**
     * Get subscribers of a specific form
     * @param $formId
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getSubscribersByFormId($formId) {
        $client = new Client([
            'verify' => false,
            'base_uri' => env('APP_URL'),
        ]);

        $response = $client->get(
            $this->url.'/forms/'.$formId.'/subscriptions?api_key='.$this->apiKey
        );

        return $response;
    }

    /**
     * Get subscribed users to sequence
     * @param $sequenceId
     * @param $fields Ex: array('email' => 'testemail@gmail.com', 'first_name' => 'John')
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function subscribedUsersToSequence($sequenceId , $fields) {

        $client = new Client();
        $fields['api_key'] = $this->apiKey;

        $response = $client->post(
            $this->url.'/courses/'.$sequenceId.'/subscribe',
            array(
                'form_params' => $fields,
            )
        );

        return $response;

    }

    /**
     * unsubscribe User from sequence
     * @param $sequenceId
     * @param $fields
     * @return mixed
     */
    public function unsubscribeUserFromSequnce($sequenceId, $fields) {
        $client = new Client();

        $fields['api_key'] = $this->apiKey;

        $response = $client->post(
            $this->url.'/courses/'.$sequenceId.'/subscribe',
            array(
                'form_params' => $fields,
            )
        );

        return $response;
    }

    /**
     * Un subscribe user from convert kit
     * @param $email
     * @return bool|mixed
     */
    public function unsubscribeUser($email) {
        $client = new Client();
        $response = false;
        $fields['api_secret'] = $this->apiSecret;
        $fields['email'] = $email;

        $response = $client->put(
            $this->url.'unsubscribe',
            array(
                'form_params' => $fields,
            )
        );

        if($response->getStatusCode() == '200') {
            $response = json_decode($response->getBody());

        }

        return $response;
    }

    /**
     * Subscribe User to a tag
     * @param $tagId
     * @param $fields
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function subscribeUserToTag($tagId , $fields) {

        $client = new Client();

        $fields['api_key'] = $this->apiKey;

        $response = $client->post(
            $this->url.'/tags/'.$tagId.'/subscribe',
            array(
                'form_params' => $fields,
            )
        );

        return $response;
    }

    /**
     * List subscribers of form
     * @param $formId
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listSubscribersOfForm($formId) {

        $client = new Client();
        $fields['api_secret'] = $this->apiSecret;

        $response = $client->get(
            $this->url.'/forms/'.$formId.'/subscriptions',
            array(
                'form_params' => $fields,
            )
        );

        return $response;
    }

    /**
     * List tags of a subscriber
     * @param $subscriberId
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listTagsOfSubscriber($subscriberId) {

        $client = new Client();
        $tags = [];

        $fields['api_secret'] = $this->apiSecret;

        $response = $client->get(
            $this->url.'/subscribers/'.$subscriberId.'/tags',
            array(
                'form_params' => $fields,
            )
        );

        if($response->getStatusCode() == '200') {
            $response = json_decode($response->getBody());
            $tags = $response->tags;
        }

        return $tags;
    }

    /**
     * List subscribers
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listSubscribers($fields = []) {

        $client = new Client();
        $subscriber = false;
        $fields['api_secret'] = $this->apiSecret;

        $response = $client->get(
            $this->url.'/subscribers',
            array(
                'form_params' => $fields,
            )
        );

        if($response->getStatusCode() == '200') {
            $response = json_decode($response->getBody());
            if(!empty($response->subscribers[0]->id)) {
                $subscriber = $response;
            }else {
                $fields['sort_field'] = 'cancelled_at';
                $response = $client->get(
                    $this->url.'/subscribers',
                    array(
                        'form_params' => $fields,
                    )
                );
                $response = json_decode($response->getBody());
                if(!empty($response->subscribers[0]->id)) {
                    $subscriber = $response;
                }
            }

        }

        return $subscriber;
    }

    /**
     * Un subscribe from a tag
     * @param $email
     * @param $tagId
     * @return bool
     */
    public function unSubscribeFromATag($email, $tagId) {

        $status = false;
        $client = new Client();
        $fields['api_secret'] = $this->apiSecret;
        $fields['email'] = $email;

        $subscriber = $this->getSubscriber($email);
        if(!empty($subscriber)) {

            $subscriberId = $subscriber->id;
            $removedTagResponse = $client->delete(
                $this->url.'/subscribers/'.$subscriberId.'/tags/'.$tagId,
                array(
                    'form_params' => $fields,
                )
            );
            if($removedTagResponse->getStatusCode() == '200'){
                $status = true;
            }
        }
        return $status;
    }


    /**
     * Subscribe User to a tag
     * @param $formId
     * @param $fields
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function subscribeUserToForm($formId , $fields) {

        $client = new Client();
        $fields['api_key'] = $this->apiKey;

        $response = $client->post(
            $this->url.'/forms/'.$formId.'/subscribe',
            array(
                'form_params' => $fields,
            )
        );

        return $response;
    }

    /**
     * Check Subscriber is tagged
     * @param $subscriberId
     * @param $tagId
     * @return bool
     */
    public function checkSubscriberIsTagged($subscriberId, $tagId) {
        $status = false;
        if(!empty($subscriberId)) {
            $tags = $this->listTagsOfSubscriber($subscriberId);

            $isNewsletterSubscribed = $this->_isValueExistInKeyValuePairArray($tagId, $tags);
            $status = $isNewsletterSubscribed;
        }

        return $status;
    }

    /**
     * get subscriber
     * @param $email
     * @return bool
     */
    public function getSubscriber($email) {
        $subscriber = false;
        $response = $this->listSubscribers(['email_address' => $email]);
        if(!empty($response) && !empty($response->subscribers[0]->id)) {
            $subscriber =  $response->subscribers[0];
        }

        return $subscriber;
    }

    /**
     * Update subscriber by email
     * @param $email
     * @param $fields
     * @return bool
     */
    public function updateSubscriberByEmail($email , $fields) {
        $subscriberUpdated = false;
        $subscriber = $this->getSubscriber($email);
        $client = new Client();
        $fields['api_secret'] = $this->apiSecret;
        if($subscriber) {
            $response = $client->put(
                $this->url.'/subscribers/'.$subscriber->id,
                array(
                    'form_params' => $fields,
                )
            );

            if($response->getStatusCode() == '200') {
                $response = json_decode($response->getBody());
                if(!empty($response)) {
                    $subscriberUpdated = $response->subscribers;
                }

            }
        }

        return $subscriberUpdated;
    }

    /**
     * Update subscriber by subscriber email
     * @param $subscriberId
     * @param $fields
     * @return array|bool|mixed|object
     */
    public function updateSubscriberBySubscriberId($subscriberId , $fields) {
        $subscriberUpdated = false;
        $client = new Client();
        $fields['api_secret'] = $this->apiSecret;

        $response = $client->put(
            $this->url.'/subscribers/'.$subscriberId,
            array(
                'form_params' => $fields,
            )
        );

        if($response->getStatusCode() == '200') {
            $response = json_decode($response->getBody());
            if(!empty($response)) {
                $subscriberUpdated = $response;
            }
        }

        return $subscriberUpdated;
    }

    /**
     * Change email of a subscriber
     * @param $oldEmail
     * @param $newEmail
     * @return array|bool|mixed|object
     */
    public function changeEmailOfSubscriber($oldEmail, $newEmail) {
        $subscriber = $this->getSubscriber($oldEmail);
        $userUpdated = false;
        if($subscriber) {
            $newSubscriber = $this->getSubscriber($newEmail);
            if($newSubscriber) {
                    $tags = $this->listTagsOfSubscriber($subscriber->id);

                    //since 'tags' => ['123', '456', '789'] is not working at this moment | convert kit side issue
                    if(!empty($tags)) {
                        foreach ($tags as $tag) {
                            $this->subscribeUserToTag($tag->id, [
                                'email' => $newEmail,
                            ]);
                        }
                    }

                    $this->unsubscribeUser($oldEmail);
            }else {
                $userUpdated = $this->updateSubscriberBySubscriberId($subscriber->id, [
                    'email_address' => $newEmail,
                    'first_name' => $subscriber->first_name,
                    'fields' => [
                        'company_name' => $subscriber->fields->company_name,
                        'indusrty' => $subscriber->fields->indusrty,
                        'last_name' => $subscriber->fields->last_name,
                        'mobile' => $subscriber->fields->mobile,
                        'title' => $subscriber->fields->title,
                    ]
                ]);
            }

            return $userUpdated;
        }
    }

    private function _isValueExistInKeyValuePairArray($value, $array) {
        $status = false;
        foreach ($array as $singleArray) {
            if($singleArray->id == $value) {
                $status = true;
                break;
            }
        }
        return $status;
    }

}

