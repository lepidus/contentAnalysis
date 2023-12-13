<?php

namespace APP\plugins\generic\contentAnalysis\classes\components\forms;

use PKP\components\forms\FormComponent;
use PKP\components\forms\FieldRadioInput;
use PKP\components\forms\FieldOptions;
use APP\submission\Submission;

class ContentAnalysisForm extends FormComponent
{
    public $id = 'contentAnalysisForm';
    public $method = 'POST';

    public function __construct(string $action, Submission $submission, bool $submitterHasJournalRole)
    {
        $this->action = $action;

        $this->addField(new FieldRadioInput('ethicsCouncil', [
            'label' => __('plugins.generic.contentAnalysis.ethicsCouncil.label'),
            'description' => __('plugins.generic.contentAnalysis.ethicsCouncil.description'),
            'type' => 'radio',
            'isRequired' => true,
            'options' => [
                ['value' => '1', 'label' => __('common.yes')],
                ['value' => '0', 'label' => __('common.no')]
            ],
            'value' => $submission->getData('researchInvolvingHumansOrAnimals')
        ]));

        if (!$submitterHasJournalRole) {
            $this->addField(new FieldOptions('submissionType', [
                'label' => __('plugins.generic.contentAnalysis.submissionType.label'),
                'type' => 'checkbox',
                'options' => [
                    ['value' => '1', 'label' => __('plugins.generic.contentAnalysis.submissionType.option')],
                ],
                'value' => $submission->getData('nonArticle')
            ]));
        }
    }
}
