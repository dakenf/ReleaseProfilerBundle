<?php

namespace Daken\ReleaseProfilerBundle\Notifier;

use Daken\ReleaseProfilerBundle\Entity\Error;

class SlackNotifier implements NotifierInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;
    private $hookUrl;
    private $emoji;
    private $username;

    public function __construct($guzzleClient, $hookUrl, $username, $emoji)
    {
        $this->guzzleClient = $guzzleClient;
        $this->hookUrl = $hookUrl;
        $this->emoji = $emoji;
        $this->username = $username;
    }

    public function notify(Error $error, $url)
    {
        $content = [
            "text" => "*{$error->getError()}*\n" .
                "{$error->getRequest()}\n" .
                "*Reference:* {$error->getReference()} <{$url}>\n\n" .

                "{$error->getExceptionClass()} at {$error->getFilename()} on {$error->getLineNumber()}\n\n" .
                "{$error->getStackTrace()}",
        ];

        if ($this->username) {
            $content['username'] = $this->username;
        }

        if ($this->emoji) {
            $content['icon_emoji'] = $this->emoji;
        }

        $this->guzzleClient->post($this->hookUrl, ['body' => json_encode($content)]);
    }
}
