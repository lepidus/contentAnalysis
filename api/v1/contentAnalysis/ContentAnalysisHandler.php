<?php

namespace APP\plugins\generic\contentAnalysis\API\v1\contentAnalysis;

use PKP\handler\APIHandler;
use PKP\security\Role;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;

class ContentAnalysisHandler extends APIHandler
{
    public function __construct()
    {
        $this->_handlerPath = 'contentAnalysis';
        $roles = [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_AUTHOR];
        $this->_endpoints = [
            'POST' => [
                [
                    'pattern' => $this->getEndpointPattern() . '/saveForm',
                    'handler' => [$this, 'saveForm'],
                    'roles' => $roles,
                ],
            ],
        ];

        parent::__construct();
    }

    public function authorize($request, &$args, $roleAssignments)
    {
        $rolePolicy = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);

        foreach ($roleAssignments as $role => $operations) {
            $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
        }
        $this->addPolicy($rolePolicy);

        return parent::authorize($request, $args, $roleAssignments);
    }

    public function saveForm($slimRequest, $response, $args)
    {
        $params = $slimRequest->getParsedBody();
        $submissionId = $args['submissionId'];

        $ethicsCouncil = $params['ethicsCouncil'];
        $documentType = $params['documentType'];

        error_log($submissionId);
        error_log($ethicsCouncil);
        error_log($documentType);

        return $response->withStatus(200);
    }
}
