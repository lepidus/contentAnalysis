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
use APP\plugins\generic\contentAnalysis\classes\api\v1\ContentAnalysisController;
use APP\plugins\generic\contentAnalysis\classes\components\forms\ContentAnalysisForm;
use APP\plugins\generic\contentAnalysis\classes\DocumentChecklist;
use PKP\core\APIRouter;
use PKP\handler\APIHandler;
use PKP\stageAssignment\StageAssignment;
use PKP\userGroup\UserGroup;

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
            Hook::add('Template::SubmissionWizard::Section::Review', [$this, 'addToReviewStep']);
            Hook::add('Submission::validateSubmit', [$this, 'validateSubmissionFields']);
            Hook::add('Schema::get::submission', [$this, 'addOurFieldsToSubmissionSchema']);

            Hook::add('Dispatcher::dispatch', function (string $hookName, array $params): bool {
                $request = $params[0];
                $router = $request->getRouter();

                if (!($router instanceof APIRouter)) {
                    return Hook::CONTINUE;
                }

                if (!str_contains($request->getRequestPath(), 'api/v1/contentAnalysis')) {
                    return Hook::CONTINUE;
                }

                $handler = new APIHandler(new ContentAnalysisController());
                $router->setHandler($handler);
                $handler->runRoutes();
                exit;
            });

            try {
                $request = Application::get()->getRequest();
                $templateMgr = TemplateManager::getManager($request);
                $this->registerWorkflowAssets($request, $templateMgr);
            } catch (\Throwable $e) {
                error_log('ContentAnalysis registerWorkflowAssets error: ' . $e->getMessage());
            }
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
        if (!$currentUser) {
            return false;
        }

        $stageAssignments = StageAssignment::withSubmissionIds([$submission->getId()])
            ->withStageIds([$submission->getData('stageId')])
            ->withUserId($currentUser->getId())
            ->get();

        foreach ($stageAssignments as $stageAssignment) {
            $userGroup = UserGroup::find($stageAssignment->userGroupId);
            if ($userGroup && (int) $userGroup->roleId === Role::ROLE_ID_AUTHOR) {
                return true;
            }
        }

        return false;
    }

    private function submitterHasJournalRole()
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $currentUser = $request->getUser();

        $userGroups = UserGroup::withContextIds([$context->getId()])
            ->withUserIds([$currentUser->getId()])
            ->get();

        foreach ($userGroups as $userGroup) {
            $journalGroupAbbrev = "SciELO";
            if ($userGroup->getLocalizedData('abbrev', 'en') == $journalGroupAbbrev) {
                return true;
            }
        }

        return false;
    }

    private function registerWorkflowAssets($request, $templateMgr): void
    {
        $baseUrl = $request->getBaseUrl();
        $pluginPath = $this->getPluginPath();

        $templateMgr->addJavaScript(
            'contentAnalysis',
            "{$baseUrl}/{$pluginPath}/public/build/build.iife.js",
            [
                'inline' => false,
                'contexts' => ['backend'],
                'priority' => TemplateManager::STYLE_SEQUENCE_LAST,
            ]
        );

        $templateMgr->addStyleSheet(
            'contentAnalysisStyles',
            "{$baseUrl}/{$pluginPath}/public/build/build.css",
            [
                'contexts' => ['backend'],
            ]
        );
    }
}
