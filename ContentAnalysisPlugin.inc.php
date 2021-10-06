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
            HookRegistry::register('submissionsubmitstep4form::display', array($this, 'addToStep4'));
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
}