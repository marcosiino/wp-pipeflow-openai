<?php

require_once ABSPATH . "wp-content/plugins/wp-pipeflow/classes/Pipeline/CorePipeFlow.php";
require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/classes/AIServices/OpenAIService.php";

class AITextCompletionStage extends AbstractPipelineStage
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
        $apiKey = get_openai_apikey();
        if(is_null($apiKey)) {
            throw new PipelineExecutionException("OpenAI API Key not set. Set the api key in the OpenAI for WP PipeFlow plugin settings");
        }

        $prompt = (string)$this->stageConfiguration->getSettingValue("prompt", $context, true);
        $attachedImageURLs = (array)$this->stageConfiguration->getSettingValue("attachedImageURLs", $context, false);
        $model = (string)$this->stageConfiguration->getSettingValue("model", $context, false, AITextCompletionStageFactory::$defaultModel);
        $temperature = (string)$this->stageConfiguration->getSettingValue("temperature", $context, false, AITextCompletionStageFactory::$defaultTemperature);
        $maxTokens = (string)$this->stageConfiguration->getSettingValue("maxTokens", $context, false, AITextCompletionStageFactory::$defaultMaxTokens);
        $outputJSON = (string)$this->stageConfiguration->getSettingValue("outputJSON", $context, false, AITextCompletionStageFactory::$defaultOutputJSON);
        $resultTo = $this->stageConfiguration->getSettingValue("resultTo", $context, false, "GENERATED_TEXT_COMPLETION");

        $openAIService = new OpenAIService($apiKey, $model);

        $promptProcessor = new PlaceholderProcessor($context);
        $prompt = $promptProcessor->process($prompt);
        try
        {
            $generatedOutput = $openAIService->perform_text_completion($prompt, $outputJSON, $attachedImageURLs, $temperature, $maxTokens);
        }
        catch (AICompletionException $e)
        {
            throw new PipelineExecutionException("An error occurred while performing the text completion: " . $e->getMessage());
        }

        $context->setParameter($resultTo, $generatedOutput);
        return $context;
    }
}