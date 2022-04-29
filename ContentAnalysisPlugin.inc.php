<?php
/**
 * @file plugins/generic/contentAnalysis/ContentAnalysisPlugin.inc.php
 *
 * @class ContentAnalysis
 * @ingroup plugins_generic_contentAnalysis
 *
 * Copyright (c) 2020-2021 Lepidus Tecnologia
 * Copyright (c) 2020-2021 SciELO
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 * 
 * @brief Plugin class for the Content Analysis plugin.
 */
import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.contentAnalysis.classes.DocumentChecklist');

class ContentAnalysisPlugin extends GenericPlugin {
    public function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
        
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE'))
            return true;
        
        if ($success && $this->getEnabled($mainContextId)) {
            HookRegistry::register('Template::Workflow::Publication', array($this, 'addToWorkflow'));
            HookRegistry::register('submissionsubmitstep1form::display', array($this, 'addToStep1'));
            HookRegistry::register('submissionsubmitstep1form::readuservars', array($this, 'allowStep1FormToReadOurFields'));
            HookRegistry::register('SubmissionHandler::saveSubmit', array($this, 'passOurFieldsValuesToSubmission'));
            HookRegistry::register('Schema::get::submission', array($this, 'addOurFieldsToSubmissionSchema'));
            HookRegistry::register('submissionsubmitstep4form::display', array($this, 'addToStep4'));
            HookRegistry::register('submissionsubmitstep4form::validate', array($this, 'addValidationToStep4'));
        }
        
        return $success;
    }

    public function getDisplayName() {
		return __('plugins.generic.contentAnalysis.displayName');
	}

	public function getDescription() {
		return __('plugins.generic.contentAnalysis.description');
    }
    
    function addToWorkflow($hookName, $params) {
        $smarty =& $params[1];
        $output =& $params[2];

        $submission = $smarty->get_template_vars('submission');
        $publication = $submission->getCurrentPublication();

        $galleys = $submission->getGalleys();

        if(count($galleys) > 0 && $galleys[0]->getFile()) {
            $galley = $galleys[0];
            $path = \Config::getVar('files', 'files_dir') . DIRECTORY_SEPARATOR . $galley->getFile()->getData('path');
            
            $checklist = new DocumentChecklist($path);
            $dataChecklist = $checklist->executeChecklist($submission);
            $dataChecklist['placedOn'] = 'workflow';

            $smarty->assign($dataChecklist);
            
            $output .= sprintf(
                '<tab id="checklistInfo" label="%s">%s</tab>',
                __('plugins.generic.contentAnalysis.status.title'),
                $smarty->fetch($this->getTemplateResource('statusChecklist.tpl'))
            );
        }
    }

    function addToStep1($hookName, $params) {
        $request = PKPApplication::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->registerFilter("output", array($this, 'addCheckboxToStep1Filter'));
        return false;
    }

    public function addCheckboxToStep1Filter($output, $templateMgr) {
        if (preg_match('/<div[^>]+class="section formButtons/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $match = $matches[0][0];
            $posMatch = $matches[0][1];
            $checkboxTemplate = $templateMgr->fetch($this->getTemplateResource('checkboxResearch.tpl'));

            $output = substr_replace($output, $checkboxTemplate, $posMatch, 0);
            $templateMgr->unregisterFilter('output', array($this, 'addCheckboxToStep1Filter'));
        }
        return $output;
    }

    public function allowStep1FormToReadOurFields($hookName, $params) {
        $formFields =& $params[1];
        $ourFields = ['researchInvolvingHumansOrAnimals'];

        $formFields = array_merge($formFields, $ourFields);
    }

    public function passOurFieldsValuesToSubmission($hookName, $params) {
        $step = $params[0];
        if($step == 1) {
            $submission =& $params[1];
            $stepForm =& $params[2];
            $ourField = 'researchInvolvingHumansOrAnimals';

            $submission->setData($ourField, $stepForm->getData($ourField));
        }

        return false;
    }

    public function addOurFieldsToSubmissionSchema($hookName, $params) {
		$schema =& $params[0];

        $schema->properties->{'researchInvolvingHumansOrAnimals'} = (object) [
            'type' => 'string',
            'apiSummary' => true,
            'validation' => ['nullable'],
        ];

        return false;
	}

    function addToStep4($hookName, $params){
        $submission = $params[0]->submission;
        $request = PKPApplication::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        
        $galleys = $submission->getGalleys();
        $hasValidGalley = (count($galleys) > 0 && $galleys[0]->getFile());
        if($hasValidGalley) {
            $galley = $galleys[0];
            $path = \Config::getVar('files', 'files_dir') . DIRECTORY_SEPARATOR . $galley->getFile()->getData('path');
            
            $checklist = new DocumentChecklist($path);
            $dataChecklist = $checklist->executeChecklist($submission);
            $dataChecklist['placedOn'] = 'step4';
            $dataChecklist['userIsAuthor'] = $this->userIsAuthor($submission);

            $templateMgr->assign($dataChecklist);
            $templateMgr->registerFilter("output", array($this, 'contentAnalysisFormFilter'));
        }
        
        return false;
    }

    public function contentAnalysisFormFilter($output, $templateMgr) {
        if (preg_match('/<input[^>]+name="submissionId"[^>]*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $match = $matches[0][0];
            $posMatch = $matches[0][1];
            $screeningTemplate = $templateMgr->fetch($this->getTemplateResource('statusChecklist.tpl'));

            $output = substr_replace($output, $screeningTemplate, $posMatch + strlen($match), 0);
            $templateMgr->unregisterFilter('output', array($this, 'contentAnalysisFormFilter'));
        }
        return $output;
    }

    public function addValidationToStep4($hookName, $params) {
        $step4Form =& $params[0];
        $submission = $step4Form->submission;
        
        if(!$this->userIsAuthor($submission)) return;
        
        $galleys = $submission->getGalleys();
        $hasValidGalley = (count($galleys) > 0 && $galleys[0]->getFile());
        if($hasValidGalley) {
            $galley = $galleys[0];
            $path = \Config::getVar('files', 'files_dir') . DIRECTORY_SEPARATOR . $galley->getFile()->getData('path');
            
            $checklist = new DocumentChecklist($path);
            $dataChecklist = $checklist->executeChecklist($submission);
            if($dataChecklist['generalStatus'] != 'Success') {
                $step4Form->addErrorField('contentAnalysisStep4ValidationError');
                $step4Form->addError('contentAnalysisStep4ValidationError', __("plugins.generic.contentAnalysis.status.cantFinishSubmissionWithErrors"));
                return;
            }
        }
    }

    private function userIsAuthor($submission){
        $currentUser = \Application::get()->getRequest()->getUser();
        $currentUserAssignedRoles = array();
        if ($currentUser) {
            $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
            $stageAssignmentsResult = $stageAssignmentDao->getBySubmissionAndUserIdAndStageId($submission->getId(), $currentUser->getId(), $submission->getData('stageId'));
            $userGroupDao = DAORegistry::getDAO('UserGroupDAO');
            while ($stageAssignment = $stageAssignmentsResult->next()) {
                $userGroup = $userGroupDao->getById($stageAssignment->getUserGroupId(), $submission->getData('contextId'));
                $currentUserAssignedRoles[] = (int) $userGroup->getRoleId();
            }
        }

        return $currentUserAssignedRoles[0] == ROLE_ID_AUTHOR;
    }
}