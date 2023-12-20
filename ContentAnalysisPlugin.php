<?php
/**
 * @file plugins/generic/contentAnalysis/ContentAnalysisPlugin.inc.php
 *
 * @class ContentAnalysis
 * @ingroup plugins_generic_contentAnalysis
 *
 * Copyright (c) 2020-2024 Lepidus Tecnologia
 * Copyright (c) 2020-2024 SciELO
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @brief Plugin class for the Content Analysis plugin.
 */

namespace APP\plugins\generic\contentAnalysis;

use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use APP\core\Application;
use APP\template\TemplateManager;
use APP\facades\Repo;
use PKP\security\Role;
use APP\pages\submission\SubmissionHandler;
use APP\plugins\generic\contentAnalysis\api\v1\contentAnalysis\ContentAnalysisHandler;
use APP\plugins\generic\contentAnalysis\classes\components\forms\ContentAnalysisForm;
use APP\plugins\generic\contentAnalysis\classes\DocumentChecklist;

class ContentAnalysisPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);

        if (Application::isUnderMaintenance()) {
            return $success;
        }

        if ($success && $this->getEnabled($mainContextId)) {
            Hook::add('TemplateManager::display', [$this, 'addToDetailsStep']);
            Hook::add('Template::Workflow::Publication', [$this, 'addToWorkflow']);
            Hook::add('Template::SubmissionWizard::Section::Review', [$this, 'addToReviewStep']);
            Hook::add('Submission::validateSubmit', [$this, 'validateSubmissionFields']);

            Hook::add('Dispatcher::dispatch', [$this, 'setupAPIHandler']);
            Hook::add('Schema::get::submission', [$this, 'addOurFieldsToSubmissionSchema']);
        }

        return $success;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.contentAnalysis.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.contentAnalysis.description');
    }

    public function addToDetailsStep($hookName, $params)
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $templateMgr = $params[0];

        if ($request->getRequestedPage() !== 'submission' || $request->getRequestedOp() === 'saved') {
            return false;
        }

        $submission = $request
            ->getRouter()
            ->getHandler()
            ->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);

        if (!$submission || !$submission->getData('submissionProgress')) {
            return false;
        }

        $saveFormUrl = $request->getDispatcher()->url($request, Application::ROUTE_API, $context->getPath(), "contentAnalysis/saveForm/" . $submission->getId());
        $contentAnalysisForm = new ContentAnalysisForm(
            $saveFormUrl,
            $submission,
            $this->submitterHasJournalRole()
        );

        $steps = $templateMgr->getState('steps');
        $steps = array_map(function ($step) use ($contentAnalysisForm) {
            if ($step['id'] === 'details') {
                $step['sections'][] = [
                    'id' => 'contentAnalysis',
                    'name' => __('plugins.generic.contentAnalysis.stepSection.name'),
                    'description' => __('plugins.generic.contentAnalysis.stepSection.description'),
                    'type' => SubmissionHandler::SECTION_TYPE_FORM,
                    'form' => $contentAnalysisForm->getConfig(),
                ];
            }
            return $step;
        }, $steps);

        $templateMgr->setState(['steps' => $steps]);

        return false;
    }

    public function addToReviewStep(string $hookName, array $params): bool
    {
        $step = $params[0]['step'];
        $templateMgr = $params[1];
        $output = &$params[2];
        $context = Application::get()->getRequest()->getContext();
        $submission = $templateMgr->getTemplateVars('submission');

        if ($step === 'details') {
            $ethicsCouncilSelection = $submission->getData('researchInvolvingHumansOrAnimals');
            $documentTypeSelection = $submission->getData('nonArticle');
            $settingMap = [
                null => 'notInformed',
                '1' => 'yes',
                '0' => 'no'
            ];

            $templateMgr->assign([
                'submitterHasJournalRole' => $this->submitterHasJournalRole(),
                'ethicsCouncilSelection' => $settingMap[$ethicsCouncilSelection],
                'documentTypeSelection' => $settingMap[$documentTypeSelection]
            ]);

            $output .= $templateMgr->fetch($this->getTemplateResource('review/details.tpl'));
        }

        if ($step === 'files') {
            $submissionChecklistData = $this->getSubmissionChecklist($submission);

            if (!is_null($submissionChecklistData)) {
                $templateMgr->assign($submissionChecklistData);
                $templateMgr->assign(['placedOn' => 'submission']);

                $output .= $templateMgr->fetch($this->getTemplateResource('review/checklist.tpl'));
            }
        }

        return false;
    }

    public function validateSubmissionFields($hookName, $params)
    {
        $errors = &$params[0];
        $submission = $params[1];
        $ethicsCouncilNotInformed = is_null($submission->getData('researchInvolvingHumansOrAnimals'));
        $documentTypeNotInformed = is_null($submission->getData('researchInvolvingHumansOrAnimals'));

        if ($ethicsCouncilNotInformed) {
            $errors['ethicsCouncil'] = [__('plugins.generic.contentAnalysis.ethicsCouncil.selected.notInformed')];
        }

        if ($this->submitterHasJournalRole() and $documentTypeNotInformed) {
            $errors['documentType'] = [__('plugins.generic.contentAnalysis.ethicsCouncil.selected.notInformed')];
        }

        $submissionChecklistData = $this->getSubmissionChecklist($submission);
        if (!is_null($submissionChecklistData)) {
            $generalStatus = $submissionChecklistData['generalStatus'];

            if ($generalStatus != 'Success') {
                $errors['documentChecklist'] = [__("plugins.generic.contentAnalysis.status.message{$generalStatus}")];
            }
        }

        return false;
    }

    public function addToWorkflow($hookName, $params)
    {
        $templateMgr = &$params[1];
        $output = &$params[2];

        $submission = $templateMgr->getTemplateVars('submission');
        $submissionChecklistData = $this->getSubmissionChecklist($submission);

        if (!is_null($submissionChecklistData)) {
            $templateMgr->assign($submissionChecklistData);
            $templateMgr->assign(['placedOn' => 'workflow']);

            $output .= sprintf(
                '<tab id="checklistInfo" label="%s">%s</tab>',
                __('plugins.generic.contentAnalysis.status.title'),
                $templateMgr->fetch($this->getTemplateResource('statusChecklist.tpl'))
            );
        }
    }

    private function getSubmissionChecklist($submission)
    {
        $galleys = Repo::galley()
            ->getCollector()
            ->filterByPublicationIds([$submission->getCurrentPublication()->getId()])
            ->getMany()
            ->toArray();

        if (count($galleys) > 0 && $galleys[0]->getFile()) {
            $galley = $galleys[0];
            $path = \Config::getVar('files', 'files_dir') . DIRECTORY_SEPARATOR . $galley->getFile()->getData('path');

            $checklist = new DocumentChecklist($path);
            return $checklist->executeChecklist($submission);
        }

        return null;
    }

    public function setupAPIHandler(string $hookname, array $params): void
    {
        $request = $params[0];
        $router = $request->getRouter();

        if (!($router instanceof \PKP\core\APIRouter)) {
            return;
        }

        if (str_contains($request->getRequestPath(), 'api/v1/contentAnalysis')) {
            $handler = new ContentAnalysisHandler();
        }

        if (!isset($handler)) {
            return;
        }

        $router->setHandler($handler);
        $handler->getApp()->run();
        exit;
    }

    public function addOurFieldsToSubmissionSchema($hookName, $params)
    {
        $schema = &$params[0];

        $schema->properties->{'researchInvolvingHumansOrAnimals'} = (object) [
            'type' => 'string',
            'apiSummary' => true,
            'validation' => ['nullable'],
        ];
        $schema->properties->{'nonArticle'} = (object) [
            'type' => 'string',
            'apiSummary' => true,
            'validation' => ['nullable'],
        ];

        return false;
    }

    private function userIsAuthor($submission)
    {
        $currentUser = Application::get()->getRequest()->getUser();
        $currentUserAssignedRoles = array();
        if ($currentUser) {
            $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
            $stageAssignmentsResult = $stageAssignmentDao->getBySubmissionAndUserIdAndStageId($submission->getId(), $currentUser->getId(), $submission->getData('stageId'));

            while ($stageAssignment = $stageAssignmentsResult->next()) {
                $userGroup = Repo::userGroup()->get($stageAssignment->getUserGroupId(), $submission->getData('contextId'));
                $currentUserAssignedRoles[] = (int) $userGroup->getRoleId();
            }
        }

        return $currentUserAssignedRoles[0] == Role::ROLE_ID_AUTHOR;
    }

    private function submitterHasJournalRole()
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $currentUser = $request->getUser();

        $userGroups = Repo::userGroup()->getCollector()
            ->filterByUserIds([$currentUser->getId()])
            ->filterByContextIds([$context->getId()])
            ->getMany();

        foreach ($userGroups as $userGroup) {
            $journalGroupAbbrev = "SciELO";
            if ($userGroup->getLocalizedData('abbrev', 'pt_BR') == $journalGroupAbbrev) {
                return true;
            }
        }

        return false;
    }
}
