<?php

require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/classes/AIServices/AICompletionException.php";
require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/classes/AIServices/AICompletionServiceInterface.php";
/**
 * OpenAI Client
 */
class OpenAIService implements AITextCompletionServiceInterface, AIImageCompletionServiceInterface
{
    private $apiKey;
    private $textCompletionsModel;
    private $imageCompletionsModel;
    private $imageCompletionSize;
    private $imageCompletionHDQuality;

    public function __construct(string $apiKey, string $textCompletionsModel = "gpt-4-turbo", string $imageCompletionsModel = "dall-e-3", string $imageCompletionSize = "1024x1024", bool $imageCompletionHDQuality = false)
    {
        $this->apiKey = $apiKey;
        $this->textCompletionsModel = $textCompletionsModel;
        $this->imageCompletionsModel = $imageCompletionsModel;
        $this->imageCompletionSize = $imageCompletionSize;
        $this->imageCompletionHDQuality = $imageCompletionHDQuality;
    }

    public function perform_text_completion(string $prompt, bool $return_json_response, array $image_attachment_urls = null, float $temperature = 0.7, int $max_tokens = 4096)
    {
        $content = array(
            array("type" => "text", "text" => $prompt)
        );

        if (isset($image_attachment_urls)) {
            foreach ($image_attachment_urls as $attachment) {
                $content[] = array(
                    "type" => "image_url",
                    "image_url" => array("url" => $attachment)
                );
            }
        }

        $body = array(
            "model" => $this->textCompletionsModel,
            "messages" => array(array("role" => "user", "content" => $content)),
            "temperature" => $temperature,
            "max_tokens" => $max_tokens
        );

        if ($return_json_response) {
            $body["response_format"] = array("type" => "json_object");
        }
/*
        echo "<pre>";
        echo print_r($body, true);
        echo "</pre>";
*/
        $response = $this->send_request('https://api.openai.com/v1/chat/completions', 'POST', $body);

        if ($response['error']) {
            throw new AICompletionException($response['error_message']);
        }

        $data = $response['body'];
        $finish_reason = $data["finish_reason"] ?? null;
        if ($return_json_response && $finish_reason == "length") {
            throw new AICompletionException("The completion took more than the max_tokens provided, and since return_json_response is true, the call is throwing because the returned json completion is not complete and will be not deserializable");
        }

        $completion = $data['choices'][0]['message']['content'] ?? null;
        if ($completion) {
            return $completion;
        } else {
            throw new AICompletionException("Invalid response, cannot find the completion content.");
        }
    }

    public function perform_image_completion(string $prompt, int $count = 1)
    {
        $body = array(
            "model" => $this->imageCompletionsModel,
            "prompt" => $prompt,
            "n" => $count,
            "size" => $this->imageCompletionSize,
            "quality" => $this->imageCompletionHDQuality == true ? "hd" : "standard"
        );

        print_r($body);
        $response = $this->send_request('https://api.openai.com/v1/images/generations', 'POST', $body);

        if ($response['error']) {
            throw new AICompletionException($response['error_message']);
        }

        $decodedJSON = $response['body'];
        if (isset($decodedJSON["data"])) {
            $generatedImagesURLs = array_map(function ($image) {
                return $image['url'];
            }, $decodedJSON['data']);
            return $generatedImagesURLs;
        } else {
            throw new AICompletionException("Error decoding response from OpenAI api call");
        }
    }

    private function send_request($url, $method, $body)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['error' => true, 'error_message' => $err];
        } else {
            $decoded_response = json_decode($response, true);
            if (isset($decoded_response['error'])) {
                return ['error' => true, 'error_message' => $decoded_response['error']['message']];
            }
            return ['error' => false, 'body' => $decoded_response];
        }
    }
}

