<?php

require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/admin/general_settings.php";

require_once ABSPATH . "wp-content/plugins/wp-pipeflow/classes/Pipeline/CorePipeFlow.php";
require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/classes/AIServices/OpenAIService.php";

class AIImageGenerationStage extends AbstractPipelineStage
{
    private StageConfiguration $stageConfiguration;

    public function __construct(StageConfiguration $stageConfiguration)
    {
        $this->stageConfiguration = $stageConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function execute(PipelineContext $context): PipelineContext
    {
        // Takes the OpenAI api key from the context
        $apiKey = get_openai_apikey();
        if(is_null($apiKey)) {
            throw new PipelineExecutionException("OpenAI API Key not set. Set the api key in the OpenAI for WP PipeFlow plugin settings");
        }

        $prompt = (string)$this->stageConfiguration->getSettingValue("prompt", $context, true);
        $model = (string)$this->stageConfiguration->getSettingValue("model", $context, false, AIImageGenerationStageFactory::$defaultModel);
        $imagesSize = (string)$this->stageConfiguration->getSettingValue("size", $context, false, AIImageGenerationStageFactory::$defaultSize);
        $hdQuality = (bool)$this->stageConfiguration->getSettingValue("highFidelity", $context, false, AIImageGenerationStageFactory::$defaultHighFidelity);
        $imageCount = (int)$this->stageConfiguration->getSettingValue("count", $context, false, AIImageGenerationStageFactory::$defaultImageCount);
        $resultTo = $this->stageConfiguration->getSettingValue("resultTo", $context, false, "GENERATED_IMAGE_URLS");

        $openAIService = new OpenAIService($apiKey,"gpt-4-turbo", $model, $imagesSize, $hdQuality);

        $promptProcessor = new PlaceholderProcessor($context);
        $prompt = $promptProcessor->process($prompt);
        try
        {
            $image_urls = $openAIService->perform_image_completion($prompt, $imageCount);
        }
        catch (AICompletionException $e)
        {
            throw new PipelineExecutionException("An error occurred while performing the image completion: " . $e->getMessage());
        }

        $generatedImageURLs = array();
        foreach($image_urls as $url) {
            $generatedImageURLs[] = $url;
        }
        $context->setParameter($resultTo, $generatedImageURLs);
        return $context;
    }
}