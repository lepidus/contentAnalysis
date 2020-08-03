<?php
/**
 * @file plugins/generic/documentMetadataChecklist/AuthorDOIScreeningPlugin.inc.php
 *
 * @class DocumentMetadataChecklistPlugin
 * @ingroup plugins_generic_documentMetadataChecklist
 *
 * @brief Plugin class for the Document Metadata Checklist plugin.
 */
import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.documentMetadataChecklist.DocumentChecker');

class DocumentMetadataChecklistPlugin extends GenericPlugin {
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
		return __('plugins.generic.documentMetadataChecklist.displayName');
	}

	public function getDescription() {
		return __('plugins.generic.documentMetadataChecklist.description');
    }
    
    function addToWorkflow($hookName, $params) {
        $smarty =& $params[1];
        $output =& $params[2];

        $submission = $smarty->get_template_vars('submission');
        $publication = $submission->getCurrentPublication();

        $galleys = $submission->getGalleys();

        if(count($galleys) > 0) {
            $galley = $galleys[0];
            $path = $galley->getFile()->getFilePath();
            
            $checker = new DocumentChecker($path);
            $dataChecklist = $checker->executeChecklist($submission);
            $dataChecklist['placedOn'] = 'workflow';

            $smarty->assign($dataChecklist);
            
            $output .= sprintf(
                '<tab id="checklistInfo" label="%s">%s</tab>',
                __('plugins.generic.documentMetadataChecklist.status.title'),
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

        if(count($galleys) > 0) {
            $galley = $galleys[0];
            $path = $galley->getFile()->getFilePath();
            
            $checker = new DocumentChecker($path);
            $dataChecklist = $checker->executeChecklist($submission);
            $dataChecklist['placedOn'] = 'step4';

            $templateMgr->assign($dataChecklist);
            $statusChecklist = $templateMgr->fetch($this->getTemplateResource('statusChecklist.tpl'));

            $this->insertTemplateIntoStep4($statusChecklist, $output);
            if(!$outputWasEmpty) return true;
        }
    }

    private function insertTemplateIntoStep4($template, &$step4) {
        $posInsert = strpos($step4, "<p>");
        $newStep4 = substr_replace($step4, $template, $posInsert, 0);

        $step4 = $newStep4;
    }
}