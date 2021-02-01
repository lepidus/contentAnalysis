<?php
/**
 * @file plugins/generic/contentAnalysis/AuthorDOIScreeningPlugin.inc.php
 *
 * @class ContentAnalysis
 * @ingroup plugins_generic_contentAnalysis
 *
 * @brief Plugin class for the Content Analysis plugin.
 */
import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.contentAnalysis.classes.DocumentChecklist');

class ContentAnalysis extends GenericPlugin {
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
            $path = $galley->getFile()->getFilePath();
            
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
        $output =& $params[1];
        $submission = $params[0]->submission;
        $templateMgr = TemplateManager::getManager(null);
        $outputWasEmpty = false;

        if($output == "") {
            $outputWasEmpty = true;
            $output = $templateMgr->fetch($params[0]->getTemplate());
        }
        
        $galleys = $submission->getGalleys();

        if(count($galleys) > 0 && $galleys[0]->getFile()) {
            $galley = $galleys[0];
            $path = $galley->getFile()->getFilePath();
            
            $checklist = new DocumentChecklist($path);
            $dataChecklist = $checklist->executeChecklist($submission);
            $dataChecklist['placedOn'] = 'step4';

            $templateMgr->assign($dataChecklist);
            $statusChecklist = $templateMgr->fetch($this->getTemplateResource('statusChecklist.tpl'));

            $this->insertTemplateIntoStep4($statusChecklist, $output);
        }
        if(!$outputWasEmpty) return true;
    }

    private function insertTemplateIntoStep4($template, &$step4) {
        $posInsert = strpos($step4, "<p>");
        $newStep4 = substr_replace($step4, $template, $posInsert, 0);

        $step4 = $newStep4;
    }
}