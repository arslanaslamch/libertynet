<?php
# Imports the Google Cloud client library
use Google\Cloud\Translate\TranslateClient;
function home_pager($app) {
	//$design = config('home-design', 'splash');
	if(is_loggedIn()) go_to_user_home();
	$app->onHeader = (config('hide-homepage-header', false)) ? false : true;
	$app->setTitle(lang('welcome-to-social'));
	return $app->render();
}

function translate_pager($app) {
	///CSRFProtection::validate(false);
    $content = input('text');
    $to_language = input('to', 'en');
    $from_language = input('from');
    $translatorType = config('translator-method','microsoft');
    $result = null;
    if ($translatorType == "microsoft"){
        require_once(path('includes/libraries/translators/microsoft/MicrosoftTranslateText.php'));
        $azure_key = config('microsoft-translate-text-api-key-2');
        $azure_translator = new MicrosoftTranslateText($azure_key);
        try {
            $translation = $translation = $azure_translator->getTranslation($content, $to_language, $from_language);
            $result = format_output_text($translation);
        } catch(Exception $exception) {
            $message = 'Invalid Microsoft Translate Text API KEY 1';
            $result = $message;
        }
    } else if ($translatorType == "google"){

        require_once(path('includes/libraries/translators/google/vendor/autoload.php'));


        # Your Google Cloud Platform project ID
        $projectId = config("google-translate-project-id");

        # Instantiates a client
        $translate = new TranslateClient([
            'projectId' => $projectId
        ]);

        # Language Translations
        $translation = $translate->translate($content, [
            'target' => $to_language
        ]);
        $result = format_output_text($translation['text']);
    }
	return $result;
}