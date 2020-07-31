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
}