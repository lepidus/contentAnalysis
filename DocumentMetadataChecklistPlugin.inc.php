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

        $smarty->assign([
            'placedOn' => 'workflow',
            'generalStatus' => 'Error',
            'contributionStatus' => 'Error',
            'orcidStatus' => 'Warning',
            'numOrcids' => 3,
            'conflictInterestStatus' => 'Success'
        ]);
        
        $output .= sprintf(
			'<tab id="checklistInfo" label="%s">%s</tab>',
			__('plugins.generic.documentMetadataChecklist.status.title'),
			$smarty->fetch($this->getTemplateResource('statusChecklist.tpl'))
		);
    }
}