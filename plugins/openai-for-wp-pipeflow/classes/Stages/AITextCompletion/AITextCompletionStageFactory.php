<?php

require_once ABSPATH . "wp-content/plugins/wp-pipeflow/classes/Pipeline/CorePipeFlow.php";
require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/classes/Stages/AITextCompletion/AITextCompletionStage.php";

class AITextCompletionStageFactory implements AbstractStageFactory
{
    public static string $defaultModel = "gpt-4o-mini";
    public static float $defaultTemperature = 0.7;
    public static int $defaultMaxTokens = 4096;
    public static bool $defaultOutputJSON = false;
    /**
     * @inheritDoc
     */
    public function instantiate(StageConfiguration $configuration): AbstractPipelineStage
    {
        //TODO: validate $configuration
        return new AITextCompletionStage($configuration);
    }

    /**
     * @inheritDoc
     */
    public function getStageDescriptor(): StageDescriptor
    {
        $description = "Requests a text completion to OpenAI and outputs the generated text into the output context.";
        $setupParameters = array(
            "prompt" => "The text completion prompt for the AI. You can use Context Placeholders to feed context values (i.e. results from previous stages) into the prompt.",
            "attachedImageURLs" => "(optional) An array of image urls that will be attached to the prompt",
            "model" => "(optional, default: " . AITextCompletionStageFactory::$defaultModel . ") The OpenAI model to use for image generation",
            "temperature" => "(optional, default: " . AITextCompletionStageFactory::$defaultTemperature . ")",
            "maxTokens" => "(optional, default: " . AITextCompletionStageFactory::$defaultMaxTokens . ")",
            "outputJSON" => "(optional, default: " . AITextCompletionStageFactory::$defaultOutputJSON . ")",
            "resultTo" => "(optional) The name of the context parameter to which the generated text is saved",
        );

        $contextInputs = array();

        $contextOutputs = array(
            "GENERATED_TEXT_COMPLETION" => "The generated text completion string. If resultTo input settings parameter is set, the text completion is wrote into the context parameter specified there instead.",
        );

        return new StageDescriptor("AITextCompletion", $description, $setupParameters, $contextInputs, $contextOutputs);
    }
}